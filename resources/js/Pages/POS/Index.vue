<template>
  <DashboardLayout :title="`POS Terminal - ${hotelName}`" :user="user" :navigation="navigation">
    <div class="pos-container" :style="{ backgroundColor: themeColors.background }">
      <!-- Header Stats Bar -->
      <div class="pos-header" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
        <div class="header-left">
          <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">POS Terminal</h1>
          <span class="hotel-name" :style="{ color: themeColors.textSecondary }">{{ hotelName }}</span>
        </div>
        <div class="header-stats">
          <div class="stat-item">
            <span class="stat-label" :style="{ color: themeColors.textTertiary }">Today's Sales</span>
            <span class="stat-value" :style="{ color: themeColors.primary }">{{ formatCurrency(todaySales) }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-label" :style="{ color: themeColors.textTertiary }">Transactions</span>
            <span class="stat-value" :style="{ color: themeColors.textPrimary }">{{ todaySalesCount }}</span>
          </div>
          <div class="stat-item" v-if="activeSession">
            <span class="stat-label" :style="{ color: themeColors.textTertiary }">Drawer Status</span>
            <span class="stat-value status-open" :style="{ color: themeColors.success }">OPEN</span>
          </div>
          <div class="stat-item" v-else>
            <span class="stat-label" :style="{ color: themeColors.textTertiary }">Drawer Status</span>
            <span class="stat-value status-closed" :style="{ color: themeColors.danger }">CLOSED</span>
          </div>
        </div>
      </div>

      <div class="pos-main">
        <!-- Left Panel: Products -->
        <div class="pos-products-panel" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
          <!-- Product Search Bar -->
          <div class="product-search-container">
            <div class="search-header">
              <h3 class="search-title" :style="{ color: themeColors.textPrimary }">Products</h3>
              <div class="search-stats">
                <span class="product-count" :style="{ backgroundColor: themeColors.background, color: themeColors.textSecondary }">{{ filteredProducts.length }} items</span>
              </div>
            </div>
            <div class="search-input-wrapper">
              <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
              <input
                type="text"
                v-model="productSearch"
                placeholder="Scan barcode or search by name, category, price..."
                class="search-input"
                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"
                @keyup.enter="handleBarcodeSearch"
                @input="handleSearchInput"
                @focus="handleSearchFocus"
                ref="searchInput"
                autocomplete="off"
              />
              <button v-if="productSearch" @click="clearSearch" class="clear-btn" :style="{ color: themeColors.textTertiary }">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
          </div>

          <!-- Category Filter -->
          <div class="category-filter" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
            <div class="category-header">
              <h4 class="category-title" :style="{ color: themeColors.textPrimary }">Categories</h4>
            </div>
            <div class="category-pills">
              <button
                v-for="category in categories"
                :key="category.id"
                class="category-pill"
                :class="{ active: selectedCategory === category.id }"
                @click="selectCategory(category.id)"
                :style="{
                  backgroundColor: selectedCategory === category.id ? category.color || themeColors.primary : 'transparent',
                  borderColor: category.color || themeColors.primary,
                  color: selectedCategory === category.id ? getContrastColor(category.color || themeColors.primary) : (category.color || themeColors.primary)
                }"
              >
                <span class="category-emoji">{{ category.emoji || '📦' }}</span>
                <span class="category-name">{{ category.name }}</span>
                <span class="category-count">{{ category.products_count }}</span>
              </button>
              <button
                class="category-pill"
                :class="{ active: selectedCategory === 'all' }"
                @click="selectCategory('all')"
                :style="{ background: themeColors.primary, borderColor: themeColors.primary, color: getContrastColor(themeColors.primary) }"
              >
                <span class="category-emoji">📦</span>
                <span class="category-name">All</span>
                <span class="category-count">{{ allProductsCount }}</span>
              </button>
            </div>
          </div>

          <!-- Products Grid -->
          <div class="products-container" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
            <div v-if="filteredProducts.length === 0" class="no-products">
              <div class="no-products-icon">🔍</div>
              <h4 class="no-products-title" :style="{ color: themeColors.textPrimary }">No products found</h4>
              <p class="no-products-text" :style="{ color: themeColors.textSecondary }">Try adjusting your search or category filter</p>
            </div>
            <div v-else class="products-grid">
              <div
                v-for="product in filteredProducts"
                :key="product.id"
                class="product-item"
                :class="{
                  'low-stock': product.is_low_stock,
                  'out-of-stock': product.stock_quantity <= 0
                }"
                @click="addToCart(product)"
                :style="{
                  backgroundColor: themeColors.card,
                  borderColor: product.category_color || themeColors.border
                }"
              >
                <div class="product-visual">
                  <div class="product-icon">{{ product.emoji || '📦' }}</div>
                  <div class="product-badge" v-if="product.stock_quantity <= 0" :style="{ backgroundColor: themeColors.danger, color: getContrastColor(themeColors.danger) }">
                    <span>Out of Stock</span>
                  </div>
                  <div class="product-badge warning" v-else-if="product.is_low_stock" :style="{ backgroundColor: themeColors.warning, color: getContrastColor(themeColors.warning) }">
                    <span>Low Stock</span>
                  </div>
                </div>
                <div class="product-details">
                  <h5 class="product-title" :style="{ color: themeColors.textPrimary }">{{ product.name }}</h5>
                  <div class="product-meta">
                    <span class="product-price-tag" :style="{ color: themeColors.primary }">{{ formatCurrency(product.price) }}</span>
                    <span class="stock-indicator" :class="{ 'critical': product.stock_quantity <= 0, 'warning': product.stock_quantity <= 5 }" :style="{ color: product.stock_quantity <= 0 ? themeColors.danger : (product.stock_quantity <= 5 ? themeColors.warning : themeColors.textSecondary) }">
                      {{ product.stock_quantity }} left
                    </span>
                  </div>
                </div>
                <div class="product-action">
                  <button
                    class="add-to-cart-btn"
                    :disabled="product.stock_quantity <= 0"
                    :style="{ backgroundColor: themeColors.primary, color: getContrastColor(themeColors.primary) }"
                    @click.stop="addToCart(product)"
                  >
                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span>Add</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Panel: Cart -->
        <div class="pos-cart-panel" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, boxShadow: themeColors.shadow }">
          <!-- Customer Selection -->
          <div class="customer-section">
            <label :style="{ color: themeColors.textSecondary }">Customer</label>
            <select v-model="cart.customer_id" class="form-input" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
              <option value="" :style="{ color: themeColors.textPrimary }">Walk-in Customer</option>
              <optgroup v-for="group in customersGrouped" :key="group.type" :label="group.label" :style="{ color: themeColors.textPrimary }">
                <option
                  v-for="customer in group.customers"
                  :key="customer.id"
                  :value="customer.id"
                  :style="{ color: themeColors.textPrimary }"
                >
                  {{ customer.full_name }}
                </option>
              </optgroup>
            </select>
          </div>

          <!-- Cart Items -->
          <div class="cart-items" :style="{ borderColor: themeColors.border }">
            <div v-if="cart.items.length === 0" class="empty-cart" :style="{ color: themeColors.textTertiary }">
              <span style="font-size: 3rem;">🛒</span>
              <p>Click products to add them here</p>
            </div>
            <div
              v-for="(item, index) in cart.items"
              :key="index"
              class="cart-item"
              :style="{ borderColor: themeColors.border }"
            >
              <div class="item-info">
                <span class="item-name" :style="{ color: themeColors.textPrimary }">{{ item.name }}</span>
                <span class="item-price" :style="{ color: themeColors.textSecondary }">{{ formatCurrency(item.price) }} each</span>
              </div>
              <div class="item-controls">
                <button @click="updateQuantity(index, -1)" class="qty-btn" :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background, color: themeColors.textPrimary }">-</button>
                <span class="qty" :style="{ color: themeColors.textPrimary }">{{ item.quantity }}</span>
                <button @click="updateQuantity(index, 1)" class="qty-btn" :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background, color: themeColors.textPrimary }">+</button>
                <button @click="removeItem(index)" class="remove-btn" :style="{ backgroundColor: themeColors.danger + '20', color: themeColors.danger }">×</button>
              </div>
              <div class="item-total" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(item.price * item.quantity) }}</div>
            </div>
          </div>

          <!-- Cart Summary -->
          <div class="cart-summary" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
            <div class="summary-row" :style="{ color: themeColors.textSecondary }">
              <span>Subtotal</span>
              <span>{{ formatCurrency(cartSubtotal) }}</span>
            </div>
            <div class="summary-row" v-if="cartDiscount > 0" :style="{ color: themeColors.textSecondary }">
              <span>Discount ({{ cartDiscount }}%)</span>
              <span class="discount" :style="{ color: themeColors.success }">-{{ formatCurrency(cartDiscountAmount) }}</span>
            </div>
            <div class="summary-row" :style="{ color: themeColors.textSecondary }">
              <span>Tax ({{ taxRate }}%)</span>
              <span>{{ formatCurrency(cartTax) }}</span>
            </div>
            <div class="summary-row total" :style="{ borderColor: themeColors.border, color: themeColors.textPrimary }">
              <span>Total</span>
              <span>{{ formatCurrency(cartTotal) }}</span>
            </div>
          </div>

          <!-- Payment Section -->
          <div class="payment-section" :style="{ borderColor: themeColors.border }">
            <div class="payment-methods">
              <button
                v-for="method in paymentMethods"
                :key="method.value"
                class="payment-btn"
                :class="{ active: cart.payment_method === method.value }"
                @click="cart.payment_method = method.value"
                :style="{
                  borderColor: cart.payment_method === method.value ? themeColors.primary : themeColors.border,
                  backgroundColor: cart.payment_method === method.value ? themeColors.primary + '20' : themeColors.background,
                  color: cart.payment_method === method.value ? themeColors.primary : themeColors.textSecondary
                }"
              >
                {{ method.icon }} {{ method.label }}
              </button>
            </div>

            <!-- Room Charge Option -->
            <div class="room-charge-option" v-if="cart.customer_id && cart.customer_id.toString().startsWith('guest_')">
              <label class="checkbox-label" :style="{ color: themeColors.textSecondary }">
                <input type="checkbox" v-model="cart.is_charged_to_room" :style="{ accentColor: themeColors.primary }" />
                <span>Charge to Room</span>
              </label>
            </div>

            <!-- Discount Input -->
            <div class="discount-input">
              <label :style="{ color: themeColors.textSecondary }">Manual Discount</label>
              <input
                type="number"
                v-model.number="cart.discount_amount"
                min="0"
                step="0.01"
                placeholder="0.00"
                class="form-input"
                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"
              />
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
              <button @click="clearCart" class="btn btn-secondary" :style="{ borderColor: themeColors.border, color: themeColors.textSecondary, backgroundColor: themeColors.background }">Clear</button>
              <button
                @click="processSale"
                class="btn btn-primary"
                :disabled="cart.items.length === 0 || isProcessing"
                :style="{ backgroundColor: themeColors.primary, color: getContrastColor(themeColors.primary) }"
              >
                {{ isProcessing ? 'Processing...' : `Pay ${formatCurrency(cartTotal)}` }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Cash Drawer Modal -->
    <DialogModal :show="showDrawerModal" @close="showDrawerModal = false" max-width="md">
      <template #title :style="{ color: themeColors.textPrimary }">Cash Drawer</template>
      <template #content>
        <div v-if="!activeSession">
          <p class="mb-4" :style="{ color: themeColors.textSecondary }">Opening Balance</p>
          <input
            type="number"
            v-model.number="openingBalance"
            min="0"
            step="0.01"
            class="form-input w-full"
            placeholder="0.00"
            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"
          />
        </div>
        <div v-else>
          <p class="mb-4" :style="{ color: themeColors.textSecondary }">Closing Balance</p>
          <input
            type="number"
            v-model.number="closingBalance"
            min="0"
            step="0.01"
            class="form-input w-full"
            placeholder="0.00"
            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"
          />
          <p class="mt-2 text-sm" :style="{ color: themeColors.textTertiary }">
            Expected: {{ formatCurrency(activeSession.opening_balance + todaySales) }}
          </p>
        </div>
      </template>
      <template #footer>
        <button @click="showDrawerModal = false" class="btn btn-secondary" :style="{ borderColor: themeColors.border, color: themeColors.textSecondary }">Cancel</button>
        <button @click="handleDrawerAction" class="btn btn-primary" :style="{ backgroundColor: themeColors.primary, color: getContrastColor(themeColors.primary) }">
          {{ activeSession ? 'Close Drawer' : 'Open Drawer' }}
        </button>
      </template>
    </DialogModal>

    <!-- Success Modal -->
    <DialogModal :show="showSuccessModal" @close="closeSuccessModal" max-width="md">
      <template #title :style="{ color: themeColors.textPrimary }">Sale Completed!</template>
      <template #content>
        <div class="text-center">
          <div style="font-size: 4rem; margin-bottom: 1rem;">✅</div>
          <p class="text-xl font-bold" :style="{ color: themeColors.textPrimary }">Transaction Successful</p>
          <p class="text-lg" :style="{ color: themeColors.textSecondary }">Sale #: {{ lastSale?.sale_number }}</p>
          <p class="text-2xl font-bold mt-4" :style="{ color: themeColors.primary }">{{ formatCurrency(lastSale?.total_amount) }}</p>
          <button @click="printReceipt" class="btn btn-primary mt-4" :style="{ backgroundColor: themeColors.primary, color: getContrastColor(themeColors.primary) }">
            🖨️ Print Receipt
          </button>
        </div>
      </template>
      <template #footer>
        <button @click="closeSuccessModal" class="btn btn-secondary" :style="{ borderColor: themeColors.border, color: themeColors.textSecondary }">Close</button>
      </template>
    </DialogModal>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from "vue"
import DashboardLayout from "@/Layouts/DashboardLayout.vue"
import DialogModal from "@/Components/DialogModal.vue"
import { formatCurrency as formatCurrencyUtil, initializeCurrencySettings } from "@/Utils/currency.js"
import { useTheme } from "@/Composables/useTheme.js"
import { printPopup } from "@/Utils/printReceipt.js"

const { themeColors: originalThemeColors, currentTheme } = useTheme()

// Safe theme colors with fallbacks
const themeColors = computed(() => ({
  background: originalThemeColors?.background || '#ffffff',
  card: originalThemeColors?.card || '#ffffff',
  border: originalThemeColors?.border || '#e2e8f0',
  textPrimary: originalThemeColors?.textPrimary || '#1e293b',
  textSecondary: originalThemeColors?.textSecondary || '#64748b',
  textTertiary: originalThemeColors?.textTertiary || '#94a3b8',
  primary: originalThemeColors?.primary || '#3b82f6',
  secondary: originalThemeColors?.secondary || '#64748b',
  success: originalThemeColors?.success || '#22c55e',
  warning: originalThemeColors?.warning || '#f59e0b',
  danger: originalThemeColors?.danger || '#ef4444',
  hover: currentTheme?.value?.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)',
  shadow: currentTheme?.value?.theme_mode === 'dark'
    ? '0 8px 20px rgba(0, 0, 0, 0.35)'
    : '0 8px 20px rgba(0, 0, 0, 0.08)',
}))

