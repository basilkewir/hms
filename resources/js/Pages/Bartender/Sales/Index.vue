<template>
    <DashboardLayout title="Bar Sales" :user="user" :navigation="navigation">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold"
                    :style="{ color: themeColors.textPrimary }">💰 Sales Reports</h1>
                <p class="text-sm mt-2"
                   :style="{ color: themeColors.textSecondary }">Track bar sales and revenue</p>
            </div>
        </div>

        <!-- Date Range Selector -->
        <div class="mb-8 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2"
                       :style="{ color: themeColors.textSecondary }">Start Date</label>
                <div class="relative">
                    <input type="date" v-model="startDate"
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
                    <input type="date" v-model="endDate"
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
                    <option value="check">Check</option>
                    <option value="transfer">Transfer</option>
                </select>
            </div>
            <div class="flex items-end">
                <button @click="applyFilters()"
                        class="w-full px-4 py-2 rounded-lg font-medium"
                        :style="{
                            backgroundColor: themeColors.primary,
                            color: '#fff'
                        }">
                    Apply Filters
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <h3 class="text-sm font-medium mb-2"
                    :style="{ color: themeColors.textSecondary }">Total Sales</h3>
                <p class="text-3xl font-bold"
                   :style="{ color: themeColors.textPrimary }">{{ filteredSales.length }}</p>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <h3 class="text-sm font-medium mb-2"
                    :style="{ color: themeColors.textSecondary }">Total Revenue</h3>
                <p class="text-3xl font-bold"
                   :style="{ color: themeColors.success }">{{ formatCurrency(totalRevenue) }}</p>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <h3 class="text-sm font-medium mb-2"
                    :style="{ color: themeColors.textSecondary }">Average Order</h3>
                <p class="text-3xl font-bold"
                   :style="{ color: themeColors.primary }">{{ formatCurrency(avgOrderValue) }}</p>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <h3 class="text-sm font-medium mb-2"
                    :style="{ color: themeColors.textSecondary }">Total Discounts</h3>
                <p class="text-3xl font-bold"
                   :style="{ color: themeColors.warning }">{{ formatCurrency(totalDiscounts) }}</p>
            </div>
        </div>

        <!-- Sales Table -->
        <div class="rounded-lg border shadow-sm overflow-hidden"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background, borderBottom: `1px solid ${themeColors.border}` }">
                            <th class="px-6 py-3 text-left text-xs font-medium"
                                :style="{ color: themeColors.textSecondary }">Sale #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium"
                                :style="{ color: themeColors.textSecondary }">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium"
                                :style="{ color: themeColors.textSecondary }">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium"
                                :style="{ color: themeColors.textSecondary }">Method</th>
                            <th class="px-6 py-3 text-right text-xs font-medium"
                                :style="{ color: themeColors.textSecondary }">Subtotal</th>
                            <th class="px-6 py-3 text-right text-xs font-medium"
                                :style="{ color: themeColors.textSecondary }">Tax</th>
                            <th class="px-6 py-3 text-right text-xs font-medium"
                                :style="{ color: themeColors.textSecondary }">Discount</th>
                            <th class="px-6 py-3 text-right text-xs font-medium"
                                :style="{ color: themeColors.textSecondary }">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y"
                           :style="{ borderColor: themeColors.border }">
                        <tr v-if="filteredSales.length === 0">
                            <td colspan="8" class="px-6 py-4 text-center"
                                :style="{ color: themeColors.textSecondary }">No sales found</td>
                        </tr>
                        <tr v-for="sale in filteredSales" :key="sale.id"
                            class="hover:opacity-75"
                            :style="{ backgroundColor: themeColors.card }">
                            <td class="px-6 py-4 font-bold"
                                :style="{ color: themeColors.primary }">{{ sale.sale_number }}</td>
                            <td class="px-6 py-4"
                                :style="{ color: themeColors.textPrimary }">{{ sale.customer_name }}</td>
                            <td class="px-6 py-4 text-sm"
                                :style="{ color: themeColors.textSecondary }">{{ formatDate(sale.sale_date) }}</td>
                            <td class="px-6 py-4 text-sm"
                                :style="{ color: themeColors.textSecondary }">{{ sale.payment_method }}</td>
                            <td class="px-6 py-4 text-right"
                                :style="{ color: themeColors.textSecondary }">{{ formatCurrency(sale.subtotal) }}</td>
                            <td class="px-6 py-4 text-right"
                                :style="{ color: themeColors.textSecondary }">{{ formatCurrency(sale.tax_amount) }}</td>
                            <td class="px-6 py-4 text-right"
                                :style="{ color: themeColors.warning }">{{ formatCurrency(sale.discount_amount) }}</td>
                            <td class="px-6 py-4 text-right font-bold"
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

const navigation = computed(() => getNavigationForRole('bartender'))
const startDate = ref('')
const endDate = ref('')
const selectedPaymentMethod = ref('')

const filteredSales = computed(() => {
    return props.sales?.filter(sale => {
        const saleDate = new Date(sale.sale_date)
        const start = startDate.value ? new Date(startDate.value) : null
        const end = endDate.value ? new Date(endDate.value) : null

        const matchesDate = (!start || saleDate >= start) && (!end || saleDate <= end)
        const matchesMethod = !selectedPaymentMethod.value || sale.payment_method === selectedPaymentMethod.value

        return matchesDate && matchesMethod
    }) || []
})

const totalRevenue = computed(() => {
    return filteredSales.value?.reduce((sum, s) => sum + (parseFloat(s.total_amount) || 0), 0) || 0
})

const totalDiscounts = computed(() => {
    return filteredSales.value?.reduce((sum, s) => sum + (parseFloat(s.discount_amount) || 0), 0) || 0
})

const avgOrderValue = computed(() => {
    return filteredSales.value?.length > 0 ? totalRevenue.value / filteredSales.value.length : 0
})

const applyFilters = () => {
    // Filters are automatically applied via computed property
}

const formatCurrency = (amount) => {
    const currency = props.currency || 'USD'
    const position = props.currencyPosition || 'prefix'

    // Ensure amount is a valid number
    const numAmount = parseFloat(amount) || 0

    if (!isFinite(numAmount)) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(0)
    }

    const formatted = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(numAmount)

    return formatted
}

const formatDate = (date) => {
    try {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        })
    } catch (e) {
        return 'N/A'
    }
}
</script>
