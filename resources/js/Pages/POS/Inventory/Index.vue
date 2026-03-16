<template>
    <DashboardLayout title="Inventory Management" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Inventory Management</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Product inventory, stock levels, and value tracking.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="showAdjustStock = true"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{
                                backgroundColor: themeColors.primary,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <AdjustmentsHorizontalIcon class="h-4 w-4 mr-2" />
                        Adjust Stock
                    </button>
                    <button @click="exportInventory"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{
                                backgroundColor: '#8b5cf6',
                            }"
                            @mouseenter="$event.target.style.backgroundColor = '#7c3aed'"
                            @mouseleave="$event.target.style.backgroundColor = '#8b5cf6'">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                        Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Inventory Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                      backgroundColor: themeColors.card,
                      borderColor: themeColors.border,
                      borderStyle: 'solid',
                      borderWidth: '1px'
                  }">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg"
                         :style="{ backgroundColor: themeColors.primary + '20' }">
                        <CubeIcon class="h-6 w-6"
                                  :style="{ color: themeColors.primary }" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Total Products</p>
                        <p class="text-2xl font-bold mt-1"
                           :style="{ color: themeColors.textPrimary }">{{ products.length }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                      backgroundColor: themeColors.card,
                      borderColor: themeColors.border,
                      borderStyle: 'solid',
                      borderWidth: '1px'
                  }">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg"
                         :style="{ backgroundColor: themeColors.danger + '20' }">
                        <ExclamationTriangleIcon class="h-6 w-6"
                                              :style="{ color: themeColors.danger }" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Low Stock</p>
                        <p class="text-2xl font-bold mt-1"
                           :style="{ color: themeColors.textPrimary }">{{ lowStockCount }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                      backgroundColor: themeColors.card,
                      borderColor: themeColors.border,
                      borderStyle: 'solid',
                      borderWidth: '1px'
                  }">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg"
                         :style="{ backgroundColor: themeColors.success + '20' }">
                        <CurrencyDollarIcon class="h-6 w-6"
                                          :style="{ color: themeColors.success }" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Total Value</p>
                        <p class="text-2xl font-bold mt-1"
                           :style="{ color: themeColors.textPrimary }">{{ formatCurrency(totalInventoryValue) }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                      backgroundColor: themeColors.card,
                      borderColor: themeColors.border,
                      borderStyle: 'solid',
                      borderWidth: '1px'
                  }">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg"
                         :style="{ backgroundColor: themeColors.warning + '20' }">
                        <ArrowTrendingUpIcon class="h-6 w-6"
                                           :style="{ color: themeColors.warning }" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Movements</p>
                        <p class="text-2xl font-bold mt-1"
                           :style="{ color: themeColors.textPrimary }">{{ recentMovements }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                      backgroundColor: themeColors.card,
                      borderColor: themeColors.border,
                      borderStyle: 'solid',
                      borderWidth: '1px'
                  }">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg"
                         :style="{ backgroundColor: themeColors.secondary + '20' }">
                        <ArchiveBoxIcon class="h-6 w-6"
                                       :style="{ color: themeColors.secondary }" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Categories</p>
                        <p class="text-2xl font-bold mt-1"
                           :style="{ color: themeColors.textPrimary }">{{ uniqueCategories }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                      backgroundColor: themeColors.card,
                      borderColor: themeColors.border,
                      borderStyle: 'solid',
                      borderWidth: '1px'
                  }">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg"
                         :style="{ backgroundColor: '#10b98120' }">
                        <CheckCircleIcon class="h-6 w-6"
                                        :style="{ color: '#10b981' }" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">In Stock</p>
                        <p class="text-2xl font-bold mt-1"
                           :style="{ color: themeColors.textPrimary }">{{ inStockCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Filter -->
        <div class="rounded-lg p-4 border shadow-sm mb-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
            <div class="flex items-center gap-4">
                <label class="text-sm font-medium whitespace-nowrap" :style="{ color: themeColors.textPrimary }">Filter by Location:</label>
                <select v-model="locationFilter"
                        class="px-3 py-2 border rounded-md focus:outline-none"
                        :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                    <option value="">All Locations</option>
                    <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                </select>
                <span class="text-sm" :style="{ color: themeColors.textSecondary }">
                    Showing {{ filteredProducts.length }} of {{ products.length }} products
                </span>
            </div>
        </div>

        <!-- Products Table -->
        <div class="rounded-lg overflow-hidden shadow"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b"
                 :style="{
                     borderColor: themeColors.border,
                     borderBottomWidth: '1px'
                 }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Product Inventory</h3>
            </div>

            <!-- Table Content -->
            <div class="w-full overflow-hidden">
                <table class="w-full border-collapse">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-2 py-3 text-left text-xs font-medium uppercase tracking-wider w-1/6"
                                :style="{ color: themeColors.textSecondary }">Product</th>
                            <th class="px-2 py-3 text-left text-xs font-medium uppercase tracking-wider w-1/12"
                                :style="{ color: themeColors.textSecondary }">Category</th>
                            <th class="px-2 py-3 text-left text-xs font-medium uppercase tracking-wider w-1/12"
                                :style="{ color: themeColors.textSecondary }">Stock</th>
                            <th class="px-2 py-3 text-left text-xs font-medium uppercase tracking-wider w-1/12"
                                :style="{ color: themeColors.textSecondary }">Min</th>
                            <th class="px-2 py-3 text-left text-xs font-medium uppercase tracking-wider w-1/12"
                                :style="{ color: themeColors.textSecondary }">Cost</th>
                            <th class="px-2 py-3 text-left text-xs font-medium uppercase tracking-wider w-1/12"
                                :style="{ color: themeColors.textSecondary }">Price</th>
                            <th class="px-2 py-3 text-left text-xs font-medium uppercase tracking-wider w-1/12"
                                :style="{ color: themeColors.textSecondary }">Markup</th>
                            <th class="px-2 py-3 text-left text-xs font-medium uppercase tracking-wider hidden lg:table-cell w-1/12"
                                :style="{ color: themeColors.textSecondary }">Locations</th>
                            <th class="px-2 py-3 text-left text-xs font-medium uppercase tracking-wider hidden xl:table-cell w-1/12"
                                :style="{ color: themeColors.textSecondary }">Value</th>
                            <th class="px-2 py-3 text-left text-xs font-medium uppercase tracking-wider w-1/12"
                                :style="{ color: themeColors.textSecondary }">Status</th>
                            <th class="px-2 py-3 text-left text-xs font-medium uppercase tracking-wider w-1/12"
                                :style="{ color: themeColors.textSecondary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="product in filteredProducts" :key="product.id"
                            class="transition-colors"
                            :style="{
                                borderBottomStyle: 'solid',
                                borderBottomWidth: '1px',
                                borderColor: themeColors.border
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                            <td class="px-2 py-4 whitespace-nowrap w-1/6">
                                <div>
                                    <div class="text-xs font-medium"
                                         :style="{ color: themeColors.textPrimary }">{{ product.name }}</div>
                                    <div class="text-xs"
                                         :style="{ color: themeColors.textTertiary }">{{ product.code }}</div>
                                </div>
                            </td>
                            <td class="px-2 py-4 whitespace-nowrap w-1/12">
                                <span class="inline-flex px-1 py-0.5 text-xs font-semibold rounded-full"
                                      :style="{
                                          backgroundColor: themeColors.secondary + '20',
                                          color: themeColors.secondary
                                      }">
                                    {{ product.category?.name || 'N/A' }}
                                </span>
                            </td>
                            <td class="px-2 py-4 whitespace-nowrap w-1/12">
                                <div class="text-xs font-medium"
                                     :style="{ color: getStockColor(product.stock_quantity, product.min_stock_level) }">
                                    {{ product.stock_quantity }}
                                </div>
                            </td>
                            <td class="px-2 py-4 whitespace-nowrap text-xs w-1/12"
                                :style="{ color: themeColors.textPrimary }">
                                {{ product.min_stock_level }}
                            </td>
                            <td class="px-2 py-4 whitespace-nowrap text-xs font-medium w-1/12"
                                :style="{ color: themeColors.textPrimary }">{{ formatCurrency(product.cost_price) }}</td>
                            <td class="px-2 py-4 whitespace-nowrap text-xs font-medium w-1/12"
                                :style="{ color: themeColors.textPrimary }">{{ formatCurrency(product.price) }}</td>
                            <td class="px-2 py-4 whitespace-nowrap w-1/12">
                                <span v-if="product.margin_percentage != null"
                                      class="inline-flex px-1 py-0.5 text-xs font-semibold rounded-full"
                                      :style="{ backgroundColor: themeColors.success + '20', color: themeColors.success }">
                                    {{ parseFloat(product.margin_percentage).toFixed(1) }}%
                                </span>
                                <span v-else class="text-xs" :style="{ color: themeColors.textTertiary }">—</span>
                            </td>
                            <td class="px-2 py-4 hidden lg:table-cell w-1/12">
                                <div v-if="product.stock_by_location && Object.keys(product.stock_by_location).length" class="space-y-0.5 text-xs">
                                    <div v-for="(qty, locName) in product.stock_by_location" :key="locName"
                                         class="flex items-center gap-1">
                                        <span class="inline-block px-1 py-0.5 rounded text-xs"
                                              :style="{ backgroundColor: themeColors.primary + '18', color: themeColors.primary }">
                                            {{ locName.substring(0, 3) }}
                                        </span>
                                        <span :style="{ color: themeColors.textPrimary }">{{ qty }}</span>
                                    </div>
                                </div>
                                <span v-else class="text-xs" :style="{ color: themeColors.textTertiary }">—</span>
                            </td>
                            <td class="px-2 py-4 whitespace-nowrap text-xs font-medium hidden xl:table-cell w-1/12"
                                :style="{ color: themeColors.textPrimary }">{{ formatCurrency(product.total_value) }}</td>
                            <td class="px-2 py-4 whitespace-nowrap w-1/12">
                                <span class="inline-flex px-1 py-0.5 text-xs font-semibold rounded-full"
                                      :class="getStockStatusClass(product.stock_quantity, product.min_stock_level)">
                                    {{ getStockStatus(product.stock_quantity, product.min_stock_level) }}
                                </span>
                            </td>
                            <td class="px-2 py-4 whitespace-nowrap text-xs w-1/12">
                                <div class="flex items-center gap-1">
                                    <button @click="adjustProductStock(product.id)"
                                            class="text-xs font-medium"
                                            :style="{ color: themeColors.primary }">
                                        Adjust
                                    </button>
                                    <button @click="viewProductHistory(product.id)"
                                            class="text-xs font-medium"
                                            :style="{ color: themeColors.success }">
                                        History
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Adjust Stock Modal -->
        <DialogModal :show="showAdjustStock" @close="showAdjustStock = false">
            <template #title>
                Adjust Stock Level
            </template>
            <template #content>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Product</label>
                        <select v-model="selectedProductId"
                                class="w-full px-4 py-2 rounded-lg border transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="">Select a product</option>
                            <option v-for="product in products" :key="product.id" :value="product.id">
                                {{ product.name }} ({{ product.code }})
                            </option>
                        </select>
                    </div>
                    
                    <div v-if="selectedProduct">
                        <div class="p-3 rounded-lg text-sm"
                             :style="{ backgroundColor: themeColors.background, color: themeColors.textSecondary }">
                            <p><strong>Current Stock:</strong> {{ selectedProduct.stock_quantity }}</p>
                            <p><strong>Min Level:</strong> {{ selectedProduct.min_stock_level }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">New Stock Level</label>
                        <input type="number" v-model="newStockLevel" min="0"
                               class="w-full px-4 py-2 rounded-lg border transition-colors"
                               :style="{
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }"
                               placeholder="Enter new stock level" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Adjustment Notes</label>
                        <textarea v-model="adjustmentNotes" rows="3"
                                  class="w-full px-4 py-2 rounded-lg border transition-colors"
                                  :style="{
                                      backgroundColor: themeColors.background,
                                      borderColor: themeColors.border,
                                      color: themeColors.textPrimary,
                                      borderWidth: '1px',
                                      borderStyle: 'solid'
                                  }"
                                  placeholder="Reason for adjustment..."></textarea>
                    </div>
                </div>
            </template>
            <template #footer>
                <button @click="showAdjustStock = false"
                        class="px-4 py-2 rounded-md transition-colors font-medium"
                        :style="{
                            borderColor: themeColors.border,
                            color: themeColors.textSecondary,
                            borderWidth: '1px',
                            borderStyle: 'solid'
                        }">
                    Cancel
                </button>
                <button @click="adjustStock" :disabled="!selectedProductId || newStockLevel === ''"
                        class="ml-3 px-4 py-2 rounded-md transition-colors font-medium text-white"
                        :style="{
                            backgroundColor: themeColors.primary,
                        }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                    Adjust Stock
                </button>
            </template>
        </DialogModal>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DialogModal from '@/Components/DialogModal.vue'
