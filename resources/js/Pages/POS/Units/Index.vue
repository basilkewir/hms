<template>
    <DashboardLayout title="POS Units" :user="user">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">📏 POS Units</h1>
                    <p class="text-sm mt-2"
                       :style="{ color: themeColors.textSecondary }">Manage POS units of measurement.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="showCreateModal = true" 
                            class="px-4 py-2 rounded-md transition-colors font-medium flex items-center"
                            style="background-color: var(--kotel-primary); color: #000000;">
                        ➕ Add Unit
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <div class="flex items-center">
                    <div class="p-3 rounded-full mr-4"
                         :style="{ backgroundColor: themeColors.primary + '20' }">
                        <span class="text-2xl">📏</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Total Units</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ units.length }}</p>
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
                           :style="{ color: themeColors.textSecondary }">Active Units</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ units.filter(u => u.is_active).length }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Units Table -->
        <div class="shadow rounded-lg p-6"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold"
                        :style="{ color: themeColors.textPrimary }">Units Management</h2>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Manage measurement units for products.</p>
                </div>
                <!-- Search -->
                <div class="relative">
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Search units..."
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
                        <tr v-for="unit in filteredUnits" :key="unit.id" 
                            class="border transition-colors hover:bg-gray-50"
                            :style="{ borderColor: themeColors.border }">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div>
                                        <div class="font-medium"
                                             :style="{ color: themeColors.textPrimary }">{{ unit.name }}</div>
                                        <div class="text-sm"
                                             :style="{ color: themeColors.textSecondary }">{{ unit.code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textSecondary }">{{ unit.description || 'No description' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 text-xs rounded-full font-medium"
                                     :class="{
                                         'bg-green-100 text-green-800': unit.products_count > 0,
                                         'bg-gray-100 text-gray-800': unit.products_count === 0
                                     }">
                                    {{ unit.products_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full font-medium"
                                     :class="{
                                         'bg-green-100 text-green-800': unit.is_active,
                                         'bg-red-100 text-red-800': !unit.is_active
                                     }">
                                    {{ unit.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button @click="editUnit(unit)" 
                                            class="px-3 py-1 text-sm rounded-md transition-colors"
                                            :style="{ 
                                                backgroundColor: themeColors.primary,
                                                color: '#000000'
                                            }"
                                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                                        ✏️ Edit
                                    </button>
                                    <button @click="deleteUnit(unit)" 
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

        <!-- Create Unit Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     color: themeColors.textPrimary 
                 }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold mb-4">Add New Unit</h3>
                    <button @click="showCreateModal = false" 
                            class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="createUnit">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Name *</label>
                            <input v-model="newUnit.name" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="Enter unit name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Code *</label>
                            <input v-model="newUnit.code" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="Enter unit code">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Description</label>
                            <textarea v-model="newUnit.description" rows="3"
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="{ 
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px',
                                          borderStyle: 'solid'
                                      }"
                                      placeholder="Enter unit description"></textarea>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" v-model="newUnit.is_active" 
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
                            ➕ Add Unit
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Unit Modal -->
        <div v-if="showEditModal && selectedUnit" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     color: themeColors.textPrimary 
                 }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold mb-4">Edit Unit</h3>
                    <button @click="showEditModal = false; selectedUnit = null" 
                            class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="updateUnit">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Name *</label>
                            <input v-model="selectedUnit.name" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="Enter unit name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Code *</label>
                            <input v-model="selectedUnit.code" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="Enter unit code">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Description</label>
                            <textarea v-model="selectedUnit.description" rows="3"
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="{ 
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px',
                                          borderStyle: 'solid'
                                      }"
                                      placeholder="Enter unit description"></textarea>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" v-model="selectedUnit.is_active" 
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
                        <button type="button" @click="showEditModal = false; selectedUnit = null"
                                class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit"
                                class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                            💾 Update Unit
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
    units: {
        type: Array,
        default: () => []
    }
})

// Reactive data
const showCreateModal = ref(false)
const showEditModal = ref(false)
const selectedUnit = ref(null)
const searchQuery = ref('')

// New unit form
const newUnit = ref({
    name: '',
    code: '',
    description: '',
    is_active: true
})

// Computed properties
const filteredUnits = computed(() => {
    let filtered = props.units

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(unit => 
            unit.name.toLowerCase().includes(query) ||
            (unit.code && unit.code.toLowerCase().includes(query)) ||
            (unit.description && unit.description.toLowerCase().includes(query))
        )
    }

    return filtered
})

// Methods
const resetNewUnitForm = () => {
    newUnit.value = {
        name: '',
        code: '',
        description: '',
        is_active: true
    }
}

const editUnit = (unit) => {
    selectedUnit.value = { ...unit }
    showEditModal.value = true
}

const createUnit = () => {
    // Validate required fields
    if (!newUnit.value.name || !newUnit.value.code) {
        alert('Please fill in all required fields (Name, Code)')
        return
    }

    // Make API call to create unit
    router.post('/pos/units', newUnit.value, {
        onSuccess: () => {
            showCreateModal.value = false
            resetNewUnitForm()
            // Show success message
            const flash = page.props.flash || {}
            if (flash.success) {
                // Success message will be shown by the framework
            }
        },
        onError: (errors) => {
            console.error('Error creating unit:', errors)
            alert('Error creating unit. Please check form and try again.')
        }
    })
}

const updateUnit = () => {
    // Validate required fields
    if (!selectedUnit.value.name || !selectedUnit.value.code) {
        alert('Please fill in all required fields (Name, Code)')
        return
    }

    // Make API call to update unit
    router.put(`/pos/units/${selectedUnit.value.id}`, selectedUnit.value, {
        onSuccess: () => {
            showEditModal.value = false
            selectedUnit.value = null
            // Show success message
            const flash = page.props.flash || {}
            if (flash.success) {
                // Success message will be shown by the framework
            }
        },
        onError: (errors) => {
            console.error('Error updating unit:', errors)
            alert('Error updating unit. Please check form and try again.')
        }
    })
}

const deleteUnit = (unit) => {
    if (confirm(`Are you sure you want to delete the unit "${unit.name}"?`)) {
        router.delete(`/pos/units/${unit.id}`, {
            onSuccess: () => {
                // Unit will be removed from the list automatically
            },
            onError: (errors) => {
                console.error('Error deleting unit:', errors)
                alert('Error deleting unit. Please try again.')
            }
        })
    }
}
</script>
