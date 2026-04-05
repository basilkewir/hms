<template>
    <DashboardLayout title="Expense Management" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Expense Management</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Track and manage hotel operational expenses.</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="addExpense" 
                            class="px-4 py-2 rounded-md transition-colors flex items-center"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                                color: themeColors.textOnPrimary 
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.primaryHover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Add Expense
                    </button>
                </div>
            </div>
        </div>

        <!-- Expense Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg shadow p-6"
                 :style="{ backgroundColor: themeColors.card }">
                <div class="flex items-center">
                    <CurrencyDollarIcon class="h-8 w-8 mr-4"
                                      :style="{ color: themeColors.danger }" />
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Monthly Expenses</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ formatCurrency(expenseStats?.monthly || 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6"
                 :style="{ backgroundColor: themeColors.card }">
                <div class="flex items-center">
                    <ClockIcon class="h-8 w-8 mr-4"
                              :style="{ color: themeColors.warning }" />
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Pending Approval</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ expenseStats?.pending || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6"
                 :style="{ backgroundColor: themeColors.card }">
                <div class="flex items-center">
                    <ChartBarIcon class="h-8 w-8 mr-4"
                                 :style="{ color: themeColors.primary }" />
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Categories</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ expenseStats?.categories || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6"
                 :style="{ backgroundColor: themeColors.card }">
                <div class="flex items-center">
                    <ExclamationTriangleIcon class="h-8 w-8 mr-4"
                                           :style="{ color: themeColors.warning }" />
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Over Budget</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ expenseStats?.overBudget || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="shadow rounded-lg p-4 mb-6"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Status</label>
                    <select v-model="filters.status" @change="applyFilters" 
                            class="w-full rounded-md shadow-sm focus:outline-none transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary,
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Category</label>
                    <select v-model="filters.category" @change="applyFilters" 
                            class="w-full rounded-md shadow-sm focus:outline-none transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary,
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }">
                        <option value="">All Categories</option>
                        <option v-for="cat in (props.categories || [])" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Start Date</label>
                    <input type="date" v-model="filters.startDate" @change="applyFilters"
                           @click="$event.target.showPicker()"
                           class="w-full rounded-md shadow-sm focus:outline-none transition-colors cursor-pointer"
                           :style="{ 
                               backgroundColor: themeColors.background,
                               borderColor: themeColors.border,
                               color: themeColors.textPrimary,
                               borderWidth: '1px',
                               borderStyle: 'solid',
                               padding: '0.5rem 0.75rem'
                           }">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">End Date</label>
                    <input type="date" v-model="filters.endDate" @change="applyFilters"
                           @click="$event.target.showPicker()"
                           class="w-full rounded-md shadow-sm focus:outline-none transition-colors cursor-pointer"
                           :style="{ 
                               backgroundColor: themeColors.background,
                               borderColor: themeColors.border,
                               color: themeColors.textPrimary,
                               borderWidth: '1px',
                               borderStyle: 'solid',
                               padding: '0.5rem 0.75rem'
                           }">
                </div>
            </div>
        </div>

        <!-- Recent Expenses -->
        <div class="shadow rounded-lg overflow-hidden"
             :style="{ backgroundColor: themeColors.card }">
            <div class="px-6 py-4 flex justify-between items-center"
                 :style="{ 
                     borderBottom: `1px solid ${themeColors.border}` 
                 }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Expenses</h3>
                <div class="text-sm"
                     :style="{ color: themeColors.textSecondary }">
                    {{ props.expenses?.total || 0 }} total expenses
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Description
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Category
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="validExpenses.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                     :style="{ stroke: themeColors.textSecondary }">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium"
                                    :style="{ color: themeColors.textPrimary }">No expenses</h3>
                                <p class="mt-1 text-sm"
                                   :style="{ color: themeColors.textSecondary }">Get started by adding a new expense.</p>
                            </td>
                        </tr>
                        <template v-for="expense in validExpenses" :key="expense.id">
                        <tr class="transition-colors"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium"
                                     :style="{ color: themeColors.textPrimary }">{{ expense?.description || 'N/A' }}</div>
                                <div class="text-sm"
                                     :style="{ color: themeColors.textSecondary }">{{ expense?.vendor || 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <div 
                                        v-if="expense?.category_color"
                                        class="w-3 h-3 rounded-full border"
                                        :style="`background-color: ${expense.category_color}; border-color: ${expense.category_color}40`"
                                    ></div>
                                    <span 
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :style="expense?.category_color ? {
                                            backgroundColor: `${expense.category_color}20`,
                                            color: expense.category_color,
                                            borderColor: `${expense.category_color}40`,
                                            borderWidth: '1px',
                                            borderStyle: 'solid'
                                        } : {}"
                                        :class="!expense?.category_color ? getCategoryColor(expense?.category) : ''"
                                    >
                                        {{ expense?.category || 'Uncategorized' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(expense?.amount || 0, expense?.currency) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                {{ formatDate(expense?.date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(expense?.status || '')">
                                    {{ formatStatus(expense?.status || '') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-3">
                                    <button @click="viewExpense(expense)" 
                                            class="transition-colors"
                                            :style="{ color: themeColors.primary }"
                                            @mouseenter="$event.target.style.color = themeColors.primaryHover"
                                            @mouseleave="$event.target.style.color = themeColors.primary">View</button>
                                    <button @click="editExpense(expense)" 
                                            class="transition-colors"
                                            :style="{ color: themeColors.textSecondary }"
                                            @mouseenter="$event.target.style.color = themeColors.textPrimary"
                                            @mouseleave="$event.target.style.color = themeColors.textSecondary">Edit</button>
                                    <button v-if="expense?.status === 'pending'"
                                            @click="quickApprove(expense)"
                                            class="px-2 py-0.5 rounded text-xs font-medium text-white transition-colors"
                                            :style="{ backgroundColor: themeColors.success }">
                                        Approve
                                    </button>
                                </div>
                            </td>
                        </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="props.expenses?.last_page > 1"
                 class="px-6 py-4 flex items-center justify-between border-t"
                 :style="{ borderColor: themeColors.border }">
                <div class="text-sm" :style="{ color: themeColors.textSecondary }">
                    Showing {{ props.expenses.from }}&ndash;{{ props.expenses.to }} of {{ props.expenses.total }}
                </div>
                <div class="flex items-center gap-1">
                    <template v-for="link in props.expenses.links" :key="link.label">
                        <button
                            v-if="link.label === '&laquo; Previous'"
                            @click="link.url && goToPage(link.url)"
                            :disabled="!link.url"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors disabled:opacity-40"
                            :style="{ backgroundColor: themeColors.background, color: themeColors.textSecondary, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                            Prev
                        </button>
                        <button
                            v-else-if="link.label === 'Next &raquo;'"
                            @click="link.url && goToPage(link.url)"
                            :disabled="!link.url"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors disabled:opacity-40"
                            :style="{ backgroundColor: themeColors.background, color: themeColors.textSecondary, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                            Next
                        </button>
                        <button
                            v-else
                            @click="link.url && goToPage(link.url)"
                            :disabled="!link.url"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors disabled:opacity-40"
                            :style="link.active ? { backgroundColor: themeColors.primary, color: '#ffffff' } : { backgroundColor: themeColors.background, color: themeColors.textSecondary, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                            {{ link.label }}
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Add Expense Modal -->
        <DialogModal :show="showAddExpenseModal" @close="closeExpenseModal" max-width="2xl">
            <template #title>
                <span :style="{ color: themeColors.textPrimary }">Add New Expense</span>
            </template>
            <template #content>
                <div class="space-y-5" :style="{ backgroundColor: themeColors.card }">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Category *</label>
                            <select v-model="expenseForm.expense_category_id" required
                                class="w-full px-3 py-2 rounded-lg border focus:outline-none transition-colors"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                                <option value="">Select Category</option>
                                <option v-for="cat in (props.categories || [])" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <p v-if="errors.expense_category_id" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.expense_category_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Vendor / Supplier</label>
                            <input v-model="expenseForm.vendor_name" type="text"
                                class="w-full px-3 py-2 rounded-lg border focus:outline-none transition-colors"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }"
                                placeholder="e.g., Office Depot" />
                            <p v-if="errors.vendor_name" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.vendor_name }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Description *</label>
                        <textarea v-model="expenseForm.description" rows="3"
                            class="w-full px-3 py-2 rounded-lg border focus:outline-none transition-colors"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }"
                            placeholder="Enter expense description"></textarea>
                        <p v-if="errors.description" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.description }}</p>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Date *</label>
                            <input type="date" v-model="expenseForm.expense_date"
                                @click="$event.target.showPicker()"
                                class="w-full px-3 py-2 rounded-lg border focus:outline-none transition-colors cursor-pointer"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }" />
                            <p v-if="errors.expense_date" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.expense_date }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Amount *</label>
                            <input v-model="expenseForm.amount" type="text" inputmode="decimal"
                                class="w-full px-3 py-2 rounded-lg border focus:outline-none transition-colors"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }"
                                placeholder="0.00" />
                            <p v-if="errors.amount" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.amount }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Payment Method</label>
                            <select v-model="expenseForm.payment_method"
                                class="w-full px-3 py-2 rounded-lg border focus:outline-none transition-colors"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                                <option value="">Select Method</option>
                                <option value="cash">Cash</option>
                                <option value="check">Check</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                            <p v-if="errors.payment_method" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.payment_method }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Receipt Number</label>
                        <input v-model="expenseForm.receipt_number" type="text"
                            class="w-full px-3 py-2 rounded-lg border focus:outline-none transition-colors"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }"
                            placeholder="Optional receipt number" />
                        <p v-if="errors.receipt_number" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.receipt_number }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Notes</label>
                        <textarea v-model="expenseForm.notes" rows="2"
                            class="w-full px-3 py-2 rounded-lg border focus:outline-none transition-colors"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }"
                            placeholder="Additional notes (optional)"></textarea>
                        <p v-if="errors.notes" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.notes }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Receipt / Proof Document</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 rounded-lg border-2 border-dashed transition-colors"
                             :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background }">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 48 48"
                                     :style="{ stroke: themeColors.textTertiary }">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm justify-center">
                                    <label for="receipt-file" class="relative cursor-pointer rounded-md font-medium" :style="{ color: themeColors.primary }">
                                        <span>Upload a file</span>
                                        <input id="receipt-file" name="receipt-file" type="file" class="sr-only"
                                               @change="handleReceiptFileChange" accept="image/*,.pdf,.doc,.docx" />
                                    </label>
                                    <p class="pl-1" :style="{ color: themeColors.textSecondary }">or drag and drop</p>
                                </div>
                                <p class="text-xs" :style="{ color: themeColors.textTertiary }">PNG, JPG, PDF, DOC, DOCX up to 10MB</p>
                            </div>
                        </div>
                        <div v-if="expenseForm.receipt_file" class="mt-3 p-3 rounded-lg border"
                             :style="{ borderColor: themeColors.success, backgroundColor: 'rgba(34,197,94,0.08)' }">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" :style="{ color: themeColors.success }">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ expenseForm.receipt_file.name }}</p>
                                    <span class="text-xs" :style="{ color: themeColors.textSecondary }">({{ formatFileSize(expenseForm.receipt_file.size) }})</span>
                                </div>
                                <button @click="removeReceiptFile" type="button" :style="{ color: themeColors.danger }">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p v-if="errors.receipt_file" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.receipt_file }}</p>
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <button @click="closeExpenseModal" type="button"
                        class="px-4 py-2 rounded-lg border transition-colors font-medium"
                        :style="{ borderColor: themeColors.border, color: themeColors.textSecondary, backgroundColor: 'transparent' }">
                        Cancel
                    </button>
                    <button @click="saveExpense" type="button"
                        class="px-4 py-2 rounded-lg transition-colors font-medium text-white"
                        :style="{ backgroundColor: processingExpense ? themeColors.secondary : themeColors.primary }"
                        :disabled="processingExpense">
                        {{ processingExpense ? 'Creating...' : 'Create Expense' }}
                    </button>
                </div>
            </template>
        </DialogModal>

        <!-- Edit Expense Modal -->
        <DialogModal :show="showEditExpenseModal" @close="closeExpenseModal" max-width="2xl">
            <template #title>
                <span :style="{ color: themeColors.textPrimary }">Edit Expense</span>
            </template>
            <template #content>
                <div class="space-y-5" :style="{ backgroundColor: themeColors.card }">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Category *</label>
                            <select v-model="expenseForm.expense_category_id" required
                                class="w-full px-3 py-2 rounded-lg border focus:outline-none transition-colors"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                                <option value="">Select Category</option>
                                <option v-for="cat in (props.categories || [])" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <p v-if="errors.expense_category_id" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.expense_category_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Vendor / Supplier</label>
                            <input v-model="expenseForm.vendor_name" type="text"
                                class="w-full px-3 py-2 rounded-lg border focus:outline-none transition-colors"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }"
                                placeholder="e.g., Office Depot" />
                            <p v-if="errors.vendor_name" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.vendor_name }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Description *</label>
                        <textarea v-model="expenseForm.description" rows="3"
                            class="w-full px-3 py-2 rounded-lg border focus:outline-none transition-colors"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }"
                            placeholder="Enter expense description"></textarea>
                        <p v-if="errors.description" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.description }}</p>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Date *</label>
                            <input type="date" v-model="expenseForm.expense_date"
                                @click="$event.target.showPicker()"
                                class="w-full px-3 py-2 rounded-lg border focus:outline-none transition-colors cursor-pointer"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }" />
                            <p v-if="errors.expense_date" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.expense_date }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Amount *</label>
                            <input v-model="expenseForm.amount" type="text" inputmode="decimal"
                                class="w-full px-3 py-2 rounded-lg border focus:outline-none transition-colors"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }"
                                placeholder="0.00" />
                            <p v-if="errors.amount" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.amount }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Payment Method</label>
                            <select v-model="expenseForm.payment_method"
                                class="w-full px-3 py-2 rounded-lg border focus:outline-none transition-colors"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                                <option value="">Select Method</option>
                                <option value="cash">Cash</option>
                                <option value="check">Check</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                            <p v-if="errors.payment_method" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.payment_method }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Receipt Number</label>
                        <input v-model="expenseForm.receipt_number" type="text"
                            class="w-full px-3 py-2 rounded-lg border focus:outline-none transition-colors"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }"
                            placeholder="Optional receipt number" />
                        <p v-if="errors.receipt_number" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.receipt_number }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Notes</label>
                        <textarea v-model="expenseForm.notes" rows="2"
                            class="w-full px-3 py-2 rounded-lg border focus:outline-none transition-colors"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }"
                            placeholder="Additional notes (optional)"></textarea>
                        <p v-if="errors.notes" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.notes }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Receipt / Proof Document</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 rounded-lg border-2 border-dashed transition-colors"
                             :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background }">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 48 48"
                                     :style="{ stroke: themeColors.textTertiary }">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm justify-center">
                                    <label for="receipt-file-edit" class="relative cursor-pointer rounded-md font-medium" :style="{ color: themeColors.primary }">
                                        <span>Upload a file</span>
                                        <input id="receipt-file-edit" name="receipt-file-edit" type="file" class="sr-only"
                                               @change="handleReceiptFileChange" accept="image/*,.pdf,.doc,.docx" />
                                    </label>
                                    <p class="pl-1" :style="{ color: themeColors.textSecondary }">or drag and drop</p>
                                </div>
                                <p class="text-xs" :style="{ color: themeColors.textTertiary }">PNG, JPG, PDF, DOC, DOCX up to 10MB</p>
                            </div>
                        </div>
                        <div v-if="expenseForm.receipt_file" class="mt-3 p-3 rounded-lg border"
                             :style="{ borderColor: themeColors.success, backgroundColor: 'rgba(34,197,94,0.08)' }">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" :style="{ color: themeColors.success }">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ expenseForm.receipt_file.name }}</p>
                                    <span class="text-xs" :style="{ color: themeColors.textSecondary }">({{ formatFileSize(expenseForm.receipt_file.size) }})</span>
                                </div>
                                <button @click="removeReceiptFile" type="button" :style="{ color: themeColors.danger }">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p v-if="errors.receipt_file" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.receipt_file }}</p>
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <button @click="closeExpenseModal" type="button"
                        class="px-4 py-2 rounded-lg border transition-colors font-medium"
                        :style="{ borderColor: themeColors.border, color: themeColors.textSecondary, backgroundColor: 'transparent' }">
                        Cancel
                    </button>
                    <button @click="updateExpense" type="button"
                        class="px-4 py-2 rounded-lg transition-colors font-medium text-white"
                        :style="{ backgroundColor: processingExpense ? themeColors.secondary : themeColors.success }"
                        :disabled="processingExpense">
                        {{ processingExpense ? 'Updating...' : 'Update Expense' }}
                    </button>
                </div>
            </template>
        </DialogModal>

    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DialogModal from '@/Components/DialogModal.vue'
