<template>
    <DashboardLayout title="Create Purchase Order" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Create Purchase Order</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Create a new purchase order for suppliers.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="$inertia.visit(route('pos.purchases.index'))"
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
                </div>
            </div>
        </div>

        <!-- Create Purchase Order Form -->
        <div class="rounded-lg overflow-hidden shadow"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <form @submit.prevent="submitForm">
                <!-- Supplier Selection -->
                <div class="px-6 py-4 border-b"
                     :style="{
                         borderColor: themeColors.border,
                         borderBottomWidth: '1px'
                     }">
                    <h3 class="text-lg font-medium"
                        :style="{ color: themeColors.textPrimary }">Supplier Information</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Supplier *</label>
                            <select v-model="form.supplier_id" required
                                    class="w-full px-4 py-2 rounded-lg border transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="">Select a supplier</option>
                                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                    {{ supplier.name }} - {{ supplier.contact_person || 'No contact' }}
                                </option>
                            </select>
                            <p v-if="errors.supplier_id" class="mt-1 text-sm"
                               :style="{ color: themeColors.danger }">{{ errors.supplier_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Expected Delivery Date</label>
                            <DatePicker v-model="form.expected_date" />
                        </div>

                        <!-- Purchase Type -->
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Purchase Type</label>
                            <select v-model="form.purchase_type"
                                    class="w-full px-4 py-2 rounded-lg border transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="resale">Resale / Inventory</option>
                                <option value="expense">Expense (Non-resale)</option>
                            </select>
                        </div>

                        <!-- Expense Category (shown when expense type) -->
                        <div v-if="form.purchase_type === 'expense'">
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Expense Category</label>
                            <input type="text" v-model="form.expense_category"
                                   class="w-full px-4 py-2 rounded-lg border transition-colors"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="e.g. Kitchen Supplies, Laundry, Maintenance..." />
                        </div>

                        <!-- Destination Location -->
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">
                                Deliver To
                                <span class="ml-1 text-xs font-normal" :style="{ color: themeColors.textTertiary }">(optional — defaults to main warehouse)</span>
                            </label>
                            <select v-model="form.location_id"
                                    class="w-full px-4 py-2 rounded-lg border transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="">Main Warehouse (default)</option>
                                <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                                    {{ loc.name }}{{ loc.type ? ' — ' + loc.type : '' }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Payment Status</label>
                            <select v-model="form.payment_status"
                                    class="w-full px-4 py-2 rounded-lg border transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="unpaid">Unpaid</option>
                                <option value="partial">Partial Payment</option>
                                <option value="paid">Full Payment</option>
                            </select>
                        </div>
                        <div v-if="form.payment_status === 'partial' || form.payment_status === 'paid'">
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Payment Method</label>
                            <select v-model="form.payment_method"
                                    class="w-full px-4 py-2 rounded-lg border transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="credit_card">Credit Card</option>
                            </select>
                        </div>
                        <div v-if="form.payment_status === 'partial'">
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Amount Paid</label>
                            <input type="number" v-model.number="form.amount_paid" min="0" :max="totalAmount" step="0.01"
                                   class="w-full px-4 py-2 rounded-lg border transition-colors"
                                   :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                   }" />
                            <p class="text-sm mt-1" :style="{ color: themeColors.textSecondary }">
                                Remaining: <span class="font-medium" :style="{ color: themeColors.danger }">{{ formatCurrency(remainingBalance) }}</span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Tax Percentage (%)</label>
                            <input type="number" v-model.number="form.tax_percentage" min="0" max="100" step="0.01"
                                   class="w-full px-4 py-2 rounded-lg border transition-colors"
                                   :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                   }" />
                        </div>
                    </div>
                </div>

                <!-- Receipt Section for Partial/Full Payment -->
                <div v-if="form.payment_status === 'partial' || form.payment_status === 'paid'" class="px-6 py-4 border-b"
                     :style="{
                         borderColor: themeColors.border,
                         borderBottomWidth: '1px'
                     }">
                    <h3 class="text-lg font-medium flex items-center"
                        :style="{ color: themeColors.textPrimary }">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Payment Receipt
                    </h3>
                </div>
                <div v-if="form.payment_status === 'partial' || form.payment_status === 'paid'" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Scanned Receipt</label>
                            <div class="space-y-3">
                                <!-- File Upload Area -->
                                <div v-if="!receiptFile" 
                                     class="border-2 border-dashed rounded-lg p-6 text-center transition-colors cursor-pointer"
                                     :style="{
                                         borderColor: themeColors.border,
                                         backgroundColor: themeColors.background
                                     }"
                                     @click="$refs.receiptFileInput.click()"
                                     @dragover.prevent="dragOver = true"
                                     @dragleave.prevent="dragOver = false"
                                     @drop.prevent="handleReceiptDrop">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 mb-3" :style="{ color: themeColors.textTertiary }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textPrimary }">
                                            Upload scanned receipt
                                        </p>
                                        <p class="text-xs" :style="{ color: themeColors.textTertiary }">
                                            Drag and drop or click to browse
                                        </p>
                                        <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">
                                            PNG, JPG, PDF up to 10MB
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- File Preview -->
                                <div v-else class="relative">
                                    <div class="rounded-lg overflow-hidden border"
                                         :style="{ borderColor: themeColors.border }">
                                        <!-- Image Preview -->
                                        <img v-if="receiptFile.type.startsWith('image/')" 
                                             :src="receiptFile.preview" 
                                             alt="Receipt preview"
                                             class="w-full h-48 object-cover" />
                                        <!-- PDF Preview -->
                                        <div v-else-if="receiptFile.type === 'application/pdf'"
                                             class="w-full h-48 flex items-center justify-center"
                                             :style="{ backgroundColor: themeColors.background }">
                                            <div class="text-center">
                                                <svg class="w-16 h-16 mx-auto mb-2" :style="{ color: themeColors.danger }" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 8v4a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                                </svg>
                                                <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">PDF Document</p>
                                                <p class="text-xs" :style="{ color: themeColors.textTertiary }">{{ receiptFile.name }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- File Actions -->
                                    <div class="absolute top-2 right-2 flex gap-2">
                                        <button @click="viewReceiptFile" 
                                                class="p-2 rounded-full transition-colors"
                                                :style="{ backgroundColor: themeColors.card, color: themeColors.textPrimary }"
                                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                                @mouseleave="$event.target.style.backgroundColor = themeColors.card">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                        <button @click="removeReceiptFile" 
                                                class="p-2 rounded-full transition-colors"
                                                :style="{ backgroundColor: themeColors.danger, color: 'white' }">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <!-- File Info -->
                                    <div class="mt-2 p-2 rounded text-xs"
                                         :style="{ backgroundColor: themeColors.background, color: themeColors.textSecondary }">
                                        <p>{{ receiptFile.name }}</p>
                                        <p>{{ formatFileSize(receiptFile.size) }}</p>
                                    </div>
                                </div>
                                
                                <!-- Hidden File Input -->
                                <input ref="receiptFileInput"
                                       type="file"
                                       accept="image/*,.pdf"
                                       class="hidden"
                                       @change="handleReceiptFileSelect" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">
                                {{ form.payment_status === 'paid' ? 'Amount Paid (Full)' : 'Amount Paid' }}
                            </label>
                            <div class="px-4 py-2 rounded-lg font-bold"
                                 :style="{
                                     backgroundColor: themeColors.background,
                                     color: themeColors.success
                                 }">
                                {{ formatCurrency(form.payment_status === 'paid' ? totalAmount : (form.amount_paid || 0)) }}
                            </div>
                        </div>
                        <div v-if="form.payment_status === 'partial'" class="md:col-span-2">
                            <div class="p-4 rounded-lg"
                                 :style="{
                                     backgroundColor: themeColors.background,
                                     borderColor: themeColors.border,
                                     borderWidth: '1px',
                                     borderStyle: 'solid'
                                 }">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium" :style="{ color: themeColors.textPrimary }">Payment Summary:</span>
                                    <span class="text-sm" :style="{ color: themeColors.textSecondary }">
                                        {{ formatCurrency(form.amount_paid || 0) }} paid of {{ formatCurrency(totalAmount) }} total
                                    </span>
                                </div>
                                <div class="mt-2 pt-2 border-t" :style="{ borderColor: themeColors.border }">
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium" :style="{ color: themeColors.textSecondary }">Remaining Balance:</span>
                                        <span class="font-bold text-lg" :style="{ color: themeColors.danger }">{{ formatCurrency(remainingBalance) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="form.payment_status === 'paid'" class="md:col-span-2">
                            <div class="p-4 rounded-lg"
                                 :style="{
                                     backgroundColor: themeColors.success + '20',
                                     borderColor: themeColors.success,
                                     borderWidth: '1px',
                                     borderStyle: 'solid'
                                 }">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" :style="{ color: themeColors.success }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="font-medium" :style="{ color: themeColors.success }">Full Payment Completed</span>
                                </div>
                                <p class="mt-1 text-sm" :style="{ color: themeColors.textSecondary }">
                                    {{ formatCurrency(totalAmount) }} has been paid in full for this purchase order.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="px-6 py-4 border-b"
                     :style="{
                         borderColor: themeColors.border,
                         borderBottomWidth: '1px'
                     }">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium"
                            :style="{ color: themeColors.textPrimary }">Order Items</h3>
                        <button type="button" @click="addItem"
                                class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                                :style="{
                                    backgroundColor: themeColors.primary,
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                            <PlusIcon class="h-4 w-4 mr-2" />
                            Add Item
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <!-- Items Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr :style="{ backgroundColor: themeColors.background }">
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                        :style="{ color: themeColors.textTertiary }">Product</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                        :style="{ color: themeColors.textTertiary }">Quantity</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                        :style="{ color: themeColors.textTertiary }">Unit Cost</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                        :style="{ color: themeColors.textTertiary }">Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                        :style="{ color: themeColors.textTertiary }">Current Stock</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                        :style="{ color: themeColors.textTertiary }">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in form.items" :key="index"
                                    :style="{
                                        borderBottomStyle: 'solid',
                                        borderBottomWidth: '1px',
                                        borderColor: themeColors.border
                                    }">
                                    <td class="px-4 py-3">
                                        <select v-model="item.product_id" @change="updateProductBadge(index)"
                                                class="w-full px-3 py-2 rounded-lg border text-sm"
                                                :style="{
                                                    backgroundColor: themeColors.background,
                                                    borderColor: themeColors.border,
                                                    color: themeColors.textPrimary,
                                                    borderWidth: '1px',
                                                    borderStyle: 'solid'
                                                }">
                                            <option value="">Select product</option>
                                            <option v-for="product in products" :key="product.id" :value="product.id">
                                                {{ product.name }} ({{ product.code }})
                                            </option>
                                        </select>
                                        <!-- Product Badge with Cost Price -->
                                        <div v-if="item.product_id && getProductBadge(index)" class="mt-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium"
                                                  :style="{
                                                      backgroundColor: themeColors.primary + '20',
                                                      color: themeColors.primary
                                                  }">
                                                Cost Badge: {{ formatCurrency(getProductBadge(index)) }}
                                                <span class="ml-1 text-xs opacity-75">@ creation</span>
                                            </span>
                                        </div>
                                        <p v-if="errors[`items.${index}.product_id`]" class="mt-1 text-xs"
                                           :style="{ color: themeColors.danger }">{{ errors[`items.${index}.product_id`] }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" v-model.number="item.quantity" min="1" required
                                               class="w-24 px-3 py-2 rounded-lg border text-sm"
                                               :style="{
                                                    backgroundColor: themeColors.background,
                                                    borderColor: themeColors.border,
                                                    color: themeColors.textPrimary,
                                                    borderWidth: '1px',
                                                    borderStyle: 'solid'
                                               }" />
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" v-model.number="item.unit_cost" min="0" step="0.01" required
                                               class="w-28 px-3 py-2 rounded-lg border text-sm"
                                               :style="{
                                                    backgroundColor: themeColors.background,
                                                    borderColor: themeColors.border,
                                                    color: themeColors.textPrimary,
                                                    borderWidth: '1px',
                                                    borderStyle: 'solid'
                                               }" />
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-medium"
                                              :style="{ color: themeColors.textPrimary }">
                                            {{ formatCurrency(item.quantity * item.unit_cost) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm"
                                              :style="{ color: themeColors.textSecondary }">
                                            {{ getProductStock(item.product_id) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <button type="button" @click="removeItem(index)"
                                                class="text-red-600 hover:text-red-800 transition-colors"
                                                :style="{ color: themeColors.danger }">
                                            <TrashIcon class="h-5 w-5" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right font-medium"
                                        :style="{ color: themeColors.textPrimary }">Subtotal:</td>
                                    <td class="px-4 py-3 font-bold"
                                        :style="{ color: themeColors.textPrimary }">{{ formatCurrency(subtotal) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right font-medium"
                                        :style="{ color: themeColors.textPrimary }">Tax ({{ form.tax_percentage }}%):</td>
                                    <td class="px-4 py-3 font-medium"
                                        :style="{ color: themeColors.textSecondary }">{{ formatCurrency(taxAmount) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right font-bold text-lg"
                                        :style="{ color: themeColors.textPrimary }">Total:</td>
                                    <td class="px-4 py-3 font-bold text-lg"
                                        :style="{ color: themeColors.primary }">{{ formatCurrency(totalAmount) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <p v-if="errors.items" class="mt-2 text-sm"
                       :style="{ color: themeColors.danger }">{{ errors.items }}</p>
                </div>

                <!-- Additional Information -->
                <div class="px-6 py-4 border-t"
                     :style="{
                         borderColor: themeColors.border,
                         borderTopWidth: '1px'
                     }">
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Additional Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Delivery Time (Days)</label>
                            <input type="number" v-model.number="form.delivery_time_days" min="0"
                                   class="w-full px-4 py-2 rounded-lg border"
                                   :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                   }" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Purchase Conditions</label>
                            <input type="text" v-model="form.purchase_conditions"
                                   placeholder="e.g., Net 30, COD"
                                   class="w-full px-4 py-2 rounded-lg border"
                                   :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                   }" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Notes</label>
                            <textarea v-model="form.notes" rows="3"
                                     class="w-full px-4 py-2 rounded-lg border"
                                     :style="{
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px',
                                          borderStyle: 'solid'
                                     }"
                                     placeholder="Any additional notes or special instructions..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 border-t flex items-center justify-end gap-3"
                     :style="{
                         borderColor: themeColors.border,
                         borderTopWidth: '1px'
                     }">
                    <button type="button" @click="$inertia.visit(route('pos.purchases.index'))"
                            class="px-6 py-2 rounded-lg transition-colors font-medium"
                            :style="{
                                borderColor: themeColors.border,
                                color: themeColors.textSecondary,
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }">
                        Cancel
                    </button>
                    <button type="submit" :disabled="isSubmitting"
                            class="px-6 py-2 rounded-lg transition-colors font-medium text-white flex items-center"
                            :style="{
                                backgroundColor: themeColors.primary,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <ArrowPathIcon v-if="isSubmitting" class="h-4 w-4 mr-2 animate-spin" />
                        {{ isSubmitting ? 'Creating...' : 'Create Purchase Order' }}
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { ArrowLeftIcon, PlusIcon, TrashIcon, ArrowPathIcon } from '@heroicons/vue/24/outline'
import DatePicker from '@/Components/DatePicker.vue'
import { formatCurrency } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme.js'

const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiery)`,
    primary: `var(--kotel-primary)`,
    secondary: `var(--kotel-secondary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
    hover: `var(--kotel-hover)`
}))

const props = defineProps({
    user: Object,
    navigation: Array,
    suppliers: Array,
    products: Array,
    locations: { type: Array, default: () => [] }
})

const isSubmitting = ref(false)
const errors = reactive({})

const form = useForm({
    supplier_id: '',
    expected_date: '',
    payment_status: 'unpaid',
    amount_paid: 0,
    payment_method: 'cash',
    purchase_type: 'resale',
    expense_category: '',
    location_id: '',
    delivery_time_days: null,
    purchase_conditions: '',
    notes: '',
    tax_percentage: 0,
    receipt_number: '',
    receipt_file: null,
    items: [
        { product_id: '', quantity: 1, unit_cost: 0, product_cost_price: 0 }
    ]
})

// Receipt file handling
const receiptFile = ref(null)
const dragOver = ref(false)

const handleReceiptFileSelect = (event) => {
    const file = event.target.files[0]
    if (file) {
        processReceiptFile(file)
    }
}

const handleReceiptDrop = (event) => {
    event.preventDefault()
    dragOver.value = false
    const file = event.dataTransfer.files[0]
    if (file) {
        processReceiptFile(file)
    }
}

const processReceiptFile = (file) => {
    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf']
    if (!allowedTypes.includes(file.type)) {
        // Show error - you might want to add a notification system
        alert('Please upload a valid image (JPG, PNG) or PDF file')
        return
    }
    
    // Validate file size (10MB max)
    const maxSize = 10 * 1024 * 1024 // 10MB in bytes
    if (file.size > maxSize) {
        alert('File size must be less than 10MB')
        return
    }
    
    // Create preview for images
    if (file.type.startsWith('image/')) {
        const reader = new FileReader()
        reader.onload = (e) => {
            receiptFile.value = {
                file: file,
                name: file.name,
                size: file.size,
                type: file.type,
                preview: e.target.result
            }
            // Update form data
            form.receipt_file = file
            form.receipt_number = file.name // Use filename as reference
        }
        reader.readAsDataURL(file)
    } else {
        // For PDFs, just store file info
        receiptFile.value = {
            file: file,
            name: file.name,
            size: file.size,
            type: file.type,
            preview: null
        }
        // Update form data
        form.receipt_file = file
        form.receipt_number = file.name // Use filename as reference
    }
}

const removeReceiptFile = () => {
    receiptFile.value = null
    form.receipt_file = null
    form.receipt_number = ''
    // Reset file input
    const fileInput = document.querySelector('input[type="file"][accept="image/*,.pdf"]')
    if (fileInput) {
        fileInput.value = ''
    }
}

const viewReceiptFile = () => {
    if (!receiptFile.value) return
    
    if (receiptFile.value.type.startsWith('image/')) {
        // Open image in new tab
        window.open(receiptFile.value.preview, '_blank')
    } else if (receiptFile.value.type === 'application/pdf') {
        // For PDFs, create object URL and open
        const fileURL = URL.createObjectURL(receiptFile.value.file)
        window.open(fileURL, '_blank')
    }
}

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const remainingBalance = computed(() => {
    if (form.payment_status === 'paid') {
        return 0
    }
    return Math.max(0, totalAmount.value - (form.amount_paid || 0))
})

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => {
        return sum + ((item.quantity || 0) * (item.unit_cost || 0))
    }, 0)
})

