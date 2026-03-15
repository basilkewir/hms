<template>
    <DashboardLayout title="Performance" :user="user">
        <div class="shadow rounded-lg p-6 mb-8" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Employee Performance</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Track and evaluate employee performance metrics</p>
                </div>
                <div class="flex gap-3">
                    <button @click="generateReport" class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center" :style="{ backgroundColor: themeColors.primary }">
                        <ChartBarIcon class="h-4 w-4 mr-2" />
                        Generate Report
                    </button>
                    <button @click="exportPerformance" class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center" style="background-color: #8b5cf6;">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                        Export
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" style="background-color: rgba(59, 130, 246, 0.1);">
                        <UserGroupIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Total Employees</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ performance.length }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" style="background-color: rgba(34, 197, 94, 0.1);">
                        <TrophyIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Avg Performance</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ avgPerformance }}%</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" style="background-color: rgba(139, 92, 246, 0.1);">
                        <StarIcon class="h-6 w-6" style="color: #8b5cf6;" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Top Performers</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ topPerformers }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" style="background-color: rgba(251, 146, 60, 0.1);">
                        <CheckCircleIcon class="h-6 w-6" style="color: #fb923c;" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Avg Attendance</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ avgAttendance }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-lg overflow-hidden shadow" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Performance Overview</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Position</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Performance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Attendance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Tasks</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Efficiency</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="employee in performance" :key="employee.id" class="transition-colors" :style="{ borderBottomStyle: 'solid', borderBottomWidth: '1px', borderColor: themeColors.border }">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ employee.name }}</div>
                                <div class="text-sm" :style="{ color: themeColors.textSecondary }">{{ employee.email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textSecondary }">
                                {{ employee.department || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textSecondary }">
                                {{ employee.position || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="h-2 rounded-full" :style="{ width: employee.performance_score + '%', backgroundColor: getPerformanceColor(employee.performance_score) }"></div>
                                    </div>
                                    <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ employee.performance_score }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="h-2 rounded-full" :style="{ width: employee.attendance_rate + '%', backgroundColor: getPerformanceColor(employee.attendance_rate) }"></div>
                                    </div>
                                    <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ employee.attendance_rate }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                {{ employee.tasks_completed }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getEfficiencyClass(employee.efficiency)">{{ employee.efficiency }}%</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click="viewDetails(employee)" class="mr-3 transition-colors" :style="{ color: themeColors.primary }">View</button>
                                <button @click="evaluate(employee)" class="transition-colors" :style="{ color: themeColors.success }">Evaluate</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { UserGroupIcon, TrophyIcon, StarIcon, CheckCircleIcon, ChartBarIcon, DocumentArrowDownIcon } from '@heroicons/vue/24/outline'

const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    primary: `var(--kotel-primary)`,
    success: `var(--kotel-success)`,
    danger: `var(--kotel-danger)`,
    warning: `var(--kotel-warning)`,
    hover: `rgba(255, 255, 255, 0.1)`
}))

loadTheme()

const props = defineProps({
    user: Object,
    performance: Array
})

const avgPerformance = computed(() => {
    if (props.performance.length === 0) return 0
    const sum = props.performance.reduce((acc, emp) => acc + emp.performance_score, 0)
    return Math.round(sum / props.performance.length)
})

const avgAttendance = computed(() => {
    if (props.performance.length === 0) return 0
    const sum = props.performance.reduce((acc, emp) => acc + emp.attendance_rate, 0)
    return Math.round(sum / props.performance.length)
})

const topPerformers = computed(() => {
    return props.performance.filter(emp => emp.performance_score >= 90).length
})

const getPerformanceColor = (score) => {
    if (score >= 90) return '#22c55e'
    if (score >= 75) return '#3b82f6'
    if (score >= 60) return '#f59e0b'
    return '#ef4444'
}

const getEfficiencyClass = (efficiency) => {
    if (efficiency >= 90) {
        return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800'
    } else if (efficiency >= 75) {
        return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800'
    } else if (efficiency >= 60) {
        return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800'
    }
    return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800'
}

const generateReport = () => {
    console.log('Generate report')
}

const exportPerformance = () => {
    console.log('Export performance')
}

const viewDetails = (employee) => {
    console.log('View details:', employee)
}

const evaluate = (employee) => {
    console.log('Evaluate:', employee)
}
</script>
