<template>
    <DashboardLayout title="Categories Management" :user="user">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">🏷️ Categories Management</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Manage product categories for better organization.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="showCreateModal = true" 
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        ➕ Add Category
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
                        <span class="text-2xl">🏷️</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Total Categories</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ categories.length }}</p>
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
                        <span class="text-2xl">📊</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Active Categories</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ categories.filter(c => c.is_active).length }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories Table -->
        <div class="shadow rounded-lg p-6"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold"
                        :style="{ color: themeColors.textPrimary }">Categories Management</h2>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Manage product categories for better organization.</p>
                </div>
                <!-- Search -->
                <div class="relative">
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Search categories..."
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
                                :style="{ color: themeColors.textSecondary }">Color</th>
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
                        <tr v-for="category in filteredCategories" :key="category.id" 
                            class="border transition-colors hover:bg-gray-50"
                            :style="{ borderColor: themeColors.border }">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div>
                                        <span class="text-2xl mr-2">{{ category.emoji || '🏷️' }}</span>
                                        <div class="font-medium"
                                             :style="{ color: themeColors.textPrimary }">{{ category.name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center"
                                     :style="{ backgroundColor: category.color || '#6b7280' }">
                                    &nbsp;
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textSecondary }">{{ category.description || 'No description' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 text-xs rounded-full font-medium"
                                     :class="{
                                         'bg-green-100 text-green-800': category.products_count > 0,
                                         'bg-gray-100 text-gray-800': category.products_count === 0
                                     }">
                                    {{ category.products_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full font-medium"
                                     :class="{
                                         'bg-green-100 text-green-800': category.is_active,
                                         'bg-red-100 text-red-800': !category.is_active
                                     }">
                                    {{ category.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button @click="editCategory(category)" 
                                            class="px-3 py-1 text-sm rounded-md transition-colors"
                                            :style="{ 
                                                backgroundColor: themeColors.primary,
                                                color: 'white'
                                            }"
                                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                                        ✏️ Edit
                                    </button>
                                    <button @click="deleteCategory(category)" 
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

        <!-- Create Category Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6" 
                 :style="{ 
                     backgroundColor: themeColors.card,
                     color: themeColors.textPrimary 
                 }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold mb-4">Add New Category</h3>
                    <button @click="showCreateModal = false" 
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="createCategory" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Name *</label>
                        <input v-model="newCategory.name" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }"
                               placeholder="Enter category name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Description</label>
                        <textarea v-model="newCategory.description" rows="3"
                                  class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                  :style="{ 
                                      backgroundColor: themeColors.background,
                                      borderColor: themeColors.border,
                                      color: themeColors.textPrimary,
                                      borderWidth: '1px',
                                      borderStyle: 'solid'
                                  }"
                                  placeholder="Enter category description"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Color</label>
                        <input v-model="newCategory.color" type="color"
                               class="w-full h-10 rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" v-model="newCategory.is_active" 
                                   class="rounded border focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border
                                   }">
                            <span class="ml-2 text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">Active</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end space-x-4 mt-6">
                        <button type="button" @click="showCreateModal = false"
                                class="px-6 py-2 rounded-md transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.secondary,
                                    color: themeColors.textPrimary 
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-2 rounded-md transition-colors font-medium text-white"
                                :style="{ 
                                    backgroundColor: themeColors.primary,
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                            Add Category
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Category Modal -->
        <div v-if="showEditModal && selectedCategory" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6" 
                 :style="{ 
                     backgroundColor: themeColors.card,
                     color: themeColors.textPrimary 
                 }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold mb-4">Edit Category</h3>
                    <button @click="showEditModal = false; selectedCategory = null" 
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="updateCategory" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Name *</label>
                        <input v-model="selectedCategory.name" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }"
                               placeholder="Enter category name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Description</label>
                        <textarea v-model="selectedCategory.description" rows="3"
                                  class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                  :style="{ 
                                      backgroundColor: themeColors.background,
                                      borderColor: themeColors.border,
                                      color: themeColors.textPrimary,
                                      borderWidth: '1px',
                                      borderStyle: 'solid'
                                  }"
                                  placeholder="Enter category description"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Color</label>
                        <input v-model="selectedCategory.color" type="color"
                               class="w-full h-10 rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" v-model="selectedCategory.is_active" 
                                   class="rounded border focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border
                                   }">
                            <span class="ml-2 text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">Active</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end space-x-4 mt-6">
                        <button type="button" @click="showEditModal = false; selectedCategory = null"
                                class="px-6 py-2 rounded-md transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.secondary,
                                    color: themeColors.textPrimary 
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-2 rounded-md transition-colors font-medium text-white"
                                :style="{ 
                                    backgroundColor: themeColors.primary,
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                            Update Category
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
    categories: Array
})

// Reactive data
const showCreateModal = ref(false)
const showEditModal = ref(false)
const selectedCategory = ref(null)
const searchQuery = ref('')

// New category form
const newCategory = ref({
    name: '',
    description: '',
    color: '#6b7280',
    is_active: true
})

// Computed properties
const filteredCategories = computed(() => {
    let filtered = props.categories

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(category => 
            category.name.toLowerCase().includes(query) ||
            (category.description && category.description.toLowerCase().includes(query))
        )
    }

    return filtered
})

// Methods
const resetNewCategoryForm = () => {
    newCategory.value = {
        name: '',
        description: '',
        color: '#6b7280',
        is_active: true
    }
}

const editCategory = (category) => {
    selectedCategory.value = { ...category }
    showEditModal.value = true
}

const createCategory = () => {
    // Validate required fields
    if (!newCategory.value.name) {
        alert('Please fill in category name')
        return
    }

    // Make API call to create category
    router.post('/pos/categories', newCategory.value, {
        onSuccess: () => {
            showCreateModal.value = false
            resetNewCategoryForm()
            // Show success message
            const flash = page.props.flash || {}
            if (flash.success) {
                // Success message will be shown by the framework
            }
        },
        onError: (errors) => {
            console.error('Error creating category:', errors)
            alert('Error creating category. Please check the form and try again.')
        }
    })
}

const updateCategory = () => {
    // Validate required fields
    if (!selectedCategory.value.name) {
        alert('Please fill in category name')
        return
    }

    // Make API call to update category
    router.put(`/pos/categories/${selectedCategory.value.id}`, selectedCategory.value, {
        onSuccess: () => {
            showEditModal.value = false
            selectedCategory.value = null
            // Show success message
            const flash = page.props.flash || {}
            if (flash.success) {
                // Success message will be shown by the framework
            }
        },
        onError: (errors) => {
            console.error('Error updating category:', errors)
            alert('Error updating category. Please check the form and try again.')
        }
    })
}

const deleteCategory = (category) => {
    if (confirm(`Are you sure you want to delete the category "${category.name}"?`)) {
        router.delete(`/pos/categories/${category.id}`, {
            onSuccess: () => {
                // Category will be removed from the list automatically
            },
            onError: (errors) => {
                console.error('Error deleting category:', errors)
                alert('Error deleting category. Please try again.')
            }
        })
    }
}
</script>
