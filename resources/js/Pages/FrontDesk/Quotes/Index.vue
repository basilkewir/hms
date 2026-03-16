<template>
    <DashboardLayout title="Quote Management" :user="user" :navigation="navigation">
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Quote Management</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Create and manage customer quotes.</p>
                </div>
                <div class="flex gap-2">
                    <button @click="createQuote"
                            class="px-4 py-2 rounded-md font-medium text-white transition-colors flex items-center"
                            :style="{ backgroundColor: themeColors.primary }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Create Quote
                    </button>
                    <button @click="exportQuotes"
                            class="px-4 py-2 rounded-md font-medium text-white transition-colors"
                            :style="{ backgroundColor: themeColors.success }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.success + 'cc'"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                        📥 Export
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="stat in statData" :key="stat.label"
                     class="rounded-lg p-4 border shadow-sm flex items-center gap-3"
                     :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-xl flex-shrink-0"
                         :style="{ backgroundColor: stat.color + '20' }">{{ getStatIcon(stat.label) }}</div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium truncate" :style="{ color: themeColors.textSecondary }">{{ stat.label }}</p>
                        <p class="text-lg font-bold mt-0.5" :style="{ color: stat.color }">{{ stat.value }}</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-lg border p-4 shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Status</label>
                        <select v-model="filters.status"
                                class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none cursor-pointer"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="sent">Sent</option>
                            <option value="accepted">Accepted</option>
                            <option value="rejected">Rejected</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Date From</label>
                        <div class="relative">
                            <input
                                v-model="filters.start_date"
                                type="date"
                                :max="filters.end_date || today"
                                class="w-full px-3 py-2 pr-10 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                placeholder="Select start date" />
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-4 w-4" :style="{ color: themeColors.textSecondary }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Date To</label>
                        <div class="relative">
                            <input
                                v-model="filters.end_date"
                                type="date"
                                :min="filters.start_date"
                                :max="today"
                                class="w-full px-3 py-2 pr-10 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                placeholder="Select end date" />
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-4 w-4" :style="{ color: themeColors.textSecondary }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Search</label>
                        <input v-model="filters.search" type="text" placeholder="Quote number, customer name..."
                               class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none cursor-text"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }" />
                    </div>
                </div>
                <div class="flex gap-2 mt-4">
                    <button @click="applyFilters"
                            class="px-4 py-2 rounded-md font-medium text-white transition-colors cursor-pointer"
                            :style="{ backgroundColor: themeColors.primary }">
                        🔍 Apply Filters
                    </button>
                    <button @click="clearFilters"
                            class="px-4 py-2 rounded-md font-medium transition-colors cursor-pointer"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                        🔄 Clear
                    </button>
                </div>
            </div>

            <!-- Quotes Table -->
            <div class="rounded-lg border shadow-sm overflow-hidden"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr :style="{ backgroundColor: themeColors.background }">
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Quote Number</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Customer Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Total Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Valid Until</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Created Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" :style="{ borderColor: themeColors.border }">
                            <tr v-for="quote in quotes" :key="quote.id">
                                <td class="px-4 py-3 text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                    {{ quote.quote_number }}
                                </td>
                                <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ quote.customer_name || 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ quote.customer_email || 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                    {{ formatCurrency(quote.total_amount) }}
                                </td>
                                <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ formatDate(quote.valid_until) }}
                                </td>
                                <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ formatDate(quote.created_at) }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium"
                                          :style="getStatusStyle(quote.status)">
                                        {{ quote.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex gap-2">
                                        <button @click="viewQuote(quote)"
                                                class="text-blue-600 hover:text-blue-800 text-sm font-medium cursor-pointer">
                                            👁 View
                                        </button>
                                        <button @click="editQuote(quote)"
                                                class="text-green-600 hover:text-green-800 text-sm font-medium cursor-pointer">
                                            ✏️ Edit
                                        </button>
                                        <button v-if="quote.status === 'draft'" @click="sendQuote(quote)"
                                                class="text-purple-600 hover:text-purple-800 text-sm font-medium cursor-pointer">
                                            📧 Send
                                        </button>
                                        <button v-if="quote.status === 'sent'" @click="convertToInvoice(quote)"
                                                class="text-orange-600 hover:text-orange-800 text-sm font-medium cursor-pointer">
                                            📄 Convert to Invoice
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="quotes.length === 0" class="text-center py-12">
                <div class="text-4xl mb-4">📋</div>
                <h3 class="text-lg font-medium mb-2" :style="{ color: themeColors.textPrimary }">No quotes found</h3>
                <p :style="{ color: themeColors.textSecondary }">No quotes match your current filters.</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme.js'
import {
    PlusIcon,
    DocumentArrowDownIcon,
    DocumentTextIcon,
    CurrencyDollarIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    ChartBarIcon
} from '@heroicons/vue/24/outline'

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
    hover: 'rgba(255, 255, 255, 0.1)'
}))

