<template>
    <DashboardLayout title="Payroll Management" :user="user">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Payroll Management</h1>
                    <p class="mt-2"
                        :style="{ color: themeColors.textSecondary }">Manage employee payroll and compensation.</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="getRoute('payroll.process')"
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <CurrencyDollarIcon class="h-4 w-4 mr-2" />
                        Process Payroll
                    </Link>
                </div>
            </div>
        </div>

        <!-- Payroll Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                        <UsersIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Employees</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ payrollStats.totalEmployees }}</p>
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
                           :style="{ color: themeColors.textSecondary }">Monthly Payroll</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ formatCurrency(payrollStats.monthlyPayroll || 0) }}</p>
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
                         :style="{ backgroundColor: 'rgba(245, 158, 11, 0.1)' }">
                        <ClockIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Pending Approvals</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ payrollStats.pendingApprovals }}</p>
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
                         :style="{ backgroundColor: 'rgba(139, 92, 246, 0.1)' }">
                        <DocumentTextIcon class="h-6 w-6" :style="{ color: '#8b5cf6' }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Overtime Hours</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ payrollStats.overtimeHours }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <Link :href="route('accountant.payroll.process')"
                  class="rounded-lg p-4 border shadow-sm transition-colors"
                  :style="{ 
                      backgroundColor: themeColors.card,
                      borderColor: themeColors.border,
                      borderStyle: 'solid',
                      borderWidth: '1px'
                  }"
                  @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                  @mouseleave="$event.target.style.backgroundColor = themeColors.card">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3"
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                        <CurrencyDollarIcon class="h-5 w-5" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <h3 class="font-medium" :style="{ color: themeColors.textPrimary }">Process Payroll</h3>
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">Run monthly payroll</p>
                    </div>
                </div>
            </Link>

            <Link :href="route('accountant.payroll.history')"
                  class="rounded-lg p-4 border shadow-sm transition-colors"
                  :style="{ 
                      backgroundColor: themeColors.card,
                      borderColor: themeColors.border,
                      borderStyle: 'solid',
                      borderWidth: '1px'
                  }"
                  @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                  @mouseleave="$event.target.style.backgroundColor = themeColors.card">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3"
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                        <ClockIcon class="h-5 w-5" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <h3 class="font-medium" :style="{ color: themeColors.textPrimary }">Payroll History</h3>
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">View past payrolls</p>
                    </div>
                </div>
            </Link>

            <Link :href="route('accountant.payroll.taxes')"
                  class="rounded-lg p-4 border shadow-sm transition-colors"
                  :style="{ 
                      backgroundColor: themeColors.card,
                      borderColor: themeColors.border,
                      borderStyle: 'solid',
                      borderWidth: '1px'
                  }"
                  @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                  @mouseleave="$event.target.style.backgroundColor = themeColors.card">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3"
                         :style="{ backgroundColor: 'rgba(139, 92, 246, 0.1)' }">
                        <DocumentTextIcon class="h-5 w-5" :style="{ color: '#8b5cf6' }" />
                    </div>
                    <div>
                        <h3 class="font-medium" :style="{ color: themeColors.textPrimary }">Tax Reports</h3>
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">Generate tax documents</p>
                    </div>
                </div>
            </Link>

            <div class="flex space-x-3">
                <select v-model="selectedFormat"
                        class="rounded-md px-3 py-2 focus:outline-none transition-colors"
                        :style="{ 
                            backgroundColor: themeColors.background,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary
                        }">
                    <option value="xlsx">Excel (.xlsx)</option>
                    <option value="csv">CSV (.csv)</option>
                    <option value="pdf">PDF (.pdf)</option>
                </select>
                <button @click="exportPayroll"
                        class="rounded-lg p-4 border shadow-sm transition-colors"
                        :style="{ 
                            backgroundColor: themeColors.card,
                            borderColor: themeColors.border,
                            borderStyle: 'solid',
                            borderWidth: '1px'
                        }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.card">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3"
                             :style="{ backgroundColor: 'rgba(251, 146, 60, 0.1)' }">
                            <DocumentArrowDownIcon class="h-5 w-5" :style="{ color: '#fb923c' }" />
                        </div>
                        <div>
                            <h3 class="font-medium" :style="{ color: themeColors.textPrimary }">Export Data</h3>
                            <p class="text-sm text-orange-600">Download payroll reports</p>
                        </div>
                    </div>
                </button>
                <button @click="printPayroll"
                        class="rounded-lg p-4 border shadow-sm transition-colors"
                        :style="{ 
                            backgroundColor: themeColors.card,
                            borderColor: themeColors.border,
                            borderStyle: 'solid',
                            borderWidth: '1px'
                        }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.card">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3"
                             :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                            <PrinterIcon class="h-5 w-5" :style="{ color: themeColors.success }" />
                        </div>
                        <div>
                            <h3 class="font-medium" :style="{ color: themeColors.textPrimary }">Print Report</h3>
                            <p class="text-sm text-green-600">Print payroll summary</p>
                        </div>
                    </div>
                </button>
            </div>
        </div>

        <!-- Employee Payroll Table -->
        <div class="rounded-lg overflow-hidden shadow"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderBottomWidth: '1px'
                 }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Employee Payroll</h3>
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
                                Base Salary
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Overtime
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Deductions
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Net Pay
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
                        <tr v-for="employee in employees" :key="employee.id" 
                            class="transition-colors"
                            :style="{ 
                                borderBottomStyle: 'solid',
                                borderBottomWidth: '1px',
                                borderColor: themeColors.border
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-4"
                                         :style="{ backgroundColor: themeColors.background }">
                                        <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                            {{ getInitials(employee.name) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ employee.name }}</div>
                                        <div class="text-sm" :style="{ color: themeColors.textTertiary }">{{ employee.employee_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatDepartment(employee.department) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(employee.base_salary || 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(employee.overtime_pay || 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(employee.deductions || 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.success }">
                                {{ formatCurrency(employee.net_pay || 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(employee.status)">
                                    {{ formatStatus(employee.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click="viewPayslip(employee)" 
                                        class="mr-3 transition-colors"
                                        :style="{ color: themeColors.primary }"
                                        @mouseenter="$event.target.style.color = themeColors.hover"
                                        @mouseleave="$event.target.style.color = themeColors.primary">View</button>
                                <button v-if="employee.status === 'pending'"
                                        @click="approvePayroll(employee)"
                                        class="transition-colors"
                                        :style="{ color: themeColors.success }"
                                        @mouseenter="$event.target.style.color = themeColors.hover"
                                        @mouseleave="$event.target.style.color = themeColors.success">Approve</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'
import {
    CurrencyDollarIcon,
    UsersIcon,
    ClockIcon,
    DocumentTextIcon,
    DocumentArrowDownIcon,
    PrinterIcon
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
    payrollStats: Object,
    employees: Array,
})

const payrollStats = computed(() => props.payrollStats || {
    totalEmployees: 0,
    monthlyPayroll: 0,
    pendingApprovals: 0,
    overtimeHours: 0
})

const employees = computed(() => props.employees || [])
const selectedFormat = ref('xlsx')

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const formatDepartment = (department) => {
    return department.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getStatusColor = (status) => {
    const colors = {
        approved: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        rejected: 'bg-red-100 text-red-800',
        paid: 'bg-blue-100 text-blue-800'
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1)
}

const getRoute = (name) => {
    const prefix = window.location.pathname.startsWith('/hr') ? 'hr.' : 'accountant.'
    return route(prefix + name)
}

const exportPayroll = () => {
    const params = new URLSearchParams()
    params.append('format', selectedFormat.value)
    
    const queryString = params.toString()
    const url = queryString ? `?${queryString}` : ''
    
    window.location.href = getRoute('payroll.export') + url
}

const printPayroll = () => {
    const params = new URLSearchParams()
    params.append('format', 'print')
    
    const queryString = params.toString()
    const url = queryString ? `?${queryString}` : ''
    
    window.open(getRoute('payroll.export') + url, '_blank')
}

const viewPayslip = (employee) => {
    router.get(getRoute('payroll.history'), { employee_id: employee.id }, {
        preserveScroll: true
    })
}

const approvePayroll = (employee) => {
    router.get(getRoute('payroll.process'), { employee_id: employee.id }, {
        preserveScroll: true
    })
}
</script>
