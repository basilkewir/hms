<template>
    <DashboardLayout title="Create Expense">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Create New Expense</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Add a new expense record to track hotel operational costs.</p>
                </div>
                <Link :href="route(`${routePrefix}.expenses.index`)"
                      class="px-4 py-2 rounded-md transition-colors"
                      :style="{ 
                          backgroundColor: themeColors.secondary,
                          color: themeColors.textPrimary 
                      }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                    <ArrowLeftIcon class="h-4 w-4 mr-2 inline" />
                    Back
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Information -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Category *</label>
                            <select v-model="form.expense_category_id" required
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="">Select Category</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                            <div v-if="errors.expense_category_id" class="mt-2 text-sm"
                                 :style="{ color: themeColors.danger }">{{ errors.expense_category_id }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Budget (Optional)</label>
                            <select v-model="form.budget_id"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="">No Budget</option>
                                <option v-for="budget in budgets" :key="budget.id" :value="budget.id">
                                    {{ budget.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Guest (Optional)</label>
                            <select v-model="form.guest_id"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="">No Guest</option>
                                <option v-for="guest in guests" :key="guest.id" :value="guest.id">
                                    {{ guest.first_name }} {{ guest.last_name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Vendor Name *</label>
                            <input type="text" v-model="form.vendor_name" required
                                   placeholder="e.g., Office Depot"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                            <div v-if="errors.vendor_name" class="mt-2 text-sm"
                                 :style="{ color: themeColors.danger }">{{ errors.vendor_name }}</div>
                        </div>
                    </div>
                </div>

                <!-- Expense Details -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Expense Details</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Description *</label>
                            <textarea v-model="form.description" rows="3" required
                                      placeholder="Enter expense description"
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="{ 
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px',
                                          borderStyle: 'solid'
                                      }"></textarea>
                            <div v-if="errors.description" class="mt-2 text-sm"
                                 :style="{ color: themeColors.danger }">{{ errors.description }}</div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2"
                                       :style="{ color: themeColors.textSecondary }">Date *</label>
                                <div class="relative">
                                    <input ref="expenseDateInput" type="date" v-model="form.expense_date" required
                                           class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors cursor-pointer"
                                           :style="{ 
                                               backgroundColor: themeColors.background,
                                               borderColor: themeColors.border,
                                               color: themeColors.textPrimary,
                                               borderWidth: '1px',
                                               borderStyle: 'solid'
                                           }">
                                    <div class="absolute inset-0 cursor-pointer" @click="expenseDateInput?.showPicker ? expenseDateInput.showPicker() : expenseDateInput?.focus()"></div>
                                </div>
                                <div v-if="errors.expense_date" class="mt-2 text-sm"
                                     :style="{ color: themeColors.danger }">{{ errors.expense_date }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2"
                                       :style="{ color: themeColors.textSecondary }">Amount *</label>
                                <input type="number" v-model="form.amount" step="0.01" min="0" required
                                       placeholder="0.00"
                                       class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                       :style="{ 
                                           backgroundColor: themeColors.background,
                                           borderColor: themeColors.border,
                                           color: themeColors.textPrimary,
                                           borderWidth: '1px',
                                           borderStyle: 'solid'
                                       }">
                                <div v-if="errors.amount" class="mt-2 text-sm"
                                     :style="{ color: themeColors.danger }">{{ errors.amount }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2"
                                       :style="{ color: themeColors.textSecondary }">Currency</label>
                                <select v-model="form.currency"
                                        class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                        :style="{ 
                                            backgroundColor: themeColors.background,
                                            borderColor: themeColors.border,
                                            color: themeColors.textPrimary,
                                            borderWidth: '1px',
                                            borderStyle: 'solid'
                                        }">
                                    <option value="USD">USD - US Dollar</option>
                                    <option value="EUR">EUR - Euro</option>
                                    <option value="GBP">GBP - British Pound</option>
                                    <option value="XAF">XAF - Central African CFA Franc</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Payment Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Payment Method</label>
                            <select v-model="form.payment_method"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="">Select Payment Method</option>
                                <option value="cash">Cash</option>
                                <option value="card">Credit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="check">Check</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Receipt Number</label>
                            <input type="text" v-model="form.receipt_number"
                                   placeholder="Optional receipt number"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Additional Information</h3>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Notes</label>
                        <textarea v-model="form.notes" rows="3"
                                  placeholder="Additional notes about this expense"
                                  class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                  :style="{ 
                                      backgroundColor: themeColors.background,
                                      borderColor: themeColors.border,
                                      color: themeColors.textPrimary,
                                      borderWidth: '1px',
                                      borderStyle: 'solid'
                                  }"></textarea>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4 pt-6"
                     :style="{ borderTop: `1px solid ${themeColors.border}` }">
                    <Link :href="route(`${routePrefix}.expenses.index`)"
                          class="px-4 py-2 rounded-md transition-colors"
                          :style="{ 
                              backgroundColor: themeColors.secondary,
                              color: themeColors.textPrimary 
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="processing"
                            class="px-4 py-2 rounded-md transition-colors flex items-center"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                                color: themeColors.textOnPrimary 
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.primaryHover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <svg v-if="processing" class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ processing ? 'Creating...' : 'Create Expense' }}
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { usePage } from '@inertiajs/vue3'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

// Theme system
const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    primary: `var(--kotel-primary)`,
    primaryHover: `var(--kotel-primary-hover)`,
    secondary: `var(--kotel-secondary)`,
    success: `var(--kotel-success)`,
    successHover: `var(--kotel-success-hover)`,
    danger: `var(--kotel-danger)`,
    dangerHover: `var(--kotel-danger-hover)`,
    warning: `var(--kotel-warning)`,
    warningHover: `var(--kotel-warning-hover)`,
    hover: `rgba(255, 255, 255, 0.1)`,
    textOnPrimary: `var(--kotel-text-on-primary)`
}))

// Load theme on mount
loadTheme()

// Permission-based navigation
const page = usePage()
const navigation = computed(() => getNavigationForRole(page.props.auth.permissions || []))

const props = defineProps({
    user: Object,
    categories: Array,
    budgets: { type: Array, default: () => [] },
    guests: { type: Array, default: () => [] },
    routePrefix: { type: String, default: 'admin' },
})

const form = useForm({
    expense_category_id: '',
    budget_id: '',
    guest_id: '',
    vendor_name: '',
    description: '',
    expense_date: new Date().toISOString().split('T')[0],
    amount: '',
    currency: 'USD',
    payment_method: '',
    receipt_number: '',
    notes: ''
})

const processing = ref(false)
const errors = ref({})

const submit = () => {
    processing.value = true
    errors.value = {}
    
    form.post(route(`${props.routePrefix}.expenses.store`), {
        onSuccess: () => {
            processing.value = false
        },
        onError: (error) => {
            processing.value = false
            errors.value = error
        }
    })
}
</script>
