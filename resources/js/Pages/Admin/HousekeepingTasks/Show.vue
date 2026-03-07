<template>
    <DashboardLayout title="Housekeeping Task Details">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Task Details</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Room: {{ task.room?.room_number || 'N/A' }}</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="`/admin/housekeeping-tasks/${task.id}/edit`"
                          class="px-4 py-2 rounded-md transition-colors text-white font-medium"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        Edit Task
                    </Link>
                    <Link href="/admin/housekeeping-tasks"
                          class="px-4 py-2 rounded-md transition-colors font-medium"
                          :style="{ 
                              backgroundColor: themeColors.secondary,
                              color: themeColors.textPrimary 
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        <ArrowLeftIcon class="h-4 w-4 mr-2 inline" />
                        Back
                    </Link>
                </div>
            </div>

            <!-- Task Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="rounded-lg p-4 border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="flex items-center mb-3">
                        <div class="p-2 rounded-md mr-3"
                             :style="{ backgroundColor: themeColors.primary + '20' }">
                            <svg class="h-6 w-6" :style="{ color: themeColors.primary }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold"
                                :style="{ color: themeColors.textPrimary }">Task Type</h3>
                            <p class="text-sm"
                               :style="{ color: themeColors.textSecondary }">{{ formatTaskType(task.task_type) }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-lg p-4 border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="flex items-center mb-3">
                        <div class="p-2 rounded-md mr-3"
                             :style="{ backgroundColor: getPriorityColor(task.priority) + '20' }">
                            <svg class="h-6 w-6" :style="{ color: getPriorityColor(task.priority) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold"
                                :style="{ color: themeColors.textPrimary }">Priority</h3>
                            <p class="text-sm capitalize"
                               :style="{ color: themeColors.textSecondary }">{{ task.priority }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-lg p-4 border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="flex items-center mb-3">
                        <div class="p-2 rounded-md mr-3"
                             :style="{ backgroundColor: getStatusColor(task.status) + '20' }">
                            <svg class="h-6 w-6" :style="{ color: getStatusColor(task.status) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold"
                                :style="{ color: themeColors.textPrimary }">Status</h3>
                            <p class="text-sm capitalize"
                               :style="{ color: themeColors.textSecondary }">{{ formatStatus(task.status) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Task Information -->
                <div class="rounded-lg p-6 border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Task Information</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b"
                             :style="{ borderColor: themeColors.border, borderBottomWidth: '1px', borderBottomStyle: 'solid' }">
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">Room</span>
                            <span class="text-sm"
                                  :style="{ color: themeColors.textPrimary }">{{ task.room?.room_number || 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b"
                             :style="{ borderColor: themeColors.border, borderBottomWidth: '1px', borderBottomStyle: 'solid' }">
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">Room Type</span>
                            <span class="text-sm"
                                  :style="{ color: themeColors.textPrimary }">{{ task.room?.room_type?.name || 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b"
                             :style="{ borderColor: themeColors.border, borderBottomWidth: '1px', borderBottomStyle: 'solid' }">
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">Assigned To</span>
                            <span class="text-sm"
                                  :style="{ color: themeColors.textPrimary }">{{ task.assigned_to?.name || 'Unassigned' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b"
                             :style="{ borderColor: themeColors.border, borderBottomWidth: '1px', borderBottomStyle: 'solid' }">
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">Scheduled Date</span>
                            <span class="text-sm"
                                  :style="{ color: themeColors.textPrimary }">{{ formatDate(task.scheduled_date) }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">Scheduled Time</span>
                            <span class="text-sm"
                                  :style="{ color: themeColors.textPrimary }">{{ task.scheduled_time || 'Not specified' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Time Tracking -->
                <div class="rounded-lg p-6 border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Time Tracking</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b"
                             :style="{ borderColor: themeColors.border, borderBottomWidth: '1px', borderBottomStyle: 'solid' }">
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">Estimated Duration</span>
                            <span class="text-sm"
                                  :style="{ color: themeColors.textPrimary }">{{ task.estimated_minutes ? `${task.estimated_minutes} minutes` : 'Not specified' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b"
                             :style="{ borderColor: themeColors.border, borderBottomWidth: '1px', borderBottomStyle: 'solid' }">
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">Actual Duration</span>
                            <span class="text-sm"
                                  :style="{ color: themeColors.textPrimary }">{{ task.actual_minutes ? `${task.actual_minutes} minutes` : 'Not completed' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b"
                             :style="{ borderColor: themeColors.border, borderBottomWidth: '1px', borderBottomStyle: 'solid' }">
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">Started At</span>
                            <span class="text-sm"
                                  :style="{ color: themeColors.textPrimary }">{{ task.started_at ? formatDateTime(task.started_at) : 'Not started' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">Completed At</span>
                            <span class="text-sm"
                                  :style="{ color: themeColors.textPrimary }">{{ task.completed_at ? formatDateTime(task.completed_at) : 'Not completed' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div v-if="task.instructions" class="mt-6 rounded-lg p-6 border"
                 :style="{ 
                     backgroundColor: themeColors.background,
                     borderColor: themeColors.border,
                     borderWidth: '1px',
                     borderStyle: 'solid'
                 }">
                <h3 class="text-lg font-medium mb-4"
                    :style="{ color: themeColors.textPrimary }">Special Instructions</h3>
                <p class="text-sm whitespace-pre-wrap"
                   :style="{ color: themeColors.textPrimary }">{{ task.instructions }}</p>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex items-center justify-end space-x-4 pt-6 border-t"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderTopWidth: '1px',
                     borderTopStyle: 'solid'
                 }">
                <button @click="deleteTask"
                        class="px-4 py-2 rounded-md transition-colors font-medium"
                        :style="{ 
                            backgroundColor: themeColors.danger,
                            color: 'white'
                        }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.danger">
                    Delete Task
                </button>
                <Link :href="`/admin/housekeeping-tasks/${task.id}/edit`"
                      class="px-4 py-2 rounded-md transition-colors text-white font-medium"
                      :style="{ 
                          backgroundColor: themeColors.primary,
                      }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                    Edit Task
                </Link>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
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
    task: Object,
})

const getStatusColor = (status) => {
    const colors = {
        pending: themeColors.value.warning,
        in_progress: themeColors.value.primary,
        completed: themeColors.value.success,
        skipped: themeColors.value.secondary
    }
    return colors[status] || colors.pending
}

const getPriorityColor = (priority) => {
    const colors = {
        low: themeColors.value.secondary,
        normal: themeColors.value.primary,
        high: themeColors.value.warning,
        urgent: themeColors.value.danger
    }
    return colors[priority] || colors.normal
}

const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const formatDateTime = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const formatTaskType = (type) => {
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatStatus = (status) => {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const deleteTask = () => {
    if (confirm('Are you sure you want to delete this task?')) {
        router.delete(`/admin/housekeeping-tasks/${props.task.id}`)
    }
}
</script>
