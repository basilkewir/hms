<template>
    <DashboardLayout title="Budget Overview" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Budget Overview</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Monitor budget performance and financial planning.</p>
                </div>
                <div class="flex space-x-3">
                    <Link v-if="forecastRoute && forecastRoute !== '#'" 
                          :href="forecastRoute" 
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <ChartBarIcon class="h-4 w-4 mr-2" />
                        View Forecast
                    </Link>
                </div>
            </div>
        </div>

        <!-- Budget Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg shadow p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <CurrencyDollarIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.primary }" />
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Total Budget</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ formatCurrency(budgetStats.totalBudget || 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <ArrowTrendingUpIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.success }" />
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Spent</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ formatCurrency(budgetStats.totalSpent || 0) }}</p>
                        <p class="text-xs"
                           :style="{ color: themeColors.textTertiary }">{{ budgetStats.spentPercentage || 0 }}% of budget</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <BanknotesIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.warning }" />
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Remaining</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ formatCurrency(budgetStats.remaining || 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <ExclamationTriangleIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.danger }" />
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Over Budget</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ budgetStats.overBudgetCategories || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <Link v-if="comparisonRoute && comparisonRoute !== '#'" 
                  :href="comparisonRoute" 
                  class="rounded-lg p-4 transition-colors border"
                  :style="{ 
                      backgroundColor: themeColors.background,
                      borderColor: themeColors.primary,
                      borderStyle: 'solid',
                      borderWidth: '1px'
                  }"
                  @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                  @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                <div class="flex items-center">
                    <ChartBarIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.primary }" />
                    <div>
                        <h3 class="font-medium" :style="{ color: themeColors.textPrimary }">Budget vs Actual</h3>
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">Compare performance</p>
                    </div>
                </div>
            </Link>

            <Link v-if="forecastRoute && forecastRoute !== '#'" 
                  :href="forecastRoute" 
                  class="rounded-lg p-4 transition-colors border"
                  :style="{ 
                      backgroundColor: themeColors.background,
                      borderColor: themeColors.success,
                      borderStyle: 'solid',
                      borderWidth: '1px'
                  }"
                  @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                  @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                <div class="flex items-center">
                    <ArrowTrendingUpIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.success }" />
                    <div>
                        <h3 class="font-medium" :style="{ color: themeColors.textPrimary }">Forecasting</h3>
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">Future projections</p>
                    </div>
                </div>
            </Link>

            <div class="flex space-x-3">
                <select v-model="selectedFormat"
                        class="rounded-md px-3 py-2 focus:outline-none transition-colors"
                        :style="{
                            backgroundColor: themeColors.background,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary,
                            borderStyle: 'solid',
                            borderWidth: '1px'
                        }">
                    <option value="xlsx">Excel (.xlsx)</option>
                    <option value="csv">CSV (.csv)</option>
                    <option value="pdf">PDF (.pdf)</option>
                </select>
                <button @click="exportBudget" 
                        class="rounded-lg p-4 transition-colors border"
                        :style="{ 
                            backgroundColor: themeColors.background,
                            borderColor: themeColors.warning,
                            borderStyle: 'solid',
                            borderWidth: '1px'
                        }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                    <div class="flex items-center">
                        <DocumentArrowDownIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.warning }" />
                        <div>
                            <h3 class="font-medium" :style="{ color: themeColors.textPrimary }">Export Report</h3>
                            <p class="text-sm" :style="{ color: themeColors.textSecondary }">Download budget data</p>
                        </div>
                    </div>
                </button>
                <button @click="printBudget"
                        class="rounded-lg p-4 transition-colors border"
                        :style="{ 
                            backgroundColor: themeColors.background,
                            borderColor: themeColors.success,
                            borderStyle: 'solid',
                            borderWidth: '1px'
                        }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                    <div class="flex items-center">
                        <PrinterIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.success }" />
                        <div>
                            <h3 class="font-medium" :style="{ color: themeColors.textPrimary }">Print Report</h3>
                            <p class="text-sm" :style="{ color: themeColors.textSecondary }">Print budget summary</p>
                        </div>
                    </div>
                </button>
            </div>
        </div>

        <!-- Budget Categories -->
        <div class="shadow rounded-lg overflow-hidden"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
                 }">
            <div class="px-6 py-4"
                 :style="{ borderBottom: '1px solid ' + themeColors.border }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Budget by Category</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y"
                     :style="{ borderColor: themeColors.border }">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Category
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Budgeted
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Actual
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Variance
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Progress
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y"
                          :style="{ borderColor: themeColors.border }">
                        <tr v-for="category in budgetCategories" :key="category?.id" 
                            class="transition-colors"
                            :style="{ backgroundColor: themeColors.card }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.card">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium"
                                     :style="{ color: themeColors.textPrimary }">{{ category?.name || 'Unknown' }}</div>
                                <div class="text-sm"
                                     :style="{ color: themeColors.textTertiary }">{{ category?.description || '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(category?.budgeted || 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(category?.actual || 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: (category?.variance || 0) >= 0 ? themeColors.success : themeColors.danger }">
                                {{ (category?.variance || 0) >= 0 ? '+' : '' }}{{ formatCurrency(Math.abs(category?.variance || 0)) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="w-full rounded-full h-2"
                                     :style="{ backgroundColor: themeColors.border }">
                                    <div class="h-2 rounded-full"
                                         :class="getProgressColor(category?.progress || 0)"
                                         :style="{ width: Math.min(category?.progress || 0, 100) + '%' }"></div>
                                </div>
                                <div class="text-xs mt-1"
                                     :style="{ color: themeColors.textTertiary }">{{ category?.progress || 0 }}%</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(category?.status || 'unknown')">
                                    {{ formatStatus(category?.status || 'unknown') }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="!budgetCategories || budgetCategories.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center"
                                :style="{ color: themeColors.textTertiary }">
                                No budget categories found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'
import {
    ChartBarIcon,
    CurrencyDollarIcon,
    ArrowTrendingUpIcon,
    BanknotesIcon,
    ExclamationTriangleIcon,
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
    budgetStats: Object,
    budgetCategories: Array,
})

const navigation = computed(() => {
    try {
        return getNavigationForRole('accountant') || []
    } catch (error) {
        console.warn('Navigation error:', error)
        return []
    }
})

// Safe route computations
const forecastRoute = computed(() => {
    try {
        return route('accountant.budget.forecast')
    } catch (error) {
        console.warn('Forecast route error:', error)
        return '#'
    }
})

const comparisonRoute = computed(() => {
    try {
        return route('accountant.budget.comparison')
    } catch (error) {
        console.warn('Comparison route error:', error)
        return '#'
    }
})

const exportRoute = computed(() => {
    try {
        return route('accountant.budget.export')
    } catch (error) {
        console.warn('Export route error:', error)
        return '#'
    }
})

const budgetStats = computed(() => props.budgetStats || {
    totalBudget: 0,
    totalSpent: 0,
    spentPercentage: 0,
    remaining: 0,
    overBudgetCategories: 0
})

const budgetCategories = computed(() => props.budgetCategories || [])
const selectedFormat = ref('xlsx')

const getProgressColor = (progress) => {
    if (progress <= 75) return 'bg-green-500'
    if (progress <= 90) return 'bg-yellow-500'
    if (progress <= 100) return 'bg-orange-500'
    return 'bg-red-500'
}

const getStatusColor = (status) => {
    const colors = {
        on_track: 'bg-green-100 text-green-800',
        under_budget: 'bg-blue-100 text-blue-800',
        over_budget: 'bg-red-100 text-red-800',
        at_risk: 'bg-yellow-100 text-yellow-800',
        unknown: 'bg-gray-100 text-gray-800'
    }
    return colors[status] || colors.unknown
}

const formatStatus = (status) => {
    if (!status) return 'Unknown'
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const exportBudget = () => {
    try {
        const params = new URLSearchParams()
        params.append('format', selectedFormat.value || 'xlsx')
        
        const queryString = params.toString()
        const url = queryString ? `?${queryString}` : ''
        
        if (exportRoute.value && exportRoute.value !== '#') {
            window.location.href = exportRoute.value + url
        } else {
            console.error('Export route not found')
        }
    } catch (error) {
        console.error('Export error:', error)
    }
}

const printBudget = () => {
    try {
        const params = new URLSearchParams()
        params.append('format', 'print')
        
        const queryString = params.toString()
        const url = queryString ? `?${queryString}` : ''
        
        if (exportRoute.value && exportRoute.value !== '#') {
            window.open(exportRoute.value + url, '_blank')
        } else {
            console.error('Print route not found')
        }
    } catch (error) {
        console.error('Print error:', error)
    }
}
</script>
