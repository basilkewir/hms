<template>
    <DashboardLayout title="Purchase Details" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Purchase #{{ purchase.purchase_number || purchase.id }}</h1>
                    <p class="mt-2 flex items-center" :style="{ color: themeColors.textSecondary }">
                        Status:
                        <span class="ml-2 px-3 py-1 text-xs rounded-full font-medium" :style="getStatusStyle(purchase.status)">
                            {{ formatStatus(purchase.status) }}
                        </span>
                    </p>
                </div>
                <div class="flex gap-3">
                    <Link :href="route('manager.purchases.edit', purchase.id)"
                          class="px-4 py-2 rounded-md font-medium hover:opacity-90 transition-all text-white"
                          :style="{ backgroundColor: themeColors.warning }">
                        Edit
                    </Link>
                    <Link :href="route('manager.purchases.index')"
                          class="px-4 py-2 rounded-md font-medium transition-all"
                          :style="{ backgroundColor: themeColors.secondary, color: '#fff' }">
                        <ArrowLeftIcon class="h-4 w-4 mr-2 inline" />
                        Back
                    </Link>
                </div>
            </div>
        </div>

        <!-- Purchase Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Supplier & Date</h3>
                <div class="space-y-3 text-sm">
                    <div :style="{ color: themeColors.textPrimary }">
                        <span class="font-medium" :style="{ color: themeColors.textSecondary }">Supplier:</span>
                        {{ purchase.supplier_name || purchase.supplier?.name || '—' }}
                    </div>
                    <div :style="{ color: themeColors.textPrimary }">
                        <span class="font-medium" :style="{ color: themeColors.textSecondary }">Purchase Date:</span>
                        {{ formatDate(purchase.purchase_date || purchase.created_at) }}
                    </div>
                    <div :style="{ color: themeColors.textPrimary }">
                        <span class="font-medium" :style="{ color: themeColors.textSecondary }">Created:</span>
                        {{ formatDateTime(purchase.created_at) }}
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Amount & Status</h3>
                <div class="space-y-3 text-sm">
                    <div :style="{ color: themeColors.textPrimary }">
                        <span class="font-medium" :style="{ color: themeColors.textSecondary }">Total Amount:</span>
                        <span class="ml-2 font-bold" :style="{ color: themeColors.primary }">{{ formatCurrency(purchase.total_amount || 0) }}</span>
                    </div>
                    <div :style="{ color: themeColors.textPrimary }">
                        <span class="font-medium" :style="{ color: themeColors.textSecondary }">Status:</span>
                        <span class="ml-2 px-3 py-1 rounded text-xs font-medium" :style="getStatusStyle(purchase.status)">
                            {{ formatStatus(purchase.status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div v-if="purchase.notes" class="mb-8">
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Notes</h3>
                <p :style="{ color: themeColors.textSecondary }}" class="whitespace-pre-wrap">{{ purchase.notes }}</p>
            </div>
        </div>

        <!-- Items (if present) -->
        <div v-if="purchase.items && purchase.items.length" class="rounded-lg overflow-hidden shadow"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Items</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Unit Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" :style="{ borderColor: themeColors.border }">
                        <tr v-for="(item, idx) in purchase.items" :key="idx"
                            :style="{ backgroundColor: themeColors.card }">
                            <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textPrimary }">{{ item.name || item.description || '—' }}</td>
                            <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textPrimary }">{{ item.quantity ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(item.unit_price || 0) }}</td>
                            <td class="px-6 py-4 text-sm font-medium" :style="{ color: themeColors.primary }">{{ formatCurrency((item.quantity || 0) * (item.unit_price || 0)) }}</td>
                        </tr>
                    </tbody>
                </table>
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
import { formatCurrency as formatCurrencyUtil } from '@/Utils/currency.js'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    purchase: Object
})

const navigation = computed(() => getNavigationForRole('manager'))

const { currentTheme, loadTheme } = useTheme()
loadTheme()

const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    primary: `var(--kotel-primary)`,
    secondary: `var(--kotel-secondary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)'
}))

const formatCurrency = (amount) => formatCurrencyUtil(amount)

const formatDate = (dateString) => {
    if (!dateString) return '—'
    return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const formatDateTime = (dateString) => {
    if (!dateString) return '—'
    return new Date(dateString).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const formatStatus = (status) => {
    return (status || 'pending').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getStatusStyle = (status) => {
    const s = status || 'pending'
    const colors = {
        pending: { backgroundColor: 'var(--kotel-warning)', color: '#000' },
        confirmed: { backgroundColor: 'var(--kotel-primary)', color: '#000' },
        partially_received: { backgroundColor: 'var(--kotel-secondary)', color: '#fff' },
        received: { backgroundColor: 'var(--kotel-success)', color: '#000' },
        cancelled: { backgroundColor: 'var(--kotel-danger)', color: '#fff' }
    }
    return colors[s] || { backgroundColor: 'var(--kotel-border)', color: 'var(--kotel-text-primary)' }
}
</script>
