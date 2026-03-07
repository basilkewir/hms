<template>
    <DashboardLayout title="Guest Types" :user="user" :navigation="navigation">
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Guest Types Management</h1>
                    <p class="text-gray-600 mt-1">Manage guest types and their configurations</p>
                </div>
                <Link :href="route('manager.guest-types.create')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Add Guest Type
                </Link>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Color</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guests</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="guestType in guestTypes" :key="guestType.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ guestType.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ guestType.code || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span v-if="guestType.color" 
                                      class="inline-block w-6 h-6 rounded-full border-2 border-gray-300"
                                      :style="{ backgroundColor: guestType.color }"
                                      :title="guestType.color"></span>
                                <span v-else class="text-sm text-gray-400">-</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span v-if="guestType.discount_percentage > 0" class="text-green-600 font-semibold">
                                    {{ guestType.discount_percentage }}%
                                </span>
                                <span v-else class="text-gray-400">0%</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ guestType.description || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ guestType.guest_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                      :class="guestType.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    {{ guestType.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link :href="route('manager.guest-types.edit', guestType.id)" class="text-blue-600 hover:text-blue-900 mr-3">Edit</Link>
                                <button @click="deleteGuestType(guestType)" class="text-red-600 hover:text-red-900">Delete</button>
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
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'

const props = defineProps({
    user: Object,
    guestTypes: Array,
})

const navigation = computed(() => getNavigationForRole('manager'))

const deleteGuestType = (guestType) => {
    if (confirm(`Are you sure you want to delete ${guestType.name}?`)) {
        router.delete(route('manager.guest-types.destroy', guestType.id))
    }
}
</script>
