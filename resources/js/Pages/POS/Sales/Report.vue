<template>
  <DashboardLayout title="Sales Report" :user="user" :navigation="navigation">
    <div class="sales-report-container" id="sales-report">
      <div class="page-header">
        <div>
          <Link :href="route('pos.sales.index')" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Sales
          </Link>
          <h1>Sales Report</h1>
          <p class="report-period">{{ formatDateRange(filters.start_date, filters.end_date) }}</p>
        </div>
        <div class="header-actions">
          <button @click="printReport" class="btn-primary">
            <i class="fa-solid fa-print"></i> Print Report
          </button>
          <button @click="exportReport" class="btn-secondary">
            <i class="fa-solid fa-download"></i> Export CSV
          </button>
        </div>
      </div>

      <!-- Comprehensive Filters -->
      <div class="filters-card">
        <h3 class="filters-title">
          <i class="fa-solid fa-filter"></i> Filter Options
        </h3>
        <form @submit.prevent="applyFilters" class="filters-form">
          <div class="filter-row">
            <div class="filter-group">
              <label>Start Date *</label>
              <DatePicker v-model="filters.start_date" placeholder="Select start date" />
            </div>
            <div class="filter-group">
              <label>End Date *</label>
              <DatePicker v-model="filters.end_date" placeholder="Select end date" />
            </div>
            <div class="filter-group">
              <label>Payment Method</label>
              <select v-model="filters.payment_method" class="form-input">
                <option value="">All Methods</option>
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="mobile">Mobile Payment</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Customer</label>
              <select v-model="filters.customer_id" class="form-input">
                <option value="">All Customers</option>
                <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                  {{ customer.first_name }} {{ customer.last_name }} {{ customer.customer_code ? `(${customer.customer_code})` : '' }}
                </option>
              </select>
            </div>
          </div>
          <div class="filter-row">
            <div class="filter-group">
              <label>Staff</label>
              <select v-model="filters.user_id" class="form-input">
                <option value="">All Staff</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>
            </div>
            <div class="filter-group">
              <label>Status</label>
              <select v-model="filters.status" class="form-input">
                <option value="">All Status</option>
                <option value="completed">Completed</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Min Amount</label>
              <input v-model.number="filters.min_amount" type="number" step="0.01" min="0" class="form-input" placeholder="0.00" />
            </div>
            <div class="filter-group">
              <label>Max Amount</label>
              <input v-model.number="filters.max_amount" type="number" step="0.01" min="0" class="form-input" placeholder="No limit" />
            </div>
          </div>
          <div class="filter-actions">
            <button type="submit" class="btn-primary">
              <i class="fa-solid fa-filter"></i> Apply Filters
            </button>
            <button type="button" @click="clearFilters" class="btn-secondary">
              <i class="fa-solid fa-times"></i> Clear
            </button>
            <button type="button" @click="setToday" class="btn-outline">
              Today
            </button>
            <button type="button" @click="setThisWeek" class="btn-outline">
              This Week
            </button>
            <button type="button" @click="setThisMonth" class="btn-outline">
              This Month
            </button>
            <button type="button" @click="setThisYear" class="btn-outline">
              This Year
            </button>
          </div>
        </form>
      </div>

      <!-- Summary Statistics -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon" style="background: #dbeafe;">
            <i class="fa-solid fa-dollar-sign" style="color: #1e40af;"></i>
          </div>
          <div class="stat-content">
            <label>Total Sales</label>
            <h2>{{ formatCurrency(stats.total_sales || 0) }}</h2>
            <p class="stat-subtitle">{{ stats.total_count || 0 }} transactions</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background: #d1fae5;">
            <i class="fa-solid fa-chart-line" style="color: #065f46;"></i>
          </div>
          <div class="stat-content">
            <label>Total Profit</label>
            <h2 :class="(stats.total_profit || 0) >= 0 ? 'text-green-600' : 'text-red-600'">
              {{ formatCurrency(stats.total_profit || 0) }}
            </h2>
            <p class="stat-subtitle">{{ (stats.profit_margin || 0).toFixed(1) }}% margin</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background: #fef3c7;">
            <i class="fa-solid fa-calculator" style="color: #92400e;"></i>
          </div>
          <div class="stat-content">
            <label>Average Sale</label>
            <h2>{{ formatCurrency(stats.average_sale || 0) }}</h2>
            <p class="stat-subtitle">Per transaction</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background: #fee2e2;">
            <i class="fa-solid fa-percent" style="color: #991b1b;"></i>
          </div>
          <div class="stat-content">
            <label>Total Discount</label>
            <h2>{{ formatCurrency(stats.total_discount || 0) }}</h2>
            <p class="stat-subtitle">{{ stats.discount_percentage || 0 }}% of sales</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background: #e9d5ff;">
            <i class="fa-solid fa-receipt" style="color: #6b21a8;"></i>
          </div>
          <div class="stat-content">
            <label>Subtotal</label>
            <h2>{{ formatCurrency(stats.total_subtotal || 0) }}</h2>
            <p class="stat-subtitle">Before tax & discount</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background: #fce7f3;">
            <i class="fa-solid fa-file-invoice-dollar" style="color: #9f1239;"></i>
          </div>
          <div class="stat-content">
            <label>Total Tax</label>
            <h2>{{ formatCurrency(stats.total_tax || 0) }}</h2>
            <p class="stat-subtitle">{{ stats.tax_percentage || 0 }}% of subtotal</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background: #cffafe;">
            <i class="fa-solid fa-coins" style="color: #0e7490;"></i>
          </div>
          <div class="stat-content">
            <label>Total Cost</label>
            <h2>{{ formatCurrency(stats.total_cost || 0) }}</h2>
            <p class="stat-subtitle">Purchase cost</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background: #f0fdf4;">
            <i class="fa-solid fa-users" style="color: #166534;"></i>
          </div>
          <div class="stat-content">
            <label>Unique Customers</label>
            <h2>{{ stats.unique_customers || 0 }}</h2>
            <p class="stat-subtitle">Active customers</p>
          </div>
        </div>
      </div>

      <!-- Payment Method Breakdown -->
      <div class="report-section">
        <h2>
          <i class="fa-solid fa-credit-card"></i> Payment Method Breakdown
        </h2>
        <div class="payment-methods-grid">
          <div v-for="(data, method) in byPaymentMethod" :key="method" class="payment-method-card">
            <div class="payment-method-header">
              <span class="badge-payment" :class="'badge-' + method">
                {{ formatPaymentMethod(method) }}
              </span>
            </div>
            <div class="payment-method-stats">
              <div class="stat-item">
                <label>Transactions</label>
                <span class="stat-value">{{ data.count || 0 }}</span>
              </div>
              <div class="stat-item">
                <label>Total Amount</label>
                <span class="stat-value">{{ formatCurrency(data.total || 0) }}</span>
              </div>
              <div class="stat-item">
                <label>Percentage</label>
                <span class="stat-value">
                  {{ stats.total_sales > 0 ? (((data.total || 0) / stats.total_sales) * 100).toFixed(1) : 0 }}%
                </span>
              </div>
              <div class="stat-item">
                <label>Average</label>
                <span class="stat-value">{{ formatCurrency((data.total || 0) / (data.count || 1)) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Daily Sales Chart -->
      <div class="report-section">
        <h2>
          <i class="fa-solid fa-chart-bar"></i> Daily Sales Trend
        </h2>
        <div class="daily-sales-chart">
          <div v-for="(data, date) in byDay" :key="date" class="daily-bar-container">
            <div class="daily-bar-wrapper">
              <div 
                class="daily-bar" 
                :style="{ height: maxDailySale > 0 ? ((data.total / maxDailySale) * 100) + '%' : '0%' }"
                :title="formatCurrency(data.total)"
              ></div>
            </div>
            <div class="daily-label">
              <span class="date">{{ formatDate(date) }}</span>
              <span class="amount">{{ formatCurrency(data.total) }}</span>
              <span class="count">{{ data.count || 0 }} sales</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Products -->
      <div class="report-section">
        <h2>
          <i class="fa-solid fa-trophy"></i> Top 10 Products by Revenue
        </h2>
        <div class="top-products-table">
          <table>
            <thead>
              <tr>
                <th>Rank</th>
                <th>Product</th>
                <th>Quantity Sold</th>
                <th>Revenue</th>
                <th>Cost</th>
                <th>Profit</th>
                <th>Margin</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(product, index) in topProducts" :key="index">
                <td class="rank">#{{ index + 1 }}</td>
                <td class="product-name">{{ product.name }}</td>
                <td class="quantity">{{ product.quantity || 0 }}</td>
                <td class="revenue">{{ formatCurrency(product.revenue || 0) }}</td>
                <td class="cost">{{ formatCurrency(product.cost || 0) }}</td>
                <td :class="(product.profit || 0) >= 0 ? 'profit-positive' : 'profit-negative'">
                  {{ formatCurrency(product.profit || 0) }}
                </td>
                <td :class="(product.margin || 0) >= 0 ? 'profit-positive' : 'profit-negative'">
                  {{ (product.margin || 0).toFixed(1) }}%
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Staff Performance -->
      <div v-if="byStaff && Object.keys(byStaff).length > 0" class="report-section">
        <h2>
          <i class="fa-solid fa-user-tie"></i> Staff Performance
        </h2>
        <div class="staff-performance-table">
          <table>
            <thead>
              <tr>
                <th>Staff Member</th>
                <th>Sales Count</th>
                <th>Total Revenue</th>
                <th>Average Sale</th>
                <th>Total Profit</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(data, staffId) in byStaff" :key="staffId">
                <td class="staff-name">{{ data.name || 'Unknown' }}</td>
                <td>{{ data.count || 0 }}</td>
                <td class="revenue">{{ formatCurrency(data.total || 0) }}</td>
                <td>{{ formatCurrency((data.total || 0) / (data.count || 1)) }}</td>
                <td :class="(data.profit || 0) >= 0 ? 'profit-positive' : 'profit-negative'">
                  {{ formatCurrency(data.profit || 0) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Customer Analysis -->
      <div v-if="byCustomer && Object.keys(byCustomer).length > 0" class="report-section">
        <h2>
          <i class="fa-solid fa-users"></i> Top Customers
        </h2>
        <div class="customer-analysis-table">
          <table>
            <thead>
              <tr>
                <th>Customer</th>
                <th>Transactions</th>
                <th>Total Spent</th>
                <th>Average Order</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(data, customerId) in topCustomers" :key="customerId">
                <td class="customer-name">{{ data.name || 'Walk-In' }}</td>
                <td>{{ data.count || 0 }}</td>
                <td class="revenue">{{ formatCurrency(data.total || 0) }}</td>
                <td>{{ formatCurrency((data.total || 0) / (data.count || 1)) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { formatCurrency as formatCurrencyUtil, initializeCurrencySettings } from '@/Utils/currency.js'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DatePicker from '@/Components/DatePicker.vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
  user: Object,
  navigation: Array,
  stats: {
    type: Object,
    default: () => ({})
  },
  byPaymentMethod: {
    type: Object,
    default: () => ({})
  },
  byDay: {
    type: Object,
    default: () => ({})
  },
  byStaff: {
    type: Object,
    default: () => ({})
  },
  topProducts: {
    type: Array,
    default: () => []
  },
  topCustomers: {
    type: Array,
    default: () => []
  },
  customers: {
    type: Array,
    default: () => []
  },
  users: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Object,
    default: () => ({
      start_date: '',
      end_date: '',
      payment_method: '',
      customer_id: '',
      user_id: '',
      status: '',
      min_amount: '',
      max_amount: ''
    })
  }
})

const page = usePage()
const settings = computed(() => page?.props?.settings || {})

const formatCurrency = (amount, currency = null, position = null) => {
  const useCurrency = currency || settings.value?.currency || 'USD'
  const usePosition = position || settings.value?.currency_position || 'prefix'
  return formatCurrencyUtil(amount, useCurrency, usePosition)
}

const filters = ref({ 
  start_date: props.filters?.start_date || new Date().toISOString().split('T')[0],
  end_date: props.filters?.end_date || new Date().toISOString().split('T')[0],
  payment_method: props.filters?.payment_method || '',
  customer_id: props.filters?.customer_id || '',
  user_id: props.filters?.user_id || '',
  status: props.filters?.status || '',
  min_amount: props.filters?.min_amount || '',
  max_amount: props.filters?.max_amount || ''
})

const maxDailySale = computed(() => {
  if (!props.byDay || Object.keys(props.byDay).length === 0) return 1
  return Math.max(...Object.values(props.byDay).map(d => d.total || 0))
})

const formatPaymentMethod = (method) => {
  const methods = {
    'cash': 'Cash',
    'card': 'Card',
    'bank_transfer': 'Bank Transfer',
    'mobile': 'Mobile Payment'
  }
  return methods[method] || method || 'N/A'
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
  } catch (e) {
    return dateString
  }
}

const formatDateRange = (start, end) => {
  if (!start || !end) return ''
  try {
    const startDate = new Date(start)
    const endDate = new Date(end)
    return `${startDate.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })} - ${endDate.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}`
  } catch (e) {
    return `${start} - ${end}`
  }
}

const applyFilters = () => {
  const cleanFilters = Object.fromEntries(
    Object.entries(filters.value).filter(([_, value]) => value !== '' && value !== null)
  )
  
  router.get(route('pos.sales.report'), cleanFilters, {
    preserveState: true,
    preserveScroll: true
  })
}

const clearFilters = () => {
  const today = new Date().toISOString().split('T')[0]
  filters.value = {
    start_date: today,
    end_date: today,
    payment_method: '',
    customer_id: '',
    user_id: '',
    status: '',
    min_amount: '',
    max_amount: ''
  }
  applyFilters()
}

const setToday = () => {
  const today = new Date().toISOString().split('T')[0]
  filters.value.start_date = today
  filters.value.end_date = today
  applyFilters()
}

const setThisWeek = () => {
  const today = new Date()
  const startOfWeek = new Date(today)
  startOfWeek.setDate(today.getDate() - today.getDay())
  filters.value.start_date = startOfWeek.toISOString().split('T')[0]
  filters.value.end_date = today.toISOString().split('T')[0]
  applyFilters()
}

const setThisMonth = () => {
  const today = new Date()
  const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1)
  filters.value.start_date = startOfMonth.toISOString().split('T')[0]
  filters.value.end_date = today.toISOString().split('T')[0]
  applyFilters()
}

const setThisYear = () => {
  const today = new Date()
  const startOfYear = new Date(today.getFullYear(), 0, 1)
  filters.value.start_date = startOfYear.toISOString().split('T')[0]
  filters.value.end_date = today.toISOString().split('T')[0]
  applyFilters()
}

const printReport = () => {
  window.print()
}

const exportReport = () => {
  const csv = [
    ['Sales Report', ''],
    ['Start Date', filters.value.start_date],
    ['End Date', filters.value.end_date],
    [''],
    ['Metric', 'Value'],
    ['Total Sales', props.stats.total_sales || 0],
    ['Total Transactions', props.stats.total_count || 0],
    ['Total Profit', props.stats.total_profit || 0],
    ['Profit Margin', (props.stats.profit_margin || 0).toFixed(2) + '%'],
    ['Average Sale', props.stats.average_sale || 0],
    ['Total Discount', props.stats.total_discount || 0],
    ['Subtotal', props.stats.total_subtotal || 0],
    ['Total Tax', props.stats.total_tax || 0],
    ['Total Cost', props.stats.total_cost || 0],
    ['Unique Customers', props.stats.unique_customers || 0],
    [''],
    ['Payment Method Breakdown', ''],
    ['Method', 'Count', 'Total', 'Percentage'],
    ...Object.entries(props.byPaymentMethod || {}).map(([method, data]) => [
      formatPaymentMethod(method),
      data.count || 0,
      data.total || 0,
      props.stats.total_sales > 0 ? (((data.total || 0) / props.stats.total_sales) * 100).toFixed(1) + '%' : '0%'
    ])
  ].map(row => row.join(',')).join('\n')

  const blob = new Blob([csv], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `sales-report-${filters.value.start_date}-to-${filters.value.end_date}.csv`
  a.click()
  window.URL.revokeObjectURL(url)
}

onMounted(() => {
  initializeCurrencySettings()
})
</script>

<style scoped>
.sales-report-container {
  padding: 0;
  width: 100%;
  max-width: 100%;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #38bdf8;
  text-decoration: none;
  font-weight: 600;
  margin-bottom: 8px;
  transition: color 0.2s;
}

.back-link:hover {
  color: #0ea5e9;
}

.page-header h1 {
  font-size: 2rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 4px 0;
}

.report-period {
  color: #64748b;
  font-size: 0.875rem;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.btn-primary, .btn-secondary, .btn-outline {
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
  border: 2px solid;
}

.btn-primary {
  background: #facc15;
  color: #000;
  border-color: #000;
}

.btn-primary:hover {
  background: #eab308;
  transform: translateY(-1px);
}

.btn-secondary {
  background: #38bdf8;
  color: white;
  border-color: #000;
}

.btn-secondary:hover {
  background: #0ea5e9;
}

.btn-outline {
  background: white;
  color: #475569;
  border-color: #e2e8f0;
}

.btn-outline:hover {
  background: #f8fafc;
  border-color: #38bdf8;
}

.filters-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filters-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.filters-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.filter-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.filter-group label {
  font-weight: 600;
  font-size: 0.875rem;
  color: #475569;
}

.form-input {
  padding: 10px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.95rem;
}

.form-input:focus {
  outline: none;
  border-color: #38bdf8;
  box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.1);
}

.filter-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  padding-top: 8px;
  border-top: 1px solid #e2e8f0;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  display: flex;
  align-items: center;
  gap: 16px;
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.stat-content {
  flex: 1;
}

.stat-content label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #64748b;
  margin-bottom: 4px;
}

.stat-content h2 {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 4px 0;
}

.stat-subtitle {
  font-size: 0.75rem;
  color: #94a3b8;
  margin: 0;
}

.report-section {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.report-section h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.payment-methods-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
}

.payment-method-card {
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px;
}

.payment-method-header {
  margin-bottom: 12px;
}

.badge-payment {
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 0.875rem;
  font-weight: 600;
  display: inline-block;
}

.badge-cash {
  background: #d1fae5;
  color: #065f46;
}

.badge-card {
  background: #dbeafe;
  color: #1e40af;
}

.badge-bank_transfer {
  background: #e9d5ff;
  color: #6b21a8;
}

.badge-mobile {
  background: #fce7f3;
  color: #9f1239;
}

.payment-method-stats {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.stat-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stat-item label {
  font-size: 0.875rem;
  color: #64748b;
}

.stat-item .stat-value {
  font-weight: 600;
  color: #1e293b;
}

.daily-sales-chart {
  display: flex;
  gap: 12px;
  align-items: flex-end;
  min-height: 300px;
  padding: 20px 0;
  overflow-x: auto;
}

.daily-bar-container {
  flex: 1;
  min-width: 80px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.daily-bar-wrapper {
  width: 100%;
  height: 250px;
  display: flex;
  align-items: flex-end;
  border-bottom: 2px solid #e2e8f0;
}

.daily-bar {
  width: 100%;
  background: linear-gradient(to top, #38bdf8, #0ea5e9);
  border-radius: 4px 4px 0 0;
  min-height: 4px;
  transition: all 0.3s;
  cursor: pointer;
}

.daily-bar:hover {
  opacity: 0.8;
}

.daily-label {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  text-align: center;
  font-size: 0.75rem;
}

.daily-label .date {
  font-weight: 600;
  color: #1e293b;
}

.daily-label .amount {
  font-weight: 700;
  color: #38bdf8;
}

.daily-label .count {
  color: #64748b;
}

.top-products-table, .staff-performance-table, .customer-analysis-table {
  overflow-x: auto;
}

.top-products-table table,
.staff-performance-table table,
.customer-analysis-table table {
  width: 100%;
  border-collapse: collapse;
}

.top-products-table thead,
.staff-performance-table thead,
.customer-analysis-table thead {
  background: #f1f5f9;
}

.top-products-table th,
.staff-performance-table th,
.customer-analysis-table th {
  padding: 12px;
  text-align: left;
  font-weight: 600;
  color: #475569;
  font-size: 0.875rem;
  text-transform: uppercase;
  border-bottom: 2px solid #e2e8f0;
}

.top-products-table td,
.staff-performance-table td,
.customer-analysis-table td {
  padding: 12px;
  border-bottom: 1px solid #e2e8f0;
}

.top-products-table tr:hover,
.staff-performance-table tr:hover,
.customer-analysis-table tr:hover {
  background: #f8fafc;
}

.rank {
  font-weight: 700;
  color: #facc15;
  font-size: 1.1rem;
}

.product-name, .staff-name, .customer-name {
  font-weight: 600;
  color: #1e293b;
}

.quantity {
  color: #38bdf8;
  font-weight: 600;
}

.revenue {
  font-weight: 700;
  color: #1e293b;
}

.cost {
  color: #64748b;
  font-weight: 600;
}

.profit-positive {
  color: #059669;
  font-weight: 600;
}

.profit-negative {
  color: #dc2626;
  font-weight: 600;
}

@media print {
  .header-actions,
  .back-link,
  .filters-card {
    display: none !important;
  }
  
  .sales-report-container {
    padding: 0;
  }
  
  .report-section {
    page-break-inside: avoid;
    box-shadow: none;
    border: 1px solid #e2e8f0;
  }
  
  .stat-card {
    page-break-inside: avoid;
  }
}
</style>
