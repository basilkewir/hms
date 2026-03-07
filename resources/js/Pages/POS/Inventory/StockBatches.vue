<template>
    <DashboardLayout title="Stock Batches" :user="user" :navigation="navigation">
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Stock Batches</h1>
                    <p class="text-gray-600 mt-2">Track inventory batches, expiry dates, and stock movements.</p>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto">
            <!-- Filters -->
            <div class="mb-6 flex gap-4">
                <input
                    v-model="productSearch"
                    type="text"
                    placeholder="Search by product..."
                    class="flex-1 border-gray-300 rounded-md shadow-sm"
                    @input="handleSearch"
                />
                <label class="flex items-center">
                    <input v-model="expiringSoon" type="checkbox" @change="handleSearch" class="rounded border-gray-300" />
                    <span class="ml-2 text-sm text-gray-700">Expiring Soon (30 days)</span>
                </label>
            </div>

            <!-- Stock Batches Table -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Cost</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Cost</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Received Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="batch in batches.data" :key="batch.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ batch.batch_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ batch.product.name }}</div>
                                    <div class="text-sm text-gray-500">Code: {{ batch.product.code }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ batch.quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatCurrency(batch.unit_cost) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatCurrency(batch.quantity * batch.unit_cost) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatDate(batch.received_date) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span v-if="batch.expiry_date" :class="getExpiryClass(batch.expiry_date)">
                                        {{ formatDate(batch.expiry_date) }}
                                    </span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span v-if="batch.purchase_order" class="text-blue-600">
                                        {{ batch.purchase_order.po_number }}
                                    </span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        v-if="batch.expiry_date"
                                        :class="getStatusClass(batch.expiry_date)"
                                        class="px-2 py-1 text-xs font-medium rounded-full"
                                    >
                                        {{ getExpiryStatus(batch.expiry_date) }}
                                    </span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="!batches.data || batches.data.length === 0" class="text-center py-12 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                        />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No stock batches</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Stock batches will appear here when you receive purchase orders.
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="batches.links" class="mt-4">
                <Pagination :links="batches.links" :meta="batches" />
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { router } from '@inertiajs/vue3';
import { getNavigationForRole } from '@/Utils/navigation.js';
import { formatCurrency, initializeCurrencySettings } from '@/Utils/currency.js';

const props = defineProps({
    user: Object,
    batches: Object,
    filters: Object
});

const navigation = computed(() => {
    // Determine role from user
    const userRole = props.user?.roles?.[0]?.name || 'manager';
    return getNavigationForRole(userRole);
});

const productSearch = ref(props.filters?.product_id || '');
const expiringSoon = ref(props.filters?.expiring_soon || false);

const handleSearch = () => {
    router.get(route('pos.stock-batches'), {
        product_id: productSearch.value,
        expiring_soon: expiringSoon.value
    }, {
        preserveState: true,
        replace: true
    });
};

// Initialize currency settings on mount
onMounted(() => {
    initializeCurrencySettings();
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const getExpiryClass = (expiryDate) => {
    const expiry = new Date(expiryDate);
    const today = new Date();
    const daysUntilExpiry = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));
    
    if (daysUntilExpiry < 0) {
        return 'text-red-600 font-bold';
    } else if (daysUntilExpiry <= 30) {
        return 'text-yellow-600 font-semibold';
    }
    return 'text-gray-900';
};

const getExpiryStatus = (expiryDate) => {
    const expiry = new Date(expiryDate);
    const today = new Date();
    const daysUntilExpiry = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));
    
    if (daysUntilExpiry < 0) {
        return 'Expired';
    } else if (daysUntilExpiry <= 30) {
        return `Expiring in ${daysUntilExpiry} days`;
    }
    return 'Active';
};

const getStatusClass = (expiryDate) => {
    const expiry = new Date(expiryDate);
    const today = new Date();
    const daysUntilExpiry = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));
    
    if (daysUntilExpiry < 0) {
        return 'bg-red-100 text-red-800';
    } else if (daysUntilExpiry <= 30) {
        return 'bg-yellow-100 text-yellow-800';
    }
    return 'bg-green-100 text-green-800';
};
</script>
