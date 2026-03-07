<template>
    <DashboardLayout title="Stock Movements" :user="user" :navigation="navigation">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Stock Movements</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Complete stock movement history and audit trail</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="exportMovements"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                            :style="{ 
                                backgroundColor: themeColors.success,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.success + 'dd'"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                        📥 Export Movements
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-lg p-4 shadow-sm"
                     :style="{ backgroundColor: themeColors.card }">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full mr-4"
                             :style="{ backgroundColor: themeColors.primary + '20' }">
                            <span class="text-2xl">📊</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textSecondary }">Total Movements</p>
                            <p class="text-xl font-bold"
                               :style="{ color: themeColors.textPrimary }">{{ movements.length }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="rounded-lg p-4 shadow-sm"
                     :style="{ backgroundColor: themeColors.card }">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full mr-4"
                             :style="{ backgroundColor: themeColors.success + '20' }">
                            <span class="text-2xl">➕</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textSecondary }">Stock In</p>
                            <p class="text-xl font-bold"
                               :style="{ color: themeColors.success }">{{ totalStockIn }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="rounded-lg p-4 shadow-sm"
                     :style="{ backgroundColor: themeColors.card }">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full mr-4"
                             :style="{ backgroundColor: themeColors.danger + '20' }">
                            <span class="text-2xl">➖</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textSecondary }">Stock Out</p>
                            <p class="text-xl font-bold"
                               :style="{ color: themeColors.danger }">{{ totalStockOut }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="rounded-lg p-4 shadow-sm"
                     :style="{ backgroundColor: themeColors.card }">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full mr-4"
                             :style="{ backgroundColor: themeColors.warning + '20' }">
                            <span class="text-2xl">⚙️</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textSecondary }">Adjustments</p>
                            <p class="text-xl font-bold"
                               :style="{ color: themeColors.warning }">{{ totalAdjustments }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-lg p-4 shadow-sm"
                 :style="{ backgroundColor: themeColors.card }">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textPrimary }">Product</label>
                        <select v-model="filters.product_id"
                                class="w-full px-3 py-2 border rounded-md focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderWidth: '1px',
                                    borderStyle: 'solid',
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary
                                }">
                            <option value="">All Products</option>
                            <option v-for="product in allProducts" :key="product.id" :value="product.id">
                                {{ product.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textPrimary }">Movement Type</label>
                        <select v-model="filters.type"
                                class="w-full px-3 py-2 border rounded-md focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderWidth: '1px',
                                    borderStyle: 'solid',
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary
                                }">
                            <option value="">All Types</option>
                            <option value="in">Stock In</option>
                            <option value="out">Stock Out</option>
                            <option value="adjustment">Adjustment</option>
                            <option value="purchase">Purchase</option>
                            <option value="sale">Sale</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textPrimary }">Location</label>
                        <select v-model="filters.location_id"
                                class="w-full px-3 py-2 border rounded-md focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderWidth: '1px',
                                    borderStyle: 'solid',
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary
                                }">
                            <option value="">All Locations</option>
                            <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textPrimary }">Date From</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10"
                                 :style="{ color: themeColors.textSecondary }">
                                <svg class="h-5 w-5" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input type="date" 
                                   ref="date_from"
                                   v-model="filters.date_from"
                                   class="w-full pl-10 pr-3 py-2 border rounded-md focus:outline-none focus:ring-2 transition-colors cursor-pointer"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderWidth: '1px',
                                       borderStyle: 'solid',
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }">
                            <div class="absolute inset-0 cursor-pointer" @click="openDatePicker('date_from')"></div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textPrimary }">Date To</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10"
                                 :style="{ color: themeColors.textSecondary }">
                                <svg class="h-5 w-5" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input type="date" 
                                   ref="date_to"
                                   v-model="filters.date_to"
                                   class="w-full pl-10 pr-3 py-2 border rounded-md focus:outline-none focus:ring-2 transition-colors cursor-pointer"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderWidth: '1px',
                                       borderStyle: 'solid',
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }">
                            <div class="absolute inset-0 cursor-pointer" @click="openDatePicker('date_to')"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Movements Table -->
            <div class="rounded-lg shadow-sm overflow-hidden"
                 :style="{ backgroundColor: themeColors.card }">
                <div class="px-4 py-3 border-b"
                     :style="{ borderColor: themeColors.border }">
                    <h3 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Stock Movements</h3>
                </div>
                
                <!-- Horizontal scrollable table container -->
                <div class="overflow-x-auto">
                    <div class="min-w-full">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap"
                                        :style="{ color: themeColors.textSecondary }">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap"
                                        :style="{ color: themeColors.textSecondary }">Time</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap"
                                        :style="{ color: themeColors.textSecondary }">Product</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap"
                                        :style="{ color: themeColors.textSecondary }">Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap"
                                        :style="{ color: themeColors.textSecondary }">Quantity</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap"
                                        :style="{ color: themeColors.textSecondary }">Previous Stock</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap"
                                        :style="{ color: themeColors.textSecondary }">New Stock</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap"
                                        :style="{ color: themeColors.textSecondary }">Location</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap"
                                        :style="{ color: themeColors.textSecondary }">User</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap"
                                        :style="{ color: themeColors.textSecondary }">Reference</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap"
                                        :style="{ color: themeColors.textSecondary }">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y"
                                  :style="{ borderColor: themeColors.border }">
                                <tr v-for="movement in paginatedMovements" :key="movement.id"
                                    class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm"
                                             :style="{ color: themeColors.textPrimary }">
                                            {{ formatDate(movement.created_at) }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm"
                                             :style="{ color: themeColors.textPrimary }">
                                            {{ formatTime(movement.created_at) }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm font-medium"
                                             :style="{ color: themeColors.textPrimary }">
                                            {{ movement.product?.name || 'N/A' }}
                                        </div>
                                        <div class="text-xs"
                                             :style="{ color: themeColors.textTertiary }">
                                            {{ movement.product?.sku || '' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                              :class="getMovementTypeClass(movement.type)">
                                            {{ getMovementTypeLabel(movement.type) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm font-medium"
                                             :class="movement.quantity > 0 ? 'text-green-600' : 'text-red-600'">
                                            {{ movement.quantity > 0 ? '+' : '' }}{{ movement.quantity }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm"
                                             :style="{ color: themeColors.textPrimary }">
                                            {{ movement.previous_stock || 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm font-medium"
                                             :style="{ color: themeColors.textPrimary }">
                                            {{ movement.new_stock || 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div v-if="movement.location" class="text-sm">
                                            <span class="inline-flex px-2 py-0.5 text-xs rounded-full"
                                                  :style="{ backgroundColor: themeColors.primary + '18', color: themeColors.primary }">
                                                {{ movement.location.name }}
                                            </span>
                                        </div>
                                        <div v-else class="text-xs" :style="{ color: themeColors.textTertiary }">—</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm"
                                             :style="{ color: themeColors.textPrimary }">
                                            {{ movement.user?.name || 'System' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm"
                                             :style="{ color: themeColors.textPrimary }">
                                            {{ movement.reference_type || '-' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm max-w-xs"
                                             :style="{ color: themeColors.textPrimary }">
                                            <div class="truncate" :title="movement.notes || '-'">
                                                {{ movement.notes || '-' }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div v-if="filteredMovements.length === 0" class="text-center py-8">
                            <div class="text-gray-400 text-lg">📋</div>
                            <p class="text-gray-500 mt-2">No stock movements found</p>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="filteredMovements.length > 0" class="px-4 py-3 border-t flex items-center justify-between"
                     :style="{ borderColor: themeColors.border }">
                    <div class="text-sm text-gray-700">
                        Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, filteredMovements.length) }} 
                        of {{ filteredMovements.length }} results
                    </div>
                    <div class="flex space-x-2">
                        <button @click="currentPage = Math.max(1, currentPage - 1)"
                                :disabled="currentPage === 1"
                                class="px-3 py-1 text-sm border rounded-md transition-colors"
                                :style="{
                                    backgroundColor: currentPage === 1 ? themeColors.background : themeColors.card,
                                    borderColor: themeColors.border,
                                    color: currentPage === 1 ? themeColors.textTertiary : themeColors.textPrimary,
                                    opacity: currentPage === 1 ? 0.5 : 1
                                }">
                            Previous
                        </button>
                        <span class="px-3 py-1 text-sm"
                              :style="{ color: themeColors.textPrimary }">
                            Page {{ currentPage }} of {{ totalPages }}
                        </span>
                        <button @click="currentPage = Math.min(totalPages, currentPage + 1)"
                                :disabled="currentPage === totalPages"
                                class="px-3 py-1 text-sm border rounded-md transition-colors"
                                :style="{
                                    backgroundColor: currentPage === totalPages ? themeColors.background : themeColors.card,
                                    borderColor: themeColors.border,
                                    color: currentPage === totalPages ? themeColors.textTertiary : themeColors.textPrimary,
                                    opacity: currentPage === totalPages ? 0.5 : 1
                                }">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
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
    info: `var(--kotel-info)`,
    hover: `var(--kotel-primary-hover)`
}))

loadTheme()

// Props
const props = defineProps({
    user: Object,
    navigation: Array,
    movements: Array,
    locations: { type: Array, default: () => [] }
})

// Data
const allProducts = ref([])
const filters = ref({
    product_id: '',
    type: '',
    date_from: '',
    date_to: '',
    location_id: ''
})
const currentPage = ref(1)
const perPage = ref(10)
const date_from = ref(null)
const date_to = ref(null)

// Computed
const totalStockIn = computed(() => {
    return props.movements.filter(m => m.quantity > 0).reduce((sum, m) => sum + m.quantity, 0)
})

const totalStockOut = computed(() => {
    return props.movements.filter(m => m.quantity < 0).reduce((sum, m) => sum + Math.abs(m.quantity), 0)
})

const totalAdjustments = computed(() => {
    return props.movements.filter(m => m.type === 'adjustment').length
})

const totalPurchases = computed(() => {
    return props.movements.filter(m => m.type === 'purchase').length
})

const filteredMovements = computed(() => {
    let filtered = props.movements

    // Product filter
    if (filters.value.product_id) {
        filtered = filtered.filter(m => m.product_id == filters.value.product_id)
    }

    // Type filter
    if (filters.value.type) {
        filtered = filtered.filter(m => m.type === filters.value.type)
    }

    // Date filter
    if (filters.value.date_from) {
        const fromDate = new Date(filters.value.date_from)
        filtered = filtered.filter(m => new Date(m.created_at) >= fromDate)
    }

    if (filters.value.date_to) {
        const toDate = new Date(filters.value.date_to)
        toDate.setHours(23, 59, 59, 999)
        filtered = filtered.filter(m => new Date(m.created_at) <= toDate)
    }

    // Location filter
    if (filters.value.location_id) {
        filtered = filtered.filter(m => m.location && m.location.id == filters.value.location_id)
    }

    return filtered
})

const totalPages = computed(() => {
    return Math.ceil(filteredMovements.value.length / perPage.value)
})

const paginatedMovements = computed(() => {
    const start = (currentPage.value - 1) * perPage.value
    const end = start + perPage.value
    return filteredMovements.value.slice(start, end)
})

// Watch for filter changes and reset pagination
watch([filters], () => {
    currentPage.value = 1
}, { deep: true })

// Methods
const getMovementTypeClass = (type) => {
    const classes = {
        'in': 'bg-green-100 text-green-800',
        'out': 'bg-red-100 text-red-800',
        'adjustment': 'bg-blue-100 text-blue-800',
        'purchase': 'bg-purple-100 text-purple-800',
        'sale': 'bg-orange-100 text-orange-800'
    }
    return classes[type] || 'bg-gray-100 text-gray-800'
}

const getMovementTypeLabel = (type) => {
    const labels = {
        'in': 'Stock In',
        'out': 'Stock Out',
        'adjustment': 'Adjustment',
        'purchase': 'Purchase',
        'sale': 'Sale'
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

const exportMovements = () => {
    const headers = ['Date', 'Time', 'Product', 'Type', 'Quantity', 'Previous Stock', 'New Stock', 'User', 'Reference', 'Notes']
    const rows = filteredMovements.value.map(movement => [
        formatDate(movement.created_at),
        formatTime(movement.created_at),
        movement.product?.name || 'N/A',
        getMovementTypeLabel(movement.type),
        movement.quantity > 0 ? `+${movement.quantity}` : movement.quantity.toString(),
        movement.previous_stock || 'N/A',
        movement.new_stock || 'N/A',
        movement.user?.name || 'System',
        movement.reference_type || '-',
        movement.notes || '-'
    ])
    
    const csvContent = [headers, ...rows]
        .map(row => row.map(cell => `"${cell}"`).join(','))
        .join('\n')
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    
    link.setAttribute('href', url)
    link.setAttribute('download', `stock-movements-${new Date().toISOString().split('T')[0]}.csv`)
    link.style.visibility = 'hidden'
    
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
}

const openDatePicker = (inputName) => {
    const inputRef = inputName === 'date_from' ? date_from : date_to
    if (inputRef.value) {
        inputRef.value.showPicker()
    }
}

// Load products for filter dropdown
onMounted(() => {
    // Load all products for the filter dropdown using web route
    axios.get('/pos/products/list')
        .then(response => {
            allProducts.value = response.data.data || []
        })
        .catch(error => {
            console.error('Failed to load products:', error)
        })
})
</script>
