<template>
    <DashboardLayout title="Supplier Payments" :user="user" :navigation="navigation">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-white mb-2">Payments: {{ supplier.name }}</h1>
                <Link :href="route('manager.suppliers.index', supplier.id)" class="text-kotel-sky-blue hover:text-kotel-yellow">
                    ← Back to Supplier
                </Link>
            </div>
            <button
                @click="showAddPayment = true"
                class="bg-kotel-yellow hover:bg-kotel-yellow/80 text-kotel-black px-4 py-2 rounded-md text-sm font-medium"
            >
                Add Payment
            </button>
        </div>
        
        <!-- Payments Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md mb-6">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Payment History</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="payment in payments.data" :key="payment.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ payment.payment_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDate(payment.payment_date) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span v-if="payment.purchase_order" class="text-blue-600">
                                            {{ payment.purchase_order.po_number }}
                                        </span>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                        {{ formatCurrency(payment.amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="capitalize">{{ payment.payment_type }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="capitalize">{{ payment.payment_method.replace('_', ' ') }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ payment.reference_number || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button @click="deletePayment(payment)" class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="!payments.data || payments.data.length === 0" class="text-center py-12 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No payments recorded</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by recording a payment to this supplier.</p>
                        <div class="mt-6">
                            <button
                                @click="showAddPayment = true"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700"
                            >
                                Add Payment
                            </button>
                        </div>
                    </div>
                </div>

        <!-- Pagination -->
        <div v-if="payments.links" class="mt-4">
            <Pagination :links="payments.links" :meta="payments" />
        </div>

        <!-- Add Payment Modal -->
        <DialogModal :show="showAddPayment" @close="showAddPayment = false">
            <template #title>Add Payment</template>
            <template #content>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Purchase Order (Optional)</label>
                        <select v-model="paymentForm.purchase_order_id" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option :value="null">Select Purchase Order...</option>
                            <option v-for="po in purchaseOrders" :key="po.id" :value="po.id">
                                {{ po.po_number }} - {{ formatCurrency(po.remaining_amount) }} remaining
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Type *</label>
                        <select v-model="paymentForm.payment_type" class="w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="partial">Partial</option>
                            <option value="full">Full</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount *</label>
                        <input v-model="paymentForm.amount" type="number" step="0.01" class="w-full border-gray-300 rounded-md shadow-sm" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method *</label>
                        <select v-model="paymentForm.payment_method" class="w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="credit_card">Credit Card</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Date *</label>
                        <div class="relative">
                            <input ref="paymentDateInput" v-model="paymentForm.payment_date" type="date" class="w-full border-gray-300 rounded-md shadow-sm cursor-pointer" required />
                            <div class="absolute inset-0 cursor-pointer" @click="paymentDateInput?.showPicker ? paymentDateInput.showPicker() : paymentDateInput?.focus()"></div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                        <input v-model="paymentForm.reference_number" type="text" class="w-full border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea v-model="paymentForm.notes" rows="3" class="w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>
                </div>
            </template>
            <template #footer>
                <button @click="showAddPayment = false" class="mr-3 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button @click="savePayment" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700">
                    Record Payment
                </button>
            </template>
        </DialogModal>
    </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DialogModal from '@/Components/DialogModal.vue';
import Pagination from '@/Components/Pagination.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, reactive, onMounted } from 'vue';
import { formatCurrency, initializeCurrencySettings } from '@/Utils/currency.js';

const props = defineProps({
    user: Object,
    navigation: Array,
    supplier: Object,
    payments: Object,
    purchaseOrders: Array
});

const showAddPayment = ref(false);

const paymentForm = reactive({
    purchase_order_id: null,
    payment_type: 'partial',
    amount: '',
    payment_method: 'cash',
    payment_date: new Date().toISOString().split('T')[0],
    reference_number: '',
    notes: ''
});

const savePayment = () => {
    router.post(route('manager.suppliers.payments.store', props.supplier.id), paymentForm, {
        preserveScroll: true,
        onSuccess: () => {
            showAddPayment.value = false;
            Object.assign(paymentForm, {
                purchase_order_id: null,
                payment_type: 'partial',
                amount: '',
                payment_method: 'cash',
                payment_date: new Date().toISOString().split('T')[0],
                reference_number: '',
                notes: ''
            });
        }
    });
};

const deletePayment = (payment) => {
    if (confirm(`Are you sure you want to delete payment ${payment.payment_number}?`)) {
        router.delete(route('manager.suppliers.payments.destroy', payment.id), {
            preserveScroll: true
        });
    }
};


const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

// Initialize currency settings on mount
onMounted(() => {
    initializeCurrencySettings();
});
</script>
