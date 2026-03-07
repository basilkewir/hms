<template>
    <DashboardLayout title="Stock Adjustments" :user="user" :navigation="navigation">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Stock Adjustments</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Manage stock adjustments and corrections</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="exportAdjustments"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                            :style="{ 
                                backgroundColor: themeColors.success,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.success + 'dd'"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                        📥 Export Adjustments
                    </button>
                    <button @click="showCreateModal = true"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        + New Adjustment
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg p-6 shadow-sm"
                     :style="{ backgroundColor: themeColors.card }">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full mr-4"
                             :style="{ backgroundColor: themeColors.primary + '20' }">
                            <span class="text-2xl">📊</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textSecondary }">Total Adjustments</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: themeColors.textPrimary }">{{ adjustments.length }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg p-6 shadow-sm"
                     :style="{ backgroundColor: themeColors.card }">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full mr-4"
                             :style="{ backgroundColor: themeColors.success + '20' }">
                            <span class="text-2xl">➕</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textSecondary }">Total Stock In</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: themeColors.success }">{{ totalStockIn }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg p-6 shadow-sm"
                     :style="{ backgroundColor: themeColors.card }">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full mr-4"
                             :style="{ backgroundColor: themeColors.danger + '20' }">
                            <span class="text-2xl">➖</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textSecondary }">Total Stock Out</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: themeColors.danger }">{{ totalStockOut }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg p-6 shadow-sm"
                     :style="{ backgroundColor: themeColors.card }">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full mr-4"
                             :style="{ backgroundColor: themeColors.warning + '20' }">
                            <span class="text-2xl">⚖️</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textSecondary }">Net Change</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: netChange > 0 ? themeColors.success : themeColors.danger }">{{ netChange }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Adjustments Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden"
                 :style="{ backgroundColor: themeColors.card }">
                <div class="px-6 py-4 border-b"
                     :style="{ borderColor: themeColors.border }">
                    <h3 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Recent Adjustments</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Previous Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">New Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Notes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y"
                              :style="{ borderColor: themeColors.border }">
                            <tr v-for="adjustment in adjustments" :key="adjustment.id"
                                class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textPrimary }">
                                        {{ formatDate(adjustment.created_at) }}
                                    </div>
                                    <div class="text-xs"
                                         :style="{ color: themeColors.textTertiary }">
                                        {{ formatTime(adjustment.created_at) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium"
                                         :style="{ color: themeColors.textPrimary }">
                                        {{ adjustment.product?.name || 'N/A' }}
                                    </div>
                                    <div class="text-xs"
                                         :style="{ color: themeColors.textTertiary }">
                                        {{ adjustment.product?.code || '' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                          :class="getAdjustmentTypeClass(adjustment.type)">
                                        {{ getAdjustmentTypeLabel(adjustment.type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium"
                                         :class="adjustment.quantity > 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ adjustment.quantity > 0 ? '+' : '' }}{{ adjustment.quantity }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textPrimary }">
                                        {{ adjustment.previous_stock || 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium"
                                         :style="{ color: themeColors.textPrimary }">
                                        {{ adjustment.new_stock || 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textPrimary }">
                                        {{ adjustment.user?.name || 'System' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textPrimary }">
                                        {{ adjustment.notes || '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button @click="viewAdjustment(adjustment)"
                                            class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="adjustments.length === 0" class="text-center py-8">
                        <div class="text-gray-400 text-lg">📋</div>
                        <p class="text-gray-500 mt-2">No stock adjustments found</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Adjustment Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6"
                 :style="{ backgroundColor: themeColors.card }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Create Stock Adjustment</h3>
                    <button @click="showCreateModal = false"
                            class="text-gray-400 hover:text-gray-600">
                        ✕
                    </button>
                </div>
                <form @submit.prevent="createAdjustment">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textPrimary }">Product</label>
                            <select v-model="newAdjustment.product_id"
                                    class="w-full px-3 py-2 border rounded-md focus:outline-none transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderWidth: '1px',
                                        borderStyle: 'solid',
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary
                                    }">
                                <option value="">Select a product</option>
                                <option v-for="product in allProducts" :key="product.id" :value="product.id">
                                    {{ product.name }} ({{ product.sku }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textPrimary }">Adjustment Type</label>
                            <select v-model="newAdjustment.type"
                                    class="w-full px-3 py-2 border rounded-md focus:outline-none transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderWidth: '1px',
                                        borderStyle: 'solid',
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary
                                    }">
                                <option value="in">Stock In</option>
                                <option value="out">Stock Out</option>
                                <option value="adjustment">Adjustment</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textPrimary }">Quantity</label>
                            <input type="number" v-model="newAdjustment.quantity" step="0.01" min="0"
                                   class="w-full px-3 py-2 border rounded-md focus:outline-none transition-colors"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderWidth: '1px',
                                       borderStyle: 'solid',
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }"
                                   placeholder="Enter quantity">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textPrimary }">Notes</label>
                            <textarea v-model="newAdjustment.notes" rows="3"
                                      class="w-full px-3 py-2 border rounded-md focus:outline-none transition-colors"
                                      :style="{
                                          backgroundColor: themeColors.background,
                                          borderWidth: '1px',
                                          borderStyle: 'solid',
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary
                                      }"
                                      placeholder="Enter notes (optional)"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" @click="showCreateModal = false"
                                class="px-4 py-2 rounded-md transition-colors font-medium"
                                :style="{
                                    backgroundColor: themeColors.secondary,
                                    color: themeColors.textPrimary
                                }">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                                :style="{
                                    backgroundColor: themeColors.primary,
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                            Create Adjustment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'

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
    hover: `var(--kotel-primary-hover)`
}))

loadTheme()

// Props
const props = defineProps({
    user: Object,
    navigation: Array,
    adjustments: Array
})

// Data
const showCreateModal = ref(false)
const allProducts = ref([])
const newAdjustment = ref({
    product_id: '',
    type: 'adjustment',
    quantity: '',
    notes: ''
})

// Computed
const totalStockIn = computed(() => {
    return props.adjustments.filter(a => a.quantity > 0).reduce((sum, a) => sum + a.quantity, 0)
})

const totalStockOut = computed(() => {
    return props.adjustments.filter(a => a.quantity < 0).reduce((sum, a) => sum + Math.abs(a.quantity), 0)
})

const netChange = computed(() => {
    return totalStockIn.value - totalStockOut.value
})

// Methods
const getAdjustmentTypeClass = (type) => {
    const classes = {
        'in': 'bg-green-100 text-green-800',
        'out': 'bg-red-100 text-red-800',
        'adjustment': 'bg-blue-100 text-blue-800'
    }
    return classes[type] || 'bg-gray-100 text-gray-800'
}

const getAdjustmentTypeLabel = (type) => {
    const labels = {
        'in': 'Stock In',
        'out': 'Stock Out',
        'adjustment': 'Adjustment'
    }
    return labels[type] || type
}

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString()
}

const formatTime = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

const createAdjustment = () => {
    if (!newAdjustment.value.product_id || !newAdjustment.value.quantity) {
        alert('Please fill in required fields')
        return
    }

    router.post('/pos/adjustments', newAdjustment.value, {
        onSuccess: () => {
            showCreateModal.value = false
            resetNewAdjustmentForm()
        },
        onError: (errors) => {
            console.error('Error creating adjustment:', errors)
            alert('Error creating adjustment. Please try again.')
        }
    })
}

const viewAdjustment = (adjustment) => {
    // Navigate to adjustment details or product history
    console.log('View adjustment:', adjustment)
}

const exportAdjustments = () => {
    const headers = ['Date', 'Time', 'Product', 'Type', 'Quantity', 'Previous Stock', 'New Stock', 'User', 'Notes']
    const rows = props.adjustments.map(adjustment => [
        formatDate(adjustment.created_at),
        formatTime(adjustment.created_at),
        adjustment.product?.name || 'N/A',
        getAdjustmentTypeLabel(adjustment.type),
        adjustment.quantity > 0 ? `+${adjustment.quantity}` : adjustment.quantity.toString(),
        adjustment.previous_stock || 'N/A',
        adjustment.new_stock || 'N/A',
        adjustment.user?.name || 'System',
        adjustment.notes || '-'
    ])
    
    const csvContent = [headers, ...rows]
        .map(row => row.map(cell => `"${cell}"`).join(','))
        .join('\n')
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    
    link.setAttribute('href', url)
    link.setAttribute('download', `stock-adjustments-${new Date().toISOString().split('T')[0]}.csv`)
    link.style.visibility = 'hidden'
    
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
}

const resetNewAdjustmentForm = () => {
    newAdjustment.value = {
        product_id: '',
        type: 'adjustment',
        quantity: '',
        notes: ''
    }
}

// Load products for dropdown
onMounted(() => {
    // Load all products for the dropdown using axios
    axios.get('/pos/products/list')
        .then(response => {
            allProducts.value = response.data.data || []
        })
        .catch(error => {
            console.error('Failed to load products:', error)
        })
})
</script>
