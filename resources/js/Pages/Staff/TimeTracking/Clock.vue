<template>
    <DashboardLayout title="Time Clock" :user="user">
        <!-- Clock Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Time Clock</h1>
                    <p class="text-gray-600 mt-2">Clock in and out for your shifts.</p>
                </div>
            </div>
        </div>

        <!-- Current Time Display -->
        <div class="bg-white shadow rounded-lg p-8 mb-8 text-center">
            <div class="text-6xl font-bold text-gray-900 mb-4">{{ currentTime }}</div>
            <div class="text-xl text-gray-600 mb-6">{{ currentDate }}</div>
            
            <div class="flex justify-center space-x-4">
                <button v-if="!isClockedIn" @click="clockIn" 
                        class="bg-green-600 text-white px-8 py-4 rounded-lg text-lg font-medium hover:bg-green-700 transition-colors">
                    <ClockIcon class="h-6 w-6 mr-2 inline" />
                    Clock In
                </button>
                <button v-else @click="clockOut" 
                        class="bg-red-600 text-white px-8 py-4 rounded-lg text-lg font-medium hover:bg-red-700 transition-colors">
                    <ClockIcon class="h-6 w-6 mr-2 inline" />
                    Clock Out
                </button>
                
                <button v-if="isClockedIn && !isOnBreak" @click="startBreak" 
                        class="bg-yellow-600 text-white px-8 py-4 rounded-lg text-lg font-medium hover:bg-yellow-700 transition-colors">
                    <PauseIcon class="h-6 w-6 mr-2 inline" />
                    Start Break
                </button>
                <button v-else-if="isClockedIn && isOnBreak" @click="endBreak" 
                        class="bg-blue-600 text-white px-8 py-4 rounded-lg text-lg font-medium hover:bg-blue-700 transition-colors">
                    <PlayIcon class="h-6 w-6 mr-2 inline" />
                    End Break
                </button>
            </div>
        </div>

        <!-- Current Status -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Current Status</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold" :class="getStatusColor()">
                        {{ currentStatus }}
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Current Status</p>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600">
                        {{ hoursWorkedToday }}
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Hours Today</p>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-600">
                        {{ totalBreakTime }}
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Break Time</p>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600">
                        {{ netHours }}
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Net Hours</p>
                </div>
            </div>
        </div>

        <!-- Today's Activity -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Today's Activity</h3>
            <div class="space-y-4">
                <div v-for="activity in todaysActivity" :key="activity.id"
                     class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <component :is="getActivityIcon(activity.type)" 
                                   class="h-5 w-5 mr-3" 
                                   :class="getActivityColor(activity.type)" />
                        <div>
                            <p class="font-medium text-gray-900">{{ activity.action }}</p>
                            <p class="text-sm text-gray-600">{{ activity.time }}</p>
                        </div>
                    </div>
                    <span class="text-sm text-gray-500">{{ activity.duration }}</span>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { ClockIcon, PauseIcon, PlayIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    timeEntry: Object,
    isClockedIn: Boolean,
    isOnBreak: Boolean,
    hoursWorkedToday: String,
    totalBreakTime: String,
    netHours: String,
    todaysActivity: {
        type: Array,
        default: () => []
    }
})

const currentTime = ref('')
const currentDate = ref('')

let timeInterval = null

onMounted(() => {
    updateCurrentTime()
    timeInterval = setInterval(updateCurrentTime, 1000)
})

onUnmounted(() => {
    if (timeInterval) {
        clearInterval(timeInterval)
    }
})

const updateCurrentTime = () => {
    const now = new Date()
    currentTime.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
    currentDate.value = now.toLocaleDateString([], { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    })
}

const currentStatus = computed(() => {
    if (!props.isClockedIn) return 'OFF DUTY'
    if (props.isOnBreak) return 'ON BREAK'
    return 'ON DUTY'
})

const getStatusColor = () => {
    if (!props.isClockedIn) return 'text-red-600'
    if (props.isOnBreak) return 'text-yellow-600'
    return 'text-green-600'
}

const clockIn = () => {
    router.post(route('staff.time-tracking.clock-in'))
}

const clockOut = () => {
    router.post(route('staff.time-tracking.clock-out'))
}

const startBreak = () => {
    router.post(route('staff.time-tracking.break.start'))
}

const endBreak = () => {
    router.post(route('staff.time-tracking.break.end'))
}

const getActivityIcon = (type) => {
    const icons = {
        clock_in: ClockIcon,
        clock_out: ClockIcon,
        break_start: PauseIcon,
        break_end: PlayIcon,
    }
    return icons[type] || ClockIcon
}

const getActivityColor = (type) => {
    const colors = {
        clock_in: 'text-green-500',
        clock_out: 'text-red-500',
        break_start: 'text-yellow-500',
        break_end: 'text-blue-500',
    }
    return colors[type] || 'text-gray-500'
}
</script>
