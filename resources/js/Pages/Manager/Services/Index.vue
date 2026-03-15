<template>
    <DashboardLayout title="Hotel Services" :user="user" :navigation="navigation">
        <!-- Header -->
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
             class="shadow rounded-lg p-6 mb-8 border">
            <div class="flex items-center justify-between">
                <div>
                    <h1 :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">Hotel Services Management</h1>
                    <p :style="{ color: themeColors.textSecondary }" class="mt-2">Manage paid and free services like car wash, laundry, etc</p>
                </div>
                <button @click="showCreateModal = true" 
                        :style="{ backgroundColor: themeColors.primary, color: '#000' }"
                        class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity">
                    Add New Service
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <CubeIcon :style="{ color: themeColors.primary }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Total Services</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ stats?.total || 0 }}</p>
                    </div>
                </div>
            </div>
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <CheckCircleIcon :style="{ color: themeColors.success }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Active</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ stats?.active || 0 }}</p>
                    </div>
                </div>
            </div>
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <GiftIcon :style="{ color: themeColors.success }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Free Services</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ stats?.free || 0 }}</p>
                    </div>
                </div>
            </div>
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <CurrencyDollarIcon :style="{ color: themeColors.primary }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Paid Services</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ stats?.paid || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services Table -->
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
             class="shadow rounded-lg overflow-hidden border">
            <div :style="{ borderColor: themeColors.border }" class="px-6 py-4 border-b">
                <h3 :style="{ color: themeColors.textPrimary }" class="text-lg font-medium">Services List</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Name</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Category</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Type</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Price</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="service in serviceList || []" :key="service?.id"
                            :style="hoveredRow === service?.id ? { backgroundColor: themeColors.hover } : {}"
                            @mouseenter="hoveredRow = service?.id"
                            @mouseleave="hoveredRow = null"
                            class="transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div :style="{ color: themeColors.textPrimary }" class="font-medium">{{ service?.name || 'N/A' }}</div>
                                <div :style="{ color: themeColors.textTertiary }" class="text-sm">{{ service?.description || '' }}</div>
                            </td>
                            <td :style="{ color: themeColors.textPrimary }" class="px-6 py-4 whitespace-nowrap text-sm capitalize">
                                {{ service?.category?.replace('_', ' ') || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full"
                                      :class="service?.is_free ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'">
                                    {{ service?.is_free ? 'Free' : 'Paid' }}
                                </span>
                            </td>
                            <td :style="{ color: themeColors.textPrimary }" class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <span v-if="service?.is_free">Free</span>
                                <span v-else>{{ formatCurrency(service?.price || 0) }} / {{ service?.pricing_type?.replace('_', ' ') || 'unit' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full"
                                      :class="service?.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    {{ service?.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button @click="editService(service)" 
                                            :style="{ color: themeColors.primary }" 
                                            class="hover:opacity-80">Edit</button>
                                    <button @click="deleteService(service)" 
                                            :style="{ color: themeColors.danger }" 
                                            class="hover:opacity-80">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination
                v-if="!Array.isArray(services)"
                :links="services?.links"
                :meta="services"
            />
        </div>

        <!-- Create/Edit Modal -->
        <DialogModal :show="showCreateModal || editingService" @close="closeModal">
            <template #title>
                {{ editingService ? 'Edit Service' : 'Create New Service' }}
            </template>

            <template #content>
                <form @submit.prevent="saveService" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Service Name *</label>
                        <input v-model="serviceForm.name" type="text" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Category *</label>
                        <select v-model="serviceForm.category" required
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="laundry">Laundry</option>
                            <option value="car_wash">Car Wash</option>
                            <option value="spa">Spa</option>
                            <option value="transport">Transport</option>
                            <option value="room">Room Service</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Description</label>
                        <textarea v-model="serviceForm.description" rows="3"
                                  class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                  :style="{ 
                                      backgroundColor: themeColors.background,
                                      borderColor: themeColors.border,
                                      color: themeColors.textPrimary,
                                      borderWidth: '1px',
                                      borderStyle: 'solid'
                                  }"></textarea>
                    </div>

                    <div>
                        <label class="flex items-center space-x-2">
                            <input v-model="serviceForm.is_free" type="checkbox"
                                   class="rounded focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                            <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">Free Service</span>
                        </label>
                    </div>

                    <div v-if="!serviceForm.is_free">
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Price *</label>
                        <input v-model="serviceForm.price" type="number" step="0.01" min="0" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Pricing Type *</label>
                        <select v-model="serviceForm.pricing_type" required
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="per_service">Per Service</option>
                            <option value="per_person">Per Person</option>
                            <option value="per_night">Per Night</option>
                        </select>
                    </div>

                    <div>
                        <label class="flex items-center space-x-2">
                            <input v-model="serviceForm.is_active" type="checkbox"
                                   class="rounded focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                            <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">Active</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end space-x-4 pt-4" 
                         :style="{ 
                             borderTop: `1px solid ${themeColors.border}`,
                             borderStyle: 'solid'
                         }">
                        <button type="button" @click="closeModal"
                                class="px-6 py-2 rounded-md transition-colors font-medium"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    color: themeColors.textSecondary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid',
                                    borderColor: themeColors.border
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                            Cancel
                        </button>
                        <button type="submit" :disabled="serviceForm.processing"
                                class="px-6 py-2 rounded-md transition-colors font-medium text-white"
                                :style="{ 
                                    backgroundColor: serviceForm.processing ? themeColors.secondary : themeColors.primary,
                                }"
                                @mouseenter="!serviceForm.processing && ($event.target.style.backgroundColor = themeColors.hover)"
                                @mouseleave="!serviceForm.processing && ($event.target.style.backgroundColor = themeColors.primary)">
                            <span v-if="serviceForm.processing">Saving...</span>
                            <span v-else>Save Service</span>
                        </button>
                    </div>
                </form>
            </template>
        </DialogModal>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DialogModal from '@/Components/DialogModal.vue'
import Pagination from '@/Components/Pagination.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'
import { useNotification } from '@/Composables/useNotification'
import { formatCurrency } from '@/Utils/currency.js'
import {
    CubeIcon,
    CheckCircleIcon,
    GiftIcon,
    CurrencyDollarIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    services: {
        type: [Object, Array],
        default: () => ({ data: [] })
    },
    stats: Object,
})

