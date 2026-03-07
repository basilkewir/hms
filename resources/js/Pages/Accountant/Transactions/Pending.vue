<template>
    <DashboardLayout title="Pending Payments" :user="user">
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Pending Payments</h1>
                    <p class="text-gray-600 mt-2">Payments awaiting approval or processing.</p>
                </div>
                <button @click="exportTransactions"
                        class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700">
                    <DocumentArrowDownIcon class="h-4 w-4 mr-2 inline" />
                    Export
                </button>
            </div>
            <div v-if="statusMessage" class="mt-4 rounded-md px-4 py-3 text-sm"
                 :class="statusType === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
                {{ statusMessage }}
            </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Pending Transactions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Transaction ID
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="payment in pendingPayments" :key="payment.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ payment.transaction_id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ payment.guest_name }}</div>
                                <div class="text-sm text-gray-500">{{ payment.reference }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-yellow-700">
                                {{ formatCurrency(payment.amount || 0) }}
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
                                {{ formatDateTime(payment.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click="processPayment(payment)"
                                        class="text-green-600 hover:text-green-900">
                                    Process
                                </button>
                            </td>
                        </tr>
                        <tr v-if="pendingPayments.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                No pending payments found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'
import { DocumentArrowDownIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    pendingPayments: Array,
})

const pendingPayments = computed(() => props.pendingPayments || [])
const statusMessage = ref('')
const statusType = ref('success')

const exportTransactions = () => {
    window.location.href = route('accountant.transactions.export')
}

const processPayment = (payment) => {
    statusMessage.value = ''
    router.post(route('accountant.transactions.process', payment.id), {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            statusType.value = 'success'
            statusMessage.value = `Payment ${payment.transaction_id} processed successfully.`
        },
        onError: () => {
            statusType.value = 'error'
            statusMessage.value = 'Failed to process payment. Please try again.'
        }
    })
}

const formatPaymentMethod = (method) => {
    if (!method) return 'Unknown'
    return method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatStatus = (status) => {
    if (!status) return 'Pending'
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
