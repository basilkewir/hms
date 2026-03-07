<template>
    <DashboardLayout title="New Purchase" :user="user" :navigation="navigation">
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create New Purchase</h1>
                    <p class="text-gray-600 mt-2">Add a new purchase order to the system.</p>
                </div>
                <!-- <Link :href="route('manager.purchases.index')"
                      class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                    <ArrowLeftIcon class="h-4 w-4 mr-2 inline" />
                    Back to Purchases
                </Link> -->
                <button disabled class="bg-gray-400 text-white px-4 py-2 rounded-md cursor-not-allowed">
                    <ArrowLeftIcon class="h-4 w-4 mr-2 inline" />
                    Back to Purchases (Coming Soon)
                </button>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <h3 class="text-lg font-medium mb-4 text-gray-900">Purchase Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700">Supplier *</label>
                            <select v-model="form.supplier_id" required
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Supplier</option>
                                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                    {{ supplier.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.supplier_id" class="mt-1 text-sm text-red-600">{{ form.errors.supplier_id }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700">Purchase Date *</label>
                            <div class="relative">
                                <input ref="purchaseDateInput" v-model="form.purchase_date" type="date" required
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                                <div class="absolute inset-0 cursor-pointer" @click="purchaseDateInput?.showPicker ? purchaseDateInput.showPicker() : purchaseDateInput?.focus()"></div>
                            </div>
                            <div v-if="form.errors.purchase_date" class="mt-1 text-sm text-red-600">{{ form.errors.purchase_date }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700">Status</label>
                            <select v-model="form.status"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="pending">Pending</option>
                                <option value="ordered">Ordered</option>
                                <option value="received">Received</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700">Notes</label>
                            <textarea v-model="form.notes" rows="2"
                                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                      placeholder="Optional notes"></textarea>
                            <div v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium mb-4 text-gray-900">Items & Total</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700">Total Amount *</label>
                            <input v-model="form.total_amount" type="number" step="0.01" min="0" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.total_amount" class="mt-1 text-sm text-red-600">{{ form.errors.total_amount }}</div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <Link :href="route('manager.purchases.index')"
                          class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300">
                        Cancel
                    </Link>
                    <button type="submit"
                            :disabled="form.processing"
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50">
                        {{ form.processing ? 'Saving...' : 'Create Purchase' }}
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    suppliers: {
        type: Array,
        default: () => []
    }
})

const navigation = computed(() => getNavigationForRole('manager'))

const form = useForm({
    supplier_id: '',
    purchase_date: new Date().toISOString().slice(0, 10),
    status: 'pending',
    notes: '',
    total_amount: ''
})

const submit = () => {
    // form.post(route('manager.purchases.store'), {
    //     preserveScroll: true
    // })
    alert('Purchase creation functionality coming soon!')
}
</script>
