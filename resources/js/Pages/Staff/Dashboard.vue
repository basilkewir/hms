<template>
    <DashboardLayout title="Staff Dashboard" :user="user">
        <!-- Dashboard Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Welcome, {{ user.first_name }}!</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Check your schedule and manage your work activities.</p>
                </div>
                <div class="text-right">
                    <p class="text-sm"
                       :style="{ color: themeColors.textTertiary }">{{ currentDateTime }}</p>
                    <p class="text-lg font-semibold"
                       :style="{ color: themeColors.textPrimary }">{{ user.roles[0]?.display_name }}</p>
                </div>
            </div>
        </div>

        <!-- Time Tracking & Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Clock In/Out -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Time Tracking</h3>
                <div class="text-center">
                    <div class="mb-4">
                        <div class="text-3xl font-bold"
                             :style="{ color: timeStatus.isClocked ? themeColors.success : themeColors.textTertiary }">
                            {{ currentTime }}
                        </div>
                        <p class="text-sm"
                           :style="{ color: themeColors.textTertiary }">Current Time</p>
                    </div>
                    <div class="mb-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                              :style="timeStatus.isClocked ? 'rgba(16, 185, 129, 0.1) rgb(16, 185, 129) 1px solid rgb(16, 185, 129)' : 'rgba(107, 114, 128, 0.1) rgb(107, 114, 128) 1px solid rgb(107, 114, 128)'">
                            {{ timeStatus.isClocked ? 'Clocked In' : 'Clocked Out' }}
                        </span>
                    </div>
                    <Link :href="route('staff.time-tracking.clock')"
                          class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium rounded transition-colors"
                          :style="{ 
                              backgroundColor: timeStatus.isClocked ? themeColors.danger : themeColors.success,
                              color: 'white'
                          }"
                          @mouseenter="$event.target.style.opacity = '0.8'"
                          @mouseleave="$event.target.style.opacity = '1'">
                        <ClockIcon class="h-4 w-4 mr-2" />
                        {{ timeStatus.isClocked ? 'Clock Out' : 'Clock In' }}
                    </Link>
                </div>
            </div>

            <!-- Today's Hours -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Today's Hours</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm"
                              :style="{ color: themeColors.textTertiary }">Clock In:</span>
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textPrimary }">{{ timeStatus.clockInTime || 'Not clocked in' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm"
                              :style="{ color: themeColors.textTertiary }">Hours Worked:</span>
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textPrimary }">{{ timeStatus.hoursWorked }}h</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm"
                              :style="{ color: themeColors.textTertiary }">Break Time:</span>
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textPrimary }">{{ timeStatus.breakTime }}m</span>
                    </div>
                    <div class="flex justify-between pt-2"
                         :style="{ 
                             borderTop: '1px solid ' + themeColors.border
                         }">
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textPrimary }">Net Hours:</span>
                        <span class="text-sm font-bold"
                              :style="{ color: themeColors.textPrimary }">{{ timeStatus.netHours }}h</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Quick Actions</h3>
                <div class="space-y-3">
                    <Link :href="route('staff.time-tracking.timesheet')"
                          class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium rounded transition-colors"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                              color: 'white'
                          }"
                          @mouseenter="$event.target.style.opacity = '0.8'"
                          @mouseleave="$event.target.style.opacity = '1'">
                        <DocumentTextIcon class="h-4 w-4 mr-2" />
                        View Timesheet
                    </Link>
                    <Link :href="route('staff.tasks.assigned')"
                          class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium rounded transition-colors"
                          :style="{ 
                              backgroundColor: themeColors.success,
                              color: 'white'
                          }"
                          @mouseenter="$event.target.style.opacity = '0.8'"
                          @mouseleave="$event.target.style.opacity = '1'">
                        <ClipboardDocumentListIcon class="h-4 w-4 mr-2" />
                        My Tasks
                    </Link>
                    <Link :href="route('staff.profile')"
                          class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium rounded transition-colors"
                          :style="{ 
                              backgroundColor: themeColors.secondary,
                              color: 'white'
                          }"
                          @mouseenter="$event.target.style.opacity = '0.8'"
                          @mouseleave="$event.target.style.opacity = '1'">
                        <UserIcon class="h-4 w-4 mr-2" />
                        My Profile
                    </Link>
                    <Link :href="route('staff.messages')"
                          class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium rounded transition-colors"
                          :style="{ 
                              backgroundColor: themeColors.warning,
                              color: 'white'
                          }"
                          @mouseenter="$event.target.style.opacity = '0.8'"
                          @mouseleave="$event.target.style.opacity = '1'">
                        <ChatBubbleLeftIcon class="h-4 w-4 mr-2" />
                        Messages
                    </Link>
                </div>
            </div>
        </div>

        <!-- Schedule & Tasks -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- My Schedule -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">My Schedule</h3>
                <div class="space-y-3">
                    <div v-for="shift in weeklySchedule" :key="shift.date"
                         class="flex items-center justify-between p-3 rounded-lg"
                         :style="shift.isToday ? 'rgba(59, 130, 246, 0.1) rgb(59, 130, 246) 1px solid rgb(59, 130, 246)' : themeColors.background + ' ' + themeColors.border + ' 1px solid ' + themeColors.border">
                        <div>
                            <p class="text-sm font-medium"
                               :style="{ color: shift.isToday ? 'rgb(59, 130, 246)' : themeColors.textPrimary }">
                                {{ formatDate(shift.date) }}
                            </p>
                            <p class="text-xs"
                               :style="{ color: shift.isToday ? 'rgb(59, 130, 246)' : themeColors.textTertiary }">
                                {{ shift.dayName }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium"
                               :style="{ color: shift.isToday ? 'rgb(59, 130, 246)' : themeColors.textPrimary }">
                                {{ shift.startTime }} - {{ shift.endTime }}
                            </p>
                            <p class="text-xs"
                               :style="{ color: shift.isToday ? 'rgb(59, 130, 246)' : themeColors.textTertiary }">
                                {{ shift.shiftName }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assigned Tasks -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Assigned Tasks</h3>
                <div class="space-y-3">
                    <div v-for="task in assignedTasks" :key="task.id"
                         class="flex items-center justify-between p-3 rounded-lg"
                         :style="{ 
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   :checked="task.completed"
                                   class="h-4 w-4 mr-3">
                            <div>
                                <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ task.title }}</p>
                                <p class="text-xs"
                                   :style="{ color: themeColors.textTertiary }">{{ task.description }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                  :style="getTaskStatusStyle(task.status)">
                                {{ task.status }}
                            </span>
                            <p class="text-xs font-medium mt-1"
                               :style="{ color: themeColors.primary }">Due: {{ formatDate(task.due_date) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages & Announcements -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Messages -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Recent Messages</h3>
                <div class="space-y-3">
                    <div v-for="message in recentMessages" :key="message.id"
                         class="flex items-start space-x-3 p-3 rounded-lg"
                         :style="{ 
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center"
                             :style="{ 
                                 backgroundColor: themeColors.primary,
                                 color: 'white'
                             }">
                            <span class="text-xs font-medium">
                                {{ message.sender_name.charAt(0) }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textPrimary }">{{ message.sender_name }}</p>
                            <p class="text-sm"
                               :style="{ color: themeColors.textSecondary }">{{ message.subject }}</p>
                            <p class="text-xs"
                               :style="{ color: themeColors.textTertiary }">{{ formatDateTime(message.sent_at) }}</p>
                        </div>
                        <div v-if="!message.read" class="w-2 h-2 rounded-full"
                             :style="{ backgroundColor: themeColors.primary }"></div>
                    </div>
                </div>
            </div>

            <!-- Announcements -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Announcements</h3>
                <div class="space-y-3">
                    <div v-for="announcement in announcements" :key="announcement.id"
                         class="p-3 rounded-lg"
                         :style="{ 
                             backgroundColor: 'rgba(245, 158, 11, 0.1)',
                             border: '1px solid rgb(245, 158, 11)'
                         }">
                        <div class="flex items-start">
                            <MegaphoneIcon class="h-5 w-5 mr-3 mt-0.5"
                                          :style="{ color: 'rgb(245, 158, 11)' }" />
                            <div class="flex-1">
                                <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ announcement.title }}</p>
                                <p class="text-sm mt-1"
                                   :style="{ color: themeColors.textSecondary }">{{ announcement.content }}</p>
                                <p class="text-xs mt-2"
                                   :style="{ color: themeColors.textTertiary }">{{ formatDate(announcement.posted_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import {
    ClockIcon,
    DocumentTextIcon,
    ClipboardDocumentListIcon,
    UserIcon,
    ChatBubbleLeftIcon,
    MegaphoneIcon
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
    timeStatus: Object,
    weeklySchedule: Array,
    assignedTasks: Array,
    recentMessages: Array,
    announcements: Array,
})

const currentTime = ref('')

const currentDateTime = computed(() => {
    return new Date().toLocaleString()
})

const getTaskStatusStyle = (status) => {
    const styles = {
        pending: 'rgba(16, 185, 129, 0.1) rgb(16, 185, 129) 1px solid rgb(16, 185, 129)',
        in_progress: 'rgba(59, 130, 246, 0.1) rgb(59, 130, 246) 1px solid rgb(59, 130, 246)',
        completed: 'rgba(16, 185, 129, 0.1) rgb(16, 185, 129) 1px solid rgb(16, 185, 129)',
        overdue: 'rgba(239, 68, 68, 0.1) rgb(239, 68, 68) 1px solid rgb(239, 68, 68)',
    }
    return styles[status] || 'rgba(107, 114, 128, 0.1) rgb(107, 114, 128) 1px solid rgb(107, 114, 128)'
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const formatDateTime = (datetime) => {
    return new Date(datetime).toLocaleString()
}

// Update current time every second
onMounted(() => {
    const updateTime = () => {
        currentTime.value = new Date().toLocaleTimeString()
    }
    updateTime()
    setInterval(updateTime, 1000)
})
</script>

<style scoped>
/* Fix placeholder colors for inputs */
input::placeholder,
textarea::placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input::-webkit-input-placeholder,
textarea::-webkit-input-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input::-moz-placeholder,
textarea::-moz-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input:-ms-input-placeholder,
textarea:-ms-input-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

/* Fix placeholder colors for select options */
select option:disabled,
select option[disabled] {
    color: var(--kotel-text-tertiary) !important;
}

select option[value=""] {
    color: var(--kotel-text-tertiary) !important;
}
</style>
