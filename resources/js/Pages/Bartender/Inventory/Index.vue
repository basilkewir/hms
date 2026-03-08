<template>
    <DashboardLayout title="Bar Inventory" :user="user" :navigation="navigation">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold"
                    :style="{ color: themeColors.textPrimary }">📦 Bar Inventory</h1>
                <p class="text-sm mt-2"
                   :style="{ color: themeColors.textSecondary }">Monitor stock levels and inventory value</p>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <h3 class="text-sm font-medium mb-2"
                    :style="{ color: themeColors.textSecondary }">Total Items</h3>
                <p class="text-3xl font-bold"
                   :style="{ color: themeColors.textPrimary }">{{ inventory.length }}</p>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <h3 class="text-sm font-medium mb-2"
                    :style="{ color: themeColors.textSecondary }">Total Value</h3>
                <p class="text-3xl font-bold"
                   :style="{ color: themeColors.success }">{{ formatCurrency(totalInventoryValue) }}</p>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <h3 class="text-sm font-medium mb-2"
                    :style="{ color: themeColors.textSecondary }">Low Stock Items</h3>
                <p class="text-3xl font-bold"
                   :style="{ color: themeColors.warning }">{{ lowStockCount }}</p>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <h3 class="text-sm font-medium mb-2"
                    :style="{ color: themeColors.textSecondary }">Out of Stock</h3>
                <p class="text-3xl font-bold"
                   :style="{ color: themeColors.danger }">{{ outOfStockCount }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-8 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2"
                       :style="{ color: themeColors.textSecondary }">Search</label>
                <input type="text" v-model="searchQuery"
                       placeholder="Search inventory..."
                       class="w-full px-4 py-2 rounded-lg border"
                       :style="{
                           backgroundColor: themeColors.card,
                           borderColor: themeColors.border,
                           color: themeColors.textPrimary
                       }" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-2"
                       :style="{ color: themeColors.textSecondary }">Stock Status</label>
                <select v-model="selectedStatus"
                        class="w-full px-4 py-2 rounded-lg border"
                        :style="{
                            backgroundColor: themeColors.card,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary
                        }">
                    <option value="">All Items</option>
                    <option value="in-stock">In Stock</option>
                    <option value="low-stock">Low Stock</option>
                    <option value="out-of-stock">Out of Stock</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2"
                       :style="{ color: themeColors.textSecondary }">Sort By</label>
                <select v-model="sortBy"
                        class="w-full px-4 py-2 rounded-lg border"
                        :style="{
                            backgroundColor: themeColors.card,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary
                        }">
                    <option value="name">Item Name</option>
                    <option value="stock-low">Stock (Low to High)</option>
                    <option value="stock-high">Stock (High to Low)</option>
                    <option value="value">Inventory Value</option>
                </select>
            </div>
            <div class="flex items-end">
                <button @click="resetFilters()"
                        class="w-full px-4 py-2 rounded-lg font-medium"
                        :style="{
                            backgroundColor: themeColors.secondary,
                            color: '#fff'
                        }">
                    Reset
                </button>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="rounded-lg border shadow-sm overflow-hidden"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background, borderBottom: `1px solid ${themeColors.border}` }">
                            <th class="px-6 py-3 text-left text-xs font-medium"
                                :style="{ color: themeColors.textSecondary }">Item Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium"
                                :style="{ color: themeColors.textSecondary }">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-center"
                                :style="{ color: themeColors.textSecondary }">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-center"
                                :style="{ color: themeColors.textSecondary }">Min Level</th>
                            <th class="px-6 py-3 text-left text-xs font-medium"
                                :style="{ color: themeColors.textSecondary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-right"
                                :style="{ color: themeColors.textSecondary }">Unit Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-right"
                                :style="{ color: themeColors.textSecondary }">Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y"
                           :style="{ borderColor: themeColors.border }">
                        <tr v-if="filteredInventory.length === 0">
                            <td colspan="7" class="px-6 py-4 text-center"
                                :style="{ color: themeColors.textSecondary }">No inventory items found</td>
                        </tr>
                        <tr v-for="item in filteredInventory" :key="item.id"
                            class="hover:opacity-75 cursor-pointer"
                            :style="{ backgroundColor: themeColors.card }">
                            <td class="px-6 py-4"
                                :style="{ color: themeColors.textPrimary }">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">{{ item.emoji || '🍹' }}</span>
                                    <div>
                                        <p class="font-medium">{{ item.name }}</p>
                                        <p class="text-xs"
                                           :style="{ color: themeColors.textSecondary }">Code: {{ item.code }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4"
                                :style="{ color: themeColors.textSecondary }">{{ item.category }}</td>
                            <td class="px-6 py-4 text-center font-bold"
                                :style="{ color: getStockColor(item.stock_quantity, item.min_stock_level) }">
                                {{ item.stock_quantity }} {{ item.unit }}
                            </td>
                            <td class="px-6 py-4 text-center"
                                :style="{ color: themeColors.textSecondary }">{{ item.min_stock_level }} {{ item.unit }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded text-xs font-medium"
                                      :style="{
                                          backgroundColor: getStockBgColor(item.stock_quantity, item.min_stock_level),
                                          color: getStockColor(item.stock_quantity, item.min_stock_level)
                                      }">
                                    {{ getStockStatus(item.stock_quantity, item.min_stock_level) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right"
                                :style="{ color: themeColors.textPrimary }">{{ formatCurrency(item.price) }}</td>
                            <td class="px-6 py-4 text-right font-bold"
                                :style="{ color: themeColors.success }">{{ formatCurrency(item.stock_quantity * item.cost_price) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { getNavigationForRole } from '@/Utils/navigation.js'

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
}))

loadTheme()

const props = defineProps({
    user: Object,
    inventory: Array,
    currency: String,
    currencyPosition: String,
})

const navigation = computed(() => getNavigationForRole('bartender'))
const searchQuery = ref('')
const selectedStatus = ref('')
const sortBy = ref('name')

const totalInventoryValue = computed(() => {
    return props.inventory?.reduce((sum, item) => sum + ((parseFloat(item.stock_quantity) || 0) * (parseFloat(item.cost_price) || 0)), 0) || 0
})

const lowStockCount = computed(() => {
    return props.inventory?.filter(i => i.stock_quantity <= i.min_stock_level && i.stock_quantity > 0).length || 0
})

const outOfStockCount = computed(() => {
    return props.inventory?.filter(i => i.stock_quantity === 0).length || 0
})

const filteredInventory = computed(() => {
    let items = props.inventory?.filter(item => {
        const matchesSearch = item.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                            item.code.toLowerCase().includes(searchQuery.value.toLowerCase())
        const matchesStatus = !selectedStatus.value ||
            (selectedStatus.value === 'in-stock' && item.stock_quantity > item.min_stock_level) ||
            (selectedStatus.value === 'low-stock' && item.stock_quantity <= item.min_stock_level && item.stock_quantity > 0) ||
            (selectedStatus.value === 'out-of-stock' && item.stock_quantity === 0)

        return matchesSearch && matchesStatus
    }) || []

    // Sort items
    if (sortBy.value === 'stock-low') {
        items.sort((a, b) => a.stock_quantity - b.stock_quantity)
    } else if (sortBy.value === 'stock-high') {
        items.sort((a, b) => b.stock_quantity - a.stock_quantity)
    } else if (sortBy.value === 'value') {
        items.sort((a, b) => (b.stock_quantity * b.cost_price) - (a.stock_quantity * a.cost_price))
    } else {
        items.sort((a, b) => a.name.localeCompare(b.name))
    }

    return items
})

const resetFilters = () => {
    searchQuery.value = ''
    selectedStatus.value = ''
    sortBy.value = 'name'
}

const formatCurrency = (amount) => {
    const currency = props.currency || 'USD'
    const numAmount = parseFloat(amount) || 0
    if (!isFinite(numAmount)) return new Intl.NumberFormat('en-US', { style: 'currency', currency, minimumFractionDigits: 2 }).format(0)
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(numAmount)
}

const getStockStatus = (quantity, minLevel) => {
    if (quantity === 0) return 'Out of Stock'
    if (quantity <= minLevel) return 'Low Stock'
    return 'In Stock'
}

const getStockColor = (quantity, minLevel) => {
    if (quantity === 0) return themeColors.value.danger
    if (quantity <= minLevel) return themeColors.value.warning
    return themeColors.value.success
}

const getStockBgColor = (quantity, minLevel) => {
    if (quantity === 0) return 'rgba(239, 68, 68, 0.1)'
    if (quantity <= minLevel) return 'rgba(245, 158, 11, 0.1)'
    return 'rgba(34, 197, 94, 0.1)'
}
</script>
