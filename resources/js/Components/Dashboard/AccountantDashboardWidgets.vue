<template>
    <div class="space-y-8">
        <!-- Financial Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="(value, key) in financialSummary" :key="key"
                 class="shadow rounded-lg p-6 transition-all hover:shadow-lg cursor-pointer"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }"
                 @click="$emit('navigate', getFinancialRoute(key))">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">{{ getFinancialLabel(key) }}</p>
                        <p class="text-2xl font-bold mt-2"
                           :style="{
                               color: getFinancialColor(key)
                           }">{{ formatCurrency(value) }}</p>
                        <p v-if="key === 'netProfit'" class="text-sm mt-2"
                           :class="value >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ value >= 0 ? 'Profit' : 'Loss' }}
                        </p>
                    </div>
                    <div class="p-3 rounded-full"
                         :style="{ backgroundColor: `${getFinancialColor(key)}20` }">
                        <component :is="getFinancialIcon(key)" class="h-6 w-6"
                                   :style="{ color: getFinancialColor(key) }" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="(value, key) in metrics" :key="key"
                 class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <h3 class="text-sm font-medium mb-2"
                    :style="{ color: themeColors.textSecondary }">{{ getMetricLabel(key) }}</h3>
                <p class="text-2xl font-bold"
                   :style="{ color: themeColors.textPrimary }">{{ formatMetric(value, key) }}</p>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Revenue vs Expenses Chart -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <h2 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Revenue vs Expenses</h2>
                <div class="h-64">
                    <canvas ref="revenueExpenseChart"></canvas>
                </div>
            </div>

            <!-- Expense Categories Chart -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <h2 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Expense Categories</h2>
                <div class="h-64">
                    <canvas ref="expenseChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Transactions & Pending Payments -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Transactions -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Recent Transactions</h2>
                    <button class="text-sm font-medium transition-colors"
                            :style="{ color: themeColors.primary }"
                            @click="$emit('navigate', '/accountant/transactions')">
                        View All
                    </button>
                </div>
                <div class="space-y-4">
                    <div v-for="transaction in recentTransactions" :key="transaction.id"
                         class="flex items-center p-4 rounded-lg transition-colors hover:bg-gray-50"
                         :style="{ backgroundColor: themeColors.card }">
                        <div class="p-2 rounded-full mr-4"
                             :style="{
                                 backgroundColor: transaction.type === 'income' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'
                             }">
                            <component :is="getTransactionIcon(transaction.type)" class="h-5 w-5"
                                       :style="{
                                           color: transaction.type === 'income' ? '#10B981' : '#EF4444'
                                       }" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textPrimary }">{{ transaction.description }}</p>
                            <p class="text-xs"
                               :style="{ color: themeColors.textTertiary }">{{ formatDate(transaction.date) }}</p>
                        </div>
                        <p class="text-sm font-bold"
                           :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'">
                            {{ transaction.type === 'income' ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Pending Payments -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Pending Payments</h2>
                    <button class="text-sm font-medium transition-colors"
                            :style="{ color: themeColors.primary }"
                            @click="$emit('navigate', '/accountant/expenses')">
                        View All
                    </button>
                </div>
                <div class="space-y-4">
                    <div v-for="payment in pendingPayments" :key="payment.id"
                         class="flex items-center p-4 rounded-lg transition-colors hover:bg-gray-50"
                         :style="{ backgroundColor: themeColors.card }">
                        <div class="p-2 rounded-full mr-4"
                             :style="{ backgroundColor: `${themeColors.warning}20` }">
                            <CurrencyDollarIcon class="h-5 w-5"
                                              :style="{ color: themeColors.warning }" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textPrimary }">{{ payment.description }}</p>
                            <p class="text-xs"
                               :style="{ color: themeColors.textTertiary }">Due: {{ formatDate(payment.due_date) }}</p>
                        </div>
                        <p class="text-sm font-bold"
                           :style="{ color: themeColors.warning }">
                            {{ formatCurrency(payment.amount) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import {
    CurrencyDollarIcon,
    BanknotesIcon,
    CreditCardIcon,
    ChartBarIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    data: Object,
    themeColors: Object
})

const emit = defineEmits(['navigate'])

const revenueExpenseChart = ref(null)
const expenseChart = ref(null)
let revenueExpenseChartInstance = null
let expenseChartInstance = null

// Extract data from props
const financialSummary = ref(props.data?.financialSummary || {})
const recentTransactions = ref(props.data?.recentTransactions || [])
const pendingPayments = ref(props.data?.pendingPayments || [])
const charts = ref(props.data?.charts || {})
const metrics = ref(props.data?.metrics || {})

// Methods
const formatCurrency = (amount) => {
    if (typeof amount !== 'number') return '$0'
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount)
}

const formatMetric = (value, key) => {
    if (typeof value !== 'number') return '0'
    
    switch (key) {
        case 'avgDailyRevenue':
            return formatCurrency(value)
        case 'profitMargin':
        case 'expenseRatio':
            return `${value}%`
        case 'cashFlow':
            return formatCurrency(value)
        default:
            return value.toLocaleString()
    }
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    })
}

