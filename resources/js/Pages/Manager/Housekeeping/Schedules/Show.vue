<template>
    <DashboardLayout title="Housekeeping Schedule Details">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <!-- Header Section -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Housekeeping Schedule #{{ schedule.id }}</h1>
                    <div class="flex items-center gap-4">
                        <p class="text-sm"
                           :style="{ color: themeColors.textSecondary }">Status:
                            <span class="px-2 py-1 text-xs rounded-full ml-1" :style="getStatusStyle(schedule.status)">
                                {{ formatStatus(schedule.status) }}
                            </span>
                        </p>
                        <p class="text-sm"
                           :style="{ color: themeColors.textSecondary }">
                            Created: {{ formatDate(schedule.created_at) }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button v-if="schedule.status === 'pending'"
                            @click="startSchedule"
                            class="px-4 py-2 rounded-md transition-colors text-white font-medium"
                            :style="{ 
                                backgroundColor: themeColors.success,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                        Start
                    </button>
                    <button v-if="schedule.status === 'in_progress'"
                            @click="completeSchedule"
                            class="px-4 py-2 rounded-md transition-colors text-white font-medium"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        Complete
                    </button>
                    <Link :href="route('admin.housekeeping-schedules.edit', schedule.id)"
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                          :style="{ 
                              backgroundColor: themeColors.warning,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = '#d97706'"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.warning">
                        Edit
                    </Link>
                    <Link :href="route('admin.housekeeping-schedules.index')"
                          class="px-4 py-2 rounded-md transition-colors font-medium"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                              color: 'white'
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        Back
                    </Link>
                </div>
            </div>

            <!-- Staff Information Section -->
            <div class="mb-8">
                <h3 class="text-lg font-medium mb-4"
                    :style="{ color: themeColors.textPrimary }">Staff Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="rounded-lg p-4 border"
                         :style="{ 
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '80px' }">Assigned To:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ schedule.assigned_to?.first_name && schedule.assigned_to?.last_name ? `${schedule.assigned_to.first_name} ${schedule.assigned_to.last_name}` : 'Unassigned' }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '80px' }">Email:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ schedule.assigned_to?.email || 'N/A' }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '80px' }">Department:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ schedule.assigned_to?.department?.name || 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg p-4 border"
                         :style="{ 
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '120px' }">Start Date:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ formatDate(schedule.start_date) }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '120px' }">End Date:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ formatDate(schedule.end_date) }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '120px' }">Preferred Time:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ schedule.preferred_start_time || 'Not specified' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rooms Section -->
            <div class="mb-8">
                <h3 class="text-lg font-medium mb-4"
                    :style="{ color: themeColors.textPrimary }">Assigned Rooms ({{ schedule.room_numbers?.length || 0 }})</h3>
                <div class="rounded-lg p-4 border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }">
                    <div v-if="schedule.room_numbers && schedule.room_numbers.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div
                            v-for="roomNumber in schedule.room_numbers"
                            :key="roomNumber"
                            class="rounded-lg p-4 border text-center"
                            :style="{ 
                                backgroundColor: themeColors.card,
                                borderColor: themeColors.border,
                                borderStyle: 'solid',
                                borderWidth: '1px'
                            }"
                        >
                            <div class="text-lg font-semibold"
                                 :style="{ color: themeColors.primary }">{{ roomNumber }}</div>
                            <div class="text-xs mt-1"
                                 :style="{ color: themeColors.textSecondary }">Room</div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8"
                         :style="{ color: themeColors.textSecondary }">
                        No rooms assigned to this schedule
                    </div>
                </div>
            </div>

            <!-- Notes Section -->
            <div v-if="schedule.notes" class="mb-8">
                <h3 class="text-lg font-medium mb-4"
                    :style="{ color: themeColors.textPrimary }">Notes</h3>
                <div class="rounded-lg p-4 border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }">
                    <p class="text-sm whitespace-pre-wrap"
                       :style="{ color: themeColors.textPrimary }">{{ schedule.notes }}</p>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'

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
    schedule: Object,
})

// Methods
const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    })
}

const formatStatus = (status) => {
    if (!status) return 'N/A'
    return status.charAt(0).toUpperCase() + status.slice(1).replace(/_/g, ' ')
}

const getStatusStyle = (status) => {
    switch (status) {
        case 'pending':
            return {
                backgroundColor: 'rgba(250, 204, 21, 0.1)',
                color: '#f59e0b'
            }
        case 'in_progress':
            return {
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                color: '#3b82f6'
            }
        case 'completed':
            return {
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                color: '#10b981'
            }
        case 'cancelled':
            return {
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                color: '#ef4444'
            }
        default:
            return {
                backgroundColor: 'rgba(107, 114, 128, 0.1)',
                color: '#6b7280'
            }
    }
}

const startSchedule = () => {
    router.put(route('admin.housekeeping-schedules.update-status', props.schedule.id), {
        status: 'in_progress'
    })
}

const completeSchedule = () => {
    router.put(route('admin.housekeeping-schedules.update-status', props.schedule.id), {
        status: 'completed'
    })
}
</script>
