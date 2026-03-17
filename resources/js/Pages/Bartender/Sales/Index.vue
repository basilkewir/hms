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
                            <th class="px-6 py-3 text-center text-xs font-medium"
                                :style="{ color: themeColors.textSecondary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y"
                           :style="{ borderColor: themeColors.border }">
                        <tr v-if="filteredSales.length === 0">
                            <td colspan="9" class="px-6 py-4 text-center"
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
                            <td class="px-6 py-4 text-center">
                                <button @click="viewSale(sale)"
                                        class="inline-flex items-center px-3 py-1.5 rounded text-xs font-medium mr-1"
                                        :style="{ backgroundColor: 'rgba(99,102,241,0.1)', color: themeColors.primary }">
                                    👁 View
                                </button>
                                <button @click="printReceipt(sale)"
                                        class="inline-flex items-center px-3 py-1.5 rounded text-xs font-medium"
                                        :style="{ backgroundColor: 'rgba(16,185,129,0.1)', color: themeColors.success }">
                                    🖨 Print
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>

    <!-- Sale Detail Modal -->
    <div v-if="selectedSale" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="selectedSale = null">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">Sale Details — {{ selectedSale.sale_number }}</h2>
                <button @click="selectedSale = null" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="p-6 overflow-y-auto space-y-4">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500 block text-xs">Customer</span>
                        <span class="font-medium text-gray-900">{{ selectedSale.customer_name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs">Date</span>
                        <span class="font-medium text-gray-900">{{ formatDate(selectedSale.sale_date) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs">Payment Method</span>
                        <span class="font-medium text-gray-900">{{ selectedSale.payment_method }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs">Status</span>
                        <span class="font-medium text-green-600">{{ selectedSale.payment_status }}</span>
                    </div>
                </div>
                <!-- Items -->
                <div v-if="selectedSale.items && selectedSale.items.length">
                    <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wider">Items Ordered</p>
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Item</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">Qty</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Unit Price</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="(item, idx) in selectedSale.items" :key="idx">
                                    <td class="px-4 py-2 text-gray-900">{{ item.name }}</td>
                                    <td class="px-4 py-2 text-center text-gray-600">{{ item.quantity }}</td>
                                    <td class="px-4 py-2 text-right text-gray-600">{{ formatCurrency(item.unit_price) }}</td>
                                    <td class="px-4 py-2 text-right font-medium text-gray-900">{{ formatCurrency(item.total) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Totals -->
                <div class="border-t border-gray-200 pt-4 space-y-1 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span><span>{{ formatCurrency(selectedSale.subtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Tax</span><span>{{ formatCurrency(selectedSale.tax_amount) }}</span>
                    </div>
                    <div v-if="parseFloat(selectedSale.discount_amount) > 0" class="flex justify-between text-orange-600">
                        <span>Discount</span><span>-{{ formatCurrency(selectedSale.discount_amount) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-base text-gray-900 pt-1 border-t border-gray-200">
                        <span>Total</span><span class="text-green-600">{{ formatCurrency(selectedSale.total_amount) }}</span>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 p-6 border-t border-gray-200">
                <button @click="printReceipt(selectedSale)"
                        class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors">
                    🖨 Print Receipt
                </button>
                <button @click="selectedSale = null"
                        class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Close
                </button>
            </div>
        </div>
    </div>
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
const selectedSale = ref(null)

const viewSale = (sale) => {
    selectedSale.value = sale
}

const printReceipt = (sale) => {
    const items = (sale.items || []).map(item =>
        `<tr>
            <td style="padding:4px 8px;border-bottom:1px solid #eee;">${item.name}</td>
            <td style="padding:4px 8px;border-bottom:1px solid #eee;text-align:center;">${item.quantity}</td>
            <td style="padding:4px 8px;border-bottom:1px solid #eee;text-align:right;">${formatCurrency(item.unit_price)}</td>
            <td style="padding:4px 8px;border-bottom:1px solid #eee;text-align:right;">${formatCurrency(item.total)}</td>
        </tr>`
    ).join('')

    const html = `<!DOCTYPE html><html><head><title>Receipt - ${sale.sale_number}</title>
    <style>body{font-family:Arial,sans-serif;font-size:13px;margin:20px;}h2{text-align:center;}table{width:100%;border-collapse:collapse;}th{background:#f3f4f6;padding:6px 8px;text-align:left;font-size:11px;}td{font-size:12px;}.totals{margin-top:12px;text-align:right;}.total-line{display:flex;justify-content:space-between;padding:2px 0;}.grand{font-weight:bold;font-size:14px;border-top:1px solid #ccc;margin-top:4px;padding-top:4px;}</style>
    </head><body>
    <h2>Sales Receipt</h2>
    <p style="text-align:center;margin:2px 0;"><strong>${sale.sale_number}</strong></p>
    <p style="text-align:center;margin:2px 0;color:#555;font-size:12px;">${formatDate(sale.sale_date)}</p>
    <p style="margin:8px 0;"><strong>Customer:</strong> ${sale.customer_name}</p>
    <p style="margin:4px 0;"><strong>Payment Method:</strong> ${sale.payment_method}</p>
    <hr style="margin:12px 0;"/>
    ${items.length ? `<table><thead><tr><th>Item</th><th style="text-align:center;">Qty</th><th style="text-align:right;">Unit</th><th style="text-align:right;">Total</th></tr></thead><tbody>${items}</tbody></table>` : ''}
    <div class="totals">
        <div class="total-line"><span>Subtotal:</span><span>${formatCurrency(sale.subtotal)}</span></div>
        <div class="total-line"><span>Tax:</span><span>${formatCurrency(sale.tax_amount)}</span></div>
        ${parseFloat(sale.discount_amount) > 0 ? `<div class="total-line"><span>Discount:</span><span>-${formatCurrency(sale.discount_amount)}</span></div>` : ''}
        <div class="total-line grand"><span>TOTAL:</span><span>${formatCurrency(sale.total_amount)}</span></div>
    </div>
    <p style="text-align:center;margin-top:24px;font-size:11px;color:#999;">Thank you!</p>
    </body></html>`

    const w = window.open('', '_blank', 'width=400,height=600')
    w.document.write(html)
    w.document.close()
    w.focus()
    w.print()
}

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
