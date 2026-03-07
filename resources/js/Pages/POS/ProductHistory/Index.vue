<template>
    <DashboardLayout title="Product History" :user="user" :navigation="navigation">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Product History</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Stock movement history for {{ product.name }}</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="exportProductHistory"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                            :style="{ 
                                backgroundColor: themeColors.success,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.success + 'dd'"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                        📥 Export History
                    </button>
                    <button @click="$inertia.visit('/pos/inventory')"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        ← Back to Inventory
                    </button>
                </div>
            </div>

            <!-- Product Info Card -->
            <div class="bg-white rounded-lg p-6 shadow-sm"
                 :style="{ backgroundColor: themeColors.card }">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <span class="text-blue-600 text-xl">📦</span>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold"
                                :style="{ color: themeColors.textPrimary }">{{ product.name }}</h2>
                            <p class="text-sm"
                               :style="{ color: themeColors.textSecondary }">Code: {{ product.code }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Current Stock</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: getStockColor(product.stock_quantity, product.min_stock_level) }">
                            {{ product.stock_quantity }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Stock Movements Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden"
                 :style="{ backgroundColor: themeColors.card }">
                <div class="px-6 py-4 border-b"
                     :style="{ borderColor: themeColors.border }">
                    <h3 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Stock Movements</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Date</th>
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
                            </tr>
                        </thead>
                        <tbody class="divide-y"
                              :style="{ borderColor: themeColors.border }">
                            <tr v-for="movement in stockMovements" :key="movement.id"
                                class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textPrimary }">
                                        {{ formatDate(movement.created_at) }}
                                    </div>
                                    <div class="text-xs"
                                         :style="{ color: themeColors.textTertiary }">
                                        {{ formatTime(movement.created_at) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                          :class="getMovementTypeClass(movement.type)">
                                        {{ getMovementTypeLabel(movement.type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium"
                                         :class="movement.quantity > 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ movement.quantity > 0 ? '+' : '' }}{{ movement.quantity }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textPrimary }">
                                        {{ movement.previous_stock || 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium"
                                         :style="{ color: themeColors.textPrimary }">
                                        {{ movement.new_stock || 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textPrimary }">
                                        {{ movement.user?.name || 'System' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textPrimary }">
                                        {{ movement.notes || '-' }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="stockMovements.length === 0" class="text-center py-8">
                        <div class="text-gray-400 text-lg">📋</div>
                        <p class="text-gray-500 mt-2">No stock movements found</p>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
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
    product: Object,
    stockMovements: Array
})

// Methods
const getStockColor = (stock, minLevel) => {
    if (stock === 0) return themeColors.value.danger
    if (stock <= minLevel) return themeColors.value.warning
    return themeColors.value.success
}

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

// Export product history
const exportProductHistory = () => {
    // Create CSV content
    const headers = ['Date', 'Time', 'Type', 'Quantity', 'Previous Stock', 'New Stock', 'User', 'Notes']
    const rows = props.stockMovements.map(movement => [
        formatDate(movement.created_at),
        formatTime(movement.created_at),
        getMovementTypeLabel(movement.type),
        movement.quantity > 0 ? `+${movement.quantity}` : movement.quantity.toString(),
        movement.previous_stock || 'N/A',
        movement.new_stock || 'N/A',
        movement.user?.name || 'System',
        movement.notes || '-'
    ])
    
    // Combine headers and rows
    const csvContent = [headers, ...rows]
        .map(row => row.map(cell => `"${cell}"`).join(','))
        .join('\n')
    
    // Create blob and download
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    
    link.setAttribute('href', url)
    link.setAttribute('download', `product-history-${props.product.name}-${new Date().toISOString().split('T')[0]}.csv`)
    link.style.visibility = 'hidden'
    
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
}
</script>