import DatePicker from '@/Components/DatePicker.vue'
import { formatCurrency as formatCurrencyUtil, initializeCurrencySettings, getCurrencySymbol } from '@/Utils/currency.js'
import { notify } from '@/Composables/useNotification.js'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme.js'
import {
    PlusIcon,
    CurrencyDollarIcon,
    ClockIcon,
    ChartBarIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

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
    expenseStats: {
        type: Object,
        default: () => ({
            monthly: 0,
            pending: 0,
            categories: 0,
            overBudget: 0
        })
    },
    expenses: {
        type: Object,
        default: () => ({ data: [], total: 0, last_page: 1, links: [] })
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    categories: {
        type: Array,
        default: () => []
    },
    settings: {
        type: Object,
        default: () => ({})
    },
    routePrefix: { type: String, default: 'admin' },
})

const supportedCurrencies = computed(() => {
    try {
        const currencies = props.settings?.supported_currencies
        if (typeof currencies === 'string') {
            return JSON.parse(currencies)
        }
        return currencies || {
            'USD': 'US Dollar ($)',
            'EUR': 'Euro (€)',
            'GBP': 'British Pound (£)',
            'XAF': 'Central African CFA Franc (FCFA)'
        }
    } catch (e) {
        return {
            'USD': 'US Dollar ($)',
            'EUR': 'Euro (€)',
            'GBP': 'British Pound (£)',
            'XAF': 'Central African CFA Franc (FCFA)'
        }
    }
})

