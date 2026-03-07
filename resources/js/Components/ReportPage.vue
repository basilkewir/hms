<template>
    <div>
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ title }}</h1>
                    <p v-if="description" class="text-gray-600 mt-2">{{ description }}</p>
                </div>
                <div class="flex space-x-3">
                    <button
                        @click="exportReport"
                        class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700"
                    >
                        Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                    <select
                        v-model="filters.dateRange"
                        @change="loadReport"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="quarter">This Quarter</option>
                        <option value="year">This Year</option>
                    </select>
                </div>
                <div v-if="showRoomTypeFilter">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Room Type</label>
                    <select
                        v-model="filters.roomType"
                        @change="loadReport"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Room Types</option>
                        <option value="standard">Standard</option>
                        <option value="deluxe">Deluxe</option>
                        <option value="suite">Suite</option>
                    </select>
                </div>
                <div v-if="showDepartmentFilter">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                    <select
                        v-model="filters.department"
                        @change="loadReport"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Departments</option>
                        <option value="front_desk">Front Desk</option>
                        <option value="housekeeping">Housekeeping</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="management">Management</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Loading / Error -->
        <div v-if="isLoading" class="bg-white shadow rounded-lg p-8 text-center">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <p class="text-gray-600">Loading report data...</p>
        </div>
        <div v-else-if="errorMessage" class="bg-white shadow rounded-lg p-8 text-center">
            <p class="text-red-600 font-medium">{{ errorMessage }}</p>
            <button
                @click="loadReport"
                class="mt-4 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
            >
                Retry
            </button>
        </div>

        <!-- Summary -->
        <div v-else-if="reportData?.summary" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div v-for="(item, key) in reportData.summary" :key="key" class="bg-white rounded-lg shadow p-6">
                <div class="text-2xl font-bold text-gray-900">{{ item.value }}</div>
                <div class="text-sm text-gray-500 mt-1">{{ item.label }}</div>
            </div>
        </div>

        <!-- Chart Summary -->
        <div v-if="reportData?.chartData" class="bg-white shadow rounded-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ reportData.chartTitle || 'Trends' }}</h3>
            <div class="h-40 bg-gray-100 rounded-lg flex items-center justify-center">
                <p class="text-gray-500">
                    {{ reportData.chartData.length }} data points loaded
                </p>
            </div>
        </div>

        <!-- Table -->
        <div v-if="reportData?.tableData" class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">{{ reportData.tableTitle || 'Detailed Data' }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th v-for="column in reportData.columns" :key="column.key"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ column.label }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(row, index) in reportData.tableData" :key="index" class="hover:bg-gray-50">
                            <td v-for="column in reportData.columns" :key="column.key"
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatCellValue(row[column.key], column.type) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="!isLoading && !errorMessage && !reportData" class="bg-white shadow rounded-lg p-8 text-center">
            <p class="text-gray-600">No report data available.</p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    description: {
        type: String,
        default: ''
    },
    category: {
        type: String,
        required: true
    }
})

const isLoading = ref(false)
const errorMessage = ref('')
const reportData = ref(null)
const filters = ref({
    dateRange: 'month',
    roomType: '',
    department: ''
})

const showRoomTypeFilter = computed(() => ['occupancy', 'revenue', 'financial'].includes(props.category))
const showDepartmentFilter = computed(() => props.category === 'staff')

const loadReport = async () => {
    isLoading.value = true
    errorMessage.value = ''

    try {
        const response = await fetch('/api/reports/data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                type: props.title,
                category: props.category,
                filters: filters.value
            })
        })

        if (!response.ok) {
            throw new Error('Failed to load report data.')
        }

        reportData.value = await response.json()
    } catch (error) {
        console.error('Error loading report data:', error)
        errorMessage.value = 'Unable to load report data. Please try again.'
        reportData.value = null
    } finally {
        isLoading.value = false
    }
}

const exportReport = () => {
    alert('Export will be added after CSV endpoint is available.')
}

const formatCellValue = (value, type) => {
    if (value === null || value === undefined) return '-'
    if (type === 'currency') return value
    return value
}

onMounted(() => {
    loadReport()
})
</script>
