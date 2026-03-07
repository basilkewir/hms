<template>
    <DashboardLayout title="My Timesheet" :user="user">
        <!-- Timesheet Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">My Timesheet</h1>
                    <p class="text-gray-600 mt-2">View your work hours and time entries.</p>
                </div>
                <div class="flex space-x-3">
                    <select v-model="selectedWeek" @change="applyWeek"
                            class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="current">Current Week</option>
                        <option value="last">Last Week</option>
                        <option value="two_weeks">2 Weeks Ago</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Weekly Summary -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Weekly Summary</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="text-center">
                <div class="text-3xl font-bold text-blue-600">{{ weeklyStats.totalHours }}</div>
                    <p class="text-sm text-gray-600 mt-1">Total Hours</p>
                </div>
                <div class="text-center">
                <div class="text-3xl font-bold text-green-600">{{ weeklyStats.regularHours }}</div>
                    <p class="text-sm text-gray-600 mt-1">Regular Hours</p>
                </div>
                <div class="text-center">
                <div class="text-3xl font-bold text-yellow-600">{{ weeklyStats.overtimeHours }}</div>
                    <p class="text-sm text-gray-600 mt-1">Overtime Hours</p>
                </div>
                <div class="text-center">
                <div class="text-3xl font-bold text-purple-600">{{ weeklyStats.breakHours }}</div>
                    <p class="text-sm text-gray-600 mt-1">Break Hours</p>
                </div>
            </div>
        </div>

        <!-- Daily Breakdown -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Daily Breakdown</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
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
                        <tr v-for="entry in timeEntries" :key="entry.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ formatDate(entry.date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ entry.clockIn }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ entry.clockOut || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ entry.breakTime }}
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
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

const props = defineProps({
    user: Object,
    selectedWeek: String,
    weeklyStats: Object,
    timeEntries: Array,
})

const selectedWeek = ref(props.selectedWeek || 'current')

const applyWeek = () => {
    router.get(route('staff.time-tracking.timesheet'), { week: selectedWeek.value }, {
        preserveScroll: true,
        preserveState: true
    })
}

const getStatusColor = (status) => {
    const colors = {
        approved: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        active: 'bg-blue-100 text-blue-800',
        rejected: 'bg-red-100 text-red-800',
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString([], { 
        weekday: 'short', 
        month: 'short', 
        day: 'numeric' 
    })
}
</script>
