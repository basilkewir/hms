<template>
    <DashboardLayout :title="isEdit ? 'Edit Expense' : 'Add New Expense'" :user="user">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">
                        {{ isEdit ? 'Edit Expense' : 'Add New Expense' }}
                    </h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">
                        {{ isEdit ? 'Update an existing expense entry.' : 'Record a new business expense.' }}
                    </p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('accountant.expenses.index')" 
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ 
                              backgroundColor: themeColors.secondary,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        <ArrowLeftIcon class="h-4 w-4 mr-2" />
                        Back to Expenses
                    </Link>
                </div>
            </div>
        </div>

        <!-- Expense Form -->
        <div class="rounded-lg shadow p-6"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <form @submit.prevent="submitExpense" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Expense Details -->
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textPrimary }">Expense Description *</label>
                        <input type="text" v-model="form.description" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary
                               }"
                               placeholder="Enter expense description">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textPrimary }">Amount *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2" :style="{ color: themeColors.textTertiary }">{{ currencySymbol }}</span>
                            <input type="number" v-model="form.amount" required step="0.01" min="0"
                                   class="w-full rounded-md pl-8 pr-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }"
                                   placeholder="0.00">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textPrimary }">Category *</label>
                        <select v-model="form.expense_category_id" required
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary
                                }">
                            <option value="">Select Category</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textPrimary }">Date *</label>
                        <DatePicker 
                            v-model="form.expense_date" 
                            required
                            :max="new Date().toISOString().split('T')[0]"
                            placeholder="Select expense date" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textPrimary }">Vendor/Supplier</label>
                        <input type="text" v-model="form.vendor_name"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary
                               }"
                               placeholder="Enter vendor name">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textPrimary }">Payment Method</label>
                        <select v-model="form.payment_method"
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary
                                }">
                            <option value="">Select Payment Method</option>
                            <option value="cash">Cash</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="check">Check</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textPrimary }">Reference Number</label>
                        <input type="text" v-model="form.receipt_number"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary
                               }"
                               placeholder="Invoice/Receipt number">
                    </div>
                </div>

                <!-- Additional Details -->
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textPrimary }">Notes</label>
                    <textarea v-model="form.notes" rows="3"
                              class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                              :style="{ 
                                  backgroundColor: themeColors.background,
                                  borderColor: themeColors.border,
                                  color: themeColors.textPrimary
                              }"
                              placeholder="Additional notes or details about this expense"></textarea>
                </div>

                <!-- Receipt Upload -->
                <div :style="{ borderTop: '1px solid ' + themeColors.border, paddingTop: '1.5rem' }">
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Receipt/Documentation</h3>
                    <div class="border-2 border-dashed rounded-lg p-6 text-center"
                         :style="{ borderColor: themeColors.border }">
                        <DocumentIcon class="mx-auto h-12 w-12" :style="{ color: themeColors.textTertiary }" />
                        <div class="mt-4">
                            <label class="cursor-pointer">
                                <span class="mt-2 block text-sm font-medium"
                                      :style="{ color: themeColors.textPrimary }">
                                    Upload receipt or documentation
                                </span>
                                <input type="file" class="sr-only" @change="handleFileUpload" 
                                       accept="image/*,.pdf,.doc,.docx">
                            </label>
                            <p class="mt-1 text-xs" :style="{ color: themeColors.textTertiary }">PNG, JPG, PDF up to 10MB</p>
                        </div>
                    </div>
                    <div v-if="form.receipt_file" class="mt-4 p-3 rounded-lg"
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                        <p class="text-sm" :style="{ color: themeColors.success }">File uploaded: {{ form.receipt_file.name }}</p>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4"
                     :style="{ borderTop: '1px solid ' + themeColors.border, paddingTop: '1.5rem' }">
                    <Link :href="route('accountant.expenses.index')" 
                          class="px-6 py-2 rounded-md transition-colors font-medium"
                          :style="{ 
                              backgroundColor: themeColors.secondary,
                              color: themeColors.textPrimary
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        Cancel
                    </Link>
                    <button type="submit"
                            class="px-6 py-2 rounded-md transition-colors font-medium text-white"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        {{ isEdit ? 'Update Expense' : 'Submit Expense' }}
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DatePicker from '@/Components/DatePicker.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { getCurrencySymbol, getCurrentCurrency } from '@/Utils/currency.js'
import {
    ArrowLeftIcon,
    DocumentIcon
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
    categories: Array,
    expense: Object,
})

const currencySymbol = computed(() => getCurrencySymbol(getCurrentCurrency()))
const categories = computed(() => props.categories || [])
const isEdit = computed(() => !!props.expense)

const form = ref({
    description: '',
    amount: '',
    expense_category_id: '',
    expense_date: new Date().toISOString().split('T')[0],
    vendor_name: '',
    payment_method: '',
    receipt_number: '',
    notes: '',
    receipt_file: null
})
const errors = ref({})
const processing = ref(false)

const handleFileUpload = (event) => {
    const file = event.target.files[0]
    if (file) {
        form.value.receipt_file = file
    }
}

onMounted(() => {
    if (props.expense) {
        Object.assign(form.value, {
            description: props.expense.description || '',
            amount: props.expense.amount || '',
            expense_category_id: props.expense.expense_category_id || '',
            expense_date: props.expense.expense_date || new Date().toISOString().split('T')[0],
            vendor_name: props.expense.vendor_name || '',
            payment_method: props.expense.payment_method || '',
            receipt_number: props.expense.receipt_number || '',
            notes: props.expense.notes || '',
        })
    }
})

const submitExpense = () => {
    errors.value = {}
    processing.value = true
    const formData = new FormData()
    formData.append('description', form.value.description)
    formData.append('amount', form.value.amount)
    formData.append('expense_category_id', form.value.expense_category_id)
    formData.append('expense_date', form.value.expense_date)
    if (form.value.vendor_name) formData.append('vendor_name', form.value.vendor_name)
    if (form.value.payment_method) formData.append('payment_method', form.value.payment_method)
    if (form.value.receipt_number) formData.append('receipt_number', form.value.receipt_number)
    if (form.value.notes) formData.append('notes', form.value.notes)
    if (form.value.receipt_file) formData.append('receipt_file', form.value.receipt_file)

    if (isEdit.value) {
        formData.append('_method', 'PUT')
        router.post(route('accountant.expenses.update', props.expense.id), formData, {
            forceFormData: true,
            onSuccess: () => {
                processing.value = false
            },
            onError: (err) => {
                errors.value = err
                processing.value = false
            }
        })
        return
    }

    router.post(route('accountant.expenses.store'), formData, {
        forceFormData: true,
        onSuccess: () => {
            processing.value = false
        },
        onError: (err) => {
            errors.value = err
            processing.value = false
        }
    })
}
</script>
