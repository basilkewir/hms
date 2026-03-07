<template>
    <DashboardLayout title="Time Tracking" :user="user" :navigation="navigation">
        <!-- Time Tracking Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Maintenance Time Tracking</h1>
                    <p class="text-gray-600 mt-2">Track work hours and maintenance tasks.</p>
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
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
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
                    <div class="text-3xl font-bold text-purple-600">
                        {{ tasksCompleted }}
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Tasks Completed</p>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold" :class="isClockedIn ? 'text-green-600' : 'text-red-600'">
                        {{ isClockedIn ? 'ON DUTY' : 'OFF DUTY' }}
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Status</p>
                </div>
            </div>
        </div>

        <!-- Active Tasks -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Active Maintenance Tasks</h3>
            <div class="space-y-4">
                <div v-for="task in activeTasks" :key="task.id"
                     class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium text-gray-900">{{ task.title }}</h4>
                        <span class="text-xs px-2 py-1 rounded-full"
                              :class="getPriorityColor(task.priority)">
                            {{ task.priority }}
                        </span>
                    </div>
                    <div class="text-sm text-gray-600 mb-3">
                        <p><strong>Room:</strong> {{ task.room }}</p>
                        <p><strong>Category:</strong> {{ task.category }}</p>
                        <p><strong>Assigned:</strong> {{ formatDate(task.assignedDate) }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex space-x-2">
                            <button v-if="!task.isStarted" @click="startTask(task)"
                                    class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                Start Task
                            </button>
                            <button v-else @click="completeTask(task)"
                                    class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                Complete Task
                            </button>
                        </div>
                        <span class="text-sm text-gray-500">
                            {{ task.isStarted ? 'In Progress' : 'Not Started' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Time Entries -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Today's Time Entries</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Task
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Start Time
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                End Time
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Duration
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="entry in timeEntries" :key="entry.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ entry.taskName }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatTime(entry.startTime) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ entry.endTime ? formatTime(entry.endTime) : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ entry.duration }}
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

const navigation = computed(() => getNavigationForRole('maintenance'))

const currentTime = ref('')
const isClockedIn = ref(false)
const hoursWorkedToday = ref('6.5h')
const tasksCompleted = ref(3)

const activeTasks = ref([
    { id: 1, title: 'Fix AC Unit in Room 205', room: '205', category: 'HVAC', priority: 'high', assignedDate: new Date(), isStarted: false },
    { id: 2, title: 'Replace TV Remote in Room 301', room: '301', category: 'IPTV', priority: 'low', assignedDate: new Date(), isStarted: true },
    { id: 3, title: 'Repair Bathroom Faucet', room: '102', category: 'Plumbing', priority: 'medium', assignedDate: new Date(), isStarted: false },
])

const timeEntries = [
    { id: 1, taskName: 'HVAC Maintenance - Room 205', startTime: '08:00', endTime: '10:30', duration: '2.5h', status: 'completed' },
    { id: 2, taskName: 'IPTV Setup - Room 301', startTime: '11:00', endTime: null, duration: '1.5h', status: 'active' },
    { id: 3, taskName: 'Plumbing Repair - Room 102', startTime: '14:00', endTime: '15:30', duration: '1.5h', status: 'completed' },
]

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

const startTask = (task) => {
    task.isStarted = true
    alert(`Started task: ${task.title}`)
}

const completeTask = (task) => {
    const index = activeTasks.value.findIndex(t => t.id === task.id)
    if (index > -1) {
        activeTasks.value.splice(index, 1)
        tasksCompleted.value++
        alert(`Completed task: ${task.title}`)
    }
}

const getPriorityColor = (priority) => {
    const colors = {
        low: 'bg-gray-100 text-gray-800',
        medium: 'bg-blue-100 text-blue-800',
        high: 'bg-yellow-100 text-yellow-800',
        urgent: 'bg-red-100 text-red-800',
    }
    return colors[priority] || 'bg-gray-100 text-gray-800'
}

const getStatusColor = (status) => {
    const colors = {
        active: 'bg-green-100 text-green-800',
        completed: 'bg-blue-100 text-blue-800',
        paused: 'bg-yellow-100 text-yellow-800',
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const formatDate = (date) => {
    return date.toLocaleDateString()
}

const formatTime = (time) => {
    return time
}
</script>
