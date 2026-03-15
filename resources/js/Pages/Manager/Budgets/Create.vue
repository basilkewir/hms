<template>
    <DashboardLayout title="Create Budget">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Create New Budget</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Set up a new budget for expense tracking and management.</p>
                </div>
                <Link :href="route(`${routePrefix}.budget.dashboard`)"
                      class="px-4 py-2 rounded-md transition-colors"
                      :style="{
                          backgroundColor: themeColors.secondary,
                          color: themeColors.textPrimary
                      }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                    Back to Budgets
                </Link>
            </div>

            <form @submit.prevent="submitForm" class="space-y-6">
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Budget Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">
                                Budget Name <span :style="{ color: themeColors.danger }">*</span>
                            </label>
                            <input
                                type="text"
                                v-model="form.name"
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }"
                                placeholder="Enter budget name"
                            />
                            <p v-if="errors.name" class="mt-1 text-sm"
                               :style="{ color: themeColors.danger }">{{ errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">
                                Category <span :style="{ color: themeColors.danger }">*</span>
                            </label>
                            <select
                                v-model="form.category_id"
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }"
                            >
                                <option value="">Select Category</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                            <p v-if="errors.category_id" class="mt-1 text-sm"
                               :style="{ color: themeColors.danger }">{{ errors.category_id }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Budget Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">
                                Department
                            </label>
                            <select
                                v-model="form.department_id"
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }"
                            >
                                <option value="">Select Department (Optional)</option>
                                <option v-for="department in departments" :key="department.id" :value="department.id">
                                    {{ department.name }}
                                </option>
                            </select>
                            <p v-if="errors.department_id" class="mt-1 text-sm"
                               :style="{ color: themeColors.danger }">{{ errors.department_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">
                                Amount <span :style="{ color: themeColors.danger }">*</span>
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                v-model="form.amount"
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }"
                                placeholder="0.00"
                            />
                            <p v-if="errors.amount" class="mt-1 text-sm"
                               :style="{ color: themeColors.danger }">{{ errors.amount }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Date Range</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">
                                Start Date <span :style="{ color: themeColors.danger }">*</span>
                            </label>
                            <DatePicker
                                v-model="form.start_date"
                                placeholder="Select start date"
                                :required="true"
                            />
                            <p v-if="errors.start_date" class="mt-1 text-sm"
                               :style="{ color: themeColors.danger }">{{ errors.start_date }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">
                                End Date <span :style="{ color: themeColors.danger }">*</span>
                            </label>
                            <DatePicker
                                v-model="form.end_date"
                                placeholder="Select end date"
                                :required="true"
                                :min="form.start_date"
                            />
                            <p v-if="errors.end_date" class="mt-1 text-sm"
                               :style="{ color: themeColors.danger }">{{ errors.end_date }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Additional Information</h3>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Description</label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }"
                                placeholder="Enter budget description"
                            ></textarea>
                            <p v-if="errors.description" class="mt-1 text-sm"
                               :style="{ color: themeColors.danger }">{{ errors.description }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Notes</label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }"
                                placeholder="Enter any additional notes"
                            ></textarea>
                            <p v-if="errors.notes" class="mt-1 text-sm"
                               :style="{ color: themeColors.danger }">{{ errors.notes }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 pt-6 border-t"
                     :style="{
                         borderTopColor: themeColors.border,
                         borderTopWidth: '1px',
                         borderTopStyle: 'solid'
                     }">
                    <Link :href="route(`${routePrefix}.budget.dashboard`)"
                          class="px-6 py-2 rounded-md transition-colors"
                          :style="{
                              backgroundColor: themeColors.secondary,
                              color: themeColors.textPrimary
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        Cancel
                    </Link>

                    <button
                        type="submit"
                        :disabled="processing"
                        class="px-6 py-2 rounded-md transition-colors disabled:opacity-50"
                        :style="{
                            backgroundColor: themeColors.primary,
                            color: 'white'
                        }"
                        @mouseenter="!processing && ($event.target.style.backgroundColor = themeColors.hover)"
                        @mouseleave="!processing && ($event.target.style.backgroundColor = themeColors.primary)"
                    >
                        <span v-if="processing">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Creating...
                        </span>
                        <span v-else>Create Budget</span>
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
import DatePicker from '@/Components/DatePicker.vue'

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
    categories: Array,
    departments: Array,
    errors: Object,
    routePrefix: { type: String, default: 'admin' },
})

const processing = ref(false)
const form = ref({
    name: '',
    description: '',
    amount: '',
    start_date: '',
    end_date: '',
    category_id: '',
    department_id: '',
    notes: '',
})

const currentDateTime = computed(() => {
    return new Date().toLocaleString()
})

const submitForm = () => {
    processing.value = true

    router.post(route(`${props.routePrefix}.budget.store`), form.value, {
        preserveScroll: true,
        onSuccess: () => {
            processing.value = false
        },
        onError: (errors) => {
            processing.value = false
            console.error('Budget creation errors:', errors)
        }
    })
}
</script>
