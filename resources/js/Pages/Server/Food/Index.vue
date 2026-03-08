<template>
    <DashboardLayout title="Food Menu" :user="user" :navigation="navigation">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold"
                    :style="{ color: themeColors.textPrimary }">🍽️ Food Menu</h1>
                <p class="text-sm mt-2"
                   :style="{ color: themeColors.textSecondary }">Browse restaurant menu items</p>
            </div>
            <div>
                <span class="px-4 py-2 rounded-lg font-bold"
                      :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)', color: themeColors.primary }">
                    {{ foodItems.length }} Items Available
                </span>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-8 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2"
                       :style="{ color: themeColors.textSecondary }">Search</label>
                <input type="text" v-model="searchQuery"
                       placeholder="Search food items..."
                       class="w-full px-4 py-2 rounded-lg border"
                       :style="{
                           backgroundColor: themeColors.card,
                           borderColor: themeColors.border,
                           color: themeColors.textPrimary
                       }" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-2"
                       :style="{ color: themeColors.textSecondary }">Category</label>
                <select v-model="selectedCategory"
                        class="w-full px-4 py-2 rounded-lg border"
                        :style="{
                            backgroundColor: themeColors.card,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary
                        }">
                    <option value="">All Categories</option>
                    <option v-for="category in categories" :key="category" :value="category">
                        {{ category }}
                    </option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2"
                       :style="{ color: themeColors.textSecondary }">Stock Status</label>
                <select v-model="selectedStock"
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
            <div class="flex items-end">
                <button @click="resetFilters()"
                        class="w-full px-4 py-2 rounded-lg font-medium"
                        :style="{
                            backgroundColor: themeColors.secondary,
                            color: '#fff'
                        }">
                    Reset Filters
                </button>
            </div>
        </div>

        <!-- Food Items Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div v-if="filteredItems.length === 0" class="col-span-full text-center py-12"
                 :style="{ backgroundColor: themeColors.card, borderRadius: '0.5rem', border: `1px solid ${themeColors.border}` }">
                <p class="text-lg font-medium"
                   :style="{ color: themeColors.textPrimary }">No food items found</p>
                <p class="text-sm mt-2"
                   :style="{ color: themeColors.textSecondary }">Try adjusting your filters</p>
            </div>

            <div v-for="item in filteredItems" :key="item.id"
                 class="rounded-lg border shadow-sm overflow-hidden hover:shadow-md transition"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <!-- Item Image/Icon -->
                <div class="h-32 flex items-center justify-center text-5xl"
                     :style="{ backgroundColor: 'rgba(34, 197, 94, 0.05)' }">
                    {{ item.emoji || '🍽️' }}
                </div>

                <!-- Item Info -->
                <div class="p-4">
                    <h3 class="text-lg font-bold mb-1"
                        :style="{ color: themeColors.textPrimary }">{{ item.name }}</h3>
                    <p class="text-sm mb-3"
                       :style="{ color: themeColors.textSecondary }">{{ item.category }}</p>

                    <!-- Description -->
                    <p v-if="item.description" class="text-xs mb-3"
                       :style="{ color: themeColors.textSecondary }">{{ item.description }}</p>

                    <!-- Price -->
                    <div class="mb-3 pb-3" :style="{ borderBottom: `1px solid ${themeColors.border}` }">
                        <p class="text-xs font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Price</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.success }">{{ formatCurrency(item.price) }}</p>
                    </div>

                    <!-- Stock -->
                    <div class="mb-3">
                        <p class="text-xs font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Stock</p>
                        <div class="flex items-center justify-between">
                            <span class="font-bold"
                                  :style="{ color: getStockColor(item.stock_quantity, item.min_stock_level) }">
                                {{ item.stock_quantity }} {{ item.unit }}
                            </span>
                            <span class="px-2 py-1 rounded text-xs font-medium"
                                  :style="{
                                      backgroundColor: getStockBgColor(item.stock_quantity, item.min_stock_level),
                                      color: getStockColor(item.stock_quantity, item.min_stock_level)
                                  }">
                                {{ getStockStatus(item.stock_quantity, item.min_stock_level) }}
                            </span>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <button @click="addToOrder(item.id)"
                            class="w-full px-4 py-2 rounded-lg font-medium transition"
                            :style="{
                                backgroundColor: themeColors.primary,
                                color: '#fff'
                            }">
                        Add to Order
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'
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
    foodItems: Array,
})

const navigation = computed(() => getNavigationForRole('server'))
const searchQuery = ref('')
const selectedCategory = ref('')
const selectedStock = ref('')

const categories = computed(() => {
    return [...new Set(props.foodItems?.map(item => item.category) || [])]
})

const filteredItems = computed(() => {
    return props.foodItems?.filter(item => {
        const matchesSearch = item.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                            item.category.toLowerCase().includes(searchQuery.value.toLowerCase())
        const matchesCategory = !selectedCategory.value || item.category === selectedCategory.value
        const matchesStock = !selectedStock.value ||
            (selectedStock.value === 'in-stock' && item.stock_quantity > item.min_stock_level) ||
            (selectedStock.value === 'low-stock' && item.stock_quantity <= item.min_stock_level && item.stock_quantity > 0) ||
            (selectedStock.value === 'out-of-stock' && item.stock_quantity === 0)

        return matchesSearch && matchesCategory && matchesStock
    }) || []
})

const resetFilters = () => {
    searchQuery.value = ''
    selectedCategory.value = ''
    selectedStock.value = ''
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

const addToOrder = (itemId) => {
    console.log('Add to order:', itemId)
    // This will integrate with POS system
}
</script>
