<template>
    <DashboardLayout title="Housekeeping Schedules" :user="user">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Housekeeping Schedules</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Manage housekeeping staff schedules and room assignments.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.housekeeping-schedules.create')" 
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        New Schedule
                    </Link>
                    <button @click="exportSchedules" 
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

        <!-- Schedule Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
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
                        <CalendarDaysIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Schedules</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ schedules?.total || 0 }}</p>
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
                           :style="{ color: themeColors.textSecondary }">Active</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">
                            {{ activeSchedulesCount }}
                        </p>
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
                        <CheckIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Completed</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">
                            {{ completedSchedulesCount }}
                        </p>
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
                        <XCircleIcon class="h-6 w-6" :style="{ color: themeColors.danger }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Cancelled</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">
                            {{ cancelledSchedulesCount }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

            <!-- Schedules Table -->
        <div class="rounded-lg overflow-hidden shadow"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <div class="px-6 py-4 border-b"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderBottomWidth: '1px'
                 }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Schedules</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Housekeeper</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Rooms</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Period</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="schedule in schedules?.data || []" :key="schedule?.id"
                            class="transition-colors"
                            :style="{ 
                                borderBottomStyle: 'solid',
                                borderBottomWidth: '1px',
                                borderColor: themeColors.border
                            }"
                            @mouseenter="$event.currentTarget.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.currentTarget.style.backgroundColor = 'transparent'">
                            <td class="px-6 py-4"
                                :style="{ color: themeColors.textPrimary }">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                        <span class="text-xs font-medium text-gray-600">
                                            {{ getInitials(schedule?.assigned_to?.first_name + ' ' + schedule?.assigned_to?.last_name) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="font-medium">
                                            {{ schedule?.assigned_to?.first_name && schedule?.assigned_to?.last_name ? `${schedule.assigned_to.first_name} ${schedule.assigned_to.last_name}` : 'Unassigned' }}
                                        </div>
                                        <div class="text-sm" :style="{ color: themeColors.textSecondary }">
                                            {{ schedule?.assigned_to?.email || '' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="room in schedule?.room_numbers" :key="room"
                                          class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
                                          :style="{ 
                                              backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                              color: themeColors.primary
                                          }">
                                        {{ room }}
                                    </span>
                                    <span v-if="!schedule?.room_numbers || schedule.room_numbers.length === 0"
                                          class="text-sm"
                                          :style="{ color: themeColors.textSecondary }">
                                        No rooms
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4"
                                :style="{ color: themeColors.textPrimary }">
                                <div class="text-sm">
                                    {{ formatDate(schedule?.start_date) }} - {{ formatDate(schedule?.end_date) }}
                                </div>
                            </td>
                            <td class="px-6 py-4"
                                :style="{ color: themeColors.textPrimary }">
                                <div class="text-sm">{{ schedule.preferred_start_time || 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                      :style="getStatusStyle(schedule.status, schedule.is_active)">
                                    {{ formatStatus(schedule.status, schedule.is_active) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <Link :href="route('admin.housekeeping-schedules.show', schedule.id)"
                                          class="transition-colors"
                                          :style="{ color: themeColors.primary }"
                                          @mouseenter="$event.target.style.color = themeColors.hover"
                                          @mouseleave="$event.target.style.color = themeColors.primary">
                                        View
                                    </Link>
                                    <Link :href="route('admin.housekeeping-schedules.edit', schedule.id)"
                                          class="transition-colors"
                                          :style="{ color: themeColors.success }"
                                          @mouseenter="$event.target.style.color = themeColors.hover"
                                          @mouseleave="$event.target.style.color = themeColors.success">
                                        Edit
                                    </Link>
                                    <button v-if="schedule.status === 'active'"
                                            @click="cancelSchedule(schedule.id)"
                                            class="transition-colors"
                                            :style="{ color: themeColors.danger }"
                                            @mouseenter="$event.target.style.color = themeColors.hover"
                                            @mouseleave="$event.target.style.color = themeColors.danger">
                                        Cancel
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="schedules.links" class="px-6 py-4 border-t"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderTopWidth: '1px'
                 }">
                <Pagination :links="schedules.links" />
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import Pagination from '@/Components/Pagination.vue'
import { 
    PlusIcon, 
    DocumentArrowDownIcon,
    CalendarDaysIcon,
    CheckCircleIcon,
    CheckIcon,
    XCircleIcon
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
    schedules: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
            from: 0,
            to: 0,
            total: 0,
            prev_page_url: null,
            next_page_url: null
        })
    }
})

// Computed properties
const activeSchedulesCount = computed(() => {
    return props.schedules?.data?.filter(s => s.status === 'active' && s.is_active).length || 0
})

const completedSchedulesCount = computed(() => {
    return props.schedules?.data?.filter(s => s.status === 'completed').length || 0
})

const cancelledSchedulesCount = computed(() => {
    return props.schedules?.data?.filter(s => s.status === 'cancelled').length || 0
})

// Methods
const getInitials = (name) => {
    if (!name) return ''
    return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    })
}

const formatStatus = (status, isActive) => {
    if (status === 'active' && isActive) return 'Active'
    if (status === 'completed') return 'Completed'
    if (status === 'cancelled') return 'Cancelled'
    if (status === 'pending') return 'Pending'
    return status || 'Unknown'
}

const getStatusStyle = (status, isActive) => {
    if (status === 'active' && isActive) {
        return {
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            color: '#10b981'
        }
    }
    if (status === 'completed') {
        return {
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            color: '#3b82f6'
        }
    }
    if (status === 'cancelled') {
        return {
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            color: '#ef4444'
        }
    }
    if (status === 'pending') {
        return {
            backgroundColor: 'rgba(250, 204, 21, 0.1)',
            color: '#f59e0b'
        }
    }
    return {
        backgroundColor: 'rgba(107, 114, 128, 0.1)',
        color: '#6b7280'
    }
}

const exportSchedules = () => {
    const params = new URLSearchParams()
    window.location.href = route('admin.housekeeping-schedules.export') + '?' + params.toString()
}

const cancelSchedule = (id) => {
    if (confirm('Are you sure you want to cancel this schedule?')) {
        router.delete(route('admin.housekeeping-schedules.destroy', id))
    }
}
</script>
