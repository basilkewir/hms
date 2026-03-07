<template>
    <DashboardLayout title="Invoice Management" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Invoice Management</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Create and manage customer invoices with full CRUD functionality.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('front-desk.invoices.create')" 
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        New Invoice
                    </Link>
                    <button @click="exportInvoices" 
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{ 
                                backgroundColor: '#8b5cf6',
                            }"
                            @mouseenter="$event.target.style.backgroundColor = '#7c3aed'"
                            @mouseleave="$event.target.style.backgroundColor = '#8b5cf6'">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                        Export
                    </button>
                </div>
            </div>
        </div>

            <!-- Invoice Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
            <div v-for="stat in statData" :key="stat.label"
                 class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: stat.color + '20' }">
                        <component :is="stat.icon" class="h-6 w-6" :style="{ color: stat.color }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">{{ stat.label }}</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stat.value }}</p>
                    </div>
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
                                class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                            <option value="">All Status</option>
                            <option value="open">Open</option>
                            <option value="paid">Paid</option>
                            <option value="overdue">Overdue</option>
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
                        <input v-model="filters.search" type="text" placeholder="Invoice number, customer name..."
                               class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }" />
                    </div>
                </div>
                <div class="flex gap-2 mt-4">
                    <button @click="applyFilters"
                            class="px-4 py-2 rounded-md font-medium text-white transition-colors"
                            :style="{ backgroundColor: themeColors.primary }">
                        🔍 Apply Filters
                    </button>
                    <button @click="clearFilters"
                            class="px-4 py-2 rounded-md font-medium transition-colors"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                        🔄 Clear
                    </button>
                </div>
            </div>

            <!-- Invoices Table -->
            <div class="rounded-lg border shadow-sm overflow-hidden"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr :style="{ backgroundColor: themeColors.background }">
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Invoice Number</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Customer Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Total Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Balance</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Issue Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Due Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" :style="{ borderColor: themeColors.border }">
                            <tr v-for="invoice in invoices" :key="invoice.id">
                                <td class="px-4 py-3 text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                    {{ invoice.invoice_number }}
                                </td>
                                <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ invoice.customer_name }}
                                </td>
                                <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ invoice.customer_email || 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                    {{ formatCurrency(invoice.total_amount) }}
                                </td>
                                <td class="px-4 py-3 text-sm font-medium" :style="{ color: getBalanceColor(invoice.balance_amount) }">
                                    {{ formatCurrency(invoice.balance_amount) }}
                                </td>
                                <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ invoice.issue_date }}
                                </td>
                                <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ invoice.due_date }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium"
                                          :style="getStatusStyle(invoice.status)">
                                        {{ invoice.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex gap-2">
                                        <button @click="viewInvoice(invoice)"
                                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            👁 View
                                        </button>
                                        <button v-if="invoice.balance_amount > 0" @click="markAsPaid(invoice)"
                                                class="text-green-600 hover:text-green-800 text-sm font-medium">
                                            ✅ Mark Paid
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="invoices.length === 0" class="text-center py-12">
                <div class="text-4xl mb-4">📄</div>
                <h3 class="text-lg font-medium mb-2" :style="{ color: themeColors.textPrimary }">No invoices found</h3>
                <p :style="{ color: themeColors.textSecondary }">No invoices match your current filters.</p>
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
    invoiceStats: Object,
    invoices: Array,
    filters: Object
})

const filters = ref({
    status: props.filters?.status || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    search: props.filters?.search || ''
})

// Invoice stats with computed values
const statData = computed(() => [
    {
        label: 'Total Invoices',
        value: props.invoiceStats?.total || 0,
        icon: DocumentTextIcon,
        color: themeColors.value.primary
    },
    {
        label: 'Total Revenue',
        value: formatCurrency(props.invoiceStats?.totalAmount || 0),
        icon: CurrencyDollarIcon,
        color: '#10b981'
    },
    {
        label: 'Pending',
        value: props.invoiceStats?.pending || 0,
        icon: ClockIcon,
        color: '#f59e0b'
    },
    {
        label: 'Overdue',
        value: props.invoiceStats?.overdue || 0,
        icon: ExclamationTriangleIcon,
        color: '#ef4444'
    },
    {
        label: 'Paid',
        value: props.invoiceStats?.paid || 0,
        icon: CheckCircleIcon,
        color: '#10b981'
    },
    {
        label: 'This Month',
        value: props.invoiceStats?.thisMonth || 0,
        icon: ChartBarIcon,
        color: themeColors.value.primary
    }
])

const getStatusStyle = (status) => {
    const styles = {
        open: { backgroundColor: '#3b82f6', color: 'white' },
        paid: { backgroundColor: '#10b981', color: 'white' },
        overdue: { backgroundColor: '#ef4444', color: 'white' }
    }
    return styles[status] || { backgroundColor: '#6b7280', color: 'white' }
}

const getBalanceColor = (balance) => {
    return balance > 0 ? '#ef4444' : '#10b981'
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString()
}

const applyFilters = () => {
    router.get(route('front-desk.invoices.index'), filters.value, {
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

const viewInvoice = (invoice) => {
    router.get(route('front-desk.invoices.show', invoice.id))
}

const markAsPaid = (invoice) => {
    if (confirm(`Mark invoice ${invoice.folio_number} as paid?`)) {
        router.post(route('front-desk.invoices.markPaid', invoice.id))
    }
}

const exportInvoices = () => {
    window.location.href = route('front-desk.invoices.export', {
        ...filters.value,
        format: 'csv'
    })
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
