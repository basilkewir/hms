<template>
    <DashboardLayout title="Edit Expense" :user="user">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Edit Expense</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Update a pending budget expense.</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route(`${routePrefix}.budget.expenses.show`, expense.id)"
                          class="px-4 py-2 rounded-md transition-colors flex items-center space-x-2"
                          :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        <ArrowLeftIcon class="h-4 w-4" />
                        <span>Back</span>
                    </Link>
                </div>
            </div>
        </div>

        <div class="shadow rounded-lg p-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <form @submit.prevent="submitForm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Budget</label>
                        <select v-model="form.budget_id"
                                :style="{ backgroundColor: themeColors.background, borderColor: errors.budget_id ? themeColors.danger : themeColors.border, color: themeColors.textPrimary }"
                                class="w-full rounded-md px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select a Budget</option>
                            <option v-for="b in budgets" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                        <p v-if="errors.budget_id" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ errors.budget_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Expense Date</label>
                        <div class="relative">
                            <input ref="expenseDateInput" type="date" v-model="form.expense_date"
                                   :style="{ backgroundColor: themeColors.background, borderColor: errors.expense_date ? themeColors.danger : themeColors.border, color: themeColors.textPrimary }"
                                   class="w-full rounded-md px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer" />
                            <div class="absolute inset-0 cursor-pointer" @click="expenseDateInput?.showPicker ? expenseDateInput.showPicker() : expenseDateInput?.focus()"></div>
                        </div>
                        <p v-if="errors.expense_date" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ errors.expense_date }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Amount</label>
                        <input type="number" min="0.01" step="0.01" v-model="form.amount"
                               :style="{ backgroundColor: themeColors.background, borderColor: errors.amount ? themeColors.danger : themeColors.border, color: themeColors.textPrimary }"
                               class="w-full rounded-md px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        <p v-if="errors.amount" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ errors.amount }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Vendor</label>
                        <input type="text" v-model="form.vendor"
                               :style="{ backgroundColor: themeColors.background, borderColor: errors.vendor ? themeColors.danger : themeColors.border, color: themeColors.textPrimary }"
                               class="w-full rounded-md px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        <p v-if="errors.vendor" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ errors.vendor }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Description</label>
                        <textarea rows="3" v-model="form.description"
                                  :style="{ backgroundColor: themeColors.background, borderColor: errors.description ? themeColors.danger : themeColors.border, color: themeColors.textPrimary }"
                                  class="w-full rounded-md px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        <p v-if="errors.description" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ errors.description }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Receipt Number</label>
                        <input type="text" v-model="form.receipt_number"
                               :style="{ backgroundColor: themeColors.background, borderColor: errors.receipt_number ? themeColors.danger : themeColors.border, color: themeColors.textPrimary }"
                               class="w-full rounded-md px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        <p v-if="errors.receipt_number" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ errors.receipt_number }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Status</label>
                        <select v-model="form.status"
                                :style="{ backgroundColor: themeColors.background, borderColor: errors.status ? themeColors.danger : themeColors.border, color: themeColors.textPrimary }"
                                class="w-full rounded-md px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
                        </select>
                        <p v-if="errors.status" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ errors.status }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Notes</label>
                        <textarea rows="3" v-model="form.notes"
                                  :style="{ backgroundColor: themeColors.background, borderColor: errors.notes ? themeColors.danger : themeColors.border, color: themeColors.textPrimary }"
                                  class="w-full rounded-md px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        <p v-if="errors.notes" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ errors.notes }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t" :style="{ borderColor: themeColors.border }">
                    <Link :href="route(`${routePrefix}.budget.expenses.show`, expense.id)"
                          class="px-4 py-2 rounded-md transition-colors"
                          :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="isSubmitting"
                            class="px-6 py-2 rounded-md transition-colors"
                            :style="{ backgroundColor: themeColors.primary, color: '#ffffff', opacity: isSubmitting ? 0.7 : 1 }">
                        {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    expense: Object,
    budgets: Array,
    routePrefix: { type: String, default: 'admin' },
    statuses: Object
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
const budgets = computed(() => props.budgets || [])
const statuses = computed(() => props.statuses || {})

const form = ref({
    budget_id: props.expense?.budget_id ?? '',
    description: props.expense?.description ?? '',
    amount: props.expense?.amount ?? '',
    expense_date: props.expense?.expense_date ?? '',
    vendor: props.expense?.vendor ?? '',
    receipt_number: props.expense?.receipt_number ?? '',
    notes: props.expense?.notes ?? '',
    status: props.expense?.status ?? 'pending'
})

const errors = ref({})
const isSubmitting = ref(false)

const submitForm = () => {
    errors.value = {}
    isSubmitting.value = true

    router.put(route(`${props.routePrefix}.budget.expenses.update`, props.expense.id), form.value, {
        onError: (e) => {
            errors.value = e
            isSubmitting.value = false
        },
        onSuccess: () => {
            isSubmitting.value = false
        }
    })
}
</script>
