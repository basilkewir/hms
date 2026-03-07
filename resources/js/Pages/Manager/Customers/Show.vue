<template>
    <DashboardLayout title="Customer Details" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Customer Details</h1>
                    <p class="text-gray-600 mt-2">{{ customer.first_name }} {{ customer.last_name }}</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('manager.customers.edit', customer.id)" 
                          class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        Edit Customer
                    </Link>
                    <Link :href="route('manager.customers.index')" 
                          class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                        Back to Customers
                    </Link>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2 bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Customer Information</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Customer Code</label>
                        <p class="mt-1 text-sm text-gray-900">{{ customer.customer_code }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Status</label>
                        <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :class="customer.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                            {{ customer.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">First Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ customer.first_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Last Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ customer.last_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Email</label>
                        <p class="mt-1 text-sm text-gray-900">{{ customer.email || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Phone</label>
                        <p class="mt-1 text-sm text-gray-900">{{ customer.phone || 'N/A' }}</p>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-500">Address</label>
                        <p class="mt-1 text-sm text-gray-900">{{ customer.address || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">City</label>
                        <p class="mt-1 text-sm text-gray-900">{{ customer.city || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">State</label>
                        <p class="mt-1 text-sm text-gray-900">{{ customer.state || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Country</label>
                        <p class="mt-1 text-sm text-gray-900">{{ customer.country || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Postal Code</label>
                        <p class="mt-1 text-sm text-gray-900">{{ customer.postal_code || 'N/A' }}</p>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-500">Customer Group</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <span v-if="customer.customer_group">
                                {{ customer.customer_group.name }} ({{ customer.customer_group.discount_percentage }}% discount)
                            </span>
                            <span v-else>No Group</span>
                        </p>
                    </div>
                    <div class="col-span-2" v-if="customer.notes">
                        <label class="block text-sm font-medium text-gray-500">Notes</label>
                        <p class="mt-1 text-sm text-gray-900">{{ customer.notes }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Sales History</h2>
                <div v-if="customer.sales && customer.sales.length > 0" class="space-y-3">
                    <div v-for="sale in customer.sales.slice(0, 5)" :key="sale.id" class="border-b border-gray-200 pb-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ sale.sale_number }}</p>
                                <p class="text-xs text-gray-500">{{ new Date(sale.sale_date).toLocaleDateString() }}</p>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">{{ formatCurrency(sale.total_amount) }}</p>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-gray-500">No sales history</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { formatCurrency, initializeCurrencySettings } from '@/Utils/currency.js'
import { onMounted } from 'vue'

const props = defineProps({
    user: Object,
    customer: Object,
})

const navigation = computed(() => getNavigationForRole('manager'))

// Initialize currency settings on mount
onMounted(() => {
    initializeCurrencySettings()
})
</script>
