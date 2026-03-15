<template>
    <DashboardLayout title="Budget Details" :user="user">
        <!-- Budget Overview Card -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <!-- Header Section -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">{{ budget.name }}</h1>
                    <div class="flex items-center gap-4">
                        <p class="text-sm"
                           :style="{ color: themeColors.textSecondary }">Status:
                            <span class="px-2 py-1 text-xs rounded-full ml-1" :style="getStatusStyle(budget.status)">
                                {{ formatStatus(budget.status) }}
                            </span>
                        </p>
                        <p class="text-sm"
                           :style="{ color: themeColors.textSecondary }">
                            Created: {{ formatDate(budget.created_at) }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route(`${routePrefix}.budget.edit`, { budget: budget.id })"
                          v-if="canEdit(budget)"
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                          :style="{
                              backgroundColor: themeColors.warning,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = '#d97706'"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.warning">
                        Edit
                    </Link>
                    <Link :href="route(`${routePrefix}.budget.index`)"
                          class="px-4 py-2 rounded-md transition-colors font-medium"
                          :style="{
                              backgroundColor: themeColors.primary,
                              color: 'white'
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        Back
                    </Link>
                </div>
            </div>

            <!-- Budget Summary Section -->
            <div class="mb-8">
                <h3 class="text-lg font-medium mb-4"
                    :style="{ color: themeColors.textPrimary }">Budget Summary</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="rounded-lg p-4 border"
                         :style="{
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '100px' }">Category:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ budget.category?.name || 'N/A' }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '100px' }">Department:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ budget.department?.name || 'N/A' }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '100px' }">Start Date:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ formatDate(budget.start_date) }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '100px' }">End Date:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ formatDate(budget.end_date) }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '100px' }">Created By:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ budget.createdBy?.full_name || 'N/A' }}</span>
                            </div>
                            <div v-if="budget.approvedBy" class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '100px' }">Approved By:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ budget.approvedBy?.full_name || 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg p-4 border"
                         :style="{
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span :style="{ color: themeColors.textSecondary }">Budget Amount:</span>
                                <span class="font-medium"
                                      :style="{ color: themeColors.textPrimary }">{{ formatCurrency(budget.amount) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span :style="{ color: themeColors.textSecondary }">Spent Amount:</span>
                                <span class="font-medium"
                                      :style="{ color: budget.spent_amount > budget.amount ? themeColors.danger : themeColors.textPrimary }">{{ formatCurrency(budget.spent_amount) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span :style="{ color: themeColors.textSecondary }">Remaining:</span>
                                <span class="font-medium"
                                      :style="{ color: budget.remaining_amount >= 0 ? themeColors.success : themeColors.danger }">{{ formatCurrency(budget.remaining_amount) }}</span>
                            </div>
                            <div class="flex justify-between text-sm font-semibold pt-2 border-t"
                                 :style="{
                                     borderTopColor: themeColors.border,
                                     borderTopWidth: '1px'
                                 }">
                                <span :style="{ color: themeColors.textSecondary }">Utilization:</span>
                                <span :style="{ color: budget.utilization_percentage >= 100 ? themeColors.danger : (budget.utilization_percentage >= 80 ? themeColors.warning : themeColors.success) }">{{ budget.utilization_percentage }}%</span>
                            </div>
                        </div>
                        <!-- Progress Bar -->
                        <div class="mt-4">
                            <div class="w-full rounded-full h-3"
                                 :style="{ backgroundColor: themeColors.background }">
                                <div
                                    class="h-3 rounded-full transition-all duration-300"
                                    :class="{
                                        'bg-red-500': budget.utilization_percentage >= 100,
                                        'bg-yellow-500': budget.utilization_percentage >= 80 && budget.utilization_percentage < 100,
                                        'bg-green-500': budget.utilization_percentage < 80
                                    }"
                                    :style="{ width: Math.min(budget.utilization_percentage, 100) + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description Section -->
            <div v-if="budget.description" class="mb-8">
                <h3 class="text-lg font-medium mb-4"
                    :style="{ color: themeColors.textPrimary }">Description</h3>
                <div class="rounded-lg p-4 border"
                     :style="{
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }">
                    <p class="text-sm whitespace-pre-wrap"
                       :style="{ color: themeColors.textPrimary }">{{ budget.description }}</p>
                </div>
            </div>

            <!-- Notes Section -->
            <div v-if="budget.notes" class="mb-8">
                <h3 class="text-lg font-medium mb-4"
                    :style="{ color: themeColors.textPrimary }">Notes</h3>
                <div class="rounded-lg p-4 border"
                     :style="{
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }">
                    <p class="text-sm whitespace-pre-wrap"
                       :style="{ color: themeColors.textPrimary }">{{ budget.notes }}</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div v-if="canSubmitForApproval(budget) || canApprove(budget) || canReject(budget) || canArchive(budget)" class="mb-8">
                <h3 class="text-lg font-medium mb-4"
                    :style="{ color: themeColors.textPrimary }">Actions</h3>
                <div class="flex items-center gap-3">
                    <button v-if="canSubmitForApproval(budget)" @click="submitForApproval(budget)"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                            :style="{
                                backgroundColor: themeColors.success,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = '#16a34a'"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                        Submit for Approval
                    </button>
                    <button v-if="canApprove(budget)" @click="approveBudget(budget)"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                            :style="{
                                backgroundColor: themeColors.primary,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        Approve
                    </button>
                    <button v-if="canReject(budget)" @click="rejectBudget(budget)"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                            :style="{
                                backgroundColor: themeColors.danger,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = '#dc2626'"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.danger">
                        Reject
                    </button>
                    <button v-if="canArchive(budget)" @click="archiveBudget(budget)"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                            :style="{
                                backgroundColor: '#f59e0b',
                            }"
                            @mouseenter="$event.target.style.backgroundColor = '#d97706'"
                            @mouseleave="$event.target.style.backgroundColor = '#f59e0b'">
                        Archive
                    </button>
                </div>
            </div>
        </div>

        <!-- Expenses Section -->
        <div class="shadow rounded-lg p-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Associated Expenses</h3>
                <Link v-if="budget.status === 'approved'"
                      :href="route(`${routePrefix}.budget.expenses.create`) + '?budget_id=' + budget.id"
                      class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                      :style="{ backgroundColor: themeColors.primary }">
                    <PlusIcon class="h-4 w-4 mr-2" />
                    Add Expense
                </Link>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="p-6 text-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-2"
                   :style="{ color: themeColors.textSecondary }">Loading expenses...</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="expenses.length === 0" class="p-6 text-center">
                <div class="mb-4"
                     :style="{ color: themeColors.textTertiary }">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <p :style="{ color: themeColors.textSecondary }">No expenses found for this budget period.</p>
            </div>

            <!-- Expenses Table -->
            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y"
                       :style="{ borderColor: themeColors.border }">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y"
                           :style="{ borderColor: themeColors.border }">
                        <tr v-for="expense in expenses" :key="expense.id" class="hover:bg-opacity-50"
                            :style="{ backgroundColor: themeColors.background }">
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatDate(expense.expense_date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ expense.description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(expense.amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="[
                                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                    getExpenseStatusStyle(expense.status)
                                ]">
                                    {{ expense.status }}
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
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'
import { PlusIcon } from '@heroicons/vue/24/outline'

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
    budget: Object,
    expenses: Array,
    routePrefix: { type: String, default: 'admin' },
})