import { router } from '@inertiajs/vue3'
import { formatCurrency } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme.js'
import {
    AdjustmentsHorizontalIcon,
    DocumentArrowDownIcon,
    CubeIcon,
    ExclamationTriangleIcon,
    CurrencyDollarIcon,
    ArrowTrendingUpIcon,
    ArchiveBoxIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    products: Array,
    lowStockCount: Number,
    locations: { type: Array, default: () => [] }
})

const locationFilter = ref('')

const filteredProducts = computed(() => {
    if (!locationFilter.value) return props.products
    return props.products.filter(p => {
        const byLoc = p.stock_by_location || {}
        return Object.keys(byLoc).some(locName =>
            props.locations.find(l => l.id == locationFilter.value && l.name === locName)
        )
    })
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
    secondary: `var(--kotel-secondary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`
}))

// Modal state
const showAdjustStock = ref(false)
const selectedProductId = ref('')
const newStockLevel = ref('')
const adjustmentNotes = ref('')

// Computed properties
const selectedProduct = computed(() => {
    return filteredProducts.value.find(p => p.id == selectedProductId.value) ||
           props.products.find(p => p.id == selectedProductId.value)
})

const totalInventoryValue = computed(() => {
    return filteredProducts.value.reduce((sum, product) => sum + (product.total_value || 0), 0)
})

