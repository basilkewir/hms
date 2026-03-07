<template>
    <DashboardLayout title="Housekeeping Management" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Housekeeping Management</h1>
                    <p class="text-gray-600 mt-2">Monitor and manage housekeeping tasks and room cleaning operations.</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('admin.housekeeping-tasks.create')"
                          class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        <PlusIcon class="h-4 w-4 mr-2 inline" />
                        New Task
                    </Link>
                    <Link :href="route('manager.rooms.index')"
                          class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                        <HomeIcon class="h-4 w-4 mr-2 inline" />
                        Room Management
                    </Link>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <ClipboardDocumentListIcon class="h-8 w-8 text-blue-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Tasks</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <ClockIcon class="h-8 w-8 text-yellow-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Pending</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.pending }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <ArrowPathIcon class="h-8 w-8 text-blue-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">In Progress</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.in_progress }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <CheckCircleIcon class="h-8 w-8 text-green-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Completed</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.completed }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <CalendarIcon class="h-8 w-8 text-purple-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Today</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.today }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button @click="activeTab = 'all'"
                            :class="activeTab === 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                        All Tasks
                    </button>
                    <button @click="activeTab = 'pending'"
                            :class="activeTab === 'pending' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                        Pending
                    </button>
                    <button @click="activeTab = 'in_progress'"
                            :class="activeTab === 'in_progress' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                        In Progress
                    </button>
                    <button @click="activeTab = 'completed'"
                            :class="activeTab === 'completed' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                        Completed
                    </button>
                </nav>
            </div>
        </div>

        <!-- Tasks Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">{{ activeTab === 'all' ? 'All Tasks' : formatStatus(activeTab) }} Tasks</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="task in filteredTasks" :key="task.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ task.room?.room_number || 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ task.room?.room_type || '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatTaskType(task.task_type) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span v-if="task.assigned_to?.name">{{ task.assigned_to.name }}</span>
                                <span v-else class="text-orange-600 font-medium">Unassigned</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDate(task.scheduled_date) }}
                                <div class="text-xs text-gray-400" v-if="task.scheduled_time">{{ task.scheduled_time }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="getPriorityColor(task.priority)">
                                    {{ task.priority }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusColor(task.status)">
                                    {{ formatStatus(task.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span v-if="task.estimated_minutes">{{ task.estimated_minutes }} min</span>
                                <span v-else>-</span>
                                <div v-if="task.actual_minutes" class="text-xs text-gray-400">Actual: {{ task.actual_minutes }} min</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <Link :href="route('manager.housekeeping-tasks.show', task.id)"
                                          class="text-blue-600 hover:text-blue-900">View</Link>
                                    <button v-if="!task.assigned_to"
                                            @click="assignTask(task)"
                                            class="text-green-600 hover:text-green-900">Assign</button>
                                    <button v-if="task.status === 'pending' || task.status === 'in_progress'"
                                            @click="updateTaskStatus(task)"
                                            class="text-purple-600 hover:text-purple-900">Update</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredTasks.length === 0">
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                No tasks found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="tasks.links" class="px-6 py-4 border-t border-gray-200">
                <Pagination :links="tasks.links" />
            </div>
        </div>

        <!-- Assign Task Modal -->
        <div v-if="showAssignModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/3 shadow-lg rounded-md bg-white">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Assign Task to Cleaner</h3>
                    <button @click="showAssignModal = false" class="text-gray-400 hover:text-gray-600">
                        <XMarkIcon class="h-6 w-6" />
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Cleaner *</label>
                        <select v-model="selectedCleanerId" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2">
                            <option :value="null">Select cleaner</option>
                            <option v-for="housekeeper in housekeepers" :key="housekeeper.id" :value="housekeeper.id">
                                {{ housekeeper.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" @click="showAssignModal = false"
                                class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button @click="confirmAssignTask" :disabled="!selectedCleanerId"
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50">
                            Assign
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import {
    PlusIcon,
    ClipboardDocumentListIcon,
    ClockIcon,
    ArrowPathIcon,
    CheckCircleIcon,
    CalendarIcon,
    HomeIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    user: Object,
    tasks: Object,
    stats: Object,
    housekeepers: {
        type: Array,
        default: () => []
    }
})

const navigation = computed(() => getNavigationForRole('manager'))
const activeTab = ref('all')
const showAssignModal = ref(false)
const selectedTaskId = ref(null)
const selectedCleanerId = ref(null)

const filteredTasks = computed(() => {
    if (activeTab.value === 'all') {
        return props.tasks.data || []
    }
    return (props.tasks.data || []).filter(task => task.status === activeTab.value)
})

const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    // If already formatted (e.g., "Feb 5, 2026"), return as-is
    if (dateString.match(/^[A-Za-z]{3}\s+\d{1,2},\s+\d{4}$/)) {
        return dateString
    }
    // Otherwise, parse and format
    return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
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

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800',
        in_progress: 'bg-blue-100 text-blue-800',
        completed: 'bg-green-100 text-green-800',
        skipped: 'bg-gray-100 text-gray-800',
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const getPriorityColor = (priority) => {
    const colors = {
        low: 'bg-gray-100 text-gray-800',
        normal: 'bg-blue-100 text-blue-800',
        high: 'bg-orange-100 text-orange-800',
        urgent: 'bg-red-100 text-red-800',
    }
    return colors[priority] || 'bg-gray-100 text-gray-800'
}

const assignTask = (task) => {
    selectedTaskId.value = task.id
    selectedCleanerId.value = null
    showAssignModal.value = true
}

const confirmAssignTask = () => {
    if (selectedTaskId.value && selectedCleanerId.value) {
        router.post(route('manager.housekeeping-tasks.update-status', selectedTaskId.value), {
            assigned_to: selectedCleanerId.value
        }, {
            preserveScroll: true,
            onSuccess: () => {
                showAssignModal.value = false
                selectedTaskId.value = null
                selectedCleanerId.value = null
            }
        })
    }
}

const updateTaskStatus = (task) => {
    const statuses = ['pending', 'in_progress', 'completed']
    const currentIndex = statuses.indexOf(task.status)
    const nextStatus = statuses[currentIndex + 1] || statuses[0]

    if (confirm(`Update task status to ${formatStatus(nextStatus)}?`)) {
        router.post(route('manager.housekeeping-tasks.update-status', task.id), {
            status: nextStatus
        }, {
            preserveScroll: true
        })
    }
}
</script>
