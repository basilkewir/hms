<template>
    <DashboardLayout title="Housekeeping Services" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Housekeeping Services</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Manage housekeeping requests and room service.</p>
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
                    <button @click="exportTasks"
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

        <!-- Housekeeping Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
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
                        <ClockIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Pending</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.pending }}</p>
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
                           :style="{ color: themeColors.textSecondary }">Completed</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.completed }}</p>
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
                    :style="{ color: themeColors.textPrimary }">Recent Requests</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: 'rgba(249, 250, 251, 0.5)' }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Room</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Service Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Scheduled</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody :style="{ backgroundColor: themeColors.card }">
                        <tr v-if="tasks.data.length === 0">
                            <td colspan="6" class="px-6 py-4 text-center"
                                :style="{ color: themeColors.textTertiary }">No requests found</td>
                        </tr>
                        <tr v-for="task in tasks.data" :key="task.id"
                            class="hover:bg-opacity-50 transition-colors"
                            :style="{
                                '&:hover': { backgroundColor: themeColors.hover }
                            }">
                            <td class="px-6 py-4 text-sm"
                                :style="{ color: themeColors.textPrimary }">{{ task.room?.room_number || 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm"
                                :style="{ color: themeColors.textPrimary }">{{ formatTaskType(task.task_type) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getPriorityClass(task.priority)">
                                    {{ task.priority }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusClass(task.status)">
                                    {{ formatStatus(task.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm"
                                :style="{ color: themeColors.textSecondary }">{{ formatSchedule(task) }}</td>
                            <td class="px-6 py-4 text-sm">
                                <button @click="advanceStatus(task)"
                                        class="font-medium transition-colors"
                                        :style="{ color: themeColors.primary }"
                                        @mouseenter="$event.target.style.color = themeColors.hover"
                                        @mouseleave="$event.target.style.color = themeColors.primary">
                                    Advance
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="tasks.links" class="px-6 py-4 border-t"
                 :style="{ borderColor: themeColors.border }">
                <Pagination :links="tasks.links" />
            </div>
        </div>

        <!-- New Request Modal -->
        <div v-if="showNewRequest" @click="showNewRequest = false"
             class="fixed inset-0 flex items-center justify-center z-50"
             :style="{ backgroundColor: 'rgba(0, 0, 0, 0.5)' }">
            <div @click.stop
                 class="rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <h2 class="text-xl font-bold mb-4"
                    :style="{ color: themeColors.textPrimary }">New Housekeeping Task</h2>
                <form @submit.prevent="submitRequest">
                    <div class="space-y-5">
                        <!-- Task Information -->
                        <div>
                            <h3 class="text-base font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Task Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Room *</label>
                                    <select v-model="newRequest.room_id" required
                                            class="w-full rounded-md px-3 py-2 transition-colors"
                                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }">
                                        <option value="">Select Room</option>
                                        <option v-for="room in rooms" :key="room.id" :value="room.id">
                                            Room {{ room.room_number }} — {{ room.room_type?.name || 'N/A' }} [{{ room.status }}] · HK: {{ room.housekeeping_status || 'unknown' }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Assigned To</label>
                                    <select v-model="newRequest.assigned_to"
                                            class="w-full rounded-md px-3 py-2 transition-colors"
                                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }">
                                        <option :value="null">Unassigned</option>
                                        <option v-for="hk in housekeepers" :key="hk.id" :value="hk.id">{{ hk.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Task Type *</label>
                                    <select v-model="newRequest.task_type" required
                                            class="w-full rounded-md px-3 py-2 transition-colors"
                                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }">
                                        <option value="">Select Type</option>
                                        <option value="checkout">Checkout Cleaning</option>
                                        <option value="cleaning">Cleaning</option>
                                        <option value="check_cleaning">Check Cleaning</option>
                                        <option value="stayover">Stayover Service</option>
                                        <option value="deep_clean">Deep Clean</option>
                                        <option value="inspection">Inspection</option>
                                        <option value="maintenance">Maintenance Assist</option>
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
                                </div>
                            </div>
                        </div>
                        <!-- Scheduling -->
                        <div>
                            <h3 class="text-base font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Scheduling</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Scheduled Date *</label>
                                    <DatePicker v-model="newRequest.scheduled_date" required />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Scheduled Time</label>
                                    <TimePicker v-model="newRequest.scheduled_time" />
                                    <p class="mt-1 text-xs" :style="{ color: themeColors.textTertiary }">Select the time when the task should be performed</p>
                                </div>
                            </div>
                        </div>
                        <!-- Duration & Instructions -->
                        <div>
                            <h3 class="text-base font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Duration & Instructions</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Estimated Duration (minutes)</label>
                                    <input v-model.number="newRequest.estimated_minutes" type="number" min="1" placeholder="e.g., 30"
                                           class="w-full rounded-md px-3 py-2 transition-colors"
                                           :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Special Instructions</label>
                                    <textarea v-model="newRequest.instructions" rows="3" placeholder="Enter any special instructions..."
                                              class="w-full rounded-md px-3 py-2 transition-colors"
                                              :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-6 pt-4 border-t" :style="{ borderColor: themeColors.border }">
                        <button type="submit"
                                class="flex-1 py-2 rounded-md transition-colors font-medium text-white"
                                :style="{ backgroundColor: themeColors.primary }">
                            Create Task
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
import DatePicker from '@/Components/DatePicker.vue'
import TimePicker from '@/Components/TimePicker.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme.js'
import {
    ArrowPathIcon,
    CheckCircleIcon,
    ClockIcon,
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
    tasks: Object,
    stats: Object,
    rooms: { type: Array, default: () => [] },
    housekeepers: { type: Array, default: () => [] },
})

const navigation = computed(() => getNavigationForRole('front_desk'))

const showNewRequest = ref(false)
const newRequest = ref({
    room_id: '',
    assigned_to: null,
    task_type: '',
    priority: 'normal',
    scheduled_date: new Date().toISOString().split('T')[0],
    scheduled_time: '',
    estimated_minutes: null,
    instructions: '',
})

const tasks = computed(() => props.tasks || { data: [], links: [] })
const stats = computed(() => props.stats || {
    pending: 0,
    in_progress: 0,
    completed: 0,
    urgent: 0
})

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-black',
        in_progress: 'bg-blue-100 text-black',
        completed: 'bg-green-100 text-black',
    }
    return classes[status] || 'bg-gray-100 text-black'
}

const getPriorityClass = (priority) => {
    const classes = {
        low: 'bg-gray-100 text-black',
        normal: 'bg-blue-100 text-black',
        high: 'bg-orange-100 text-black',
        urgent: 'bg-red-100 text-black',
    }
    return classes[priority] || 'bg-gray-100 text-black'
}

const formatStatus = (status) => {
    return status ? status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A'
}

const formatTaskType = (type) => {
    if (!type) return 'N/A'
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatSchedule = (task) => {
    if (!task.scheduled_date) return 'N/A'
    try {
        const date = new Date(task.scheduled_date + 'T00:00:00')
        const dateStr = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
        if (task.scheduled_time) {
            // Format time from HH:MM:SS to HH:MM AM/PM
            const [hours, minutes] = task.scheduled_time.split(':')
            const h = parseInt(hours)
            const ampm = h >= 12 ? 'PM' : 'AM'
            const h12 = h % 12 || 12
            return `${dateStr} ${h12}:${minutes} ${ampm}`
        }
        return dateStr
    } catch {
        return task.scheduled_date
    }
}

const submitRequest = () => {
    router.post(route('front-desk.services.housekeeping.store'), newRequest.value, {
        preserveScroll: true,
        onSuccess: () => {
            showNewRequest.value = false
            newRequest.value = {
                room_id: '',
                assigned_to: null,
                task_type: '',
                priority: 'normal',
                scheduled_date: new Date().toISOString().split('T')[0],
                scheduled_time: '',
                estimated_minutes: null,
                instructions: '',
            }
        }
    })
}

const advanceStatus = (task) => {
    const nextStatus = task.status === 'pending'
        ? 'in_progress'
        : task.status === 'in_progress'
            ? 'completed'
            : 'completed'

    router.post(route('front-desk.services.housekeeping.update-status', task.id), {
        status: nextStatus
    }, {
        preserveScroll: true
    })
}

const exportTasks = () => {
    const tasksData = props.tasks.data || []

    // Create CSV headers
    const headers = [
        'Room',
        'Task Type',
        'Priority',
        'Status',
        'Scheduled Date',
        'Scheduled Time',
        'Assigned To',
        'Estimated Minutes',
        'Instructions'
    ]

    // Create CSV rows
    const rows = tasksData.map(task => [
        task.room?.room_number || 'N/A',
        formatTaskType(task.task_type),
        task.priority || '',
        formatStatus(task.status),
        task.scheduled_date || '',
        task.scheduled_time || '',
        task.assigned_to?.name || 'Unassigned',
        task.estimated_minutes || '',
        (task.instructions || '').replace(/\n/g, ' ').replace(/,/g, ';')
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
    link.download = `housekeeping_tasks_${new Date().toISOString().split('T')[0]}.csv`
    link.click()
    URL.revokeObjectURL(url)
}
</script>