const filters = ref({
    status: '',
    category: '',
    startDate: '',
    endDate: ''
})

const errors = ref({})

const validExpenses = computed(() => {
    return (props.expenses?.data || []).filter(expense => expense && expense.id)
})

// Get merged settings from component props and page props
const mergedSettings = computed(() => {
    const page = usePage()
    const pageSettings = page?.props?.settings || {}
    const componentSettings = props.settings || {}
    return { ...pageSettings, ...componentSettings }
})

const formatCurrency = (amount, currency = null, position = null) => {
    // Use expense's currency if provided, otherwise use settings currency
    const settings = mergedSettings.value
    const expenseCurrency = currency || settings?.currency || 'USD'
    const expensePosition = position || settings?.currency_position || 'prefix'
    return formatCurrencyUtil(amount, expenseCurrency, expensePosition)
}

const applyFilters = () => {
    router.get(route(`${props.routePrefix}.expenses.index`), {
        status: filters.value.status || undefined,
        category: filters.value.category || undefined,
        start_date: filters.value.startDate || undefined,
        end_date: filters.value.endDate || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const goToPage = (url) => {
    router.visit(url, { preserveState: true, preserveScroll: true })
}

// Initialize currency settings on mount
onMounted(() => {
    initializeCurrencySettings()
    if (props.filters) {
        filters.value.status = props.filters.status || ''
        filters.value.category = props.filters.category || ''
        filters.value.startDate = props.filters.start_date || ''
        filters.value.endDate = props.filters.end_date || ''
    }
})

const getCategoryColor = (category, categoryColor = null) => {
    // If category has a color from database, use it
    if (categoryColor) {
        return '' // Return empty string to use inline style instead
    }
    // Fallback to default colors based on category name
    const categoryLower = (category || '').toLowerCase()
    const colors = {
        'supplies': 'bg-blue-100 text-blue-800',
        'utilities': 'bg-yellow-100 text-yellow-800',
        'maintenance': 'bg-orange-100 text-orange-800',
        'marketing': 'bg-purple-100 text-purple-800',
        'payroll': 'bg-green-100 text-green-800',
        'office': 'bg-indigo-100 text-indigo-800',
        'travel': 'bg-pink-100 text-pink-800',
        'uncategorized': 'bg-gray-100 text-gray-800'
    }
    return colors[categoryLower] || 'bg-gray-100 text-gray-800'
}

const getStatusColor = (status) => {
    const statusLower = (status || '').toLowerCase()
    const colors = {
        'approved': 'bg-green-100 text-green-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'rejected': 'bg-red-100 text-red-800',
        'paid': 'bg-blue-100 text-blue-800',
        'cancelled': 'bg-gray-100 text-gray-800'
    }
    return colors[statusLower] || 'bg-gray-100 text-gray-800'
}

const formatStatus = (status) => {
    if (!status) return 'Unknown'
    return status.charAt(0).toUpperCase() + status.slice(1)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const showAddExpenseModal = ref(false)
const showEditExpenseModal = ref(false)
const editingExpenseId = ref(null)
const processingExpense = ref(false)
const expenseForm = reactive({
    expense_category_id: '',
    vendor_name: '',
    description: '',
    expense_date: new Date().toISOString().split('T')[0],
    amount: '',
    payment_method: '',
    receipt_number: '',
    receipt_file: null,
    notes: ''
})

const addExpense = () => {
    showAddExpenseModal.value = true
}

const handleReceiptFileChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        // Validate file size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            notify.error('File size must be less than 10MB')
            event.target.value = ''
            return
        }
        expenseForm.receipt_file = file
    }
}

const removeReceiptFile = () => {
    expenseForm.receipt_file = null
    const fileInput = document.getElementById('receipt-file')
    if (fileInput) {
        fileInput.value = ''
    }
}

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

const saveExpense = () => {
    processingExpense.value = true
    errors.value = {}
    expenseForm.amount = String(expenseForm.amount || '').trim()

    // Use FormData if there's a file, otherwise use regular form data
    const formData = new FormData()
    formData.append('expense_category_id', expenseForm.expense_category_id)
    formData.append('vendor_name', expenseForm.vendor_name)
    formData.append('description', expenseForm.description)
    formData.append('expense_date', expenseForm.expense_date)
    formData.append('amount', expenseForm.amount)
    formData.append('payment_method', expenseForm.payment_method)
    formData.append('receipt_number', expenseForm.receipt_number || '')
    formData.append('notes', expenseForm.notes || '')
    
    if (expenseForm.receipt_file) {
        formData.append('receipt_file', expenseForm.receipt_file)
    }

    router.post(route(`${props.routePrefix}.expenses.store`), formData, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            notify.success('Expense created successfully.')
            closeExpenseModal()
        },
        onError: (err) => {
            errors.value = err
            notify.error('Failed to create expense. Please check the form.')
        },
        onFinish: () => {
            processingExpense.value = false
        }
    })
}