const recentMovements = computed(() => {
    return props.products.reduce((sum, product) => {
        return sum + (product.recent_movements ? product.recent_movements.length : 0)
    }, 0)
})

const uniqueCategories = computed(() => {
    const categories = new Set(props.products.map(p => p.category).filter(Boolean))
    return categories.size
})

const inStockCount = computed(() => {
    return props.products.filter(p => p.stock_quantity > 0).length
})

// Methods
const getStockStatus = (stock, minLevel) => {
    if (stock === 0) return 'Out of Stock'
    if (stock <= minLevel) return 'Low Stock'
    return 'In Stock'
}

const getStockStatusClass = (stock, minLevel) => {
    if (stock === 0) return 'bg-red-100 text-red-800'
    if (stock <= minLevel) return 'bg-yellow-100 text-yellow-800'
    return 'bg-green-100 text-green-800'
}

const getStockColor = (stock, minLevel) => {
    if (stock === 0) return themeColors.value.danger
    if (stock <= minLevel) return themeColors.value.warning
    return themeColors.value.success
}

const adjustProductStock = (productId) => {
    selectedProductId.value = productId.toString()
    newStockLevel.value = ''
    adjustmentNotes.value = ''
    showAdjustStock.value = true
}

const adjustStock = () => {
    if (!selectedProductId.value || newStockLevel.value === '') return

    router.post('/pos/adjust-stock', {
        product_id: selectedProductId.value,
        quantity: parseInt(newStockLevel.value),
        notes: adjustmentNotes.value
    }, {
        onSuccess: () => {
            showAdjustStock.value = false
            selectedProductId.value = ''
            newStockLevel.value = ''
            adjustmentNotes.value = ''
        },
        onError: (errors) => {
            console.error('Stock adjustment failed:', errors)
        }
    })
}

