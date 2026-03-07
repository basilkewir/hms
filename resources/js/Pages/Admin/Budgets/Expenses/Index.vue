<template>
    <DashboardLayout :title="isFiltered ? 'Budget Expenses' : 'Budget Expenses Management'">
        <!-- Page Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Budget Expenses</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Track and manage budget expenses with approval workflow.</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route(`${routePrefix}.budget.expenses.pending-approvals`)"
                          class="px-4 py-2 rounded-md transition-colors flex items-center space-x-2"
                          :style="{
                              backgroundColor: 'rgba(245, 158, 11, 0.1)',
                              color: themeColors.warning
                          }"
                          @mouseenter="$event.target.style.backgroundColor = 'rgba(245, 158, 11, 0.2)'"
                          @mouseleave="$event.target.style.backgroundColor = 'rgba(245, 158, 11, 0.1)'">
                        <ClockIcon class="h-4 w-4" />
                        <span>Pending Approvals</span>
                    </Link>
                    <Link :href="route(`${routePrefix}.budget.expenses.create`)"
                          class="px-4 py-2 rounded-md transition-colors flex items-center space-x-2"
                          :style="{
                              backgroundColor: themeColors.primary,
                              color: '#ffffff'
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4" />
                        <span>Add Expense</span>
                    </Link>
                </div>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Search</label>
                    <input type="text"
                           v-model="search"
                           :style="{
                               backgroundColor: themeColors.background,
                               borderColor: themeColors.border,
                               color: themeColors.textPrimary
                           }"
                           class="w-full rounded-md px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Description or vendor..." />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Budget</label>
                    <select v-model="selectedBudgetId"
                            :style="{
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary
                            }"
                            class="w-full rounded-md px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Budgets</option>
                        <option v-for="budget in budgets" :key="budget.id" :value="budget.id">
                            {{ budget.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Status</label>
                    <select v-model="selectedStatus"
                            :style="{
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary
                            }"
                            class="w-full rounded-md px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button @click="clearFilters"
                            class="px-4 py-2 rounded-md transition-colors"
                            :style="{
                                backgroundColor: themeColors.secondary,
                                color: themeColors.textPrimary
                            }">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Expenses</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ expenses.total }}</p>
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
                           :style="{ color: themeColors.textSecondary }">Pending</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.warning }">{{ getStatusCount('pending') }}</p>
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
                           :style="{ color: themeColors.textSecondary }">Approved</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.success }">{{ getStatusCount('approved') }}</p>
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
                           :style="{ color: themeColors.textSecondary }">Total Amount</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ formatCurrency(totalAmount) }}</p>
                    </div>
                    <div class="p-3 rounded-full"
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                        <CurrencyDollarIcon class="h-6 w-6"
                                           :style="{ color: themeColors.success }" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Expenses Table -->
        <div class="shadow rounded-lg overflow-hidden"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead
                        :style="{
                            backgroundColor: themeColors.background,
                            borderBottom: `2px solid ${themeColors.border}`
                        }">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Budget</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Vendor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Submitted By</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" :style="{ borderColor: themeColors.border }">
                        <tr v-for="expense in expenses.data" :key="expense.id"
                            class="hover:bg-opacity-50 transition-colors"
                            :style="{
                                backgroundColor: expense.status === 'pending' ? 'rgba(245, 158, 11, 0.05)' : 'transparent'
                            }">
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatDate(expense.expense_date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                {{ expense.budget?.name || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ expense.description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                {{ expense.vendor || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(expense.amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full"
                                      :style="getStatusStyle(expense.status)">
                                    {{ formatStatus(expense.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                {{ expense.creator?.name || 'System' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <Link :href="route(`${routePrefix}.budget.expenses.show`, expense.id)"
                                          class="p-2 rounded-md transition-colors hover:bg-opacity-50"
                                          :style="{ color: themeColors.primary }"
                                          title="View">
                                        <EyeIcon class="h-4 w-4" />
                                    </Link>
                                    <Link v-if="expense.status === 'pending'"
                                          :href="route(`${routePrefix}.budget.expenses.edit`, expense.id)"
                                          class="p-2 rounded-md transition-colors hover:bg-opacity-50"
                                          :style="{ color: themeColors.warning }"
                                          title="Edit">
                                        <PencilIcon class="h-4 w-4" />
                                    </Link>
                                    <button v-if="expense.status === 'pending'"
                                            @click="approveExpense(expense.id)"
                                            class="p-2 rounded-md transition-colors hover:bg-opacity-50"
                                            :style="{ color: themeColors.success }"
                                            title="Approve">
                                        <CheckIcon class="h-4 w-4" />
                                    </button>
                                    <button v-if="expense.status === 'pending'"
                                            @click="rejectExpense(expense.id)"
                                            class="p-2 rounded-md transition-colors hover:bg-opacity-50"
                                            :style="{ color: themeColors.danger }"
                                            title="Reject">
                                        <XMarkIcon class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="expenses.data.length === 0">
                            <td colspan="8" class="px-6 py-12 text-center"
                                :style="{ color: themeColors.textSecondary }">
                                No expenses found. Create your first expense to get started.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="expenses.data.length > 0" class="px-6 py-4 border-t"
                 :style="{ borderColor: themeColors.border }">
                <div class="flex items-center justify-between">
                    <div class="text-sm"
                         :style="{ color: themeColors.textSecondary }">
                        Showing {{ expenses.from }} to {{ expenses.to }} of {{ expenses.total }} results
                    </div>
                    <div class="flex space-x-2">
                        <Link v-for="link in expenses.links" :key="link.label"
                              :href="link.url"
                              class="px-3 py-2 rounded-md text-sm transition-colors"
                              :class="{
                                  'bg-blue-500 text-white': link.active,
                                  'bg-gray-100 text-gray-700': !link.active
                              }"
                              :style="{
                                  backgroundColor: link.active ? themeColors.primary : themeColors.background,
                                  color: link.active ? '#ffffff' : themeColors.textPrimary
                              }"
                              v-html="link.label">
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency, formatDate } from '@/Utils/currency'
import {
    DocumentTextIcon,
    PlusIcon,
    ClockIcon,
    CheckCircleIcon,
    CurrencyDollarIcon,
    EyeIcon,
    PencilIcon,
    CheckIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    expenses: Object,
    budgets: Array,
    filters: Object,
    routePrefix: { type: String, default: 'admin' },
})

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

loadTheme()

// Filters
const search = ref(props.filters.search || '')
const selectedBudgetId = ref(props.filters.budget_id || '')
const selectedStatus = ref(props.filters.status || '')

// Computed
const isFiltered = computed(() => {
    return search.value || selectedBudgetId.value || selectedStatus.value
})

const totalAmount = computed(() => {
    return props.expenses.data.reduce((sum, expense) => sum + expense.amount, 0)
})

// Methods
const getStatusCount = (status) => {
    return props.expenses.data.filter(e => e.status === status).length
}

const getStatusStyle = (status) => {
    const styles = {
        'pending': {
            backgroundColor: 'rgba(245, 158, 11, 0.1)',
            color: '#F59E0B'
        },
        'approved': {
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            color: '#22C55E'
        },
        'rejected': {
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            color: '#EF4444'
        },
        'paid': {
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            color: '#3B82F6'
        }
    }
    return styles[status] || styles['pending']
}

const formatStatus = (status) => {
    const labels = {
        'pending': 'Pending',
        'approved': 'Approved',
        'rejected': 'Rejected',
        'paid': 'Paid'
    }
    return labels[status] || status
}

const clearFilters = () => {
    search.value = ''
    selectedBudgetId.value = ''
    selectedStatus.value = ''
}

const approveExpense = (id) => {
    if (confirm('Are you sure you want to approve this expense?')) {
        router.post(route(`${props.routePrefix}.budget.expenses.approve`, { budgetExpense: id }))
    }
}

const rejectExpense = (id) => {
    if (confirm('Are you sure you want to reject this expense?')) {
        router.post(route(`${props.routePrefix}.budget.expenses.reject`, { budgetExpense: id }))
    }
}

// Watch for filter changes
watch([search, selectedBudgetId, selectedStatus], () => {
    router.get(route(`${props.routePrefix}.budget.expenses.index`), {
        search: search.value,
        budget_id: selectedBudgetId.value,
        status: selectedStatus.value
    }, {
        preserveState: true,
        replace: true
    })
})
</script>