const updateExpense = () => {
    if (!editingExpenseId.value) {
        notify.error('No expense selected for editing')
        return
    }

    processingExpense.value = true
    errors.value = {}
    expenseForm.amount = String(expenseForm.amount || '').trim()

    // Use FormData if there's a file, otherwise use regular form data
    const formData = new FormData()
    formData.append('expense_category_id', expenseForm.expense_category_id)
    formData.append('vendor_name', expenseForm.vendor_name)
    formData.append('description', expenseForm.description)
    formData.append('expense_date', expenseForm.expense_date)
    formData.append('amount', expenseForm.amount)
    formData.append('payment_method', expenseForm.payment_method)
    formData.append('receipt_number', expenseForm.receipt_number || '')
    formData.append('notes', expenseForm.notes || '')
    
    // Only append file if a new one was selected
    if (expenseForm.receipt_file) {
        formData.append('receipt_file', expenseForm.receipt_file)
    }

    // Use _method to simulate PUT request with FormData
    formData.append('_method', 'PUT')

    router.post(route(`${props.routePrefix}.expenses.update`, editingExpenseId.value), formData, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            notify.success('Expense updated successfully.')
            closeExpenseModal()
        },
        onError: (err) => {
            errors.value = err
            notify.error('Failed to update expense. Please check the form.')
        },
        onFinish: () => {
            processingExpense.value = false
        }
    })
}

