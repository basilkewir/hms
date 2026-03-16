<template>
    <DashboardLayout title="Purchase Order Details" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Purchase Order Details</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">View and manage purchase order information.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="router.visit(route('manager.purchases.index'))"
                            class="px-4 py-2 rounded-md transition-colors font-medium flex items-center"
                            :style="{
                                borderColor: themeColors.border,
                                color: themeColors.textSecondary,
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }">
                        <ArrowLeftIcon class="h-4 w-4 mr-2" />
                        Back to Purchases
                    </button>
                    <button v-if="canEdit" @click="editPurchaseOrder"
                            class="px-4 py-2 rounded-md transition-colors font-medium flex items-center"
                            :style="{
                                backgroundColor: themeColors.primary,
                                color: '#000000'
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PencilIcon class="h-4 w-4 mr-2" />
                        Edit
                    </button>
                    <button v-if="canReceive" @click="receivePurchaseOrder"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{
                                backgroundColor: themeColors.success,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = '#16a34a'"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                        <CheckIcon class="h-4 w-4 mr-2" />
                        Receive Items
                    </button>
                </div>
            </div>
        </div>

        <!-- Purchase Order Info -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Main Info -->
            <div class="lg:col-span-2">
                <div class="rounded-lg overflow-hidden shadow"
                     :style="{
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }">
                    <!-- Header -->
                    <div class="px-6 py-4 border-b"
                         :style="{
                             borderColor: themeColors.border,
                             borderBottomWidth: '1px'
                         }">
                        <h3 class="text-lg font-medium"
                            :style="{ color: themeColors.textPrimary }">Order Information</h3>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2"
                                       :style="{ color: themeColors.textSecondary }">PO Number</label>
                                <p class="text-lg font-bold"
                                   :style="{ color: themeColors.textPrimary }">{{ purchaseOrder.po_number }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2"
                                       :style="{ color: themeColors.textSecondary }">Status</label>
                                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full"
                                      :class="getStatusClass(purchaseOrder.status)">
                                    {{ purchaseOrder.status }}
                                </span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2"
                                       :style="{ color: themeColors.textSecondary }">Supplier</label>
                                <p class="font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ purchaseOrder.supplier?.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2"
                                       :style="{ color: themeColors.textSecondary }">Order Date</label>
                                <p class="font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ formatDate(purchaseOrder.order_date) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2"
                                       :style="{ color: themeColors.textSecondary }">Expected Date</label>
                                <p class="font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ formatDate(purchaseOrder.expected_date) || 'Not set' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2"
                                       :style="{ color: themeColors.textSecondary }">Created By</label>
                                <p class="font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ purchaseOrder.user?.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2"
                                       :style="{ color: themeColors.textSecondary }">Purchase Type</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="purchaseOrder.purchase_type === 'expense' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800'">
                                    {{ purchaseOrder.purchase_type === 'expense' ? '💸 Expense' : '📦 Resale / Inventory' }}
                                </span>
                            </div>
                            <div v-if="purchaseOrder.location">
                                <label class="block text-sm font-medium mb-2"
                                       :style="{ color: themeColors.textSecondary }">Deliver To</label>
                                <p class="font-medium"
                                   :style="{ color: themeColors.textPrimary }">📍 {{ purchaseOrder.location.name }}<span v-if="purchaseOrder.location.type" class="text-xs ml-1" :style="{ color: themeColors.textSecondary }">— {{ purchaseOrder.location.type }}</span></p>
                            </div>
                            <div v-if="purchaseOrder.purchase_type === 'expense' && purchaseOrder.expense_category">
                                <label class="block text-sm font-medium mb-2"
                                       :style="{ color: themeColors.textSecondary }">Expense Category</label>
                                <p class="font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ purchaseOrder.expense_category }}</p>
                            </div>
                        </div>
                        
                        <!-- Notes and Conditions -->
                        <div class="mt-6 space-y-4">
                            <div v-if="purchaseOrder.purchase_conditions">
                                <label class="block text-sm font-medium mb-2"
                                       :style="{ color: themeColors.textSecondary }">Purchase Conditions</label>
                                <p class="text-sm"
                                   :style="{ color: themeColors.textPrimary }">{{ purchaseOrder.purchase_conditions }}</p>
                            </div>
                            <div v-if="purchaseOrder.notes">
                                <label class="block text-sm font-medium mb-2"
                                       :style="{ color: themeColors.textSecondary }">Notes</label>
                                <p class="text-sm"
                                   :style="{ color: themeColors.textPrimary }">{{ purchaseOrder.notes }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Summary -->
            <div>
                <div class="rounded-lg overflow-hidden shadow"
                     :style="{
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }">
                    <!-- Header -->
                    <div class="px-6 py-4 border-b"
                         :style="{
                             borderColor: themeColors.border,
                             borderBottomWidth: '1px'
                         }">
                        <h3 class="text-lg font-medium"
                            :style="{ color: themeColors.textPrimary }">Financial Summary</h3>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between">
                            <span :style="{ color: themeColors.textSecondary }">Subtotal:</span>
                            <span class="font-medium"
                                  :style="{ color: themeColors.textPrimary }">{{ formatCurrency(purchaseOrder.subtotal) }}</span>
                        </div>
                        <div v-if="purchaseOrder.tax_amount > 0" class="flex justify-between">
                            <span :style="{ color: themeColors.textSecondary }">Tax Amount ({{ purchaseOrder.tax_rate || 0 }}%):</span>
                            <span class="font-medium"
                                  :style="{ color: themeColors.textPrimary }">{{ formatCurrency(purchaseOrder.tax_amount) }}</span>
                        </div>
                        <div class="border-t pt-4"
                             :style="{ borderColor: themeColors.border }">
                            <div class="flex justify-between">
                                <span class="font-semibold"
                                      :style="{ color: themeColors.textPrimary }">Total Amount:</span>
                                <span class="font-bold text-lg"
                                      :style="{ color: themeColors.primary }">{{ formatCurrency(purchaseOrder.total_amount) }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <span :style="{ color: themeColors.textSecondary }">Paid Amount:</span>
                            <span class="font-medium"
                                  :style="{ color: themeColors.success }">{{ formatCurrency(purchaseOrder.paid_amount) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span :style="{ color: themeColors.textSecondary }">Remaining:</span>
                            <span class="font-medium"
                                  :style="{ color: themeColors.danger }">{{ formatCurrency(purchaseOrder.remaining_amount) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="rounded-lg overflow-hidden shadow"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <!-- Header -->
            <div class="px-6 py-4 border-b"
                 :style="{
                     borderColor: themeColors.border,
                     borderBottomWidth: '1px'
                 }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Order Items</h3>
            </div>
            
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Unit Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Total Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in purchaseOrder.items" :key="item.id"
                            class="transition-colors"
                            :style="{
                                borderBottomStyle: 'solid',
                                borderBottomWidth: '1px',
                                borderColor: themeColors.border
                            }">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium"
                                         :style="{ color: themeColors.textPrimary }">{{ item.product?.name || 'N/A' }}</div>
                                    <div class="text-xs"
                                         :style="{ color: themeColors.textTertiary }">{{ item.product?.code || 'N/A' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div>
                                    <span class="font-medium"
                                          :style="{ color: themeColors.textPrimary }">{{ item.quantity_ordered }}</span>
                                    <span class="text-xs"
                                          :style="{ color: themeColors.textTertiary }"> ordered</span>
                                    <br>
                                    <span class="font-medium"
                                          :style="{ color: themeColors.success }">{{ item.quantity_received }}</span>
                                    <span class="text-xs"
                                          :style="{ color: themeColors.textTertiary }"> received</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">{{ formatCurrency(item.unit_cost) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">{{ formatCurrency(item.total_cost) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                      :class="getItemStatusClass(item.quantity_ordered, item.quantity_received)">
                                    {{ getItemStatus(item.quantity_ordered, item.quantity_received) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Section -->
        <div class="rounded-lg overflow-hidden shadow"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <!-- Header -->
            <div class="px-6 py-4 border-b flex items-center justify-between"
                 :style="{
                     borderColor: themeColors.border,
                     borderBottomWidth: '1px'
                 }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Payment History & Recording</h3>
                <button v-if="purchaseOrder.remaining_amount > 0" 
                        @click="showPaymentModal = true"
                        class="px-4 py-2 rounded-md font-medium text-white"
                        style="background-color: var(--kotel-primary); color: #000000;">
                    + Record Payment
                </button>
            </div>

            <!-- Payment History -->
            <div class="p-6">
                <div v-if="purchaseOrder.payments && purchaseOrder.payments.length > 0" class="space-y-4">
                    <div v-for="payment in purchaseOrder.payments" :key="payment.id"
                         class="flex items-center justify-between p-4 rounded-lg border"
                         :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background }">
                        <div>
                            <p class="font-medium" :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(payment.amount) }}
                            </p>
                            <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                                {{ payment.payment_method }} - {{ new Date(payment.payment_date).toLocaleDateString() }}
                            </p>
                            <p class="text-xs" :style="{ color: themeColors.textTertiary }">
                                {{ payment.reference_number ? `Ref: ${payment.reference_number}` : 'No reference' }}
                            </p>
                        </div>
                        <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full"
                              :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)', color: '#22c55e' }">
                            {{ payment.payment_type }}
                        </span>
                    </div>
                </div>
                <div v-else class="text-center py-8">
                    <p :style="{ color: themeColors.textSecondary }">No payments recorded yet</p>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <div v-if="showPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="rounded-lg p-6 max-w-md w-full shadow-lg"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold"
                        :style="{ color: themeColors.textPrimary }">Record Payment</h3>
                    <button @click="showPaymentModal = false"
                            class="p-2 rounded-lg transition-colors"
                            :style="{
                                backgroundColor: themeColors.background,
                                color: themeColors.textSecondary
                            }">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitPayment" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Amount</label>
                        <input v-model.number="paymentForm.amount"
                               type="number"
                               step="0.01"
                               :max="purchaseOrder.remaining_amount"
                               class="w-full px-3 py-2 border rounded-lg"
                               :style="{
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary
                               }"
                               placeholder="0.00"
                               required />
                        <p class="text-xs mt-1" :style="{ color: themeColors.textSecondary }">
                            Remaining: {{ formatCurrency(purchaseOrder.remaining_amount) }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Payment Method</label>
                        <select v-model="paymentForm.payment_method"
                                class="w-full px-3 py-2 border rounded-lg"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary
                                }"
                                required>
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="credit_card">Credit Card</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Payment Date</label>
                        <div class="relative">
                            <input ref="paymentDateInput" v-model="paymentForm.payment_date"
                                   type="date"
                                   class="w-full px-3 py-2 border rounded-lg cursor-pointer"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }"
                                   required />
                            <div class="absolute inset-0 cursor-pointer" @click="paymentDateInput?.showPicker ? paymentDateInput.showPicker() : paymentDateInput?.focus()"></div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Reference (Optional)</label>
                        <input v-model="paymentForm.reference_number"
                               type="text"
                               class="w-full px-3 py-2 border rounded-lg"
                               :style="{
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary
                               }"
                               placeholder="e.g., Check #123" />
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="submit"
                                class="flex-1 px-4 py-2 rounded-md font-medium text-white"
                                style="background-color: var(--kotel-primary); color: #000000;">
                            Record Payment
                        </button>
                        <button type="button"
                                @click="showPaymentModal = false"
                                class="flex-1 px-4 py-2 rounded-md font-medium border"
                                :style="{ borderColor: themeColors.border, color: themeColors.textSecondary }">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Receive Modal -->
        <div v-if="showReceiveModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="rounded-lg p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-lg"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold"
                        :style="{ color: themeColors.textPrimary }">Receive Purchase Order Items</h3>
                    <button @click="showReceiveModal = false"
                            class="p-2 rounded-lg transition-colors"
                            :style="{
                                backgroundColor: themeColors.background,
                                color: themeColors.textSecondary
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.border"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div v-for="(item, index) in receiveForm.received_items" :key="item.item_id" 
                         class="rounded-lg p-4 border shadow-sm"
                         :style="{
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h4 class="font-semibold text-lg"
                                    :style="{ color: themeColors.textPrimary }">{{ item.product_name }}</h4>
                                <p class="text-sm"
                                   :style="{ color: themeColors.textSecondary }">Ordered: {{ item.ordered_quantity }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textSecondary }">Unit Cost</p>
                                <p class="text-lg font-semibold"
                                   :style="{ color: themeColors.primary }">{{ formatCurrency(item.unit_cost) }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <span class="mr-3 font-medium"
                                      :style="{ color: themeColors.textPrimary }">Received Quantity:</span>
                                <input 
                                    type="number" 
                                    v-model.number="item.received_quantity"
                                    min="0"
                                    :max="item.ordered_quantity"
                                    class="w-24 px-3 py-2 rounded-lg border text-sm font-medium"
                                    :style="{
                                        backgroundColor: themeColors.card,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderStyle: 'solid',
                                        borderWidth: '1px'
                                    }"
                                />
                            </label>
                            <span class="text-sm"
                                  :style="{ color: themeColors.textTertiary }">
                                Max: {{ item.ordered_quantity }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block">
                        <span class="font-medium mb-2 block"
                              :style="{ color: themeColors.textPrimary }">Notes:</span>
                        <textarea 
                            v-model="receiveForm.notes"
                            rows="2"
                            class="w-full px-3 py-2 rounded-lg border text-sm resize-none"
                            :style="{
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary,
                                borderStyle: 'solid',
                                borderWidth: '1px'
                            }"
                            placeholder="Optional notes about receiving..."
                        ></textarea>
                    </label>
                </div>

                <!-- Payment & Deal Settlement Section -->
                <div class="mt-6 space-y-4">

                    <!-- Record Payment Row -->
                    <div class="rounded-lg p-4 border"
                         :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background }">
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Pay Now (Optional)</label>
                                <input type="number" v-model.number="receiveForm.payment_amount"
                                       step="0.01" min="0"
                                       class="w-full px-3 py-2 border rounded-lg text-sm"
                                       :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }"
                                       placeholder="Amount" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Payment Method</label>
                                <select v-model="receiveForm.payment_method"
                                        class="w-full px-3 py-2 border rounded-lg text-sm"
                                        :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }">
                                    <option value="">None</option>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="credit_card">Credit Card</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Reference</label>
                                <input type="text" v-model="receiveForm.payment_reference"
                                       class="w-full px-3 py-2 border rounded-lg text-sm"
                                       :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }"
                                       placeholder="Ref / Cheque #" />
                            </div>
                        </div>
                    </div>

                    <!-- Close Deal Toggle -->
                    <div class="rounded-lg p-4 border"
                         :style="{ borderColor: receiveForm.close_deal ? themeColors.primary : themeColors.border, backgroundColor: receiveForm.close_deal ? 'rgba(8,145,171,0.05)' : themeColors.background }">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" v-model="receiveForm.close_deal" class="w-4 h-4 rounded" />
                            <div>
                                <span class="font-medium" :style="{ color: themeColors.textPrimary }">Close Deal with Partial Goods</span>
                                <p class="text-xs mt-0.5" :style="{ color: themeColors.textSecondary }">
                                    Accept this partial delivery as final and settle any balance between supplier and purchaser.
                                </p>
                            </div>
                        </label>

                        <!-- Balance Breakdown when Close Deal is checked -->
                        <div v-if="receiveForm.close_deal" class="mt-4 space-y-3">
                            <!-- Calculation Summary -->
                            <div class="rounded-lg p-3 space-y-2"
                                 :style="{ backgroundColor: themeColors.card }">
                                <div class="flex justify-between text-sm">
                                    <span :style="{ color: themeColors.textSecondary }">Value of goods being received now:</span>
                                    <span class="font-medium" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(currentReceiptValue) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span :style="{ color: themeColors.textSecondary }">Previously received value:</span>
                                    <span class="font-medium" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(previouslyReceivedValue) }}</span>
                                </div>
                                <div class="flex justify-between text-sm border-t pt-2" :style="{ borderColor: themeColors.border }">
                                    <span class="font-medium" :style="{ color: themeColors.textSecondary }">Total accepted value:</span>
                                    <span class="font-semibold" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(totalAcceptedValue) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span :style="{ color: themeColors.textSecondary }">
                                        Total paid{{ receiveForm.payment_amount > 0 ? ' (incl. pay now)' : '' }}:
                                    </span>
                                    <span class="font-medium" :style="{ color: themeColors.textPrimary }">
                                        {{ formatCurrency(purchaseOrder.paid_amount + (receiveForm.payment_amount || 0)) }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm border-t pt-2 font-bold" :style="{ borderColor: themeColors.border }">
                                    <span :style="{ color: themeColors.textPrimary }">Settlement Balance:</span>
                                    <span :style="{ color: settlementBalance >= 0 ? themeColors.success : themeColors.danger }">
                                        {{ formatCurrency(Math.abs(settlementBalance)) }}
                                        <span class="text-xs font-normal ml-1">
                                            ({{ settlementBalance > 0 ? 'Supplier owes you' : settlementBalance < 0 ? 'You owe supplier' : 'Settled' }})
                                        </span>
                                    </span>
                                </div>
                            </div>

                            <!-- Supplier Owes Purchaser -->
                            <div v-if="settlementBalance > 0" class="rounded-lg p-3 border"
                                 :style="{ borderColor: themeColors.success, backgroundColor: 'rgba(34,197,94,0.05)' }">
                                <p class="text-sm font-semibold mb-1" :style="{ color: themeColors.success }">
                                    ✅ Supplier owes you {{ formatCurrency(settlementBalance) }}
                                </p>
                                <p class="text-xs" :style="{ color: themeColors.textSecondary }">
                                    You overpaid relative to goods received. This will be recorded as a supplier credit note.
                                </p>
                                <div class="mt-2">
                                    <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Credit Reference / Notes</label>
                                    <input type="text" v-model="receiveForm.settlement_reference"
                                           class="w-full px-3 py-2 border rounded-lg text-sm"
                                           :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"
                                           placeholder="e.g. Credit note CN-001..." />
                                </div>
                            </div>

                            <!-- Purchaser Owes Supplier -->
                            <div v-if="settlementBalance < 0" class="rounded-lg p-3 border"
                                 :style="{ borderColor: themeColors.danger, backgroundColor: 'rgba(239,68,68,0.05)' }">
                                <p class="text-sm font-semibold mb-1" :style="{ color: themeColors.danger }">
                                    ⚠️ You owe supplier {{ formatCurrency(Math.abs(settlementBalance)) }}
                                </p>
                                <p class="text-xs mb-3" :style="{ color: themeColors.textSecondary }">
                                    You received goods worth more than you paid. Settle the balance now or it will remain outstanding.
                                </p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Settlement Amount</label>
                                        <input type="number" v-model.number="receiveForm.settlement_amount"
                                               step="0.01" min="0" :max="Math.abs(settlementBalance)"
                                               class="w-full px-3 py-2 border rounded-lg text-sm"
                                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"
                                               :placeholder="formatCurrency(Math.abs(settlementBalance))" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Payment Method</label>
                                        <select v-model="receiveForm.settlement_method"
                                                class="w-full px-3 py-2 border rounded-lg text-sm"
                                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                                            <option value="">Leave outstanding</option>
                                            <option value="cash">Cash</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="cheque">Cheque</option>
                                            <option value="credit_card">Credit Card</option>
                                        </select>
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Reference</label>
                                        <input type="text" v-model="receiveForm.settlement_reference"
                                               class="w-full px-3 py-2 border rounded-lg text-sm"
                                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"
                                               placeholder="e.g. Transfer ref, Cheque #..." />
                                    </div>
                                </div>
                            </div>

                            <!-- Already Settled -->
                            <div v-if="settlementBalance === 0" class="rounded-lg p-3 border"
                                 :style="{ borderColor: themeColors.success, backgroundColor: 'rgba(34,197,94,0.05)' }">
                                <p class="text-sm font-semibold" :style="{ color: themeColors.success }">
                                    ✅ Perfectly settled — paid amount matches received goods value.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button 
                        @click="showReceiveModal = false"
                        class="px-4 py-2 rounded-md transition-colors font-medium"
                        :style="{
                            backgroundColor: themeColors.background,
                            color: themeColors.textSecondary,
                            borderColor: themeColors.border,
                            borderStyle: 'solid',
                            borderWidth: '1px'
                        }">
                        Cancel
                    </button>
                    <button 
                        @click="submitReceiveOrder"
                        :disabled="isSubmittingReceive"
                        class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                        :style="{ backgroundColor: themeColors.success, opacity: isSubmittingReceive ? 0.7 : 1 }">
                        <CheckIcon class="h-4 w-4 mr-2" />
                        {{ isSubmittingReceive ? 'Processing...' : 'Confirm Receipt' }}
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { ArrowLeftIcon, PencilIcon, CheckIcon } from '@heroicons/vue/24/outline'
import { formatCurrency } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme.js'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    user: Object,
    navigation: Array,
    purchaseOrder: Object
})

