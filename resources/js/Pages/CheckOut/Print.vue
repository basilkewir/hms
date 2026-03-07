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
                    <h1 class="text-2xl font-bold mb-1">{{ hotelName }}</h1>
                    <div class="text-xs leading-5" :style="{ color: '#4b5563' }">
                        <div v-if="hotelAddress">{{ hotelAddress }}</div>
                        <div v-if="hotelPhone">{{ hotelPhone }}</div>
                        <div v-if="hotelEmail">{{ hotelEmail }}</div>
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
                        <div v-if="(bill?.balance_amount ?? 0) > 0" class="flex justify-between py-2 text-red-600 font-semibold">
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
    const el = document.getElementById('checkout-bill-print')
    if (!el) return
    const size = (props.receiptSize || 'A4').toString()
    const pageSizeMap = { A4: '210mm 297mm', A5: '148mm 210mm', Letter: '8.5in 11in', '80mm': '80mm auto', '58mm': '58mm auto' }
    const pageSize = pageSizeMap[size] || '210mm 297mm'
    const printWin = window.open('', '_blank', 'width=800,height=900')
    if (!printWin) {
        window.print()
        return
    }
    
    const enhancedPrintCSS = `
/* Enhanced Professional Receipt Styles */
* { box-sizing: border-box; }
body { 
    font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif; 
    font-size: 14px; 
    color: #000; 
    background: #fff; 
    padding: 20px; 
    line-height: 1.5;
}
.flex { display: flex; }
.justify-between { justify-content: space-between; }
.justify-center { justify-content: center; }
.items-center { align-items: center; }
.grid { display: grid; }
.grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
.gap-2 { gap: 0.5rem; }
.gap-4 { gap: 1rem; }
.gap-6 { gap: 1.5rem; }
.space-y-1 > * + * { margin-top: 0.25rem; }
.space-y-2 > * + * { margin-top: 0.5rem; }
.space-y-4 > * + * { margin-top: 1rem; }
.space-y-6 > * + * { margin-top: 1.5rem; }
.border { border: 1px solid #e5e7eb; }
.border-t { border-top: 1px solid #e5e7eb; }
.border-b { border-bottom: 1px solid #e5e7eb; }
.border-gray-200 { border-color: #e5e7eb; }
.border-gray-300 { border-color: #d1d5db; }
.border-gray-400 { border-color: #9ca3af; }
.text-center { text-align: center; }
.text-sm { font-size: 0.875rem; }
.text-xs { font-size: 0.75rem; }
.text-\[11px\] { font-size: 11px; }
.text-\[12px\] { font-size: 12px; }
.text-lg { font-size: 1.125rem; }
.text-xl { font-size: 1.25rem; }
.text-2xl { font-size: 1.5rem; }
.text-3xl { font-size: 1.875rem; }
.font-semibold { font-weight: 600; }
.font-bold { font-weight: 700; }
.font-medium { font-weight: 500; }
.font-mono { font-family: 'Courier New', monospace; }
.tabular-nums { font-variant-numeric: tabular-nums; }
.whitespace-nowrap { white-space: nowrap; }
.min-w-0 { min-width: 0; }
.flex-1 { flex: 1 1 0%; }
.truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.text-gray-500 { color: #6b7280; }
.text-gray-600 { color: #4b5563; }
.text-gray-700 { color: #374151; }
.text-gray-800 { color: #1f2937; }
.text-gray-900 { color: #111827; }
.text-blue-700 { color: #1e40af; }
.text-green-600 { color: #059669; }
.text-red-600 { color: #dc2626; }
.bg-white { background-color: #ffffff; }
.bg-gray-50 { background-color: #f9fafb; }
.bg-gray-100 { background-color: #f3f4f6; }
.bg-blue-50 { background-color: #eff6ff; }
.bg-gradient-to-r { background-image: linear-gradient(to right, var(--tw-gradient-stops)); }
.from-blue-600 { --tw-gradient-from: #2563eb; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgb(37 99 235 / 0)); }
.to-blue-800 { --tw-gradient-to: #1e40af; }
.rounded-lg { border-radius: 0.5rem; }
.rounded-full { border-radius: 9999px; }
.shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
.shadow-xl { box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
.overflow-hidden { overflow: hidden; }
.inline-block { display: inline-block; }
.pl-2 { padding-left: 0.5rem; }
.pl-4 { padding-left: 1rem; }
.py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
.py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
.pt-2 { padding-top: 0.5rem; }
.pt-3 { padding-top: 0.75rem; }
.pt-4 { padding-top: 1rem; }
.pt-6 { padding-top: 1.5rem; }
.pb-2 { padding-bottom: 0.5rem; }
.pb-4 { padding-bottom: 1rem; }
.pb-6 { padding-bottom: 1.5rem; }
.p-3 { padding: 0.75rem; }
.p-4 { padding: 1rem; }
.p-6 { padding: 1.5rem; }
.p-8 { padding: 2rem; }
.px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
.px-3 { padding-left: 0.75rem; padding-right: 0.75rem; }
.py-1\.5 { padding-top: 0.375rem; padding-bottom: 0.375rem; }
.py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
.py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
.pt-2 { padding-top: 0.5rem; }
.pb-4 { padding-bottom: 1rem; }
.mb-6 { margin-bottom: 1.5rem; }
.rounded-md { border-radius: 0.375rem; }
.mt-2 { margin-top: 0.5rem; }
.mt-4 { margin-top: 1rem; }
.mt-6 { margin-top: 1.5rem; }
.mt-8 { margin-top: 2rem; }
.mb-2 { margin-bottom: 0.5rem; }
.mb-4 { margin-bottom: 1rem; }
.mb-6 { margin-bottom: 1.5rem; }
.mb-8 { margin-bottom: 2rem; }
.ml-2 { margin-left: 0.5rem; }
.w-20 { width: 5rem; }
.h-20 { height: 5rem; }
.w-5 { width: 1.25rem; }
.h-5 { height: 1.25rem; }

/* Print-specific optimizations */
@media print {
    body { padding: 10px; }
    .text-3xl { font-size: 1.5rem; }
    .text-2xl { font-size: 1.25rem; }
    .p-8 { padding: 1rem; }
    .mb-8 { margin-bottom: 1rem; }
    .mt-8 { margin-top: 1rem; }
}
    `
    
    printWin.document.write(`
<!DOCTYPE html><html><head>
<meta charset="utf-8"><title>Professional Checkout Bill – ${props.bill?.reservation_number || ''}</title>
<style>
${enhancedPrintCSS}
@page { size: ${pageSize}; margin: 10mm; }
</style>
</head><body>
${el.outerHTML}
</body></html>
`)
    printWin.document.close()
    printWin.onload = () => {
        printWin.print()
        printWin.onafterprint = () => printWin.close()
    }
}
</script>