const taxAmount = computed(() => {
    return subtotal.value * (form.tax_percentage / 100)
})

const totalAmount = computed(() => {
    return subtotal.value + taxAmount.value
})

const getProductStock = (productId) => {
    if (!productId) return '-'
    const product = props.products.find(p => p.id === productId)
    return product ? product.stock_quantity : '-'
}

const getProductBadge = (index) => {
    return form.items[index]?.product_cost_price || null
}

const updateProductBadge = (index) => {
    const productId = form.items[index]?.product_id
    if (productId) {
        const product = props.products.find(p => p.id === productId)
        if (product) {
            // Create a new array to trigger reactivity
            const newItems = [...form.items]
            newItems[index] = {
                ...newItems[index],
                product_cost_price: product.cost_price || 0,
                unit_cost: product.cost_price || 0
            }
            form.items = newItems
        }
    }
}

const addItem = () => {
    console.log('Adding item - current items:', form.items.length)
    // Create a new array to trigger reactivity
    const newItems = [...form.items, { product_id: '', quantity: 1, unit_cost: 0, product_cost_price: 0 }]
    form.items = newItems
    console.log('Item added - new items count:', form.items.length)
}

const removeItem = (index) => {
    if (form.items.length > 1) {
        // Create a new array without the item at the specified index
        const newItems = form.items.filter((_, itemIndex) => itemIndex !== index)
        form.items = newItems
    }
}

