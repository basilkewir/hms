<template>
    <DashboardLayout title="Sale Details" :user="user" :navigation="navigation">

        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <Link :href="route('pos.sales.index')"
                          class="inline-flex items-center gap-1 text-sm font-medium mb-2 transition-colors"
                          :style="{ color: themeColors.primary }">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Sales
                    </Link>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">
                        Sale Details
                    </h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        {{ sale.sale_number }} &mdash; {{ formatDateTime(sale.sale_date || sale.created_at) }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="printSale"
                            class="px-4 py-2 rounded-md font-medium text-white flex items-center gap-2 transition-colors"
                            style="background-color:#8b5cf6;"
                            @mouseenter="$event.currentTarget.style.backgroundColor='#7c3aed'"
                            @mouseleave="$event.currentTarget.style.backgroundColor='#8b5cf6'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Print Receipt
                    </button>
                </div>
            </div>
        </div>

        <!-- Top cards row: Sale Info + Customer Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

            <!-- Sale Information -->
            <div class="rounded-lg border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                    <h2 class="text-base font-semibold" :style="{ color: themeColors.textPrimary }">Sale Information</h2>
                </div>
                <div class="p-6 grid gap-4">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Sale Number</span>
                        <span class="text-lg font-bold" :style="{ color: themeColors.primary }">{{ sale.sale_number }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Date &amp; Time</span>
                        <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ formatDateTime(sale.sale_date || sale.created_at) }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Cashier / Staff</span>
                        <span class="text-sm" :style="{ color: themeColors.textPrimary }">
                            {{ sale.user ? (sale.user.first_name + ' ' + sale.user.last_name) : (sale.user?.name || 'N/A') }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Payment Method</span>
                        <span class="inline-flex w-fit px-2.5 py-0.5 rounded-full text-xs font-semibold"
                              style="background-color:#000000; color:#ffffff;">
                            {{ formatPaymentMethod(sale.payment_method) }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Payment Status</span>
                        <span class="inline-flex w-fit px-2.5 py-0.5 rounded-full text-xs font-semibold"
                              style="background-color:#000000; color:#ffffff;">
                            {{ sale.payment_status || 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="rounded-lg border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                    <h2 class="text-base font-semibold" :style="{ color: themeColors.textPrimary }">Customer Information</h2>
                </div>
                <div class="p-6 grid gap-4">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Customer Type</span>
                        <span v-if="sale.is_walk_in"
                              class="inline-flex w-fit px-2.5 py-0.5 rounded-full text-xs font-semibold"
                              style="background-color:#000000; color:#ffffff;">Walk-In</span>
                        <span v-else class="text-sm" :style="{ color: themeColors.textPrimary }">Registered Customer</span>
                    </div>
                    <div v-if="sale.customer" class="flex flex-col gap-1">
                        <span class="text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Name</span>
                        <span class="text-sm" :style="{ color: themeColors.textPrimary }">
                            {{ sale.customer.first_name }} {{ sale.customer.last_name }}
                        </span>
                    </div>
                    <div v-else-if="sale.customer_name" class="flex flex-col gap-1">
                        <span class="text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Name</span>
                        <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ sale.customer_name }}</span>
                    </div>
                    <div v-if="sale.customer_phone" class="flex flex-col gap-1">
                        <span class="text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Phone</span>
                        <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ sale.customer_phone }}</span>
                    </div>
                    <div v-if="sale.customer?.customer_code" class="flex flex-col gap-1">
                        <span class="text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Customer Code</span>
                        <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ sale.customer.customer_code }}</span>
                    </div>
                    <div v-if="!sale.customer && !sale.customer_name && !sale.is_walk_in"
                         class="text-sm" :style="{ color: themeColors.textTertiary }">No customer information</div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="rounded-lg border shadow-sm mb-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h2 class="text-base font-semibold" :style="{ color: themeColors.textPrimary }">
                    Items
                    <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-semibold"
                          :style="{ backgroundColor: themeColors.primary + '22', color: themeColors.primary }">
                        {{ sale.items?.length || 0 }}
                    </span>
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide"
                                :style="{ color: themeColors.textSecondary }">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide"
                                :style="{ color: themeColors.textSecondary }">Category</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide"
                                :style="{ color: themeColors.textSecondary }">Qty</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide"
                                :style="{ color: themeColors.textSecondary }">Unit Price</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide"
                                :style="{ color: themeColors.textSecondary }">Unit Cost</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide"
                                :style="{ color: themeColors.textSecondary }">Total</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide"
                                :style="{ color: themeColors.textSecondary }">Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in sale.items" :key="item.id"
                            class="border-t hover:opacity-80 transition-opacity"
                            :style="{ borderColor: themeColors.border }">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="text-xl">{{ item.product?.emoji || '🍽️' }}</span>
                                    <span class="font-medium" :style="{ color: themeColors.textPrimary }">
                                        {{ item.product?.name || 'Unknown Product' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4" :style="{ color: themeColors.textSecondary }">
                                {{ item.product?.category?.name || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-right font-semibold" :style="{ color: themeColors.primary }">
                                {{ item.quantity }}×
                            </td>
                            <td class="px-6 py-4 text-right" :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(item.unit_price) }}
                            </td>
                            <td class="px-6 py-4 text-right" :style="{ color: themeColors.textSecondary }">
                                {{ formatCurrency(item.unit_cost || 0) }}
                            </td>
                            <td class="px-6 py-4 text-right font-semibold" :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(item.total_price) }}
                            </td>
                            <td class="px-6 py-4 text-right font-semibold"
                                :style="{ color: getItemProfit(item) >= 0 ? '#22c55e' : '#ef4444' }">
                                {{ formatCurrency(getItemProfit(item)) }}
                            </td>
                        </tr>
                        <tr v-if="!sale.items?.length">
                            <td colspan="7" class="px-6 py-8 text-center text-sm"
                                :style="{ color: themeColors.textTertiary }">No items found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Totals + Notes row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Totals -->
            <div class="rounded-lg border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                    <h2 class="text-base font-semibold" :style="{ color: themeColors.textPrimary }">Totals</h2>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex justify-between py-2 border-b" :style="{ borderColor: themeColors.border }">
                        <span class="text-sm" :style="{ color: themeColors.textSecondary }">Subtotal</span>
                        <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(sale.subtotal) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b" :style="{ borderColor: themeColors.border }">
                        <span class="text-sm" :style="{ color: themeColors.textSecondary }">Tax</span>
                        <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(sale.tax_amount) }}</span>
                    </div>
                    <div v-if="sale.discount_amount > 0" class="flex justify-between py-2 border-b" :style="{ borderColor: themeColors.border }">
                        <span class="text-sm" style="color:#22c55e;">Discount</span>
                        <span class="text-sm font-medium" style="color:#22c55e;">-{{ formatCurrency(sale.discount_amount) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b" :style="{ borderColor: themeColors.border }">
                        <span class="text-sm" :style="{ color: themeColors.textSecondary }">Total Cost</span>
                        <span class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">{{ formatCurrency(sale.total_cost || 0) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b" :style="{ borderColor: themeColors.border }">
                        <span class="text-sm" :style="{ color: themeColors.textSecondary }">Total Profit</span>
                        <span class="text-sm font-semibold"
                              :style="{ color: (sale.total_profit || 0) >= 0 ? '#22c55e' : '#ef4444' }">
                            {{ formatCurrency(sale.total_profit || 0) }}
                        </span>
                    </div>
                    <div class="flex justify-between py-2 border-b" :style="{ borderColor: themeColors.border }">
                        <span class="text-sm" :style="{ color: themeColors.textSecondary }">Profit Margin</span>
                        <span class="text-sm font-semibold"
                              :style="{ color: (sale.profit_margin || 0) >= 0 ? '#22c55e' : '#ef4444' }">
                            {{ (sale.profit_margin || 0).toFixed(1) }}%
                        </span>
                    </div>
                    <div class="flex justify-between pt-3">
                        <span class="text-base font-bold" :style="{ color: themeColors.textPrimary }">Total Amount</span>
                        <span class="text-xl font-bold" :style="{ color: themeColors.primary }">{{ formatCurrency(sale.total_amount) }}</span>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="rounded-lg border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                    <h2 class="text-base font-semibold" :style="{ color: themeColors.textPrimary }">Notes</h2>
                </div>
                <div class="p-6">
                    <p v-if="sale.notes" class="text-sm leading-relaxed whitespace-pre-wrap"
                       :style="{ color: themeColors.textSecondary }">{{ sale.notes }}</p>
                    <p v-else class="text-sm" :style="{ color: themeColors.textTertiary }">No notes for this sale.</p>
                </div>
            </div>
        </div>

    </DashboardLayout>

    <!-- Thermal Receipt — hidden on screen, shown only when printing -->
    <div id="thermal-receipt" aria-hidden="true">
        <Receipt
            :sale="sale"
            :hotelName="hotelName"
            :hotelAddress="hotelAddress"
            :hotelPhone="hotelPhone"
            :hotelEmail="hotelEmail"
            :hotelLogo="hotelLogo"
            :hotelWebsite="hotelWebsite"
        />
    </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency, initializeCurrencySettings } from '@/Utils/currency.js'
import Receipt from '@/Components/Receipt.vue'
import { printPopup } from '@/Utils/printReceipt.js'

const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background:    `var(--kotel-background)`,
    card:          `var(--kotel-card)`,
    border:        `var(--kotel-border)`,
    textPrimary:   `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary:  `var(--kotel-text-tertiary)`,
    primary:       `var(--kotel-primary)`,
    success:       `var(--kotel-success)`,
    danger:        `var(--kotel-danger)`,
    warning:       `var(--kotel-warning)`,
}))
loadTheme()

const props = defineProps({
    user:       Object,
    navigation: Array,
    sale:       Object,
})

const formatDateTime = (dateString) => {
    if (!dateString) return '—'
    return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })
}

const formatPaymentMethod = (method) => {
    const map = { cash: 'Cash', card: 'Card', bank_transfer: 'Bank Transfer', mobile: 'Mobile Payment' }
    return map[method] || method || 'N/A'
}

const page = usePage()

const hotelName    = computed(() => page.props?.branding?.hotel_name    || page.props?.hotelSettings?.name    || 'Hotel & Restaurant')
const hotelAddress = computed(() => page.props?.branding?.hotel_address || page.props?.hotelSettings?.address || '')
const hotelPhone   = computed(() => page.props?.branding?.hotel_phone   || page.props?.hotelSettings?.phone   || '')
const hotelEmail   = computed(() => page.props?.branding?.contact?.email || '')
const hotelLogo    = computed(() => page.props?.branding?.hotel_logo    || '')
const hotelWebsite = computed(() => page.props?.branding?.hotel_website || '')

const getItemProfit = (item) => {
    const cost    = (item.unit_cost || 0) * item.quantity
    const revenue = item.total_price - (item.discount_amount || 0)
    return revenue - cost
}

const printSale = () => printPopup('thermal-receipt', `Receipt – ${props.sale?.sale_number || ''}`, '80mm')

onMounted(() => {
    initializeCurrencySettings()
    if (new URLSearchParams(window.location.search).get('print') === '1') {
        setTimeout(() => printSale(), 500)
    }
})
</script>

<style>
/* Hide thermal receipt on screen — it's only used by printPopup() */
#thermal-receipt { display: none; }
</style>
