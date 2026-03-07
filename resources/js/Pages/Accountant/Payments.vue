<template>
    <DashboardLayout title="Payment Management" :user="user" :navigation="navigation">
        <!-- Payments Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Payment Management</h1>
                    <p class="text-gray-600 mt-2">Process and manage all hotel payments and transactions.</p>
                </div>
                <div class="flex space-x-3">
                    <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        <PlusIcon class="h-4 w-4 mr-2 inline" />
                        New Payment
                    </button>
                    <button class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2 inline" />
                        Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <CreditCardIcon class="h-8 w-8 text-green-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Payments Received</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(paymentSummary.received || 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <ClockIcon class="h-8 w-8 text-yellow-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Pending Payments</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(paymentSummary.pending || 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <ExclamationTriangleIcon class="h-8 w-8 text-red-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Failed Payments</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(paymentSummary.failed || 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <ArrowPathIcon class="h-8 w-8 text-blue-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Refunds Issued</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(paymentSummary.refunds || 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Filters -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                    <select v-model="filters.method"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Methods</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="debit_card">Debit Card</option>
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select v-model="filters.status"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                    <select v-model="filters.dateRange"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="year">This Year</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" v-model="filters.search" placeholder="Search payments..."
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Payments</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Payment ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Guest/Customer
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
                        <tr v-for="payment in filteredPayments" :key="payment.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ payment.transaction_id || payment.id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ payment.guest_name || 'Guest' }}</div>
                                <div class="text-sm text-gray-500">{{ payment.reference || '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ formatCurrency(payment.amount || 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getMethodColor(payment.payment_method || 'unknown')">
                                    {{ formatMethod(payment.payment_method || 'unknown') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(payment.status)">
                                    {{ payment.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatDate(payment.date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button class="text-blue-600 hover:text-blue-900">View</button>
                                    <button v-if="payment.status === 'completed'" class="text-red-600 hover:text-red-900">Refund</button>
                                    <button v-if="payment.status === 'pending'" class="text-green-600 hover:text-green-900">Process</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { formatCurrency } from '@/Utils/currency.js'
import {
    CreditCardIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    ArrowPathIcon,
    PlusIcon,
    DocumentArrowDownIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    payments: Array,
})

const navigation = computed(() => getNavigationForRole('accountant'))

const filters = ref({
    method: '',
    status: '',
    dateRange: 'month',
    search: '',
})

const paymentSummary = computed(() => {
    const payments = props.payments || []
    const received = payments.filter(p => p.status === 'completed').reduce((sum, p) => sum + (p.amount || 0), 0)
    const pending = payments.filter(p => p.status === 'pending').reduce((sum, p) => sum + (p.amount || 0), 0)
    const failed = payments.filter(p => p.status === 'failed').reduce((sum, p) => sum + (p.amount || 0), 0)
    const refunds = payments.filter(p => p.type === 'refund').reduce((sum, p) => sum + (p.amount || 0), 0)
    
    return {
        received,
        pending,
        failed,
        refunds,
    }
})

const payments = computed(() => props.payments || [])

const filteredPayments = computed(() => {
    return payments.value.filter(payment => {
        const matchesMethod = !filters.value.method || payment.payment_method === filters.value.method
        const matchesStatus = !filters.value.status || payment.status === filters.value.status
        const matchesSearch = !filters.value.search || 
            (payment.guest_name && payment.guest_name.toLowerCase().includes(filters.value.search.toLowerCase())) ||
            (payment.transaction_id && payment.transaction_id.toLowerCase().includes(filters.value.search.toLowerCase()))
        
        return matchesMethod && matchesStatus && matchesSearch
    })
})

const getMethodColor = (method) => {
    const colors = {
        credit_card: 'bg-blue-100 text-blue-800',
        debit_card: 'bg-green-100 text-green-800',
        cash: 'bg-yellow-100 text-yellow-800',
        bank_transfer: 'bg-purple-100 text-purple-800',
    }
    return colors[method] || 'bg-gray-100 text-gray-800'
}

const getStatusColor = (status) => {
    const colors = {
        completed: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        failed: 'bg-red-100 text-red-800',
        refunded: 'bg-gray-100 text-gray-800',
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const formatMethod = (method) => {
    return method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (date) => {
    return date ? new Date(date).toLocaleDateString() : ''
}
</script>
