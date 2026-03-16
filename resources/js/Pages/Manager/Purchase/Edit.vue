<template>
    <DashboardLayout :title="isEditing ? 'Edit Purchase Order' : 'View Purchase Order'" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button @click="router.visit(route('manager.purchases.index'))"
                            class="p-2 rounded-lg transition-colors"
                            :style="{ backgroundColor: themeColors.background, color: themeColors.textSecondary }">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">
                            {{ isEditing ? 'Edit Purchase Order' : 'Purchase Order Details' }}
                        </h1>
                        <p class="text-sm mt-1" :style="{ color: themeColors.textSecondary }">
                            PO Number: <span class="font-medium">{{ purchaseOrder?.po_number || 'N/A' }}</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
                          :class="getStatusClass(purchaseOrder?.status)"
                          :style="getStatusStyle(purchaseOrder?.status)">
                        {{ purchaseOrder?.status || 'Unknown' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Order Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Supplier Information -->
                <div class="shadow rounded-lg p-6"
                     :style="{
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border
                     }">
                    <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Supplier Information</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Supplier</label>
                            <select v-if="isEditing" v-model="form.supplier_id"
                                    class="w-full px-3 py-2 border rounded-lg"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary
                                    }">
                                <option value="">Select Supplier</option>
                                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                    {{ supplier.name }}
                                </option>
                            </select>
                            <p v-else class="font-medium" :style="{ color: themeColors.textPrimary }">{{ purchaseOrder?.supplier?.name || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Order Date</label>
                            <p class="font-medium" :style="{ color: themeColors.textPrimary }">
                                {{ purchaseOrder?.order_date ? new Date(purchaseOrder.order_date).toLocaleDateString() : 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Expected Delivery</label>
                            <input v-if="isEditing" 
                                   type="date" 
                                   v-model="form.expected_delivery_date"
                                   class="w-full px-3 py-2 border rounded-lg cursor-pointer"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: '#ffffff'
                                   }"
                                   @click="$event.target.showPicker()" />
                            <p v-else class="font-medium" :style="{ color: themeColors.textPrimary }">
                                {{ purchaseOrder?.expected_delivery_date ? new Date(purchaseOrder.expected_delivery_date).toLocaleDateString() : 'Not set' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Reference</label>
                            <input v-if="isEditing" type="text" v-model="form.reference"
                                   class="w-full px-3 py-2 border rounded-lg"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }" />
                            <p v-else class="font-medium" :style="{ color: themeColors.textPrimary }">{{ purchaseOrder?.reference || 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Products -->
                <div class="shadow rounded-lg p-6"
                     :style="{
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border
                     }">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Order Items</h3>
                        <button v-if="isEditing" @click="addProduct"
                                class="px-3 py-1 text-sm rounded-lg transition-colors text-white"
                                :style="{ backgroundColor: themeColors.primary }">
                            + Add Product
                        </button>
                    </div>

                    <!-- Products Table -->
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
                                    <th v-if="isEditing" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                        :style="{ color: themeColors.textTertiary }">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in form.items" :key="index"
                                    class="border-b"
                                    :style="{ borderColor: themeColors.border }">
                                    <td class="px-4 py-3">
                                        <select v-if="isEditing" v-model="item.product_id"
                                                class="w-full px-3 py-2 border rounded-lg"
                                                :style="{
                                                    backgroundColor: themeColors.background,
                                                    borderColor: themeColors.border,
                                                    color: themeColors.textPrimary
                                                }">
                                            <option value="">Select Product</option>
                                            <option v-for="product in products" :key="product.id" :value="product.id">
                                                {{ product.name }} ({{ product.sku }})
                                            </option>
                                        </select>
                                        <div v-else>
                                            <p class="font-medium" :style="{ color: themeColors.textPrimary }">{{ item.product_name }}</p>
                                            <p class="text-xs" :style="{ color: themeColors.textSecondary }">{{ item.product_sku }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input v-if="isEditing" type="number" v-model.number="item.quantity" min="1"
                                               class="w-24 px-3 py-2 border rounded-lg"
                                               :style="{
                                                   backgroundColor: themeColors.background,
                                                   borderColor: themeColors.border,
                                                   color: themeColors.textPrimary
                                               }" />
                                        <p v-else class="font-medium" :style="{ color: themeColors.textPrimary }">{{ item.quantity }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input v-if="isEditing" type="number" v-model.number="item.unit_cost" min="0" step="0.01"
                                               class="w-28 px-3 py-2 border rounded-lg"
                                               :style="{
                                                   backgroundColor: themeColors.background,
                                                   borderColor: themeColors.border,
                                                   color: themeColors.textPrimary
                                               }" />
                                        <p v-else class="font-medium" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(item.unit_cost) }}</p>
                                    </td>
                                    <td class="px-4 py-3 font-medium" :style="{ color: themeColors.textPrimary }">
                                        {{ formatCurrency(item.quantity * item.unit_cost) }}
                                    </td>
                                    <td v-if="isEditing" class="px-4 py-3">
                                        <button @click="removeProduct(index)"
                                                class="text-red-500 hover:text-red-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Notes -->
                <div class="shadow rounded-lg p-6"
                     :style="{
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border
                     }">
                    <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Notes</h3>
                    <textarea v-if="isEditing" v-model="form.notes" rows="3"
                              class="w-full px-3 py-2 border rounded-lg"
                              :style="{
                                  backgroundColor: themeColors.background,
                                  borderColor: themeColors.border,
                                  color: themeColors.textPrimary
                              }"></textarea>
                    <p v-else class="text-sm whitespace-pre-wrap" :style="{ color: themeColors.textSecondary }">
                        {{ purchaseOrder?.notes || 'No notes' }}
                    </p>
                </div>
            </div>

            <!-- Right Column - Summary -->
            <div class="space-y-6">
                <!-- Order Summary -->
                <div class="shadow rounded-lg p-6"
                     :style="{
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border
                     }">
                    <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Order Summary</h3>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span :style="{ color: themeColors.textSecondary }">Subtotal</span>
                            <span :style="{ color: themeColors.textPrimary }">{{ formatCurrency(subtotal) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span :style="{ color: themeColors.textSecondary }">Tax (%)</span>
                            <input v-if="isEditing" type="number" v-model.number="form.tax_rate" min="0" max="100" step="0.1"
                                   class="w-20 px-2 py-1 text-right border rounded-lg text-sm"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }" />
                            <span v-else :style="{ color: themeColors.textPrimary }">{{ form.tax_rate }}%</span>
                        </div>
                        <div class="flex justify-between">
                            <span :style="{ color: themeColors.textSecondary }">Tax Amount</span>
                            <span :style="{ color: themeColors.textPrimary }">{{ formatCurrency(taxAmount) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span :style="{ color: themeColors.textSecondary }">Shipping</span>
                            <input v-if="isEditing" type="number" v-model.number="form.shipping_cost" min="0" step="0.01"
                                   class="w-28 px-2 py-1 text-right border rounded-lg text-sm"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }" />
                            <span v-else :style="{ color: themeColors.textPrimary }">{{ formatCurrency(form.shipping_cost) }}</span>
                        </div>
                        <div class="border-t pt-3 mt-3">
                            <div class="flex justify-between text-lg font-bold">
                                <span :style="{ color: themeColors.textPrimary }">Total</span>
                                <span :style="{ color: themeColors.textPrimary }">{{ formatCurrency(total) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="shadow rounded-lg p-6"
                     :style="{
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border
                     }">
                    <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Payment Summary</h3>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span :style="{ color: themeColors.textSecondary }">Total Amount</span>
                            <span class="font-medium" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(total) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span :style="{ color: themeColors.textSecondary }">Paid Amount</span>
                            <span class="font-medium" :style="{ color: themeColors.success }">{{ formatCurrency(purchaseOrder?.paid_amount || 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span :style="{ color: themeColors.textSecondary }">Remaining</span>
                            <span class="font-medium" :style="{ color: remainingBalance > 0 ? themeColors.danger : themeColors.success }">
                                {{ formatCurrency(remainingBalance) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div v-if="isEditing" class="shadow rounded-lg p-6"
                     :style="{
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border
                     }">
                    <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Actions</h3>
                    <div class="space-y-3">
                        <button @click="savePurchaseOrder"
                                class="w-full py-2 px-4 rounded-lg transition-colors font-medium text-white"
                                :style="{ backgroundColor: themeColors.primary }"
                                :disabled="saving">
                            {{ saving ? 'Saving...' : 'Save Changes' }}
                        </button>
                        <button @click="cancelEdit"
                                class="w-full py-2 px-4 rounded-lg transition-colors border font-medium"
                                :style="{
                                    borderColor: themeColors.border,
                                    color: themeColors.textSecondary,
                                    backgroundColor: themeColors.background
                                }">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { router } from '@inertiajs/vue3'
import { formatCurrency } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme.js'

const { loadTheme } = useTheme()

// Initialize theme
const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    primary: `var(--kotel-primary)`,
    hover: `var(--kotel-hover)`,
    secondary: `var(--kotel-secondary)`,
    success: `var(--kotel-success)`,
    danger: `var(--kotel-danger)`,
    warning: `var(--kotel-warning)`
}))

// Load theme on mount
loadTheme()

const props = defineProps({
    user: Object,
    navigation: Array,
    purchaseOrder: Object,
    suppliers: Array,
    products: Array
})

const isEditing = ref(true)
const saving = ref(false)

// Helper function to format date for HTML5 date input (YYYY-MM-DD)
const formatDateForInput = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
}

// Initialize form with purchase order data
const form = ref({
    supplier_id: props.purchaseOrder?.supplier_id || '',
    expected_delivery_date: formatDateForInput(props.purchaseOrder?.expected_delivery_date),
    reference: props.purchaseOrder?.reference || '',
    tax_rate: Number(props.purchaseOrder?.tax_rate) || 0,
    shipping_cost: Number(props.purchaseOrder?.shipping_cost) || 0,
    notes: props.purchaseOrder?.notes || '',
    items: props.purchaseOrder?.items?.map(item => {
        // Debug: log the item structure
        console.log('Purchase order item:', item)
        console.log('Product data:', item.product)
        
        return {
            product_id: item.product_id,
            product_name: item.product?.name || item.product_name || item.name || 'Unknown Product',
            product_sku: item.product?.code || item.product_sku || item.sku || item.code || '',
            quantity: Number(item.quantity_ordered) || 0,
            unit_cost: Number(item.unit_cost) || Number(item.cost_price) || 0
        }
    }) || []
})

// Debug: log the entire form
console.log('Form data:', form.value)

// Computed values
const subtotal = computed(() => {
    const result = form.value.items.reduce((sum, item) => {
        const quantity = Number(item.quantity) || 0
        const unitCost = Number(item.unit_cost) || 0
        console.log(`Item: ${item.product_name}, Quantity: ${quantity}, Unit Cost: ${unitCost}`)
        return sum + (quantity * unitCost)
    }, 0)
    console.log('Subtotal result:', result)
    return result
})

const taxAmount = computed(() => {
    const subtotalValue = subtotal.value || 0
    const taxRate = Number(form.value.tax_rate) || 0
    const result = subtotalValue * (taxRate / 100)
    console.log('Tax calculation:', { subtotalValue, taxRate, result })
    return result
})

const total = computed(() => {
    const subtotalValue = subtotal.value || 0
    const taxAmountValue = taxAmount.value || 0
    const shippingCost = Number(form.value.shipping_cost) || 0
    const result = subtotalValue + taxAmountValue + shippingCost
    console.log('Total calculation:', { subtotalValue, taxAmountValue, shippingCost, result })
    return result
})

const remainingBalance = computed(() => {
    const totalValue = total.value || 0
    const paidAmount = Number(props.purchaseOrder?.paid_amount) || 0
    const result = totalValue - paidAmount
    console.log('Remaining balance calculation:', { totalValue, paidAmount, result })
    return result
})

// Methods
const addProduct = () => {
    form.value.items.push({
        product_id: '',
        product_name: '',
        product_sku: '',
        quantity: 1,
        unit_cost: 0
    })
}

const removeProduct = (index) => {
    form.value.items.splice(index, 1)
}

const getStatusClass = (status) => {
    const statusClasses = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'in_transit': 'bg-blue-100 text-blue-800',
        'received': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    }
    return statusClasses[status] || 'bg-gray-100 text-gray-800'
}

const getStatusStyle = (status) => {
    const statusStyles = {
        'pending': { backgroundColor: 'rgba(250, 204, 21, 0.1)', color: '#ca8a04' },
        'in_transit': { backgroundColor: 'rgba(59, 130, 246, 0.1)', color: '#1d4ed8' },
        'received': { backgroundColor: 'rgba(34, 197, 94, 0.1)', color: '#16a34a' },
        'cancelled': { backgroundColor: 'rgba(239, 68, 68, 0.1)', color: '#dc2626' }
    }
    return statusStyles[status] || { backgroundColor: 'rgba(107, 114, 128, 0.1)', color: '#6b7280' }
}

const savePurchaseOrder = () => {
    saving.value = true
    router.put(route('manager.purchases.update', props.purchaseOrder.id), {
        ...form.value,
        items: form.value.items.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
            unit_cost: item.unit_cost
        }))
    }, {
        onFinish: () => {
            saving.value = false
        }
    })
}

const cancelEdit = () => {
    router.visit(route('manager.purchases.index'))
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

/* Custom animations and transitions */
.transition-colors {
    transition-property: background-color, border-color, color;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Hover effects for interactive elements */
button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

button:active {
    transform: translateY(0);
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}
</style>