const props = defineProps({
  user: Object,
  navigation: Array,
  categories: Array,
  products: Array,
  customers: Array,
  activeSession: Object,
  canManageInventory: Boolean,
  canManageExpenses: Boolean,
  taxRate: Number,
  todaySales: Number,
  todaySalesCount: Number,
  hotelName: String,
  hotelAddress: String,
  hotelPhone: String,
  hotelEmail: String
})

const selectedCategory = ref("all")
const previousCategory = ref(null)
const productSearch = ref("")
const searchInput = ref(null)
const showDrawerModal = ref(false)
const showSuccessModal = ref(false)
const isProcessing = ref(false)
const openingBalance = ref(0)
const closingBalance = ref(0)
const lastSale = ref(null)

const cart = ref({
  items: [],
  customer_id: "",
  payment_method: "cash",
  is_charged_to_room: false,
  discount_amount: 0,
  is_walk_in: true
})

const paymentMethods = [
  { value: "cash", label: "Cash", icon: "💵" },
  { value: "card", label: "Card", icon: "💳" },
  { value: "bank_transfer", label: "Bank", icon: "🏦" },
  { value: "mobile", label: "Mobile", icon: "📱" }
]

const filteredProducts = computed(() => {
  let products = props.products
  if (selectedCategory.value !== "all" && selectedCategory.value !== "search") {
    products = products.filter((p) => p.category_id === selectedCategory.value)
  }
  if (productSearch.value && productSearch.value.trim() !== "") {
    const search = productSearch.value.toLowerCase().trim()
    products = products.filter((p) => {
      const nameMatch = p.name && p.name.toLowerCase().includes(search)
      const barcodeMatch = p.barcode && p.barcode.toLowerCase().includes(search)
      const skuMatch = p.sku && p.sku.toLowerCase().includes(search)
      const categoryMatch = p.category_name && p.category_name.toLowerCase().includes(search)
      const priceMatch =
        p.price &&
        (p.price.toString().includes(search) ||
          p.price.toString() === search ||
          formatCurrencyUtil(p.price).toLowerCase().includes(search))
      return nameMatch || barcodeMatch || skuMatch || categoryMatch || priceMatch
    })
  }
  return products
})

