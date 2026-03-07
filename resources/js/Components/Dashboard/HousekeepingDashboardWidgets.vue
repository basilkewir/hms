<template>
    <div class="space-y-8">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="(value, key) in quickStats" :key="key"
                 class="shadow rounded-lg p-6 transition-all hover:shadow-lg cursor-pointer"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }"
                 @click="$emit('navigate', getStatRoute(key))">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">{{ getStatLabel(key) }}</p>
                        <p class="text-2xl font-bold mt-2"
                           :style="{ color: themeColors.textPrimary }">{{ value }}</p>
                    </div>
                    <div class="p-3 rounded-full"
                         :style="{ backgroundColor: `${getStatColor(key)}20` }">
                        <component :is="getStatIcon(key)" class="h-6 w-6"
                                   :style="{ color: getStatColor(key) }" />
                    </div>
                </div>
            </div>
        </div>

        <!-- My Tasks -->
        <div class="shadow rounded-lg p-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold"
                    :style="{ color: themeColors.textPrimary }">My Tasks</h2>
                <button class="text-sm font-medium transition-colors"
                        :style="{ color: themeColors.primary }"
                        @click="$emit('navigate', '/housekeeping/tasks')">
                    View All
                </button>
            </div>
            <div class="space-y-4">
                <div v-for="task in myTasks" :key="task.id"
                     class="flex items-center p-4 rounded-lg transition-colors hover:bg-gray-50"
                     :style="{ backgroundColor: themeColors.card }">
                    <div class="p-2 rounded-full mr-4"
                         :style="{ backgroundColor: `${getTaskPriorityColor(task.priority)}20` }">
                        <SparklesIcon class="h-5 w-5"
                                     :style="{ color: getTaskPriorityColor(task.priority) }" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textPrimary }">{{ task.title }}</p>
                        <p class="text-xs"
                           :style="{ color: themeColors.textTertiary }">Room {{ task.room_number }} • {{ task.task_type }}</p>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full"
                          :style="{
                              backgroundColor: `${getTaskStatusColor(task.status)}20`,
                              color: getTaskStatusColor(task.status)
                          }">
                        {{ task.status }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Room Status Overview -->
        <div class="shadow rounded-lg p-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <h2 class="text-lg font-semibold mb-4"
                :style="{ color: themeColors.textPrimary }">Room Status Overview</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="(status, key) in roomStatus" :key="key"
                     class="p-4 rounded-lg text-center cursor-pointer transition-colors hover:opacity-80"
                     :style="{
                         backgroundColor: `${getRoomStatusColor(key)}20`,
                         borderStyle: 'solid',
                         borderWidth: '1px',
                         borderColor: getRoomStatusColor(key)
                     }"
                     @click="$emit('navigate', '/housekeeping/rooms')">
                    <p class="text-2xl font-bold"
                       :style="{ color: getRoomStatusColor(key) }">{{ status }}</p>
                    <p class="text-sm mt-1"
                       :style="{ color: themeColors.textSecondary }">{{ formatRoomStatusKey(key) }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import {
    SparklesIcon,
    HomeIcon,
    ClipboardDocumentListIcon,
    CheckCircleIcon,
    ClockIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    data: Object,
    themeColors: Object
})

const emit = defineEmits(['navigate'])

// Extract data from props
const quickStats = ref(props.data?.quickStats || {})
const myTasks = ref(props.data?.myTasks || [])
const roomStatus = ref(props.data?.roomStatus || {})

// Methods
const getStatLabel = (key) => {
    const labels = {
        pendingTasks: 'Pending Tasks',
        completedToday: 'Completed Today',
        inProgress: 'In Progress',
        overdueTasks: 'Overdue'
    }
    return labels[key] || key
}

const getStatIcon = (key) => {
    const icons = {
        pendingTasks: ClipboardDocumentListIcon,
        completedToday: CheckCircleIcon,
        inProgress: ClockIcon,
        overdueTasks: SparklesIcon
    }
    return icons[key] || ClipboardDocumentListIcon
}

const getStatColor = (key) => {
    const colors = {
        pendingTasks: '#F59E0B',
        completedToday: '#10B981',
        inProgress: '#3B82F6',
        overdueTasks: '#EF4444'
    }
    return colors[key] || '#6B7280'
}

const getStatRoute = (key) => {
    const routes = {
        pendingTasks: '/housekeeping/tasks',
        completedToday: '/housekeeping/tasks',
        inProgress: '/housekeeping/tasks',
        overdueTasks: '/housekeeping/tasks'
    }
    return routes[key] || '/housekeeping/dashboard'
}

const getTaskPriorityColor = (priority) => {
    const colors = {
        high: '#EF4444',
        normal: '#F59E0B',
        low: '#10B981'
    }
    return colors[priority] || '#6B7280'
}

const getTaskStatusColor = (status) => {
    const colors = {
        pending: '#F59E0B',
        in_progress: '#3B82F6',
        completed: '#10B981'
    }
    return colors[status] || '#6B7280'
}

const formatRoomStatusKey = (key) => {
    const formatted = {
        dirty: 'Dirty',
        cleaning: 'Cleaning',
        clean: 'Clean',
        inspection: 'Inspection'
    }
    return formatted[key] || key
}

const getRoomStatusColor = (key) => {
    const colors = {
        dirty: '#EF4444',
        cleaning: '#F59E0B',
        clean: '#10B981',
        inspection: '#3B82F6'
    }
    return colors[key] || '#6B7280'
}

// Watch for data changes
watch(() => props.data, (newData) => {
    if (newData) {
        quickStats.value = newData.quickStats || {}
        myTasks.value = newData.myTasks || []
        roomStatus.value = newData.roomStatus || {}
    }
}, { deep: true })
</script>