const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    primary: `var(--kotel-primary)`,
    hover: `var(--kotel-primary-hover)`,
    success: `var(--kotel-success)`,
    danger: `var(--kotel-danger)`,
    warning: `var(--kotel-warning)`
}))

const canEdit = computed(() => {
    return props.purchaseOrder.status === 'pending'
})

const canReceive = computed(() => {
    return ['pending', 'confirmed', 'in_transit', 'pending_payment'].includes(props.purchaseOrder.status)
})

const formatDate = (dateString) => {
    if (!dateString) return null
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-blue-100 text-blue-800',
        partial: 'bg-purple-100 text-purple-800',
        in_transit: 'bg-indigo-100 text-indigo-800',
        pending_payment: 'bg-orange-100 text-orange-800',
        received: 'bg-green-100 text-green-800',
        closed: 'bg-gray-100 text-gray-700',
        cancelled: 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const getItemStatus = (ordered, received) => {
    if (received === 0) return 'Pending'
    if (received < ordered) return 'Partial'
    if (received >= ordered) return 'Received'
    return 'Unknown'
}

const getItemStatusClass = (ordered, received) => {
    if (received === 0) return 'bg-yellow-100 text-yellow-800'
    if (received < ordered) return 'bg-purple-100 text-purple-800'
    if (received >= ordered) return 'bg-green-100 text-green-800'
    return 'bg-gray-100 text-gray-800'
}

const editPurchaseOrder = () => {
    router.visit(route('manager.purchases.edit', props.purchaseOrder.id))
}

const receivePurchaseOrder = () => {
    showReceiveModal.value = true
}

const showReceiveModal = ref(false)
const showPaymentModal = ref(false)
const isSubmittingReceive = ref(false)
const receiveForm = ref({
    received_items: [],
    notes: '',
    payment_amount: null,
    payment_method: '',
    payment_reference: '',
    close_deal: false,
    settlement_amount: null,
    settlement_method: '',
    settlement_reference: ''
})

// Value of goods in the current receive session (from form inputs)
const currentReceiptValue = computed(() => {
    return receiveForm.value.received_items.reduce((sum, item) => {
        return sum + ((item.received_quantity || 0) * (item.unit_cost || 0))
    }, 0)
})

// Previously received value = from actual item records (quantity_received * unit_cost)
const previouslyReceivedValue = computed(() => {
    if (!props.purchaseOrder.items) return 0
    return props.purchaseOrder.items.reduce((sum, item) => {
        return sum + ((item.quantity_received || 0) * (item.unit_cost || 0))
    }, 0)
})

// Total goods value accepted when closing the deal (previous + this session)
const totalAcceptedValue = computed(() => {
    return previouslyReceivedValue.value + currentReceiptValue.value
})

// Settlement balance: +ve = supplier owes buyer (overpaid), -ve = buyer owes supplier (underpaid)
// Considers all payments made (including any pay-now amount in this session)
const settlementBalance = computed(() => {
    const payNow = receiveForm.value.payment_amount || 0
    const totalPaid = props.purchaseOrder.paid_amount + payNow
    return totalPaid - totalAcceptedValue.value
})

const paymentForm = ref({
    amount: '',
    payment_method: '',
    payment_date: new Date().toISOString().split('T')[0],
    reference_number: ''
})

const initializeReceiveForm = () => {
    receiveForm.value.received_items = props.purchaseOrder.items.map(item => ({
        item_id: item.id,
        product_id: item.product_id,
        product_name: item.product?.name || 'Unknown Product',
        ordered_quantity: item.quantity_ordered,
        received_quantity: item.quantity_received || 0,
        unit_cost: item.unit_cost
    }))
}

const submitReceiveOrder = () => {
    isSubmittingReceive.value = true
    router.post(route('manager.purchases.receive', props.purchaseOrder.id), {
        received_items: receiveForm.value.received_items,
        notes: receiveForm.value.notes,
        payment_amount: receiveForm.value.payment_amount || null,
        payment_method: receiveForm.value.payment_method || null,
        payment_reference: receiveForm.value.payment_reference || null,
        close_deal: receiveForm.value.close_deal,
        settlement_amount: receiveForm.value.settlement_amount || null,
        settlement_method: receiveForm.value.settlement_method || null,
        settlement_reference: receiveForm.value.settlement_reference || null,
        total_accepted_value: receiveForm.value.close_deal ? totalAcceptedValue.value : null,
        settlement_balance: receiveForm.value.close_deal ? settlementBalance.value : null,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showReceiveModal.value = false
            isSubmittingReceive.value = false
            receiveForm.value = {
                received_items: [],
                notes: '',
                payment_amount: null,
                payment_method: '',
                payment_reference: '',
                close_deal: false,
                settlement_amount: null,
                settlement_method: '',
                settlement_reference: ''
            }
        },
        onError: () => { isSubmittingReceive.value = false },
        onFinish: () => { isSubmittingReceive.value = false }
    })
}

const submitPayment = () => {
    if (!paymentForm.value.amount || paymentForm.value.amount <= 0) {
        alert('Please enter a valid amount')
        return
    }

    if (paymentForm.value.amount > props.purchaseOrder.remaining_amount) {
        alert('Payment amount cannot exceed remaining balance')
        return
    }

    router.post(route('manager.suppliers.payments.store', props.purchaseOrder.supplier_id), {
        purchase_order_id: props.purchaseOrder.id,
        payment_type: paymentForm.value.amount >= props.purchaseOrder.remaining_amount ? 'full' : 'partial',
        amount: paymentForm.value.amount,
        payment_method: paymentForm.value.payment_method,
        payment_date: paymentForm.value.payment_date,
        reference_number: paymentForm.value.reference_number
    }, {
        onSuccess: () => {
            showPaymentModal.value = false
            paymentForm.value = {
                amount: '',
                payment_method: '',
                payment_date: new Date().toISOString().split('T')[0],
                reference_number: ''
            }
        }
    })
}

// Initialize receive form when modal opens
watch(showReceiveModal, (newValue) => {
    if (newValue) {
        initializeReceiveForm()
    }
})
</script>

<style scoped>
.animate-spin {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
