<template>
    <DashboardLayout title="Budget Reports">
        <!-- Page Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Budget Reports</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Detailed budget analysis and performance trends.</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route(`${routePrefix}.budget.dashboard`)"
                          class="px-4 py-2 rounded-md transition-colors flex items-center space-x-2"
                          :style="{
                              backgroundColor: themeColors.secondary,
                              color: themeColors.textPrimary
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        <ArrowLeftIcon class="h-4 w-4" />
                        Back to Dashboard
                    </Link>
                    <button @click="exportReport"
                            class="px-4 py-2 rounded-md transition-colors flex items-center space-x-2"
                            :style="{
                                backgroundColor: themeColors.primary,
                                color: '#ffffff'
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <DocumentArrowDownIcon class="h-4 w-4" />
                        Export Report
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="p-4 rounded-md mb-6"
                 :style="{ backgroundColor: themeColors.background }">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Year</label>
                        <select v-model="filters.year"
                                @change="applyFilters"
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.card,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option v-for="yearOption in availableYears" :key="yearOption" :value="yearOption">{{ yearOption }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Month</label>
                        <select v-model="filters.month"
                                @change="applyFilters"
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.card,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option v-for="(monthName, index) in monthNames" :key="index + 1" :value="index + 1">{{ monthName }}</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Search</label>
                        <input
                            type="text"
                            v-model="filters.search"
                            @input="debouncedSearch"
                            placeholder="Search budgets..."
                            class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                            :style="{
                                backgroundColor: themeColors.card,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary,
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }"
                        >
                    </div>
                    <div class="flex items-end">
                        <button type="button"
                                @click="clearFilters"
                                class="w-full px-4 py-2 rounded-md transition-colors"
                                :style="{
                                    backgroundColor: themeColors.secondary,
                                    color: themeColors.textPrimary
                                }">
                            Clear
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Total Budgets -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Total Budgets</h3>
                    <div class="p-2 rounded-lg"
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                        <ChartBarIcon class="h-6 w-6"
                                     :style="{ color: themeColors.primary }" />
                    </div>
                </div>
                <p class="text-3xl font-bold"
                   :style="{ color: themeColors.textPrimary }">
                    {{ budgetHealthSummary?.total_budgets || 0 }}
                </p>
                <p class="text-sm mt-2"
                   :style="{ color: themeColors.textTertiary }">
                    All budgets in {{ year }}
                </p>
            </div>

            <!-- Active Budgets -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Active Budgets</h3>
                    <div class="p-2 rounded-lg"
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                        <CheckCircleIcon class="h-6 w-6"
                                        :style="{ color: themeColors.success }" />
                    </div>
                </div>
                <p class="text-3xl font-bold"
                   :style="{ color: themeColors.textPrimary }">
                    {{ budgetHealthSummary?.active_budgets || 0 }}
                </p>
                <p class="text-sm mt-2"
                   :style="{ color: themeColors.textTertiary }">
                    Currently active
                </p>
            </div>

            <!-- On Track -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">On Track</h3>
                    <div class="p-2 rounded-lg"
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                        <CheckCircleIcon class="h-6 w-6"
                                        :style="{ color: themeColors.success }" />
                    </div>
                </div>
                <p class="text-3xl font-bold"
                   :style="{ color: themeColors.textPrimary }">
                    {{ budgetHealthSummary?.on_track || 0 }}
                </p>
                <p class="text-sm mt-2"
                   :style="{ color: themeColors.textTertiary }">
                    Within budget
                </p>
            </div>

            <!-- Over Budget -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Over Budget</h3>
                    <div class="p-2 rounded-lg"
                         :style="{ backgroundColor: 'rgba(239, 68, 68, 0.1)' }">
                        <ExclamationTriangleIcon class="h-6 w-6"
                                             :style="{ color: themeColors.danger }" />
                    </div>
                </div>
                <p class="text-3xl font-bold"
                   :style="{ color: themeColors.textPrimary }">
                    {{ budgetHealthSummary?.over_budget || 0 }}
                </p>
                <p class="text-sm mt-2"
                   :style="{ color: themeColors.textTertiary }">
                    Exceeded budget
                </p>
            </div>
        </div>

        <!-- Monthly Trends -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <h3 class="text-lg font-semibold mb-4"
                :style="{ color: themeColors.textPrimary }">Monthly Trends - {{ year }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="trend in monthlyTrends" :key="trend.month"
                     class="p-4 rounded-md border"
                     :style="{
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border
                     }">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-medium"
                              :style="{ color: themeColors.textPrimary }">{{ trend.month }}</span>
                        <span class="text-sm font-medium"
                              :style="{ color: getTrendColor(trend.utilization) }">
                            {{ trend.utilization || 0 }}%
                        </span>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span :style="{ color: themeColors.textSecondary }">Budgeted:</span>
                            <span :style="{ color: themeColors.textPrimary }">{{ formatCurrency(trend.budgeted) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span :style="{ color: themeColors.textSecondary }">Actual:</span>
                            <span :style="{ color: themeColors.textPrimary }">{{ formatCurrency(trend.actual) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span :style="{ color: themeColors.textSecondary }">Variance:</span>
                            <span :style="{ color: getVarianceColor(trend.variance) }">{{ formatCurrency(trend.variance) }}</span>
                        </div>
                    </div>
                    <!-- Progress bar -->
                    <div class="mt-3 h-2 rounded-full"
                         :style="{ backgroundColor: themeColors.border }">
                        <div class="h-2 rounded-full transition-all duration-300"
                             :style="{
                                 width: Math.min(trend.utilization || 0, 100) + '%',
                                 backgroundColor: getTrendColor(trend.utilization)
                             }"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Analysis -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <h3 class="text-lg font-semibold mb-4"
                :style="{ color: themeColors.textPrimary }">Department Analysis - {{ year }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="dept in departmentAnalysis" :key="dept.name"
                     class="p-4 rounded-md border"
                     :style="{
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border
                     }">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-medium"
                              :style="{ color: themeColors.textPrimary }">{{ dept.name }}</span>
                        <span class="text-xs px-2 py-1 rounded-full"
                              :style="{
                                  backgroundColor: dept.health === 'good' ? 'rgba(34, 197, 94, 0.1)' : (dept.health === 'warning' ? 'rgba(245, 158, 11, 0.1)' : 'rgba(239, 68, 68, 0.1)'),
                                  color: dept.health === 'good' ? themeColors.success : (dept.health === 'warning' ? themeColors.warning : themeColors.danger)
                              }">
                            {{ dept.health }}
                        </span>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span :style="{ color: themeColors.textSecondary }">Total Budgeted:</span>
                            <span :style="{ color: themeColors.textPrimary }">{{ formatCurrency(dept.total_budgeted) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span :style="{ color: themeColors.textSecondary }">Total Actual:</span>
                            <span :style="{ color: themeColors.textPrimary }">{{ formatCurrency(dept.total_actual) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span :style="{ color: themeColors.textSecondary }">Budgets:</span>
                            <span :style="{ color: themeColors.textPrimary }">{{ dept.budgets_count }}</span>
                        </div>
                    </div>
                    <div class="mt-3 h-2 rounded-full"
                         :style="{ backgroundColor: themeColors.border }">
                        <div class="h-2 rounded-full transition-all duration-300"
                             :style="{
                                 width: Math.min(dept.utilization || 0, 100) + '%',
                                 backgroundColor: dept.health === 'good' ? themeColors.success : (dept.health === 'warning' ? themeColors.warning : themeColors.danger)
                             }"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Analysis Table -->
        <div class="shadow rounded-lg p-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <h3 class="text-lg font-semibold mb-4"
                :style="{ color: themeColors.textPrimary }">Budget Analysis - {{ year }}</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="text-left py-3 px-4"
                                :style="{ color: themeColors.textSecondary }">Budget Name</th>
                            <th class="text-left py-3 px-4"
                                :style="{ color: themeColors.textSecondary }">Category</th>
                            <th class="text-left py-3 px-4"
                                :style="{ color: themeColors.textSecondary }">Department</th>
                            <th class="text-right py-3 px-4"
                                :style="{ color: themeColors.textSecondary }">Budgeted</th>
                            <th class="text-right py-3 px-4"
                                :style="{ color: themeColors.textSecondary }">Actual</th>
                            <th class="text-right py-3 px-4"
                                :style="{ color: themeColors.textSecondary }">Variance</th>
                            <th class="text-right py-3 px-4"
                                :style="{ color: themeColors.textSecondary }">Utilization</th>
                            <th class="text-center py-3 px-4"
                                :style="{ color: themeColors.textSecondary }">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="budget in budgetAnalysis" :key="budget.name"
                            class="border-t"
                            :style="{ borderColor: themeColors.border }">
                            <td class="py-3 px-4 font-medium"
                                :style="{ color: themeColors.textPrimary }">{{ budget.name }}</td>
                            <td class="py-3 px-4"
                                :style="{ color: themeColors.textSecondary }">{{ budget.category || 'N/A' }}</td>
                            <td class="py-3 px-4"
                                :style="{ color: themeColors.textSecondary }">{{ budget.department || 'N/A' }}</td>
                            <td class="text-right py-3 px-4"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(budget.budgeted) }}
                            </td>
                            <td class="text-right py-3 px-4"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(budget.actual) }}
                            </td>
                            <td class="text-right py-3 px-4 font-medium"
                                :style="{ color: getVarianceColor(budget.variance) }">
                                {{ formatCurrency(budget.variance) }}
                            </td>
                            <td class="text-right py-3 px-4"
                                :style="{ color: getUtilizationColor(budget.utilization) }">
                                {{ budget.utilization }}%
                            </td>
                            <td class="text-center py-3 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="getBudgetStatusStyle(budget.status)">
                                    {{ formatStatus(budget.status) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import {
    ChartBarIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    DocumentArrowDownIcon,
    ArrowLeftIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    budgetAnalysis: Array,
    monthlyTrends: Array,
    budgetHealthSummary: Object,
    departmentAnalysis: Array,
    categoryAnalysis: Array,
    year: Number,
    month: Number,
    currencyConfig: Object,
    routePrefix: { type: String, default: 'admin' },
})

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

loadTheme()

// Reactive data
const filters = ref({
    year: props.year || new Date().getFullYear(),
    month: props.month || new Date().getMonth() + 1,
    search: ''
})

const availableYears = computed(() => {
    const currentYear = new Date().getFullYear()
    return Array.from({ length: 5 }, (_, i) => currentYear - i)
})

const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
]

// Custom formatCurrency function using currencyConfig from settings
const formatCurrency = (amount) => {
    if (!amount && amount !== 0) return '0.00'

    const currency = props.currencyConfig || { code: 'XAF', symbol: 'XAF', position: 'suffix' }
    const symbol = currency.symbol || currency.code || 'XAF'

    const formatted = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2
    }).format(Math.abs(amount))

    // Respect currency position setting
    if (currency.position === 'prefix') {
        return symbol + formatted
    }
    // suffix (default) or any other value
    return formatted + ' ' + symbol
}

