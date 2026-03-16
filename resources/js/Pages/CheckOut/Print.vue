<template>
    <DashboardLayout :title="'Print Bill – ' + (bill?.reservation_number || '')" :user="user" :navigation="navigation">
        <div class="max-w-4xl mx-auto">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6 no-print">
                <Link :href="checkoutRoute"
                      class="inline-flex items-center px-4 py-2 rounded-md transition-colors font-medium"
                      :style="{ 
                          backgroundColor: themeColors.secondary,
                          color: themeColors.textPrimary,
                          borderColor: themeColors.border,
                          borderWidth: '1px',
                          borderStyle: 'solid'
                      }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                    ← Back to Checkout
                </Link>
                <button @click="printBill"
                        class="inline-flex items-center px-6 py-2 rounded-md transition-colors font-medium text-white"
                        :style="{ 
                            backgroundColor: themeColors.primary,
                        }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                    <PrinterIcon class="h-5 w-5 mr-2" />
                    Print Professional Receipt
                </button>
            </div>

            <div id="checkout-bill-print" class="rounded-lg shadow-xl p-8 print:p-6 print:shadow-none" :style="{ backgroundColor: '#ffffff', color: '#000000' }">
                <!-- Header -->
                <div class="text-center border-b pb-4 mb-6" :style="{ borderColor: '#d1d5db' }">
                    <img v-if="hotelLogo" :src="hotelLogo" alt="Hotel Logo" class="h-16 max-w-xs object-contain mx-auto mb-3">
                    <h1 class="text-2xl font-bold mb-1">{{ hotelName }}</h1>
                    <div class="text-xs leading-5" :style="{ color: '#4b5563' }">
                        <div v-if="hotelAddress">{{ hotelAddress }}</div>
                        <div v-if="hotelPhone">{{ hotelPhone }}</div>
                        <div v-if="hotelEmail">{{ hotelEmail }}</div>
                        <div v-if="hotelWebsite">{{ hotelWebsite }}</div>
                    </div>
                    <div class="mt-3 font-semibold">Guest Checkout Bill</div>
                </div>

                <!-- Bill Details Header -->
                <div class="grid grid-cols-2 gap-4 mb-6 text-sm rounded-lg p-4" :style="{ backgroundColor: '#f9fafb' }">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-700">Reservation #:</span>
                            <span class="font-mono">{{ bill?.reservation_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-700">Folio #:</span>
                            <span class="font-mono">{{ bill?.folio_number || '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-700">Date:</span>
                            <span>{{ formatDate(new Date()) }}</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-700">Guest:</span>
                            <span class="font-medium">{{ bill?.guest_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-700">Room:</span>
                            <span class="font-medium">{{ bill?.room_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-700">Nights:</span>
                            <span class="font-medium">{{ bill?.nights }}</span>
                        </div>
                    </div>
                </div>

                <!-- Stay Details -->
                <div class="grid grid-cols-2 gap-4 mb-6 text-sm rounded-lg p-4" :style="{ backgroundColor: '#eff6ff' }">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="font-semibold text-blue-700">Check-in:</span>
                            <span class="font-medium">{{ formatDateTime(bill?.actual_check_in || bill?.check_in_date) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold text-blue-700">Check-out:</span>
                            <span class="font-medium">{{ formatDateTime(bill?.actual_check_out || bill?.check_out_date) }}</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="font-semibold text-blue-700">Room Type:</span>
                            <span class="font-medium">{{ getRoomType() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold text-blue-700">Rate/Night:</span>
                            <span class="font-medium">{{ getRoomRate() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Detailed Charges -->
                <div class="space-y-6">
                    <!-- Room Charges -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gray-100 px-3 py-2 border-b border-gray-200">
                            <h3 class="font-semibold text-gray-800 flex items-center">
                                🏠 Room Charges
                            </h3>
                        </div>
                        <div class="px-3 py-2">
                            <div class="flex justify-between text-[12px]">
                                <span>Room {{ bill?.room_number }} ({{ bill?.nights }} night(s))</span>
                                <span class="font-semibold tabular-nums">{{ formatCurrency(bill?.room_charges ?? 0) }}</span>
                            </div>
                            <div class="text-[11px] text-gray-500 mt-1">
                                {{ formatCurrency(getRoomRate()) }} × {{ bill?.nights }}
                            </div>
                        </div>
                    </div>

                    <!-- Service Charges -->
                    <div v-if="(bill?.service_charges ?? 0) > 0" class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gray-100 px-3 py-2 border-b border-gray-200">
                            <h3 class="font-semibold text-gray-800 flex items-center">
                                🛎️ Service Charges
                            </h3>
                        </div>
                        <div class="p-3 space-y-2">
                            <template v-for="c in (bill?.charges ?? []).filter(x => x.charge_code === 'SERVICE')" :key="c.description + c.charge_date">
                                <div class="flex justify-between gap-2 text-[12px]">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex justify-between gap-2">
                                            <span class="font-medium truncate">{{ c.description }}</span>
                                            <span class="font-medium tabular-nums whitespace-nowrap">{{ formatCurrency(c.net_amount) }}</span>
                                        </div>
                                        <div class="text-[11px] text-gray-500">
                                            {{ c.charge_date }} {{ c.charge_time || '' }} · {{ c.quantity }} × {{ formatCurrency(c.unit_price) }}
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div class="flex justify-between font-semibold pt-2 border-t border-gray-200">
                                <span>Service Subtotal</span>
                                <span class="tabular-nums">{{ formatCurrency(bill?.service_charges ?? 0) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Restaurant & Bar -->
                    <div v-if="(bill?.pos_charges ?? 0) > 0" class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
                            <h3 class="font-semibold text-gray-800 flex items-center">
                                🍽️ Restaurant & Bar Charges
                            </h3>
                        </div>
                        <div class="p-3 space-y-3">
                            <template v-for="sale in (bill?.pos_sales ?? [])" :key="sale.sale_number">
                                <div class="rounded-md border border-gray-200" :style="{ backgroundColor: '#f9fafb' }">
                                    <div class="flex justify-between items-center px-3 py-2 border-b border-gray-200">
                                        <span class="font-medium text-xs">Sale #{{ sale.sale_number }}</span>
                                        <span class="text-[11px] text-gray-500">{{ formatDateTime(sale.sale_date) }}</span>
                                    </div>
                                    <div class="flex justify-between font-semibold text-[12px] px-3 py-2 border-t border-gray-200">
                                        <span>Subtotal</span>
                                        <span class="tabular-nums">{{ formatCurrency(sale.total_amount) }}</span>
                                    </div>
                                </div>
                            </template>
                            <div class="flex justify-between font-semibold pt-2 border-t border-gray-200">
                                <span>Restaurant/Bar Total</span>
                                <span>{{ formatCurrency(bill?.pos_charges ?? 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="border-t-2 border-gray-300 pt-4">
                    <h3 class="font-semibold mb-3 text-center">Payment Summary</h3>
                    <div class="space-y-1 text-[12px]">
                        <div class="flex justify-between py-1">
                            <span class="font-medium text-gray-600">Room Charges</span>
                            <span class="font-medium tabular-nums">{{ formatCurrency(bill?.room_charges ?? 0) }}</span>
                        </div>
                        <div v-if="(bill?.service_charges ?? 0) > 0" class="flex justify-between py-1">
                            <span class="font-medium text-gray-600">Service Charges</span>
                            <span class="font-medium tabular-nums">{{ formatCurrency(bill?.service_charges ?? 0) }}</span>
                        </div>
                        <div v-if="(bill?.pos_charges ?? 0) > 0" class="flex justify-between py-1">
                            <span class="font-medium text-gray-600">Restaurant/Bar</span>
                            <span class="font-medium tabular-nums">{{ formatCurrency(bill?.pos_charges ?? 0) }}</span>
                        </div>
                        <div v-if="(bill?.tax_amount ?? 0) > 0" class="flex justify-between py-1">
                            <span class="font-medium text-gray-600">Tax</span>
                            <span class="font-medium tabular-nums">{{ formatCurrency(bill?.tax_amount ?? 0) }}</span>
                        </div>
                        <div v-if="(bill?.discount_amount ?? 0) > 0" class="flex justify-between py-1 text-green-600">
                            <span class="font-medium">Discount</span>
                            <span class="font-medium tabular-nums">-{{ formatCurrency(bill?.discount_amount ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-t-2 border-gray-300 border-b-2">
                            <span class="font-bold">Total Amount</span>
                            <span class="font-bold tabular-nums">{{ formatCurrency(bill?.total_amount ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="font-medium text-gray-600">Paid Amount</span>
                            <span class="font-medium tabular-nums">{{ formatCurrency(bill?.paid_amount ?? 0) }}</span>
                        </div>
                        <div v-if="(refundAmount ?? 0) > 0" class="flex justify-between py-2 text-green-600 font-bold border-t-2 border-gray-300">
                            <span>💚 Refund Due (Early Checkout)</span>
                            <span class="tabular-nums">{{ formatCurrency(refundAmount) }}</span>
                        </div>
                        <div v-else-if="(bill?.balance_amount ?? 0) > 0" class="flex justify-between py-2 text-red-600 font-semibold">
                            <span>Balance Due</span>
                            <span class="tabular-nums">{{ formatCurrency(bill?.balance_amount ?? 0) }}</span>
                        </div>
                        <div v-else class="flex justify-between py-2 text-green-600 font-semibold">
                            <span>Payment Status</span>
                            <span>Fully Paid</span>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-6 pt-4 border-t border-gray-300 text-center">
                    <div class="text-[12px] text-gray-600 mb-2">
                        <p class="font-semibold">Thank you for choosing {{ hotelName }}!</p>
                        <p>We hope you enjoyed your stay and look forward to welcoming you back.</p>
                        <p v-if="hotelWebsite" class="mt-1">{{ hotelWebsite }}</p>
                    </div>
                    <div class="text-[11px] text-gray-500">
                        <p>This is a computer-generated receipt. No signature required.</p>
                        <p>Receipt ID: {{ bill?.reservation_number }}-{{ Date.now() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { formatCurrency } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme.js'
import { PrinterIcon } from '@heroicons/vue/24/outline'
import { printPopup } from '@/Utils/printReceipt.js'

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
    user: Object,
    bill: Object,
    role: { type: String, default: 'front_desk' },
    receiptSize: { type: String, default: 'A4' },
    hotelName: String,
    hotelAddress: String,
    hotelPhone: String,
    hotelEmail: String,
    hotelLogo: { type: String, default: '' },
    hotelWebsite: { type: String, default: '' },
    refundAmount: { type: Number, default: 0 },
})

const navigation = computed(() => getNavigationForRole(props.role))

const checkoutRoute = computed(() => {
    const r = props.role
    if (r === 'admin') return '/admin/checkout'
    if (r === 'manager') return '/manager/checkout'
    return '/front-desk/checkout'
})

// Helper methods for the receipt
const getHotelInitials = (name) => {
    if (!name) return 'H'
    return name.split(' ').map(word => word.charAt(0)).join('').substring(0, 2).toUpperCase()
}

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const formatDateTime = (dateTime) => {
    if (!dateTime) return 'N/A'
    return new Date(dateTime).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getRoomType = () => {
    // This would ideally come from the bill data, but for now we'll return a placeholder
    return 'Deluxe Room'
}

const getRoomRate = () => {
    // Calculate room rate from charges and nights
    const roomCharges = props.bill?.room_charges ?? 0
    const nights = props.bill?.nights ?? 1
    return nights > 0 ? roomCharges / nights : 0
}

function printBill() {
    printPopup('checkout-bill-print', `Checkout Bill – ${props.bill?.reservation_number || ''}`, props.receiptSize || 'A4')
}
</script>

<style scoped>
.no-print { display: none; }
@media screen { .no-print { display: flex; } }
#checkout-bill-print { background: #ffffff; color: #000000; }
</style>

<style>
@media print {
    body { visibility: hidden; }
    #checkout-bill-print {
        visibility: visible !important;
        display: block !important;
        position: fixed;
        top: 0; left: 0;
        width: 100%;
        padding: 10mm;
        margin: 0;
        background: #fff;
        color: #000;
    }
    #checkout-bill-print * { visibility: visible !important; }
    @page { margin: 0; size: A4; }
}
</style>