const customersGrouped = computed(() => {
  const regular = props.customers.filter((c) => c.type === "customer")
  const guests = props.customers.filter((c) => c.type === "guest")
  return [
    { type: "customer", label: "Customers", customers: regular },
    { type: "guest", label: "Hotel Guests", customers: guests }
  ].filter((g) => g.customers.length > 0)
})

const cartSubtotal = computed(() => {
  return cart.value.items.reduce((sum, item) => sum + item.price * item.quantity, 0)
})

const cartDiscount = computed(() => {
  const customer = props.customers.find((c) => c.id === cart.value.customer_id)
  if (customer && customer.customer_group && !cart.value.is_walk_in) {
    return customer.customer_group.discount_percentage || 0
  }
  return 0
})

const cartDiscountAmount = computed(() => {
  return cartSubtotal.value * (cartDiscount.value / 100)
})

const cartTax = computed(() => {
  const taxableAmount = cartSubtotal.value - cartDiscountAmount.value
  return taxableAmount * (props.taxRate / 100)
})

const cartTotal = computed(() => {
  const taxableAmount = cartSubtotal.value - cartDiscountAmount.value
  return taxableAmount + cartTax.value - (cart.value.discount_amount || 0)
})

const allProductsCount = computed(() => props.products.length)

const formatCurrency = (amount, currency = null, position = null) => {
  return formatCurrencyUtil(amount || 0, currency, position)
}

