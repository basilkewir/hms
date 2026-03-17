<template>
    <DashboardLayout title="Reports & Analytics" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold" :style="{ color: themeColors.textPrimary }">Reports & Analytics Dashboard</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Comprehensive financial, operational, and performance analytics for your hotel.</p>
                </div>
            </div>
        </div>

        <!-- Primary KPI Cards - 6 Columns -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
            <!-- Total Revenue -->
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Total Revenue</p>
                    <span class="text-lg" role="img" aria-label="money">💰</span>
                </div>
                <p class="text-2xl font-bold" :style="{ color: themeColors.success }">{{ formatCurrency(kpis.total_revenue) }}</p>
                <p class="text-xs mt-2" :style="{ color: themeColors.textTertiary }">All time</p>
            </div>

            <!-- Total Expenses -->
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Total Expenses</p>
                    <span class="text-lg" role="img" aria-label="expenses">📊</span>
                </div>
                <p class="text-2xl font-bold" :style="{ color: themeColors.danger }">{{ formatCurrency(kpis.total_expenses) }}</p>
                <p class="text-xs mt-2" :style="{ color: themeColors.textTertiary }">All time</p>
            </div>

            <!-- Net Profit -->
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Net Profit</p>
                    <span class="text-lg" role="img" aria-label="profit">📈</span>
                </div>
                <p class="text-2xl font-bold" :style="{ color: kpis.net_profit >= 0 ? themeColors.success : themeColors.danger }">{{ formatCurrency(kpis.net_profit) }}</p>
                <p class="text-xs mt-2" :style="{ color: themeColors.textTertiary }">{{ kpis.profit_margin }}% margin</p>
            </div>

            <!-- Outstanding Invoices -->
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Outstanding</p>
                    <span class="text-lg" role="img" aria-label="invoices">📑</span>
                </div>
                <p class="text-2xl font-bold" :style="{ color: themeColors.warning }">{{ formatCurrency(kpis.outstanding_invoices) }}</p>
                <p class="text-xs mt-2" :style="{ color: themeColors.textTertiary }">{{ kpis.unpaid_count }} invoices</p>
            </div>

            <!-- Total Customers -->
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Total Customers</p>
                    <span class="text-lg" role="img" aria-label="customers">👥</span>
                </div>
                <p class="text-2xl font-bold" :style="{ color: themeColors.primary }">{{ kpis.total_customers }}</p>
                <p class="text-xs mt-2" :style="{ color: themeColors.textTertiary }">{{ kpis.new_customers }} new</p>
            </div>

            <!-- Total Suppliers -->
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Supplier Debt</p>
                    <span class="text-lg" role="img" aria-label="suppliers">🏭</span>
                </div>
                <p class="text-2xl font-bold" :style="{ color: themeColors.warning }">{{ formatCurrency(kpis.total_payables) }}</p>
                <p class="text-xs mt-2" :style="{ color: themeColors.textTertiary }">{{ kpis.total_suppliers }} suppliers</p>
            </div>
        </div>

        <!-- Secondary Stats Grid - 2 Rows -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Sales This Month -->
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Sales This Month</p>
                <p class="text-2xl font-bold" :style="{ color: themeColors.success }">{{ formatCurrency(kpis.monthly_revenue) }}</p>
                <p class="text-xs mt-3 flex items-center" :style="{ color: themeColors.success }">📈 {{ kpis.revenue_growth }}% vs last month</p>
            </div>

            <!-- Expenses This Month -->
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Expenses This Month</p>
                <p class="text-2xl font-bold" :style="{ color: themeColors.danger }">{{ formatCurrency(kpis.monthly_expenses) }}</p>
                <p class="text-xs mt-3" :style="{ color: themeColors.textTertiary }">{{ kpis.monthly_expense_count }} transactions</p>
            </div>

            <!-- Occupancy Rate -->
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Occupancy Rate</p>
                <p class="text-2xl font-bold" :style="{ color: themeColors.primary }">{{ kpis.occupancy_rate }}%</p>
                <p class="text-xs mt-3" :style="{ color: themeColors.textTertiary }">{{ kpis.rooms_occupied }}/{{ kpis.total_rooms }} rooms</p>
            </div>

            <!-- Average Daily Rate -->
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Avg. Daily Rate</p>
                <p class="text-2xl font-bold" :style="{ color: themeColors.secondary }">{{ formatCurrency(kpis.average_daily_rate) }}</p>
                <p class="text-xs mt-3" :style="{ color: themeColors.textTertiary }">Per room per night</p>
            </div>
        </div>

        <!-- Middle Section: Sales & Expenses Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Recent Sales -->
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Recent Sales</h3>
                <div v-if="recentSales && recentSales.length" class="space-y-4">
                    <div v-for="sale in recentSales" :key="sale.id" class="flex items-center justify-between pb-4 border-b"
                         :style="{ borderColor: themeColors.border }">
                        <div>
                            <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ sale.sale_number }}</p>
                            <p class="text-xs" :style="{ color: themeColors.textSecondary }">{{ sale.customer_name || 'Walk-in' }} · {{ formatDate(sale.sale_date) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold" :style="{ color: themeColors.success }">{{ formatCurrency(sale.total_amount) }}</p>
                            <span class="text-xs px-2 py-0.5 rounded-full capitalize" :style="{ backgroundColor: themeColors.success + '20', color: themeColors.success }">{{ sale.status }}</span>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No recent sales</p>
            </div>

            <!-- Recent Expenses -->
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Recent Expenses</h3>
                <div v-if="recentExpenses && recentExpenses.length" class="space-y-4">
                    <div v-for="expense in recentExpenses" :key="expense.id" class="flex items-center justify-between pb-4 border-b"
                         :style="{ borderColor: themeColors.border }">
                        <div>
                            <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ expense.category || 'Uncategorized' }}</p>
                            <p class="text-xs" :style="{ color: themeColors.textSecondary }">{{ expense.description || formatDate(expense.date) }}</p>
                        </div>
                        <p class="text-sm font-semibold" :style="{ color: themeColors.danger }">{{ formatCurrency(expense.amount) }}</p>
                    </div>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No recent expenses</p>
            </div>
        </div>

        <!-- Invoices & Suppliers Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Outstanding Invoices List -->
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Outstanding Invoices</h3>
                    <Link :href="route('admin.invoices.index')" class="text-sm" :style="{ color: themeColors.primary }">View All →</Link>
                </div>
                <div v-if="outstandingInvoices && outstandingInvoices.length" class="space-y-4">
                    <div v-for="invoice in outstandingInvoices" :key="invoice.id" class="flex items-center justify-between pb-4 border-b"
                         :style="{ borderColor: themeColors.border }">
                        <div>
                            <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ invoice.invoice_number }}</p>
                            <p class="text-xs" :style="{ color: themeColors.textSecondary }">Customer: {{ invoice.customer_name }}</p>
                            <p class="text-xs" :style="{ color: invoice.days_overdue > 30 ? themeColors.danger : themeColors.warning }">Due: {{ formatDate(invoice.due_date) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold" :style="{ color: themeColors.warning }">{{ formatCurrency(invoice.outstanding_amount) }}</p>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No outstanding invoices</p>
            </div>

            <!-- Supplier Balances -->
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Top Suppliers (Amount Due)</h3>
                    <Link :href="route('admin.reports.financial')" class="text-sm" :style="{ color: themeColors.primary }">View All →</Link>
                </div>
                <div v-if="topSuppliers && topSuppliers.length" class="space-y-4">
                    <div v-for="supplier in topSuppliers" :key="supplier.id" class="flex items-center justify-between pb-4 border-b"
                         :style="{ borderColor: themeColors.border }">
                        <div class="flex-1">
                            <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ supplier.name }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="w-full bg-gray-200 rounded-full h-2" :style="{ backgroundColor: themeColors.border }">
                                    <div class="h-2 rounded-full" 
                                         :style="{ 
                                             width: (supplier.payment_percentage || 0) + '%',
                                             backgroundColor: supplier.payment_percentage >= 75 ? themeColors.success : 
                                                            supplier.payment_percentage >= 50 ? themeColors.warning : themeColors.danger
                                         }"></div>
                                </div>
                            </div>
                        </div>
                        <div class="text-right ml-4">
                            <p class="text-sm font-semibold" :style="{ color: themeColors.danger }">{{ formatCurrency(supplier.balance_due) }}</p>
                            <p class="text-xs" :style="{ color: themeColors.textTertiary }">Paid: {{ supplier.payment_percentage }}%</p>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No suppliers with outstanding balances</p>
            </div>
        </div>

        <!-- Top Products & Customers Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Top Products -->
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Top Selling Products</h3>
                    <Link :href="route('admin.products.index')" class="text-sm" :style="{ color: themeColors.primary }">View All →</Link>
                </div>
                <div v-if="topProducts && topProducts.length" class="space-y-4">
                    <div v-for="(product, idx) in topProducts" :key="product.id" class="flex items-center justify-between pb-4 border-b"
                         :style="{ borderColor: themeColors.border }">
                        <div class="flex items-center gap-3">
                            <span class="text-xl font-bold" :style="{ color: themeColors.primary }">{{ idx + 1 }}</span>
                            <div>
                                <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ product.name }}</p>
                                <p class="text-xs" :style="{ color: themeColors.textSecondary }">{{ product.quantity_sold }} units</p>
                            </div>
                        </div>
                        <p class="text-sm font-semibold" :style="{ color: themeColors.success }">{{ formatCurrency(product.revenue) }}</p>
                    </div>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No product data</p>
            </div>

            <!-- Top Customers -->
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Top Customers by Spending</h3>
                    <Link :href="route('admin.customers.index')" class="text-sm" :style="{ color: themeColors.primary }">View All →</Link>
                </div>
                <div v-if="topCustomers && topCustomers.length" class="space-y-4">
                    <div v-for="(customer, idx) in topCustomers" :key="customer.id" class="flex items-center justify-between pb-4 border-b"
                         :style="{ borderColor: themeColors.border }">
                        <div class="flex items-center gap-3">
                            <span class="text-xl font-bold" :style="{ color: themeColors.primary }">{{ idx + 1 }}</span>
                            <div>
                                <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ customer.name }}</p>
                                <p class="text-xs" :style="{ color: themeColors.textSecondary }">{{ customer.order_count }} orders</p>
                            </div>
                        </div>
                        <p class="text-sm font-semibold" :style="{ color: themeColors.success }">{{ formatCurrency(customer.total_spent) }}</p>
                    </div>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No customer data</p>
            </div>
        </div>

        <!-- Detailed Report Links -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Detailed Reports</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <Link :href="route('admin.reports.revenue')" class="group rounded-lg shadow p-6 border hover:shadow-lg transition-all"
                   :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                   @mouseenter="$event.currentTarget.style.borderColor = themeColors.primary"
                   @mouseleave="$event.currentTarget.style.borderColor = themeColors.border">
                    <h4 class="text-sm font-semibold mb-2" :style="{ color: themeColors.primary }">📊 Revenue Report</h4>
                    <p class="text-xs" :style="{ color: themeColors.textSecondary }">Detailed revenue breakdown by period, customer, and service</p>
                </Link>
                <Link :href="route('admin.reports.occupancy')" class="group rounded-lg shadow p-6 border hover:shadow-lg transition-all"
                   :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                   @mouseenter="$event.currentTarget.style.borderColor = themeColors.primary"
                   @mouseleave="$event.currentTarget.style.borderColor = themeColors.border">
                    <h4 class="text-sm font-semibold mb-2" :style="{ color: themeColors.primary }">🏨 Occupancy Report</h4>
                    <p class="text-xs" :style="{ color: themeColors.textSecondary }">Room occupancy rates, utilization, and forecasting</p>
                </Link>
                <Link :href="route('admin.reports.financial')" class="group rounded-lg shadow p-6 border hover:shadow-lg transition-all"
                   :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                   @mouseenter="$event.currentTarget.style.borderColor = themeColors.primary"
                   @mouseleave="$event.currentTarget.style.borderColor = themeColors.border">
                    <h4 class="text-sm font-semibold mb-2" :style="{ color: themeColors.primary }">💰 Financial Report</h4>
                    <p class="text-xs" :style="{ color: themeColors.textSecondary }">Profit, margin analysis, and KPI summaries</p>
                </Link>
                <Link :href="route('admin.reports.inventory')" class="group rounded-lg shadow p-6 border hover:shadow-lg transition-all"
                   :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                   @mouseenter="$event.currentTarget.style.borderColor = themeColors.primary"
                   @mouseleave="$event.currentTarget.style.borderColor = themeColors.border">
                    <h4 class="text-sm font-semibold mb-2" :style="{ color: themeColors.primary }">📦 Inventory Report</h4>
                    <p class="text-xs" :style="{ color: themeColors.textSecondary }">Stock levels, movements, and valuations</p>
                </Link>
                <Link :href="route('admin.reports.staff')" class="group rounded-lg shadow p-6 border hover:shadow-lg transition-all"
                   :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                   @mouseenter="$event.currentTarget.style.borderColor = themeColors.primary"
                   @mouseleave="$event.currentTarget.style.borderColor = themeColors.border">
                    <h4 class="text-sm font-semibold mb-2" :style="{ color: themeColors.primary }">👥 Staff Report</h4>
                    <p class="text-xs" :style="{ color: themeColors.textSecondary }">Performance, scheduling, and payroll insights</p>
                </Link>
                <Link :href="route('admin.reports.guests')" class="group rounded-lg shadow p-6 border hover:shadow-lg transition-all"
                   :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                   @mouseenter="$event.currentTarget.style.borderColor = themeColors.primary"
                   @mouseleave="$event.currentTarget.style.borderColor = themeColors.border">
                    <h4 class="text-sm font-semibold mb-2" :style="{ color: themeColors.primary }">🧑‍🤝‍🧑 Guest Report</h4>
                    <p class="text-xs" :style="{ color: themeColors.textSecondary }">Guest demographics, satisfaction, and retention</p>
                </Link>
                <Link :href="route('admin.reports.maintenance')" class="group rounded-lg shadow p-6 border hover:shadow-lg transition-all"
                   :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                   @mouseenter="$event.currentTarget.style.borderColor = themeColors.primary"
                   @mouseleave="$event.currentTarget.style.borderColor = themeColors.border">
                    <h4 class="text-sm font-semibold mb-2" :style="{ color: themeColors.primary }">🔧 Maintenance Report</h4>
                    <p class="text-xs" :style="{ color: themeColors.textSecondary }">Work orders, completion rates, and costs</p>
                </Link>
                <Link :href="route('admin.reports.financial')" class="group rounded-lg shadow p-6 border hover:shadow-lg transition-all"
                   :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                   @mouseenter="$event.currentTarget.style.borderColor = themeColors.primary"
                   @mouseleave="$event.currentTarget.style.borderColor = themeColors.border">
                    <h4 class="text-sm font-semibold mb-2" :style="{ color: themeColors.primary }">💸 Expense Report</h4>
                    <p class="text-xs" :style="{ color: themeColors.textSecondary }">Detailed expense tracking by category and period</p>
                </Link>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <h3 class="text-lg font-semibold mb-6" :style="{ color: themeColors.textPrimary }">Summary Statistics</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Average Transaction Value</p>
                    <p class="text-xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(kpis.avg_transaction_value) }}</p>
                </div>
                <div>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Total Transactions</p>
                    <p class="text-xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ kpis.total_transactions }}</p>
                </div>
                <div>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Return Rate</p>
                    <p class="text-xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ kpis.return_rate }}%</p>
                </div>
                <div>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Customer Retention</p>
                    <p class="text-xl font-bold mt-1" :style="{ color: themeColors.success }">{{ kpis.retention_rate }}%</p>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'
