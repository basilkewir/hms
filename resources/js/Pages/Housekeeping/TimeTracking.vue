<template>
    <DashboardLayout title="Time Tracking" :user="user" :navigation="navigation">
        <!-- Time Tracking Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Time Tracking</h1>
                    <p class="text-gray-600 mt-2">Track your work hours and breaks.</p>
                </div>
                <div class="flex space-x-3">
                    <button v-if="!isClockedIn" @click="clockIn" 
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                        <ClockIcon class="h-4 w-4 mr-2 inline" />
                        Clock In
                    </button>
                    <button v-else @click="clockOut" 
                            class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                        <ClockIcon class="h-4 w-4 mr-2 inline" />
                        Clock Out
                    </button>
                </div>
            </div>
        </div>

        <!-- Current Status -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Current Status</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold" :class="isClockedIn ? 'text-green-600' : 'text-gray-400'">
                        {{ currentTime }}
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Current Time</p>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600">
                        {{ hoursWorkedToday }}
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Hours Today</p>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold" :class="isClockedIn ? 'text-green-600' : 'text-red-600'">
                        {{ isClockedIn ? 'ON DUTY' : 'OFF DUTY' }}
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Status</p>
                </div>
            </div>
        </div>

        <!-- Today's Time Entries -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Today's Time Entries</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Clock In
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Clock Out
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Break Time
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Hours
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="entry in todaysEntries" :key="entry.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatTime(entry.clockIn) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ entry.clockOut ? formatTime(entry.clockOut) : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ entry.breakTime }} min
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ entry.totalHours }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(entry.status)">
                                    {{ entry.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Weekly Summary -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Weekly Summary</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ weeklyStats.totalHours }}</div>
                    <p class="text-sm text-gray-600 mt-1">Total Hours</p>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ weeklyStats.regularHours }}</div>
                    <p class="text-sm text-gray-600 mt-1">Regular Hours</p>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ weeklyStats.overtimeHours }}</div>
                    <p class="text-sm text-gray-600 mt-1">Overtime Hours</p>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ weeklyStats.daysWorked }}</div>
                    <p class="text-sm text-gray-600 mt-1">Days Worked</p>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { ClockIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
})

const navigation = computed(() => getNavigationForRole('housekeeping'))

const currentTime = ref('')
const isClockedIn = ref(false)
const hoursWorkedToday = ref('7.5h')

const todaysEntries = [
    { id: 1, clockIn: '08:00', clockOut: '12:00', breakTime: 30, totalHours: '3.5h', status: 'completed' },
    { id: 2, clockIn: '13:00', clockOut: null, breakTime: 0, totalHours: '4.0h', status: 'active' },
]

const weeklyStats = {
    totalHours: '38.5h',
    regularHours: '35.0h',
    overtimeHours: '3.5h',
    daysWorked: 5,
}

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
}

const clockIn = () => {
    isClockedIn.value = true
    alert('Clocked in successfully!')
}

const clockOut = () => {
    isClockedIn.value = false
    alert('Clocked out successfully!')
}

const getStatusColor = (status) => {
    const colors = {
        active: 'bg-green-100 text-green-800',
        completed: 'bg-blue-100 text-blue-800',
        break: 'bg-yellow-100 text-yellow-800',
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const formatTime = (time) => {
    return time
}
</script>
