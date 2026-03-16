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
                <div v-for="stat in quoteStats" :key="stat.label"
                     class="rounded-lg p-4 border shadow-sm flex items-center gap-3"
                     :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-xl flex-shrink-0"
                         :style="{ backgroundColor: stat.color + '20' }">{{ stat.icon }}</div>
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
                                class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
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
                        <input v-model="filters.start_date" type="date"
                               class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Date To</label>
                        <input v-model="filters.end_date" type="date"
                               class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Search</label>
                        <input v-model="filters.search" type="text" placeholder="Quote number, customer name..."
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
                                    {{ quote.customer_name }}
                                </td>
                                <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ quote.customer_email || 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                    {{ formatCurrency(quote.total_amount) }}
                                </td>
                                <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ quote.valid_until }}
                                </td>
                                <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ quote.created_at }}
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
                                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            👁 View
                                        </button>
                                        <button @click="editQuote(quote)"
                                                class="text-green-600 hover:text-green-800 text-sm font-medium">
                                            ✏️ Edit
                                        </button>
                                        <button v-if="quote.status === 'draft'" @click="sendQuote(quote)"
                                                class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                                            📧 Send
                                        </button>
                                        <button v-if="quote.status === 'sent'" @click="convertToInvoice(quote)"
                                                class="text-orange-600 hover:text-orange-800 text-sm font-medium">
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
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { formatCurrency } from '@/Utils/currency.js';
import { useTheme } from '@/Composables/useTheme.js';

const { loadTheme } = useTheme();
loadTheme();

const themeColors = computed(() => ({
    primary: 'var(--kotel-primary)',
    success: 'var(--kotel-success)',
    danger: 'var(--kotel-danger)',
    warning: 'var(--kotel-warning)',
    background: 'var(--kotel-background)',
    card: 'var(--kotel-card)',
    border: 'var(--kotel-border)',
    textPrimary: 'var(--kotel-text-primary)',
    textSecondary: 'var(--kotel-text-secondary)',
    hover: 'rgba(255, 255, 255, 0.1)'
}));

const props = defineProps({
    user: Object,
    navigation: Array,
    quoteStats: Object,
    quotes: Array,
    filters: Object
});

const filters = ref({
    status: props.filters.status || '',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    search: props.filters.search || ''
});

const quoteStats = computed(() => [
    {
        label: 'Total Quotes',
        value: props.quoteStats.total || 0,
        icon: '📋',
        color: themeColors.value.primary
    },
    {
        label: 'Total Amount',
        value: formatCurrency(props.quoteStats.totalAmount || 0),
        icon: '💰',
        color: themeColors.value.success
    },
    {
        label: 'Pending',
        value: props.quoteStats.pending || 0,
        icon: '⏳',
        color: themeColors.value.warning
    },
    {
        label: 'Accepted',
        value: props.quoteStats.accepted || 0,
        icon: '✅',
        color: themeColors.value.success
    }
]);

const getStatusStyle = (status) => {
    const styles = {
        draft: { backgroundColor: '#f3f4f6', color: '#374151' },
        sent: { backgroundColor: '#dbeafe', color: '#1e40af' },
        accepted: { backgroundColor: '#dcfce7', color: '#166534' },
        rejected: { backgroundColor: '#fee2e2', color: '#991b1b' },
        expired: { backgroundColor: '#fef3c7', color: '#92400e' }
    };
    return styles[status] || { backgroundColor: '#f3f4f6', color: '#374151' };
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString();
};

const applyFilters = () => {
    router.get(route('admin.quotes.index'), filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const clearFilters = () => {
    filters.value = {
        status: '',
        start_date: '',
        end_date: '',
        search: ''
    };
    applyFilters();
};

const viewQuote = (quote) => {
    router.get(route('admin.quotes.show', quote.id));
};

const editQuote = (quote) => {
    router.get(route('admin.quotes.edit', quote.id));
};

const sendQuote = (quote) => {
    if (confirm(`Send quote ${quote.quote_number} to ${quote.customer_email}?`)) {
        router.post(route('admin.quotes.send', quote.id));
    }
};

const convertToInvoice = (quote) => {
    if (confirm(`Convert quote ${quote.quote_number} to invoice?`)) {
        router.post(route('admin.quotes.convert', quote.id));
    }
};

const exportQuotes = () => {
    window.location.href = route('admin.quotes.export', {
        ...filters.value,
        format: 'csv'
    });
};
</script>
