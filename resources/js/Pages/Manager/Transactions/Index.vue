<template>
    <DashboardLayout title="Transactions" :user="user" :navigation="navigation">
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Transactions</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">All financial transactions including payments, sales, POS transactions, and expenses</p>
                </div>
                <div class="flex gap-2">
                    <button @click="exportTransactions"
                            class="px-4 py-2 rounded-md font-medium text-white transition-colors"
                            :style="{ backgroundColor: themeColors.success }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.success + 'cc'"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                        📥 Export
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div v-for="stat in transactionStats" :key="stat.label"
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
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Search</label>
                        <input v-model="filters.search" type="text" placeholder="Transaction ID, guest name..."
                               class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Type</label>
                        <select v-model="filters.type"
                                class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                            <option value="">All Types</option>
                            <option value="payment">Payments</option>
                            <option value="sale">Sales</option>
                            <option value="pos_transaction">POS Transactions</option>
                            <option value="folio_charge">Room Charges</option>
                            <option value="expense">Expenses</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Status</label>
                        <select v-model="filters.status"
                                class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                            <option value="">All Status</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                            <option value="active">Active</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Start Date</label>
                        <input v-model="filters.start_date" type="date"
                               class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">End Date</label>
                        <input v-model="filters.end_date" type="date"
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

            <!-- Transactions Table -->
            <div class="rounded-lg border shadow-sm overflow-hidden"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr :style="{ backgroundColor: themeColors.background }">
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Transaction ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Guest Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Reference</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Payment Method</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" :style="{ borderColor: themeColors.border }">
                            <tr v-for="transaction in recentTransactions" :key="transaction.transaction_id">
                                <td class="px-4 py-3 text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                    {{ transaction.transaction_id }}
                                </td>
                                <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ transaction.guest_name }}
                                </td>
                                <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ transaction.reference }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium"
                                          :style="getTypeStyle(transaction.type)">
                                        {{ transaction.type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                    {{ formatCurrency(transaction.amount) }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium"
                                          :style="getStatusStyle(transaction.status)">
                                        {{ transaction.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ transaction.payment_method }}
                                </td>
                                <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ formatDate(transaction.date) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="recentTransactions.length === 0" class="text-center py-12">
                <div class="text-4xl mb-4">💳</div>
                <h3 class="text-lg font-medium mb-2" :style="{ color: themeColors.textPrimary }">No transactions found</h3>
                <p :style="{ color: themeColors.textSecondary }">No transactions match your current filters.</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<script>
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';

export default {
    name: 'ManagerTransactions',
    props: {
        user: Object,
        navigation: Array,
        transactionStats: Object,
        recentTransactions: Array,
        filters: Object
    },
    setup(props) {
        const page = usePage();
        const themeColors = computed(() => page.props.themeColors || {
            primary: '#3b82f6',
            success: '#10b981',
            danger: '#ef4444',
            warning: '#f59e0b',
            background: '#ffffff',
            card: '#ffffff',
            border: '#e5e7eb',
            textPrimary: '#111827',
            textSecondary: '#6b7280'
        });

        const filters = ref({
            search: props.filters.search || '',
            type: props.filters.type || '',
            status: props.filters.status || '',
            start_date: props.filters.start_date || '',
            end_date: props.filters.end_date || ''
        });

        const statData = computed(() => [
            {
                label: 'Total Transactions',
                value: props.transactionStats.total || 0,
                icon: '💳',
                color: themeColors.value.primary
            },
            {
                label: 'Completed',
                value: props.transactionStats.completed || 0,
                icon: '✅',
                color: themeColors.value.success
            },
            {
                label: 'Pending',
                value: props.transactionStats.pending || 0,
                icon: '⏳',
                color: themeColors.value.warning
            },
            {
                label: 'Failed',
                value: props.transactionStats.failed || 0,
                icon: '❌',
                color: themeColors.value.danger
            },
            {
                label: 'Today Revenue',
                value: formatCurrency(props.transactionStats.todayRevenue || 0),
                icon: '💰',
                color: themeColors.value.success
            }
        ]);

        const getTypeStyle = (type) => {
            const styles = {
                payment: { backgroundColor: '#dbeafe', color: '#1e40af' },
                sale: { backgroundColor: '#dcfce7', color: '#166534' },
                pos_transaction: { backgroundColor: '#fef3c7', color: '#92400e' },
                folio_charge: { backgroundColor: '#ede9fe', color: '#5b21b6' },
                expense: { backgroundColor: '#fee2e2', color: '#991b1b' }
            };
            return styles[type] || { backgroundColor: '#f3f4f6', color: '#374151' };
        };

        const getStatusStyle = (status) => {
            const styles = {
                completed: { backgroundColor: '#dcfce7', color: '#166534' },
                pending: { backgroundColor: '#fef3c7', color: '#92400e' },
                failed: { backgroundColor: '#fee2e2', color: '#991b1b' },
                active: { backgroundColor: '#dbeafe', color: '#1e40af' }
            };
            return styles[status] || { backgroundColor: '#f3f4f6', color: '#374151' };
        };

        const formatCurrency = (amount) => {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(amount);
        };

        const formatDate = (dateString) => {
            return new Date(dateString).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        };

        const applyFilters = () => {
            router.get(route('manager.transactions.index'), filters.value, {
                preserveState: true,
                preserveScroll: true
            });
        };

        const clearFilters = () => {
            filters.value = {
                search: '',
                type: '',
                status: '',
                start_date: '',
                end_date: ''
            };
            applyFilters();
        };

        const exportTransactions = () => {
            window.location.href = route('manager.transactions.export', {
                ...filters.value,
                format: 'csv'
            });
        };

        return {
            themeColors,
            filters,
            transactionStats: statData,
            getTypeStyle,
            getStatusStyle,
            formatCurrency,
            formatDate,
            applyFilters,
            clearFilters,
            exportTransactions
        };
    }
};
</script>
