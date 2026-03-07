<template>
    <DashboardLayout v-if="themeColors && navigation" title="Budget Forecast" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Budget Forecast</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">
                        Forecast based on average spend from {{ historyPeriod.start }} to {{ historyPeriod.end }}.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button v-if="exportRoute && exportRoute !== '#'" @click="exportForecast" 
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{ 
                                backgroundColor: '#8b5cf6',
                            }"
                            @mouseenter="$event.target.style.backgroundColor = '#7c3aed'"
                            @mouseleave="$event.target.style.backgroundColor = '#8b5cf6'">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                        Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Forecast Stats Cards -->
        <div v-if="totalForecast !== null && monthlyAverage !== null" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <CalendarDaysIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.primary }" />
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Forecast Period</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">3 Months</p>
                        <p class="text-xs mt-1"
                           :style="{ color: themeColors.textTertiary }">Based on {{ historyPeriod.months_analyzed }} months</p>
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
                    <CurrencyDollarIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.success }" />
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Total Forecast</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ formatCurrency(totalForecast) }}</p>
                        <p class="text-xs mt-1"
                           :style="{ color: themeColors.textTertiary }">Next 3 months</p>
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
                    <ChartBarIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.warning }" />
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Monthly Average</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ formatCurrency(monthlyAverage) }}</p>
                        <p class="text-xs mt-1"
                           :style="{ color: themeColors.textTertiary }">Projected monthly</p>
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
                    <UserGroupIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.danger }" />
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Categories</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ categoryAverages?.length || 0 }}</p>
                        <p class="text-xs mt-1"
                           :style="{ color: themeColors.textTertiary }">Tracked categories</p>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="text-center py-8"
             :style="{ color: themeColors.textTertiary }">
            Loading forecast data...
        </div>
        <!-- Forecast Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Next 3 Months Forecast -->
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
                        :style="{ color: themeColors.textPrimary }">3-Month Forecast</h3>
                    <p class="text-sm mt-1"
                       :style="{ color: themeColors.textTertiary }">Seasonal adjustments applied</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y"
                         :style="{ borderColor: themeColors.border }">
                        <thead>
                            <tr :style="{ backgroundColor: themeColors.background }">
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textTertiary }">
                                    Month
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textTertiary }">
                                    Forecast
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textTertiary }">
                                    Adjustment
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y"
                              :style="{ borderColor: themeColors.border }">
                            <tr v-for="(month, index) in forecastMonths" :key="month?.month || index" 
                                class="transition-colors"
                                :style="{ backgroundColor: themeColors.card }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.card">
                                <td class="px-6 py-4 whitespace-nowrap text-sm"
                                    :style="{ color: themeColors.textPrimary }">
                                    <div>
                                        <div class="font-medium">{{ month?.month || 'Unknown' }}</div>
                                        <div class="text-xs"
                                             :style="{ color: themeColors.textTertiary }">
                                            Seasonal: {{ month?.seasonal_multiplier ? (month.seasonal_multiplier * 100).toFixed(0) + '%' : 'N/A' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                    :style="{ color: themeColors.textPrimary }">
                                    {{ formatCurrency(month?.total || 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span v-if="month?.variation_factor && month?.variation_factor !== 1"
                                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                          :style="{ 
                                              backgroundColor: month.variation_factor > 1 ? themeColors.success + '20' : themeColors.warning + '20',
                                              color: month.variation_factor > 1 ? themeColors.success : themeColors.warning
                                          }">
                                        {{ month.variation_factor > 1 ? '+' : '' }}{{ ((month.variation_factor - 1) * 100).toFixed(1) }}%
                                    </span>
                                    <span v-else
                                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                          :style="{ 
                                              backgroundColor: themeColors.textTertiary + '20',
                                              color: themeColors.textTertiary
                                          }">
                                        Baseline
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!forecastMonths || forecastMonths.length === 0">
                                <td colspan="3" class="px-6 py-8 text-center text-sm"
                                    :style="{ color: themeColors.textTertiary }">
                                    Not enough data to generate forecast.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Category Averages -->
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
                        :style="{ color: themeColors.textPrimary }">Category Analysis</h3>
                    <p class="text-sm mt-1"
                       :style="{ color: themeColors.textTertiary }">Monthly averages with trend analysis</p>
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
                                    Monthly Avg
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textTertiary }">
                                    Trend
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y"
                              :style="{ borderColor: themeColors.border }">
                            <tr v-for="category in categoryAverages" :key="category?.id" 
                                class="transition-colors"
                                :style="{ backgroundColor: themeColors.card }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.card">
                                <td class="px-6 py-4 whitespace-nowrap text-sm"
                                    :style="{ color: themeColors.textPrimary }">
                                    <div class="font-medium">{{ category?.name || 'Unknown' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                    :style="{ color: themeColors.textPrimary }">
                                    {{ formatCurrency(category?.avg_monthly || 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span v-if="category?.trend_factor && category.trend_factor !== 1"
                                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                          :style="{ 
                                              backgroundColor: category.trend_factor > 1 ? themeColors.danger + '20' : themeColors.success + '20',
                                              color: category.trend_factor > 1 ? themeColors.danger : themeColors.success
                                          }">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path v-if="category.trend_factor > 1" fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                            <path v-else fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        {{ category.trend_factor > 1 ? '+' : '' }}{{ ((category.trend_factor - 1) * 100).toFixed(1) }}%
                                    </span>
                                    <span v-else
                                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                          :style="{ 
                                              backgroundColor: themeColors.textTertiary + '20',
                                              color: themeColors.textTertiary
                                          }">
                                        Stable
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!categoryAverages || categoryAverages.length === 0">
                                <td colspan="3" class="px-6 py-8 text-center text-sm"
                                    :style="{ color: themeColors.textTertiary }">
                                    No category spend recorded in the last 6 months.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </DashboardLayout>
    <!-- Fallback for when components don't load -->
    <div v-else class="min-h-screen flex items-center justify-center"
         :style="{ backgroundColor: '#0b0b0b' }">
        <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-yellow-400 mx-auto mb-4"></div>
            <p class="text-white">Loading Budget Forecast...</p>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'
import {
    DocumentArrowDownIcon,
    CalendarDaysIcon,
    CurrencyDollarIcon,
    ChartBarIcon,
    UserGroupIcon
} from '@heroicons/vue/24/outline'

// Initialize theme with safety checks
const { loadTheme } = useTheme()
const themeColors = computed(() => {
    try {
        return {
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
        }
    } catch (error) {
        console.warn('Theme colors error:', error)
        // Fallback colors
        return {
            background: '#0b0b0b',
            card: '#111827',
            border: '#374151',
            textPrimary: '#f3f4f6',
            textSecondary: '#9ca3af',
            textTertiary: '#6b7280',
            primary: '#facc15',
            secondary: '#3b82f6',
            success: '#22c55e',
            warning: '#f59e0b',
            danger: '#ef4444',
            hover: 'rgba(255, 255, 255, 0.1)'
        }
    }
})

// Load theme on mount with error handling
try {
    loadTheme()
} catch (error) {
    console.warn('Theme loading error:', error)
}

const props = defineProps({
    user: Object,
    historyPeriod: Object,
    forecastMonths: Array,
    categoryAverages: Array,
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
const exportRoute = computed(() => {
    try {
        return route('accountant.budget.forecast.export')
    } catch (error) {
        console.warn('Export route error:', error)
        return '#'
    }
})
const historyPeriod = computed(() => props.historyPeriod || { start: '', end: '' })
const forecastMonths = computed(() => props.forecastMonths || [])
const categoryAverages = computed(() => props.categoryAverages || [])

// Computed statistics
const totalForecast = computed(() => {
    return forecastMonths.value.reduce((total, month) => total + (month?.total || 0), 0)
})

const monthlyAverage = computed(() => {
    const months = forecastMonths.value.length
    return months > 0 ? totalForecast.value / months : 0
})

// Export function
const exportForecast = () => {
    try {
        const params = new URLSearchParams()
        params.append('format', 'xlsx')
        
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
</script>
