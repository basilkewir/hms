<template>
  <div class="receipt-container" id="receipt-print">
    <div class="receipt-content">
      <!-- Receipt Header -->
      <div class="receipt-header">
        <img v-if="normalizedHotelLogo" :src="normalizedHotelLogo" alt="Hotel Logo" class="receipt-logo">
        <h1 class="receipt-hotel-name">{{ hotelName }}</h1>
        <div v-if="hotelAddress" class="receipt-address">{{ hotelAddress }}</div>
        <div v-if="hotelPhone" class="receipt-phone">Tel: {{ hotelPhone }}</div>
        <div v-if="hotelEmail" class="receipt-email">{{ hotelEmail }}</div>
        <div class="receipt-divider"></div>
      </div>

      <!-- Receipt Info -->
      <div class="receipt-info">
        <div class="receipt-row">
          <span>Receipt #:</span>
          <span class="receipt-number">{{ sale.sale_number }}</span>
        </div>
        <div class="receipt-row">
          <span>Date:</span>
          <span>{{ formatDate(sale.sale_date) }}</span>
        </div>
        <div class="receipt-row">
          <span>Time:</span>
          <span>{{ formatTime(sale.sale_date) }}</span>
        </div>
        <div v-if="sale.user" class="receipt-row">
          <span>Staff:</span>
          <span>{{ sale.user.first_name }} {{ sale.user.last_name }}</span>
        </div>
        <div v-if="sale.customer && !sale.is_walk_in" class="receipt-row">
          <span>Customer:</span>
          <span>{{ sale.customer.first_name }} {{ sale.customer.last_name }}</span>
        </div>
        <div v-else-if="sale.is_walk_in" class="receipt-row">
          <span>Customer:</span>
          <span>Walk-In</span>
        </div>
        <div class="receipt-row">
          <span>Payment:</span>
          <span>{{ formatPaymentMethod(sale.payment_method) }}</span>
        </div>
        <div class="receipt-divider"></div>
      </div>

      <!-- Items -->
      <div class="receipt-items">
        <div class="receipt-items-header">
          <span>Item</span>
          <span class="text-right">Total</span>
        </div>
        <div v-for="item in sale.items" :key="item.id" class="receipt-item">
          <div class="receipt-item-name">
            <span class="item-emoji">{{ item.product?.emoji || '🍽️' }}</span>
            <span>{{ item.product?.name || 'Unknown' }}</span>
          </div>
          <div class="receipt-item-details">
            <div class="receipt-item-quantity">{{ item.quantity }} × {{ formatCurrency(item.unit_price) }}</div>
            <div class="receipt-item-total">{{ formatCurrency(item.total_price) }}</div>
          </div>
        </div>
        <div class="receipt-divider"></div>
      </div>

      <!-- Totals -->
      <div class="receipt-totals">
        <div class="receipt-total-row">
          <span>Subtotal:</span>
          <span>{{ formatCurrency(sale.subtotal) }}</span>
        </div>
        <div v-if="sale.tax_amount > 0" class="receipt-total-row">
          <span>Tax:</span>
          <span>{{ formatCurrency(sale.tax_amount) }}</span>
        </div>
        <div v-if="sale.discount_amount > 0" class="receipt-total-row discount">
          <span>Discount:</span>
          <span>-{{ formatCurrency(sale.discount_amount) }}</span>
        </div>
        <div class="receipt-total-row grand-total">
          <span>Total:</span>
          <span>{{ formatCurrency(sale.total_amount) }}</span>
        </div>
      </div>

      <!-- Footer -->
      <div class="receipt-footer">
        <div class="receipt-divider"></div>
        <div class="receipt-thank-you">Thank you for your business!</div>
        <div class="receipt-footer-text">Have a great day!</div>
        <div v-if="hotelWebsite" class="receipt-website-footer">{{ hotelWebsite }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { formatCurrency } from '@/Utils/currency.js'

const props = defineProps({
  sale: {
    type: Object,
    required: true
  },
  hotelName: {
    type: String,
    default: 'Grand Hotel'
  },
  hotelAddress: {
    type: String,
    default: ''
  },
  hotelPhone: {
    type: String,
    default: ''
  },
  hotelEmail: {
    type: String,
    default: ''
  },
  hotelLogo: {
    type: String,
    default: ''
  },
  hotelWebsite: {
    type: String,
    default: ''
  }
})

const normalizedHotelLogo = computed(() => {
  const logo = props.hotelLogo
  if (!logo) return ''
  if (/^(https?:)?\/\//i.test(logo) || logo.startsWith('data:')) return logo
  if (logo.startsWith('/storage/') || logo.startsWith('/images/') || logo.startsWith('/img/')) return logo
  return `/storage/${logo.replace(/^\/+/, '')}`
})

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatTime = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: true
  })
}