const getContrastColor = (hexColor) => {
  if (!hexColor || typeof hexColor !== "string") return "#ffffff"
  try {
    const hex = hexColor.replace("#", "")
    const r = parseInt(hex.substr(0, 2), 16)
    const g = parseInt(hex.substr(2, 2), 16)
    const b = parseInt(hex.substr(4, 2), 16)
    const brightness = (r * 299 + g * 587 + b * 114) / 1000
    return brightness > 128 ? "#000000" : "#ffffff"
  } catch {
    return "#ffffff"
  }
}

const selectCategory = (categoryId) => {
  selectedCategory.value = categoryId
  if (productSearch.value) productSearch.value = ""
}

const handleBarcodeSearch = () => {
  if (productSearch.value && productSearch.value.trim() !== "") {
    const searchResults = filteredProducts.value
    if (searchResults.length === 1) {
      const product = searchResults[0]
      if (product.stock_quantity > 0) {
        addToCart(product)
        productSearch.value = ""
      }
    }
  }
}

const handleSearchInput = () => {
  if (productSearch.value && productSearch.value.trim() !== "") {
    if (selectedCategory.value !== "all") {
      if (!previousCategory.value) previousCategory.value = selectedCategory.value
      selectedCategory.value = "all"
    }
  }
}

const handleSearchFocus = () => {
  searchInput.value?.select()
}

