<template>
    <div class="space-y-8">
        <!-- Personal Information -->
        <div class="shadow rounded-lg p-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <h2 class="text-lg font-semibold mb-4"
                :style="{ color: themeColors.textPrimary }">Personal Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm font-medium"
                       :style="{ color: themeColors.textSecondary }">Name</p>
                    <p class="text-lg"
                       :style="{ color: themeColors.textPrimary }">{{ user.name }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium"
                       :style="{ color: themeColors.textSecondary }">Email</p>
                    <p class="text-lg"
                       :style="{ color: themeColors.textPrimary }">{{ user.email }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium"
                       :style="{ color: themeColors.textSecondary }">Department</p>
                    <p class="text-lg"
                       :style="{ color: themeColors.textPrimary }">{{ user.department || 'Not Assigned' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium"
                       :style="{ color: themeColors.textSecondary }">Position</p>
                    <p class="text-lg"
                       :style="{ color: themeColors.textPrimary }">{{ user.position || 'Not Assigned' }}</p>
                </div>
            </div>
        </div>

        <!-- Today's Schedule -->
        <div class="shadow rounded-lg p-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold"
                    :style="{ color: themeColors.textPrimary }">Today's Schedule</h2>
                <button class="text-sm font-medium transition-colors"
                        :style="{ color: themeColors.primary }"
                        @click="$emit('navigate', '/staff/schedule')">
                    View Full Schedule
                </button>
            </div>
            <div class="space-y-4">
                <div v-for="shift in todaySchedule" :key="shift.id"
                     class="flex items-center p-4 rounded-lg transition-colors hover:bg-gray-50"
                     :style="{ backgroundColor: themeColors.card }">
                    <div class="p-2 rounded-full mr-4"
                         :style="{ backgroundColor: `${themeColors.primary}20` }">
                        <ClockIcon class="h-5 w-5"
                                  :style="{ color: themeColors.primary }" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textPrimary }">{{ shift.shift_type }}</p>
                        <p class="text-xs"
                           :style="{ color: themeColors.textTertiary }">{{ formatTime(shift.start_time) }} - {{ formatTime(shift.end_time) }}</p>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full"
                          :style="{
                              backgroundColor: `${getShiftStatusColor(shift.status)}20`,
                              color: getShiftStatusColor(shift.status)
                          }">
                        {{ shift.status }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="shadow rounded-lg p-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <h2 class="text-lg font-semibold mb-4"
                :style="{ color: themeColors.textPrimary }">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <button v-for="action in quickActions" :key="action.key"
                        class="p-4 rounded-lg text-center transition-colors hover:opacity-80"
                        :style="{
                            backgroundColor: `${action.color}20`,
                            borderStyle: 'solid',
                            borderWidth: '1px',
                            borderColor: action.color
                        }"
                        @click="$emit('navigate', action.route)">
                    <component :is="action.icon" class="h-8 w-8 mx-auto mb-2"
                               :style="{ color: action.color }" />
                    <p class="text-sm font-medium"
                       :style="{ color: action.color }">{{ action.label }}</p>
                </button>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="shadow rounded-lg p-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <h2 class="text-lg font-semibold mb-4"
                :style="{ color: themeColors.textPrimary }">Recent Activity</h2>
            <div class="space-y-4">
                <div v-for="activity in recentActivity" :key="activity.id"
                     class="flex items-center p-4 rounded-lg transition-colors hover:bg-gray-50"
                     :style="{ backgroundColor: themeColors.card }">
                    <div class="p-2 rounded-full mr-4"
                         :style="{ backgroundColor: `${themeColors.primary}20` }">
                        <component :is="getActivityIcon(activity.type)" class="h-5 w-5"
                                   :style="{ color: themeColors.primary }" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textPrimary }">{{ activity.description }}</p>
                        <p class="text-xs"
                           :style="{ color: themeColors.textTertiary }">{{ formatDate(activity.created_at) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import {
    ClockIcon,
    UserIcon,
    CalendarDaysIcon,
    DocumentTextIcon,
    BellIcon,
    CreditCardIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    data: Object,
    themeColors: Object
})

const emit = defineEmits(['navigate'])

// Extract data from props
const todaySchedule = ref(props.data?.todaySchedule || [])
const recentActivity = ref(props.data?.recentActivity || [])

// Quick actions based on user role
const quickActions = ref([
    {
        key: 'profile',
        label: 'My Profile',
        icon: UserIcon,
        color: '#3B82F6',
        route: '/profile'
    },
    {
        key: 'schedule',
        label: 'My Schedule',
        icon: CalendarDaysIcon,
        color: '#10B981',
        route: '/staff/schedule'
    },
    {
        key: 'requests',
        label: 'My Requests',
        icon: DocumentTextIcon,
        color: '#F59E0B',
        route: '/staff/requests'
    },
    {
        key: 'messages',
        label: 'Messages',
        icon: BellIcon,
        color: '#8B5CF6',
        route: '/staff/messages'
    },
    {
        key: 'expenses',
        label: 'Expenses',
        icon: CreditCardIcon,
        color: '#EF4444',
        route: '/staff/expenses'
    },
    {
        key: 'timecard',
        label: 'Time Card',
        icon: ClockIcon,
        color: '#6B7280',
        route: '/staff/timecard'
    }
])

// Methods
const formatTime = (timeString) => {
    if (!timeString) return ''
    const date = new Date(timeString)
    return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    })
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getShiftStatusColor = (status) => {
    const colors = {
        scheduled: '#3B82F6',
        in_progress: '#10B981',
        completed: '#6B7280',
        absent: '#EF4444'
    }
    return colors[status] || '#6B7280'
}

const getActivityIcon = (type) => {
    const icons = {
        clock_in: ClockIcon,
        clock_out: ClockIcon,
        task: DocumentTextIcon,
        message: BellIcon
    }
    return icons[type] || DocumentTextIcon
}

// Watch for data changes
watch(() => props.data, (newData) => {
    if (newData) {
        todaySchedule.value = newData.todaySchedule || []
        recentActivity.value = newData.recentActivity || []
    }
}, { deep: true })
</script>
