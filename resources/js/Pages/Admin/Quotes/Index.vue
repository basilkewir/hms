<template>
    <DashboardLayout title="Quotes" :user="user" :navigation="navigation">

        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Quote Management</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Create and manage customer quotes.</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="exportQuotes"
                            class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity flex items-center border"
                            :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        <ArrowDownTrayIcon class="h-4 w-4 mr-2" />
                        Export CSV
                    </button>
                    <Link :href="route('admin.quotes.create')"
                          class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity flex items-center"
                          :style="{ backgroundColor: themeColors.primary, color: '#000' }">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        New Quote
                    </Link>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <DocumentTextIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.primary }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Total Quotes</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ props.quoteStats?.total || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <CurrencyDollarIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.success }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Total Amount</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(props.quoteStats?.totalAmount || 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <ClockIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.warning }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Pending</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ props.quoteStats?.pending || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <CheckCircleIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.success }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Accepted</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ props.quoteStats?.accepted || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quotes Table -->
        <div class="shadow rounded-lg overflow-hidden border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">All Quotes</h3>
                    <div class="flex flex-wrap gap-2 items-center">
                        <input v-model="filters.search" type="text" placeholder="Quote no., customer..."
                               class="border rounded-md px-3 py-1 text-sm focus:outline-none"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                        <select v-model="filters.status"
                                class="border rounded-md px-3 py-1 text-sm focus:outline-none"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="sent">Sent</option>
                            <option value="accepted">Accepted</option>
                            <option value="rejected">Rejected</option>
                            <option value="expired">Expired</option>
                        </select>
                        <input v-model="filters.start_date" type="date"
                               class="border rounded-md px-3 py-1 text-sm focus:outline-none"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                        <input v-model="filters.end_date" type="date"
                               class="border rounded-md px-3 py-1 text-sm focus:outline-none"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                        <button @click="applyFilters"
                                class="px-3 py-1 rounded-md text-sm hover:opacity-90 transition-opacity"
                                :style="{ backgroundColor: themeColors.primary, color: '#000' }">Apply</button>
                        <button @click="clearFilters"
                                class="px-3 py-1 rounded-md text-sm border hover:opacity-80 transition-opacity"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">Clear</button>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Quote Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Valid Until</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="quote in quotes" :key="quote.id"
                            class="transition-colors"
                            :style="hoveredRow === quote.id ? { backgroundColor: themeColors.hover } : {}"
                            @mouseenter="hoveredRow = quote.id"
                            @mouseleave="hoveredRow = null">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                {{ quote.quote_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ quote.customer_name }}</div>
                                <div class="text-sm" :style="{ color: themeColors.textSecondary }">{{ quote.customer_email || '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(quote.total_amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ quote.valid_until ? formatDate(quote.valid_until) : '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ quote.created_at ? formatDate(quote.created_at) : '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="getStatusStyle(quote.status)">
                                    {{ formatStatusLabel(quote.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button @click="viewQuote(quote)" class="hover:opacity-80 transition-opacity" :style="{ color: themeColors.primary }">View</button>
                                    <button @click="editQuote(quote)" class="hover:opacity-80 transition-opacity" :style="{ color: themeColors.success }">Edit</button>
                                    <button v-if="quote.status === 'draft'" @click="sendQuote(quote)" class="hover:opacity-80 transition-opacity" :style="{ color: themeColors.warning }">Send</button>
                                    <button v-if="quote.status === 'sent'" @click="convertToInvoice(quote)" class="hover:opacity-80 transition-opacity" :style="{ color: themeColors.danger }">Convert</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!quotes || quotes.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-sm" :style="{ color: themeColors.textSecondary }">
                                No quotes found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'
import {
    PlusIcon,
    DocumentTextIcon,
    CurrencyDollarIcon,
    ClockIcon,
    CheckCircleIcon,
    ArrowDownTrayIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    navigation: Array,
    quoteStats: Object,
    quotes: Array,
    filters: Object,
})

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
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)',
}))

const hoveredRow = ref(null)

const filters = ref({
    status: props.filters?.status || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    search: props.filters?.search || '',
})

const getStatusStyle = (status) => {
    const key = (status || '').toLowerCase()
    if (key === 'draft')    return { backgroundColor: themeColors.value.border, color: themeColors.value.textPrimary }
    if (key === 'sent')     return { backgroundColor: themeColors.value.primary, color: '#000' }
    if (key === 'accepted') return { backgroundColor: themeColors.value.success, color: '#000' }
    if (key === 'rejected') return { backgroundColor: themeColors.value.danger, color: '#fff' }
    if (key === 'expired')  return { backgroundColor: themeColors.value.warning, color: '#000' }
    return { backgroundColor: themeColors.value.border, color: themeColors.value.textPrimary }
}

const formatStatusLabel = (status) => (status || '').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
const formatDate = (d) => d ? new Date(d).toLocaleDateString() : '—'

const applyFilters = () => {
    router.get(route('admin.quotes.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    })
}

const clearFilters = () => {
    filters.value = { status: '', start_date: '', end_date: '', search: '' }
    applyFilters()
}

const viewQuote = (quote) => router.get(route('admin.quotes.show', quote.id))
const editQuote = (quote) => router.get(route('admin.quotes.edit', quote.id))

const sendQuote = (quote) => {
    if (confirm(`Send quote ${quote.quote_number} to ${quote.customer_email}?`)) {
        router.post(route('admin.quotes.send', quote.id))
    }
}

const convertToInvoice = (quote) => {
    if (confirm(`Convert quote ${quote.quote_number} to invoice?`)) {
        router.post(route('admin.quotes.convert', quote.id))
    }
}

const exportQuotes = () => {
    window.location.href = route('admin.quotes.export', { ...filters.value, format: 'csv' })
}
</script>