const clearSearch = () => {
  productSearch.value = ""
  if (previousCategory.value) {
    selectedCategory.value = previousCategory.value
    previousCategory.value = null
  }
  if (searchInput.value) searchInput.value.focus()
}

const addToCart = (product) => {
  if (product.stock_quantity <= 0) return
  const existingIndex = cart.value.items.findIndex((item) => item.id === product.id)
  if (existingIndex >= 0) {
    if (cart.value.items[existingIndex].quantity < product.stock_quantity) {
      cart.value.items[existingIndex].quantity++
    }
  } else {
    cart.value.items.push({
      id: product.id,
      name: product.name,
      price: product.price,
      quantity: 1,
      stock_quantity: product.stock_quantity
    })
  }
}

const updateQuantity = (index, delta) => {
  const newQty = cart.value.items[index].quantity + delta
  if (newQty > 0 && newQty <= cart.value.items[index].stock_quantity) {
    cart.value.items[index].quantity = newQty
  }
}

const removeItem = (index) => {
  cart.value.items.splice(index, 1)
}

const clearCart = () => {
  cart.value = {
    items: [],
    customer_id: "",
    payment_method: "cash",
    is_charged_to_room: false,
    discount_amount: 0,
    is_walk_in: true
  }
}

const processSale = async () => {
  isProcessing.value = true
  try {
    const response = await fetch("/pos/process-sale", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
      },
      body: JSON.stringify({
        items: cart.value.items.map((item) => ({
          product_id: item.id,
          quantity: item.quantity,
          unit_price: item.price
        })),
        customer_id: cart.value.customer_id,
        payment_method: cart.value.payment_method,
        is_charged_to_room: cart.value.is_charged_to_room,
        discount_amount: cart.value.discount_amount,
        is_walk_in: !cart.value.customer_id
      })
    })
    if (response.ok) {
      const result = await response.json()
      lastSale.value = result.sale
      showSuccessModal.value = true
      clearCart()
    }
  } catch (error) {
    console.error("Sale failed:", error)
    alert(error.message || "Sale failed. Please try again.")
  } finally {
    isProcessing.value = false
  }
}

