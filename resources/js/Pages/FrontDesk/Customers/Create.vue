<template>
    <DashboardLayout title="Create Customer">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Create New Customer</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Add a new customer to the system.</p>
                </div>
                <Link :href="route('front-desk.customers.index')"
                      class="px-4 py-2 rounded-md transition-colors"
                      :style="{ 
                          backgroundColor: themeColors.secondary,
                          color: themeColors.textPrimary 
                      }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                    Back to Customers
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">First Name *</label>
                            <input type="text" v-model="form.first_name" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                            <div v-if="form.errors.first_name" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.first_name }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Last Name *</label>
                            <input type="text" v-model="form.last_name" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                            <div v-if="form.errors.last_name" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.last_name }}</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Contact Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Email</label>
                            <input type="email" v-model="form.email"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                            <div v-if="form.errors.email" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.email }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Phone</label>
                            <input type="tel" v-model="form.phone"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                            <div v-if="form.errors.phone" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.phone }}</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Address Information</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Address</label>
                            <input type="text" v-model="form.address"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                            <div v-if="form.errors.address" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.address }}</div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2"
                                       :style="{ color: themeColors.textSecondary }">City</label>
                                <input type="text" v-model="form.city"
                                       class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                       :style="{ 
                                           backgroundColor: themeColors.background,
                                           borderColor: themeColors.border,
                                           color: themeColors.textPrimary,
                                           borderWidth: '1px',
                                           borderStyle: 'solid'
                                       }">
                                <div v-if="form.errors.city" class="mt-1 text-sm"
                                     :style="{ color: themeColors.danger }">{{ form.errors.city }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2"
                                       :style="{ color: themeColors.textSecondary }">State/Province</label>
                                <input type="text" v-model="form.state"
                                       class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                       :style="{ 
                                           backgroundColor: themeColors.background,
                                           borderColor: themeColors.border,
                                           color: themeColors.textPrimary,
                                           borderWidth: '1px',
                                           borderStyle: 'solid'
                                       }">
                                <div v-if="form.errors.state" class="mt-1 text-sm"
                                     :style="{ color: themeColors.danger }">{{ form.errors.state }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2"
                                       :style="{ color: themeColors.textSecondary }">Postal Code</label>
                                <input type="text" v-model="form.postal_code"
                                       class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                       :style="{ 
                                           backgroundColor: themeColors.background,
                                           borderColor: themeColors.border,
                                           color: themeColors.textPrimary,
                                           borderWidth: '1px',
                                           borderStyle: 'solid'
                                       }">
                                <div v-if="form.errors.postal_code" class="mt-1 text-sm"
                                     :style="{ color: themeColors.danger }">{{ form.errors.postal_code }}</div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Country</label>
                            <input type="text" v-model="form.country"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                            <div v-if="form.errors.country" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.country }}</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Additional Information</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Customer Group</label>
                            <select v-model="form.customer_group_id"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="">Select Customer Group</option>
                                <option v-for="group in customerGroups" :key="group.id" :value="group.id">
                                    {{ group.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.customer_group_id" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.customer_group_id }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Notes</label>
                            <textarea v-model="form.notes" rows="4"
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="{ 
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px',
                                          borderStyle: 'solid'
                                      }"></textarea>
                            <div v-if="form.errors.notes" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.notes }}</div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <Link :href="route('front-desk.customers.index')"
                          class="px-4 py-2 rounded-md transition-colors"
                          :style="{ 
                              backgroundColor: themeColors.secondary,
                              color: themeColors.textPrimary 
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                            class="px-6 py-2 rounded-md transition-colors text-white"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <span v-if="form.processing">Creating...</span>
                        <span v-else>Create Customer</span>
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'

// Initialize theme
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

// Load theme on mount
loadTheme()

const props = defineProps({
    customerGroups: Array
})

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    state: '',
    postal_code: '',
    country: '',
    customer_group_id: '',
    notes: ''
})

const submit = () => {
    form.post(route('front-desk.customers.store'))
}
</script>