const submitForm = () => {
    // Clear errors
    Object.keys(errors).forEach(key => delete errors[key])

    // Validate
    if (!form.supplier_id) {
        errors.supplier_id = 'Please select a supplier'
        return
    }

    if (form.items.length === 0 || form.items.every(item => !item.product_id)) {
        errors.items = 'Please add at least one item'
        return
    }

    form.items.forEach((item, index) => {
        if (!item.product_id) {
            errors[`items.${index}.product_id`] = 'Please select a product'
        }
    })

    if (Object.keys(errors).length > 0) {
        return
    }

    isSubmitting.value = true

    // Create FormData for file upload
    const formData = new FormData()
    
    // Add all form fields
    formData.append('supplier_id', form.supplier_id)
    formData.append('expected_date', form.expected_date)
    formData.append('payment_status', form.payment_status)
    formData.append('amount_paid', form.amount_paid)
    formData.append('payment_method', form.payment_method)
    formData.append('purchase_type', form.purchase_type)
    formData.append('expense_category', form.expense_category || '')
    formData.append('location_id', form.location_id || '')
    formData.append('delivery_time_days', form.delivery_time_days || '')
    formData.append('purchase_conditions', form.purchase_conditions)
    formData.append('notes', form.notes)
    formData.append('tax_percentage', form.tax_percentage)
    formData.append('receipt_number', form.receipt_number)
    
    // Add receipt file if present
    if (form.receipt_file) {
        formData.append('receipt_file', form.receipt_file)
    }
    
    // Add items
    form.items.forEach((item, index) => {
        formData.append(`items[${index}][product_id]`, item.product_id)
        formData.append(`items[${index}][quantity]`, item.quantity)
        formData.append(`items[${index}][unit_cost]`, item.unit_cost)
        formData.append(`items[${index}][product_cost_price]`, item.product_cost_price)
    })

    // Submit using router.post with FormData
    router.post(route('pos.purchases.store'), formData, {
        onSuccess: () => {
            isSubmitting.value = false
        },
        onError: (err) => {
            isSubmitting.value = false
            Object.assign(errors, err)
        },
        onProgress: (progress) => {
            // Optional: Handle upload progress
            console.log('Upload progress:', progress)
        }
    })
}
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
