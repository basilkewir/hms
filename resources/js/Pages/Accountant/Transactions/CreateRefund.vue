<template>
    <DashboardLayout title="Process Refund" :user="user">
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Process Refund</h1>
                    <p class="text-gray-600 mt-2">Refund a payment to a guest or customer.</p>
                </div>
                <button @click="goBack"
                        class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                    <ArrowLeftIcon class="h-4 w-4 mr-2 inline" />
                    Back to Refunds
                </button>
            </div>
        </div>

        <!-- Search Section -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Find Payment to Refund</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Payments</label>
                    <input v-model="searchQuery"
                           type="text"
                           placeholder="Search by payment ID, guest name, or reference..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                    <select v-model="paymentMethodFilter"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Methods</option>
                        <option value="cash">Cash</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="debit_card">Debit Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                    <select v-model="dateRangeFilter"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Payments List -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Available Payments</h3>
                <div class="mt-2 text-sm text-gray-500">
                    Showing {{ filteredPayments.length }} of {{ allPayments.length }} payments
                </div>
            </div>
            <div class="overflow-x-auto">
                <div v-if="filteredPayments.length === 0" class="text-center py-8">
                    <div class="text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No payments found</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ searchQuery || paymentMethodFilter || dateRangeFilter !== 'all' 
                                ? 'Try adjusting your search filters.' 
                                : 'There are no payments available for refund at this time.' }}
                        </p>
                        <div class="mt-6">
                            <button @click="clearFilters" 
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Clear filters
                            </button>
                        </div>
                    </div>
                </div>
                <table v-else class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Select
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Payment ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Guest/Reference
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Method
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="payment in filteredPayments" :key="payment.id"
                            :class="selectedPayment?.id === payment.id ? 'bg-blue-50' : 'hover:bg-gray-50'"
                            @click="selectPayment(payment)">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="radio"
                                       :checked="selectedPayment?.id === payment.id"
                                       @change="selectPayment(payment)"
                                       class="text-blue-600 focus:ring-blue-500" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ payment.payment_number || payment.transaction_id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ payment.guest_name }}</div>
                                <div class="text-sm text-gray-500">{{ payment.reference }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                {{ formatCurrency(payment.local_amount || payment.amount || 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatPaymentMethod(payment.payment_method) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(payment.status)">
                                    {{ formatStatus(payment.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDateTime(payment.processed_at || payment.created_at) }}
                            </td>
                        </tr>
                        <tr v-if="filteredPayments.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                No payments found. Try adjusting your search criteria.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Refund Details -->
        <div v-if="selectedPayment" class="bg-white shadow rounded-lg p-6 mt-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Refund Details</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-md font-medium text-gray-900 mb-3">Payment Information</h3>
                    <div class="space-y-2 text-sm">
                        <div><span class="font-medium">Payment ID:</span> {{ selectedPayment.payment_number || selectedPayment.transaction_id }}</div>
                        <div><span class="font-medium">Guest:</span> {{ selectedPayment.guest_name }}</div>
                        <div><span class="font-medium">Reference:</span> {{ selectedPayment.reference }}</div>
                        <div><span class="font-medium">Original Amount:</span> {{ formatCurrency(selectedPayment.local_amount || selectedPayment.amount || 0) }}</div>
                        <div><span class="font-medium">Payment Method:</span> {{ formatPaymentMethod(selectedPayment.payment_method) }}</div>
                        <div><span class="font-medium">Already Refunded:</span> {{ formatCurrency(selectedPayment.refunded_amount || 0) }}</div>
                        <div><span class="font-medium">Refundable Amount:</span> {{ formatCurrency(getRefundableAmount(selectedPayment)) }}</div>
                    </div>
                </div>

                <div>
                    <h3 class="text-md font-medium text-gray-900 mb-3">Refund Configuration</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Refund Amount</label>
                            <input v-model.number="refundAmount"
                                   type="number"
                                   :max="getRefundableAmount(selectedPayment)"
                                   :min="0.01"
                                   step="0.01"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            <p class="mt-1 text-xs text-gray-500">
                                Maximum refundable: {{ formatCurrency(getRefundableAmount(selectedPayment)) }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Refund Reason</label>
                            <select v-model="refundReason"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select a reason...</option>
                                <option value="Guest dissatisfaction">Guest dissatisfaction</option>
                                <option value="Service not provided">Service not provided</option>
                                <option value="Duplicate charge">Duplicate charge</option>
                                <option value="Overcharge">Overcharge</option>
                                <option value="Cancellation">Cancellation</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div v-if="refundReason === 'Other'">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Specify Reason</label>
                            <textarea v-model="customReason"
                                      rows="3"
                                      placeholder="Please specify the reason for this refund..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Refund Method</label>
                            <select v-model="refundMethod"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="same_method">Same as original payment method</option>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Refund Summary -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="text-md font-medium text-gray-900 mb-2">Refund Summary</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="font-medium">Refund Amount:</span>
                        <span class="ml-2 text-green-600 font-semibold">{{ formatCurrency(refundAmount || 0) }}</span>
                    </div>
                    <div>
                        <span class="font-medium">Refund Method:</span>
                        <span class="ml-2">{{ formatRefundMethod(refundMethod) }}</span>
                    </div>
                    <div>
                        <span class="font-medium">Reason:</span>
                        <span class="ml-2">{{ refundReason || customReason || 'Not specified' }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex justify-between">
                <button @click="resetSelection"
                        class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                    Clear Selection
                </button>
                <button @click="processRefund"
                        :disabled="!canProcessRefund"
                        :class="canProcessRefund ? 'bg-red-600 hover:bg-red-700' : 'bg-red-400 cursor-not-allowed'"
                        class="text-white px-6 py-2 rounded-md">
                    <CurrencyDollarIcon class="h-4 w-4 mr-2 inline" />
                    Process Refund
                </button>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'
import {
    ArrowLeftIcon,
    CurrencyDollarIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    payments: Array,
})

const user = computed(() => props.user)
const allPayments = ref(props.payments || [])
const isLoading = ref(false)

// Reactive search functionality
const searchQuery = ref('')
const paymentMethodFilter = ref('')
const dateRangeFilter = ref('all')

// Watch for changes in props.payments and update allPayments
watch(() => props.payments, (newPayments) => {
    if (newPayments) {
        allPayments.value = newPayments
    }
}, { immediate: true })

// Client-side filtering - no API call needed
const filteredPayments = computed(() => {
    let payments = allPayments.value.filter(p => {
        // More permissive filtering - include payments that can be refunded
        const hasValidStatus = !p.status || ['completed', 'paid', 'success'].includes(p.status.toLowerCase())
        const hasValidAmount = (p.local_amount || p.amount || 0) > 0
        return hasValidStatus && hasValidAmount
    })

    // Apply search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        payments = payments.filter(p =>
            (p.payment_number || p.transaction_id || '').toLowerCase().includes(query) ||
            (p.guest_name || '').toLowerCase().includes(query) ||
            (p.reference || '').toLowerCase().includes(query)
        )
    }

    // Apply payment method filter
    if (paymentMethodFilter.value) {
        payments = payments.filter(p => p.payment_method === paymentMethodFilter.value)
    }

    // Apply date range filter
    if (dateRangeFilter.value && dateRangeFilter.value !== 'all') {
        const now = new Date()
        const paymentDate = (payment) => new Date(payment.processed_at || payment.created_at)

        switch (dateRangeFilter.value) {
            case 'today':
                payments = payments.filter(p => {
                    const date = paymentDate(p)
                    return date.toDateString() === now.toDateString()
                })
                break
            case 'week':
                const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)
                payments = payments.filter(p => paymentDate(p) >= weekAgo)
                break
            case 'month':
                const monthAgo = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000)
                payments = payments.filter(p => paymentDate(p) >= monthAgo)
                break
        }
    }

    return payments
})

// Refund configuration
const selectedPayment = ref(null)
const refundAmount = ref(0)
const refundReason = ref('')
const customReason = ref('')
const refundMethod = ref('same_method')

const canProcessRefund = computed(() => {
    return selectedPayment.value &&
           refundAmount.value > 0 &&
           refundAmount.value <= getRefundableAmount(selectedPayment.value) &&
           (refundReason.value || customReason.value)
})

// Methods
const selectPayment = (payment) => {
    selectedPayment.value = payment
    refundAmount.value = Math.min(getRefundableAmount(payment), payment.local_amount || payment.amount || 0)
    refundReason.value = ''
    customReason.value = ''
    refundMethod.value = 'same_method'
}

const resetSelection = () => {
    selectedPayment.value = null
    refundAmount.value = 0
    refundReason.value = ''
    customReason.value = ''
    refundMethod.value = 'same_method'
}

const getRefundableAmount = (payment) => {
    const originalAmount = payment.local_amount || payment.amount || 0
    const alreadyRefunded = payment.refunded_amount || 0
    return Math.max(0, originalAmount - alreadyRefunded)
}

const processRefund = async () => {
    if (!canProcessRefund.value) return

    const reason = refundReason.value || customReason.value

    try {
        await router.post(route('accountant.transactions.refund.process', selectedPayment.value.id), {
            refund_amount: refundAmount.value,
            refund_reason: reason,
            refund_method: refundMethod.value
        })

        // Reset form after successful refund
        resetSelection()

        // Show success message (you might want to add a toast notification system)
        alert('Refund processed successfully!')
    } catch (error) {
        console.error('Refund failed:', error)
        alert('Failed to process refund. Please try again.')
    }
}

const goBack = () => {
    router.get(route('accountant.transactions.refunds'))
}

const clearFilters = () => {
    searchQuery.value = ''
    paymentMethodFilter.value = ''
    dateRangeFilter.value = 'all'
}

// Formatting methods
const formatPaymentMethod = (method) => {
    if (!method) return 'Unknown'
    return method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatRefundMethod = (method) => {
    const methods = {
        'same_method': 'Same as original payment method',
        'cash': 'Cash',
        'bank_transfer': 'Bank Transfer'
    }
    return methods[method] || method
}

const formatStatus = (status) => {
    if (!status) return 'Completed'
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getStatusColor = (status) => {
    const colors = {
        completed: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        failed: 'bg-red-100 text-red-800'
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const formatDateTime = (date) => {
    return date ? new Date(date).toLocaleString() : 'N/A'
}
</script>