const closeExpenseModal = () => {
    showAddExpenseModal.value = false
    showEditExpenseModal.value = false
    editingExpenseId.value = null
    expenseForm.expense_category_id = ''
    expenseForm.vendor_name = ''
    expenseForm.description = ''
    expenseForm.expense_date = new Date().toISOString().split('T')[0]
    expenseForm.amount = ''
    expenseForm.payment_method = ''
    expenseForm.receipt_number = ''
    expenseForm.receipt_file = null
    expenseForm.notes = ''
    errors.value = {}
    // Clear file input
    const fileInput = document.getElementById('receipt-file')
    if (fileInput) {
        fileInput.value = ''
    }
}

const quickApprove = (expense) => {
    if (!expense?.id) return
    router.post(route(`${props.routePrefix}.expenses.approve`, expense.id), {}, {
        preserveScroll: true,
        onSuccess: () => notify.success('Expense approved.'),
        onError: () => notify.error('Failed to approve expense.')
    })
}

const viewExpense = (expense) => {
    router.visit(route(`${props.routePrefix}.expenses.show`, expense.id))
}

const editExpense = (expense) => {
    if (!expense || !expense.id) {
        notify.error('Invalid expense data')
        return
    }
    
    editingExpenseId.value = expense.id
    expenseForm.expense_category_id = expense.category_id || ''
    expenseForm.vendor_name = expense.vendor || ''
    expenseForm.description = expense.description || ''
    expenseForm.expense_date = expense.date || new Date().toISOString().split('T')[0]
    expenseForm.amount = expense.amount || ''
    expenseForm.payment_method = expense.payment_method || ''
    expenseForm.receipt_number = expense.receipt_number || ''
    expenseForm.notes = expense.notes || ''
    
    showEditExpenseModal.value = true
}
</script>
