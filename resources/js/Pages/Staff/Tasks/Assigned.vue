<template>
    <DashboardLayout title="My Assigned Tasks" :user="user" :navigation="navigation">
        <!-- Tasks Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">My Assigned Tasks</h1>
                    <p class="text-gray-600 mt-2">View and manage your assigned tasks.</p>
                </div>
                <div class="flex space-x-3">
                    <select v-model="filterStatus" 
                            class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Tasks</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Task Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <ClipboardDocumentListIcon class="h-8 w-8 text-blue-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Tasks</p>
                        <p class="text-2xl font-bold text-gray-900">{{ taskStats.total }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <ClockIcon class="h-8 w-8 text-yellow-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Pending</p>
                        <p class="text-2xl font-bold text-gray-900">{{ taskStats.pending }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <PlayIcon class="h-8 w-8 text-purple-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">In Progress</p>
                        <p class="text-2xl font-bold text-gray-900">{{ taskStats.inProgress }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <CheckCircleIcon class="h-8 w-8 text-green-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Completed</p>
                        <p class="text-2xl font-bold text-gray-900">{{ taskStats.completed }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tasks List -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Task List</h3>
            <div class="space-y-4">
                <div v-for="task in filteredTasks" :key="task.id"
                     class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <h4 class="font-medium text-gray-900 mr-3">{{ task.title }}</h4>
                            <span class="text-xs px-2 py-1 rounded-full"
                                  :class="getPriorityColor(task.priority)">
                                {{ task.priority }}
                            </span>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full"
                              :class="getStatusColor(task.status)">
                            {{ formatStatus(task.status) }}
                        </span>
                    </div>
                    
                    <p class="text-sm text-gray-600 mb-3">{{ task.description }}</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600 mb-4">
                        <div>
                            <strong>Assigned by:</strong> {{ task.assignedBy }}
                        </div>
                        <div>
                            <strong>Due date:</strong> {{ formatDate(task.dueDate) }}
                        </div>
                        <div>
                            <strong>Location:</strong> {{ task.location }}
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex space-x-2">
                            <button v-if="task.status === 'pending'" @click="startTask(task)"
                                    class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                Start Task
                            </button>
                            <button v-else-if="task.status === 'in_progress'" @click="completeTask(task)"
                                    class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                Mark Complete
                            </button>
                            <button class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700">
                                View Details
                            </button>
                        </div>
                        <div class="text-sm text-gray-500">
                            Created: {{ formatDate(task.createdAt) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import {
    ClipboardDocumentListIcon,
    ClockIcon,
    PlayIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
})

const navigation = computed(() => getNavigationForRole('staff'))
const filterStatus = ref('')

const tasks = ref([
    {
        id: 1,
        title: 'Clean Room 205',
        description: 'Deep clean room after guest checkout, replace linens and amenities',
        status: 'pending',
        priority: 'high',
        assignedBy: 'Sarah Manager',
        dueDate: new Date(2024, 5, 22),
        location: 'Room 205',
        createdAt: new Date(2024, 5, 21)
    },
    {
        id: 2,
        title: 'Restock Housekeeping Cart',
        description: 'Refill cleaning supplies and linens on housekeeping cart',
        status: 'in_progress',
        priority: 'medium',
        assignedBy: 'Mike Supervisor',
        dueDate: new Date(2024, 5, 22),
        location: 'Housekeeping Storage',
        createdAt: new Date(2024, 5, 21)
    },
    {
        id: 3,
        title: 'Inspect Room 301',
        description: 'Quality inspection of room after cleaning',
        status: 'completed',
        priority: 'low',
        assignedBy: 'Sarah Manager',
        dueDate: new Date(2024, 5, 21),
        location: 'Room 301',
        createdAt: new Date(2024, 5, 20)
    },
    {
        id: 4,
        title: 'Deliver Extra Towels',
        description: 'Guest in room 102 requested additional towels',
        status: 'pending',
        priority: 'urgent',
        assignedBy: 'Front Desk',
        dueDate: new Date(2024, 5, 22),
        location: 'Room 102',
        createdAt: new Date(2024, 5, 22)
    }
])

const taskStats = computed(() => {
    return {
        total: tasks.value.length,
        pending: tasks.value.filter(t => t.status === 'pending').length,
        inProgress: tasks.value.filter(t => t.status === 'in_progress').length,
        completed: tasks.value.filter(t => t.status === 'completed').length,
    }
})

const filteredTasks = computed(() => {
    if (!filterStatus.value) return tasks.value
    return tasks.value.filter(task => task.status === filterStatus.value)
})

const startTask = (task) => {
    task.status = 'in_progress'
    alert(`Started task: ${task.title}`)
}

const completeTask = (task) => {
    task.status = 'completed'
    alert(`Completed task: ${task.title}`)
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
        pending: 'bg-yellow-100 text-yellow-800',
        in_progress: 'bg-blue-100 text-blue-800',
        completed: 'bg-green-100 text-green-800',
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const formatStatus = (status) => {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (date) => {
    return date.toLocaleDateString()
}
</script>
