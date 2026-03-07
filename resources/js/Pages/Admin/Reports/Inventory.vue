<template>
    <DashboardLayout title="Inventory Report" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Inventory Report</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Stock overview, low stock items, and recent movements.</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-2">
                        <label class="text-sm" :style="{ color: themeColors.textSecondary }">Low stock ≤</label>
                        <input type="number" min="0" step="1" v-model.number="threshold"
                               @change="applyFilters"
                               class="w-20 rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }" />
                    </div>
                    <a :href="route('admin.reports.inventory.export', { format: 'csv', threshold })"
                       class="px-4 py-2 rounded-md border hover:opacity-80"
                       :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        Export CSV
                    </a>
                    <a :href="route('admin.reports.inventory.export', { format: 'json', threshold })"
                       class="px-4 py-2 rounded-md hover:opacity-90"
                       :style="{ backgroundColor: themeColors.primary, color: '#000' }">
                        Export JSON
                    </a>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Total Items</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ stats.total_items }}</p>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Low Stock</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.warning }">{{ stats.low_stock }}</p>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Out of Stock</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.danger }">{{ stats.out_of_stock }}</p>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Stock Value</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.success }">{{ formatCurrency(stats.stock_value) }}</p>
            </div>
        </div>

        <!-- Body -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Low Stock Items -->
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Low Stock Items</h2>
                <div v-if="lowStockItems && lowStockItems.length" class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Item</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">SKU</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Qty</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in lowStockItems" :key="p.id" class="border-t" :style="{ borderColor: themeColors.border }">
                                <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ p.name }}</td>
                                <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ p.sku || '-' }}</td>
                                <td class="px-4 py-2 text-sm text-right" :style="{ color: themeColors.textPrimary }">{{ p.quantity }}</td>
                                <td class="px-4 py-2 text-sm text-right" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(p.price) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No low stock items</p>
            </div>

            <!-- Recent Movements -->
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Recent Movements</h2>
                <div v-if="recentMovements && recentMovements.length" class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Type</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Product ID</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Qty</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="m in recentMovements" :key="m.id" class="border-t" :style="{ borderColor: themeColors.border }">
                                <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ m.type }}</td>
                                <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">
                                    <template v-if="productHref(m.product_id)">
                                        <a :href="productHref(m.product_id)" class="underline hover:opacity-80">#{{ m.product_id }}</a>
                                    </template>
                                    <template v-else>
                                        #{{ m.product_id }}
                                    </template>
                                </td>
                                <td class="px-4 py-2 text-sm text-right" :style="{ color: themeColors.textPrimary }">{{ m.quantity }}</td>
                                <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ formatDate(m.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No recent movements</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'

const props = defineProps({
    user: Object,
    stats: { type: Object, default: () => ({ total_items: 0, low_stock: 0, out_of_stock: 0, stock_value: 0 }) },
    lowStockItems: { type: Array, default: () => [] },
    recentMovements: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ threshold: 5 }) },
})

const navigation = computed(() => {
    const role = props.user?.roles?.[0]?.name || 'admin'
    return getNavigationForRole(role)
})

const { currentTheme } = useTheme()
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
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.02)'
}))

// Filters
const threshold = ref(Number(props.filters?.threshold ?? 5))
const applyFilters = () => {
    router.get(route('admin.reports.inventory'), { threshold: threshold.value }, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    })
}

// Build product details link safely (avoid Ziggy errors if route is missing)
const productHref = (id) => {
    try {
        if (!id) return null
        return route('admin.products.show', { id })
    } catch (e) {
        return null
    }
}

const formatCurrency = (v) => {
    const num = Number(v || 0)
    return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 }).format(num)
}
const formatDate = (d) => {
    if (!d) return ''
    const dt = new Date(d)
    return isNaN(dt.getTime()) ? '' : dt.toLocaleDateString()
}
</script>
