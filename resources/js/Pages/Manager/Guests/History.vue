<template>
    <DashboardLayout title="Guest History" :user="user" :navigation="navigation">
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Guest History</h1>
                    <p class="text-gray-600 mt-2">Historical records of all guest stays</p>
                </div>
                <div class="flex space-x-3">
                    <input type="text" v-model="searchQuery" placeholder="Search guests..."
                           class="border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div v-if="guestHistory && guestHistory.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Guest Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Room</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check-In</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check-Out</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nights</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="guest in filteredHistory" :key="guest.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ guest.guest_name }}</div>
                                <div class="text-sm text-gray-500">{{ guest.guest_email || 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-800 text-white">
                                    {{ guest.room_number }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatDate(guest.check_in_date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatDate(guest.check_out_date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ guest.nights }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getStatusClass(guest.status)"
                                      class="px-2 py-1 text-xs font-medium rounded-full">
                                    {{ guest.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link :href="route('manager.reservations.show', guest.reservation_id)" 
                                      class="text-blue-600 hover:text-blue-900">View</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No guest history</h3>
                <p class="mt-1 text-sm text-gray-500">No historical guest records found.</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'

const props = defineProps({
    user: Object,
    guestHistory: {
        type: Array,
        default: () => []
    },
})

const navigation = computed(() => getNavigationForRole('manager'))
const searchQuery = ref('')

const filteredHistory = computed(() => {
    if (!props.guestHistory || !searchQuery.value) {
        return props.guestHistory || []
    }
    
    const query = searchQuery.value.toLowerCase()
    return props.guestHistory.filter(guest => 
        guest.guest_name?.toLowerCase().includes(query) ||
        guest.guest_email?.toLowerCase().includes(query) ||
        guest.room_number?.toLowerCase().includes(query)
    )
})

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const getStatusClass = (status) => {
    const statusClasses = {
        'checked_out': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800',
        'no_show': 'bg-yellow-100 text-yellow-800',
        'checked_in': 'bg-blue-100 text-blue-800',
    }
    return statusClasses[status] || 'bg-gray-100 text-gray-800'
}
</script>
