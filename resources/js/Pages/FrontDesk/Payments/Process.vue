<template>
    <DashboardLayout title="Process Payment" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Process Payment</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Process guest payments and transactions.</p>
                </div>
            </div>
        </div>

        <div v-if="statusMessage" class="rounded-lg p-4 mb-6 border"
             :style="{ backgroundColor: statusType === 'error' ? 'rgba(239, 68, 68, 0.1)' : 'rgba(34, 197, 94, 0.1)', borderColor: themeColors.border, color: statusType === 'error' ? themeColors.danger : themeColors.success }">
            {{ statusMessage }}
        </div>

        <!-- Payment Form -->
        <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <form @submit.prevent="processPayment" class="space-y-6">
                <!-- Guest Selection -->
                <div>
                    <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Guest Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Guest/Room Number</label>
                            <select v-model="form.guestId" @change="loadGuestBill" required
                                    class="w-full border rounded-md px-3 py-2 focus:outline-none"
                                    :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                                <option value="">Select Guest</option>
                                <option v-for="guest in currentGuests" :key="guest.id" :value="guest.id">
                                    {{ guest.name }} — Room {{ guest.room }} (Balance: {{ formatCurrency(guest.balance) }})
                                </option>
                            </select>
                        </div>
                        <div v-if="selectedGuest">
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Guest Details</label>
                            <div class="p-3 rounded-md border space-y-1" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                                <p class="text-sm"><span class="font-semibold">Name:</span> {{ selectedGuest.name }}</p>
                                <p class="text-sm"><span class="font-semibold">Room:</span> {{ selectedGuest.room }}</p>
                                <p class="text-sm"><span class="font-semibold">Check-out:</span> {{ selectedGuest.checkOut }}</p>
                                <p v-if="selectedGuest.email" class="text-sm"><span class="font-semibold">Email:</span> {{ selectedGuest.email }}</p>
                                <p v-if="selectedGuest.phone" class="text-sm"><span class="font-semibold">Phone:</span> {{ selectedGuest.phone }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bill Summary -->
                <div v-if="selectedGuest">
                    <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Bill Summary</h3>
                    <div class="rounded-lg p-4 border space-y-2" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                        <div class="flex items-center justify-between">
                            <span :style="{ color: themeColors.textSecondary }">Total Charges</span>
                            <span :style="{ color: themeColors.textPrimary }">{{ formatCurrency(selectedGuest.total) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span :style="{ color: themeColors.textSecondary }">Amount Paid</span>
                            <span :style="{ color: themeColors.success }">{{ formatCurrency(selectedGuest.paid) }}</span>
                        </div>
                        <div class="flex items-center justify-between border-t pt-2" :style="{ borderColor: themeColors.border }">
                            <span class="font-semibold" :style="{ color: themeColors.textPrimary }">Outstanding Balance</span>
                            <span class="font-bold text-lg" :style="{ color: themeColors.danger }">{{ formatCurrency(totalAmount) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div v-if="selectedGuest">
                    <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Payment Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Payment Method *</label>
                            <select v-model="form.paymentMethod" required
                                    class="w-full border rounded-md px-3 py-2 focus:outline-none"
                                    :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                                <option value="">Select Payment Method</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="debit_card">Debit Card</option>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Amount to Pay *</label>
                            <input type="number" step="0.01" v-model="form.amount" required
                                   :max="totalAmount"
                                   class="w-full border rounded-md px-3 py-2 focus:outline-none"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        </div>
                    </div>
                </div>

                <!-- Card Details (if card payment) -->
                <div v-if="form.paymentMethod === 'credit_card' || form.paymentMethod === 'debit_card'">
                    <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Card Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Card Number *</label>
                            <input type="text" v-model="form.cardNumber" placeholder="1234 5678 9012 3456" required
                                   class="w-full border rounded-md px-3 py-2 focus:outline-none"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Cardholder Name *</label>
                            <input type="text" v-model="form.cardholderName" required
                                   class="w-full border rounded-md px-3 py-2 focus:outline-none"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Expiry Date *</label>
                            <input type="text" v-model="form.expiryDate" placeholder="MM/YY" required
                                   class="w-full border rounded-md px-3 py-2 focus:outline-none"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">CVV *</label>
                            <input type="text" v-model="form.cvv" placeholder="123" required
                                   class="w-full border rounded-md px-3 py-2 focus:outline-none"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        </div>
                    </div>
                </div>

                <!-- Payment Notes -->
                <div v-if="selectedGuest">
                    <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Payment Notes</h3>
                    <textarea v-model="form.notes" rows="3" placeholder="Any additional notes about this payment..."
                              class="w-full border rounded-md px-3 py-2 focus:outline-none"
                              :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"></textarea>
                </div>

                <!-- Form Actions -->
                <div v-if="selectedGuest" class="flex items-center justify-end space-x-4 pt-6 border-t" :style="{ borderColor: themeColors.border }">
                    <button type="button" @click="resetForm"
                            class="px-6 py-2 rounded-md hover:opacity-90 transition-opacity"
                            :style="{ backgroundColor: themeColors.border, color: themeColors.textPrimary }">
                        Reset
                    </button>
                    <button type="submit" :disabled="isProcessing"
                            class="px-6 py-2 rounded-md hover:opacity-90 transition-opacity disabled:opacity-50"
                            :style="{ backgroundColor: themeColors.success, color: '#000' }">
                        <span v-if="isProcessing">Processing...</span>
                        <span v-else>Process Payment</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Recent Payments -->
        <div class="shadow rounded-lg p-6 mt-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Recent Payments</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Guest
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Method
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Time
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="payment in recentPayments" :key="payment.id" class="transition-colors" :style="hoveredRow === payment.id ? { backgroundColor: themeColors.hover } : {}" @mouseenter="hoveredRow = payment.id" @mouseleave="hoveredRow = null">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                {{ payment.guest }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(payment.amount || 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="getMethodPillStyle(payment.method)">
                                    {{ formatMethod(payment.method) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="getStatusPillStyle(payment.status)">
                                    {{ formatStatus(payment.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textSecondary }">
                                {{ payment.time }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Receipt Modal -->
        <div v-if="showReceiptModal && receiptData"
             class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4"
             @click.self="showReceiptModal = false">
            <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full mx-4 overflow-hidden">
                <!-- Receipt Content (used for printing) -->
                <div id="payment-receipt-content" class="p-6">
                    <div style="text-align:center; margin-bottom:16px; border-bottom:2px solid #000; padding-bottom:12px;">
                        <h2 style="margin:0; font-size:20px; font-weight:bold;">Payment Receipt</h2>
                        <p style="margin:4px 0; color:#555; font-size:13px;">{{ new Date().toLocaleDateString('en-US', { year:'numeric', month:'long', day:'numeric' }) }}</p>
                    </div>
                    <div style="text-align:center; font-size:16px; font-weight:bold; margin:10px 0; color:#1a1a1a;">
                        {{ receiptData.payment_number }}
                    </div>
                    <div style="margin:8px 0; display:flex; justify-content:space-between;">
                        <span style="color:#555;">Guest</span>
                        <span style="font-weight:bold;">{{ receiptData.guest_name }}</span>
                    </div>
                    <div style="margin:8px 0; display:flex; justify-content:space-between;">
                        <span style="color:#555;">Room</span>
                        <span style="font-weight:bold;">{{ receiptData.room_number }}</span>
                    </div>
                    <div style="margin:8px 0; display:flex; justify-content:space-between;">
                        <span style="color:#555;">Payment Method</span>
                        <span style="font-weight:bold; text-transform:capitalize;">{{ receiptData.payment_method?.replace('_', ' ') }}</span>
                    </div>
                    <div style="margin:8px 0; display:flex; justify-content:space-between;">
                        <span style="color:#555;">Date & Time</span>
                        <span style="font-weight:bold;">{{ receiptData.processed_at }}</span>
                    </div>
                    <div v-if="receiptData.notes" style="margin:8px 0; display:flex; justify-content:space-between;">
                        <span style="color:#555;">Notes</span>
                        <span style="font-weight:bold;">{{ receiptData.notes }}</span>
                    </div>
                    <div style="border-top:2px solid #000; padding-top:10px; margin-top:10px; display:flex; justify-content:space-between; font-size:18px; font-weight:bold;">
                        <span>Amount Paid</span>
                        <span>{{ formatCurrency(receiptData.amount) }}</span>
                    </div>
                    <div style="text-align:center; margin-top:20px; font-size:12px; color:#777; border-top:1px solid #ccc; padding-top:10px;">
                        Thank you for your payment!
                    </div>
                </div>
                <!-- Modal Actions -->
                <div class="flex gap-3 p-4 border-t border-gray-200 bg-gray-50">
                    <button @click="printReceipt"
                            class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition">
                        🖨️ Print Receipt
                    </button>
                    <button @click="showReceiptModal = false"
                            class="flex-1 bg-gray-200 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-300 transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme'

const props = defineProps({
    user:           Object,
    navigation:     Array,
    currentGuests:  { type: Array, default: () => [] },
    recentPayments: { type: Array, default: () => [] },
    paymentReceipt: { type: Object, default: null },
})

const statusMessage = ref('')
const statusType = ref('success')

const isProcessing = ref(false)
const totalAmount = ref(0)
const hoveredRow = ref(null)
const showReceiptModal = ref(false)
const receiptData = ref(null)

const handleReceipt = (receipt) => {
    if (receipt) {
        receiptData.value = receipt
        showReceiptModal.value = true
        statusType.value = 'success'
        statusMessage.value = `Payment ${receipt.payment_number} processed successfully.`
        // Auto-trigger print after a short delay to allow modal to render
        setTimeout(() => {
            printReceipt()
        }, 600)
    }
}

// Handle receipt on initial mount (direct page load)
onMounted(() => {
    handleReceipt(props.paymentReceipt)
})

// Handle receipt on Inertia navigation (after router.post redirect)
watch(() => props.paymentReceipt, (newReceipt) => {
    handleReceipt(newReceipt)
})

const printReceipt = () => {
    if (!receiptData.value) return
    const r = receiptData.value
    const printWindow = window.open('', '_blank', 'width=420,height=650')
    if (!printWindow) {
        // Popup blocked - fallback: use current window print with CSS
        window.print()
        return
    }
    const methodLabel = (r.payment_method || '').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
    printWindow.document.write(`<!DOCTYPE html>
<html>
<head>
    <title>Payment Receipt - ${r.payment_number}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; padding: 24px; font-size: 14px; color: #1a1a1a; max-width: 380px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #000; padding-bottom: 12px; }
        .header h2 { font-size: 22px; font-weight: bold; margin-bottom: 4px; }
        .header p { color: #555; font-size: 12px; }
        .receipt-num { text-align: center; font-size: 15px; font-weight: bold; margin: 12px 0; background: #f5f5f5; padding: 8px; border-radius: 4px; }
        .row { display: flex; justify-content: space-between; align-items: center; padding: 7px 0; border-bottom: 1px solid #eee; }
        .row:last-child { border-bottom: none; }
        .label { color: #666; }
        .value { font-weight: 600; text-align: right; }
        .total-section { border-top: 2px solid #000; margin-top: 12px; padding-top: 12px; }
        .total-row { display: flex; justify-content: space-between; font-size: 18px; font-weight: bold; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; border-top: 1px solid #ccc; padding-top: 12px; }
        @media print { body { padding: 10px; } }
    </style>
</head>
<body>
    <div class="header">
        <h2>Payment Receipt</h2>
        <p>${new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
    </div>
    <div class="receipt-num">${r.payment_number}</div>
    <div class="row"><span class="label">Guest</span><span class="value">${r.guest_name}</span></div>
    <div class="row"><span class="label">Room</span><span class="value">${r.room_number}</span></div>
    <div class="row"><span class="label">Payment Method</span><span class="value">${methodLabel}</span></div>
    <div class="row"><span class="label">Date & Time</span><span class="value">${r.processed_at}</span></div>
    ${r.notes ? `<div class="row"><span class="label">Notes</span><span class="value">${r.notes}</span></div>` : ''}
    <div class="total-section">
        <div class="total-row"><span>Amount Paid</span><span>${r.amount.toLocaleString('en-US', { style: 'currency', currency: 'USD' })}</span></div>
    </div>
    <div class="footer">Thank you for your payment!</div>
</body>
</html>`)
    printWindow.document.close()
    printWindow.focus()
    setTimeout(() => {
        printWindow.print()
        printWindow.close()
    }, 300)
}

const { currentTheme } = useTheme()

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
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)'
}))

const form = ref({
    guestId: '',
    paymentMethod: '',
    amount: '',
    cardNumber: '',
    cardholderName: '',
    expiryDate: '',
    cvv: '',
    notes: '',
})

const selectedGuest = computed(() => {
    if (!props.currentGuests || !form.value.guestId) return null
    return props.currentGuests.find(guest => guest?.id == form.value.guestId)
})

const loadGuestBill = () => {
    if (!selectedGuest.value) return

    // Calculate current balance by subtracting all payments from total amount
    // We need to fetch the actual reservation data to get accurate payment information
    // For now, we'll use the balance from the guest data, but in a real implementation
    // this should be calculated server-side

    const totalAmountValue = parseFloat(selectedGuest.value.total || 0)
    const paidAmount = parseFloat(selectedGuest.value.paid || 0)

    // Calculate remaining balance
    const remainingBalance = totalAmountValue - paidAmount

    totalAmount.value = Number.isFinite(remainingBalance) ? remainingBalance : 0
    form.value.amount = totalAmount.value ? totalAmount.value.toFixed(2) : ''
}

const processPayment = () => {
    if (isProcessing.value) return
    isProcessing.value = true

    router.post(route('front-desk.payments.store'), {
        reservation_id: form.value.guestId,
        payment_method: form.value.paymentMethod,
        amount: form.value.amount,
        notes: form.value.notes,
    }, {
        onSuccess: (page) => {
            statusType.value = 'success'
            statusMessage.value = page.props?.flash?.success || 'Payment processed successfully.'
            resetForm()
        },
        onError: (errors) => {
            statusType.value = 'error'
            statusMessage.value = errors?.message || Object.values(errors)[0] || 'Error processing payment. Please try again.'
        },
        onFinish: () => {
            isProcessing.value = false
        },
    })
}

const resetForm = () => {
    Object.assign(form.value, {
        guestId: '',
        paymentMethod: '',
        amount: '',
        cardNumber: '',
        cardholderName: '',
        expiryDate: '',
        cvv: '',
        notes: '',
    })
    totalAmount.value = 0
}

const getMethodPillStyle = (method) => {
    const key = (method || '').toLowerCase()
    if (key === 'credit_card') return { backgroundColor: themeColors.value.primary, color: '#000' }
    if (key === 'debit_card') return { backgroundColor: themeColors.value.success, color: '#000' }
    if (key === 'cash') return { backgroundColor: themeColors.value.warning, color: '#000' }
    if (key === 'bank_transfer') return { backgroundColor: themeColors.value.secondary, color: '#000' }
    return { backgroundColor: themeColors.value.border, color: themeColors.value.textPrimary }
}

const getStatusPillStyle = (status) => {
    const key = (status || '').toLowerCase()
    if (key === 'completed') return { backgroundColor: themeColors.value.success, color: '#000' }
    if (key === 'pending') return { backgroundColor: themeColors.value.warning, color: '#000' }
    if (key === 'failed') return { backgroundColor: themeColors.value.danger, color: '#000' }
    return { backgroundColor: themeColors.value.border, color: themeColors.value.textPrimary }
}

const formatMethod = (method) => {
    if (!method) return 'Unknown'
    return method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatStatus = (status) => {
    if (!status) return 'Unknown'
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatTime = (time) => {
    return time
}
</script>
