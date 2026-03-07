<template>
    <DashboardLayout title="Inventory Request" :user="user">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Inventory Request</h1>
                    <p class="text-gray-600 mt-2">Request housekeeping supplies and inventory items.</p>
                </div>
            </div>
        </div>

        <!-- Request Form -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">New Inventory Request</h3>
            <form @submit.prevent="submitRequest" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Item Category</label>
                        <select v-model="form.category" @change="updateItems" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Category</option>
                            <option value="cleaning">Cleaning Supplies</option>
                            <option value="linens">Linens & Towels</option>
                            <option value="amenities">Guest Amenities</option>
                            <option value="equipment">Equipment</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Item</label>
                        <select v-model="form.item" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Item</option>
                            <option v-for="item in availableItems" :key="item.id" :value="item.id">
                                {{ item.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                        <input type="number" v-model="form.quantity" required min="1"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                        <select v-model="form.priority" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea v-model="form.notes" rows="3" placeholder="Additional notes or special requirements..."
                              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" :disabled="isSubmitting"
                            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50">
                        <span v-if="isSubmitting">Submitting...</span>
                        <span v-else>Submit Request</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- My Requests -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">My Recent Requests</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Item
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quantity
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Priority
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="request in myRequests" :key="request.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ request.itemName }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ request.quantity }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getPriorityColor(request.priority)">
                                    {{ request.priority }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(request.status)">
                                    {{ request.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDate(request.date) }}
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
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

const props = defineProps({
    user: Object,
    myRequests: Array,
})

const isSubmitting = ref(false)

const inventoryItems = {
    cleaning: [
        { id: 1, name: 'All-Purpose Cleaner' },
        { id: 2, name: 'Toilet Paper' },
        { id: 3, name: 'Paper Towels' },
        { id: 4, name: 'Vacuum Bags' },
    ],
    linens: [
        { id: 5, name: 'Bed Sheets' },
        { id: 6, name: 'Pillowcases' },
        { id: 7, name: 'Bath Towels' },
        { id: 8, name: 'Hand Towels' },
    ],
    amenities: [
        { id: 9, name: 'Shampoo' },
        { id: 10, name: 'Soap' },
        { id: 11, name: 'Coffee Pods' },
        { id: 12, name: 'Tea Bags' },
    ],
    equipment: [
        { id: 13, name: 'Vacuum Cleaner' },
        { id: 14, name: 'Cleaning Cart' },
        { id: 15, name: 'Iron' },
        { id: 16, name: 'Hair Dryer' },
    ],
}

const form = ref({
    category: '',
    item: '',
    quantity: 1,
    priority: 'medium',
    notes: '',
})

const availableItems = computed(() => {
    return inventoryItems[form.value.category] || []
})

const myRequests = computed(() => props.myRequests || [])

const updateItems = () => {
    form.value.item = ''
}

const submitRequest = () => {
    const selectedItem = availableItems.value.find(item => item.id === form.value.item)
    if (!selectedItem) {
        alert('Please select an item to request.')
        return
    }

    isSubmitting.value = true
    router.post(route('housekeeping.inventory.request.store'), {
        category: form.value.category,
        item_name: selectedItem.name,
        quantity: form.value.quantity,
        priority: form.value.priority,
        notes: form.value.notes,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isSubmitting.value = false
            form.value = {
                category: '',
                item: '',
                quantity: 1,
                priority: 'medium',
                notes: '',
            }
        },
        onError: () => {
            isSubmitting.value = false
        }
    })
}

const getPriorityColor = (priority) => {
    const colors = {
        low: 'bg-gray-100 text-gray-800',
        medium: 'bg-blue-100 text-blue-800',
        high: 'bg-yellow-100 text-yellow-800',
        urgent: 'bg-red-100 text-red-800',
    }
    return colors[(priority || '').toLowerCase()] || 'bg-gray-100 text-gray-800'
}

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        delivered: 'bg-blue-100 text-blue-800',
        rejected: 'bg-red-100 text-red-800',
    }
    return colors[(status || '').toLowerCase()] || 'bg-gray-100 text-gray-800'
}

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString()
}
</script>
