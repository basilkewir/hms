<template>
    <DashboardLayout title="Accountant Dashboard" :user="user">
        <!-- Welcome Section -->
        <div class="rounded-lg shadow p-6 mb-8 border"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold" :style="{ color: themeColors.textPrimary }">Financial Overview</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Manage financial operations, process payments, and generate reports.</p>
                </div>
                <div class="text-right">
                    <p class="text-sm" :style="{ color: themeColors.textTertiary }">{{ currentDateTime }}</p>
                    <p class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">{{ user.full_name }}</p>
                </div>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <CurrencyDollarIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.success }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Today's Revenue</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(financialSummary?.todaysRevenue || 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <BanknotesIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.primary }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Monthly Revenue</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(financialSummary?.monthlyRevenue || 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <ReceiptPercentIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.danger }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Monthly Expenses</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(financialSummary?.monthlyExpenses || 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <ChartBarIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.warning }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Net Profit</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(financialSummary?.netProfit || 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Revenue vs Expenses -->
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Revenue vs Expenses</h3>
                <div class="h-64">
                    <canvas ref="revenueExpenseChart"></canvas>
                </div>
            </div>

            <!-- Expense Breakdown -->
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Expense Categories</h3>
                <div class="h-64">
                    <canvas ref="expenseChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Financial Activities -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Recent Transactions -->
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Recent Transactions</h3>
                <div class="space-y-3">
                    <div v-for="transaction in recentTransactions" :key="transaction.id"
                         class="flex items-center justify-between p-3 rounded-lg border"
                         :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3"
                                 :style="getTransactionPillStyle(transaction.type)">
                                <component :is="getTransactionIcon(transaction.type)" class="h-4 w-4" />
                            </div>
                            <div>
                                <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ transaction.description }}</p>
                                <p class="text-xs" :style="{ color: themeColors.textTertiary }">{{ formatDate(transaction.date) }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium" :style="{ color: transaction.type === 'income' ? themeColors.success : themeColors.danger }">
                                {{ transaction.type === 'income' ? '+' : '-' }}{{ formatCurrency(transaction.amount || 0) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Payments -->
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Pending Payments</h3>
                <div class="space-y-3">
                    <div v-for="payment in pendingPayments" :key="payment.id"
                         class="flex items-center justify-between p-3 rounded-lg border"
                         :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                        <div>
                            <p class="text-sm font-bold" :style="{ color: themeColors.textPrimary }">{{ payment.description }}</p>
                            <p class="text-xs" :style="{ color: themeColors.warning }">Due: {{ formatDate(payment.due_date) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-extrabold" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(payment.amount || 0) }}</p>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold"
                                  :style="{ backgroundColor: themeColors.warning, color: '#000' }">
                                Pending
                            </span>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Quick Actions -->
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Quick Actions</h3>
                <div class="space-y-3">
                    <Link :href="route('accountant.expenses.create')"
                          class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md hover:opacity-90 transition-opacity"
                          :style="{ backgroundColor: themeColors.danger, color: '#000' }">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Add Expense
                    </Link>
                    <Link :href="route('accountant.transactions.index')"
                          class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md hover:opacity-90 transition-opacity"
                          :style="{ backgroundColor: themeColors.primary, color: '#000' }">
                        <CurrencyDollarIcon class="h-4 w-4 mr-2" />
                        View Transactions
                    </Link>
                    <Link :href="route('accountant.payroll.index')"
                          class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md hover:opacity-90 transition-opacity"
                          :style="{ backgroundColor: themeColors.success, color: '#000' }">
                        <BanknotesIcon class="h-4 w-4 mr-2" />
                        Process Payroll
                    </Link>
                    <Link :href="route('accountant.reports.profit-loss')"
                          class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md hover:opacity-90 transition-opacity"
                          :style="{ backgroundColor: themeColors.warning, color: '#000' }">
                        <DocumentTextIcon class="h-4 w-4 mr-2" />
                        Financial Reports
                    </Link>
                    <Link :href="route('accountant.budget.index')"
                          class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md hover:opacity-90 transition-opacity"
                          :style="{ backgroundColor: themeColors.primary, color: '#000' }">
                        <ChartPieIcon class="h-4 w-4 mr-2" />
                        Budget Management
                    </Link>
                    <Link :href="route('accountant.reports.revenue')"
                          class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md hover:opacity-90 transition-opacity"
                          :style="{ backgroundColor: themeColors.secondary, color: '#000' }">
                        <ChartBarIcon class="h-4 w-4 mr-2" />
                        Revenue Analysis
                    </Link>
                </div>
            </div>
        </div>

        <!-- Financial Metrics -->
        <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Key Financial Metrics</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="text-2xl font-bold" :style="{ color: themeColors.primary }">{{ formatCurrency(metrics.avgDailyRevenue || 0) }}</div>
                    <div class="text-sm" :style="{ color: themeColors.textSecondary }">Avg Daily Revenue</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold" :style="{ color: themeColors.success }">{{ metrics.profitMargin }}%</div>
                    <div class="text-sm" :style="{ color: themeColors.textSecondary }">Profit Margin</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold" :style="{ color: themeColors.warning }">{{ metrics.expenseRatio }}%</div>
                    <div class="text-sm" :style="{ color: themeColors.textSecondary }">Expense Ratio</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold" :style="{ color: themeColors.secondary }">{{ formatCurrency(metrics.cashFlow || 0) }}</div>
                    <div class="text-sm" :style="{ color: themeColors.textSecondary }">Cash Flow</div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme'
import {
    CurrencyDollarIcon,
    BanknotesIcon,
    ReceiptPercentIcon,
    ChartBarIcon,
    ChartPieIcon,
    PlusIcon,
    DocumentTextIcon,
    ArrowUpIcon,
    ArrowDownIcon
} from '@heroicons/vue/24/outline'
import Chart from 'chart.js/auto'

const props = defineProps({
    user: Object,
    financialSummary: Object,
    recentTransactions: Array,
    pendingPayments: Array,
    charts: Object,
    metrics: { type: Object, default: () => ({}) },
})

const revenueExpenseChart = ref(null)
const expenseChart = ref(null)

const currentDateTime = computed(() => {
    return new Date().toLocaleString()
})

const { currentTheme } = useTheme()

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
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)'
}))

const recentTransactions = computed(() => props.recentTransactions || [])

const pendingPayments = computed(() => props.pendingPayments || [])

const getTransactionIcon = (type) => {
    return type === 'income' ? ArrowUpIcon : ArrowDownIcon
}

const getTransactionColor = (type) => {
    return type === 'income' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'
}

const getTransactionPillStyle = (type) => {
    return type === 'income'
        ? { backgroundColor: themeColors.value.success, color: '#000' }
        : { backgroundColor: themeColors.value.danger, color: '#000' }
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

// Chart initialization
onMounted(() => {
    if (props.charts?.revenueExpense && revenueExpenseChart.value) {
        new Chart(revenueExpenseChart.value, {
            type: 'line',
            data: {
                labels: props.charts.revenueExpense.map(item => item.month),
                datasets: [
                    {
                        label: 'Revenue',
                        data: props.charts.revenueExpense.map(item => item.revenue),
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.1
                    },
                    {
                        label: 'Expenses',
                        data: props.charts.revenueExpense.map(item => item.expenses),
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value)
                            }
                        }
                    }
                }
            }
        })
    }

    if (props.charts?.expenses && expenseChart.value) {
        new Chart(expenseChart.value, {
            type: 'doughnut',
            data: {
                labels: props.charts.expenses.map(item => item.category),
                datasets: [{
                    data: props.charts.expenses.map(item => item.amount),
                    backgroundColor: [
                        'rgb(239, 68, 68)',
                        'rgb(245, 158, 11)',
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(139, 92, 246)',
                        'rgb(236, 72, 153)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        })
    }
})
</script>
