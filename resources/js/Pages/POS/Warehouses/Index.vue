<template>
    <DashboardLayout title="Warehouses Management" :user="user">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold"
                        :style="{ color: themeColors.textPrimary }">Warehouses</h1>
                    <p class="text-sm mt-1"
                       :style="{ color: themeColors.textSecondary }">Manage warehouse locations and inventory</p>
                </div>
                <button @click="showCreateModal = true"
                        class="px-4 py-2 rounded-md transition-colors font-medium"
                        style="background-color: var(--kotel-primary); color: #000000;">
                    🏢 Add Warehouse
                </button>
            </div>

            <!-- Search and Filter -->
            <div class="bg-white rounded-lg p-4 shadow-sm"
                 :style="{ backgroundColor: themeColors.card }">
                <div class="flex items-center space-x-4">
                    <div class="flex-1">
                        <input v-model="searchQuery" type="text" placeholder="Search warehouses..."
                               class="w-full px-4 py-2 rounded-md border focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                    </div>
                </div>
            </div>

            <!-- Warehouses Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden"
                 :style="{ backgroundColor: themeColors.card }">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b"
                                :style="{ borderColor: themeColors.border }">
                            <tr class="text-left">
                                <th class="px-6 py-3 text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Name</th>
                                <th class="px-6 py-3 text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Location</th>
                                <th class="px-6 py-3 text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Products</th>
                                <th class="px-6 py-3 text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Status</th>
                                <th class="px-6 py-3 text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y"
                                :style="{ borderColor: themeColors.border }">
                            <tr v-for="warehouse in filteredWarehouses" :key="warehouse.id" 
                                class="hover:bg-gray-50 transition-colors"
                                :style="{ hoverBackgroundColor: themeColors.hover }">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                            <span class="text-blue-600 text-sm font-medium">🏢</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium"
                                                 :style="{ color: themeColors.textPrimary }">{{ warehouse.name }}</div>
                                            <div class="text-xs"
                                                 :style="{ color: themeColors.textSecondary }">{{ warehouse.code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textPrimary }">{{ warehouse.location }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :style="{ 
                                              backgroundColor: warehouse.products_count > 0 ? themeColors.success + '20' : themeColors.warning + '20',
                                              color: warehouse.products_count > 0 ? themeColors.success : themeColors.warning
                                          }">
                                        {{ warehouse.products_count }} products
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :style="{ 
                                              backgroundColor: warehouse.is_active ? themeColors.success + '20' : themeColors.danger + '20',
                                              color: warehouse.is_active ? themeColors.success : themeColors.danger
                                          }">
                                        {{ warehouse.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center space-x-2">
                                        <button @click="editWarehouse(warehouse)"
                                                class="text-blue-600 hover:text-blue-900"
                                                :style="{ color: themeColors.primary }">
                                            ✏️ Edit
                                        </button>
                                        <button @click="deleteWarehouse(warehouse)"
                                                class="text-red-600 hover:text-red-900"
                                                :style="{ color: themeColors.danger }">
                                            🗑️ Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create Warehouse Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     color: themeColors.textPrimary 
                 }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold mb-4">Add New Warehouse</h3>
                    <button @click="showCreateModal = false" 
                            class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="createWarehouse">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Name *</label>
                            <input v-model="newWarehouse.name" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="Enter warehouse name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Code *</label>
                            <input v-model="newWarehouse.code" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="Enter warehouse code">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Location</label>
                            <input v-model="newWarehouse.location"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="Enter warehouse location">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Description</label>
                            <textarea v-model="newWarehouse.description" rows="3"
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="{ 
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px',
                                          borderStyle: 'solid'
                                      }"
                                      placeholder="Enter warehouse description"></textarea>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" v-model="newWarehouse.is_active" 
                                       class="rounded border"
                                       :style="{ 
                                           backgroundColor: themeColors.background,
                                           borderColor: themeColors.border
                                       }">
                                <span class="ml-2 text-sm font-medium"
                                      :style="{ color: themeColors.textSecondary }">Active</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 mt-6">
                        <button type="button" @click="showCreateModal = false"
                                class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit"
                                class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                            ➕ Add Warehouse
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Warehouse Modal -->
        <div v-if="showEditModal && selectedWarehouse" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     color: themeColors.textPrimary 
                 }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold mb-4">Edit Warehouse</h3>
                    <button @click="showEditModal = false; selectedWarehouse = null" 
                            class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="updateWarehouse">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Name *</label>
                            <input v-model="selectedWarehouse.name" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="Enter warehouse name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Code *</label>
                            <input v-model="selectedWarehouse.code" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="Enter warehouse code">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Location</label>
                            <input v-model="selectedWarehouse.location"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="Enter warehouse location">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Description</label>
                            <textarea v-model="selectedWarehouse.description" rows="3"
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="{ 
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px',
                                          borderStyle: 'solid'
                                      }"
                                      placeholder="Enter warehouse description"></textarea>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" v-model="selectedWarehouse.is_active" 
                                       class="rounded border"
                                       :style="{ 
                                           backgroundColor: themeColors.background,
                                           borderColor: themeColors.border
                                       }">
                                <span class="ml-2 text-sm font-medium"
                                      :style="{ color: themeColors.textSecondary }">Active</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 mt-6">
                        <button type="button" @click="showEditModal = false; selectedWarehouse = null"
                                class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit"
                                class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                            💾 Update Warehouse
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'

