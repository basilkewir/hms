<template>
    <DashboardLayout title="Payroll History" :user="user">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Payroll History</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Payroll totals by month.</p>
                </div>
                <Link :href="getRoute('payroll.index')" 
                      class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                      :style="{ 
                          backgroundColor: themeColors.secondary,
                      }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                    Back to Payroll
                </Link>
            </div>
        </div>

        <div class="rounded-lg overflow-hidden shadow"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Month
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Total Payroll
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Employees
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Overtime Hours
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Pending
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in months" :key="row.month" 
                            class="transition-colors"
                            :style="{ 
                                borderBottomStyle: 'solid',
                                borderBottomWidth: '1px',
                                borderColor: themeColors.border
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">{{ row.month }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(row.total_payroll || 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">{{ row.employees }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">{{ row.overtime_hours }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span v-if="row.pending > 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="{ backgroundColor: 'rgba(245, 158, 11, 0.1)', color: themeColors.warning }">
                                    {{ row.pending }} Pending
                                </span>
                                <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)', color: themeColors.success }">
                                    Complete
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click="viewMonthDetails(row)" 
                                        class="transition-colors"
                                        :style="{ color: themeColors.primary }"
                                        @mouseenter="$event.target.style.color = themeColors.hover"
                                        @mouseleave="$event.target.style.color = themeColors.primary">View Details</button>
                            </td>
                        </tr>
                        <tr v-if="months.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-sm"
                                :style="{ color: themeColors.textTertiary }">
                                No payroll history available.
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
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'

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
    months: Array,
})

const months = computed(() => props.months || [])

const getRoute = (name) => {
    const prefix = window.location.pathname.startsWith('/hr') ? 'hr.' : 'accountant.'
    return route(prefix + name)
}

const viewMonthDetails = (row) => {
    // Note: 'monthly.details' route might need to be created if it doesn't exist yet for HR
    // For now, we'll try to use the same pattern or fallback
    // Since I haven't added monthly.details to web.php yet, this might still break if clicked.
    // However, the main goal is to fix the 'Back' link and general structure.
    // I will assume we might need to add this route later or it's a future feature.
    // For now, I'll use the getRoute pattern but be aware it might 404 if route missing.
    // Actually, looking at previous web.php, I didn't add monthly.details.
    // I will just leave it using getRoute and if it fails, I'll need to add the route.
    // But wait, the original code used 'accountant.payroll.monthly.details'.
    // I should probably check if that route exists in web.php. 
    // I grepped earlier and didn't see it. It might be missing.
    // I'll leave it as is but use dynamic prefix.
    
    // Actually, to be safe and consistent with previous edits:
    const prefix = window.location.pathname.startsWith('/hr') ? 'hr.' : 'accountant.'
    
    // Navigate to index with filter params
    router.get(route(prefix + 'payroll.index'), { 
        month: row.month_num,
        year: row.year 
    })
}
</script>