import { formatCurrency as moneyFormat } from '@/Utils/currency.js'

const props = defineProps({
    user: Object,
    kpis: { 
        type: Object, 
        default: () => ({ 
            total_revenue: 0,
            total_expenses: 0,
            net_profit: 0,
            profit_margin: 0,
            outstanding_invoices: 0,
            unpaid_count: 0,
            total_customers: 0,
            new_customers: 0,
            total_suppliers: 0,
            total_payables: 0,
            monthly_revenue: 0,
            revenue_growth: 0,
            monthly_expenses: 0,
            monthly_expense_count: 0,
            occupancy_rate: 0,
            rooms_occupied: 0,
            total_rooms: 0,
            average_daily_rate: 0,
            avg_transaction_value: 0,
            total_transactions: 0,
            return_rate: 0,
            retention_rate: 0
        }) 
    },
    recentSales: { type: Array, default: () => [] },
    recentExpenses: { type: Array, default: () => [] },
    outstandingInvoices: { type: Array, default: () => [] },
    topSuppliers: { type: Array, default: () => [] },
    topProducts: { type: Array, default: () => [] },
    topCustomers: { type: Array, default: () => [] },
})

const navigation = computed(() => {
    const role = props.user?.roles?.[0]?.name || 'admin'
    return getNavigationForRole(role)
})

const { currentTheme, loadTheme } = useTheme()
loadTheme()
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
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.02)'
}))

const formatCurrency = (v) => moneyFormat(v || 0)
const formatDate = (d) => {
    if (!d) return ''
    const dt = new Date(d)
    return isNaN(dt.getTime()) ? '' : dt.toLocaleDateString()
}
</script>
