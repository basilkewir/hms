<template>
    <DashboardLayout title="Purchase Management" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Purchase Management</h1>
                    <p class="text-gray-600 mt-2">Manage hotel purchases, suppliers, and inventory orders.</p>
                </div>
                <!-- <Link :href="route('manager.purchases.create')"
                      class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    <PlusIcon class="h-4 w-4 mr-2 inline" />
                    New Purchase
                </Link> -->
                <button disabled class="bg-gray-400 text-white px-4 py-2 rounded-md cursor-not-allowed">
                    <PlusIcon class="h-4 w-4 mr-2 inline" />
                    New Purchase (Coming Soon)
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <ShoppingCartIcon class="h-8 w-8 text-blue-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Purchases</p>
                        <p class="text-2xl font-bold text-gray-900">{{ purchaseStats.total || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <ClockIcon class="h-8 w-8 text-yellow-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Pending</p>
                        <p class="text-2xl font-bold text-gray-900">{{ purchaseStats.pending || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <CheckCircleIcon class="h-8 w-8 text-green-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Received</p>
                        <p class="text-2xl font-bold text-gray-900">{{ purchaseStats.received || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <CurrencyDollarIcon class="h-8 w-8 text-purple-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Amount</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(purchaseStats.totalAmount || 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchases Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Purchases</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Purchase #
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Supplier
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Amount
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
                        <tr v-for="purchase in purchases" :key="purchase.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ purchase.purchase_number || purchase.id }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ purchase.supplier_name || purchase.supplier?.name || '—' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatDate(purchase.purchase_date || purchase.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatCurrency(purchase.total_amount || 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="getStatusStyle(purchase.status)">
                                    {{ formatStatus(purchase.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <!-- <Link :href="route('manager.purchases.show', purchase.id)"
                                          class="text-blue-600 hover:text-blue-900">View</Link> -->
                                    <span class="text-gray-400">View (Coming Soon)</span>
                                    <!-- <Link :href="route('manager.purchases.edit', purchase.id)"
                                          class="text-green-600 hover:text-green-900">Edit</Link> -->
                                    <span class="text-gray-400">Edit (Coming Soon)</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!purchases.length">
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                No purchases found. <!-- <Link :href="route('manager.purchases.create')" class="text-blue-600 hover:underline">Create one</Link>. -->
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { formatCurrency as formatCurrencyUtil } from '@/Utils/currency.js'
import {
    PlusIcon,
    ShoppingCartIcon,
    ClockIcon,
    CheckCircleIcon,
    CurrencyDollarIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    purchases: {
        type: Array,
        default: () => []
    },
    purchaseStats: {
        type: Object,
        default: () => ({})
    }
})

const navigation = computed(() => getNavigationForRole('manager'))

const formatCurrency = (amount) => formatCurrencyUtil(amount)

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString()
}

const formatStatus = (status) => {
    return (status || 'pending').replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getStatusStyle = (status) => {
    const s = status || 'pending'
    const map = {
        pending: { backgroundColor: '#fef3c7', color: '#92400e' },
        ordered: { backgroundColor: '#dbeafe', color: '#1e40af' },
        received: { backgroundColor: '#d1fae5', color: '#065f46' },
        cancelled: { backgroundColor: '#fee2e2', color: '#991b1b' }
    }
    return map[s] || { backgroundColor: '#f3f4f6', color: '#1f2937' }
}
</script>