const handleDrawerAction = async () => {
  try {
    let response
    if (props.activeSession) {
      response = await fetch("/pos/close-drawer", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
        },
        body: JSON.stringify({ closing_balance: closingBalance.value })
      })
    } else {
      response = await fetch("/pos/open-drawer", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
        },
        body: JSON.stringify({ opening_balance: openingBalance.value })
      })
    }
    
    const data = await response.json()
    
    if (!response.ok || !data.success) {
      throw new Error(data.message || 'Failed to process drawer action')
    }
    
    showDrawerModal.value = false
    window.location.reload()
  } catch (error) {
    console.error('Drawer action failed:', error)
    alert(error.message || 'Failed to open/close drawer. Please try again.')
  }
}

const closeSuccessModal = () => {
  showSuccessModal.value = false
  lastSale.value = null
}

const printReceipt = () => {
  if (!lastSale.value) return
  const s = lastSale.value
  const fmt = (v) => formatCurrency(v ?? 0)
  const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric' }) : ''
  const fmtTime = (d) => d ? new Date(d).toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit', hour12:true }) : ''
  const items = (s.items || []).map(i => `
    <div class="receipt-item">
      <div class="receipt-item-name"><span class="item-emoji">${i.product?.emoji || '\uD83C\uDF7D\uFE0F'}</span><span>${i.product?.name || 'Item'}</span></div>
      <div class="receipt-item-details"><span class="receipt-item-quantity">${i.quantity} × ${fmt(i.unit_price)}</span><span class="receipt-item-total">${fmt(i.total_price)}</span></div>
    </div>`).join('')
  const html = `
    <div class="receipt-header">
      <div class="receipt-hotel-name">${props.hotelName || 'Hotel'}</div>
      ${props.hotelAddress ? `<div class="receipt-address">${props.hotelAddress}</div>` : ''}
      ${props.hotelPhone ? `<div class="receipt-phone">Tel: ${props.hotelPhone}</div>` : ''}
      ${props.hotelEmail ? `<div class="receipt-email">${props.hotelEmail}</div>` : ''}
      <div class="receipt-divider"></div>
    </div>
    <div class="receipt-info">
      <div class="receipt-row"><span>Receipt #:</span><span class="receipt-number">${s.sale_number || ''}</span></div>
      <div class="receipt-row"><span>Date:</span><span>${fmtDate(s.sale_date || s.created_at)}</span></div>
      <div class="receipt-row"><span>Time:</span><span>${fmtTime(s.sale_date || s.created_at)}</span></div>
      ${s.user ? `<div class="receipt-row"><span>Staff:</span><span>${s.user.first_name || ''} ${s.user.last_name || ''}</span></div>` : ''}
      <div class="receipt-row"><span>Payment:</span><span>${s.payment_method || 'Cash'}</span></div>
      <div class="receipt-divider"></div>
    </div>
    <div class="receipt-items">
      <div class="receipt-items-header"><span>Item</span><span>Total</span></div>
      ${items}
      <div class="receipt-divider"></div>
    </div>
    <div class="receipt-totals">
      <div class="receipt-total-row"><span>Subtotal:</span><span>${fmt(s.subtotal)}</span></div>
      ${(s.tax_amount > 0) ? `<div class="receipt-total-row"><span>Tax:</span><span>${fmt(s.tax_amount)}</span></div>` : ''}
      ${(s.discount_amount > 0) ? `<div class="receipt-total-row discount"><span>Discount:</span><span>-${fmt(s.discount_amount)}</span></div>` : ''}
      <div class="receipt-total-row grand-total"><span>TOTAL:</span><span>${fmt(s.total_amount)}</span></div>
    </div>
    <div class="receipt-footer">
      <div class="receipt-divider"></div>
      <div class="receipt-thank-you">Thank you!</div>
    </div>`
  // inject into hidden div then print
  let el = document.getElementById('pos-receipt-popup')
  if (!el) { el = document.createElement('div'); el.id = 'pos-receipt-popup'; el.style.display = 'none'; document.body.appendChild(el) }
  el.innerHTML = html
  printPopup('pos-receipt-popup', `Receipt – ${s.sale_number || ''}`, '80mm')
}
</script>

