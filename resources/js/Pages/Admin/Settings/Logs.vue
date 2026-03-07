<template>
    <DashboardLayout title="System Logs" :user="user">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">System Logs</h1>
                    <p class="text-gray-600 mt-2">Monitor system activities and troubleshoot issues.</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="clearLogs" 
                            class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                        <TrashIcon class="h-4 w-4 mr-2 inline" />
                        Clear Logs
                    </button>
                    <button @click="downloadLogs" 
                            class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2 inline" />
                        Download
                    </button>
                </div>
            </div>
        </div>

        <div v-if="statusMessage" class="rounded-lg p-4 mb-6 border"
             :class="statusType === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700'">
            {{ statusMessage }}
        </div>

        <!-- Log Filters -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Log Level</label>
                    <select v-model="selectedLevel"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Levels</option>
                        <option value="error">Error</option>
                        <option value="warning">Warning</option>
                        <option value="info">Info</option>
                        <option value="debug">Debug</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select v-model="selectedCategory"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Categories</option>
                        <option value="authentication">Authentication</option>
                        <option value="database">Database</option>
                        <option value="api">API</option>
                        <option value="system">System</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                    <select v-model="selectedDateRange"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button @click="refreshLogs" 
                            class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        <ArrowPathIcon class="h-4 w-4 mr-2 inline" />
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Log Entries -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Log Entries</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Timestamp
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Level
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Message
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="log in filteredLogs" :key="log.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatDateTime(log.timestamp) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getLevelColor(log.level)">
                                    {{ formatLevel(log.level) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatCategory(log.category) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="max-w-xs truncate">{{ log.message }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ log.user || 'System' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click="viewLogDetails(log)" class="text-blue-600 hover:text-blue-900">
                                    View Details
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {
    TrashIcon,
    DocumentArrowDownIcon,
    ArrowPathIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    logs: Array,
})

const statusMessage = ref('')
const statusType = ref('success')

const selectedLevel = ref('')
const selectedCategory = ref('')
const selectedDateRange = ref('today')

const logs = computed(() => props.logs || [])

const filteredLogs = computed(() => {
    return logs.value.filter(log => {
        const matchesLevel = !selectedLevel.value || log.level === selectedLevel.value
        const matchesCategory = !selectedCategory.value || log.category === selectedCategory.value
        return matchesLevel && matchesCategory
    })
})

const getLevelColor = (level) => {
    const colors = {
        error: 'bg-red-100 text-red-800',
        warning: 'bg-yellow-100 text-yellow-800',
        info: 'bg-blue-100 text-blue-800',
        debug: 'bg-gray-100 text-gray-800'
    }
    return colors[level] || 'bg-gray-100 text-gray-800'
}

const formatLevel = (level) => {
    return level.charAt(0).toUpperCase() + level.slice(1)
}

const formatCategory = (category) => {
    return category.charAt(0).toUpperCase() + category.slice(1)
}

const formatDateTime = (date) => {
    return new Date(date).toLocaleString()
}

const clearLogs = () => {
    if (confirm('Are you sure you want to clear all logs? This action cannot be undone.')) {
        router.post(route('admin.settings.logs.clear'), {}, {
            onSuccess: () => {
                statusType.value = 'success'
                statusMessage.value = 'Logs cleared successfully.'
            },
            onError: () => {
                statusType.value = 'error'
                statusMessage.value = 'Failed to clear logs.'
            }
        })
    }
}

const downloadLogs = () => {
    window.location.href = route('admin.settings.logs.download')
}

const refreshLogs = () => {
    router.reload({ only: ['logs'], preserveState: true })
}

const viewLogDetails = (log) => {
    statusType.value = 'success'
    statusMessage.value = `Log ${log.id}: ${log.message}`
}
</script>
