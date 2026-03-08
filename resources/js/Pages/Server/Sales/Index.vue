<template>
    <DashboardLayout title="Sales Reports" :user="user" :navigation="navigation">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold"
                    :style="{ color: themeColors.textPrimary }">📊 Sales Reports</h1>
                <p class="text-sm mt-2"
                   :style="{ color: themeColors.textSecondary }">Track all restaurant sales and orders</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-4 py-2 rounded-lg font-bold"
                      :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)', color: themeColors.primary }">
                    {{ filteredSales.length }} Orders
                </span>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg p-6 border"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <p class="text-sm font-medium mb-2"
                   :style="{ color: themeColors.textSecondary }">Total Sales</p>
                <p class="text-3xl font-bold mb-2"
                   :style="{ color: themeColors.primary }">{{ formatCurrency(summaryStats.totalSales) }}</p>
                <p class="text-xs"
                   :style="{ color: themeColors.textTertiary }">{{ filteredSales.length }} transactions</p>
            </div>

            <div class="rounded-lg p-6 border"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <p class="text-sm font-medium mb-2"
                   :style="{ color: themeColors.textSecondary }">Total Revenue</p>
                <p class="text-3xl font-bold mb-2"
                   :style="{ color: themeColors.success }">{{ formatCurrency(summaryStats.totalRevenue) }}</p>
                <p class="text-xs"
                   :style="{ color: themeColors.textTertiary }">After discounts</p>
            </div>

            <div class="rounded-lg p-6 border"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <p class="text-sm font-medium mb-2"
                   :style="{ color: themeColors.textSecondary }">Average Order Value</p>
                <p class="text-3xl font-bold mb-2"
                   :style="{ color: themeColors.warning }">{{ formatCurrency(summaryStats.avgOrderValue) }}</p>
                <p class="text-xs"
                   :style="{ color: themeColors.textTertiary }">Per transaction</p>
            </div>

            <div class="rounded-lg p-6 border"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <p class="text-sm font-medium mb-2"
                   :style="{ color: themeColors.textSecondary }">Total Tax</p>
                <p class="text-3xl font-bold mb-2"
                   :style="{ color: themeColors.danger }">{{ formatCurrency(summaryStats.totalTax) }}</p>
                <p class="text-xs"
                   :style="{ color: themeColors.textTertiary }">Tax collected</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-8 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2"
                       :style="{ color: themeColors.textSecondary }">Start Date</label>
                <div class="relative">
                    <input type="date" v-model="filterStartDate"
                           class="w-full px-4 py-2 rounded-lg border cursor-pointer appearance-none"
                           :style="{
                               backgroundColor: themeColors.card,
                               borderColor: themeColors.border,
                               color: themeColors.textPrimary,
                               colorScheme: 'auto'
                           }"
                           @click="$event.target.showPicker ? $event.target.showPicker() : null" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2"
                       :style="{ color: themeColors.textSecondary }">End Date</label>
                <div class="relative">
                    <input type="date" v-model="filterEndDate"
                           class="w-full px-4 py-2 rounded-lg border cursor-pointer appearance-none"
                           :style="{
                               backgroundColor: themeColors.card,
                               borderColor: themeColors.border,
                               color: themeColors.textPrimary,
                               colorScheme: 'auto'
                           }"
                           @click="$event.target.showPicker ? $event.target.showPicker() : null" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2"
                       :style="{ color: themeColors.textSecondary }">Payment Method</label>
                <select v-model="selectedPaymentMethod"
                        class="w-full px-4 py-2 rounded-lg border"
                        :style="{
                            backgroundColor: themeColors.card,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary
                        }">
                    <option value="">All Methods</option>
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                    <option value="transfer">Transfer</option>
                    <option value="check">Check</option>
                </select>
            </div>
            <div class="flex items-end">
                <button @click="resetFilters()"
                        class="w-full px-4 py-2 rounded-lg font-medium"
                        :style="{
                            backgroundColor: themeColors.secondary,
                            color: '#fff'
                        }">
                    Reset Filters
                </button>
            </div>
        </div>

        <!-- Sales Table -->
        <div class="rounded-lg border overflow-hidden"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div v-if="filteredSales.length === 0" class="p-12 text-center">
                <p class="text-lg font-medium"
                   :style="{ color: themeColors.textPrimary }">No sales found</p>
                <p class="text-sm mt-2"
                   :style="{ color: themeColors.textSecondary }">Try adjusting your filters</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr :style="{ borderBottom: `1px solid ${themeColors.border}`, backgroundColor: 'rgba(0,0,0,0.02)' }">
                            <th class="px-6 py-4 text-left text-sm font-semibold"
                                :style="{ color: themeColors.textPrimary }">Order #</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold"
                                :style="{ color: themeColors.textPrimary }">Customer</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold"
                                :style="{ color: themeColors.textPrimary }">Date</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold"
                                :style="{ color: themeColors.textPrimary }">Method</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold"
                                :style="{ color: themeColors.textPrimary }">Subtotal</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold"
                                :style="{ color: themeColors.textPrimary }">Tax</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold"
                                :style="{ color: themeColors.textPrimary }">Discount</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold"
                                :style="{ color: themeColors.textPrimary }">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="sale in filteredSales" :key="sale.id"
                            :style="{
                                borderBottom: `1px solid ${themeColors.border}`,
                                backgroundColor: 'transparent'
                            }"
                            class="hover:opacity-75 transition">
                            <td class="px-6 py-4 text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">{{ sale.sale_number }}</td>
                            <td class="px-6 py-4 text-sm"
                                :style="{ color: themeColors.textSecondary }">{{ sale.customer_name || 'Walk-in' }}</td>
                            <td class="px-6 py-4 text-sm"
                                :style="{ color: themeColors.textSecondary }">{{ formatDate(sale.sale_date) }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2 py-1 rounded text-xs font-medium"
                                      :style="{
                                          backgroundColor: getPaymentMethodColor(sale.payment_method),
                                          color: '#fff'
                                      }">
                                    {{ sale.payment_method }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-right"
                                :style="{ color: themeColors.textSecondary }">{{ formatCurrency(sale.subtotal) }}</td>
                            <td class="px-6 py-4 text-sm text-right"
                                :style="{ color: themeColors.danger }">{{ formatCurrency(sale.tax_amount) }}</td>
                            <td class="px-6 py-4 text-sm text-right"
                                :style="{ color: themeColors.warning }">{{ formatCurrency(sale.discount_amount) }}</td>
                            <td class="px-6 py-4 text-sm text-right font-bold"
                                :style="{ color: themeColors.success }">{{ formatCurrency(sale.total_amount) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
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
    sales: Array,
    currency: String,
    currencyPosition: String,
})

const navigation = computed(() => getNavigationForRole('server'))
const filterStartDate = ref('')
const filterEndDate = ref('')
const selectedPaymentMethod = ref('')

const formatCurrency = (amount) => {
    const currency = props.currency || 'USD'
    const position = props.currencyPosition || 'prefix'

    // Ensure amount is a valid number
    const numAmount = parseFloat(amount) || 0

    // Check if the number is valid
    if (!isFinite(numAmount)) {
        return props.currency === 'XAF' ? 'FCFA 0.00' : `${currency} 0.00`
    }

    const formatted = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(numAmount)

    return formatted
}

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }
    return new Date(dateString).toLocaleDateString('en-US', options)
}