const { loadTheme } = useTheme()
const page = usePage()
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
    hover: `rgba(255, 255, 255, 0.1)`
}))

loadTheme()

// Props
const props = defineProps({
    user: Object,
    warehouses: {
        type: Array,
        default: () => []
    }
})

// Reactive data
const showCreateModal = ref(false)
const showEditModal = ref(false)
const selectedWarehouse = ref(null)
const searchQuery = ref('')

// New warehouse form
const newWarehouse = ref({
    name: '',
    code: '',
    location: '',
    description: '',
    is_active: true
})

// Computed properties
const filteredWarehouses = computed(() => {
    let filtered = props.warehouses

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(warehouse => 
            warehouse.name.toLowerCase().includes(query) ||
            (warehouse.code && warehouse.code.toLowerCase().includes(query)) ||
            (warehouse.location && warehouse.location.toLowerCase().includes(query)) ||
            (warehouse.description && warehouse.description.toLowerCase().includes(query))
        )
    }

    return filtered
})

// Methods
const resetNewWarehouseForm = () => {
    newWarehouse.value = {
        name: '',
        code: '',
        location: '',
        description: '',
        is_active: true
    }
}

const editWarehouse = (warehouse) => {
    selectedWarehouse.value = { ...warehouse }
    showEditModal.value = true
}

const createWarehouse = () => {
    // Validate required fields
    if (!newWarehouse.value.name || !newWarehouse.value.code) {
        alert('Please fill in all required fields (Name, Code)')
        return
    }

    // Make API call to create warehouse
    router.post('/pos/warehouses', newWarehouse.value, {
        onSuccess: () => {
            showCreateModal.value = false
            resetNewWarehouseForm()
            // Show success message
            const flash = page.props.flash || {}
            if (flash.success) {
                // Success message will be shown by the framework
            }
        },
        onError: (errors) => {
            console.error('Error creating warehouse:', errors)
            alert('Error creating warehouse. Please check form and try again.')
        }
    })
}

const updateWarehouse = () => {
    // Validate required fields
    if (!selectedWarehouse.value.name || !selectedWarehouse.value.code) {
        alert('Please fill in all required fields (Name, Code)')
        return
    }

    // Make API call to update warehouse
    router.put(`/pos/warehouses/${selectedWarehouse.value.id}`, selectedWarehouse.value, {
        onSuccess: () => {
            showEditModal.value = false
            selectedWarehouse.value = null
            // Show success message
            const flash = page.props.flash || {}
            if (flash.success) {
                // Success message will be shown by the framework
            }
        },
        onError: (errors) => {
            console.error('Error updating warehouse:', errors)
            alert('Error updating warehouse. Please check form and try again.')
        }
    })
}

const deleteWarehouse = (warehouse) => {
    if (confirm(`Are you sure you want to delete warehouse "${warehouse.name}"?`)) {
        router.delete(`/pos/warehouses/${warehouse.id}`, {
            onSuccess: () => {
                // Warehouse will be removed from the list automatically
            },
            onError: (errors) => {
                console.error('Error deleting warehouse:', errors)
                alert('Error deleting warehouse. Please try again.')
            }
        })
    }
}
</script>
