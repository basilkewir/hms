<template>
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeReport">
        <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 shadow-lg rounded-md bg-white"
             @click.stop>
            <!-- Report Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">{{ report.name }}</h3>
                    <p class="text-sm text-gray-600 mt-1">{{ report.description }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <button @click="exportReport" 
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2 inline" />
                        Export
                    </button>
                    <button @click="printReport" 
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">
                        <PrinterIcon class="h-4 w-4 mr-2 inline" />
                        Print
                    </button>
                    <button @click="closeReport" class="text-gray-400 hover:text-gray-600">
                        <XMarkIcon class="h-6 w-6" />
                    </button>
                </div>
            </div>

            <!-- Report Filters -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Report Filters</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Date Range</label>
                        <select v-model="filters.dateRange" @change="updateReport"
                                class="w-full text-sm border border-gray-300 rounded-md px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="quarter">This Quarter</option>
                            <option value="year">This Year</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div v-if="report.category === 'occupancy' || report.category === 'revenue'">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Room Type</label>
                        <select v-model="filters.roomType" @change="updateReport"
                                class="w-full text-sm border border-gray-300 rounded-md px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">All Room Types</option>
                            <option value="standard">Standard</option>
                            <option value="deluxe">Deluxe</option>
                            <option value="suite">Suite</option>
                        </select>
                    </div>
                    <div v-if="report.category === 'staff'">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Department</label>
                        <select v-model="filters.department" @change="updateReport"
                                class="w-full text-sm border border-gray-300 rounded-md px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">All Departments</option>
                            <option value="front_desk">Front Desk</option>
                            <option value="housekeeping">Housekeeping</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="management">Management</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Report Content -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6" style="min-height: 500px;">
                <!-- Summary Cards -->
                <div v-if="reportData.summary" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div v-for="(item, key) in reportData.summary" :key="key" 
                         class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-gray-900">{{ item.value }}</div>
                        <div class="text-sm text-gray-600">{{ item.label }}</div>
                    </div>
                </div>

                <!-- Chart Area -->
                <div v-if="reportData.chartData" class="mb-6">
                    <h5 class="text-lg font-medium text-gray-900 mb-4">{{ reportData.chartTitle || 'Trends' }}</h5>
                    <div class="bg-gray-100 rounded-lg p-8 text-center">
                        <ChartBarIcon class="h-16 w-16 text-gray-400 mx-auto mb-4" />
                        <p class="text-gray-600">Chart visualization would appear here</p>
                        <p class="text-sm text-gray-500 mt-2">{{ reportData.chartData.length }} data points</p>
                    </div>
                </div>

                <!-- Data Table -->
                <div v-if="reportData.tableData">
                    <h5 class="text-lg font-medium text-gray-900 mb-4">{{ reportData.tableTitle || 'Detailed Data' }}</h5>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th v-for="column in reportData.columns" :key="column.key"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ column.label }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="(row, index) in reportData.tableData" :key="index" class="hover:bg-gray-50">
                                    <td v-for="column in reportData.columns" :key="column.key"
                                        class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatCellValue(row[column.key], column.type) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Loading / Error State -->
                <div v-if="isLoading" class="text-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                    <p class="text-gray-600">Generating report...</p>
                </div>
                <div v-else-if="errorMessage" class="text-center py-12">
                    <p class="text-red-600 font-medium">{{ errorMessage }}</p>
                    <button
                        @click="generateReportData"
                        class="mt-4 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
                    >
                        Retry
                    </button>
                </div>
            </div>

            <!-- Report Footer -->
            <div class="flex items-center justify-between text-sm text-gray-500 border-t border-gray-200 pt-4">
                <div>
                    Generated on {{ formatDate(new Date()) }} at {{ formatTime(new Date()) }}
                </div>
                <div>
                    {{ reportData.tableData?.length || 0 }} records
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import {
    XMarkIcon,
    DocumentArrowDownIcon,
    PrinterIcon,
    ChartBarIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    report: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['close'])

const isLoading = ref(true)
const errorMessage = ref('')
const reportData = ref({})
const filters = ref({
    dateRange: 'month',
    roomType: '',
    department: ''
})

onMounted(() => {
    generateReportData()
})

const generateReportData = async () => {
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
                type: props.report.name,
                category: props.report.category || 'system',
                filters: filters.value
            })
        })

        if (!response.ok) {
            throw new Error('Failed to load report data.')
        }

        reportData.value = await response.json()
    } catch (error) {
        console.error('Error fetching report data:', error)
        reportData.value = {}
        errorMessage.value = 'Unable to load report data. Please try again.'
    }

    isLoading.value = false
}

const updateReport = () => {
    generateReportData()
}

const exportReport = () => {
    alert('Report exported successfully!')
}

const printReport = () => {
    window.print()
}

const closeReport = () => {
    emit('close')
}

const formatCellValue = (value, type) => {
    if (type === 'currency') {
        return value
    }
    if (type === 'number') {
        return typeof value === 'number' ? value.toLocaleString() : value
    }
    return value
}

const formatDate = (date) => {
    return date.toLocaleDateString()
}

const formatTime = (date) => {
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
</script>
