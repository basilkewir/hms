<template>
    <DashboardLayout title="Customer Details" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Customer Details</h1>
                    <p :style="{ color: themeColors.textSecondary }" class="mt-2">{{ customer.first_name }} {{ customer.last_name }}</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('admin.customers.edit', customer.id)"
                          class="px-4 py-2 rounded-md transition-colors"
                          :style="{ backgroundColor: themeColors.primary, color: '#fff' }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        Edit Customer
                    </Link>
                    <Link :href="route('admin.customers.index')"
                          class="px-4 py-2 rounded-md transition-colors"
                          :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        Back to Customers
                    </Link>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2 shadow rounded-lg p-6"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Customer Information</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium" :style="{ color: themeColors.textSecondary }">Customer Code</label>
                        <p class="mt-1 text-sm" :style="{ color: themeColors.textPrimary }">{{ customer.customer_code }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium" :style="{ color: themeColors.textSecondary }">Status</label>
                        <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :class="customer.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                            {{ customer.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium" :style="{ color: themeColors.textSecondary }">First Name</label>
                        <p class="mt-1 text-sm" :style="{ color: themeColors.textPrimary }">{{ customer.first_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium" :style="{ color: themeColors.textSecondary }">Last Name</label>
                        <p class="mt-1 text-sm" :style="{ color: themeColors.textPrimary }">{{ customer.last_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium" :style="{ color: themeColors.textSecondary }">Email</label>
                        <p class="mt-1 text-sm" :style="{ color: themeColors.textPrimary }">{{ customer.email || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium" :style="{ color: themeColors.textSecondary }">Phone</label>
                        <p class="mt-1 text-sm" :style="{ color: themeColors.textPrimary }">{{ customer.phone || 'N/A' }}</p>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium" :style="{ color: themeColors.textSecondary }">Address</label>
                        <p class="mt-1 text-sm" :style="{ color: themeColors.textPrimary }">{{ customer.address || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium" :style="{ color: themeColors.textSecondary }">City</label>
                        <p class="mt-1 text-sm" :style="{ color: themeColors.textPrimary }">{{ customer.city || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium" :style="{ color: themeColors.textSecondary }">State</label>
                        <p class="mt-1 text-sm" :style="{ color: themeColors.textPrimary }">{{ customer.state || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium" :style="{ color: themeColors.textSecondary }">Country</label>
                        <p class="mt-1 text-sm" :style="{ color: themeColors.textPrimary }">{{ customer.country || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium" :style="{ color: themeColors.textSecondary }">Postal Code</label>
                        <p class="mt-1 text-sm" :style="{ color: themeColors.textPrimary }">{{ customer.postal_code || 'N/A' }}</p>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium" :style="{ color: themeColors.textSecondary }">Customer Group</label>
                        <p class="mt-1 text-sm" :style="{ color: themeColors.textPrimary }">
                            <span v-if="customer.customer_group">
                                {{ customer.customer_group.name }} ({{ customer.customer_group.discount_percentage }}% discount)
                            </span>
                            <span v-else>No Group</span>
                        </p>
                    </div>
                    <div class="col-span-2" v-if="customer.notes">
                        <label class="block text-sm font-medium" :style="{ color: themeColors.textSecondary }">Notes</label>
                        <p class="mt-1 text-sm" :style="{ color: themeColors.textPrimary }">{{ customer.notes }}</p>
                    </div>
                </div>
            </div>

            <div class="shadow rounded-lg p-6"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Sales History</h2>
                <div v-if="customer.sales && customer.sales.length > 0" class="space-y-3">
                    <div v-for="sale in customer.sales.slice(0, 5)" :key="sale.id" class="border-b pb-3"
                         :style="{ borderColor: themeColors.border }">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ sale.sale_number }}</p>
                                <p class="text-xs" :style="{ color: themeColors.textSecondary }">{{ new Date(sale.sale_date).toLocaleDateString() }}</p>
                            </div>
                            <p class="text-sm font-semibold" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(sale.total_amount) }}</p>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No sales history</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { formatCurrency, initializeCurrencySettings } from '@/Utils/currency.js'
import { onMounted } from 'vue'
import { useTheme } from '@/Composables/useTheme.js'

const props = defineProps({
    user: Object,
    customer: Object,
})

const navigation = computed(() => {
    const userRole = props.user?.roles?.[0]?.name || 'admin'
    return getNavigationForRole(userRole)
})

// Initialize currency settings on mount
onMounted(() => {
    initializeCurrencySettings()
})

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