const viewProductHistory = (productId) => {
    // Navigate to product history page
    router.visit(`/pos/product-history/${productId}`)
}

const exportInventory = () => {
    const headers = ['Name', 'Code', 'Category', 'Brand', 'Unit', 'Cost Price', 'Sale Price', 'Stock Quantity', 'Min Stock Level', 'Total Value', 'Status']
    const rows = props.products.map(product => [
        product.name || '',
        product.code || '',
        product.category || '',
        product.brand || '',
        product.unit || '',
        product.cost_price || 0,
        product.price || 0,
        product.stock_quantity || 0,
        product.min_stock_level || 0,
        (product.price * product.stock_quantity) || 0,
        product.stock_quantity > 0 ? 'In Stock' : (product.stock_quantity > 0 ? 'In Stock' : 'Out of Stock')
    ])
    
    const csvContent = [headers, ...rows]
        .map(row => row.map(cell => `"${cell}"`).join(','))
        .join('\n')
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    
    link.setAttribute('href', url)
    link.setAttribute('download', `inventory-${new Date().toISOString().split('T')[0]}.csv`)
    link.style.visibility = 'hidden'
    
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
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

/* Status badge improvements */
.rounded-full {
    border-radius: 9999px;
}

.inline-flex {
    display: inline-flex;
}
</style>