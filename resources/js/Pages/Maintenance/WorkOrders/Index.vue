<template>
    <DashboardLayout :title="statusLabel" :user="user">
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ statusLabel }}</h1>
                    <p class="text-gray-600 mt-2">Track maintenance work orders.</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Requests</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Request #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Room/Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reported</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned To</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="requests.data.length === 0">
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No work orders found.</td>
                        </tr>
                        <tr v-for="request in requests.data" :key="request.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ request.request_number }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ request.title }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ request.room ? `Room ${request.room}` : (request.location || 'N/A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 rounded-full text-xs font-medium" :class="getPriorityColor(request.priority)">
                                    {{ request.priority }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 rounded-full text-xs font-medium" :class="getStatusColor(request.status)">
                                    {{ formatStatus(request.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(request.reported_at) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ request.assigned_to || 'Unassigned' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="requests.links" class="px-6 py-4 border-t border-gray-200">
                <Pagination :links="requests.links" />
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    user: Object,
    requests: Object,
    statusLabel: String,
})

const requests = computed(() => props.requests || { data: [], links: [] })

const getPriorityColor = (priority) => {
    const colors = {
        low: 'bg-gray-100 text-gray-800',
        normal: 'bg-blue-100 text-blue-800',
        high: 'bg-orange-100 text-orange-800',
        urgent: 'bg-red-100 text-red-800',
    }
    return colors[(priority || '').toLowerCase()] || 'bg-gray-100 text-gray-800'
}

const getStatusColor = (status) => {
    const colors = {
        open: 'bg-yellow-100 text-yellow-800',
        assigned: 'bg-blue-100 text-blue-800',
        in_progress: 'bg-purple-100 text-purple-800',
        resolved: 'bg-green-100 text-green-800',
        closed: 'bg-gray-100 text-gray-800',
        cancelled: 'bg-red-100 text-red-800',
    }
    return colors[(status || '').toLowerCase()] || 'bg-gray-100 text-gray-800'
}

const formatStatus = (status) => {
    return status ? status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A'
}

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString()
}
</script>
