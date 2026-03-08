<template>
    <DashboardLayout title="Drinks Menu" :user="user" :navigation="navigation">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold"
                    :style="{ color: themeColors.textPrimary }">🍹 Drinks Menu</h1>
                <p class="text-sm mt-2"
                   :style="{ color: themeColors.textSecondary }">Manage bar beverages and cocktails</p>
            </div>
            <div>
                <span class="px-4 py-2 rounded-lg font-bold"
                      :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)', color: themeColors.primary }">
                    {{ drinks.length }} Drinks Available
                </span>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-8 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2"
                       :style="{ color: themeColors.textSecondary }">Search</label>
                <input type="text" v-model="searchQuery"
                       placeholder="Search drinks..."
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

        <!-- Drinks Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div v-if="filteredDrinks.length === 0" class="col-span-full text-center py-12"
                 :style="{ backgroundColor: themeColors.card, borderRadius: '0.5rem', border: `1px solid ${themeColors.border}` }">
                <p class="text-lg font-medium"
                   :style="{ color: themeColors.textPrimary }">No drinks found</p>
                <p class="text-sm mt-2"
                   :style="{ color: themeColors.textSecondary }">Try adjusting your filters</p>
            </div>

            <div v-for="drink in filteredDrinks" :key="drink.id"
                 class="rounded-lg border shadow-sm overflow-hidden hover:shadow-md transition"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <!-- Drink Image/Icon -->
                <div class="h-32 flex items-center justify-center text-5xl"
                     :style="{ backgroundColor: 'rgba(59, 130, 246, 0.05)' }">
                    {{ drink.emoji || '🍹' }}
                </div>

                <!-- Drink Info -->
                <div class="p-4">
                    <h3 class="text-lg font-bold mb-1"
                        :style="{ color: themeColors.textPrimary }">{{ drink.name }}</h3>
                    <p class="text-sm mb-3"
                       :style="{ color: themeColors.textSecondary }">{{ drink.category }}</p>

                    <!-- Price -->
                    <div class="mb-3 pb-3" :style="{ borderBottom: `1px solid ${themeColors.border}` }">
                        <p class="text-xs font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Price</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.success }">{{ formatCurrency(drink.price) }}</p>
                    </div>

                    <!-- Stock -->
                    <div class="mb-3 pb-3" :style="{ borderBottom: `1px solid ${themeColors.border}` }">
                        <p class="text-xs font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Stock</p>
                        <div class="flex items-center justify-between">
                            <span class="font-bold"
                                  :style="{ color: getStockColor(drink.stock_quantity, drink.min_stock_level) }">
                                {{ drink.stock_quantity }} {{ drink.unit }}
                            </span>
                            <span class="px-2 py-1 rounded text-xs font-medium"
                                  :style="{
                                      backgroundColor: getStockBgColor(drink.stock_quantity, drink.min_stock_level),
                                      color: getStockColor(drink.stock_quantity, drink.min_stock_level)
                                  }">
                                {{ getStockStatus(drink.stock_quantity, drink.min_stock_level) }}
                            </span>
                        </div>
                    </div>

                    <!-- Cost & Margin -->
                    <div class="mb-4">
                        <div class="flex justify-between text-sm mb-1">
                            <span :style="{ color: themeColors.textSecondary }">Cost:</span>
                            <span :style="{ color: themeColors.textPrimary }">{{ formatCurrency(drink.cost_price) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span :style="{ color: themeColors.textSecondary }">Margin:</span>
                            <span class="font-bold"
                                  :style="{ color: themeColors.success }">{{ drink.margin_percentage || 0 }}%</span>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <button @click="viewDrinkDetails(drink.id)"
                            class="w-full px-4 py-2 rounded-lg font-medium transition"
                            :style="{
                                backgroundColor: themeColors.primary,
                                color: '#fff'
                            }">
                        View Details
                    </button>
                </div>
            </div>
        </div>

        <!-- Drink Details Modal -->
        <div v-if="selectedDrink"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             :style="{ backgroundColor: 'rgba(0, 0, 0, 0.5)' }"
             @click.self="selectedDrink = null">
            <div class="rounded-lg w-full max-w-md"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     border: `1px solid ${themeColors.border}`
                 }">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b"
                     :style="{ borderColor: themeColors.border }">
                    <h2 class="text-2xl font-bold"
                        :style="{ color: themeColors.textPrimary }">{{ selectedDrink.name }}</h2>
                    <button @click="selectedDrink = null"
                            class="text-2xl leading-none"
                            :style="{ color: themeColors.textTertiary }">×</button>
                </div>

                <!-- Modal Content -->
                <div class="p-6 space-y-4">
                    <!-- Emoji Icon -->
                    <div class="text-5xl text-center mb-4">{{ selectedDrink.emoji || '🍹' }}</div>

                    <!-- Category -->
                    <div>
                        <label class="text-sm font-medium block mb-1"
                               :style="{ color: themeColors.textSecondary }">Category</label>
                        <p :style="{ color: themeColors.textPrimary }">{{ selectedDrink.category }}</p>
                    </div>

                    <!-- Code -->
                    <div>
                        <label class="text-sm font-medium block mb-1"
                               :style="{ color: themeColors.textSecondary }">Product Code</label>
                        <p :style="{ color: themeColors.textPrimary }">{{ selectedDrink.code }}</p>
                    </div>

                    <!-- Price -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium block mb-1"
                                   :style="{ color: themeColors.textSecondary }">Selling Price</label>
                            <p class="text-lg font-bold"
                               :style="{ color: themeColors.success }">{{ formatCurrency(selectedDrink.price) }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium block mb-1"
                                   :style="{ color: themeColors.textSecondary }">Cost Price</label>
                            <p class="text-lg font-bold"
                               :style="{ color: themeColors.textPrimary }">{{ formatCurrency(selectedDrink.cost_price) }}</p>
                        </div>
                    </div>

                    <!-- Profit Margin -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium block mb-1"
                                   :style="{ color: themeColors.textSecondary }">Profit Margin</label>
                            <p class="text-lg font-bold"
                               :style="{ color: themeColors.warning }">{{ selectedDrink.margin_percentage || 0 }}%</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium block mb-1"
                                   :style="{ color: themeColors.textSecondary }">Profit Amount</label>
                            <p class="text-lg font-bold"
                               :style="{ color: themeColors.success }">{{ formatCurrency(selectedDrink.price - selectedDrink.cost_price) }}</p>
                        </div>
                    </div>

                    <!-- Stock Information -->
                    <div>
                        <label class="text-sm font-medium block mb-1"
                               :style="{ color: themeColors.textSecondary }">Stock Level</label>
                        <div class="flex items-center justify-between">
                            <p class="font-bold"
                               :style="{ color: getStockColor(selectedDrink.stock_quantity, selectedDrink.min_stock_level) }">
                                {{ selectedDrink.stock_quantity }} {{ selectedDrink.unit }}
                            </p>
                            <span class="px-3 py-1 rounded text-xs font-medium"
                                  :style="{
                                      backgroundColor: getStockBgColor(selectedDrink.stock_quantity, selectedDrink.min_stock_level),
                                      color: getStockColor(selectedDrink.stock_quantity, selectedDrink.min_stock_level)
                                  }">
                                {{ getStockStatus(selectedDrink.stock_quantity, selectedDrink.min_stock_level) }}
                            </span>
                        </div>
                        <p class="text-sm mt-2"
                           :style="{ color: themeColors.textSecondary }">Minimum Level: {{ selectedDrink.min_stock_level }} {{ selectedDrink.unit }}</p>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-6 border-t flex gap-2"
                     :style="{ borderColor: themeColors.border }">
                    <button @click="selectedDrink = null"
                            class="flex-1 px-4 py-2 rounded-lg font-medium transition"
                            :style="{
                                backgroundColor: themeColors.background,
                                color: themeColors.textPrimary,
                                border: `1px solid ${themeColors.border}`
                            }">
                        Close
                    </button>
                </div>
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
    drinks: Array,
    currency: String,
    currencyPosition: String,
})

