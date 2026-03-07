<template>
    <DashboardLayout title="Maintenance Services" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Maintenance Services</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Track and monitor maintenance requests.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="showNewRequest = true"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{
                                backgroundColor: themeColors.primary,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        New Request
                    </button>
                    <button @click="exportRequests"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{
                                backgroundColor: '#8b5cf6',
                            }"
                            @mouseenter="$event.target.style.backgroundColor = '#7c3aed'"
                            @mouseleave="$event.target.style.backgroundColor = '#8b5cf6'">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                        Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Maintenance Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                        <WrenchScrewdriverIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.total }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(250, 204, 21, 0.1)' }">
                        <ExclamationCircleIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Open</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.open }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                        <ArrowPathIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">In Progress</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.in_progress }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                        <CheckCircleIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Resolved</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.resolved }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(239, 68, 68, 0.1)' }">
                        <ExclamationTriangleIcon class="h-6 w-6" :style="{ color: themeColors.danger }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Urgent</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.urgent }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requests Table -->
        <div class="shadow rounded-lg overflow-hidden"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="px-6 py-4 border-b"
                 :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Recent Maintenance Requests</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: 'rgba(249, 250, 251, 0.5)' }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Request #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Room/Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Assigned To</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Reported</th>
                        </tr>
                    </thead>
                    <tbody :style="{ backgroundColor: themeColors.card }">
                        <tr v-if="requests.data.length === 0">
                            <td colspan="8" class="px-6 py-4 text-center"
                                :style="{ color: themeColors.textTertiary }">No maintenance requests found.</td>
                        </tr>
                        <tr v-for="request in requests.data" :key="request.id"
                            class="hover:bg-opacity-50 transition-colors"
                            :style="{
                                '&:hover': { backgroundColor: themeColors.hover }
                            }">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">{{ request.request_number }}</td>
                            <td class="px-6 py-4 text-sm"
                                :style="{ color: themeColors.textPrimary }">{{ request.title }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                <span v-if="request.room">{{ request.room.room_number }}</span>
                                <span v-else>{{ request.location || 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">{{ formatCategory(request.category) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getPriorityColor(request.priority)">
                                    {{ request.priority }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(request.status)">
                                    {{ formatStatus(request.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">{{ request.assigned_to?.name || 'Unassigned' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">{{ formatDateTime(request.reported_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="requests.links" class="px-6 py-4 border-t"
                 :style="{ borderColor: themeColors.border }">
                <Pagination :links="requests.links" />
            </div>
        </div>

        <!-- New Request Modal -->
        <div v-if="showNewRequest" @click="showNewRequest = false"
             class="fixed inset-0 flex items-center justify-center z-50"
             :style="{ backgroundColor: 'rgba(0, 0, 0, 0.5)' }">
            <div @click.stop
                 class="rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                <h2 class="text-xl font-bold mb-4" :style="{ color: themeColors.textPrimary }">New Maintenance Request</h2>
                <form @submit.prevent="submitRequest">
                    <div class="space-y-5">
                        <!-- Request Information -->
                        <div>
                            <h3 class="text-base font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Request Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Room</label>
                                    <select v-model="newRequest.room_id"
                                            class="w-full rounded-md px-3 py-2 transition-colors"
                                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }">
                                        <option :value="null">Not Room-Specific</option>
                                        <option v-for="room in rooms" :key="room.id" :value="room.id">Room {{ room.room_number }}</option>
                                    </select>
                                    <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">Select the room if the issue is room-specific</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Category *</label>
                                    <select v-model="newRequest.category" required
                                            class="w-full rounded-md px-3 py-2 transition-colors"
                                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }">
                                        <option value="">Select Category</option>
                                        <option value="plumbing">Plumbing</option>
                                        <option value="electrical">Electrical</option>
                                        <option value="hvac">HVAC</option>
                                        <option value="furniture">Furniture</option>
                                        <option value="appliances">Appliances</option>
                                        <option value="security">Security</option>
                                        <option value="it">IT</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Priority *</label>
                                    <select v-model="newRequest.priority" required
                                            class="w-full rounded-md px-3 py-2 transition-colors"
                                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }">
                                        <option value="low">Low</option>
                                        <option value="normal">Normal</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                    <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">Select urgency level</p>
                                </div>
                                <div v-if="departments.length > 0">
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Department</label>
                                    <select v-model="newRequest.department_id"
                                            class="w-full rounded-md px-3 py-2 transition-colors"
                                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }">
                                        <option :value="null">Select Department</option>
                                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                    </select>
                                    <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">Assign to specific department if needed</p>
                                </div>
                            </div>
                        </div>
                        <!-- Request Details -->
                        <div>
                            <h3 class="text-base font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Request Details</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Title *</label>
                                    <input v-model="newRequest.title" type="text" required placeholder="Brief description of the issue"
                                           class="w-full rounded-md px-3 py-2 transition-colors"
                                           :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Description *</label>
                                    <textarea v-model="newRequest.description" rows="4" required placeholder="Detailed description of the maintenance issue..."
                                              class="w-full rounded-md px-3 py-2 transition-colors"
                                              :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }"></textarea>
                                </div>
                            </div>
                        </div>
                        <!-- Location Information -->
                        <div>
                            <h3 class="text-base font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Location Information</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Location</label>
                                    <input v-model="newRequest.location" type="text" placeholder="e.g., Lobby, Restaurant, Pool Area"
                                           class="w-full rounded-md px-3 py-2 transition-colors"
                                           :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }">
                                    <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">General area where the issue is located</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Location Details</label>
                                    <textarea v-model="newRequest.location_details" rows="2" placeholder="Specific location details..."
                                              class="w-full rounded-md px-3 py-2 transition-colors"
                                              :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }"></textarea>
                                    <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">More specific location information</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-6 pt-4 border-t" :style="{ borderColor: themeColors.border }">
                        <button type="submit"
                                class="flex-1 py-2 rounded-md transition-colors font-medium text-white"
                                :style="{ backgroundColor: themeColors.primary }">
                            Submit Request
                        </button>
                        <button type="button" @click="showNewRequest = false"
                                class="flex-1 py-2 rounded-md transition-colors font-medium"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme.js'
import {
    WrenchScrewdriverIcon,
    ExclamationCircleIcon,
    ArrowPathIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    PlusIcon,
    DocumentArrowDownIcon
} from '@heroicons/vue/24/outline'

// Initialize theme
const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    primary: `var(--kotel-primary)`,
    secondary: `var(--kotel-secondary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
    hover: `rgba(255, 255, 255, 0.1)`
}))

// Load theme on mount
loadTheme()

const props = defineProps({
    user: Object,
    requests: Object,
    stats: Object,
    rooms: { type: Array, default: () => [] },
    departments: { type: Array, default: () => [] },
})

const navigation = computed(() => getNavigationForRole('front_desk'))

const requests = computed(() => props.requests || { data: [], links: [] })
const stats = computed(() => props.stats || {
    total: 0,
    open: 0,
    in_progress: 0,
    resolved: 0,
    urgent: 0
})

const showNewRequest = ref(false)
const newRequest = ref({
    room_id: null,
    title: '',
    description: '',
    category: '',
    priority: 'normal',
    location: '',
    location_details: '',
    department_id: null,
})

const formatDateTime = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const formatCategory = (category) => {
    return category ? category.charAt(0).toUpperCase() + category.slice(1) : 'N/A'
}

const formatStatus = (status) => {
    return status ? status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A'
}

const getStatusColor = (status) => {
    const colors = {
        open: 'bg-yellow-100 text-black',
        assigned: 'bg-blue-100 text-black',
        in_progress: 'bg-purple-100 text-black',
        on_hold: 'bg-gray-100 text-black',
        resolved: 'bg-green-100 text-black',
        closed: 'bg-gray-100 text-black',
        cancelled: 'bg-red-100 text-black',
    }
    return colors[status] || 'bg-gray-100 text-black'
}

const getPriorityColor = (priority) => {
    const colors = {
        low: 'bg-gray-100 text-black',
        normal: 'bg-blue-100 text-black',
        high: 'bg-orange-100 text-black',
        urgent: 'bg-red-100 text-black',
    }
    return colors[priority] || 'bg-gray-100 text-black'
}

const exportRequests = () => {
    const requestsData = props.requests.data || []

    // Create CSV headers
    const headers = [
        'Request #',
        'Title',
        'Room/Location',
        'Category',
        'Priority',
        'Status',
        'Assigned To',
        'Reported At',
        'Description',
        'Location Details'
    ]

    // Create CSV rows
    const rows = requestsData.map(request => [
        request.request_number || '',
        request.title || '',
        request.room ? request.room.room_number : (request.location || 'N/A'),
        formatCategory(request.category),
        request.priority || '',
        formatStatus(request.status),
        request.assigned_to?.name || 'Unassigned',
        formatDateTime(request.reported_at),
        (request.description || '').replace(/\n/g, ' ').replace(/,/g, ';'),
        (request.location_details || '').replace(/\n/g, ' ').replace(/,/g, ';')
    ])

    // Build CSV content
    const csvContent = [
        headers.join(','),
        ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
    ].join('\n')

    // Create and download CSV file
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `maintenance_requests_${new Date().toISOString().split('T')[0]}.csv`
    link.click()
    URL.revokeObjectURL(url)
}

const submitRequest = () => {
    router.post(route('front-desk.services.maintenance.store'), newRequest.value, {
        preserveScroll: true,
        onSuccess: () => {
            showNewRequest.value = false
            newRequest.value = {
                room_id: null,
                title: '',
                description: '',
                category: '',
                priority: 'normal',
                location: '',
                location_details: '',
                department_id: null,
            }
        }
    })
}
</script>
