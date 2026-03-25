<template>
    <DashboardLayout title="Product Return Reports" :user="user" :navigation="navigation">
        <div class="space-y-6">
            <div class="shadow rounded-lg p-6" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Product Return Reports</h1>
                <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                    {{ isApprover ? 'All return and exchange requests' : 'Your return and exchange requests' }}
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="rounded-lg shadow p-4" :style="{ backgroundColor: themeColors.card }">
                    <p class="text-xs" :style="{ color: themeColors.textSecondary }">Total</p>
                    <p class="text-xl font-bold" :style="{ color: themeColors.textPrimary }">{{ summary.total_requests }}</p>
                </div>
                <div class="rounded-lg shadow p-4" :style="{ backgroundColor: themeColors.card }">
                    <p class="text-xs" :style="{ color: themeColors.textSecondary }">Pending</p>
                    <p class="text-xl font-bold" :style="{ color: themeColors.warning }">{{ summary.pending_requests }}</p>
                </div>
                <div class="rounded-lg shadow p-4" :style="{ backgroundColor: themeColors.card }">
                    <p class="text-xs" :style="{ color: themeColors.textSecondary }">Approved</p>
                    <p class="text-xl font-bold" :style="{ color: themeColors.success }">{{ summary.approved_requests }}</p>
                </div>
                <div class="rounded-lg shadow p-4" :style="{ backgroundColor: themeColors.card }">
                    <p class="text-xs" :style="{ color: themeColors.textSecondary }">Rejected</p>
                    <p class="text-xl font-bold" :style="{ color: themeColors.danger }">{{ summary.rejected_requests }}</p>
                </div>
                <div class="rounded-lg shadow p-4" :style="{ backgroundColor: themeColors.card }">
                    <p class="text-xs" :style="{ color: themeColors.textSecondary }">Qty</p>
                    <p class="text-xl font-bold" :style="{ color: themeColors.textPrimary }">{{ summary.total_quantity }}</p>
                </div>
                <div class="rounded-lg shadow p-4" :style="{ backgroundColor: themeColors.card }">
                    <p class="text-xs" :style="{ color: themeColors.textSecondary }">Amount</p>
                    <p class="text-xl font-bold" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(summary.total_amount) }}</p>
                </div>
            </div>

            <div class="shadow rounded-lg p-4" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Status</label>
                    <select
                        :value="filters.status"
                        class="px-3 py-2 rounded border text-sm"
                        :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"
                        @change="applyStatusFilter"
                    >
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>

            <div class="shadow rounded-lg overflow-x-auto" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <table class="w-full text-sm">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-4 py-3 text-left" :style="{ color: themeColors.textSecondary }">Sale #</th>
                            <th class="px-4 py-3 text-left" :style="{ color: themeColors.textSecondary }">Type</th>
                            <th class="px-4 py-3 text-left" :style="{ color: themeColors.textSecondary }">Status</th>
                            <th class="px-4 py-3 text-right" :style="{ color: themeColors.textSecondary }">Qty</th>
                            <th class="px-4 py-3 text-right" :style="{ color: themeColors.textSecondary }">Amount</th>
                            <th class="px-4 py-3 text-left" :style="{ color: themeColors.textSecondary }">Requested By</th>
                            <th class="px-4 py-3 text-left" :style="{ color: themeColors.textSecondary }">Processed By</th>
                            <th class="px-4 py-3 text-left" :style="{ color: themeColors.textSecondary }">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="requests.length === 0">
                            <td colspan="8" class="px-4 py-6 text-center" :style="{ color: themeColors.textTertiary }">No return requests found.</td>
                        </tr>
                        <tr v-for="request in requests" :key="request.id" class="border-t" :style="{ borderColor: themeColors.border }">
                            <td class="px-4 py-3" :style="{ color: themeColors.textPrimary }">{{ request.sale_number || '—' }}</td>
                            <td class="px-4 py-3 capitalize" :style="{ color: themeColors.textPrimary }">{{ request.request_type }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium capitalize" :style="statusBadgeStyle(request.status)">
                                    {{ request.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right" :style="{ color: themeColors.textPrimary }">{{ request.quantity_total }}</td>
                            <td class="px-4 py-3 text-right" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(request.amount_total) }}</td>
                            <td class="px-4 py-3" :style="{ color: themeColors.textPrimary }">{{ request.requested_by_name || '—' }}</td>
                            <td class="px-4 py-3" :style="{ color: themeColors.textPrimary }">{{ request.approved_by_name || '—' }}</td>
                            <td class="px-4 py-3" :style="{ color: themeColors.textTertiary }">{{ formatDate(request.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'

const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
}))
loadTheme()

const props = defineProps({
    user: Object,
    navigation: Array,
    summary: Object,
    requests: Array,
    filters: Object,
    isApprover: Boolean,
})

const formatDate = (value) => {
    if (!value) return '—'
    return new Date(value).toLocaleString()
}

const statusBadgeStyle = (status) => {
    if (status === 'approved') {
        return { backgroundColor: 'rgba(16, 185, 129, 0.12)', color: 'rgb(16, 185, 129)' }
    }
    if (status === 'rejected') {
        return { backgroundColor: 'rgba(239, 68, 68, 0.12)', color: 'rgb(239, 68, 68)' }
    }

    return { backgroundColor: 'rgba(245, 158, 11, 0.12)', color: 'rgb(245, 158, 11)' }
}

const applyStatusFilter = (event) => {
    const status = event.target.value

    router.get('/pos/returns/report', { status }, { preserveScroll: true, preserveState: true })
}
</script>
