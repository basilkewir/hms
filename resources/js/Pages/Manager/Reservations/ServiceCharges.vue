<script setup>
import { ref, computed } from 'vue'
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
    hover: `rgba(255, 255, 255, 0.1)`
}))

loadTheme()

const props = defineProps({
    user: Object,
    navigation: Object
})

// Service charges data
const serviceCharges = ref([
    {
        id: 1,
        name: 'Room Service',
        description: 'In-room dining and beverage service',
        rate: 15.00,
        type: 'percentage',
        active: true
    },
    {
        id: 2,
        name: 'Laundry Service',
        description: 'Professional laundry and dry cleaning',
        rate: 25.00,
        type: 'fixed',
        active: true
    },
    {
        id: 3,
        name: 'Spa Access',
        description: 'Access to hotel spa and wellness facilities',
        rate: 50.00,
        type: 'fixed',
        active: true
    },
    {
        id: 4,
        name: 'Airport Transfer',
        description: 'Airport pickup and drop-off service',
        rate: 75.00,
        type: 'fixed',
        active: false
    }
])

const searchQuery = ref('')
const showAddModal = ref(false)
const editingCharge = ref(null)

// Computed properties
const filteredCharges = computed(() => {
    if (!searchQuery.value) return serviceCharges.value
    
    return serviceCharges.value.filter(charge => 
        charge.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        charge.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})

const activeCharges = computed(() => {
    return serviceCharges.value.filter(charge => charge.active)
})

const isEditing = computed(() => {
    return editingCharge.value?.id != null
})

// Methods
const formatRate = (charge) => {
    if (charge.type === 'percentage') {
        return `${charge.rate}%`
    }
    return `${charge.rate.toFixed(2)} FCFA`
}

const toggleStatus = (charge) => {
    charge.active = !charge.active
}

const editServiceCharge = (charge) => {
    editingCharge.value = { ...charge }
    showAddModal.value = true
}

const addServiceCharge = () => {
    editingCharge.value = {
        id: null,
        name: '',
        description: '',
        rate: 0,
        type: 'percentage',
        active: true,
    }
    showAddModal.value = true
}

const saveServiceCharge = () => {
    if (isEditing.value) {
        // Update existing charge
        const index = serviceCharges.value.findIndex(c => c.id === editingCharge.value.id)
        if (index !== -1) {
            serviceCharges.value[index] = { ...editingCharge.value }
        }
    } else {
        // Add new charge
        const newCharge = {
            id: Math.max(...serviceCharges.value.map(c => c.id)) + 1,
            name: editingCharge.value?.name || 'New Service Charge',
            description: editingCharge.value?.description || 'Description for new service charge',
            rate: Number(editingCharge.value?.rate ?? 10.00),
            type: editingCharge.value?.type || 'percentage',
            active: Boolean(editingCharge.value?.active ?? true)
        }
        serviceCharges.value.push(newCharge)
    }
    
    showAddModal.value = false
    editingCharge.value = {
        id: null,
        name: '',
        description: '',
        rate: 0,
        type: 'percentage',
        active: true,
    }
}

const deleteServiceCharge = (chargeId) => {
    if (confirm('Are you sure you want to delete this service charge?')) {
        const index = serviceCharges.value.findIndex(c => c.id === chargeId)
        if (index !== -1) {
            serviceCharges.value.splice(index, 1)
        }
    }
}
</script>

<template>
    <DashboardLayout title="Service Charges" :user="user">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
              }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold"
                        :style="{ color: themeColors.textPrimary }">Service Charges</h1>
                    <p class="mt-2"
                        :style="{ color: themeColors.textSecondary }">
                        Manage hotel service charges and additional fees
                    </p>
                </div>
                <button @click="addServiceCharge"
                    class="px-4 py-2 rounded-lg font-medium transition-colors"
                    :style="{
                        backgroundColor: themeColors.primary,
                        color: '#ffffff'
                    }">
                    Add Service Charge
                </button>
            </div>
        </div>

        <!-- Search and Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="md:col-span-2">
                <div class="rounded-lg p-6 border shadow-sm"
                     :style="{
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                      }">
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Search service charges..."
                        class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                        :style="{
                            backgroundColor: themeColors.background,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary,
                            focusRingColor: themeColors.primary
                        }"
                    />
                </div>
            </div>
            
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                  }">
                <div class="text-sm font-medium mb-2"
                    :style="{ color: themeColors.textSecondary }">Active Charges</div>
                <div class="text-2xl font-bold"
                    :style="{ color: themeColors.textPrimary }">{{ activeCharges.length }}</div>
            </div>
        </div>

        <!-- Service Charges List -->
        <div class="shadow rounded-lg p-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
              }">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b"
                            :style="{ borderColor: themeColors.border }">
                            <th class="text-left py-3 px-4 font-medium"
                                :style="{ color: themeColors.textPrimary }">Name</th>
                            <th class="text-left py-3 px-4 font-medium"
                                :style="{ color: themeColors.textPrimary }">Description</th>
                            <th class="text-left py-3 px-4 font-medium"
                                :style="{ color: themeColors.textPrimary }">Rate</th>
                            <th class="text-left py-3 px-4 font-medium"
                                :style="{ color: themeColors.textPrimary }">Type</th>
                            <th class="text-left py-3 px-4 font-medium"
                                :style="{ color: themeColors.textPrimary }">Status</th>
                            <th class="text-left py-3 px-4 font-medium"
                                :style="{ color: themeColors.textPrimary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="charge in filteredCharges" :key="charge.id"
                            class="border-b transition-colors hover:bg-opacity-50"
                            :style="{ 
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary
                            }">
                            <td class="py-3 px-4"
                                :style="{ color: themeColors.textPrimary }">{{ charge.name }}</td>
                            <td class="py-3 px-4"
                                :style="{ color: themeColors.textSecondary }">{{ charge.description }}</td>
                            <td class="py-3 px-4"
                                :style="{ color: themeColors.textPrimary }">{{ formatRate(charge) }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 rounded text-xs font-medium"
                                    :class="charge.type === 'percentage' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'">
                                    {{ charge.type }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 rounded text-xs font-medium"
                                    :class="charge.active ? 'bg-green-100 text-green-800' : 'bg-black text-white border border-gray-700'">
                                    {{ charge.active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex space-x-2">
                                    <button @click="toggleStatus(charge)"
                                        class="text-sm font-medium transition-colors"
                                        :style="{ color: themeColors.primary }">
                                        {{ charge.active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                    <button @click="editServiceCharge(charge)"
                                        class="text-sm font-medium transition-colors"
                                        :style="{ color: themeColors.primary }">
                                        Edit
                                    </button>
                                    <button @click="deleteServiceCharge(charge.id)"
                                        class="text-sm font-medium text-red-600 hover:text-red-800">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div v-if="filteredCharges.length === 0" class="text-center py-8"
                    :style="{ color: themeColors.textSecondary }">
                    No service charges found
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="rounded-lg p-6 w-full max-w-md"
                :style="{ backgroundColor: themeColors.card }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">
                    {{ isEditing ? 'Edit Service Charge' : 'Add Service Charge' }}
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                            :style="{ color: themeColors.textPrimary }">Name</label>
                        <input v-model="editingCharge.name" type="text"
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                            :style="{
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary,
                                focusRingColor: themeColors.primary
                            }" />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2"
                            :style="{ color: themeColors.textPrimary }">Description</label>
                        <textarea v-model="editingCharge.description" rows="3"
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                            :style="{
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary,
                                focusRingColor: themeColors.primary
                            }"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2"
                            :style="{ color: themeColors.textPrimary }">Rate</label>
                        <input v-model.number="editingCharge.rate" type="number" step="0.01"
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                            :style="{
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary,
                                focusRingColor: themeColors.primary
                            }" />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2"
                            :style="{ color: themeColors.textPrimary }">Type</label>
                        <select v-model="editingCharge.type"
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                            :style="{
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary,
                                focusRingColor: themeColors.primary
                            }">
                            <option value="percentage">Percentage</option>
                            <option value="fixed">Fixed Amount</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button @click="showAddModal = false"
                        class="px-4 py-2 rounded-lg font-medium transition-colors"
                        :style="{
                            backgroundColor: themeColors.background,
                            color: themeColors.textPrimary,
                            borderColor: themeColors.border,
                            borderWidth: '1px'
                        }">
                        Cancel
                    </button>
                    <button @click="saveServiceCharge"
                        class="px-4 py-2 rounded-lg font-medium transition-colors"
                        :style="{
                            backgroundColor: themeColors.primary,
                            color: '#ffffff'
                        }">
                        {{ isEditing ? 'Update' : 'Add' }}
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
/* Component specific styles */
</style>