const navigation = computed(() => getNavigationForRole('bartender'))
const searchQuery = ref('')
const selectedCategory = ref('')
const selectedStock = ref('')
const selectedDrink = ref(null)

const categories = computed(() => {
    return [...new Set(props.drinks?.map(d => d.category) || [])]
})

const filteredDrinks = computed(() => {
    return props.drinks?.filter(drink => {
        const matchesSearch = drink.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                            drink.category.toLowerCase().includes(searchQuery.value.toLowerCase())
        const matchesCategory = !selectedCategory.value || drink.category === selectedCategory.value
        const matchesStock = !selectedStock.value ||
            (selectedStock.value === 'in-stock' && drink.stock_quantity > drink.min_stock_level) ||
            (selectedStock.value === 'low-stock' && drink.stock_quantity <= drink.min_stock_level && drink.stock_quantity > 0) ||
            (selectedStock.value === 'out-of-stock' && drink.stock_quantity === 0)

        return matchesSearch && matchesCategory && matchesStock
    }) || []
})

const resetFilters = () => {
    searchQuery.value = ''
    selectedCategory.value = ''
    selectedStock.value = ''
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

const viewDrinkDetails = (drinkId) => {
    const drink = props.drinks?.find(d => d.id === drinkId)
    if (drink) {
        selectedDrink.value = drink
    }
}
</script>
