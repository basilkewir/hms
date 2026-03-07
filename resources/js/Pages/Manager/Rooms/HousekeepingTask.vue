<template>
    <DashboardLayout title="Housekeeping Task Details" :user="user" :navigation="navigation">
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-white">Task Details</h1>
                    <p class="text-white mt-2">Room: {{ task.room?.room_number || 'N/A' }}</p>
                </div>
                <Link :href="route('manager.rooms.housekeeping')"
                      class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                    Back
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="font-semibold text-white mb-3">Task Information</h3>
                    <div class="space-y-2 text-sm text-white">
                        <div><span class="font-medium">Task Type:</span> {{ formatTaskType(task.task_type) }}</div>
                        <div><span class="font-medium">Priority:</span>
                            <span :class="getPriorityClass(task.priority)" class="px-2 py-0.5 rounded-full text-xs font-medium">
                                {{ task.priority }}
                            </span>
                        </div>
                        <div><span class="font-medium">Status:</span>
                            <span :class="getStatusClass(task.status)" class="px-2 py-0.5 rounded-full text-xs font-medium">
                                {{ formatStatus(task.status) }}
                            </span>
                        </div>
                        <div><span class="font-medium">Scheduled:</span> {{ formatDate(task.scheduled_date) }} {{ task.scheduled_time || '' }}</div>
                        <div v-if="task.assigned_to"><span class="font-medium">Assigned To:</span> {{ task.assigned_to.name }}</div>
                        <div v-else><span class="font-medium">Assigned To:</span> <span class="text-orange-400">Unassigned</span></div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="font-semibold text-white mb-3">Time Tracking</h3>
                    <div class="space-y-2 text-sm text-white">
                        <div v-if="task.estimated_minutes"><span class="font-medium">Estimated:</span> {{ task.estimated_minutes }} minutes</div>
                        <div v-if="task.actual_minutes"><span class="font-medium">Actual:</span> {{ task.actual_minutes }} minutes</div>
                        <div v-if="task.started_at"><span class="font-medium">Started:</span> {{ formatDateTime(task.started_at) }}</div>
                        <div v-if="task.completed_at"><span class="font-medium">Completed:</span> {{ formatDateTime(task.completed_at) }}</div>
                    </div>
                </div>
            </div>

            <div v-if="task.instructions" class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-800 mb-3">Instructions</h3>
                <p class="text-sm text-gray-700">{{ task.instructions }}</p>
            </div>

            <div v-if="task.notes" class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-800 mb-3">Notes</h3>
                <p class="text-sm text-gray-700">{{ task.notes }}</p>
            </div>

            <div v-if="task.completion_notes" class="bg-green-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-green-800 mb-3">Completion Notes</h3>
                <p class="text-sm text-green-700">{{ task.completion_notes }}</p>
            </div>

            <div v-if="task.inspection_status" class="bg-blue-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-800 mb-3">Inspection Details</h3>
                <div class="space-y-2 text-sm text-blue-700">
                    <div><span class="font-medium">Status:</span> {{ formatStatus(task.inspection_status) }}</div>
                    <div v-if="task.inspected_by"><span class="font-medium">Inspected By:</span> {{ task.inspected_by.name }}</div>
                    <div v-if="task.inspected_at"><span class="font-medium">Inspected At:</span> {{ formatDateTime(task.inspected_at) }}</div>
                    <div v-if="task.inspection_notes"><span class="font-medium">Notes:</span> {{ task.inspection_notes }}</div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'

const props = defineProps({
    user: Object,
    task: Object,
})

const navigation = computed(() => getNavigationForRole('manager'))

const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    // If already formatted (e.g., "Feb 5, 2026"), return as-is
    if (dateString.match(/^[A-Za-z]{3}\s+\d{1,2},\s+\d{4}$/)) {
        return dateString
    }
    // Otherwise, parse and format
    return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const formatDateTime = (dateString) => {
    return new Date(dateString).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const formatTaskType = (type) => {
    const types = {
        checkout: 'Checkout Cleaning',
        cleaning: 'Cleaning',
        check_cleaning: 'Check Cleaning',
        stayover: 'Stayover Service',
        deep_clean: 'Deep Clean',
        inspection: 'Inspection',
        maintenance: 'Maintenance'
    }
    return types[type] || type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatStatus = (status) => {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-500/30 text-yellow-300 border border-yellow-500/50',
        in_progress: 'bg-blue-500/30 text-blue-300 border border-blue-500/50',
        completed: 'bg-green-500/30 text-green-300 border border-green-500/50',
        skipped: 'bg-gray-500/30 text-gray-300 border border-gray-500/50',
    }
    return classes[status] || 'bg-gray-500/30 text-gray-300 border border-gray-500/50'
}

const getPriorityClass = (priority) => {
    const classes = {
        low: 'bg-gray-500/30 text-gray-300 border border-gray-500/50',
        normal: 'bg-blue-500/30 text-blue-300 border border-blue-500/50',
        high: 'bg-orange-500/30 text-orange-300 border border-orange-500/50',
        urgent: 'bg-red-500/30 text-red-300 border border-red-500/50',
    }
    return classes[priority] || 'bg-gray-500/30 text-gray-300 border border-gray-500/50'
}
</script>
