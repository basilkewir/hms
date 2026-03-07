<template>
    <DashboardLayout title="Paid Invoices" :user="user">
        <!-- Header -->
        <div class="bg-gray-800 shadow rounded-lg p-6 mb-8 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">Paid Invoices</h1>
                    <p class="text-gray-300 mt-2">Successfully paid invoices and payment history.</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('accountant.invoices.index')" 
                          class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                        Back to All Invoices
                    </Link>
                    <button @click="exportPaidInvoices" 
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2 inline" />
                        Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gray-800 rounded-lg shadow p-6 border border-gray-700">
                <div class="flex items-center">
                    <CheckCircleIcon class="h-8 w-8 text-green-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-400">Paid Invoices</p>
                        <p class="text-2xl font-bold text-white">{{ invoiceStats.paid }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-800 rounded-lg shadow p-6 border border-gray-700">
                <div class="flex items-center">
                    <CurrencyDollarIcon class="h-8 w-8 text-green-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-400">Total Paid Amount</p>
                        <p class="text-2xl font-bold text-white">{{ formatCurrency(invoiceStats.paidAmount || 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-800 rounded-lg shadow p-6 border border-gray-700">
                <div class="flex items-center">
                    <ChartBarIcon class="h-8 w-8 text-blue-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-400">Average Payment</p>
                        <p class="text-2xl font-bold text-white">{{ averagePaymentAmount }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-800 rounded-lg shadow p-6 border border-gray-700">
                <div class="flex items-center">
                    <CalendarIcon class="h-8 w-8 text-purple-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-400">This Month</p>
                        <p class="text-2xl font-bold text-white">{{ thisMonthPaid }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paid Invoices Table -->
        <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
            <div class="px-6 py-4 border-b border-gray-700">
                <h3 class="text-lg font-medium text-white">Payment History</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Invoice</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Paid Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Payment Method</th>
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
                                <div class="text-sm text-gray-300">{{ formatDate(invoice.paid_date) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-green-400">{{ formatCurrency(invoice.total_amount) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-200">
                                    {{ getPaymentMethod(invoice) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button @click="viewInvoice(invoice)" class="text-blue-400 hover:text-blue-300">View</button>
                                    <button @click="downloadReceipt(invoice)" class="text-purple-400 hover:text-purple-300">Receipt</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="filteredInvoices.length === 0" class="text-center py-8">
                    <div class="text-gray-400">
                        <CheckCircleIcon class="mx-auto h-12 w-12 text-gray-500" />
                        <h3 class="mt-2 text-sm font-medium text-white">No paid invoices</h3>
                        <p class="mt-1 text-sm text-gray-400">No invoices have been paid yet.</p>
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
    CheckCircleIcon,
    CurrencyDollarIcon,
    ChartBarIcon,
    CalendarIcon,
    DocumentArrowDownIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    invoiceStats: Object,
    invoices: Array,
})

const invoiceStats = computed(() => props.invoiceStats || { paid: 0, paidAmount: 0 })
const invoices = computed(() => props.invoices || [])

const filteredInvoices = computed(() => {
    return invoices.value.filter(invoice => invoice.status === 'paid')
})

const averagePaymentAmount = computed(() => {
    if (filteredInvoices.value.length === 0) return 0
    const total = filteredInvoices.value.reduce((sum, invoice) => sum + (invoice.total_amount || 0), 0)
    return formatCurrency(total / filteredInvoices.value.length)
})

const thisMonthPaid = computed(() => {
    const now = new Date()
    const currentMonth = now.getMonth()
    const currentYear = now.getFullYear()
    
    return filteredInvoices.value.filter(invoice => {
        if (!invoice.paid_date) return false
        const paidDate = new Date(invoice.paid_date)
        return paidDate.getMonth() === currentMonth && paidDate.getFullYear() === currentYear
    }).length
})

const formatDate = (date) => {
    return date ? new Date(date).toLocaleDateString() : 'N/A'
}

const getPaymentMethod = (invoice) => {
    // This would come from payment data, for now we'll return a default
    return invoice.payment_method || 'Unknown'
}

const exportPaidInvoices = () => {
    // TODO: Implement export functionality
    console.log('Exporting paid invoices...')
}

const viewInvoice = (invoice) => {
    router.get(route('accountant.invoices.show', invoice.id))
}

const downloadReceipt = (invoice) => {
    // TODO: Implement receipt download
    console.log('Downloading receipt for:', invoice.invoice_number)
}
</script>