// Methods
const applyFilters = () => {
    router.get(route(`${props.routePrefix}.budget.reports`), {
        year: filters.value.year,
        month: filters.value.month,
        search: filters.value.search
    }, {
        preserveState: true,
        replace: true
    })
}

const clearFilters = () => {
    filters.value.search = ''
    filters.value.year = new Date().getFullYear()
    filters.value.month = new Date().getMonth() + 1
    applyFilters()
}

const debouncedSearch = () => {
    // Simple debounce - in production use lodash debounce
    setTimeout(() => {
        applyFilters()
    }, 300)
}

const getTrendColor = (utilization) => {
    if (!utilization) return themeColors.value.success
    if (utilization <= 75) return themeColors.value.success
    if (utilization <= 90) return themeColors.value.warning
    return themeColors.value.danger
}

const getVarianceColor = (variance) => {
    if (!variance) return themeColors.value.success
    if (variance >= 0) return themeColors.value.success
    return themeColors.value.danger
}

const getUtilizationColor = (utilization) => {
    if (!utilization) return themeColors.value.success
    if (utilization <= 75) return themeColors.value.success
    if (utilization <= 90) return themeColors.value.warning
    return themeColors.value.danger
}

const getBudgetStatusStyle = (status) => {
    const statusColors = {
        'draft': {
            backgroundColor: 'rgba(107, 114, 128, 0.1)',
            color: themeColors.value.secondary
        },
        'pending_approval': {
            backgroundColor: 'rgba(251, 191, 36, 0.1)',
            color: themeColors.value.warning
        },
        'approved': {
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            color: themeColors.value.success
        },
        'rejected': {
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            color: themeColors.value.danger
        },
        'expired': {
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            color: themeColors.value.danger
        }
    }

    return statusColors[status] || statusColors['draft']
}

