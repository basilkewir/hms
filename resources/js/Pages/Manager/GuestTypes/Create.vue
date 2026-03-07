<template>
    <DashboardLayout title="Create Guest Type" :user="user" :navigation="navigation">
        <div class="bg-white shadow rounded-lg p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Create New Guest Type</h1>
            <form @submit.prevent="submit">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                        <input v-model="form.name" type="text" required 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Code</label>
                        <input v-model="form.code" type="text" 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="e.g., VIP, CORP, REG">
                        <div v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea v-model="form.description" rows="3"
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                            <div class="flex items-center gap-3">
                                <input v-model="form.color" type="color" 
                                       class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                                <input v-model="form.color" type="text" 
                                       placeholder="#000000"
                                       class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Color for UI display</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Discount Percentage</label>
                            <input v-model.number="form.discount_percentage" type="number" step="0.01" min="0" max="100"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500">Discount percentage for this guest type (0-100). This discount will be automatically applied in POS and reservations if enabled in settings.</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                        <input v-model.number="form.sort_order" type="number" 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center">
                        <input v-model="form.is_active" type="checkbox" class="mr-2">
                        <label class="text-sm font-medium text-gray-700">Active</label>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="submit" :disabled="form.processing" 
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50">
                        Create Guest Type
                    </button>
                    <Link :href="route('manager.guest-types.index')" 
                          class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'

const props = defineProps({
    user: Object,
})

const navigation = computed(() => getNavigationForRole('manager'))

const form = useForm({
    name: '',
    code: '',
    description: '',
    color: '#3B82F6',
    discount_percentage: 0,
    is_active: true,
    sort_order: 0,
})

const submit = () => {
    form.post(route('manager.guest-types.store'))
}
</script>
