<template>
  <div class="pos-terminal">
    <header class="pos-header">
      <div class="header-left">
        <div class="hotel-brand">
          <span class="brand-icon">🏨</span>
          <div class="brand-info">
            <h1>POS Terminal</h1>
            <span class="hotel-name">{{ hotelName }}</span>
          </div>
        </div>
      </div>
      <div class="header-center">
        <div class="datetime-display">
          <div class="time">{{ currentTime }}</div>
          <div class="date">{{ currentDate }}</div>
        </div>
      </div>
      <div class="header-right">
        <button @click="showCalculator = true" class="btn-header btn-calculator">Calculator</button>
        <button @click="toggleDrawer" class="btn-header btn-drawer">{{ activeSession ? 'Close Drawer' : 'Open Drawer' }}</button>
        <Link href="/admin/dashboard" class="btn-header btn-back">Exit</Link>
      </div>
    </header>
    <div class="stats-bar">
      <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
          <span class="stat-value">{{ formatCurrency(todaySales) }}</span>
          <span class="stat-label">Today's Sales</span>
        </div>
      </div>
      <div class="stat-card stat-card--link" @click="showTransactionsModal = true">
        <div class="stat-icon">🧾</div>
        <div class="stat-content">
          <span class="stat-value">{{ todaySalesCount }}</span>
          <span class="stat-label">Transactions →</span>
        </div>
      </div>
      <div class="stat-card" :class="activeSession ? 'drawer-open' : 'drawer-closed'">
        <div class="stat-icon">{{ activeSession ? '🔓' : '🔒' }}</div>
        <div class="stat-content">
          <span class="stat-value">{{ activeSession ? 'OPEN' : 'CLOSED' }}</span>
          <span class="stat-label">Drawer</span>
        </div>
      </div>
      <div class="stat-card user-card">
        <div class="stat-icon">👤</div>
        <div class="stat-content">
          <span class="stat-value">{{ user?.first_name || 'Staff' }}</span>
          <span class="stat-label">Cashier</span>
        </div>
      </div>
    </div>
    <div class="pos-main">
      <div class="products-panel">
        <div class="search-bar-row">
          <div class="search-input-wrapper">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"></circle>
              <path d="M21 21l-4.35-4.35"></path>
            </svg>
            <input v-model="productSearch" type="text" placeholder="Search products..." class="search-input" @input="handleSearch" />
            <button v-if="productSearch" @click="productSearch = ''; handleSearch()" class="search-clear">✕</button>
          </div>
          <button @click="startBarcodeScanner" class="barcode-btn" :class="{ active: barcodeScannerActive }">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 5h2v14H3zM7 5h1v14H7zM11 5h2v14h-2zM15 5h1v14h-1zM19 5h2v14h-2z"></path>
            </svg>
            {{ barcodeScannerActive ? 'Scanner Active' : 'Barcode' }}
          </button>
        </div>
        <div class="categories-scroll">
          <div class="categories-row">
            <button v-for="cat in categories" :key="cat.id" class="category-btn" :class="{ active: selectedCategory === cat.id }" :style="{ backgroundColor: selectedCategory === cat.id ? cat.color : '#f8fafc', borderColor: cat.color, color: selectedCategory === cat.id ? '#fff' : cat.color }" @click="selectedCategory = cat.id">
              <span class="cat-emoji">{{ cat.emoji }}</span>
              <span class="cat-name">{{ cat.name }}</span>
            </button>
            <button class="category-btn" :class="{ active: selectedCategory === 'all' }" style="background: #3b82f6; border-color: #3b82f6; color: white;" @click="selectedCategory = 'all'">
              <span class="cat-emoji">📦</span>
              <span class="cat-name">All</span>
            </button>
          </div>
        </div>
        <div class="products-grid">
          <div v-for="product in filteredProducts" :key="product.id" class="product-tile" :class="{ 'low-stock': product.is_low_stock, 'out-of-stock': product.stock_quantity <= 0 }" @click="addToCart(product)">
            <div class="product-emoji">{{ product.emoji }}</div>
            <div class="product-name">{{ product.name }}</div>
            <div class="product-price">{{ formatCurrency(product.price) }}</div>
            <div class="product-stock" :class="{ 'low': product.stock_quantity > 0 && product.stock_quantity <= 10, 'out': product.stock_quantity <= 0 }">
              {{ product.stock_quantity <= 0 ? 'Out of Stock' : product.stock_quantity + ' in stock' }}
            </div>
            <div v-if="getProductQty(product.id) > 0" class="product-qty-badge">{{ getProductQty(product.id) }}</div>
          </div>
          <div v-if="filteredProducts.length === 0" class="no-products">
            <span class="no-products-icon">📦</span>
            <span>No products found</span>
          </div>
        </div>
      </div>
      <div class="cart-panel">
        <div class="cart-header-row">
          <div class="customer-section">
            <select v-model="cart.customer_id" class="customer-select">
              <option value="">🚶 Walk-in</option>
              <optgroup v-for="group in customersGrouped" :key="group.type" :label="group.label">
                <option v-for="c in group.customers" :key="c.id" :value="c.id">
                  {{ c.full_name }}
                  <template v-if="c.customer_group"> (-{{ c.customer_group.discount_percentage }}%)</template>
                  <template v-if="c.type === 'guest'"> (R:{{ c.room_number }})</template>
                </option>
              </optgroup>
            </select>
          </div>
          <div class="cart-actions-row">
            <button v-if="heldOrders.length > 0" @click="showHeldOrders = true" class="hold-btn">
              📋 Hold ({{ heldOrders.length }})
            </button>
            <button v-if="cart.items.length > 0" @click="holdOrder" class="hold-btn">📌 Hold</button>
          </div>
        </div>
        <div v-if="cart.customer_id && String(cart.customer_id).startsWith('guest_')" class="room-billing-row">
          <label class="checkbox-label">
            <input type="checkbox" v-model="cart.is_charged_to_room" />
            <span>Charge to Room</span>
          </label>
          <span v-if="cart.is_charged_to_room" class="room-badge">💳 Pay at Checkout</span>
        </div>
        <div v-if="cartDiscount > 0" class="discount-badge">🏷️ {{ cartDiscount }}% {{ customerGroupName }} Discount</div>
        <div class="cart-items-container">
          <div v-if="cart.items.length === 0" class="empty-cart">
            <span class="empty-icon">🛒</span>
            <span>Click products to add</span>
          </div>
          <div class="cart-items-list">
            <div v-for="(item, index) in cart.items" :key="index" class="cart-item">
              <div class="cart-item-left">
                <span class="item-name">{{ item.name }}</span>
                <span class="item-price-each">@ {{ formatCurrency(item.price) }}</span>
              </div>
              <div class="cart-item-right">
                <div class="qty-controls">
                  <button @click="updateQuantity(index, -1)" class="qty-btn">−</button>
                  <span class="qty-num">{{ item.quantity }}</span>
                  <button @click="updateQuantity(index, 1)" class="qty-btn">+</button>
                </div>
                <span class="item-total">{{ formatCurrency(item.price * item.quantity) }}</span>
                <button @click="removeItem(index)" class="remove-btn">×</button>
              </div>
            </div>
          </div>
        </div>
        <div class="cart-summary">
          <div class="summary-row">
            <span>Subtotal</span>
            <span>{{ formatCurrency(cartSubtotal) }}</span>
          </div>
          <div class="summary-row" v-if="cartDiscountAmount > 0">
            <span>Discount</span>
            <span class="discount">-{{ formatCurrency(cartDiscountAmount) }}</span>
          </div>
          <div class="summary-row">
            <span>Tax</span>
            <span>{{ formatCurrency(cartTax) }}</span>
          </div>
          <div class="summary-row total-row">
            <span>TOTAL</span>
            <span class="total-amount">{{ formatCurrency(cartTotal) }}</span>
          </div>
        </div>
        <div class="payment-section">
          <div class="payment-methods-grid">
            <button v-for="m in paymentMethods" :key="m.value" class="payment-btn" :class="{ active: cart.payment_method === m.value }" @click="cart.payment_method = m.value">
              <span class="payment-icon">{{ m.icon }}</span>
              <span class="payment-label">{{ m.label }}</span>
            </button>
          </div>
        </div>
        <div class="action-buttons">
          <button @click="clearCart" class="btn-action btn-clear">Clear</button>
          <button @click="processSale" class="btn-action btn-pay" :disabled="cart.items.length === 0 || isProcessing">
            {{ isProcessing ? 'Processing...' : 'PAY ' + formatCurrency(cartTotal) }}
          </button>
        </div>

      </div>
    </div>
    <div v-if="showCalculator" class="modal-overlay" @click.self="showCalculator = false">
      <div class="calculator-modal">
        <div class="calc-header">
          <span class="calc-title">Calculator</span>
          <button @click="showCalculator = false" class="calc-close">✕</button>
        </div>
        <div class="calc-display">
          <div class="calc-expression">{{ calcExpression || '0' }}</div>
          <div class="calc-result">{{ calcResult || '0' }}</div>
        </div>
        <div class="calc-buttons">
          <button @click="calcClear" class="calc-btn calc-clear">AC</button>
          <button @click="calcToggleSign" class="calc-btn">+/-</button>
          <button @click="calcPercent" class="calc-btn">%</button>
          <button @click="calcDivide" class="calc-btn calc-op">÷</button>
          <button @click="calcInput('7')" class="calc-btn">7</button>
          <button @click="calcInput('8')" class="calc-btn">8</button>
          <button @click="calcInput('9')" class="calc-btn">9</button>
          <button @click="calcMultiply" class="calc-btn calc-op">×</button>
          <button @click="calcInput('4')" class="calc-btn">4</button>
          <button @click="calcInput('5')" class="calc-btn">5</button>
          <button @click="calcInput('6')" class="calc-btn">6</button>
          <button @click="calcSubtract" class="calc-btn calc-op">−</button>
          <button @click="calcInput('1')" class="calc-btn">1</button>
          <button @click="calcInput('2')" class="calc-btn">2</button>
          <button @click="calcInput('3')" class="calc-btn">3</button>
          <button @click="calcAdd" class="calc-btn calc-op">+</button>
          <button @click="calcInput('0')" class="calc-btn calc-zero">0</button>
          <button @click="calcDecimal" class="calc-btn">.</button>
          <button @click="calcEquals" class="calc-btn calc-equals">=</button>
        </div>
      </div>
    </div>
    <div v-if="showDrawerModal" class="modal-overlay" @click.self="showDrawerModal = false">
      <div class="drawer-modal">
        <div class="drawer-header">
          <span class="drawer-icon">🗄️</span>
          <span class="drawer-title">{{ activeSession ? 'Close Cash Drawer' : 'Open Cash Drawer' }}</span>
        </div>
        <div v-if="!activeSession" class="drawer-content">
          <label>Opening Balance</label>
          <input type="number" v-model.number="openingBalance" min="0" step="0.01" class="drawer-input" placeholder="0.00" autofocus />
          <div class="drawer-actions">
            <button @click="showDrawerModal = false" class="btn-cancel">Cancel</button>
            <button @click="openDrawer" class="btn-confirm">Open Drawer</button>
          </div>
        </div>
        <div v-else class="drawer-content">
          <label>Closing Balance</label>
          <input type="number" v-model.number="closingBalance" min="0" step="0.01" class="drawer-input" placeholder="0.00" autofocus />
          <p class="expected-balance">Expected: {{ formatCurrency(expectedBalance) }}</p>
          <div class="drawer-actions">
            <button @click="showDrawerModal = false" class="btn-cancel">Cancel</button>
            <button @click="closeDrawer" class="btn-confirm">Close Drawer</button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="showSuccessModal" class="receipt-modal-overlay" @click.self="closeSuccessModal">
      <div class="receipt-modal-card">
        <div class="receipt-modal-actions">
          <button @click="printReceipt" class="receipt-print-btn" title="Print Receipt">🖨️ Print Receipt</button>
          <button @click="closeSuccessModal" class="receipt-close-btn" title="Close">✕</button>
        </div>
        <div class="receipt-fullpage-inner">
        <div class="receipt-page-header">
          <div class="receipt-page-brand">
            <span class="receipt-page-logo">🏨</span>
            <div>
              <h2 class="receipt-page-title">RECEIPT</h2>
              <p class="receipt-page-hotel">{{ hotelName }}</p>
            </div>
          </div>
          <div class="receipt-page-meta">
            <span class="receipt-page-number">#{{ lastSale?.sale_number }}</span>
            <span class="receipt-page-date">{{ formatReceiptDate(lastSale?.sale_date) }}</span>
            <span class="receipt-page-cashier">Cashier: {{ user?.first_name || 'Staff' }}</span>
          </div>
        </div>

        <div class="receipt-page-divider"></div>

        <div class="receipt-page-items">
          <div class="receipt-page-items-header">
            <span>Item</span>
            <span>Qty</span>
            <span>Unit Price</span>
            <span>Total</span>
          </div>
          <div class="receipt-page-divider receipt-page-divider--thin"></div>
          <div v-for="item in lastSale?.items" :key="item.id" class="receipt-page-item-row">
            <span class="receipt-page-item-name">{{ item.product?.name || 'Item' }}</span>
            <span class="receipt-page-item-qty">{{ item.quantity }}</span>
            <span class="receipt-page-item-unit">{{ formatCurrency(item.unit_price) }}</span>
            <span class="receipt-page-item-total">{{ formatCurrency(item.total_price) }}</span>
          </div>
        </div>

        <div class="receipt-page-divider"></div>

        <div class="receipt-page-totals">
          <div class="receipt-page-total-row">
            <span>Subtotal</span>
            <span>{{ formatCurrency(lastSale?.subtotal) }}</span>
          </div>
          <div class="receipt-page-total-row" v-if="lastSale?.tax_amount > 0">
            <span>Tax</span>
            <span>{{ formatCurrency(lastSale?.tax_amount) }}</span>
          </div>
          <div class="receipt-page-total-row receipt-page-total-row--discount" v-if="lastSale?.discount_amount > 0">
            <span>Discount</span>
            <span>-{{ formatCurrency(lastSale?.discount_amount) }}</span>
          </div>
          <div class="receipt-page-divider receipt-page-divider--thin"></div>
          <div class="receipt-page-total-row receipt-page-total-row--grand">
            <span>TOTAL</span>
            <span>{{ formatCurrency(lastSale?.total_amount) }}</span>
          </div>
        </div>

        <div class="receipt-page-divider"></div>

        <div class="receipt-page-footer">
          <div class="receipt-page-payment">
            <span class="receipt-page-payment-label">Payment Method</span>
            <span class="receipt-page-payment-value">{{ formatPaymentMethod(lastSale?.payment_method) }}</span>
          </div>
          <p v-if="lastSale?.is_charged_to_room" class="receipt-page-room-charge">💳 Room Charge — Pay at Checkout</p>
          <p class="receipt-page-thankyou">Thank you for your purchase!</p>
        </div>
      </div>
      </div><!-- end receipt-modal-card -->

    </div><!-- end receipt-modal-overlay -->
    <!-- Transactions Modal -->
    <div v-if="showTransactionsModal" class="modal-overlay" @click.self="showTransactionsModal = false">
      <div class="transactions-modal">
        <div class="txn-modal-header">
          <div class="txn-modal-title">
            <span class="txn-modal-icon">🧾</span>
            <div>
              <h3>My Transactions</h3>
              <p>{{ user?.first_name || 'Staff' }} — Today: {{ todaySalesCount }} sales</p>
            </div>
          </div>
          <button @click="showTransactionsModal = false" class="txn-modal-close">✕</button>
        </div>
        <div class="txn-modal-body">
          <div v-if="recentSales.length === 0" class="txn-empty">
            <span style="font-size:2.5rem">💭</span>
            <p>No transactions yet today.</p>
          </div>
          <table v-else class="txn-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Method</th>
                <th>Amount</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="sale in recentSales" :key="sale.id" class="txn-row">
                <td class="txn-number">{{ sale.sale_number }}</td>
                <td class="txn-customer">{{ sale.customer_name }}</td>
                <td class="txn-items">{{ sale.items_count }}</td>
                <td><span class="txn-method-badge">{{ formatPaymentMethod(sale.payment_method) }}</span></td>
                <td class="txn-amount">{{ formatCurrency(sale.total_amount) }}</td>
                <td class="txn-date">{{ formatReceiptDate(sale.sale_date) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="txn-modal-footer">
          <span class="txn-footer-note">Showing last {{ recentSales.length }} transactions</span>
          <Link :href="route('pos.sales.index')" class="txn-view-all">View All →</Link>
        </div>
      </div>
    </div>

    <div v-if="showHeldOrders" class="modal-overlay" @click.self="showHeldOrders = false">
      <div class="held-orders-modal">
        <div class="held-orders-header">
          <h3>📋 Held Orders</h3>
          <button @click="showHeldOrders = false" class="close-btn">✕</button>
        </div>
        <div class="held-orders-list">
          <div v-if="heldOrders.length === 0" class="no-held">No held orders</div>
          <div v-for="(order, idx) in heldOrders" :key="idx" class="held-order-card">
            <div class="held-order-info">
              <span class="held-order-name">{{ order.customer_name || 'Walk-in' }}</span>
              <span class="held-order-items">{{ order.items.length }} items</span>
              <span class="held-order-total">{{ formatCurrency(order.total) }}</span>
              <span class="held-order-time">{{ formatTime(order.held_at) }}</span>
            </div>
            <div class="held-order-actions">
              <button @click="resumeOrder(idx)" class="resume-btn">Resume</button>
              <button @click="deleteHeldOrder(idx)" class="delete-btn">×</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-if="barcodeScannerActive" class="barcode-overlay" @click.self="stopBarcodeScanner">
      <div class="barcode-scanner-box">
        <div class="scanner-header">
          <h3>📷 Barcode Scanner</h3>
          <button @click="stopBarcodeScanner" class="close-btn">✕</button>
        </div>
        <div class="scanner-input-area">
          <input ref="barcodeInput" v-model="barcodeValue" type="text" placeholder="Scan or type barcode..." class="barcode-input" @keyup.enter="submitBarcode" />
          <button @click="submitBarcode" class="submit-barcode-btn">Add</button>
        </div>
        <p class="scanner-hint">Use a barcode scanner or type the code above</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { Link } from '@inertiajs/vue3'
import axios from 'axios'
import { formatCurrency as formatCurrencyUtil } from '@/Utils/currency.js'

const props = defineProps({
  user: Object,
  categories: Array,
  products: Array,
  customers: Array,
  activeSession: Object,
  taxRate: Number,
  todaySales: Number,
  todaySalesCount: Number,
  recentSales: {
    type: Array,
    default: () => [],
  },
  hotelName: String,
  printSettings: Object
})

// Get print settings with defaults
const printSettings = computed(() => ({
  paperWidth: props.printSettings?.pos_print_paper_width || 80,
  fontSize: props.printSettings?.pos_print_font_size || 12,
  showLogo: props.printSettings?.pos_print_show_logo !== '0'
}))

const receiptStyle = computed(() => ({}))

const now = ref(new Date())
let timeInterval = null
const currentTime = computed(() => now.value.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' }))
const currentDate = computed(() => now.value.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' }))

const selectedCategory = ref('all')
const showCalculator = ref(false)
const showDrawerModal = ref(false)
const showSuccessModal = ref(false)
const showHeldOrders = ref(false)
const showTransactionsModal = ref(false)
const isProcessing = ref(false)
const openingBalance = ref(0)
const closingBalance = ref(0)
const lastSale = ref(null)
const calcExpression = ref('')
const calcResult = ref('')
const productSearch = ref('')
const barcodeScannerActive = ref(false)
const barcodeValue = ref('')
const heldOrders = ref([])
const barcodeInput = ref(null)

const calcInput = (val) => { calcExpression.value += val; try { calcResult.value = eval(calcExpression.value.replace(/×/g, '*').replace(/÷/g, '/')).toFixed(2) } catch {} }
const calcClear = () => { calcExpression.value = ''; calcResult.value = '' }
const calcToggleSign = () => { if (calcExpression.value) calcExpression.value = calcExpression.value.startsWith('-') ? calcExpression.value.slice(1) : '-' + calcExpression.value }
const calcPercent = () => { try { const v = eval(calcExpression.value.replace(/×/g, '*').replace(/÷/g, '/')); calcExpression.value = (v / 100).toString(); calcResult.value = (v / 100).toFixed(2) } catch {} }
const calcDecimal = () => { if (!calcExpression.value.includes('.')) calcExpression.value += '.' }
const calcAdd = () => calcExpression.value += '+'
const calcSubtract = () => calcExpression.value += '−'
const calcMultiply = () => calcExpression.value += '×'
const calcDivide = () => calcExpression.value += '÷'
const calcEquals = () => { try { calcResult.value = eval(calcExpression.value.replace(/×/g, '*').replace(/÷/g, '/').replace(/−/g, '-')).toFixed(2); calcExpression.value = calcResult.value } catch {} }

const cart = ref({ items: [], customer_id: '', payment_method: 'cash', is_charged_to_room: false, discount_amount: 0, is_walk_in: true })

const activeSession = ref(props.activeSession)
const recentSales = ref(Array.isArray(props.recentSales) ? [...props.recentSales] : [])

const customerGroupName = computed(() => { const c = props.customers.find(x => x.id === cart.value.customer_id); return c?.customer_group?.name || 'Group' })
const getGuestRoomNumber = (id) => { const c = props.customers.find(x => x.id === id); return c?.room_number || '' }
const expectedBalance = computed(() => activeSession.value ? (activeSession.value.opening_balance + props.todaySales) : 0)

const paymentMethods = [
  { value: 'cash', label: 'Cash', icon: '💵' },
  { value: 'card', label: 'Card', icon: '💳' },
  { value: 'bank_transfer', label: 'Bank', icon: '🏦' },
  { value: 'mobile', label: 'Mobile', icon: '📱' }
]

const filteredProducts = computed(() => {
  let result = props.products
  if (selectedCategory.value !== 'all') {
    result = result.filter(p => p.category_id === selectedCategory.value)
  }
  if (productSearch.value) {
    const search = productSearch.value.toLowerCase()
    result = result.filter(p => p.name.toLowerCase().includes(search) || (p.barcode && p.barcode.includes(search)))
  }
  return result
})
const customersGrouped = computed(() => { const r = props.customers.filter(c => c.type === 'customer'); const g = props.customers.filter(c => c.type === 'guest'); return [{ type: 'customer', label: 'Customers', customers: r }, { type: 'guest', label: 'Hotel Guests', customers: g }].filter(x => x.customers.length > 0) })
const cartSubtotal = computed(() => cart.value.items.reduce((sum, i) => sum + (i.price * i.quantity), 0))
const cartDiscount = computed(() => { const c = props.customers.find(x => x.id === cart.value.customer_id); if (c && c.customer_group && !cart.value.is_walk_in) return c.customer_group.discount_percentage || 0; return 0 })
const cartDiscountAmount = computed(() => cartSubtotal.value * (cartDiscount.value / 100))
const cartTax = computed(() => (cartSubtotal.value - cartDiscountAmount.value) * (props.taxRate / 100))
const cartTotal = computed(() => (cartSubtotal.value - cartDiscountAmount.value) + cartTax.value)
const formatCurrency = (amount) => formatCurrencyUtil(amount || 0)
const getProductQty = (id) => { const i = cart.value.items.find(x => x.id === id); return i ? i.quantity : 0 }

const addToCart = (product) => { if (product.stock_quantity <= 0) return; const existingIndex = cart.value.items.findIndex(i => i.id === product.id); if (existingIndex >= 0) { if (cart.value.items[existingIndex].quantity < product.stock_quantity) cart.value.items[existingIndex].quantity++ } else { cart.value.items.push({ id: product.id, name: product.name, price: product.price, quantity: 1, stock_quantity: product.stock_quantity }) } }
const updateQuantity = (index, delta) => { const newQty = cart.value.items[index].quantity + delta; if (newQty > 0 && newQty <= cart.value.items[index].stock_quantity) cart.value.items[index].quantity = newQty }
const removeItem = (index) => cart.value.items.splice(index, 1)
const clearCart = () => { cart.value = { items: [], customer_id: '', payment_method: 'cash', is_charged_to_room: false, discount_amount: 0, is_walk_in: true } }
const toggleDrawer = () => { showDrawerModal.value = true }

const openDrawer = async () => {
  try {
    const response = await fetch('/pos/open-drawer', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify({ opening_balance: openingBalance.value }),
    })

    const result = await response.json().catch(() => null)
    if (!response.ok || !result?.success) {
      alert(result?.message || 'Failed to open drawer')
      return
    }

    activeSession.value = result.session
    showDrawerModal.value = false
  } catch (e) {
    alert('Failed to open drawer')
  }
}

const closeDrawer = async () => {
  try {
    const response = await fetch('/pos/close-drawer', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify({ closing_balance: closingBalance.value }),
    })

    const result = await response.json().catch(() => null)
    if (!response.ok || !result?.success) {
      alert(result?.message || 'Failed to close drawer')
      return
    }

    activeSession.value = null
    showDrawerModal.value = false
  } catch (e) {
    alert('Failed to close drawer')
  }
}
const processSale = async () => {
  isProcessing.value = true
  try {
    const response = await axios.post('/pos/process-sale', {
      items: cart.value.items.map(i => ({ product_id: i.id, quantity: i.quantity, unit_price: i.price })),
      customer_id: cart.value.customer_id,
      payment_method: cart.value.payment_method,
      is_charged_to_room: cart.value.is_charged_to_room,
      discount_amount: cart.value.discount_amount,
      is_walk_in: !cart.value.customer_id
    })

    if (response.data.success) {
      lastSale.value = response.data.sale
      showSuccessModal.value = true

      const sale = response.data.sale
      if (sale) {
        recentSales.value.unshift({
          id: sale.id,
          sale_number: sale.sale_number,
          sale_date: sale.sale_date,
          payment_method: sale.payment_method,
          is_walk_in: !!sale.is_walk_in,
          customer_name: sale.is_walk_in ? 'Walk-In' : (sale.customer_name || 'N/A'),
          items_count: Array.isArray(sale.items) ? sale.items.length : (sale.items?.length || 0),
          total_amount: Number(sale.total_amount) || 0,
        })
        recentSales.value = recentSales.value.slice(0, 20)
      }

      clearCart()
    }
  } catch (error) {
    alert(error.response?.data?.message || 'Sale failed')
  } finally {
    isProcessing.value = false
  }
}
const closeSuccessModal = () => { showSuccessModal.value = false; lastSale.value = null }
const formatReceiptDate = (date) => {
  if (!date) return ''
  const d = new Date(date)
  return d.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
const formatPaymentMethod = (method) => {
  const methods = {
    cash: 'Cash',
    card: 'Card',
    bank_transfer: 'Bank Transfer',
    mobile: 'Mobile Money',
    room_charge: 'Room Charge'
  }
  return methods[method] || method
}
const printReceipt = () => window.print()
const updateTime = () => { now.value = new Date() }
const handleSearch = () => { /* filteredProducts is reactive */ }
const startBarcodeScanner = async () => { barcodeScannerActive.value = true; await nextTick(); barcodeInput.value?.focus() }
const stopBarcodeScanner = () => { barcodeScannerActive.value = false; barcodeValue.value = '' }
const submitBarcode = () => { if (!barcodeValue.value) return; const product = props.products.find(p => p.barcode === barcodeValue.value); if (product) { addToCart(product); stopBarcodeScanner() } else { alert('Product not found') } }
const holdOrder = () => { if (cart.value.items.length === 0) return; const customer = props.customers.find(x => x.id === cart.value.customer_id); heldOrders.value.push({ items: [...cart.value.items], customer_id: cart.value.customer_id, customer_name: customer?.full_name || 'Walk-in', total: cartTotal.value, payment_method: cart.value.payment_method, is_charged_to_room: cart.value.is_charged_to_room, held_at: new Date() }); clearCart() }
const resumeOrder = (idx) => { const order = heldOrders.value[idx]; cart.value = { items: [...order.items], customer_id: order.customer_id, payment_method: order.payment_method, is_charged_to_room: order.is_charged_to_room, discount_amount: 0, is_walk_in: !order.customer_id }; heldOrders.value.splice(idx, 1); showHeldOrders.value = false }
const deleteHeldOrder = (idx) => { heldOrders.value.splice(idx, 1) }
const formatTime = (date) => { const d = new Date(date); return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) }

onMounted(() => { updateTime(); timeInterval = setInterval(updateTime, 1000) })
onUnmounted(() => { if (timeInterval) clearInterval(timeInterval) })
</script>

<style scoped>
.pos-terminal {
  height: 100vh;
  background: #f1f5f9;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.pos-header {
  background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
  color: white;
  padding: 6px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
  gap: 8px;
  min-width: 0;
  overflow: hidden;
}
.header-left {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 0 1 auto;
  min-width: 0;
}
.header-center {
  display: flex;
  align-items: center;
  flex: 0 0 auto;
}
.header-right {
  display: flex;
  align-items: center;
  gap: 6px;
  flex: 0 0 auto;
}
.hotel-brand {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
}
.brand-icon {
  font-size: 22px;
  flex-shrink: 0;
}
.brand-info {
  min-width: 0;
}
.brand-info h1 {
  font-size: 14px;
  font-weight: 600;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.hotel-name {
  font-size: 10px;
  opacity: 0.8;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: block;
  max-width: 180px;
}
.datetime-display {
  text-align: center;
}
.time {
  font-size: 16px;
  font-weight: 700;
  font-family: 'Courier New', monospace;
  line-height: 1.2;
}
.date {
  font-size: 10px;
  opacity: 0.8;
}
.btn-header {
  background: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.2);
  color: white;
  padding: 5px 10px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 11px;
  white-space: nowrap;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
}
.stats-bar {
  display: flex;
  gap: 12px;
  padding: 10px 20px;
  background: white;
  border-bottom: 1px solid #e2e8f0;
  flex-shrink: 0;
}
.stat-card {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  background: #f8fafc;
  border-radius: 6px;
  border: 1px solid #e2e8f0;
  text-decoration: none;
  color: inherit;
}
.stat-card--link {
  cursor: pointer;
  transition: background 0.15s, border-color 0.15s, transform 0.1s;
}
.stat-card--link:hover {
  background: #eff6ff;
  border-color: #3b82f6;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(59,130,246,0.15);
}
.stat-card--link:hover .stat-value {
  color: #2563eb;
}
.stat-card--link:hover .stat-label {
  color: #3b82f6;
}
.stat-icon {
  font-size: 24px;
}
.stat-value {
  font-size: 16px;
  font-weight: 700;
  color: #1e293b;
}
.stat-label {
  font-size: 10px;
  color: #64748b;
}
.drawer-open { background: #ecfdf5; border-color: #10b981; }
.drawer-closed { background: #fef2f2; border-color: #ef4444; }
.user-card { max-width: 140px; }
.pos-main {
  flex: 1;
  display: flex;
  gap: 16px;
  padding: 16px 20px;
  overflow: hidden;
}
.products-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: white;
  border-radius: 10px;
  padding: 12px;
  overflow: hidden;
}
.search-bar-row {
  display: flex;
  gap: 10px;
  margin-bottom: 10px;
  flex-shrink: 0;
}
.search-input-wrapper {
  flex: 1;
  position: relative;
}
.search-icon {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  color: #94a3b8;
}
.search-input {
  width: 100%;
  padding: 8px 30px 8px 36px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 13px;
}
.search-clear {
  position: absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  font-size: 14px;
}
.barcode-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 12px;
  cursor: pointer;
}
.barcode-btn.active {
  background: #dbeafe;
  border-color: #3b82f6;
  color: #1d4ed8;
}
.barcode-btn svg {
  width: 18px;
  height: 18px;
}
.categories-scroll {
  margin-bottom: 10px;
  overflow-x: auto;
  flex-shrink: 0;
}
.categories-row {
  display: flex;
  gap: 6px;
  flex-wrap: nowrap;
}
.category-btn {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 6px 12px;
  border: 2px solid;
  border-radius: 16px;
  background: #f8fafc;
  cursor: pointer;
  font-size: 12px;
  white-space: nowrap;
  flex-shrink: 0;
}
.category-btn.active { transform: scale(1.03); }
.cat-emoji { font-size: 14px; }
.products-grid {
  flex: 1;
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  gap: 0;
  align-content: start;
  overflow-y: auto;
}
.product-tile {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  padding: 4px;
  margin: 1px;
  text-align: center;
  cursor: pointer;
  position: relative;
}
.product-tile:hover { background: #f1f5f9; }
.product-tile.low-stock { border-color: #fbbf24; }
.product-tile.out-of-stock { opacity: 0.5; cursor: not-allowed; }
.product-emoji { font-size: 20px; display: block; margin-bottom: 2px; }
.product-name { font-size: 10px; font-weight: 600; color: #1e293b; display: block; }
.product-price { font-size: 12px; font-weight: 700; color: #059669; display: block; margin-top: 2px; }
.product-stock {
  font-size: 9px;
  color: #64748b;
  margin-top: 2px;
}
.product-stock.low {
  color: #f59e0b;
}
.product-stock.out {
  color: #ef4444;
}
.product-qty-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #3b82f6;
  color: white;
  font-size: 9px;
  font-weight: 700;
  min-width: 16px;
  height: 16px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 4px;
}
.no-products {
  grid-column: 1 / -1;
  text-align: center;
  padding: 40px;
  color: #94a3b8;
}
.cart-panel {
  width: 340px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  background: white;
  border-radius: 10px;
  padding: 12px;
}
.cart-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
}
.customer-select {
  width: 100%;
  padding: 8px;
  border: 1px solid #e2e8f0;
  border-radius: 5px;
  font-size: 12px;
}
.cart-actions-row {
  display: flex;
  gap: 6px;
}
.hold-btn {
  padding: 6px 10px;
  background: #fef3c7;
  border: 1px solid #fbbf24;
  border-radius: 5px;
  font-size: 11px;
  cursor: pointer;
}
.room-billing-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 6px 8px;
  background: #f8fafc;
  border-radius: 5px;
}
.checkbox-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  cursor: pointer;
}
.checkbox-label input { width: 14px; height: 14px; }
.room-badge {
  background: #dbeafe;
  color: #1d4ed8;
  padding: 2px 8px;
  border-radius: 10px;
  font-size: 11px;
}
.discount-badge {
  background: #fef3c7;
  color: #b45309;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 11px;
  text-align: center;
}
.cart-items-container {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}
.cart-items-list {
  flex: 1;
  overflow-y: auto;
}
.empty-cart {
  text-align: center;
  padding: 30px;
  color: #94a3b8;
}
.empty-icon {
  font-size: 32px;
  display: block;
  margin-bottom: 8px;
}
.cart-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px;
  background: #f8fafc;
  border-radius: 6px;
  margin-bottom: 6px;
}
.cart-item-left {
  display: flex;
  flex-direction: column;
}
.item-name { font-weight: 600; font-size: 12px; }
.item-price-each { font-size: 10px; color: #64748b; }
.cart-item-right {
  display: flex;
  align-items: center;
  gap: 8px;
}
.qty-controls { display: flex; align-items: center; gap: 4px; }
.qty-btn {
  width: 24px;
  height: 24px;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}
.qty-num {
  font-weight: 600;
  min-width: 20px;
  text-align: center;
  font-size: 12px;
}
.item-total { font-weight: 700; color: #059669; font-size: 12px; }
.remove-btn {
  background: none;
  border: none;
  color: #ef4444;
  cursor: pointer;
  font-size: 16px;
}
.cart-summary {
  background: #f8fafc;
  border-radius: 6px;
  padding: 10px;
}
.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 3px 0;
  font-size: 12px;
}
.summary-row.total-row {
  border-top: 1px solid #e2e8f0;
  margin-top: 6px;
  padding-top: 6px;
  font-weight: 700;
}
.total-amount { color: #059669; font-size: 16px; }
.discount { color: #ef4444; }
.payment-section {
  flex-shrink: 0;
}
.payment-methods-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 6px;
}
.payment-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  padding: 8px;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 5px;
  cursor: pointer;
  font-size: 11px;
}
.payment-btn.active {
  border-color: #3b82f6;
  background: #eff6ff;
}
.payment-icon { font-size: 16px; }
.action-buttons {
  display: flex;
  gap: 8px;
}
.btn-action {
  flex: 1;
  padding: 10px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
}
.btn-clear { background: #fee2e2; color: #dc2626; }
.btn-pay {
  background: linear-gradient(135deg, #059669 0%, #10b981 100%);
  color: white;
}
.btn-pay:disabled { opacity: 0.5; cursor: not-allowed; }
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.calculator-modal {
  background: white;
  border-radius: 12px;
  padding: 16px;
  width: 280px;
}
.calc-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}
.calc-title { font-weight: 600; font-size: 14px; }
.calc-close {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  color: #64748b;
}
.calc-display {
  background: #1e293b;
  border-radius: 6px;
  padding: 10px;
  margin-bottom: 10px;
  text-align: right;
}
.calc-expression { color: #94a3b8; font-size: 12px; }
.calc-result { color: white; font-size: 24px; font-weight: 700; }
.calc-buttons {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 6px;
}
.calc-btn {
  padding: 12px;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
}
.calc-clear { background: #fee2e2; color: #dc2626; border-color: #fecaca; }
.calc-op { background: #3b82f6; color: white; border-color: #2563eb; }
.calc-equals { background: #059669; color: white; border-color: #047857; }
.calc-zero { grid-column: span 2; }
.receipt-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}
.receipt-modal-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.25);
  width: 100%;
  max-width: 520px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.receipt-modal-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-bottom: 1px solid #e2e8f0;
  background: #f8fafc;
  flex-shrink: 0;
}
.receipt-print-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: #1e293b;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
}
.receipt-print-btn:hover { background: #0f172a; }
.receipt-fullpage {
  position: fixed;
  inset: 0;
  background: #f1f5f9;
  z-index: 1000;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.receipt-fullpage-inner {
  overflow-y: auto;
  padding: 24px 24px 20px;
  background: white;
}
.receipt-page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 4px;
}
.receipt-page-brand {
  display: flex;
  align-items: center;
  gap: 14px;
}
.receipt-page-logo { font-size: 48px; }
.receipt-page-title {
  font-size: 28px;
  font-weight: 800;
  color: #1e293b;
  margin: 0 0 4px;
  letter-spacing: 2px;
}
.receipt-page-hotel {
  font-size: 14px;
  color: #64748b;
  margin: 0;
}
.receipt-page-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
}
.receipt-page-number {
  font-size: 18px;
  font-weight: 700;
  color: #1e293b;
}
.receipt-page-date {
  font-size: 13px;
  color: #64748b;
}
.receipt-page-cashier {
  font-size: 12px;
  color: #94a3b8;
}
.receipt-page-divider {
  border-top: 2px dashed #e2e8f0;
  margin: 20px 0;
}
.receipt-page-divider--thin {
  border-top-width: 1px;
  margin: 8px 0;
}
.receipt-page-items-header {
  display: grid;
  grid-template-columns: 1fr 80px 120px 120px;
  gap: 8px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  color: #94a3b8;
  letter-spacing: 0.5px;
  padding: 0 4px;
}
.receipt-page-item-row {
  display: grid;
  grid-template-columns: 1fr 80px 120px 120px;
  gap: 8px;
  padding: 10px 4px;
  border-bottom: 1px solid #f1f5f9;
  font-size: 14px;
  color: #1e293b;
  align-items: center;
}
.receipt-page-item-row:last-child { border-bottom: none; }
.receipt-page-item-name { font-weight: 500; }
.receipt-page-item-qty { text-align: center; color: #64748b; }
.receipt-page-item-unit { text-align: right; color: #64748b; }
.receipt-page-item-total { text-align: right; font-weight: 600; }
.receipt-page-totals {
  max-width: 320px;
  margin-left: auto;
}
.receipt-page-total-row {
  display: flex;
  justify-content: space-between;
  padding: 6px 0;
  font-size: 14px;
  color: #64748b;
}
.receipt-page-total-row--discount { color: #059669; }
.receipt-page-total-row--grand {
  font-size: 20px;
  font-weight: 800;
  color: #1e293b;
  padding: 10px 0;
}
.receipt-page-footer {
  margin-top: 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}
.receipt-page-payment {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 10px 20px;
}
.receipt-page-payment-label {
  font-size: 11px;
  text-transform: uppercase;
  color: #94a3b8;
  letter-spacing: 0.5px;
}
.receipt-page-payment-value {
  font-size: 15px;
  font-weight: 700;
  color: #1e293b;
}
.receipt-page-room-charge {
  font-size: 13px;
  font-weight: 600;
  color: #3b82f6;
  margin: 0;
}
.receipt-page-thankyou {
  font-size: 16px;
  font-weight: 700;
  color: #059669;
  margin: 8px 0 0;
  text-align: center;
}
.receipt-close-btn {
  background: transparent;
  border: 1px solid #e2e8f0;
  color: #475569;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  font-size: 16px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s;
}
.receipt-close-btn:hover {
  background: #fee2e2;
  color: #dc2626;
  border-color: #fecaca;
}
@media print {
  body { visibility: hidden; }
  .receipt-modal-overlay {
    position: fixed !important;
    top: 0; left: 0;
    background: none !important;
    visibility: visible !important;
  }
  .receipt-modal-card {
    visibility: visible !important;
    position: fixed !important;
    top: 0; left: 0;
    width: 80mm;
    box-shadow: none !important;
    max-height: none !important;
    overflow: visible !important;
    background: #fff !important;
    padding: 4mm;
  }
  .receipt-modal-card * { visibility: visible !important; }
  .receipt-modal-actions { visibility: hidden !important; }
  .receipt-close-btn { visibility: hidden !important; }
  @page { margin: 0; size: 80mm auto; }
}
.transactions-modal {
  background: white;
  border-radius: 14px;
  width: 720px;
  max-width: 95vw;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,0.25);
}
.txn-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 20px;
  border-bottom: 1px solid #e2e8f0;
  background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
  color: white;
}
.txn-modal-title {
  display: flex;
  align-items: center;
  gap: 12px;
}
.txn-modal-icon { font-size: 28px; }
.txn-modal-title h3 {
  margin: 0;
  font-size: 17px;
  font-weight: 700;
}
.txn-modal-title p {
  margin: 2px 0 0;
  font-size: 12px;
  opacity: 0.75;
}
.txn-modal-close {
  background: rgba(255,255,255,0.15);
  border: 1px solid rgba(255,255,255,0.25);
  color: white;
  width: 30px;
  height: 30px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.txn-modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 0;
}
.txn-empty {
  text-align: center;
  padding: 48px 20px;
  color: #94a3b8;
}
.txn-empty p { margin: 8px 0 0; font-size: 14px; }
.txn-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.txn-table thead tr {
  background: #f8fafc;
  border-bottom: 2px solid #e2e8f0;
}
.txn-table th {
  padding: 10px 14px;
  text-align: left;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  color: #64748b;
}
.txn-row {
  border-bottom: 1px solid #f1f5f9;
  transition: background 0.1s;
}
.txn-row:hover { background: #f8fafc; }
.txn-row td {
  padding: 10px 14px;
  vertical-align: middle;
}
.txn-number { font-weight: 600; color: #1e293b; font-family: monospace; font-size: 12px; }
.txn-customer { color: #475569; }
.txn-items { color: #64748b; text-align: center; }
.txn-amount { font-weight: 700; color: #059669; }
.txn-date { color: #94a3b8; font-size: 11px; white-space: nowrap; }
.txn-method-badge {
  background: #eff6ff;
  color: #2563eb;
  padding: 2px 8px;
  border-radius: 10px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
}
.txn-modal-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 20px;
  border-top: 1px solid #e2e8f0;
  background: #f8fafc;
}
.txn-footer-note {
  font-size: 12px;
  color: #94a3b8;
}
.txn-view-all {
  font-size: 13px;
  font-weight: 600;
  color: #3b82f6;
  text-decoration: none;
  padding: 6px 14px;
  border: 1px solid #bfdbfe;
  border-radius: 6px;
  background: white;
  transition: background 0.15s;
}
.txn-view-all:hover { background: #eff6ff; }
.drawer-header, .success-icon { margin-bottom: 16px; }
.drawer-icon, .success-icon { font-size: 40px; display: block; margin-bottom: 8px; }
.drawer-title { font-size: 18px; font-weight: 600; }
.drawer-content label { display: block; text-align: left; font-size: 13px; font-weight: 500; margin-bottom: 6px; }
.drawer-input {
  width: 100%;
  padding: 10px;
  border: 2px solid #e2e8f0;
  border-radius: 6px;
  font-size: 20px;
  text-align: right;
  margin-bottom: 12px;
}
.expected-balance {
  background: #f0fdf4;
  padding: 8px;
  border-radius: 4px;
  color: #15803d;
  margin-bottom: 12px;
}
.drawer-actions, .success-actions { display: flex; gap: 10px; margin-top: 12px; }
.btn-cancel, .btn-close {
  flex: 1;
  padding: 10px;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 6px;
  cursor: pointer;
}
.btn-confirm, .btn-print {
  flex: 1;
  padding: 10px;
  border: none;
  background: #059669;
  color: white;
  border-radius: 6px;
  cursor: pointer;
}
.sale-number { font-size: 16px; color: #64748b; }
.sale-total { font-size: 32px; font-weight: 700; color: #059669; margin: 8px 0 16px; }
.held-orders-modal {
  background: white;
  border-radius: 12px;
  padding: 20px;
  width: 400px;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
}
.held-orders-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}
.held-orders-header h3 { font-size: 18px; font-weight: 600; }
.close-btn {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #64748b;
}
.held-orders-list { flex: 1; overflow-y: auto; }
.no-held { text-align: center; padding: 40px; color: #94a3b8; }
.held-order-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background: #f8fafc;
  border-radius: 8px;
  margin-bottom: 8px;
}
.held-order-info { display: flex; flex-direction: column; gap: 2px; }
.held-order-name { font-weight: 600; font-size: 13px; }
.held-order-items { font-size: 11px; color: #64748b; }
.held-order-total { font-weight: 700; color: #059669; font-size: 14px; }
.held-order-time { font-size: 10px; color: #94a3b8; }
.held-order-actions { display: flex; gap: 6px; }
.resume-btn {
  padding: 6px 12px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 12px;
  cursor: pointer;
}
.delete-btn {
  padding: 6px 10px;
  background: #fee2e2;
  color: #dc2626;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
.barcode-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.barcode-scanner-box {
  background: white;
  border-radius: 12px;
  padding: 20px;
  width: 360px;
}
.scanner-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}
.scanner-header h3 { font-size: 18px; font-weight: 600; }
.scanner-input-area { display: flex; gap: 10px; margin-bottom: 12px; }
.barcode-input {
  flex: 1;
  padding: 12px;
  border: 2px solid #e2e8f0;
  border-radius: 6px;
  font-size: 16px;
}
.submit-barcode-btn {
  padding: 12px 20px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
}
.scanner-hint { font-size: 12px; color: #64748b; }
</style>
