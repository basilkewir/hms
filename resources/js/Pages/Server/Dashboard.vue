<template>
    <DashboardLayout title="Server Dashboard" :user="user" :navigation="navigation">
        <!-- Welcome Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Welcome, {{ user.first_name }}!</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Manage restaurant orders and sales</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium"
                       :style="{ color: themeColors.textSecondary }">Current Shift</p>
                    <p class="text-2xl font-bold"
                       :style="{ color: themeColors.textPrimary }">{{ stats.shift_hours }}h</p>
                </div>
            </div>
        </div>

        <!-- Key Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Food Items Count -->
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
                        <span class="text-2xl">🍽️</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Menu Items</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.food_count }}</p>
                    </div>
                </div>
            </div>

            <!-- Drinks Available -->
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
                        <span class="text-2xl">🍷</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Beverages</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.drinks_count }}</p>
                    </div>
                </div>
            </div>

            <!-- Today's Sales -->
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
                        <span class="text-2xl">💳</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Today's Revenue</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ formatCurrency(stats.todays_revenue) }}</p>
                    </div>
                </div>
            </div>

            <!-- Low Stock Items -->
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(239, 68, 68, 0.1)' }">
                        <span class="text-2xl">⚠️</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Low Stock Items</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.low_stock_items }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <h3 class="text-sm font-medium mb-3"
                    :style="{ color: themeColors.textSecondary }">Today</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span :style="{ color: themeColors.textSecondary }">Orders:</span>
                        <span class="font-bold"
                              :style="{ color: themeColors.textPrimary }">{{ stats.todays_sales }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span :style="{ color: themeColors.textSecondary }">Revenue:</span>
                        <span class="font-bold"
                              :style="{ color: themeColors.success }">{{ formatCurrency(stats.todays_revenue) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span :style="{ color: themeColors.textSecondary }">Avg Order:</span>
                        <span class="font-bold"
                              :style="{ color: themeColors.textPrimary }">{{ formatCurrency(stats.avg_order_value) }}</span>
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
                <h3 class="text-sm font-medium mb-3"
                    :style="{ color: themeColors.textSecondary }">This Week</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span :style="{ color: themeColors.textSecondary }">Total Revenue:</span>
                        <span class="font-bold"
                              :style="{ color: themeColors.success }">{{ formatCurrency(stats.week_revenue) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span :style="{ color: themeColors.textSecondary }">Daily Average:</span>
                        <span class="font-bold"
                              :style="{ color: themeColors.textPrimary }">{{ formatCurrency(stats.week_revenue / 7) }}</span>
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
                <h3 class="text-sm font-medium mb-3"
                    :style="{ color: themeColors.textSecondary }">This Month</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span :style="{ color: themeColors.textSecondary }">Total Revenue:</span>
                        <span class="font-bold"
                              :style="{ color: themeColors.success }">{{ formatCurrency(stats.month_revenue) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span :style="{ color: themeColors.textSecondary }">Daily Average:</span>
                        <span class="font-bold"
                              :style="{ color: themeColors.textPrimary }">{{ formatCurrency(stats.month_revenue / 30) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Items Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Top Selling Items -->
            <div class="rounded-lg border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="p-6 border-b"
                     :style="{ borderColor: themeColors.border }">
                    <h3 class="text-lg font-bold"
                        :style="{ color: themeColors.textPrimary }">Top Selling Items</h3>
                </div>
                <div class="divide-y"
                     :style="{ borderColor: themeColors.border }">
                    <div v-if="topItems.length === 0" class="p-6 text-center"
                         :style="{ color: themeColors.textSecondary }">
                        No sales data available
                    </div>
                    <div v-for="item in topItems" :key="item.id"
                         class="p-4 flex items-center justify-between hover:opacity-75"
                         :style="{ backgroundColor: themeColors.background }">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">{{ item.emoji || '🍽️' }}</span>
                            <div>
                                <p class="font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ item.name }}</p>
                                <p class="text-sm"
                                   :style="{ color: themeColors.textSecondary }">{{ item.total_sold }} sold</p>
                            </div>
                        </div>
                        <span class="font-bold"
                              :style="{ color: themeColors.success }">{{ formatCurrency(item.total_revenue) }}</span>
                    </div>
                </div>
            </div>

            <!-- Sales by Category -->
            <div class="rounded-lg border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="p-6 border-b"
                     :style="{ borderColor: themeColors.border }">
                    <h3 class="text-lg font-bold"
                        :style="{ color: themeColors.textPrimary }">Sales by Category</h3>
                </div>
                <div class="divide-y"
                     :style="{ borderColor: themeColors.border }">
                    <div v-if="salesByCategory.length === 0" class="p-6 text-center"
                         :style="{ color: themeColors.textSecondary }">
                        No sales data available
                    </div>
                    <div v-for="category in salesByCategory" :key="category.category"
                         class="p-4 flex items-center justify-between hover:opacity-75"
                         :style="{ backgroundColor: themeColors.background }">
                        <div>
                            <p class="font-medium"
                               :style="{ color: themeColors.textPrimary }">{{ category.category }}</p>
                            <p class="text-sm"
                               :style="{ color: themeColors.textSecondary }">{{ category.quantity_sold }} items sold</p>
                        </div>
                        <span class="font-bold"
                              :style="{ color: themeColors.success }">{{ formatCurrency(category.revenue) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Sales Table -->
        <div class="rounded-lg border shadow-sm"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <div class="p-6 border-b"
                 :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-bold"
                    :style="{ color: themeColors.textPrimary }">Recent Orders</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background, borderBottom: `1px solid ${themeColors.border}` }">
                            <th class="px-6 py-3 text-left text-xs font-medium"
                                :style="{ color: themeColors.textSecondary }">Order #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium"
                                :style="{ color: themeColors.textSecondary }">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium"
                                :style="{ color: themeColors.textSecondary }">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium"
                                :style="{ color: themeColors.textSecondary }">Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium"
                                :style="{ color: themeColors.textSecondary }">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y"
                           :style="{ borderColor: themeColors.border }">
                        <tr v-if="recentSales.length === 0"
                            class="hover:opacity-75 cursor-pointer">
                            <td colspan="5" class="px-6 py-4 text-center"
                                :style="{ color: themeColors.textSecondary }">No orders yet</td>
                        </tr>
                        <tr v-for="sale in recentSales" :key="sale.id"
                            class="hover:opacity-75 cursor-pointer"
                            :style="{ backgroundColor: themeColors.card }">
                            <td class="px-6 py-4"
                                :style="{ color: themeColors.textPrimary }">{{ sale.sale_number }}</td>
                            <td class="px-6 py-4"
                                :style="{ color: themeColors.textPrimary }">{{ sale.customer_name }}</td>
                            <td class="px-6 py-4 font-bold"
                                :style="{ color: themeColors.success }">{{ formatCurrency(sale.amount) }}</td>
                            <td class="px-6 py-4 text-sm"
                                :style="{ color: themeColors.textSecondary }">{{ sale.payment_method }}</td>
                            <td class="px-6 py-4 text-sm"
                                :style="{ color: themeColors.textSecondary }">{{ sale.date }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { getNavigationForRole } from '@/Utils/navigation.js'

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
}))

loadTheme()

const props = defineProps({
    user: Object,
    stats: Object,
    recentSales: Array,
    topItems: Array,
    salesByCategory: Array,
    revenueTrend: Array,
    currency: String,
    currencyPosition: String,
})

const navigation = computed(() => getNavigationForRole('server'))

const formatCurrency = (amount) => {
    const currency = props.currency || 'USD'
    const numAmount = parseFloat(amount) || 0
    if (!isFinite(numAmount)) return new Intl.NumberFormat('en-US', { style: 'currency', currency, minimumFractionDigits: 2 }).format(0)
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(numAmount)
}
</script>