<style scoped>
.pos-container {
  height: calc(100vh - 80px);
  display: flex;
  flex-direction: column;
}

.pos-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
  border-bottom: 2px solid;
  margin-bottom: 16px;
}

.header-left h1 {
  margin: 0;
}

.hotel-name {
  font-size: 0.875rem;
}

.header-stats {
  display: flex;
  gap: 32px;
}

.stat-item {
  text-align: center;
}

.stat-label {
  display: block;
  font-size: 0.75rem;
  text-transform: uppercase;
}

.stat-value {
  font-size: 1.25rem;
  font-weight: 700;
}

.status-open {
  color: inherit;
}

.status-closed {
  color: inherit;
}

.pos-main {
  display: flex;
  gap: 24px;
  flex: 1;
  min-height: 0;
}

.pos-products-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 16px;
}

.search-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.search-title {
  font-size: 1.125rem;
  font-weight: 600;
  margin: 0;
}

.search-stats {
  display: flex;
  align-items: center;
  gap: 8px;
}

.product-count {
  font-size: 0.875rem;
  padding: 4px 8px;
  border-radius: 6px;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 12px;
  width: 16px;
  height: 16px;
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 10px 12px 10px 40px;
  border: 1px solid;
  border-radius: 8px;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.search-input:focus {
  outline: none;
}

.search-input::placeholder {
  color: var(--kotel-text-tertiary);
}

.clear-btn {
  position: absolute;
  right: 8px;
  padding: 4px;
  background: none;
  border: none;
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.2s;
}

.category-filter {
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 16px;
}

.category-header {
  margin-bottom: 12px;
}

.category-title {
  font-size: 1rem;
  font-weight: 600;
  margin: 0;
}

.category-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.category-pill {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  border: 1px solid;
  border-radius: 20px;
  background: transparent;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.category-pill:hover {
  transform: translateY(-1px);
  box-shadow: none;
}

.category-emoji {
  font-size: 0.875rem;
}

.category-name {
  color: inherit;
}

.category-count {
  background: var(--kotel-border);
  padding: 2px 6px;
  border-radius: 10px;
  font-size: 0.75rem;
  font-weight: 600;
}

.products-container {
  flex: 1;
  overflow-y: auto;
  border-radius: 12px;
  padding: 16px;
}

.no-products {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 48px 24px;
  text-align: center;
}

.no-products-icon {
  font-size: 3rem;
  margin-bottom: 16px;
  opacity: 0.5;
}

.no-products-title {
  font-size: 1.125rem;
  font-weight: 600;
  margin: 0 0 8px 0;
}

.no-products-text {
  font-size: 0.875rem;
  margin: 0;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 2px;
  grid-auto-rows: min-content;
  align-content: start;
}

.product-item {
  border: 1px solid;
  border-radius: 6px;
  padding: 8px;
  cursor: pointer;
  transition: all 0.15s ease;
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-height: 120px;
}

.product-item:hover {
  transform: translateY(-1px);
  box-shadow: none;
}

.product-item.low-stock {
  background: transparent;
}

.product-item.out-of-stock {
  opacity: 0.5;
  cursor: not-allowed;
}

.product-item.out-of-stock:hover {
  transform: none;
  box-shadow: none;
}

.product-visual {
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 2px;
}

.product-icon {
  font-size: 1.75rem;
  line-height: 1;
  text-align: center;
}

.product-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  color: inherit;
  font-size: 0.625rem;
  font-weight: 600;
  padding: 2px 4px;
  border-radius: 3px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  line-height: 1;
}

.product-details {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.product-title {
  font-size: 0.75rem;
  font-weight: 600;
  margin: 0;
  line-height: 1.2;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-align: center;
}

.product-meta {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
}

.product-price-tag {
  font-size: 0.875rem;
  font-weight: 700;
  text-align: center;
}

.stock-indicator {
  font-size: 0.625rem;
  font-weight: 500;
  text-align: center;
}

.product-action {
  display: flex;
  justify-content: center;
  margin-top: auto;
}

.add-to-cart-btn {
  background: var(--kotel-primary);
  border: none;
  border-radius: 4px;
  padding: 4px 8px;
  color: inherit;
  cursor: pointer;
  transition: all 0.15s ease;
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.75rem;
  font-weight: 500;
  min-height: 28px;
}

.add-to-cart-btn:hover:not(:disabled) {
  transform: scale(1.02);
}

.add-to-cart-btn:disabled {
  cursor: not-allowed;
  opacity: 0.4;
}

.btn-icon {
  width: 12px;
  height: 12px;
}

.pos-cart-panel {
  width: 380px;
  border-radius: 12px;
  padding: 16px;
  display: flex;
  flex-direction: column;
}

.customer-section {
  margin-bottom: 16px;
}

.customer-section label {
  display: block;
  font-weight: 600;
  font-size: 0.875rem;
  margin-bottom: 8px;
}

.cart-items {
  flex: 1;
  overflow-y: auto;
  border: 1px solid;
  border-radius: 8px;
  margin-bottom: 16px;
}

.empty-cart {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
}

.cart-item {
  display: flex;
  align-items: center;
  padding: 12px;
  border-bottom: 1px solid;
  gap: 12px;
}

.cart-item:last-child {
  border-bottom: none;
}

.item-info {
  flex: 1;
}

.item-name {
  display: block;
  font-weight: 600;
  font-size: 0.875rem;
}

.item-price {
  font-size: 0.75rem;
}

.item-controls {
  display: flex;
  align-items: center;
  gap: 4px;
}

.qty-btn {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  border: 1px solid;
  background: transparent;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
}

.qty-btn:hover {
  background: var(--kotel-border);
}

.qty {
  min-width: 24px;
  text-align: center;
  font-weight: 600;
}

.remove-btn {
  width: 24px;
  height: 24px;
  border-radius: 4px;
  border: none;
  cursor: pointer;
  font-weight: bold;
  margin-left: 8px;
}

.item-total {
  font-weight: 700;
  min-width: 70px;
  text-align: right;
}

.cart-summary {
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 16px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 4px 0;
  font-size: 0.875rem;
}

.summary-row.total {
  border-top: 2px solid;
  margin-top: 8px;
  padding-top: 8px;
  font-weight: 700;
  font-size: 1.125rem;
}

.summary-row .discount {
  color: inherit;
}

.payment-section {
  border-top: 1px solid;
  padding-top: 16px;
}

.payment-methods {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
  margin-bottom: 12px;
}

.payment-btn {
  padding: 10px;
  border-radius: 8px;
  border: 2px solid;
  background: transparent;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.payment-btn:hover {
  border-color: var(--kotel-primary);
}

.payment-btn.active {
  background: transparent;
}

.room-charge-option {
  margin-bottom: 12px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-weight: 600;
}

.discount-input {
  margin-bottom: 12px;
}

.discount-input label {
  display: block;
  font-weight: 600;
  font-size: 0.875rem;
  margin-bottom: 8px;
}

.form-input {
  width: 100%;
  padding: 10px;
  border: 2px solid;
  border-radius: 8px;
  font-size: 0.875rem;
}

.form-input:focus {
  outline: none;
  border-color: var(--kotel-primary);
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.btn {
  padding: 12px 20px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  flex: 1;
}

.btn-primary {
  background: transparent;
  color: inherit;
  border: none;
}

.btn-primary:hover:not(:disabled) {
  background: transparent;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-secondary {
  background: transparent;
  border: 2px solid;
}

.btn-secondary:hover {
  background: transparent;
}

.text-center {
  text-align: center;
}

.text-xl {
  font-size: 1.25rem;
}

.text-2xl {
  font-size: 1.5rem;
}

.text-lg {
  font-size: 1.125rem;
}

.font-bold {
  font-weight: 700;
}

.mt-2 {
  margin-top: 0.5rem;
}

.mt-4 {
  margin-top: 1rem;
}

.mb-4 {
  margin-bottom: 1rem;
}

.w-full {
  width: 100%;
}

@media (max-width: 1024px) {
  .pos-main {
    flex-direction: column;
  }

  .pos-products-panel {
    max-height: 50vh;
  }

  .pos-cart-panel {
    width: 100%;
  }
}
</style>
