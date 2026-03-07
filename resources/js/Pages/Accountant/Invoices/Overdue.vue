<template>
    <DashboardLayout title="Overdue Invoices" :user="user">
        <!-- Header -->
        <div class="bg-gray-800 shadow rounded-lg p-6 mb-8 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">Overdue Invoices</h1>
                    <p class="text-gray-300 mt-2">Invoices that are past their due date and require immediate attention.</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('accountant.invoices.index')" 
                          class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                        Back to All Invoices
                    </Link>
                    <button @click="sendReminders" 
                            class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                        <EnvelopeIcon class="h-4 w-4 mr-2 inline" />
                        Send Reminders
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gray-800 rounded-lg shadow p-6 border border-gray-700">
                <div class="flex items-center">
                    <ExclamationTriangleIcon class="h-8 w-8 text-red-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-400">Overdue Invoices</p>
                        <p class="text-2xl font-bold text-white">{{ invoiceStats.overdue }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-800 rounded-lg shadow p-6 border border-gray-700">
                <div class="flex items-center">
                    <CurrencyDollarIcon class="h-8 w-8 text-yellow-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-400">Total Overdue Amount</p>
                        <p class="text-2xl font-bold text-white">{{ formatCurrency(invoiceStats.overdueAmount || 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-800 rounded-lg shadow p-6 border border-gray-700">
                <div class="flex items-center">
                    <ClockIcon class="h-8 w-8 text-purple-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-400">Average Days Overdue</p>
                        <p class="text-2xl font-bold text-white">{{ averageDaysOverdue }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overdue Invoices Table -->
        <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
            <div class="px-6 py-4 border-b border-gray-700">
                <h3 class="text-lg font-medium text-white">Overdue Invoice List</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Invoice</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Days Overdue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-for="invoice in filteredInvoices" :key="invoice.id" class="hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-white">{{ invoice.invoice_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-300">{{ invoice.customer_name }}</div>
                                <div class="text-xs text-gray-500">{{ invoice.customer_email || 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-300">{{ formatDate(invoice.due_date) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-white">{{ formatCurrency(invoice.balance_amount) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-900 text-red-200">
                                    {{ getDaysOverdue(invoice.due_date) }} days
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button @click="viewInvoice(invoice)" class="text-blue-400 hover:text-blue-300">View</button>
                                    <button @click="sendSingleReminder(invoice)" class="text-purple-400 hover:text-purple-300">Remind</button>
                                    <button @click="markAsPaid(invoice)" class="text-green-400 hover:text-green-300">Mark Paid</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="filteredInvoices.length === 0" class="text-center py-8">
                    <div class="text-gray-400">
                        <ExclamationTriangleIcon class="mx-auto h-12 w-12 text-gray-500" />
                        <h3 class="mt-2 text-sm font-medium text-white">No overdue invoices</h3>
                        <p class="mt-1 text-sm text-gray-400">Great! All invoices are paid or on time.</p>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'
import {
    ExclamationTriangleIcon,
    CurrencyDollarIcon,
    ClockIcon,
    EnvelopeIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    invoiceStats: Object,
    invoices: Array,
})

const invoiceStats = computed(() => props.invoiceStats || { overdue: 0, overdueAmount: 0 })
const invoices = computed(() => props.invoices || [])

const filteredInvoices = computed(() => {
    return invoices.value.filter(invoice => invoice.status === 'overdue')
})

const averageDaysOverdue = computed(() => {
    if (filteredInvoices.value.length === 0) return 0
    const totalDays = filteredInvoices.value.reduce((sum, invoice) => {
        return sum + getDaysOverdue(invoice.due_date)
    }, 0)
    return Math.round(totalDays / filteredInvoices.value.length)
})

const getDaysOverdue = (dueDate) => {
    if (!dueDate) return 0
    const today = new Date()
    const due = new Date(dueDate)
    const diffTime = Math.abs(today - due)
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
}

const formatDate = (date) => {
    return date ? new Date(date).toLocaleDateString() : 'N/A'
}

const sendReminders = () => {
    router.post(route('accountant.invoices.send-reminders'))
}

const sendSingleReminder = (invoice) => {
    if (confirm(`Send payment reminder to ${invoice.customer_name}?`)) {
        // TODO: Implement single reminder sending
        console.log('Sending reminder to:', invoice.customer_name)
    }
}

const viewInvoice = (invoice) => {
    router.get(route('accountant.invoices.show', invoice.id))
}

const markAsPaid = (invoice) => {
    if (confirm(`Mark invoice ${invoice.invoice_number} as paid?`)) {
        router.post(route('accountant.invoices.mark-paid', invoice.id))
    }
}
</script>