const formatPaymentMethod = (method) => {
  const methods = {
    'cash': 'Cash',
    'card': 'Card',
    'bank_transfer': 'Bank Transfer',
    'mobile': 'Mobile Payment'
  }
  return methods[method] || method
}
</script>

<style scoped>
.receipt-container {
  background: white;
  width: 100%;
  max-width: 400px;
  margin: 0 auto;
  padding: 20px;
  font-family: 'Courier New', monospace;
  font-size: 14px;
  line-height: 1.4;
  color: #000;
}

.receipt-content {
  width: 100%;
}

.receipt-header {
  text-align: center;
  margin-bottom: 16px;
}

.receipt-logo {
  max-width: 96px;
  max-height: 72px;
  margin: 0 auto 10px;
  object-fit: contain;
}

.receipt-hotel-name {
  font-size: 20px;
  font-weight: bold;
  margin: 0 0 8px 0;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.receipt-address,
.receipt-phone,
.receipt-email,
.receipt-website {
  font-size: 12px;
  margin: 4px 0;
  color: #333;
}

.receipt-divider {
  border-top: 1px dashed #000;
  margin: 12px 0;
}

.receipt-info {
  margin-bottom: 16px;
}

.receipt-row {
  display: flex;
  justify-content: space-between;
  margin: 6px 0;
  font-size: 13px;
}

.receipt-number {
  font-weight: bold;
}

.receipt-items {
  margin-bottom: 16px;
}

.receipt-items-header {
  display: flex;
  justify-content: space-between;
  font-weight: bold;
  margin-bottom: 8px;
  font-size: 13px;
  text-transform: uppercase;
}

.receipt-item {
  margin-bottom: 10px;
}

.receipt-item-name {
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 600;
  margin-bottom: 4px;
}

.item-emoji {
  font-size: 16px;
}

.receipt-item-details {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: #666;
  padding-left: 22px;
}

.receipt-item-quantity {
  flex: 1;
}

.receipt-item-total {
  font-weight: 600;
  color: #000;
}

.receipt-totals {
  margin-bottom: 16px;
}

.receipt-total-row {
  display: flex;
  justify-content: space-between;
  margin: 8px 0;
  font-size: 14px;
}

.receipt-total-row.discount {
  color: #10b981;
}

.receipt-total-row.grand-total {
  font-size: 18px;
  font-weight: bold;
  border-top: 2px solid #000;
  border-bottom: 2px solid #000;
  padding: 8px 0;
  margin-top: 12px;
}

.receipt-footer {
  text-align: center;
  margin-top: 20px;
}

.receipt-thank-you {
  font-weight: bold;
  font-size: 14px;
  margin: 12px 0;
}

.receipt-footer-text {
  font-size: 12px;
  color: #666;
  margin-top: 8px;
}

.receipt-website-footer {
  font-size: 11px;
  color: #666;
  margin-top: 6px;
}

/* Screen display only — printing is handled by printPopup() in the parent */
@media screen {
  .receipt-container {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
  }
}
</style>