<style scoped>
@media print {
    .no-print { display: none !important; }
}

/* Force white background and black text for the entire receipt */
#checkout-bill-print {
    background: #ffffff !important;
    color: #000000 !important;
}

/* Force all text to be black on white background */
#checkout-bill-print *,
#checkout-bill-print *::before,
#checkout-bill-print *::after {
    color: #000000 !important;
    background-color: transparent !important;
}

/* Override all possible text color classes */
#checkout-bill-print .text-gray-900,
#checkout-bill-print .text-gray-800,
#checkout-bill-print .text-gray-700,
#checkout-bill-print .text-gray-600,
#checkout-bill-print .text-gray-500,
#checkout-bill-print .text-gray-400,
#checkout-bill-print .text-gray-300,
#checkout-bill-print .text-gray-200,
#checkout-bill-print .text-gray-100,
#checkout-bill-print .text-black,
#checkout-bill-print .text-white,
#checkout-bill-print .text-kotel-black,
#checkout-bill-print .text-kotel-yellow,
#checkout-bill-print .text-kotel-sky-blue,
#checkout-bill-print .text-kotel-gray,
#checkout-bill-print .text-blue-700,
#checkout-bill-print .text-green-600,
#checkout-bill-print .text-red-600,
#checkout-bill-print h1,
#checkout-bill-print h2,
#checkout-bill-print h3,
#checkout-bill-print h4,
#checkout-bill-print h5,
#checkout-bill-print h6,
#checkout-bill-print p,
#checkout-bill-print span,
#checkout-bill-print div,
#checkout-bill-print strong,
#checkout-bill-print em,
#checkout-bill-print i,
#checkout-bill-print label,
#checkout-bill-print input,
#checkout-bill-print select,
#checkout-bill-print textarea {
    color: #000000 !important;
}

/* Force white backgrounds for all elements */
#checkout-bill-print,
#checkout-bill-print .bg-white,
#checkout-bill-print .bg-gray-50,
#checkout-bill-print .bg-gray-100,
#checkout-bill-print .bg-blue-50,
#checkout-bill-print .bg-gradient-to-r,
#checkout-bill-print div,
#checkout-bill-print section,
#checkout-bill-print article,
#checkout-bill-print header,
#checkout-bill-print footer,
#checkout-bill-print main {
    background-color: #ffffff !important;
    background-image: none !important;
}

/* Force borders to be gray */
#checkout-bill-print .border,
#checkout-bill-print .border-t,
#checkout-bill-print .border-b,
#checkout-bill-print .border-gray-200,
#checkout-bill-print .border-gray-300,
#checkout-bill-print .border-gray-400 {
    border-color: #d1d5db !important;
}

/* Force box shadows to be removed for printing */
#checkout-bill-print .shadow-lg,
#checkout-bill-print .shadow-xl,
#checkout-bill-print .shadow {
    box-shadow: none !important;
}

@media print {
    /* Ensure print styles override everything */
    #checkout-bill-print * {
        color: #000000 !important;
        background-color: #ffffff !important;
        background-image: none !important;
        box-shadow: none !important;
        text-shadow: none !important;
    }
    
    /* Keep some color for status indicators if needed */
    #checkout-bill-print .text-green-700,
    #checkout-bill-print .text-green-600 {
        color: #059669 !important;
    }
    
    #checkout-bill-print .text-red-700,
    #checkout-bill-print .text-red-600 {
        color: #dc2626 !important;
    }
    
    #checkout-bill-print .text-blue-700 {
        color: #1e40af !important;
    }
}
</style>