const getFinancialLabel = (key) => {
    const labels = {
        todaysRevenue: "Today's Revenue",
        monthlyRevenue: 'Monthly Revenue',
        monthlyExpenses: 'Monthly Expenses',
        netProfit: 'Net Profit'
    }
    return labels[key] || key
}

const getFinancialColor = (key) => {
    const colors = {
        todaysRevenue: '#10B981',
        monthlyRevenue: '#3B82F6',
        monthlyExpenses: '#EF4444',
        netProfit: '#8B5CF6'
    }
    return colors[key] || '#6B7280'
}

const getFinancialIcon = (key) => {
    const icons = {
        todaysRevenue: ArrowTrendingUpIcon,
        monthlyRevenue: CurrencyDollarIcon,
        monthlyExpenses: BanknotesIcon,
        netProfit: ChartBarIcon
    }
    return icons[key] || CurrencyDollarIcon
}

const getMetricLabel = (key) => {
    const labels = {
        avgDailyRevenue: 'Avg Daily Revenue',
        profitMargin: 'Profit Margin',
        expenseRatio: 'Expense Ratio',
        cashFlow: 'Cash Flow'
    }
    return labels[key] || key
}

const getTransactionIcon = (type) => {
    return type === 'income' ? ArrowTrendingUpIcon : ArrowTrendingDownIcon
}

const getFinancialRoute = (key) => {
    const routes = {
        todaysRevenue: '/accountant/transactions',
        monthlyRevenue: '/accountant/reports',
        monthlyExpenses: '/accountant/expenses',
        netProfit: '/accountant/reports'
    }
    return routes[key] || '/accountant/dashboard'
}

const initializeCharts = () => {
    // Initialize Revenue vs Expenses Chart
    if (revenueExpenseChart.value && charts.value?.revenueExpense) {
        const ctx = revenueExpenseChart.value.getContext('2d')
        // Chart.js implementation would go here
        ctx.fillStyle = props.themeColors.textSecondary
        ctx.font = '14px Arial'
        ctx.textAlign = 'center'
        ctx.fillText('Revenue vs Expenses Chart', ctx.canvas.width / 2, ctx.canvas.height / 2)
    }

    // Initialize Expense Categories Chart
    if (expenseChart.value && charts.value?.expenses) {
        const ctx = expenseChart.value.getContext('2d')
        // Chart.js implementation would go here
        ctx.fillStyle = props.themeColors.textSecondary
        ctx.font = '14px Arial'
        ctx.textAlign = 'center'
        ctx.fillText('Expense Categories Chart', ctx.canvas.width / 2, ctx.canvas.height / 2)
    }
}

const destroyCharts = () => {
    if (revenueExpenseChartInstance) {
        revenueExpenseChartInstance.destroy()
        revenueExpenseChartInstance = null
    }
    if (expenseChartInstance) {
        expenseChartInstance.destroy()
        expenseChartInstance = null
    }
}

// Lifecycle hooks
onMounted(() => {
    setTimeout(() => {
        initializeCharts()
    }, 100)
})

onUnmounted(() => {
    destroyCharts()
})

// Watch for data changes
watch(() => props.data, (newData) => {
    if (newData) {
        financialSummary.value = newData.financialSummary || {}
        recentTransactions.value = newData.recentTransactions || []
        pendingPayments.value = newData.pendingPayments || []
        charts.value = newData.charts || {}
        metrics.value = newData.metrics || {}
        
        destroyCharts()
        setTimeout(() => {
            initializeCharts()
        }, 100)
    }
}, { deep: true })
</script>