const formatStatus = (status) => {
    const labels = {
        'draft': 'Draft',
        'pending_approval': 'Pending',
        'approved': 'Approved',
        'rejected': 'Rejected',
        'expired': 'Expired'
    }
    return labels[status] || status
}

const exportReport = () => {
    showExportDialog()
}

const showExportDialog = () => {
    // Create modal dialog
    const modal = document.createElement('div')
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50'
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6" style="background-color: var(--kotel-card); color: var(--kotel-text-primary);">
            <h3 class="text-lg font-semibold mb-4">Choose Export Format</h3>
            <div class="space-y-3">
                <button onclick="exportData('csv')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div>
                            <div class="font-medium">CSV</div>
                            <div class="text-sm text-gray-500">Excel-compatible spreadsheet format</div>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                <button onclick="exportData('xlsx')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v6a2 2 0 002 2h2m4-4h.01M17 16h.01"></path>
                        </svg>
                        <div>
                            <div class="font-medium">Excel</div>
                            <div class="text-sm text-gray-500">Microsoft Excel format</div>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                <button onclick="exportData('pdf')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <div class="font-medium">PDF</div>
                            <div class="text-sm text-gray-500">Portable Document Format</div>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                <button onclick="exportData('docx')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div>
                            <div class="font-medium">Word</div>
                            <div class="text-sm text-gray-500">Microsoft Word format</div>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
            <div class="flex gap-3 mt-6">
                <button onclick="closeExportDialog()" class="flex-1 px-4 py-2 border rounded-lg hover:bg-gray-50 transition-colors" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    Cancel
                </button>
            </div>
        </div>
    `
    
    // Add to page
    document.body.appendChild(modal)
    
    // Make functions globally available
    window.exportData = (format) => {
        closeExportDialog()
        performExport(format)
    }
    
    window.closeExportDialog = () => {
        document.body.removeChild(modal)
        delete window.exportData
        delete window.closeExportDialog
    }
    
    // Close on backdrop click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeExportDialog()
        }
    })
}

const performExport = (format) => {
    const params = new URLSearchParams({
        format: format,
        year: filters.year || new Date().getFullYear(),
        month: filters.month || '',
        search: filters.search || ''
    })
    
    // Create a form to submit the export request
    const form = document.createElement('form')
    form.method = 'POST'
    form.action = route(`${props.routePrefix}.budget.export`)
    form.style.display = 'none'
    
    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')
    if (csrfToken) {
        const csrfInput = document.createElement('input')
        csrfInput.type = 'hidden'
        csrfInput.name = '_token'
        csrfInput.value = csrfToken.getAttribute('content')
        form.appendChild(csrfInput)
    }
    
    // Add parameters
    params.toString().split('&').forEach(param => {
        const [key, value] = param.split('=')
        const input = document.createElement('input')
        input.type = 'hidden'
        input.name = key
        input.value = decodeURIComponent(value)
        form.appendChild(input)
    })
    
    document.body.appendChild(form)
    form.submit()
    document.body.removeChild(form)
}
</script>
