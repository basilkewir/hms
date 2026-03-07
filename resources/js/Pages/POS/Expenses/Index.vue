<template>
    <AppLayout title="Expense Management">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Expense Management
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Add Expense Button -->
                <div class="mb-6">
                    <button 
                        @click="showAddExpense = true"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                    >
                        Add New Expense
                    </button>
                </div>

                <!-- Expenses Table -->
                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Expenses</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expense #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="expense in expenses.data" :key="expense.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ expense.expense_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ expense.description }}</div>
                                        <div v-if="expense.receipt_number" class="text-sm text-gray-500">Receipt: {{ expense.receipt_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                              :style="`background-color: ${expense.category.color}20; color: ${expense.category.color}`">
                                            {{ expense.category.name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatCurrency(expense.amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="capitalize">{{ expense.payment_method.replace('_', ' ') }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDate(expense.expense_date) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ expense.user.first_name }} {{ expense.user.last_name }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Expense Modal -->
        <DialogModal :show="showAddExpense" @close="showAddExpense = false">
            <template #title>Add New Expense</template>
            <template #content>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select v-model="form.category_id" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Select category...</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <input 
                            v-model="form.description" 
                            type="text" 
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            placeholder="Enter expense description"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                        <input 
                            v-model="form.amount" 
                            type="number" 
                            step="0.01"
                            min="0"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            placeholder="0.00"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <select v-model="form.payment_method" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expense Date</label>
                        <div class="relative">
                            <input 
                                ref="expenseDateInput"
                                v-model="form.expense_date" 
                                type="date" 
                                class="w-full border-gray-300 rounded-md shadow-sm cursor-pointer"
                            >
                            <div class="absolute inset-0 cursor-pointer" @click="expenseDateInput?.showPicker ? expenseDateInput.showPicker() : expenseDateInput?.focus()"></div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Receipt Number (Optional)</label>
                        <input 
                            v-model="form.receipt_number" 
                            type="text" 
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            placeholder="Receipt or invoice number"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                        <textarea 
                            v-model="form.notes" 
                            rows="3"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            placeholder="Additional notes..."
                        ></textarea>
                    </div>
                </div>
            </template>
            <template #footer>
                <SecondaryButton @click="showAddExpense = false">Cancel</SecondaryButton>
                <PrimaryButton @click="addExpense" class="ml-3" :disabled="!isFormValid || processing">
                    {{ processing ? 'Adding...' : 'Add Expense' }}
                </PrimaryButton>
            </template>
        </DialogModal>
    </AppLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import DialogModal from '@/Components/DialogModal.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import { router } from '@inertiajs/vue3'
import { formatCurrency, initializeCurrencySettings } from '@/Utils/currency.js'

export default {
    components: {
        AppLayout,
        DialogModal,
        PrimaryButton,
        SecondaryButton
    },
    props: {
        user: Object,
        expenses: Object,
        categories: Array
    },
    setup(props) {
        const showAddExpense = ref(false)
        const processing = ref(false)
        
        const form = ref({
            category_id: '',
            description: '',
            amount: '',
            payment_method: 'cash',
            expense_date: new Date().toISOString().split('T')[0],
            receipt_number: '',
            notes: ''
        })

        const isFormValid = computed(() => {
            return form.value.category_id && 
                   form.value.description && 
                   form.value.amount && 
                   form.value.expense_date
        })

        const addExpense = () => {
            if (!isFormValid.value) return
            
            processing.value = true
            
            router.post('/pos/expenses', form.value, {
                onSuccess: () => {
                    showAddExpense.value = false
                    resetForm()
                },
                onError: (errors) => {
                    console.error('Failed to add expense:', errors)
                },
                onFinish: () => {
                    processing.value = false
                }
            })
        }

        const resetForm = () => {
            form.value = {
                category_id: '',
                description: '',
                amount: '',
                payment_method: 'cash',
                expense_date: new Date().toISOString().split('T')[0],
                receipt_number: '',
                notes: ''
            }
        }

        const formatDate = (date) => {
            return new Date(date).toLocaleDateString()
        }

        // Initialize currency settings
        onMounted(() => {
            initializeCurrencySettings()
        })

        return {
            showAddExpense,
            processing,
            form,
            isFormValid,
            addExpense,
            resetForm,
            formatDate,
            formatCurrency
        }
    }
}
</script>