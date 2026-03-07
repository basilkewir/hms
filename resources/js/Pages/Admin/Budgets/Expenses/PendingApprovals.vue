<template>
    <DashboardLayout title="Pending Expense Approvals">
        <!-- Page Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Pending Approvals</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Review and approve pending expense requests.</p>
                </div>
                <Link :href="route(`${routePrefix}.budget.expenses.index`)"
                      class="px-4 py-2 rounded-md transition-colors flex items-center space-x-2"
                      :style="{
                          backgroundColor: themeColors.secondary,
                          color: themeColors.textPrimary
                      }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                    <ArrowLeftIcon class="h-4 w-4" />
                    <span>Back to Expenses</span>
                </Link>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="shadow rounded-lg p-6"
                     :style="{
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border
                     }">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium mb-1"
                               :style="{ color: themeColors.textSecondary }">Pending Requests</p>
                            <p class="text-3xl font-bold"
                               :style="{ color: themeColors.warning }">{{ pendingExpenses.total ?? pendingExpenses.data?.length ?? 0 }}</p>
                        </div>
                        <div class="p-3 rounded-full"
                             :style="{ backgroundColor: 'rgba(245, 158, 11, 0.1)' }">
                            <ClockIcon class="h-8 w-8"
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
                               :style="{ color: themeColors.textSecondary }">Total Amount</p>
                            <p class="text-3xl font-bold"
                               :style="{ color: themeColors.textPrimary }">{{ formatCurrency(totalPendingAmount) }}</p>
                        </div>
                        <div class="p-3 rounded-full"
                             :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                            <CurrencyDollarIcon class="h-8 w-8"
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
                               :style="{ color: themeColors.textSecondary }">Action Required</p>
                            <p class="text-sm"
                               :style="{ color: themeColors.textPrimary }">
                                {{ pendingExpenses.data?.length > 0 ? 'Review all pending expenses' : 'All caught up!' }}
                            </p>
                        </div>
                        <div class="p-3 rounded-full"
                             :style="{ backgroundColor: pendingExpenses.length > 0 ? 'rgba(34, 197, 94, 0.1)' : 'rgba(34, 197, 94, 0.1)' }">
                            <CheckCircleIcon class="h-8 w-8"
                                            :style="{ color: themeColors.success }" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Expenses List -->
        <div class="space-y-4">
            <div v-for="budget in pendingExpenses.data" :key="budget.id"
                 class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <!-- Budget Info -->
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-lg font-semibold"
                                :style="{ color: themeColors.textPrimary }">
                                {{ budget.name }}
                            </h3>
                            <span class="px-2 py-1 text-xs rounded-full"
                                  :style="{
                                      backgroundColor: 'rgba(245, 158, 11, 0.1)',
                                      color: themeColors.warning
                                  }">
                                Pending Approval
                            </span>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <p class="text-xs uppercase tracking-wider mb-1"
                                   :style="{ color: themeColors.textSecondary }">Amount</p>
                                <p class="font-semibold"
                                   :style="{ color: themeColors.textPrimary }">
                                    {{ formatCurrency(budget.amount) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wider mb-1"
                                   :style="{ color: themeColors.textSecondary }">Category</p>
                                <p :style="{ color: themeColors.textPrimary }">
                                    {{ budget.category?.name || 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wider mb-1"
                                   :style="{ color: themeColors.textSecondary }">Department</p>
                                <p :style="{ color: themeColors.textPrimary }">
                                    {{ budget.department?.name || 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wider mb-1"
                                   :style="{ color: themeColors.textSecondary }">Start Date</p>
                                <p :style="{ color: themeColors.textPrimary }">
                                    {{ formatDate(budget.start_date) }}
                                </p>
                            </div>
                        </div>

                        <div v-if="budget.description" class="mt-2">
                            <p class="text-sm">
                                <span class="text-xs uppercase tracking-wider"
                                      :style="{ color: themeColors.textSecondary }">Description:</span>
                                <span :style="{ color: themeColors.textPrimary }">{{ budget.description }}</span>
                            </p>
                        </div>

                        <div v-if="budget.notes" class="mt-2">
                            <p class="text-sm">
                                <span class="text-xs uppercase tracking-wider"
                                      :style="{ color: themeColors.textSecondary }">Notes:</span>
                                <span :style="{ color: themeColors.textPrimary }">{{ budget.notes }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3">
                        <Link :href="route(`${routePrefix}.budget.show`, { budget: budget.id })"
                              class="px-4 py-2 rounded-md transition-colors flex items-center space-x-2"
                              :style="{
                                  backgroundColor: themeColors.secondary,
                                  color: themeColors.textPrimary
                              }">
                            <EyeIcon class="h-4 w-4" />
                            <span>View Details</span>
                        </Link>
                        <button @click="approveBudget(budget.id)"
                                class="px-4 py-2 rounded-md transition-colors flex items-center space-x-2"
                                :style="{
                                    backgroundColor: themeColors.success,
                                    color: '#ffffff'
                                }"
                                @mouseenter="$event.target.style.backgroundColor = 'rgba(34, 197, 94, 0.9)'"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                            <CheckIcon class="h-4 w-4" />
                            <span>Approve</span>
                        </button>
                        <button @click="rejectBudget(budget.id)"
                                class="px-4 py-2 rounded-md transition-colors flex items-center space-x-2"
                                :style="{
                                    backgroundColor: themeColors.danger,
                                    color: '#ffffff'
                                }"
                                @mouseenter="$event.target.style.backgroundColor = 'rgba(239, 68, 68, 0.9)'"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.danger">
                            <XMarkIcon class="h-4 w-4" />
                            <span>Reject</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pendingExpenses.links && pendingExpenses.links.length > 3" class="mt-6">
                <div class="flex justify-center space-x-1">
                    <Link v-for="link in pendingExpenses.links" :key="link.label"
                          :href="link.url || '#'"
                          v-html="link.label"
                          :class="[
                              'relative inline-flex items-center px-4 py-2 border text-sm font-medium rounded-md',
                              link.active ? 'z-10 border-blue-500 text-blue-600' : 'border-gray-300',
                              link.url ? '' : 'cursor-not-allowed opacity-50'
                          ]"
                          :style="link.active ? { backgroundColor: themeColors.primary, color: '#fff' } : { backgroundColor: themeColors.card, color: themeColors.textSecondary }">
                    </Link>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!pendingExpenses.data?.length"
                 class="shadow rounded-lg p-12 text-center"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <CheckCircleIcon class="h-16 w-16 mx-auto mb-4"
                                 :style="{ color: themeColors.success }" />
                <h3 class="text-lg font-semibold mb-2"
                    :style="{ color: themeColors.textPrimary }">
                    All Caught Up!
                </h3>
                <p class="mb-6"
                   :style="{ color: themeColors.textSecondary }">
                    There are no pending budget approvals at this time.
                </p>
                <Link :href="route(`${routePrefix}.budget.index`)"
                      class="px-4 py-2 rounded-md transition-colors inline-flex items-center space-x-2"
                      :style="{
                          backgroundColor: themeColors.primary,
                          color: '#ffffff'
                      }">
                    <span>View All Budgets</span>
                </Link>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency, formatDate } from '@/Utils/currency.js'
import {
    ArrowLeftIcon,
    ClockIcon,
    CurrencyDollarIcon,
    CheckCircleIcon,
    EyeIcon,
    CheckIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    pendingExpenses: Object,
    totalPendingAmount: Number,
    categories: Array,
    departments: Array,
    years: Array,
    filters: Object,
    routePrefix: { type: String, default: 'admin' },
});

const { loadTheme } = useTheme();
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
}));

loadTheme();

const approveBudget = (budgetId) => {
    if (confirm('Are you sure you want to approve this budget?')) {
        router.post(route(`${props.routePrefix}.budget.approve`, { budget: budgetId }), {}, {
            preserveState: true,
            onSuccess: () => {
                router.reload()
            }
        });
    }
};

const rejectBudget = (budgetId) => {
    if (confirm('Are you sure you want to reject this budget?')) {
        router.post(route(`${props.routePrefix}.budget.reject`, { budget: budgetId }), {}, {
            preserveState: true,
            onSuccess: () => {
                router.reload()
            }
        });
    }
};
</script>
