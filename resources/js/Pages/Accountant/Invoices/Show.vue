<template>
    <DashboardLayout title="Invoice Details" :user="user">
        <div class="bg-gray-800 shadow rounded-lg p-6 mb-8 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">Invoice {{ invoice.invoice_number }}</h1>
                    <p class="text-gray-300 mt-2">Issued {{ formatDate(invoice.issue_date) }} • Due {{ formatDate(invoice.due_date) }}</p>
                </div>
                <Link :href="route('accountant.invoices.index')" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                    Back to Invoices
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-gray-800 shadow rounded-lg p-6 border border-gray-700">
                <p class="text-sm text-gray-400">Customer</p>
                <p class="text-lg font-semibold text-white">{{ invoice.customer_name }}</p>
                <p class="text-sm text-gray-400">{{ invoice.customer_email || 'N/A' }}</p>
                <p class="text-sm text-gray-400 mt-2">Room {{ invoice.room_number }}</p>
            </div>
            <div class="bg-gray-800 shadow rounded-lg p-6 border border-gray-700">
                <p class="text-sm text-gray-400">Total Amount</p>
                <p class="text-lg font-semibold text-white">{{ formatCurrency(invoice.total_amount || 0) }}</p>
                <p class="text-sm text-gray-400 mt-2">Paid {{ formatCurrency(invoice.paid_amount || 0) }}</p>
                <p class="text-sm text-gray-400">Balance {{ formatCurrency(invoice.balance_amount || 0) }}</p>
            </div>
            <div class="bg-gray-800 shadow rounded-lg p-6 border border-gray-700">
                <p class="text-sm text-gray-400">Status</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="getStatusColor(invoice.status)">
                    {{ formatStatus(invoice.status) }}
                </span>
                <p class="text-sm text-gray-400 mt-4">Notes</p>
                <p class="text-sm text-gray-300">{{ invoice.notes || 'N/A' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-gray-800 shadow rounded-lg p-6 border border-gray-700">
                <h3 class="text-lg font-medium text-white mb-4">Charges</h3>
                <div v-if="invoice.charges.length === 0" class="text-sm text-gray-400">No charges recorded.</div>
                <ul v-else class="space-y-3">
                    <li v-for="charge in invoice.charges" :key="charge.id" class="flex justify-between text-sm text-gray-300">
                        <span>{{ charge.description }}</span>
                        <span>{{ formatCurrency(charge.amount || 0) }}</span>
                    </li>
                </ul>
            </div>

            <div class="bg-gray-800 shadow rounded-lg p-6 border border-gray-700">
                <h3 class="text-lg font-medium text-white mb-4">Payments</h3>
                <div v-if="invoice.payments.length === 0" class="text-sm text-gray-400">No payments recorded.</div>
                <ul v-else class="space-y-3">
                    <li v-for="payment in invoice.payments" :key="payment.id" class="flex justify-between text-sm text-gray-300">
                        <span>{{ formatStatus(payment.status) }} • {{ formatMethod(payment.method) }}</span>
                        <span>{{ formatCurrency(payment.amount || 0) }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'

const props = defineProps({
    user: Object,
    invoice: Object,
})

const invoice = computed(() => props.invoice || {
    charges: [],
    payments: [],
})

const formatDate = (date) => {
    return date ? new Date(date).toLocaleDateString() : 'N/A'
}

const formatStatus = (status) => {
    return (status || '').replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatMethod = (method) => {
    return (method || 'unknown').replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getStatusColor = (status) => {
    const colors = {
        sent: 'bg-blue-900 text-blue-200',
        overdue: 'bg-red-900 text-red-200',
        paid: 'bg-green-900 text-green-200',
    }
    return colors[status] || 'bg-gray-700 text-gray-300'
}
</script>
