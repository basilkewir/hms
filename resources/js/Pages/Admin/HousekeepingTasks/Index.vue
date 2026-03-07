<template>
    <DashboardLayout title="Housekeeping Tasks">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Housekeeping Tasks</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Manage and track housekeeping tasks and room cleaning.</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="generateTasks"
                            :disabled="generating"
                            class="px-4 py-2 rounded-md transition-colors text-sm font-medium"
                            :style="{
                                backgroundColor: generating ? themeColors.secondary : themeColors.warning,
                                color: 'white'
                            }">
                        <ArrowPathIcon class="h-4 w-4 mr-2 inline" :class="{ 'animate-spin': generating }" />
                        {{ generating ? 'Generating...' : "Generate Today's Tasks" }}
                    </button>
                    <Link :href="route('admin.housekeeping-tasks.create')"
                          class="px-4 py-2 rounded-md transition-colors text-sm font-medium"
                          :style="{
                              backgroundColor: themeColors.primary,
                              color: 'white'
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4 mr-2 inline" />
                        Add Task
                    </Link>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="p-4 rounded-lg"
                     :style="{
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="flex items-center">
                        <ClipboardDocumentListIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.primary }" />
                        <div>
                            <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ stats?.total || 0 }}</p>
                            <p class="text-sm" :style="{ color: themeColors.textSecondary }">Total Tasks</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 rounded-lg"
                     :style="{
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="flex items-center">
                        <ClockIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.warning }" />
                        <div>
                            <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ stats?.pending || 0 }}</p>
                            <p class="text-sm" :style="{ color: themeColors.textSecondary }">Pending</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 rounded-lg"
                     :style="{
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="flex items-center">
                        <ArrowPathIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.primary }" />
                        <div>
                            <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ stats?.in_progress || 0 }}</p>
                            <p class="text-sm" :style="{ color: themeColors.textSecondary }">In Progress</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 rounded-lg"
                     :style="{
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="flex items-center">
                        <CheckCircleIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.success }" />
                        <div>
                            <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ stats?.completed || 0 }}</p>
                            <p class="text-sm" :style="{ color: themeColors.textSecondary }">Completed</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 rounded-lg"
                     :style="{
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="flex items-center">
                        <CalendarIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.secondary }" />
                        <div>
                            <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ stats?.today || 0 }}</p>
                            <p class="text-sm" :style="{ color: themeColors.textSecondary }">Today</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="mb-6">
                <div class="flex flex-wrap gap-2">
                    <button class="px-3 py-1 rounded-md text-sm transition-colors"
                            :style="{
                                backgroundColor: themeColors.primary,
                                color: 'white'
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        All
                    </button>
                    <button class="px-3 py-1 rounded-md text-sm transition-colors"
                            :style="{
                                backgroundColor: themeColors.background,
                                color: themeColors.textPrimary,
                                borderColor: themeColors.border,
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                        Pending
                    </button>
                    <button class="px-3 py-1 rounded-md text-sm transition-colors"
                            :style="{
                                backgroundColor: themeColors.background,
                                color: themeColors.textPrimary,
                                borderColor: themeColors.border,
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                        In Progress
                    </button>
                    <button class="px-3 py-1 rounded-md text-sm transition-colors"
                            :style="{
                                backgroundColor: themeColors.background,
                                color: themeColors.textPrimary,
                                borderColor: themeColors.border,
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                        Completed
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-hidden shadow rounded-lg"
                 :style="{ borderColor: themeColors.border }">
                <table class="min-w-full divide-y"
                       :style="{ borderColor: themeColors.border }">
                    <thead class="bg-gray-50"
                            :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Room</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Task Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Assigned To</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Scheduled</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y"
                           :style="{ borderColor: themeColors.border }">
                        <tr v-for="task in tasks?.data || []" :key="task?.id"
                            :style="hoveredRow === task.id ? { backgroundColor: themeColors.hover } : {}"
                            @mouseenter="hoveredRow = task.id"
                            @mouseleave="hoveredRow = null"
                            class="transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                <div>
                                    <div>{{ task.room?.room_number || 'N/A' }}</div>
                                    <div class="text-xs" :style="{ color: themeColors.textSecondary }">{{ task.room?.room_type?.name || '' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">{{ formatTaskType(task.task_type) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">{{ task.assigned_to?.name || 'Unassigned' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs rounded-full" :style="getPriorityStyle(task.priority)">
                                    {{ task.priority }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs rounded-full" :style="getStatusStyle(task.status)">
                                    {{ formatStatus(task.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                <div>{{ formatScheduledDate(task.scheduled_date) }}</div>
                                <div class="text-xs" :style="{ color: themeColors.textSecondary }">{{ formatScheduledTime(task.scheduled_time) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textPrimary }">
                                    <span v-if="task.estimated_minutes">{{ task.estimated_minutes }} min</span>
                                    <span v-else>-</span>
                                </div>
                                <div v-if="task.actual_minutes" class="text-xs mt-1"
                                     :style="{ color: themeColors.textSecondary }">Actual: {{ task.actual_minutes }} min</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <Link :href="`/admin/housekeeping-tasks/${task.id}`"
                                          class="transition-colors"
                                          :style="{ color: themeColors.primary }"
                                          @mouseenter="$event.target.style.color = themeColors.hover"
                                          @mouseleave="$event.target.style.color = themeColors.primary">
                                        View
                                    </Link>
                                    <button @click="deleteTask(task)"
                                            class="transition-colors"
                                            :style="{ color: themeColors.danger }"
                                            @mouseenter="$event.target.style.color = themeColors.hover"
                                            @mouseleave="$event.target.style.color = themeColors.danger">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm" :style="{ color: themeColors.textSecondary }">
                    Showing {{ tasks?.from || 0 }} to {{ tasks?.to || 0 }} of {{ tasks?.total || 0 }} results
                </div>
                <div class="flex space-x-2">
                    <button v-if="tasks?.prev_page_url"
                            @click="previousPage"
                            class="px-3 py-1 rounded-md text-sm transition-colors"
                            :style="{
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary,
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                        Previous
                    </button>
                    <button v-if="tasks?.next_page_url"
                            @click="nextPage"
                            class="px-3 py-1 rounded-md text-sm transition-colors"
                            :style="{
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary,
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {
    PlusIcon,
    ArrowPathIcon,
    ClipboardDocumentListIcon,
    ClockIcon,
    CheckCircleIcon,
    CalendarIcon
} from '@heroicons/vue/24/outline'
import { useTheme } from '@/Composables/useTheme.js'

// Initialize theme
const { loadTheme, currentTheme } = useTheme()
const hoveredRow = ref(null)
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
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)'
}))

// Load theme on mount
loadTheme()

const props = defineProps({
    tasks: Object,
    stats: Object,
})

const getStatusStyle = (status) => {
    const styles = {
        pending: {
            backgroundColor: `var(--kotel-warning)`,
            color: 'white'
        },
        in_progress: {
            backgroundColor: `var(--kotel-primary)`,
            color: 'white'
        },
        completed: {
            backgroundColor: `var(--kotel-success)`,
            color: 'white'
        },
        skipped: {
            backgroundColor: `var(--kotel-secondary)`,
            color: 'white'
        }
    }
    return styles[status] || styles['pending']
}

const getPriorityStyle = (priority) => {
    const styles = {
        low: {
            backgroundColor: `var(--kotel-secondary)`,
            color: 'white'
        },
        normal: {
            backgroundColor: `var(--kotel-primary)`,
            color: 'white'
        },
        high: {
            backgroundColor: `var(--kotel-warning)`,
            color: 'white'
        },
        urgent: {
            backgroundColor: `var(--kotel-danger)`,
            color: 'white'
        }
    }
    return styles[priority] || styles['normal']
}

const formatScheduledDate = (dateString) => {
    if (!dateString) return 'N/A'
    
    // If already formatted (e.g., "Feb 5, 2026"), return as-is
    if (dateString.match(/^[A-Za-z]{3}\s+\d{1,2},\s+\d{4}$/)) {
        return dateString
    }
    
    // If it's an ISO timestamp, format it
    if (dateString.includes('T') && dateString.includes('Z')) {
        try {
            const date = new Date(dateString)
            return date.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric' 
            })
        } catch (error) {
            return dateString
        }
    }
    
    // Try to parse as regular date
    try {
        const date = new Date(dateString)
        return date.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric' 
        })
    } catch (error) {
        return dateString
    }
}

const formatScheduledTime = (timeString) => {
    if (!timeString) return ''
    
    // If already formatted (e.g., "9:12 PM"), return as-is
    if (timeString.match(/^\d{1,2}:\d{2}\s[AP]M$/)) {
        return timeString
    }
    
    // If it's in 24-hour format (e.g., "20:32:54"), convert to 12-hour
    if (timeString.match(/^\d{2}:\d{2}:\d{2}$/)) {
        try {
            const [hours, minutes] = timeString.split(':')
            const time = new Date()
            time.setHours(parseInt(hours), parseInt(minutes))
            return time.toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit',
                hour12: true 
            })
        } catch (error) {
            return timeString
        }
    }
    
    return timeString
}

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
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatStatus = (status) => {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDateTime = (dateString, timeString) => {
    if (!dateString) return 'N/A'

    try {
        // Handle ISO timestamp format
        if (dateString.includes('T') && dateString.includes('Z')) {
            const date = new Date(dateString)
            const formattedDate = date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            })

            // Extract time from the ISO timestamp if no separate time is provided
            const timeToUse = timeString || dateString.split('T')[1]?.split('.')[0] || ''

            if (timeToUse) {
                const [hours, minutes] = timeToUse.split(':')
                const time = new Date()
                time.setHours(parseInt(hours), parseInt(minutes))
                const formattedTime = time.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                })
                return `${formattedDate} ${formattedTime}`
            }

            return formattedDate
        }

        // Handle regular date format
        const date = new Date(dateString)
        const formattedDate = date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        })

        if (timeString) {
            const [hours, minutes] = timeString.split(':')
            const time = new Date()
            time.setHours(parseInt(hours), parseInt(minutes))
            const formattedTime = time.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            })
            return `${formattedDate} ${formattedTime}`
        }

        return formattedDate
    } catch (error) {
        // Fallback to showing original values if formatting fails
        if (dateString && timeString) {
            return `${dateString} ${timeString}`
        }
        return dateString || 'N/A'
    }
}

const deleteTask = (task) => {
    if (confirm('Are you sure you want to delete this task?') && task?.id) {
        router.delete(`/admin/housekeeping-tasks/${task.id}`)
    }
}

const generating = ref(false)
const generateTasks = () => {
    if (generating.value) return
    generating.value = true
    router.post(route('admin.housekeeping-tasks.generate-daily'), {}, {
        onFinish: () => { generating.value = false }
    })
}

const nextPage = () => {
    if (props.tasks?.next_page_url) {
        window.location.href = props.tasks.next_page_url
    }
}

const previousPage = () => {
    if (props.tasks?.prev_page_url) {
        window.location.href = props.tasks.prev_page_url
    }
}
</script>
