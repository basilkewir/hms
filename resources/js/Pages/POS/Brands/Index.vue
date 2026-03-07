<template>
    <DashboardLayout title="Brands Management" :user="user">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">🏢 Brands Management</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Manage product brands for better organization.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="showCreateModal = true" 
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        ➕ Add Brand
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <div class="flex items-center">
                    <div class="p-3 rounded-full mr-4"
                         :style="{ backgroundColor: themeColors.primary + '20' }">
                        <span class="text-2xl">🏢</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Total Brands</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ brands.length }}</p>
                    </div>
                </div>
            </div>
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <div class="flex items-center">
                    <div class="p-3 rounded-full mr-4"
                         :style="{ backgroundColor: '#10b98120' }">
                        <span class="text-2xl">⚡</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Active Brands</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ brands.filter(b => b.is_active).length }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Brands Table -->
        <div class="shadow rounded-lg p-6"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold"
                        :style="{ color: themeColors.textPrimary }">Brands Management</h2>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Manage product brands for better organization.</p>
                </div>
                <!-- Search -->
                <div class="relative">
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Search brands..."
                        class="w-full px-4 py-2 rounded-md border"
                        :style="{ 
                            backgroundColor: themeColors.card,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary 
                        }"
                    >
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <svg class="w-5 h-5" :style="{ color: themeColors.textSecondary }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-6a2 2 0 01-2.828 2.828l6-6 6-6z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.card }">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Products Count</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="brand in filteredBrands" :key="brand.id" 
                            class="border transition-colors hover:bg-gray-50"
                            :style="{ borderColor: themeColors.border }">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div>
                                        <div class="font-medium"
                                             :style="{ color: themeColors.textPrimary }">{{ brand.name }}</div>
                                        <div class="text-sm"
                                             :style="{ color: themeColors.textSecondary }">{{ brand.code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textSecondary }">{{ brand.description || 'No description' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 text-xs rounded-full font-medium"
                                     :class="{
                                         'bg-green-100 text-green-800': brand.products_count > 0,
                                         'bg-gray-100 text-gray-800': brand.products_count === 0
                                     }">
                                    {{ brand.products_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full font-medium"
                                     :class="{
                                         'bg-green-100 text-green-800': brand.is_active,
                                         'bg-red-100 text-red-800': !brand.is_active
                                     }">
                                    {{ brand.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button @click="editBrand(brand)" 
                                            class="px-3 py-1 text-sm rounded-md transition-colors"
                                            :style="{ 
                                                backgroundColor: themeColors.primary,
                                                color: 'white'
                                            }"
                                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                                        ✏️ Edit
                                    </button>
                                    <button @click="deleteBrand(brand)" 
                                            class="px-3 py-1 text-sm rounded-md transition-colors"
                                            :style="{ 
                                                backgroundColor: themeColors.danger,
                                                color: 'white'
                                            }"
                                            @mouseenter="$event.target.style.backgroundColor = '#dc2626'"
                                            @mouseleave="$event.target.style.backgroundColor = themeColors.danger">
                                        🗑️ Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create Brand Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     color: themeColors.textPrimary 
                 }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold mb-4">Add New Brand</h3>
                    <button @click="showCreateModal = false" 
                            class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="createBrand">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Name *</label>
                            <input v-model="newBrand.name" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="Enter brand name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Code *</label>
                            <input v-model="newBrand.code" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="Enter brand code">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Description</label>
                            <textarea v-model="newBrand.description" rows="3"
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="{ 
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px',
                                          borderStyle: 'solid'
                                      }"
                                      placeholder="Enter brand description"></textarea>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" v-model="newBrand.is_active" 
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
                            ➕ Add Brand
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Brand Modal -->
        <div v-if="showEditModal && selectedBrand" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     color: themeColors.textPrimary 
                 }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold mb-4">Edit Brand</h3>
                    <button @click="showEditModal = false; selectedBrand = null" 
                            class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="updateBrand">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Name *</label>
                            <input v-model="selectedBrand.name" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="Enter brand name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Code *</label>
                            <input v-model="selectedBrand.code" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="Enter brand code">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Description</label>
                            <textarea v-model="selectedBrand.description" rows="3"
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="{ 
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px',
                                          borderStyle: 'solid'
                                      }"
                                      placeholder="Enter brand description"></textarea>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" v-model="selectedBrand.is_active" 
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
                        <button type="button" @click="showEditModal = false; selectedBrand = null"
                                class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit"
                                class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                            💾 Update Brand
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
    brands: Array
})

// Reactive data
const showCreateModal = ref(false)
const showEditModal = ref(false)
const selectedBrand = ref(null)
const searchQuery = ref('')

// New brand form
const newBrand = ref({
    name: '',
    code: '',
    description: '',
    is_active: true
})

// Computed properties
const filteredBrands = computed(() => {
    let filtered = props.brands

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(brand => 
            brand.name.toLowerCase().includes(query) ||
            (brand.code && brand.code.toLowerCase().includes(query)) ||
            (brand.description && brand.description.toLowerCase().includes(query))
        )
    }

    return filtered
})

// Methods
const resetNewBrandForm = () => {
    newBrand.value = {
        name: '',
        code: '',
        description: '',
        is_active: true
    }
}

const editBrand = (brand) => {
    selectedBrand.value = { ...brand }
    showEditModal.value = true
}

const createBrand = () => {
    // Validate required fields
    if (!newBrand.value.name || !newBrand.value.code) {
        alert('Please fill in all required fields (Name, Code)')
        return
    }

    // Make API call to create brand
    router.post('/pos/brands', newBrand.value, {
        onSuccess: () => {
            showCreateModal.value = false
            resetNewBrandForm()
            // Show success message
            const flash = page.props.flash || {}
            if (flash.success) {
                // Success message will be shown by the framework
            }
        },
        onError: (errors) => {
            console.error('Error creating brand:', errors)
            alert('Error creating brand. Please check the form and try again.')
        }
    })
}

const updateBrand = () => {
    // Validate required fields
    if (!selectedBrand.value.name || !selectedBrand.value.code) {
        alert('Please fill in all required fields (Name, Code)')
        return
    }

    // Make API call to update brand
    router.put(`/pos/brands/${selectedBrand.value.id}`, selectedBrand.value, {
        onSuccess: () => {
            showEditModal.value = false
            selectedBrand.value = null
            // Show success message
            const flash = page.props.flash || {}
            if (flash.success) {
                // Success message will be shown by the framework
            }
        },
        onError: (errors) => {
            console.error('Error updating brand:', errors)
            alert('Error updating brand. Please check the form and try again.')
        }
    })
}

const deleteBrand = (brand) => {
    if (confirm(`Are you sure you want to delete the brand "${brand.name}"?`)) {
        router.delete(`/pos/brands/${brand.id}`, {
            onSuccess: () => {
                // Brand will be removed from the list automatically
            },
            onError: (errors) => {
                console.error('Error deleting brand:', errors)
                alert('Error deleting brand. Please try again.')
            }
        })
    }
}
</script>
