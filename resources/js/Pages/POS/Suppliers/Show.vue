<template>
    <DashboardLayout title="Supplier Details" :user="user" :navigation="navigation">
        <div class="mb-6">
            <Link :href="route('pos.suppliers.index')" class="text-kotel-sky-blue hover:text-kotel-yellow mb-4 inline-block">
                ← Back to Suppliers
            </Link>
        </div>
                <!-- Supplier Info Card -->
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Supplier Information</h3>
                    </div>
                    <div class="px-6 py-4 grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Contact Person</label>
                            <p class="text-sm text-gray-900">{{ supplier.contact_person || '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Email</label>
                            <p class="text-sm text-gray-900">{{ supplier.email || '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Phone</label>
                            <p class="text-sm text-gray-900">{{ supplier.phone || '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Credit Limit</label>
                            <p class="text-sm text-gray-900">{{ formatCurrency(supplier.credit_limit) }}</p>
                        </div>
                        <div class="col-span-2">
                            <label class="text-sm font-medium text-gray-500">Address</label>
                            <p class="text-sm text-gray-900">{{ supplier.address || '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="grid grid-cols-3 gap-6 mb-6">
                    <div class="bg-white shadow rounded-lg p-6">
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Total Purchases</h4>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(totalPurchases) }}</p>
                    </div>
                    <div class="bg-white shadow rounded-lg p-6">
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Total Paid</h4>
                        <p class="text-2xl font-bold text-green-600">{{ formatCurrency(totalPaid) }}</p>
                    </div>
                    <div class="bg-white shadow rounded-lg p-6">
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Pending Amount</h4>
                        <p class="text-2xl font-bold text-red-600">{{ formatCurrency(totalPending) }}</p>
                    </div>
                </div>

                <!-- Recent Purchase Orders -->
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Recent Purchase Orders</h3>
                        <Link :href="route('pos.purchases')" class="text-sm text-blue-600 hover:text-blue-900">
                            View All →
                        </Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="po in supplier.purchase_orders" :key="po.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ po.po_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDate(po.order_date) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatCurrency(po.total_amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatCurrency(po.paid_amount || 0) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getStatusClass(po.status)"
                                              class="px-2 py-1 text-xs font-medium rounded-full">
                                            {{ po.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="!supplier.purchase_orders || supplier.purchase_orders.length === 0" class="text-center py-8 text-gray-500">
                        <p class="text-sm">No purchase orders found for this supplier.</p>
                    </div>
                </div>

                <!-- Recent Payments -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Recent Payments</h3>
                        <Link :href="route('pos.suppliers.payments', supplier.id)" class="text-sm text-blue-600 hover:text-blue-900">
                            View All →
                        </Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="payment in supplier.payments" :key="payment.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ payment.payment_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDate(payment.payment_date) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatCurrency(payment.amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="capitalize">{{ payment.payment_type }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="capitalize">{{ payment.payment_method.replace('_', ' ') }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="!supplier.payments || supplier.payments.length === 0" class="text-center py-8 text-gray-500">
                        <p class="text-sm">No payments recorded for this supplier.</p>
                    </div>
                </div>
    </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { formatCurrency, initializeCurrencySettings } from '@/Utils/currency.js';

const props = defineProps({
    user: Object,
    navigation: Array,
    supplier: Object,
    totalPurchases: Number,
    totalPaid: Number,
    totalPending: Number
});


const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const getStatusClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-blue-100 text-blue-800',
        'received': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

// Initialize currency settings on mount
onMounted(() => {
    initializeCurrencySettings();
});
</script>
