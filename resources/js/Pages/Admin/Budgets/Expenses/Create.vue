<template>
    <DashboardLayout title="Create Budget Expense">
        <!-- Page Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Create Budget Expense</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Submit a new expense for budget approval.</p>
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
        </div>

        <!-- Create Expense Form -->
        <div class="shadow rounded-lg p-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <form @submit.prevent="submitForm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Budget Selection -->
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textPrimary }">
                            Budget <span class="text-red-500">*</span>
                        </label>
                        <select v-model="form.budget_id"
                                required
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: errors.budget_id ? themeColors.danger : themeColors.border,
                                    color: themeColors.textPrimary
                                }"
                                class="w-full rounded-md px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select a Budget</option>
                            <option v-for="budget in budgets" :key="budget.id" :value="budget.id">
                                {{ budget.name }} - {{ formatCurrency(budget.amount) }}
                                (Remaining: {{ formatCurrency(budget.amount - budget.spent_amount) }})
                            </option>
                        </select>
                        <p v-if="errors.budget_id" class="mt-1 text-sm"
                           :style="{ color: themeColors.danger }">{{ errors.budget_id }}</p>
                    </div>

                    <!-- Expense Date -->
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textPrimary }">
                            Expense Date <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input ref="expenseDateInput" type="date"
                                   v-model="form.expense_date"
                                   required
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: errors.expense_date ? themeColors.danger : themeColors.border,
                                       color: themeColors.textPrimary
                                   }"
                                   class="w-full rounded-md px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer" />
                            <div class="absolute inset-0 cursor-pointer" @click="expenseDateInput?.showPicker ? expenseDateInput.showPicker() : expenseDateInput?.focus()"></div>
                        </div>
                        <p v-if="errors.expense_date" class="mt-1 text-sm"
                           :style="{ color: themeColors.danger }">{{ errors.expense_date }}</p>
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textPrimary }">
                            Amount <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2"
                                  :style="{ color: themeColors.textSecondary }">$</span>
                            <input type="number"
                                   v-model="form.amount"
                                   required
                                   min="0.01"
                                   step="0.01"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: errors.amount ? themeColors.danger : themeColors.border,
                                       color: themeColors.textPrimary
                                   }"
                                   class="w-full rounded-md pl-8 pr-3 py-2 border focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="0.00" />
                        </div>
                        <p v-if="errors.amount" class="mt-1 text-sm"
                           :style="{ color: themeColors.danger }">{{ errors.amount }}</p>
                    </div>

                    <!-- Vendor -->
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textPrimary }">Vendor / Supplier</label>
                        <input type="text"
                               v-model="form.vendor"
                               :style="{
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary
                               }"
                               class="w-full rounded-md px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="e.g., Office Supplies Inc." />
                    </div>

                    <!-- Receipt Number -->
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textPrimary }">Receipt Number</label>
                        <input type="text"
                               v-model="form.receipt_number"
                               :style="{
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary
                               }"
                               class="w-full rounded-md px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="e.g., REC-001234" />
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textPrimary }">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <textarea v-model="form.description"
                                  required
                                  rows="3"
                                  :style="{
                                      backgroundColor: themeColors.background,
                                      borderColor: errors.description ? themeColors.danger : themeColors.border,
                                      color: themeColors.textPrimary
                                  }"
                                  class="w-full rounded-md px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Describe the expense in detail..."></textarea>
                        <p v-if="errors.description" class="mt-1 text-sm"
                           :style="{ color: themeColors.danger }">{{ errors.description }}</p>
                    </div>

                    <!-- Notes -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textPrimary }">Additional Notes</label>
                        <textarea v-model="form.notes"
                                  rows="3"
                                  :style="{
                                      backgroundColor: themeColors.background,
                                      borderColor: themeColors.border,
                                      color: themeColors.textPrimary
                                  }"
                                  class="w-full rounded-md px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Any additional information..."></textarea>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t"
                     :style="{ borderColor: themeColors.border }">
                    <Link :href="route(`${routePrefix}.budget.expenses.index`)"
                          class="px-4 py-2 rounded-md transition-colors"
                          :style="{
                              backgroundColor: themeColors.secondary,
                              color: themeColors.textPrimary
                          }">
                        Cancel
                    </Link>
                    <button type="submit"
                            :disabled="isSubmitting"
                            class="px-6 py-2 rounded-md transition-colors flex items-center space-x-2"
                            :style="{
                                backgroundColor: themeColors.primary,
                                color: '#ffffff',
                                opacity: isSubmitting ? 0.7 : 1
                            }">
                        <ArrowPathIcon v-if="isSubmitting" class="h-4 w-4 animate-spin" />
                        <span>{{ isSubmitting ? 'Submitting...' : 'Submit for Approval' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency'
import {
    ArrowLeftIcon,
    ArrowPathIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    budgets: Array,
    selectedBudgetId: Number,
    routePrefix: { type: String, default: 'admin' }
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

// Form data
const form = ref({
    budget_id: props.selectedBudgetId || '',
    description: '',
    amount: '',
    expense_date: new Date().toISOString().split('T')[0],
    vendor: '',
    receipt_number: '',
    notes: ''
})

const errors = ref({})
const isSubmitting = ref(false)

// Submit form
const submitForm = () => {
    errors.value = {}
    isSubmitting.value = true

    router.post(route(`${props.routePrefix}.budget.expenses.store`), form.value, {
        onSuccess: () => {
            isSubmitting.value = false
        },
        onError: (responseErrors) => {
            errors.value = responseErrors
            isSubmitting.value = false
        }
    })
}
</script>
