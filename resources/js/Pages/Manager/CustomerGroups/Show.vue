<template>
    <DashboardLayout title="Customer Group Details" :user="user" :navigation="navigation">
        <!-- Header -->
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
             class="shadow rounded-lg p-6 mb-8 border">
            <div class="flex items-center justify-between">
                <div>
                    <h1 :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">Customer Group Details</h1>
                    <p :style="{ color: themeColors.textSecondary }" class="mt-2">{{ group.name }}</p>
                </div>
                <div class="flex space-x-3">
                    <Link v-if="editHref" :href="editHref" 
                          :style="{ backgroundColor: themeColors.primary, color: '#000' }"
                          class="px-4 py-2 rounded-md hover:opacity-90">
                        Edit Group
                    </Link>
                    <Link :href="route('admin.customer-groups.index')" 
                          :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }"
                          class="px-4 py-2 rounded-md border hover:opacity-80">
                        Back to Groups
                    </Link>
                </div>
            </div>
        </div>

        <!-- Group Information -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="lg:col-span-2 shadow rounded-lg p-6 border">
                <h2 :style="{ color: themeColors.textPrimary }" class="text-lg font-semibold mb-4">Group Information</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label :style="{ color: themeColors.textSecondary }" class="block text-sm font-medium">Group Name</label>
                        <p :style="{ color: themeColors.textPrimary }" class="mt-1 text-sm">{{ group.name }}</p>
                    </div>
                    <div>
                        <label :style="{ color: themeColors.textSecondary }" class="block text-sm font-medium">Status</label>
                        <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :class="group.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                            {{ group.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div>
                        <label :style="{ color: themeColors.textSecondary }" class="block text-sm font-medium">Discount Percentage</label>
                        <p :style="{ color: themeColors.success }" class="mt-1 text-sm font-semibold">{{ group.discount_percentage }}%</p>
                    </div>
                    <div>
                        <label :style="{ color: themeColors.textSecondary }" class="block text-sm font-medium">Total Customers</label>
                        <p :style="{ color: themeColors.textPrimary }" class="mt-1 text-sm">{{ (group.customers && group.customers.length) || 0 }} customers</p>
                    </div>
                    <div class="col-span-2" v-if="group.description">
                        <label :style="{ color: themeColors.textSecondary }" class="block text-sm font-medium">Description</label>
                        <p :style="{ color: themeColors.textPrimary }" class="mt-1 text-sm">{{ group.description }}</p>
                    </div>
                </div>
            </div>

            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="shadow rounded-lg p-6 border">
                <h2 :style="{ color: themeColors.textPrimary }" class="text-lg font-semibold mb-4">Customers in Group</h2>
                <div v-if="group.customers && group.customers.length > 0" class="space-y-2">
                    <div v-for="customer in group.customers.slice(0, 10)" :key="customer.id" 
                         :style="{ borderColor: themeColors.border }"
                         class="border-b pb-2">
                        <p :style="{ color: themeColors.textPrimary }" class="text-sm font-medium">{{ customer.first_name }} {{ customer.last_name }}</p>
                        <p :style="{ color: themeColors.textTertiary }" class="text-xs">{{ customer.customer_code }}</p>
                    </div>
                    <p v-if="group.customers.length > 10" :style="{ color: themeColors.textTertiary }" class="text-xs mt-2">
                        + {{ group.customers.length - 10 }} more customers
                    </p>
                </div>
                <p v-else :style="{ color: themeColors.textSecondary }" class="text-sm">No customers in this group</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'

const props = defineProps({
    user: Object,
    customerGroup: Object,
})

const { currentTheme } = useTheme()
const navigation = computed(() => {
    const userRole = props.user?.roles?.[0]?.name || 'admin'
    return getNavigationForRole(userRole)
})

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
}))

// Safe group object
const group = computed(() => props.customerGroup || { id: null, name: '', description: '', discount_percentage: 0, is_active: true, customers: [] })

// Compute edit href lazily to avoid calling route() without id
const editHref = computed(() => {
    const id = group.value?.id
    if (!id) return null
    // Provide both keys to satisfy route patterns like {id} or {customer_group}
    return route('admin.customer-groups.edit', { id, customer_group: id })
})
</script>