const loading = computed(() => false)

const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const formatStatus = (status) => {
    if (!status) return 'N/A'
    return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getStatusStyle = (status) => {
    const styles = {
        draft: {
            backgroundColor: `var(--kotel-text-tertiary)`,
            color: 'white'
        },
        pending_approval: {
            backgroundColor: `var(--kotel-warning)`,
            color: 'white'
        },
        approved: {
            backgroundColor: `var(--kotel-success)`,
            color: 'white'
        },
        rejected: {
            backgroundColor: `var(--kotel-danger)`,
            color: 'white'
        },
        expired: {
            backgroundColor: '#f59e0b',
            color: 'white'
        },
        archived: {
            backgroundColor: `var(--kotel-primary)`,
            color: 'white'
        }
    }
    return styles[status] || styles['draft']
}

const getExpenseStatusStyle = (status) => {
    const styles = {
        approved: {
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            color: 'var(--kotel-success)'
        },
        pending_approval: {
            backgroundColor: 'rgba(245, 158, 11, 0.1)',
            color: 'var(--kotel-warning)'
        },
        paid: {
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            color: 'var(--kotel-primary)'
        },
        rejected: {
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            color: 'var(--kotel-danger)'
        }
    }
    return styles[status] || styles['approved']
}

const canEdit = (budget) => {
    return budget.status !== 'approved'
}

const canSubmitForApproval = (budget) => {
    return budget.status === 'draft'
}

const canApprove = (budget) => {
    return budget.status === 'pending_approval'
}

const canReject = (budget) => {
    return budget.status === 'pending_approval'
}

const canArchive = (budget) => {
    return budget.status === 'approved'
}

const submitForApproval = (budget) => {
    if (confirm('Are you sure you want to submit this budget for approval?')) {
        router.post(route(`${props.routePrefix}.budget.submit-for-approval`, { budget: budget.id }), {}, {
            onSuccess: () => {
                // Budget will be updated via inertia
            }
        })
    }
}

const approveBudget = (budget) => {
    if (confirm('Are you sure you want to approve this budget?')) {
        router.post(route(`${props.routePrefix}.budget.approve`, { budget: budget.id }), {}, {
            onSuccess: () => {
                // Budget will be updated via inertia
            }
        })
    }
}

const rejectBudget = (budget) => {
    if (confirm('Are you sure you want to reject this budget?')) {
        router.post(route(`${props.routePrefix}.budget.reject`, { budget: budget.id }), {}, {
            onSuccess: () => {
                // Budget will be updated via inertia
            }
        })
    }
}

const archiveBudget = (budget) => {
    if (confirm('Are you sure you want to archive this budget?')) {
        router.post(route(`${props.routePrefix}.budget.archive`, { budget: budget.id }), {}, {
            onSuccess: () => {
                // Budget will be updated via inertia
            }
        })
    }
}
</script>
