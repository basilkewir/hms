<template>
  <DashboardLayout :title="`Suppliers - ${$page.props.appName || 'POS System'}`" :user="user" :navigation="navigation">
    <div class="suppliers-page" :style="{ backgroundColor: themeColors.background }">
    <div class="page-header" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
      <div class="header-left">
        <h1 :style="{ color: themeColors.textPrimary }">🏢 Suppliers</h1>
        <p class="subtitle" :style="{ color: themeColors.textSecondary }">Manage your product suppliers</p>
      </div>
      <button @click="showCreateModal = true" class="btn-primary" style="background-color: var(--kotel-primary); color: #000000;">
        ➕ Add Supplier
      </button>
    </div>

    <div class="stats-row">
      <div class="stat-card" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
        <div class="stat-icon">🏢</div>
        <div class="stat-content">
          <span class="stat-value" :style="{ color: themeColors.textPrimary }">{{ suppliersCount }}</span>
          <span class="stat-label" :style="{ color: themeColors.textSecondary }">Total Suppliers</span>
        </div>
      </div>
      <div class="stat-card" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
        <div class="stat-icon">✅</div>
        <div class="stat-content">
          <span class="stat-value" :style="{ color: themeColors.textPrimary }">{{ activeSuppliers }}</span>
          <span class="stat-label" :style="{ color: themeColors.textSecondary }">Active</span>
        </div>
      </div>
      <div class="stat-card" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
          <span class="stat-value" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(totalPending) }}</span>
          <span class="stat-label" :style="{ color: themeColors.textSecondary }">Pending Balance</span>
        </div>
      </div>
    </div>

    <div class="search-bar">
      <input v-model="search" type="text" placeholder="Search suppliers..." class="search-input" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }" />
    </div>

    <div class="suppliers-grid">
      <div v-for="supplier in filteredSuppliers" :key="supplier.id" class="supplier-card" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
        <div class="supplier-header">
          <div class="supplier-avatar">{{ supplier.name.charAt(0) }}</div>
          <div class="supplier-info">
            <h3 :style="{ color: themeColors.textPrimary }">{{ supplier.name }}</h3>
            <span class="status-badge" :class="supplier.is_active ? 'active' : 'inactive'" :style="{ backgroundColor: supplier.is_active ? themeColors.success : themeColors.danger, color: '#ffffff' }">
              {{ supplier.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
        </div>
        <div class="supplier-details">
          <div class="detail-row">
            <span class="detail-icon">👤</span>
            <span :style="{ color: themeColors.textSecondary }">{{ supplier.contact_person || 'No contact' }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-icon">📞</span>
            <span :style="{ color: themeColors.textSecondary }">{{ supplier.phone || 'No phone' }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-icon">📧</span>
            <span :style="{ color: themeColors.textSecondary }">{{ supplier.email || 'No email' }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-icon">📍</span>
            <span :style="{ color: themeColors.textSecondary }">{{ supplier.address || 'No address' }}</span>
          </div>
        </div>
        <div class="supplier-financials" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
          <div class="financial">
            <span class="label" :style="{ color: themeColors.textTertiary }">Credit Limit</span>
            <span class="value" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(supplier.credit_limit) }}</span>
          </div>
          <div class="financial">
            <span class="label" :style="{ color: themeColors.textTertiary }">Balance</span>
            <span class="value" :class="{ 'text-red': supplier.current_balance > 0 }" :style="{ color: supplier.current_balance > 0 ? themeColors.danger : themeColors.textPrimary }">
              {{ formatCurrency(supplier.current_balance) }}
            </span>
          </div>
        </div>
        <div class="supplier-actions">
          <button @click="editSupplier(supplier)" class="btn-secondary" :style="{ borderColor: themeColors.border, color: themeColors.textSecondary, backgroundColor: themeColors.background }">Edit</button>
          <button @click="viewOrders(supplier)" class="btn-outline" :style="{ borderColor: themeColors.primary, color: themeColors.primary, backgroundColor: 'transparent' }">Orders</button>
        </div>
      </div>
    </div>

    <div v-if="suppliersCount === 0" class="empty-state" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
      <span class="empty-icon">🏢</span>
      <h3 :style="{ color: themeColors.textPrimary }">No suppliers yet</h3>
      <p :style="{ color: themeColors.textSecondary }">Add your first supplier to start managing purchases</p>
      <button @click="showCreateModal = true" class="btn-primary" :style="{ backgroundColor: themeColors.primary, color: '#000000' }">Add Supplier</button>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="modal-overlay" @click.self="closeModal" :style="{ backgroundColor: 'rgba(0, 0, 0, 0.5)' }">
      <div class="modal" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
        <div class="modal-header">
          <h2 :style="{ color: themeColors.textPrimary }">{{ showEditModal ? 'Edit Supplier' : 'Add New Supplier' }}</h2>
          <button @click="closeModal" class="close-btn" :style="{ color: themeColors.textTertiary }">✕</button>
        </div>
        <form @submit.prevent="saveSupplier" class="modal-body">
          <div class="form-row">
            <div class="form-group">
              <label :style="{ color: themeColors.textSecondary }">Supplier Name *</label>
              <input v-model="form.name" type="text" required placeholder="Enter supplier name" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label :style="{ color: themeColors.textSecondary }">Contact Person</label>
              <input v-model="form.contact_person" type="text" placeholder="Contact person name" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
            </div>
            <div class="form-group">
              <label :style="{ color: themeColors.textSecondary }">Phone</label>
              <input v-model="form.phone" type="text" placeholder="Phone number" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label :style="{ color: themeColors.textSecondary }">Email</label>
              <input v-model="form.email" type="email" placeholder="Email address" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
            </div>
            <div class="form-group">
              <label :style="{ color: themeColors.textSecondary }">Credit Limit</label>
              <input v-model.number="form.credit_limit" type="number" min="0" step="0.01" placeholder="0.00" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group full-width">
              <label :style="{ color: themeColors.textSecondary }">Address</label>
              <textarea v-model="form.address" rows="2" placeholder="Full address" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"></textarea>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="checkbox-label" :style="{ color: themeColors.textSecondary }">
                <input type="checkbox" v-model="form.is_active" :style="{ accentColor: themeColors.primary }" />
                <span>Active</span>
              </label>
            </div>
          </div>
          <div class="modal-actions">
            <button type="button" @click="closeModal" class="btn-secondary" :style="{ borderColor: themeColors.border, color: themeColors.textSecondary, backgroundColor: themeColors.background }">Cancel</button>
            <button type="submit" class="btn-primary" :style="{ backgroundColor: themeColors.primary, color: '#000000' }">{{ showEditModal ? 'Update' : 'Create' }} Supplier</button>
          </div>
        </form>
      </div>
    </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency as formatCurrencyUtil } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme.js'
import { getNavigationForRole } from '@/Utils/navigation.js'

const { currentTheme } = useTheme()

const props = defineProps({
  suppliers: { type: Object, default: () => ({ data: [] }) },
  user: { type: Object, default: () => ({}) },
})

const navigation = computed(() => {
  const userRole = props.user?.roles?.[0]?.name || 'admin'
  return getNavigationForRole(userRole)
})

const themeColors = computed(() => ({
  background: `var(--kotel-background)`,
  card: `var(--kotel-card)`,
  border: `var(--kotel-border)`,
  textPrimary: `var(--kotel-text-primary)`,
  textSecondary: `var(--kotel-text-secondary)`,
  textTertiary: `var(--kotel-text-tertiary)`,
  primary: `var(--kotel-primary)`,
  success: `var(--kotel-success)`,
  warning: `var(--kotel-warning)`,
  danger: `var(--kotel-danger)`,
  hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)'
}))

const search = ref('')
const showCreateModal = ref(false)
const showEditModal = ref(false)
const editingSupplier = ref(null)

// Local reactive suppliers that handles the prop properly
const suppliersList = computed(() => {
  return Array.isArray(props.suppliers?.data) ? props.suppliers.data : []
})

const form = ref({
  name: '',
  contact_person: '',
  email: '',
  phone: '',
  address: '',
  credit_limit: 0,
  current_balance: 0,
  is_active: true
})

const formatCurrency = (amount) => formatCurrencyUtil(amount || 0)

const filteredSuppliers = computed(() => {
  const list = suppliersList.value
  if (!search.value) return list
  const s = search.value.toLowerCase()
  return list.filter(supplier =>
    supplier.name?.toLowerCase().includes(s) ||
    supplier.contact_person?.toLowerCase().includes(s) ||
    supplier.email?.toLowerCase().includes(s)
  )
})

const activeSuppliers = computed(() => suppliersList.value.filter(s => s.is_active).length)
const totalPending = computed(() => suppliersList.value.reduce((sum, s) => sum + (s.current_balance || 0), 0))
const suppliersCount = computed(() => suppliersList.value.length)

const editSupplier = (supplier) => {
  editingSupplier.value = supplier
  form.value = { ...supplier }
  showEditModal.value = true
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  editingSupplier.value = null
  form.value = {
    name: '',
    contact_person: '',
    email: '',
    phone: '',
    address: '',
    credit_limit: 0,
    current_balance: 0,
    is_active: true
  }
}

const saveSupplier = () => {
  if (showEditModal.value && editingSupplier.value) {
    router.put(`/pos/suppliers/${editingSupplier.value.id}`, form.value, {
      onSuccess: closeModal
    })
  } else {
    router.post('/pos/suppliers', form.value, {
      onSuccess: closeModal
    })
  }
}

const viewOrders = (supplier) => {
  router.visit(`/pos/purchases?supplier_id=${supplier.id}`)
}
</script>

<style scoped>
.suppliers-page {
  padding: 24px;
  background: #f1f5f9;
  min-height: 100vh;
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}
.page-header h1 {
  font-size: 24px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}
.subtitle {
  color: #64748b;
  font-size: 14px;
  margin: 4px 0 0;
}
.stats-row {
  display: flex;
  gap: 16px;
  margin-bottom: 24px;
}
.stat-card {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.stat-icon {
  font-size: 32px;
  flex-shrink: 0;
}
.stat-content {
  display: flex;
  flex-direction: column;
  min-width: 0;
}
.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #1e293b;
  line-height: 1.2;
}
.stat-label {
  font-size: 12px;
  color: #64748b;
  margin-top: 2px;
}
.search-bar {
  margin-bottom: 24px;
}
.search-input {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
}
.suppliers-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}
.supplier-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.supplier-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}
.supplier-avatar {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 20px;
  font-weight: 700;
}
.supplier-info h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
}
.status-badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 11px;
  margin-top: 4px;
}
.status-badge.active {
  background: #ecfdf5;
  color: #10b981;
}
.status-badge.inactive {
  background: #fef2f2;
  color: #ef4444;
}
.supplier-details {
  margin-bottom: 16px;
}
.detail-row {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 0;
  font-size: 13px;
  color: #64748b;
}
.detail-icon {
  width: 20px;
}
.supplier-financials {
  display: flex;
  gap: 24px;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 16px;
}
.financial {
  display: flex;
  flex-direction: column;
}
.financial .label {
  font-size: 11px;
  color: #64748b;
}
.financial .value {
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
}
.financial .value.text-red {
  color: #ef4444;
}
.supplier-actions {
  display: flex;
  gap: 8px;
}
.btn-primary {
  padding: 10px 20px;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
}
.btn-secondary {
  padding: 10px 16px;
  background: #f1f5f9;
  color: #475569;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
  cursor: pointer;
}
.btn-outline {
  padding: 10px 16px;
  background: white;
  color: #3b82f6;
  border: 1px solid #3b82f6;
  border-radius: 8px;
  font-size: 14px;
  cursor: pointer;
  flex: 1;
}
.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 12px;
}
.empty-icon {
  font-size: 64px;
  display: block;
  margin-bottom: 16px;
}
.empty-state h3 {
  margin: 0 0 8px;
  color: #1e293b;
}
.empty-state p {
  color: #64748b;
  margin-bottom: 20px;
}
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
.modal {
  background: white;
  border-radius: 16px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e2e8f0;
}
.modal-header h2 {
  margin: 0;
  font-size: 18px;
}
.close-btn {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #64748b;
}
.modal-body {
  padding: 20px;
}
.form-row {
  display: flex;
  gap: 16px;
  margin-bottom: 16px;
}
.form-group {
  flex: 1;
}
.form-group.full-width {
  flex: 100%;
}
.form-group label {
  display: block;
  font-size: 13px;
  font-weight: 500;
  color: #475569;
  margin-bottom: 6px;
}
.form-group input,
.form-group textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
}
.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}
.checkbox-label input {
  width: auto;
}
.modal-actions {
  display: flex;
  gap: 12px;
  margin-top: 24px;
}
.modal-actions .btn-primary {
  flex: 1;
}
.modal-actions .btn-secondary {
  flex: 1;
}
</style>
