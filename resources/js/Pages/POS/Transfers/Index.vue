<template>
    <DashboardLayout title="Stock Transfers" :user="user" :navigation="navigation">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Stock Transfers</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Manage stock transfers between locations</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="exportTransfers"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                            :style="{ 
                                backgroundColor: themeColors.success,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.success + 'dd'"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                        📥 Export Transfers
                    </button>
                    <button @click="showCreateModal = true"
                            class="px-4 py-2 rounded-md transition-colors font-medium"
                            style="background-color: var(--kotel-primary); color: #000000;">
                        + New Transfer
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg p-6 shadow-sm"
                     :style="{ backgroundColor: themeColors.card }">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full mr-4"
                             :style="{ backgroundColor: themeColors.primary + '20' }">
                            <span class="text-2xl">📦</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textSecondary }">Total Transfers</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: themeColors.textPrimary }">{{ transfers.length }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg p-6 shadow-sm"
                     :style="{ backgroundColor: themeColors.card }">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full mr-4"
                             :style="{ backgroundColor: themeColors.success + '20' }">
                            <span class="text-2xl">✅</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textSecondary }">Completed</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: themeColors.success }">{{ completedTransfers }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg p-6 shadow-sm"
                     :style="{ backgroundColor: themeColors.card }">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full mr-4"
                             :style="{ backgroundColor: themeColors.warning + '20' }">
                            <span class="text-2xl">⏳</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textSecondary }">Pending</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: themeColors.warning }">{{ pendingTransfers }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg p-6 shadow-sm"
                     :style="{ backgroundColor: themeColors.card }">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full mr-4"
                             :style="{ backgroundColor: themeColors.info + '20' }">
                            <span class="text-2xl">📊</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textSecondary }">Total Quantity</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: themeColors.info }">{{ totalQuantityTransferred }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transfers Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden"
                 :style="{ backgroundColor: themeColors.card }">
                <div class="px-6 py-4 border-b"
                     :style="{ borderColor: themeColors.border }">
                    <h3 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Recent Transfers</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">From Location</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">To Location</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y"
                              :style="{ borderColor: themeColors.border }">
                            <tr v-for="transfer in transfers" :key="transfer.id"
                                class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textPrimary }">
                                        {{ formatDate(transfer.created_at) }}
                                    </div>
                                    <div class="text-xs"
                                         :style="{ color: themeColors.textTertiary }">
                                        {{ formatTime(transfer.created_at) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium"
                                         :style="{ color: themeColors.textPrimary }">
                                        {{ transfer.product?.name || 'N/A' }}
                                    </div>
                                    <div class="text-xs"
                                         :style="{ color: themeColors.textTertiary }">
                                        {{ transfer.product?.code || '' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textPrimary }">
                                        {{ transfer.from_location?.name || '—' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textPrimary }">
                                        {{ transfer.to_location?.name || '—' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium"
                                         :style="{ color: themeColors.textPrimary }">
                                        {{ transfer.quantity }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                          :class="getTransferStatusClass(transfer.status)">
                                        {{ getTransferStatusLabel(transfer.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textPrimary }">
                                        {{ transfer.user?.name || 'System' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button @click="viewTransfer(transfer)"
                                            class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="transfers.length === 0" class="text-center py-8">
                        <div class="text-gray-400 text-lg">📦</div>
                        <p class="text-gray-500 mt-2">No stock transfers found</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Transfer Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6"
                 :style="{ backgroundColor: themeColors.card }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Create Stock Transfer</h3>
                    <button @click="showCreateModal = false"
                            class="text-gray-400 hover:text-gray-600">
                        ✕
                    </button>
                </div>
                <form @submit.prevent="createTransfer">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textPrimary }">Product</label>
                            <select v-model="newTransfer.product_id"
                                    class="w-full px-3 py-2 border rounded-md focus:outline-none transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderWidth: '1px',
                                        borderStyle: 'solid',
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary
                                    }">
                                <option value="">Select a product</option>
                                <option v-for="product in allProducts" :key="product.id" :value="product.id">
                                    {{ product.name }} ({{ product.code }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textPrimary }">From Location</label>
                            <select v-model="newTransfer.from_location_id"
                                    class="w-full px-3 py-2 border rounded-md focus:outline-none transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderWidth: '1px',
                                        borderStyle: 'solid',
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary
                                    }">
                                <option value="">Select source location</option>
                                <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                                    {{ loc.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textPrimary }">To Location</label>
                            <select v-model="newTransfer.to_location_id"
                                    class="w-full px-3 py-2 border rounded-md focus:outline-none transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderWidth: '1px',
                                        borderStyle: 'solid',
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary
                                    }">
                                <option value="">Select destination location</option>
                                <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                                    {{ loc.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textPrimary }">Quantity</label>
                            <input type="number" v-model="newTransfer.quantity" step="0.01" min="0.01"
                                   class="w-full px-3 py-2 border rounded-md focus:outline-none transition-colors"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderWidth: '1px',
                                       borderStyle: 'solid',
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }"
                                   placeholder="Enter quantity">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textPrimary }">Notes</label>
                            <textarea v-model="newTransfer.notes" rows="3"
                                      class="w-full px-3 py-2 border rounded-md focus:outline-none transition-colors"
                                      :style="{
                                          backgroundColor: themeColors.background,
                                          borderWidth: '1px',
                                          borderStyle: 'solid',
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary
                                      }"
                                      placeholder="Enter notes (optional)"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" @click="showCreateModal = false"
                                class="px-4 py-2 rounded-md transition-colors font-medium"
                                :style="{
                                    backgroundColor: themeColors.secondary,
                                    color: themeColors.textPrimary
                                }">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 rounded-md transition-colors font-medium"
                                :style="{
                                    backgroundColor: themeColors.primary,
                                    color: '#000000'
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                            Create Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
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
    info: `var(--kotel-info)`,
    hover: `var(--kotel-primary-hover)`
}))

loadTheme()

// Props
const props = defineProps({
    user: Object,
    navigation: Array,
    transfers: Array,
    locations: { type: Array, default: () => [] }
})

// Data
const showCreateModal = ref(false)
const allProducts = ref([])
const newTransfer = ref({
    product_id: '',
    from_location_id: '',
    to_location_id: '',
    quantity: '',
    notes: ''
})

// Computed
const completedTransfers = computed(() => {
    return props.transfers.filter(t => t.status === 'completed').length
})

const pendingTransfers = computed(() => {
    return props.transfers.filter(t => t.status === 'pending').length
})

const totalQuantityTransferred = computed(() => {
    return props.transfers.reduce((sum, t) => sum + t.quantity, 0)
})

// Methods
const getTransferStatusClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const getTransferStatusLabel = (status) => {
    const labels = {
        'pending': 'Pending',
        'completed': 'Completed',
        'cancelled': 'Cancelled'
    }
    return labels[status] || status
}

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString()
}

const formatTime = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

const createTransfer = () => {
    if (!newTransfer.value.product_id || !newTransfer.value.from_location_id || !newTransfer.value.to_location_id || !newTransfer.value.quantity) {
        alert('Please fill in all required fields')
        return
    }

    if (newTransfer.value.from_location_id === newTransfer.value.to_location_id) {
        alert('Source and destination locations must be different')
        return
    }

    router.post('/pos/transfers', newTransfer.value, {
        onSuccess: () => {
            showCreateModal.value = false
            resetNewTransferForm()
        },
        onError: (errors) => {
            console.error('Error creating transfer:', errors)
            alert('Error creating transfer. Please try again.')
        }
    })
}

const viewTransfer = (transfer) => {
    // Navigate to transfer details
    console.log('View transfer:', transfer)
}

const exportTransfers = () => {
    const headers = ['Date', 'Product', 'From Location', 'To Location', 'Quantity', 'Status', 'User', 'Notes']
    const rows = props.transfers.map(transfer => [
        formatDate(transfer.created_at),
        transfer.product?.name || 'N/A',
        transfer.from_location?.name || '—',
        transfer.to_location?.name || '—',
        transfer.quantity,
        getTransferStatusLabel(transfer.status),
        transfer.user?.name || 'System',
        transfer.notes || '-'
    ])
    
    const csvContent = [headers, ...rows]
        .map(row => row.map(cell => `"${cell}"`).join(','))
        .join('\n')
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    
    link.setAttribute('href', url)
    link.setAttribute('download', `stock-transfers-${new Date().toISOString().split('T')[0]}.csv`)
    link.style.visibility = 'hidden'
    
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
}

const resetNewTransferForm = () => {
    newTransfer.value = {
        product_id: '',
        from_location_id: '',
        to_location_id: '',
        quantity: '',
        notes: ''
    }
}

// Load products for dropdown
onMounted(() => {
    axios.get('/pos/products-and-warehouses')
        .then(response => {
            allProducts.value = response.data.products || []
        })
        .catch(error => {
            console.error('Failed to load products:', error)
        })
})
</script>