// Load theme on mount
loadTheme()

// Get today's date for date picker validation
const today = computed(() => {
    return new Date().toISOString().split('T')[0]
})

const props = defineProps({
    user: Object,
    navigation: Array,
    quoteStats: Object,
    quotes: Array,
    filters: Object
})

const filters = ref({
    status: props.filters?.status || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    search: props.filters?.search || ''
})

// Quote stats with computed values
const statData = computed(() => [
    {
        label: 'Total Quotes',
        value: props.quoteStats?.total || 0,
        icon: DocumentTextIcon,
        color: themeColors.value.primary
    },
    {
        label: 'Total Value',
        value: formatCurrency(props.quoteStats?.totalAmount || 0),
        icon: CurrencyDollarIcon,
        color: '#10b981'
    },
    {
        label: 'Pending',
        value: props.quoteStats?.pending || 0,
        icon: ClockIcon,
        color: '#f59e0b'
    },
    {
        label: 'Accepted',
        value: props.quoteStats?.accepted || 0,
        icon: CheckCircleIcon,
        color: '#10b981'
    },
    {
        label: 'This Month',
        value: props.quoteStats?.thisMonth || 0,
        icon: ChartBarIcon,
        color: themeColors.value.primary
    }
])

// Helper function to get icon emoji based on label
const getStatIcon = (label) => {
    const iconMap = {
        'Total Quotes': '📄',
        'Total Value': '💰',
        'Pending': '⏳',
        'Accepted': '✅',
        'This Month': '📊'
    }
    return iconMap[label] || '📌'
}

const getStatusStyle = (status) => {
    const styles = {
        pending: { backgroundColor: '#f59e0b', color: 'white' },
        accepted: { backgroundColor: '#10b981', color: 'white' },
        rejected: { backgroundColor: '#ef4444', color: 'white' }
    }
    return styles[status] || { backgroundColor: '#6b7280', color: 'white' }
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString()
}

const applyFilters = () => {
    router.get(route('front-desk.quotes.index'), filters.value, {
        preserveState: true,
        preserveScroll: true
    })
}

const clearFilters = () => {
    filters.value = {
        status: '',
        start_date: '',
        end_date: '',
        search: ''
    }
    applyFilters()
}

const viewQuote = (quote) => {
    router.get(route('front-desk.quotes.show', quote.id))
}

const editQuote = (quote) => {
    router.get(route('front-desk.quotes.edit', quote.id))
}

const createQuote = () => {
    router.get(route('front-desk.quotes.create'))
}

const exportQuotes = () => {
    window.location.href = route('front-desk.quotes.export', {
        ...filters.value,
        format: 'csv'
    })
}

const sendQuote = (quote) => {
    // Mark quote as sent
    if (confirm(`Send quote #${quote.quote_number} to ${quote.customer_email}?`)) {
        router.patch(route('front-desk.quotes.update', quote.id), { status: 'sent' })
    }
}

const convertToInvoice = (quote) => {
    if (confirm(`Convert quote #${quote.quote_number} to invoice? You will be redirected to the invoices page.`)) {
        router.post(route('front-desk.quotes.convert', quote.id))
    }
}
</script>

<style scoped>
/* Enhanced date input styling */
input[type="date"] {
    position: relative;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

input[type="date"]::-webkit-calendar-picker-indicator {
    background: transparent;
    bottom: 0;
    color: transparent;
    cursor: pointer;
    height: auto;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
    width: auto;
}

input[type="date"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
}

input[type="date"]::-webkit-clear-button {
    -webkit-appearance: none;
}

input[type="date"]::-ms-clear {
    display: none;
}

input[type="date"]::-ms-reveal {
    display: none;
}

/* Focus states */
input[type="date"]:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Hover state */
input[type="date"]:hover {
    border-color: #6b7280;
}

/* Firefox specific styling */
@-moz-document url-prefix() {
    input[type="date"] {
        padding-right: 2.5rem;
    }
}

/* Dark theme adjustments */
@media (prefers-color-scheme: dark) {
    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
    }
}

/* Mobile responsive */
@media (max-width: 768px) {
    input[type="date"] {
        font-size: 16px; /* Prevent zoom on iOS */
    }
}
</style>
