<template>
    <DashboardLayout title="Budget Dashboard">
        <!-- Page Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Budget Dashboard</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Overview of budget performance and utilization across all departments.</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route(`${routePrefix}.budget.index`)"
                          class="px-4 py-2 rounded-md transition-colors flex items-center space-x-2"
                          :style="{
                              backgroundColor: themeColors.secondary,
                              color: themeColors.textPrimary
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        <DocumentTextIcon class="h-4 w-4" />
                        <span>View All Budgets</span>
                    </Link>
                    <Link :href="route(`${routePrefix}.budget.create`)"
                          class="px-4 py-2 rounded-md transition-colors flex items-center space-x-2"
                          :style="{
                              backgroundColor: themeColors.primary,
                              color: '#ffffff'
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4" />
                        <span>Create Budget</span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Budgets</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ summary.total_budgets }}</p>
                    </div>
                    <div class="p-3 rounded-full"
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                        <DocumentTextIcon class="h-6 w-6"
                                         :style="{ color: '#3B82F6' }" />
                    </div>
                </div>
            </div>

            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Active Budgets</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.success }">{{ summary.active_budgets }}</p>
                    </div>
                    <div class="p-3 rounded-full"
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                        <CheckCircleIcon class="h-6 w-6"
                                        :style="{ color: themeColors.success }" />
                    </div>
                </div>
            </div>

            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Pending Approval</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.warning }">{{ summary.pending_approval }}</p>
                    </div>
                    <div class="p-3 rounded-full"
                         :style="{ backgroundColor: 'rgba(245, 158, 11, 0.1)' }">
                        <ClockIcon class="h-6 w-6"
                                   :style="{ color: themeColors.warning }" />
                    </div>
                </div>
            </div>

            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Over Budget</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.danger }">{{ summary.over_budget }}</p>
                    </div>
                    <div class="p-3 rounded-full"
                         :style="{ backgroundColor: 'rgba(239, 68, 68, 0.1)' }">
                        <ExclamationTriangleIcon class="h-6 w-6"
                                               :style="{ color: themeColors.danger }" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Budget Utilization by Department -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Department Utilization</h3>
                <div class="space-y-4">
                    <div v-for="dept in departmentUtilization" :key="dept.name"
                         class="border-b pb-3"
                         :style="{ borderColor: themeColors.border }">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textPrimary }">{{ dept.name }}</span>
                            <span class="text-sm"
                                  :style="{ color: themeColors.textSecondary }">{{ dept.utilization }}%</span>
                        </div>
                        <div class="w-full rounded-full h-2"
                             :style="{ backgroundColor: themeColors.background }">
                            <div
                                class="h-2 rounded-full transition-all duration-300"
                                :class="{
                                    'bg-red-500': dept.utilization >= 100,
                                    'bg-yellow-500': dept.utilization >= 80 && dept.utilization < 100,
                                    'bg-green-500': dept.utilization < 80
                                }"
                                :style="{ width: Math.min(dept.utilization, 100) + '%' }"
                            ></div>
                        </div>
                        <div class="flex justify-between text-xs mt-1">
                            <span :style="{ color: themeColors.textSecondary }">Budget: {{ formatCurrency(dept.total_budget) }}</span>
                            <span :style="{ color: themeColors.textSecondary }">Spent: {{ formatCurrency(dept.total_spent) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- All Budgets -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">All Budgets</h3>
                <div class="space-y-4">
                    <div v-for="budget in budgets" :key="budget.id"
                         class="border rounded-lg p-4"
                         :style="{ borderColor: themeColors.border }">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h4 class="font-medium"
                                    :style="{ color: themeColors.textPrimary }">{{ budget.name }}</h4>
                                <p class="text-sm"
                                   :style="{ color: themeColors.textSecondary }">{{ budget.category?.name || 'Uncategorized' }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full"
                                  :style="getStatusStyle(budget.status)">{{ budget.status_label || budget.status }}</span>
                        </div>
                        <div class="flex justify-between text-sm mb-2">
                            <span :style="{ color: themeColors.textSecondary }">
                                {{ formatDate(budget.start_date) }} - {{ formatDate(budget.end_date) }}
                            </span>
                            <span :style="{ color: themeColors.textSecondary }">
                                {{ budget.utilization_percentage }}% used
                            </span>
                        </div>
                        <div class="w-full rounded-full h-2 mb-2"
                             :style="{ backgroundColor: themeColors.background }">
                            <div
                                class="h-2 rounded-full transition-all duration-300"
                                :class="{
                                    'bg-red-500': budget.is_over_budget,
                                    'bg-yellow-500': budget.is_near_budget,
                                    'bg-green-500': budget.is_on_track
                                }"
                                :style="{ width: Math.min(budget.utilization_percentage, 100) + '%' }"
                            ></div>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span :style="{ color: themeColors.textSecondary }">
                                Budget: {{ formatCurrency(budget.amount) }}
                            </span>
                            <span class="font-medium"
                                  :style="{ color: themeColors.textPrimary }">
                                Spent: {{ formatCurrency(budget.spent_amount) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="shadow rounded-lg p-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <h3 class="text-lg font-semibold mb-4"
                :style="{ color: themeColors.textPrimary }">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Link :href="route(`${routePrefix}.budget.reports`)"
                      class="flex items-center space-x-3 p-4 border rounded-lg transition-colors"
                      :style="{
                          borderColor: themeColors.border,
                          backgroundColor: themeColors.background
                      }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                    <div class="p-2 rounded-lg"
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                        <ChartBarIcon class="h-5 w-5"
                                      :style="{ color: '#3B82F6' }" />
                    </div>
                    <div>
                        <h4 class="font-medium"
                            :style="{ color: themeColors.textPrimary }">View Reports</h4>
                        <p class="text-sm"
                           :style="{ color: themeColors.textSecondary }">Detailed budget analysis and trends</p>
                    </div>
                </Link>

                <Link :href="route(`${routePrefix}.budget.index`)"
                      class="flex items-center space-x-3 p-4 border rounded-lg transition-colors"
                      :style="{
                          borderColor: themeColors.border,
                          backgroundColor: themeColors.background
                      }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                    <div class="p-2 rounded-lg"
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                        <DocumentTextIcon class="h-5 w-5"
                                         :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <h4 class="font-medium"
                            :style="{ color: themeColors.textPrimary }">Manage Budgets</h4>
                        <p class="text-sm"
                           :style="{ color: themeColors.textSecondary }">View and manage all budgets</p>
                    </div>
                </Link>

                <Link :href="route(`${routePrefix}.budget.create`)"
                      class="flex items-center space-x-3 p-4 border rounded-lg transition-colors"
                      :style="{
                          borderColor: themeColors.border,
                          backgroundColor: themeColors.background
                      }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                    <div class="p-2 rounded-lg"
                         :style="{ backgroundColor: 'rgba(168, 85, 247, 0.1)' }">
                        <PlusIcon class="h-5 w-5"
                                  :style="{ color: '#A855F7' }" />
                    </div>
                    <div>
                        <h4 class="font-medium"
                            :style="{ color: themeColors.textPrimary }">Create Budget</h4>
                        <p class="text-sm"
                           :style="{ color: themeColors.textSecondary }">Create a new budget</p>
                    </div>
                </Link>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency, formatDate } from '@/Utils/currency'
import {
    DocumentTextIcon,
    PlusIcon,
    CheckCircleIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    ChartBarIcon
} from '@heroicons/vue/24/outline'

// Initialize theme
const { loadTheme } = useTheme()
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
    hover: `rgba(255, 255, 255, 0.1)`
}))

// Load theme on mount
loadTheme()

const props = defineProps({
    user: Object,
    summary: Object,
    budgets: Array,
    departmentUtilization: Array,
    routePrefix: { type: String, default: 'admin' },
})

// Status style helper
function getStatusStyle(status) {
    const styles = {
        'approved': {
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            color: themeColors.value.success
        },
        'pending_approval': {
            backgroundColor: 'rgba(245, 158, 11, 0.1)',
            color: themeColors.value.warning
        },
        'draft': {
            backgroundColor: 'rgba(156, 163, 175, 0.1)',
            color: themeColors.value.textSecondary
        },
        'rejected': {
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            color: themeColors.value.danger
        },
        'archived': {
            backgroundColor: 'rgba(107, 114, 128, 0.1)',
            color: '#6B7280'
        }
    }
    return styles[status] || styles['draft']
}
</script>