const getPaymentMethodColor = (method) => {
    const colors = {
        'cash': '#10b981',
        'card': '#3b82f6',
        'transfer': '#8b5cf6',
        'check': '#f59e0b',
    }
    return colors[method.toLowerCase()] || '#6b7280'
}

const filteredSales = computed(() => {
    return props.sales?.filter(sale => {
        const saleDate = new Date(sale.sale_date)
        const startDate = filterStartDate.value ? new Date(filterStartDate.value) : null
        const endDate = filterEndDate.value ? new Date(filterEndDate.value) : null

        const matchesDate = (!startDate || saleDate >= startDate) && (!endDate || saleDate <= endDate)
        const matchesPayment = !selectedPaymentMethod.value || sale.payment_method.toLowerCase() === selectedPaymentMethod.value

        return matchesDate && matchesPayment
    }) || []
})

const summaryStats = computed(() => {
    const sales = filteredSales.value || []
    return {
        totalSales: sales.reduce((sum, s) => sum + (parseFloat(s.subtotal) || 0), 0),
        totalRevenue: sales.reduce((sum, s) => sum + (parseFloat(s.total_amount) || 0), 0),
        avgOrderValue: sales.length > 0 ? sales.reduce((sum, s) => sum + (parseFloat(s.total_amount) || 0), 0) / sales.length : 0,
        totalTax: sales.reduce((sum, s) => sum + (parseFloat(s.tax_amount) || 0), 0),
    }
})

const resetFilters = () => {
    filterStartDate.value = ''
    filterEndDate.value = ''
    selectedPaymentMethod.value = ''
}
</script>
