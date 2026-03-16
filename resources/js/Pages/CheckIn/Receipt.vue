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
import { printPopup } from '@/Utils/printReceipt.js'

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
    printPopup('checkin-receipt-print', 'Check-In Receipt', '80mm')
}
</script>

<style scoped>
.no-print { display: none; }
@media screen { .no-print { display: flex; } }
#checkin-receipt-print { background: #ffffff; color: #000000; }
</style>
