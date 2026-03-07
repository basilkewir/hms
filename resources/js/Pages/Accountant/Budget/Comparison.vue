<template>
    <DashboardLayout v-if="themeColors && navigation" title="Budget Comparison" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Budget Comparison</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">
                        Compare expenses between {{ periods.previous }} and {{ periods.current }}.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button v-if="exportRoute && exportRoute !== '#'" @click="exportComparison" 
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

        <!-- Budget Comparison Stats Cards -->
        <div v-if="totalPrevious !== null && totalCurrent !== null" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
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
                           :style="{ color: themeColors.textSecondary }">Total Previous</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ formatCurrency(totalPrevious) }}</p>
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
                           :style="{ color: themeColors.textSecondary }">Total Current</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ formatCurrency(totalCurrent) }}</p>
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
                    <ArrowTrendingUpIcon class="h-8 w-8 mr-4" :style="{ color: totalVariance >= 0 ? themeColors.danger : themeColors.success }" />
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Total Variance</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: totalVariance >= 0 ? themeColors.danger : themeColors.success }">
                            {{ totalVariance >= 0 ? '+' : '' }}{{ formatCurrency(totalVariance) }}
                        </p>
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
                           :style="{ color: themeColors.textSecondary }">Categories</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ rows?.length || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="text-center py-8"
             :style="{ color: themeColors.textTertiary }">
            Loading comparison data...
        </div>
        <!-- Category Comparison Table -->
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
                    :style="{ color: themeColors.textPrimary }">Category Comparison</h3>
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
                                {{ periods.previous }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                {{ periods.current }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Variance
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                %
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y"
                          :style="{ borderColor: themeColors.border }">
                        <tr v-for="row in rows" :key="row?.id" 
                            class="transition-colors"
                            :style="{ backgroundColor: themeColors.card }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.card">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium"
                                     :style="{ color: themeColors.textPrimary }">{{ row?.name || 'Unknown' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(row?.previous || 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(row?.current || 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: (row?.variance || 0) >= 0 ? themeColors.danger : themeColors.success }">
                                {{ (row?.variance || 0) >= 0 ? '+' : '' }}{{ formatCurrency(Math.abs(row?.variance || 0)) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ row?.percent || 0 }}%
                            </td>
                        </tr>
                        <tr v-if="!rows || rows.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-sm"
                                :style="{ color: themeColors.textTertiary }">
                                No expense data available for this period.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
    <!-- Fallback for when components don't load -->
    <div v-else class="min-h-screen flex items-center justify-center"
         :style="{ backgroundColor: '#0b0b0b' }">
        <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-yellow-400 mx-auto mb-4"></div>
            <p class="text-white">Loading Budget Comparison...</p>
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
    CurrencyDollarIcon,
    ArrowTrendingUpIcon,
    ChartBarIcon
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
    periods: Object,
    rows: Array,
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
        return route('accountant.budget.comparison.export')
    } catch (error) {
        console.warn('Export route error:', error)
        return '#'
    }
})
const periods = computed(() => props.periods || { current: 'Current', previous: 'Previous' })
const rows = computed(() => props.rows || [])

// Computed statistics
const totalPrevious = computed(() => {
    return rows.value.reduce((total, row) => total + (row?.previous || 0), 0)
})

const totalCurrent = computed(() => {
    return rows.value.reduce((total, row) => total + (row?.current || 0), 0)
})

const totalVariance = computed(() => {
    return totalCurrent.value - totalPrevious.value
})

// Export function
const exportComparison = () => {
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
