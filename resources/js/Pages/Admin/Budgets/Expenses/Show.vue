<template>
    <DashboardLayout title="Expense Details" :user="user">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Expense Details</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">View budget expense information.</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route(`${routePrefix}.budget.expenses.index`)"
                          class="px-4 py-2 rounded-md transition-colors flex items-center space-x-2"
                          :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        <ArrowLeftIcon class="h-4 w-4" />
                        <span>Back</span>
                    </Link>
                    <Link v-if="expense.status === 'pending'"
                          :href="route(`${routePrefix}.budget.expenses.edit`, expense.id)"
                          class="px-4 py-2 rounded-md transition-colors flex items-center space-x-2"
                          :style="{ backgroundColor: themeColors.warning, color: '#ffffff' }"
                          @mouseenter="$event.target.style.backgroundColor = 'rgba(245, 158, 11, 0.9)'"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.warning">
                        <PencilIcon class="h-4 w-4" />
                        <span>Edit</span>
                    </Link>
                    <button v-if="expense.status === 'pending'" @click="approveExpense(expense.id)"
                            class="px-4 py-2 rounded-md transition-colors flex items-center space-x-2"
                            :style="{ backgroundColor: themeColors.success, color: '#ffffff' }"
                            @mouseenter="$event.target.style.backgroundColor = 'rgba(34, 197, 94, 0.9)'"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                        <CheckIcon class="h-4 w-4" />
                        <span>Approve</span>
                    </button>
                    <button v-if="expense.status === 'pending'" @click="rejectExpense(expense.id)"
                            class="px-4 py-2 rounded-md transition-colors flex items-center space-x-2"
                            :style="{ backgroundColor: themeColors.danger, color: '#ffffff' }"
                            @mouseenter="$event.target.style.backgroundColor = 'rgba(239, 68, 68, 0.9)'"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.danger">
                        <XMarkIcon class="h-4 w-4" />
                        <span>Reject</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="shadow rounded-lg p-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-sm" :style="{ color: themeColors.textSecondary }">Budget</div>
                    <div class="font-medium" :style="{ color: themeColors.textPrimary }">{{ expense.budget?.name || 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-sm" :style="{ color: themeColors.textSecondary }">Status</div>
                    <div class="font-medium" :style="{ color: themeColors.textPrimary }">{{ formatStatus(expense.status) }}</div>
                </div>
                <div>
                    <div class="text-sm" :style="{ color: themeColors.textSecondary }">Expense Date</div>
                    <div class="font-medium" :style="{ color: themeColors.textPrimary }">{{ formatDate(expense.expense_date) }}</div>
                </div>
                <div>
                    <div class="text-sm" :style="{ color: themeColors.textSecondary }">Amount</div>
                    <div class="font-medium" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(expense.amount) }}</div>
                </div>
                <div>
                    <div class="text-sm" :style="{ color: themeColors.textSecondary }">Submitted By</div>
                    <div class="font-medium" :style="{ color: themeColors.textPrimary }">{{ expense.creator?.name || expense.creator?.full_name || 'System' }}</div>
                </div>
                <div>
                    <div class="text-sm" :style="{ color: themeColors.textSecondary }">Approved By</div>
                    <div class="font-medium" :style="{ color: themeColors.textPrimary }">{{ expense.approver?.name || expense.approver?.full_name || '-' }}</div>
                </div>
            </div>

            <div class="mt-6">
                <div class="text-sm" :style="{ color: themeColors.textSecondary }">Description</div>
                <div class="mt-1" :style="{ color: themeColors.textPrimary }">{{ expense.description }}</div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-sm" :style="{ color: themeColors.textSecondary }">Vendor</div>
                    <div class="mt-1" :style="{ color: themeColors.textPrimary }">{{ expense.vendor || '-' }}</div>
                </div>
                <div>
                    <div class="text-sm" :style="{ color: themeColors.textSecondary }">Receipt Number</div>
                    <div class="mt-1" :style="{ color: themeColors.textPrimary }">{{ expense.receipt_number || '-' }}</div>
                </div>
            </div>

            <div v-if="expense.notes" class="mt-6">
                <div class="text-sm" :style="{ color: themeColors.textSecondary }">Notes</div>
                <div class="mt-1 whitespace-pre-wrap" :style="{ color: themeColors.textPrimary }">{{ expense.notes }}</div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency, formatDate } from '@/Utils/currency'
import { ArrowLeftIcon, PencilIcon, CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    expense: Object,
    routePrefix: { type: String, default: 'admin' },
})

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

const user = computed(() => props.user)
const expense = computed(() => props.expense)

const formatStatus = (status) => {
    const labels = {
        pending: 'Pending',
        approved: 'Approved',
        rejected: 'Rejected',
        paid: 'Paid'
    }
    return labels[status] || status
}

const approveExpense = (id) => {
    if (confirm('Are you sure you want to approve this expense?')) {
        router.post(route(`${props.routePrefix}.budget.expenses.approve`, id))
    }
}

const rejectExpense = (id) => {
    if (confirm('Are you sure you want to reject this expense?')) {
        router.post(route(`${props.routePrefix}.budget.expenses.reject`, id))
    }
}
</script>
