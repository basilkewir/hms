<template>
    <DashboardLayout title="Purchase Details" :user="user" :navigation="navigation">
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Purchase #{{ purchase.purchase_number || purchase.id }}</h1>
                    <p class="mt-2 text-gray-600">
                        Status:
                        <span class="px-2 py-1 text-xs rounded-full" :style="getStatusStyle(purchase.status)">
                            {{ formatStatus(purchase.status) }}
                        </span>
                    </p>
                </div>
                <div class="flex space-x-3">
                    <!-- <Link :href="route('manager.purchases.edit', purchase.id)"
                          class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700">
                        Edit
                    </Link> -->
                    <button disabled class="bg-gray-400 text-white px-4 py-2 rounded-md cursor-not-allowed">
                        Edit (Coming Soon)
                    </button>
                    <!-- <Link :href="route('manager.purchases.index')"
                          class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                        <ArrowLeftIcon class="h-4 w-4 mr-2 inline" />
                        Back
                    </Link> -->
                    <button disabled class="bg-gray-400 text-white px-4 py-2 rounded-md cursor-not-allowed">
                        <ArrowLeftIcon class="h-4 w-4 mr-2 inline" />
                        Back (Coming Soon)
                    </button>
                </div>
            </div>

            <!-- Purchase Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="rounded-lg p-4 bg-gray-50">
                    <h3 class="font-semibold mb-3 text-gray-900">Supplier & Date</h3>
                    <div class="space-y-2 text-sm">
                        <div class="text-gray-700">
                            <span class="font-medium text-gray-900">Supplier:</span>
                            {{ purchase.supplier_name || purchase.supplier?.name || '—' }}
                        </div>
                        <div class="text-gray-700">
                            <span class="font-medium text-gray-900">Purchase Date:</span>
                            {{ formatDate(purchase.purchase_date || purchase.created_at) }}
                        </div>
                        <div class="text-gray-700">
                            <span class="font-medium text-gray-900">Created:</span>
                            {{ formatDateTime(purchase.created_at) }}
                        </div>
                    </div>
                </div>
                <div class="rounded-lg p-4 bg-gray-50">
                    <h3 class="font-semibold mb-3 text-gray-900">Amount & Status</h3>
                    <div class="space-y-2 text-sm">
                        <div class="text-gray-700">
                            <span class="font-medium text-gray-900">Total Amount:</span>
                            {{ formatCurrency(purchase.total_amount || 0) }}
                        </div>
                        <div class="text-gray-700">
                            <span class="font-medium text-gray-900">Status:</span>
                            <span class="px-2 py-0.5 rounded text-xs font-medium" :style="getStatusStyle(purchase.status)">
                                {{ formatStatus(purchase.status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="purchase.notes" class="mb-6">
                <h3 class="font-semibold mb-3 text-gray-900">Notes</h3>
                <p class="rounded-lg p-4 bg-gray-50 text-gray-700">{{ purchase.notes }}</p>
            </div>

            <!-- Items (if present) -->
            <div v-if="purchase.items && purchase.items.length" class="mb-6">
                <h3 class="font-semibold mb-3 text-gray-900">Items</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(item, idx) in purchase.items" :key="idx">
                                <td class="px-4 py-2 text-sm text-gray-900">{{ item.name || item.description || '—' }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ item.quantity ?? '—' }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ formatCurrency(item.unit_price || 0) }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ formatCurrency((item.quantity || 0) * (item.unit_price || 0)) }}</td>
                            </tr>
                        </tbody>
                    </table>
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
import { formatCurrency as formatCurrencyUtil } from '@/Utils/currency.js'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    purchase: Object
})

const navigation = computed(() => getNavigationForRole('manager'))

const formatCurrency = (amount) => formatCurrencyUtil(amount)

const formatDate = (dateString) => {
    if (!dateString) return '—'
    return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const formatDateTime = (dateString) => {
    if (!dateString) return '—'
    return new Date(dateString).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })
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
