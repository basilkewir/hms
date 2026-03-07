<template>
    <DashboardLayout title="Edit Customer" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Edit Customer</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Update customer information.</p>
                </div>
                <Link :href="route('admin.customers.index')"
                      class="px-4 py-2 rounded-md transition-colors"
                      :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                    Back to Customers
                </Link>
            </div>
        </div>

        <!-- Form -->
        <div class="shadow rounded-lg p-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Customer Code</label>
                        <input type="text" :value="customer.customer_code" disabled
                               class="w-full rounded-md px-3 py-2"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textTertiary, borderWidth: '1px', borderStyle: 'solid' }">
                    </div>
                    <div></div>
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">First Name *</label>
                        <input type="text" v-model="form.first_name" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                        <div v-if="form.errors.first_name" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.first_name }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Last Name *</label>
                        <input type="text" v-model="form.last_name" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                        <div v-if="form.errors.last_name" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.last_name }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Email</label>
                        <input type="email" v-model="form.email"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                        <div v-if="form.errors.email" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.email }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Phone</label>
                        <input type="text" v-model="form.phone"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                        <div v-if="form.errors.phone" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.phone }}</div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Address</label>
                        <input type="text" v-model="form.address"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">City</label>
                        <input type="text" v-model="form.city"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">State</label>
                        <input type="text" v-model="form.state"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Country</label>
                        <input type="text" v-model="form.country"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Postal Code</label>
                        <input type="text" v-model="form.postal_code"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Customer Group</label>
                        <select v-model="form.customer_group_id"
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                            <option :value="null">No Group</option>
                            <option v-for="group in customerGroups" :key="group.id" :value="group.id">
                                {{ group.name }} ({{ group.discount_percentage }}% discount)
                            </option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Notes</label>
                        <textarea v-model="form.notes" rows="3"
                                  class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                  :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }"></textarea>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" v-model="form.is_active" class="mr-2">
                            <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">Active</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <Link :href="route('admin.customers.index')"
                          class="px-6 py-2 rounded-md transition-colors"
                          :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                            class="px-6 py-2 rounded-md transition-colors disabled:opacity-50"
                            :style="{ backgroundColor: themeColors.primary, color: '#fff' }"
                            @mouseenter="!form.processing && ($event.target.style.backgroundColor = themeColors.hover)"
                            @mouseleave="!form.processing && ($event.target.style.backgroundColor = themeColors.primary)">
                        {{ form.processing ? 'Updating...' : 'Update Customer' }}
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme.js'

const props = defineProps({
    user: Object,
    customer: Object,
    customerGroups: Array,
})

const navigation = computed(() => {
    const userRole = props.user?.roles?.[0]?.name || 'admin'
    return getNavigationForRole(userRole)
})

const form = useForm({
    first_name: props.customer.first_name,
    last_name: props.customer.last_name,
    email: props.customer.email || '',
    phone: props.customer.phone || '',
    address: props.customer.address || '',
    city: props.customer.city || '',
    state: props.customer.state || '',
    country: props.customer.country || '',
    postal_code: props.customer.postal_code || '',
    customer_group_id: props.customer.customer_group_id,
    notes: props.customer.notes || '',
    is_active: props.customer.is_active,
})

const submit = () => {
    form.put(route('admin.customers.update', props.customer.id))
}

// Theme setup
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
    hover: `rgba(255, 255, 255, 0.1)`,
}))
loadTheme()
</script>
