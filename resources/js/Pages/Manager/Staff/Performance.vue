<template>
    <DashboardLayout title="Staff Performance" :user="user">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Staff Performance</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Monitor and analyze employee performance metrics.</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="exportPerformance"
                            class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity"
                            :style="{ backgroundColor: themeColors.warning, color: '#000' }">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2 inline" />
                        Export Report
                    </button>
                </div>
            </div>
            <div v-if="statusMessage" class="mt-4 rounded-md px-4 py-3 text-sm border"
                 :style="statusType === 'success' ? { backgroundColor: themeColors.success, color: '#000', borderColor: themeColors.border } : { backgroundColor: themeColors.danger, color: '#000', borderColor: themeColors.border }">
                {{ statusMessage }}
            </div>
        </div>

        <!-- Performance Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <ChartBarIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.primary }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Average Performance Score</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ performanceStats.averageScore }}/10</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <UsersIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.success }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">High Performers</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ performanceStats.highPerformers }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <ExclamationTriangleIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.warning }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Needs Improvement</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ performanceStats.needsImprovement }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <StarIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.secondary }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Employee Satisfaction</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ performanceStats.employeeSatisfaction }}/5</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Performance Chart -->
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Performance Distribution</h3>
                <div class="h-64 flex items-end justify-between space-x-2">
                    <div class="flex-1 rounded-t flex items-end justify-center py-2 border" :style="{ backgroundColor: themeColors.primary, borderColor: themeColors.border }">
                        <span class="text-xs" :style="{ color: '#000' }">Excellent</span>
                    </div>
                    <div class="flex-1 rounded-t flex items-end justify-center py-4 border" :style="{ backgroundColor: themeColors.success, borderColor: themeColors.border }">
                        <span class="text-xs" :style="{ color: '#000' }">Good</span>
                    </div>
                    <div class="flex-1 rounded-t flex items-end justify-center py-6 border" :style="{ backgroundColor: themeColors.warning, borderColor: themeColors.border }">
                        <span class="text-xs" :style="{ color: '#000' }">Average</span>
                    </div>
                    <div class="flex-1 rounded-t flex items-end justify-center py-8 border" :style="{ backgroundColor: themeColors.danger, borderColor: themeColors.border }">
                        <span class="text-xs" :style="{ color: '#000' }">Poor</span>
                    </div>
                </div>
            </div>

            <!-- Recent Reviews -->
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Recent Performance Reviews</h3>
                <div class="space-y-4">
                    <div v-for="review in recentReviews" :key="review.id" class="border-b pb-4" :style="{ borderColor: themeColors.border }">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium" :style="{ color: themeColors.textPrimary }">{{ review.employee_name }}</p>
                                <p class="text-sm" :style="{ color: themeColors.textSecondary }">{{ review.position }}</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-lg font-bold" :style="{ color: themeColors.textPrimary }">{{ review.score }}</span>
                                <span class="text-sm" :style="{ color: themeColors.textSecondary }">/10</span>
                            </div>
                        </div>
                        <p class="text-sm mt-2" :style="{ color: themeColors.textSecondary }">{{ review.feedback }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Performance List -->
        <div class="shadow rounded-lg overflow-hidden border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Employee Performance</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Employee
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Department
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Performance Score
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Attendance Rate
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Last Review
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="employee in employees" :key="employee.id" class="transition-colors" :style="hoveredRow === employee.id ? { backgroundColor: themeColors.hover } : {}" @mouseenter="hoveredRow = employee.id" @mouseleave="hoveredRow = null">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center mr-4 border"
                                         :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                                        <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                            {{ getInitials(employee.name) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ employee.name }}</div>
                                        <div class="text-sm" :style="{ color: themeColors.textSecondary }">{{ employee.employee_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ formatDepartment(employee.department) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <div class="flex-1 rounded-full h-2 border" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                                        <div class="h-2 rounded-full"
                                             :style="{ width: `${employee.performance_score * 10}%`, backgroundColor: themeColors.primary }"></div>
                                    </div>
                                    <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ employee.performance_score }}/10</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ employee.attendance_rate }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ employee.last_review }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="getPerformancePillStyle(employee.performance_score)">
                                    {{ getPerformanceLevel(employee.performance_score) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button @click="viewPerformance(employee)" class="hover:opacity-80 transition-opacity" :style="{ color: themeColors.primary }">View</button>
                                    <button @click="scheduleReview(employee)" class="hover:opacity-80 transition-opacity" :style="{ color: themeColors.success }">Review</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme'
import {
    DocumentArrowDownIcon,
    ChartBarIcon,
    UsersIcon,
    ExclamationTriangleIcon,
    StarIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    performanceStats: Object,
    recentReviews: Array,
    employees: Array,
})

const statusMessage = ref('')
const statusType = ref('success')

const hoveredRow = ref(null)

const { currentTheme } = useTheme()

const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    primary: `var(--kotel-primary)`,
    secondary: `var(--kotel-secondary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)'
}))

const getInitials = (name) => {
    if (!name) return 'N/A'
    return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const formatDepartment = (department) => {
    return department.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getPerformancePillStyle = (score) => {
    if (score >= 8) return { backgroundColor: themeColors.value.success, color: '#000' }
    if (score >= 6) return { backgroundColor: themeColors.value.warning, color: '#000' }
    return { backgroundColor: themeColors.value.danger, color: '#000' }
}

const getPerformanceLevel = (score) => {
    if (score >= 9) return 'Excellent'
    if (score >= 8) return 'Very Good'
    if (score >= 7) return 'Good'
    if (score >= 6) return 'Satisfactory'
    return 'Needs Improvement'
}

const exportPerformance = () => {
    window.location.href = route('manager.staff.performance.export')
}

const viewPerformance = (employee) => {
    router.visit(route('manager.staff.show', employee.id))
}

const scheduleReview = (employee) => {
    statusMessage.value = ''
    router.post(route('manager.staff.performance.schedule-review', employee.id), {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            statusType.value = 'success'
            statusMessage.value = `Review scheduled for ${employee.name}.`
        },
        onError: () => {
            statusType.value = 'error'
            statusMessage.value = 'Failed to schedule review. Please try again.'
        }
    })
}
</script>
