<template>
    <DashboardLayout title="Transaction Management" :user="user" :navigation="navigation">
        <!-- Transactions Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Transaction Management</h1>
                    <p class="text-gray-600 mt-2">Monitor and manage all financial transactions.</p>
                </div>
                <div class="flex space-x-3">
                    <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        <PlusIcon class="h-4 w-4 mr-2 inline" />
                        New Transaction
                    </button>
                    <button class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2 inline" />
                        Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Transaction Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <CurrencyDollarIcon class="h-8 w-8 text-green-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Income</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(summary.totalIncome || 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <MinusIcon class="h-8 w-8 text-red-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Expenses</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(summary.totalExpenses || 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <ChartBarIcon class="h-8 w-8 text-blue-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Net Profit</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(summary.netProfit || 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <ClockIcon class="h-8 w-8 text-yellow-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Pending</p>
                        <p class="text-2xl font-bold text-gray-900">{{ summary.pendingCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Type</label>
                    <select v-model="filters.type"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Types</option>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select v-model="filters.category"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Categories</option>
                        <option value="room_revenue">Room Revenue</option>
                        <option value="food_beverage">Food & Beverage</option>
                        <option value="utilities">Utilities</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="payroll">Payroll</option>
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
                    </select>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Transactions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Description
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="transaction in transactions" :key="transaction.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatDate(transaction.date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ transaction.guest_name || transaction.description }}</div>
                                <div class="text-sm text-gray-500">{{ transaction.reference || transaction.transaction_id }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getCategoryColor(transaction.category || 'other')">
                                    {{ formatCategory(transaction.category || 'other') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getTypeColor(transaction.type)">
                                    {{ transaction.type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'">
                                {{ transaction.type === 'income' ? '+' : '-' }}{{ formatCurrency(transaction.amount || 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(transaction.status)">
                                    {{ transaction.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button class="text-blue-600 hover:text-blue-900">View</button>
                                    <button class="text-green-600 hover:text-green-900">Edit</button>
                                    <button class="text-red-600 hover:text-red-900">Delete</button>
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
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { formatCurrency } from '@/Utils/currency.js'
import {
    CurrencyDollarIcon,
    MinusIcon,
    ChartBarIcon,
    ClockIcon,
    PlusIcon,
    DocumentArrowDownIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    transactionStats: Object,
    recentTransactions: Object, // Changed to Object for pagination
    filters: Object,
})

const navigation = computed(() => getNavigationForRole('accountant'))

const filters = ref({
    dateRange: 'month',
    type: '',
    category: '',
    status: '',
})

const summary = computed(() => props.transactionStats || {
    totalIncome: 0,
    totalExpenses: 0,
    netProfit: 0,
    pendingCount: 0,
})

const transactions = computed(() => props.recentTransactions?.data || [])
const pagination = computed(() => ({
    current_page: props.recentTransactions?.current_page || 1,
    last_page: props.recentTransactions?.last_page || 1,
    per_page: props.recentTransactions?.per_page || 15,
    total: props.recentTransactions?.total || 0,
    from: props.recentTransactions?.from || 0,
    to: props.recentTransactions?.to || 0,
}))

// Remove client-side filtering since it's now done on server
const filteredTransactions = computed(() => transactions.value)

const getCategoryColor = (category) => {
    const colors = {
        room_revenue: 'bg-blue-100 text-blue-800',
        food_beverage: 'bg-green-100 text-green-800',
        utilities: 'bg-yellow-100 text-yellow-800',
        maintenance: 'bg-red-100 text-red-800',
        payroll: 'bg-purple-100 text-purple-800',
    }
    return colors[category] || 'bg-gray-100 text-gray-800'
}

const getTypeColor = (type) => {
    return type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
}

const getStatusColor = (status) => {
    const colors = {
        completed: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        failed: 'bg-red-100 text-red-800',
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const formatCategory = (category) => {
    return category.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (date) => {
    return date ? new Date(date).toLocaleDateString() : ''
}
</script>
