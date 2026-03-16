<template>
    <DashboardLayout :title="'Check-In Receipt – ' + (receipt?.reservation_number || '')" :user="user" :navigation="navigation">
        <div class="max-w-2xl mx-auto">
            <!-- Screen-only actions -->
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6 no-print">
                <Link :href="checkinRoute"
                    class="inline-flex items-center px-4 py-2 rounded-md transition-colors font-medium"
                    :style="{
                        backgroundColor: themeColors.secondary,
                        color: themeColors.textPrimary,
                        borderColor: themeColors.border,
                        borderWidth: '1px',
                        borderStyle: 'solid'
                    }">
                    ← Back to Check-In
                </Link>
                <button @click="printReceipt"
                    class="inline-flex items-center px-6 py-2 rounded-md transition-colors font-medium text-white"
                    :style="{ backgroundColor: themeColors.primary }">
                    🖨️ Print Receipt
                </button>
            </div>

            <!-- Receipt -->
            <div id="checkin-receipt-print" class="bg-white text-black rounded-lg shadow-xl p-8 print:p-4 print:shadow-none">
                <!-- Header -->
                <div class="text-center border-b pb-4 mb-6" style="border-color:#d1d5db;">
                    <img v-if="hotelLogo" :src="hotelLogo" alt="Hotel Logo" class="h-16 max-w-xs object-contain mx-auto mb-3">
                    <h1 class="text-2xl font-bold mb-1">{{ hotelName }}</h1>
                    <div class="text-xs leading-5 text-gray-600">
                        <div v-if="hotelAddress">{{ hotelAddress }}</div>
                        <div v-if="hotelPhone">{{ hotelPhone }}</div>
                        <div v-if="hotelEmail">{{ hotelEmail }}</div>
                        <div v-if="hotelWebsite">{{ hotelWebsite }}</div>
                    </div>
                    <div class="mt-3 font-semibold text-sm">Check-In Payment Receipt</div>
                </div>

                <!-- Receipt Details -->
                <div class="grid grid-cols-2 gap-4 mb-6 text-sm rounded-lg p-4" style="background-color:#f9fafb;">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-700">Reservation #:</span>
                            <span class="font-mono">{{ receipt?.reservation_number }}</span>
                        </div>
                        <div v-if="receipt?.folio_number" class="flex justify-between">
                            <span class="font-semibold text-gray-700">Folio #:</span>
                            <span class="font-mono">{{ receipt?.folio_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-700">Date:</span>
                            <span>{{ formatDate(new Date()) }}</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-700">Guest:</span>
                            <span class="font-medium">{{ receipt?.guest_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-700">Room:</span>
                            <span class="font-medium">{{ receipt?.room_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-700">Nights:</span>
                            <span class="font-medium">{{ receipt?.nights }}</span>
                        </div>
                    </div>
                </div>

                <!-- Stay Details -->
                <div class="grid grid-cols-2 gap-4 mb-6 text-sm rounded-lg p-4" style="background-color:#eff6ff;">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="font-semibold text-blue-700">Check-In:</span>
                            <span class="font-medium">{{ formatDateTime(receipt?.actual_check_in || receipt?.check_in_date) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold text-blue-700">Check-Out:</span>
                            <span class="font-medium">{{ formatDate(receipt?.check_out_date) }}</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="font-semibold text-blue-700">Room Rate:</span>
                            <span class="font-medium">{{ formatCurrency(receipt?.room_rate ?? 0) }}/night</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold text-blue-700">Payment Method:</span>
                            <span class="font-medium capitalize">{{ formatPaymentMethod(receipt?.payment_method) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="border-t-2 border-gray-300 pt-4 mb-6">
                    <h3 class="font-semibold mb-3 text-center">Payment Summary</h3>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between py-1">
                            <span class="font-medium text-gray-600">Total Bill Amount</span>
                            <span class="font-medium">{{ formatCurrency(receipt?.total_amount ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-t-2 border-b-2 border-gray-300 text-green-700 font-bold">
                            <span>Amount Paid Now</span>
                            <span>{{ formatCurrency(receipt?.payment_amount ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between py-1 text-gray-500">
                            <span class="font-medium">Already Paid</span>
                            <span class="font-medium">{{ formatCurrency((receipt?.paid_amount ?? 0) - (receipt?.payment_amount ?? 0)) }}</span>
                        </div>
                        <div class="flex justify-between py-2 font-semibold" :class="(receipt?.balance_amount ?? 0) > 0 ? 'text-red-600' : 'text-green-600'">
                            <span>Balance Remaining</span>
                            <span>{{ (receipt?.balance_amount ?? 0) > 0 ? formatCurrency(receipt?.balance_amount) : 'Fully Paid' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-6 pt-4 border-t border-gray-300 text-center">
                    <div class="text-xs text-gray-600 mb-2">
                        <p class="font-semibold">Thank you for choosing {{ hotelName }}!</p>
                        <p>We hope you enjoy your stay.</p>
                        <p v-if="hotelWebsite" class="mt-1">{{ hotelWebsite }}</p>
                    </div>
                    <div class="text-xs text-gray-500 mt-2">
                        <p>This is a computer-generated receipt. No signature required.</p>
                        <p>Receipt ID: {{ receipt?.reservation_number }}-CI-{{ Date.now() }}</p>
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

const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    secondary: `var(--kotel-secondary)`,
    primary: `var(--kotel-primary)`,
}))
loadTheme()

const props = defineProps({
    user: Object,
    role: { type: String, default: 'front_desk' },
    receipt: Object,
    hotelName: String,
    hotelAddress: String,
    hotelPhone: String,
    hotelEmail: String,
    hotelLogo: String,
    hotelWebsite: String,
})

const navigation = computed(() => getNavigationForRole(props.role))

const checkinRoute = computed(() => {
    if (props.role === 'admin') return '/admin/checkin'
    if (props.role === 'manager') return '/manager/checkin'
    return '/front-desk/checkin'
})

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
}

const formatDateTime = (dateTime) => {
    if (!dateTime) return 'N/A'
    return new Date(dateTime).toLocaleString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
    })
}

const formatPaymentMethod = (method) => {
    const map = { cash: 'Cash', card: 'Card', bank_transfer: 'Bank Transfer', mobile_payment: 'Mobile Payment' }
    return map[method] || method || 'Cash'
}

function printReceipt() {
    const el = document.getElementById('checkin-receipt-print')
    if (!el) return
    const printWin = window.open('', '_blank', 'width=700,height=900')
    if (!printWin) { window.print(); return }
    printWin.document.write(`<!DOCTYPE html><html><head>
<meta charset="utf-8"><title>Check-In Receipt</title>
<style>
*{box-sizing:border-box;}
body{font-family:'Segoe UI',Arial,sans-serif;font-size:13px;color:#000;background:#fff;padding:16px;margin:0;}
.flex{display:flex;} .justify-between{justify-content:space-between;} .items-center{align-items:center;}
.grid{display:grid;} .grid-cols-2{grid-template-columns:repeat(2,1fr);}
.gap-4{gap:1rem;} .space-y-1>*+*{margin-top:0.25rem;} .space-y-2>*+*{margin-top:0.5rem;}
.text-center{text-align:center;} .text-sm{font-size:0.875rem;} .text-xs{font-size:0.75rem;}
.font-semibold{font-weight:600;} .font-bold{font-weight:700;} .font-medium{font-weight:500;} .font-mono{font-family:monospace;}
.text-gray-500{color:#6b7280;} .text-gray-600{color:#4b5563;} .text-gray-700{color:#374151;}
.text-blue-700{color:#1e40af;} .text-green-600{color:#16a34a;} .text-green-700{color:#15803d;} .text-red-600{color:#dc2626;}
.border-t{border-top:1px solid #e5e7eb;} .border-b{border-bottom:1px solid #e5e7eb;}
.border-t-2{border-top:2px solid #d1d5db;} .border-b-2{border-bottom:2px solid #d1d5db;}
.border-gray-300{border-color:#d1d5db;} .rounded-lg{border-radius:0.5rem;}
.pb-4{padding-bottom:1rem;} .mb-6{margin-bottom:1.5rem;} .mb-3{margin-bottom:0.75rem;} .mb-1{margin-bottom:0.25rem;} .mb-2{margin-bottom:0.5rem;}
.mt-1{margin-top:0.25rem;} .mt-2{margin-top:0.5rem;} .mt-3{margin-top:0.75rem;} .mt-6{margin-top:1.5rem;}
.p-4{padding:1rem;} .pt-4{padding-top:1rem;} .py-1{padding-top:0.25rem;padding-bottom:0.25rem;}
.py-2{padding-top:0.5rem;padding-bottom:0.5rem;}
.h-16{height:4rem;} .max-w-xs{max-width:20rem;} .object-contain{object-fit:contain;} .mx-auto{margin-left:auto;margin-right:auto;}
.capitalize{text-transform:capitalize;}
@page{size:A4;margin:10mm;}
</style></head><body>
${el.outerHTML}
</body></html>`)
    printWin.document.close()
    printWin.onload = () => { printWin.print(); printWin.onafterprint = () => printWin.close() }
}
</script>

<style scoped>
@media print {
    .no-print { display: none !important; }
}
#checkin-receipt-print {
    background: #ffffff !important;
    color: #000000 !important;
}
@media print {
    #checkin-receipt-print * {
        color: #000000 !important;
        background-color: #ffffff !important;
        background-image: none !important;
        box-shadow: none !important;
    }
    #checkin-receipt-print .text-green-600,
    #checkin-receipt-print .text-green-700 { color: #059669 !important; }
    #checkin-receipt-print .text-red-600 { color: #dc2626 !important; }
    #checkin-receipt-print .text-blue-700 { color: #1e40af !important; }
}
</style>
