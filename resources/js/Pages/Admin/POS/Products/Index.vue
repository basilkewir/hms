<template>
  <DashboardLayout title="Products Management" :user="user" :navigation="navigation">
    <!-- Products Management Header -->
    <div class="bg-kotel-dark/90 backdrop-blur-xl shadow-2xl rounded-xl p-6 mb-8 border border-kotel-yellow/30">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-kotel-yellow">Products Management</h1>
          <p class="text-kotel-sky-blue mt-2">Manage your POS products and inventory</p>
        </div>
        <div class="flex items-center gap-3">
          <button 
            v-if="selectedIds.length > 0"
            @click="deleteSelected"
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
          >
            Delete Selected ({{ selectedIds.length }})
          </button>
          <button 
            @click="showAddProduct = true"
            class="bg-kotel-yellow hover:bg-kotel-yellow/80 text-kotel-black px-4 py-2 rounded-md text-sm font-medium transition-colors"
          >
            Add New Product
          </button>
          <button 
            @click="deleteAllProducts"
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
          >
            Delete All
          </button>
        </div>
      </div>
    </div>

    <!-- Products Table -->
    <div class="bg-kotel-dark/90 backdrop-blur-xl shadow-xl rounded-xl border border-kotel-yellow/30">
      <div class="p-6">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-kotel-yellow/30">
            <thead>
              <tr>
                <th class="px-4 py-3 text-left">
                  <input type="checkbox" :checked="allSelected" @change="toggleAll" class="rounded border-kotel-yellow/30 bg-kotel-black/50 text-kotel-yellow focus:ring-kotel-yellow" />
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-kotel-sky-blue uppercase tracking-wider">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-kotel-sky-blue uppercase tracking-wider">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-kotel-sky-blue uppercase tracking-wider">Price</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-kotel-sky-blue uppercase tracking-wider">Stock</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-kotel-sky-blue uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-kotel-sky-blue uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-kotel-yellow/20">
              <tr v-for="product in products" :key="product.id" class="hover:bg-kotel-yellow/10 transition-colors" :class="{ 'bg-kotel-yellow/5': selectedIds.includes(product.id) }">
                <td class="px-4 py-4 whitespace-nowrap">
                  <input type="checkbox" :value="product.id" v-model="selectedIds" class="rounded border-kotel-yellow/30 bg-kotel-black/50 text-kotel-yellow focus:ring-kotel-yellow" />
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="text-2xl mr-3">{{ product.emoji || '🍽️' }}</div>
                    <div>
                      <div class="text-sm font-medium text-white">{{ product.name }}</div>
                      <div class="text-sm text-kotel-sky-blue/70">{{ product.code }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :style="`background-color: ${product.category?.color}20; color: ${product.category?.color}`"
                  >
                    {{ product.category?.name || 'Uncategorized' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                  {{ formatCurrency(product.price) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-white">{{ product.stock_quantity }}</div>
                  <div class="text-xs text-kotel-sky-blue/70">Min: {{ product.min_stock_level }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="product.is_active ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
                  >
                    {{ product.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button 
                    @click="editProduct(product)"
                    class="text-kotel-yellow hover:text-kotel-yellow/80 mr-3 transition-colors"
                  >
                    Edit
                  </button>
                  <button 
                    @click="deleteProduct(product.id)"
                    class="text-red-400 hover:text-red-300 transition-colors"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Add/Edit Product Modal -->
    <DialogModal :show="showAddProduct" @close="showAddProduct = false">
      <template #title>
        <span class="text-kotel-yellow">{{ editingProduct ? 'Edit Product' : 'Add New Product' }}</span>
      </template>
      <template #content>
        <div class="space-y-4 bg-kotel-dark">
          <div>
            <label class="block text-sm font-medium text-kotel-sky-blue mb-2">Product Name</label>
            <input 
              v-model="form.name" 
              type="text" 
              class="w-full bg-kotel-black/50 border-kotel-yellow/30 text-white rounded-md shadow-sm focus:border-kotel-yellow focus:ring-kotel-yellow"
              placeholder="Enter product name"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-kotel-sky-blue mb-2">Category</label>
            <select v-model="form.category_id" class="w-full bg-kotel-black/50 border-kotel-yellow/30 text-white rounded-md shadow-sm focus:border-kotel-yellow focus:ring-kotel-yellow">
              <option value="">Select category...</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-kotel-sky-blue mb-2">Price ({{ getCurrencySymbol() }})</label>
              <input 
                v-model="form.price" 
                type="number" 
                step="0.01"
                class="w-full bg-kotel-black/50 border-kotel-yellow/30 text-white rounded-md shadow-sm focus:border-kotel-yellow focus:ring-kotel-yellow"
                placeholder="0.00"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-kotel-sky-blue mb-2">Cost Price ({{ getCurrencySymbol() }})</label>
              <input 
                v-model="form.cost_price" 
                type="number" 
                step="0.01"
                class="w-full bg-kotel-black/50 border-kotel-yellow/30 text-white rounded-md shadow-sm focus:border-kotel-yellow focus:ring-kotel-yellow"
                placeholder="0.00"
              >
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-kotel-sky-blue mb-2">Stock Quantity</label>
              <input 
                v-model="form.stock_quantity" 
                type="number"
                class="w-full bg-kotel-black/50 border-kotel-yellow/30 text-white rounded-md shadow-sm focus:border-kotel-yellow focus:ring-kotel-yellow"
                placeholder="0"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-kotel-sky-blue mb-2">Min Stock Level</label>
              <input 
                v-model="form.min_stock_level" 
                type="number"
                class="w-full bg-kotel-black/50 border-kotel-yellow/30 text-white rounded-md shadow-sm focus:border-kotel-yellow focus:ring-kotel-yellow"
                placeholder="0"
              >
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-kotel-sky-blue mb-2">Emoji</label>
            <input 
              v-model="form.emoji" 
              type="text" 
              class="w-full bg-kotel-black/50 border-kotel-yellow/30 text-white rounded-md shadow-sm focus:border-kotel-yellow focus:ring-kotel-yellow"
              placeholder="🍽️"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-kotel-sky-blue mb-2">Description</label>
            <textarea 
              v-model="form.description" 
              rows="3"
              class="w-full bg-kotel-black/50 border-kotel-yellow/30 text-white rounded-md shadow-sm focus:border-kotel-yellow focus:ring-kotel-yellow"
              placeholder="Product description..."
            ></textarea>
          </div>
        </div>
      </template>
      <template #footer>
        <SecondaryButton @click="showAddProduct = false">Cancel</SecondaryButton>
        <PrimaryButton @click="saveProduct" class="ml-3">
          {{ editingProduct ? 'Update' : 'Create' }} Product
        </PrimaryButton>
      </template>
    </DialogModal>
  </DashboardLayout>
</template>

<script>
import { ref, computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DialogModal from '@/Components/DialogModal.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import { router } from '@inertiajs/vue3'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { formatCurrency, getCurrencySymbol } from '@/Utils/currency.js'

export default {
  components: {
    DashboardLayout,
    DialogModal,
    PrimaryButton,
    SecondaryButton
  },
  props: {
    products: Array,
    categories: Array,
    user: Object
  },
  setup(props) {
    const showAddProduct = ref(false)
    const editingProduct = ref(null)
    const selectedIds = ref([])
    
    const allSelected = computed(() => {
      return props.products && props.products.length > 0 && selectedIds.value.length === props.products.length
    })

    const navigation = computed(() => {
      const role = props.user.roles[0]?.name || 'staff'
      return getNavigationForRole(role)
    })
    
    const form = ref({
      name: '',
      category_id: '',
      price: '',
      cost_price: '',
      stock_quantity: '',
      min_stock_level: '',
      emoji: '',
      description: ''
    })

    const editProduct = (product) => {
      editingProduct.value = product
      form.value = {
        name: product.name,
        category_id: product.category_id,
        price: product.price,
        cost_price: product.cost_price,
        stock_quantity: product.stock_quantity,
        min_stock_level: product.min_stock_level,
        emoji: product.emoji,
        description: product.description
      }
      showAddProduct.value = true
    }

    const saveProduct = () => {
      const url = editingProduct.value 
        ? `/admin/pos/products/${editingProduct.value.id}` 
        : '/admin/pos/products'
      
      const method = editingProduct.value ? 'put' : 'post'
      
      router[method](url, form.value, {
        onSuccess: () => {
          showAddProduct.value = false
          editingProduct.value = null
          resetForm()
        }
      })
    }

    const deleteProduct = (id) => {
      if (confirm('Are you sure you want to delete this product?')) {
        router.delete(`/admin/pos/products/${id}`)
      }
    }

    const deleteAllProducts = () => {
      if (confirm('Are you sure you want to delete ALL products? This cannot be undone.')) {
        router.delete('/admin/pos/products')
      }
    }

    const toggleAll = () => {
      if (allSelected.value) {
        selectedIds.value = []
      } else {
        selectedIds.value = props.products.map(p => p.id)
      }
    }

    const deleteSelected = () => {
      if (selectedIds.value.length === 0) return
      if (confirm(`Are you sure you want to delete ${selectedIds.value.length} selected product(s)?`)) {
        router.delete('/admin/pos/products/bulk', {
          data: { ids: selectedIds.value },
          onSuccess: () => { selectedIds.value = [] }
        })
      }
    }

    const resetForm = () => {
      form.value = {
        name: '',
        category_id: '',
        price: '',
        cost_price: '',
        stock_quantity: '',
        min_stock_level: '',
        emoji: '',
        description: ''
      }
    }

    return {
      showAddProduct,
      editingProduct,
      form,
      navigation,
      selectedIds,
      allSelected,
      editProduct,
      saveProduct,
      deleteProduct,
      deleteAllProducts,
      toggleAll,
      deleteSelected,
      resetForm,
      formatCurrency,
      getCurrencySymbol
    }
  }
}
</script>