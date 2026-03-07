<template>
    <DashboardLayout title="Financial Reports">
        <!-- Page Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Financial Reports</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Monitor and analyze your hotel's financial performance.</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="exportFinancialReport" 
                            class="px-4 py-2 rounded-md transition-colors flex items-center space-x-2"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                                color: themeColors.textPrimary 
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <DocumentArrowDownIcon class="h-4 w-4" />
                        Export Report
                    </button>
                    <button @click="toggleFilters"
                            class="px-4 py-2 rounded-md transition-colors flex items-center space-x-2"
                            :style="{ 
                                backgroundColor: themeColors.secondary,
                                color: themeColors.textPrimary 
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        <FunnelIcon class="h-4 w-4" />
                        Filter
                    </button>
                </div>
            </div>

            <!-- Date Range Filter -->
            <div v-if="showFilters" class="p-4 rounded-md mb-6"
                 :style="{ backgroundColor: themeColors.background }">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Start Date</label>
                        <input v-model="filters.start_date" type="date"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.card,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">End Date</label>
                        <input v-model="filters.end_date" type="date"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.card,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                    </div>
                    <div class="flex items-end">
                        <button @click="applyFilters"
                                class="w-full px-4 py-2 rounded-md transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.success,
                                    color: 'white'
                                }">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Revenue Summary -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Total Revenue</h3>
                    <div class="p-2 rounded-lg"
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                        <CurrencyDollarIcon class="h-6 w-6"
                                          :style="{ color: themeColors.success }" />
                    </div>
                </div>
                <p class="text-3xl font-bold"
                   :style="{ color: themeColors.textPrimary }">
                    {{ formatCurrency(financialData.totalRevenue || 0) }}
                </p>
                <p class="text-sm mt-2"
                   :style="{ color: themeColors.textTertiary }">
                    Last 30 days
                </p>
                <div class="mt-4 flex items-center text-sm">
                    <span :style="{ color: themeColors.success }">+12.5%</span>
                    <span :style="{ color: themeColors.textTertiary }" class="ml-2">from last month</span>
                </div>
            </div>

            <!-- Expenses Summary -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Total Expenses</h3>
                    <div class="p-2 rounded-lg"
                         :style="{ backgroundColor: 'rgba(239, 68, 68, 0.1)' }">
                        <CurrencyDollarIcon class="h-6 w-6"
                                          :style="{ color: themeColors.danger }" />
                    </div>
                </div>
                <p class="text-3xl font-bold"
                   :style="{ color: themeColors.textPrimary }">
                    {{ formatCurrency(financialData.totalExpenses || 0) }}
                </p>
                <p class="text-sm mt-2"
                   :style="{ color: themeColors.textTertiary }">
                    Last 30 days
                </p>
                <div class="mt-4 flex items-center text-sm">
                    <span :style="{ color: themeColors.danger }">+8.2%</span>
                    <span :style="{ color: themeColors.textTertiary }" class="ml-2">from last month</span>
                </div>
            </div>

            <!-- Net Profit Summary -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Net Profit</h3>
                    <div class="p-2 rounded-lg"
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                        <ChartBarIcon class="h-6 w-6"
                                     :style="{ color: themeColors.primary }" />
                    </div>
                </div>
                <p class="text-3xl font-bold"
                   :style="{ color: themeColors.textPrimary }">
                    {{ formatCurrency((financialData.totalRevenue || 0) - (financialData.totalExpenses || 0)) }}
                </p>
                <p class="text-sm mt-2"
                   :style="{ color: themeColors.textTertiary }">
                    Last 30 days
                </p>
                <div class="mt-4 flex items-center text-sm">
                    <span :style="{ color: themeColors.success }">+15.3%</span>
                    <span :style="{ color: themeColors.textTertiary }" class="ml-2">from last month</span>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Revenue Chart -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Revenue Trend</h3>
                <div class="h-64 flex items-center justify-center"
                     :style="{ color: themeColors.textTertiary }">
                    Revenue chart will be displayed here
                </div>
            </div>

            <!-- Expense Breakdown -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Expense Breakdown</h3>
                <div class="space-y-3">
                    <div v-for="category in financialData.expenseByCategory" :key="category.name"
                         class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 rounded-full"
                                 :style="{ backgroundColor: category.color || themeColors.primary }"></div>
                            <span :style="{ color: themeColors.textPrimary }">{{ category.name }}</span>
                        </div>
                        <span class="font-medium"
                              :style="{ color: themeColors.textPrimary }">
                            {{ formatCurrency(category.total) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Analysis -->
        <div class="shadow rounded-lg p-6"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <h3 class="text-lg font-semibold mb-4"
                :style="{ color: themeColors.textPrimary }">Department Analysis</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="text-left py-3 px-4"
                                :style="{ color: themeColors.textSecondary }">Department</th>
                            <th class="text-right py-3 px-4"
                                :style="{ color: themeColors.textSecondary }">Revenue</th>
                            <th class="text-right py-3 px-4"
                                :style="{ color: themeColors.textSecondary }">Expenses</th>
                            <th class="text-right py-3 px-4"
                                :style="{ color: themeColors.textSecondary }">Profit</th>
                            <th class="text-right py-3 px-4"
                                :style="{ color: themeColors.textSecondary }">Variance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="dept in financialData.departmentAnalysis" :key="dept.department.id"
                            class="border-t"
                            :style="{ borderColor: themeColors.border }">
                            <td class="py-3 px-4"
                                :style="{ color: themeColors.textPrimary }">{{ dept.department.name }}</td>
                            <td class="text-right py-3 px-4"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(dept.revenue) }}
                            </td>
                            <td class="text-right py-3 px-4"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(dept.expenses) }}
                            </td>
                            <td class="text-right py-3 px-4 font-medium"
                                :style="{ color: dept.profit >= 0 ? themeColors.success : themeColors.danger }">
                                {{ formatCurrency(dept.profit) }}
                            </td>
                            <td class="text-right py-3 px-4"
                                :style="{ color: dept.budgetVariance >= 0 ? themeColors.success : themeColors.danger }">
                                {{ formatCurrency(dept.budgetVariance) }}
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
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'
import {
    CurrencyDollarIcon,
    ChartBarIcon,
    DocumentArrowDownIcon,
    FunnelIcon
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
    financialData: {
        type: Object,
        default: () => ({
            totalRevenue: 0,
            totalExpenses: 0,
            netProfit: 0,
            expenseByCategory: [],
            departmentAnalysis: [],
            recentTransactions: []
        })
    }
})

// Reactive data
const showFilters = ref(false)
const filters = ref({
    start_date: '',
    end_date: ''
})

// Methods
const toggleFilters = () => {
    showFilters.value = !showFilters.value
}

const applyFilters = () => {
    // Apply filters logic here
    console.log('Applying filters:', filters.value)
}

const exportFinancialReport = () => {
    // Export logic here
    console.log('Exporting financial report')
}
</script>

<style scoped>
/* Fix placeholder colors for inputs */
input::placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input::-webkit-input-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input::-moz-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input:-ms-input-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}
</style>
