<template>
    <DashboardLayout title="Maintenance Management" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Maintenance Management</h1>
                    <p class="text-gray-600 mt-2">Track and manage maintenance issues and repairs across the hotel.</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('manager.maintenance-requests.create')" 
                          class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        <PlusIcon class="h-4 w-4 mr-2 inline" />
                        New Request
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
                    <WrenchScrewdriverIcon class="h-8 w-8 text-blue-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <ExclamationCircleIcon class="h-8 w-8 text-yellow-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Open</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.open }}</p>
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
                        <p class="text-sm font-medium text-gray-500">Resolved</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.resolved }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <ExclamationTriangleIcon class="h-8 w-8 text-red-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Urgent</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.urgent }}</p>
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
                        All Requests
                    </button>
                    <button @click="activeTab = 'open'" 
                            :class="activeTab === 'open' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                        Open
                    </button>
                    <button @click="activeTab = 'in_progress'" 
                            :class="activeTab === 'in_progress' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                        In Progress
                    </button>
                    <button @click="activeTab = 'resolved'" 
                            :class="activeTab === 'resolved' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                        Resolved
                    </button>
                    <button @click="activeTab = 'urgent'" 
                            :class="activeTab === 'urgent' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                        Urgent
                    </button>
                </nav>
            </div>
        </div>

        <!-- Requests Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">{{ getTabTitle() }} Requests</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room/Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reported</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="request in filteredRequests" :key="request.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ request.request_number }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ request.title }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span v-if="request.room">{{ request.room.room_number }}</span>
                                <span v-else>{{ request.location || 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatCategory(request.category) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="getPriorityColor(request.priority)">
                                    {{ request.priority }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusColor(request.status)">
                                    {{ formatStatus(request.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span v-if="request.assigned_to?.name">{{ request.assigned_to.name }}</span>
                                <span v-else class="text-orange-600 font-medium">Unassigned</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDateTime(request.reported_at) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <Link :href="route('manager.maintenance-requests.show', { maintenanceRequest: request.id })" 
                                          class="text-blue-600 hover:text-blue-900">View</Link>
                                    <button v-if="!request.assigned_to" 
                                            @click="assignRequest(request)" 
                                            class="text-green-600 hover:text-green-900">Assign</button>
                                    <button v-if="request.status === 'open' || request.status === 'assigned' || request.status === 'in_progress'" 
                                            @click="updateRequestStatus(request)" 
                                            class="text-purple-600 hover:text-purple-900">Update</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredRequests.length === 0">
                            <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">
                                No requests found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="requests.links" class="px-6 py-4 border-t border-gray-200">
                <Pagination :links="requests.links" />
            </div>
        </div>

        <!-- Assign Request Modal -->
        <div v-if="showAssignModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/3 shadow-lg rounded-md bg-white">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Assign Maintenance Request</h3>
                    <button @click="showAssignModal = false" class="text-gray-400 hover:text-gray-600">
                        <XMarkIcon class="h-6 w-6" />
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Maintenance Staff *</label>
                        <select v-model="selectedStaffId" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2">
                            <option :value="null">Select maintenance staff</option>
                            <option v-for="staff in maintenanceStaff" :key="staff.id" :value="staff.id">
                                {{ staff.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" @click="showAssignModal = false"
                                class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button @click="confirmAssignRequest" :disabled="!selectedStaffId"
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
    WrenchScrewdriverIcon,
    ExclamationCircleIcon,
    ArrowPathIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    HomeIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    user: Object,
    requests: Object,
    stats: Object,
    maintenanceStaff: {
        type: Array,
        default: () => []
    }
})

const navigation = computed(() => getNavigationForRole('manager'))
const activeTab = ref('all')
const showAssignModal = ref(false)
const selectedRequestId = ref(null)
const selectedStaffId = ref(null)

const filteredRequests = computed(() => {
    if (activeTab.value === 'all') {
        return props.requests.data || []
    }
    if (activeTab.value === 'urgent') {
        return (props.requests.data || []).filter(request => 
            request.priority === 'urgent' && 
            ['open', 'assigned', 'in_progress'].includes(request.status)
        )
    }
    return (props.requests.data || []).filter(request => request.status === activeTab.value)
})

const getTabTitle = () => {
    const titles = {
        all: 'All',
        open: 'Open',
        in_progress: 'In Progress',
        resolved: 'Resolved',
        urgent: 'Urgent'
    }
    return titles[activeTab.value] || 'All'
}

const formatDateTime = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit' 
    })
}

const formatCategory = (category) => {
    return category.charAt(0).toUpperCase() + category.slice(1)
}

const formatStatus = (status) => {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getStatusColor = (status) => {
    const colors = {
        open: 'bg-yellow-100 text-yellow-800',
        assigned: 'bg-blue-100 text-blue-800',
        in_progress: 'bg-purple-100 text-purple-800',
        on_hold: 'bg-gray-100 text-gray-800',
        resolved: 'bg-green-100 text-green-800',
        closed: 'bg-gray-100 text-gray-800',
        cancelled: 'bg-red-100 text-red-800',
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

const assignRequest = (request) => {
    selectedRequestId.value = request.id
    selectedStaffId.value = null
    showAssignModal.value = true
}

const confirmAssignRequest = () => {
    if (selectedRequestId.value && selectedStaffId.value) {
        router.post(route('manager.maintenance-requests.assign', selectedRequestId.value), {
            assigned_to: selectedStaffId.value
        }, {
            preserveScroll: true,
            onSuccess: () => {
                showAssignModal.value = false
                selectedRequestId.value = null
                selectedStaffId.value = null
            }
        })
    }
}

const updateRequestStatus = (request) => {
    const statuses = ['open', 'assigned', 'in_progress', 'resolved']
    const currentIndex = statuses.indexOf(request.status)
    const nextStatus = statuses[Math.min(currentIndex + 1, statuses.length - 1)] || 'resolved'
    
    if (confirm(`Update request status to ${formatStatus(nextStatus)}?`)) {
        router.post(route('manager.maintenance-requests.update-status', request.id), {
            status: nextStatus
        }, {
            preserveScroll: true
        })
    }
}
</script>
