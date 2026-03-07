<template>
    <DashboardLayout title="Process Payroll" :user="user">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div>
                <h1 class="text-2xl font-bold mb-2"
                    :style="{ color: themeColors.textPrimary }">Process Payroll</h1>
                <p class="mt-2"
                   :style="{ color: themeColors.textSecondary }">Pending payroll approvals for {{ period.start }} to {{ period.end }}.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(245, 158, 11, 0.1)' }">
                        <ClockIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Pending Employees</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ pendingEmployees.length }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                        <CurrencyDollarIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Pending Total</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ formatCurrency(pendingTotal || 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-lg overflow-hidden shadow"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b flex justify-between items-center"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderBottomWidth: '1px'
                 }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Pending Payroll</h3>
                <button v-if="pendingEmployees.length > 0"
                        @click="approveAllPayroll"
                        class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                        :style="{ 
                            backgroundColor: themeColors.success,
                        }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                    Approve All ({{ pendingEmployees.length }})
                </button>
            </div>
            
            <!-- Table Content -->
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Employee
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Department
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Net Pay
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Overtime Hours
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="employee in pendingEmployees" :key="employee.id" 
                            class="transition-colors"
                            :style="{ 
                                borderBottomStyle: 'solid',
                                borderBottomWidth: '1px',
                                borderColor: themeColors.border
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ employee.name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ employee.department }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(employee.net_pay || 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ employee.overtime_hours }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="{ backgroundColor: 'rgba(245, 158, 11, 0.1)', color: themeColors.warning }">
                                    Pending
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click="viewDetails(employee)" 
                                        class="mr-3 transition-colors"
                                        :style="{ color: themeColors.primary }"
                                        @mouseenter="$event.target.style.color = themeColors.hover"
                                        @mouseleave="$event.target.style.color = themeColors.primary">View</button>
                                <button @click="approveEmployee(employee)"
                                        class="transition-colors"
                                        :style="{ color: themeColors.success }"
                                        @mouseenter="$event.target.style.color = themeColors.hover"
                                        @mouseleave="$event.target.style.color = themeColors.success">Approve</button>
                            </td>
                        </tr>
                        <tr v-if="pendingEmployees.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-sm"
                                :style="{ color: themeColors.textTertiary }">
                                No pending payroll items.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'
import {
    ClockIcon,
    CurrencyDollarIcon
} from '@heroicons/vue/24/outline'

// Initialize theme
const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    primary: `var(--kotel-primary)`,
    secondary: `var(--kotel-secondary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
    hover: `rgba(255, 255, 255, 0.1)`
}))

// Load theme on mount
loadTheme()

const props = defineProps({
    user: Object,
    period: Object,
    pendingEmployees: Array,
    pendingTotal: Number,
})

const period = computed(() => props.period || { start: '', end: '' })
const pendingEmployees = computed(() => props.pendingEmployees || [])
const pendingTotal = computed(() => props.pendingTotal || 0)

// Payroll processing methods
const getRoute = (name) => {
    const prefix = window.location.pathname.startsWith('/hr') ? 'hr.' : 'accountant.'
    return route(prefix + name)
}

const viewDetails = (employee) => {
    router.get(getRoute('payroll.history'), { employee_id: employee.id }, {
        preserveScroll: true
    })
}

const approveEmployee = (employee) => {
    if (confirm(`Are you sure you want to approve payroll for ${employee.name}?`)) {
        router.post(getRoute('payroll.approve'), {
            employee_id: employee.id,
            period_start: period.value.start,
            period_end: period.value.end
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Refresh the page to show updated data
                router.reload({ only: ['pendingEmployees', 'pendingTotal'] })
            }
        })
    }
}

const approveAllPayroll = () => {
    if (confirm(`Are you sure you want to approve all ${pendingEmployees.value.length} pending payroll items? Total amount: ${formatCurrency(pendingTotal.value)}`)) {
        router.post(getRoute('payroll.approve.all'), {
            period_start: period.value.start,
            period_end: period.value.end
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Refresh the page to show updated data
                router.reload({ only: ['pendingEmployees', 'pendingTotal'] })
            }
        })
    }
}
</script>