const serviceList = computed(() => {
    if (Array.isArray(props.services)) return props.services
    return props.services?.data || []
})

const { currentTheme } = useTheme()
const navigation = computed(() => getNavigationForRole('admin'))
const notification = useNotification()
const hoveredRow = ref(null)

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

const showCreateModal = ref(false)
const editingService = ref(null)

const serviceForm = useForm({
    name: '',
    category: 'other',
    description: '',
    price: 0,
    is_free: false,
    pricing_type: 'per_service',
    is_active: true,
})

const editService = (service) => {
    editingService.value = service
    serviceForm.name = service.name
    serviceForm.category = service.category
    serviceForm.description = service.description || ''
    serviceForm.price = service.price
    serviceForm.is_free = service.is_free
    serviceForm.pricing_type = service.pricing_type
    serviceForm.is_active = service.is_active
}

const saveService = () => {
    if (editingService.value) {
        serviceForm.put(route('admin.services.update', editingService.value.id), {
            onSuccess: () => {
                notification.success('Service updated successfully')
                closeModal()
            }
        })
    } else {
        serviceForm.post(route('admin.services.store'), {
            onSuccess: () => {
                notification.success('Service created successfully')
                closeModal()
            }
        })
    }
}

const deleteService = (service) => {
    if (confirm(`Are you sure you want to delete "${service?.name || 'this service'}"?`)) {
        router.delete(route('admin.services.destroy', service?.id), {
            onSuccess: () => {
                notification.success('Service deleted successfully')
            }
        })
    }
}

const closeModal = () => {
    showCreateModal.value = false
    editingService.value = null
    serviceForm.reset()
}
</script>
