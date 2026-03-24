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
                <div class="grid grid-cols-2 md:grid-cols-7 gap-4">
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
                            <option v-for="type in transactionTypeOptions" :key="type" :value="type">
                                {{ formatType(type) }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Revenue Type</label>
                        <select v-model="filters.revenue_type"
                                class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                            <option value="">All Revenue Types</option>
                            <option v-for="option in revenueTypeOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
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
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Employee</label>
                        <select v-model="filters.employee_id"
                                class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                            <option value="">All Employees</option>
                            <option v-for="employee in employeeOptions" :key="employee.id" :value="String(employee.id)">
                                {{ employee.name }}
                            </option>
                        </select>
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
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Employee</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Revenue Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Payment Method</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" :style="{ borderColor: themeColors.border }">
                            <tr v-for="transaction in filteredTransactions" :key="transaction.transaction_id">
                                <td class="px-4 py-3 text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                    {{ transaction.transaction_id }}
                                </td>
                                <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ transaction.guest_name }}
                                </td>
                                <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ transaction.reference }}
                                </td>
                                <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ transaction.user_name || '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium"
                                          :style="getTypeStyle(transaction.type)">
                                        {{ transaction.type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ transaction.source_label || formatType(transaction.type) }}
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
                                <td class="px-4 py-3 text-sm font-medium">
                                    <button @click="viewTransaction(transaction)"
                                            class="px-3 py-1 rounded text-xs font-medium text-white transition-colors"
                                            :style="{ backgroundColor: themeColors.primary }">
                                        View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredTransactions.length === 0" class="text-center py-12">
                <div class="text-4xl mb-4">💳</div>
                <h3 class="text-lg font-medium mb-2" :style="{ color: themeColors.textPrimary }">No transactions found</h3>
                <p :style="{ color: themeColors.textSecondary }">No transactions match your current filters.</p>
            </div>
        </div>

        <!-- Transaction Detail Modal -->
        <Teleport to="body">
            <div v-if="selectedTransaction" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black bg-opacity-60" @click="closeModal"></div>
                <div class="relative w-full max-w-lg rounded-xl shadow-2xl z-10"
                     :style="{ backgroundColor: themeColors.card, border: '1px solid ' + themeColors.border }">
                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b"
                         :style="{ borderColor: themeColors.border }">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg font-bold"
                                 :style="{ backgroundColor: themeColors.primary + '20', color: themeColors.primary }">💳</div>
                            <div>
                                <h2 class="text-lg font-bold" :style="{ color: themeColors.textPrimary }">Transaction Details</h2>
                                <p class="text-xs font-mono" :style="{ color: themeColors.textSecondary }">{{ selectedTransaction.transaction_id }}</p>
                            </div>
                        </div>
                        <button @click="closeModal"
                                class="w-8 h-8 rounded-full flex items-center justify-center text-xl font-bold transition-colors hover:bg-red-500 hover:text-white"
                                :style="{ color: themeColors.textSecondary }">×</button>
                    </div>
                    <!-- Body -->
                    <div class="px-6 py-5 space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-lg p-3" :style="{ backgroundColor: themeColors.background }">
                                <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Guest / Reference</p>
                                <p class="text-sm font-semibold" :style="{ color: themeColors.textPrimary }">{{ selectedTransaction.guest_name || '—' }}</p>
                                <p class="text-xs" :style="{ color: themeColors.textSecondary }">{{ selectedTransaction.reference || '—' }}</p>
                            </div>
                            <div class="rounded-lg p-3" :style="{ backgroundColor: themeColors.background }">
                                <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Amount</p>
                                <p class="text-lg font-bold" :style="{ color: themeColors.success }">
                                    +{{ formatCurrency(selectedTransaction.amount || 0) }}
                                </p>
                            </div>
                            <div class="rounded-lg p-3" :style="{ backgroundColor: themeColors.background }">
                                <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Type</p>
                                <span class="px-2 py-1 rounded-full text-xs font-medium"
                                      :style="getTypeStyle(selectedTransaction.type)">
                                    {{ selectedTransaction.type }}
                                </span>
                            </div>
                            <div class="rounded-lg p-3" :style="{ backgroundColor: themeColors.background }">
                                <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Status</p>
                                <span class="px-2 py-1 rounded-full text-xs font-medium"
                                      :style="getStatusStyle(selectedTransaction.status)">
                                    {{ selectedTransaction.status }}
                                </span>
                            </div>
                            <div class="rounded-lg p-3" :style="{ backgroundColor: themeColors.background }">
                                <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Payment Method</p>
                                <p class="text-sm font-semibold" :style="{ color: themeColors.textPrimary }">{{ selectedTransaction.payment_method || '—' }}</p>
                            </div>
                            <div class="rounded-lg p-3" :style="{ backgroundColor: themeColors.background }">
                                <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Date</p>
                                <p class="text-sm" :style="{ color: themeColors.textPrimary }">{{ formatDate(selectedTransaction.date) }}</p>
                            </div>
                        </div>
                    </div>
                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-3 px-6 py-4 border-t"
                         :style="{ borderColor: themeColors.border }">
                        <button @click="closeModal"
                                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                                :style="{ backgroundColor: themeColors.background, color: themeColors.textSecondary, border: '1px solid ' + themeColors.border }">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </DashboardLayout>
</template>

<script>
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { formatCurrency } from '@/Utils/currency.js';

export default {
    name: 'ManagerTransactions',
    props: {
        user: Object,
        navigation: Array,
        transactionStats: Object,
        recentTransactions: Array,
        filters: Object,
        employeeOptions: {
            type: Array,
            default: () => []
        }
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
            revenue_type: props.filters.revenue_type || '',
            status: props.filters.status || '',
            start_date: props.filters.start_date || '',
            end_date: props.filters.end_date || '',
            employee_id: props.filters.employee_id ? String(props.filters.employee_id) : ''
        });

        const transactionTypeOptions = computed(() => {
            return [...new Set((props.recentTransactions || []).map(transaction => transaction.type).filter(Boolean))].sort();
        });

        const revenueTypeOptions = computed(() => {
            return [...new Map((props.recentTransactions || [])
                .filter(transaction => transaction.source_key || transaction.source_label)
                .map(transaction => [
                    transaction.source_key || transaction.source_label,
                    {
                        value: transaction.source_key || transaction.source_label,
                        label: transaction.source_label || formatType(transaction.type),
                    }
                ])).values()].sort((left, right) => left.label.localeCompare(right.label));
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

        const formatDate = (dateString) => {
            return new Date(dateString).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        };

        const formatType = (type) => {
            if (!type) return 'Unknown';
            return type.replace(/_/g, ' ').replace(/\b\w/g, letter => letter.toUpperCase());
        };

        const filteredTransactions = computed(() => {
            return (props.recentTransactions || []).filter(transaction => {
                const search = filters.value.search?.toLowerCase() || '';
                const matchesSearch = !search || [
                    transaction.transaction_id,
                    transaction.guest_name,
                    transaction.reference,
                    transaction.user_name,
                    transaction.source_label,
                ].filter(Boolean).join(' ').toLowerCase().includes(search);

                const matchesType = !filters.value.type || transaction.type === filters.value.type;
                const matchesRevenueType = !filters.value.revenue_type || (transaction.source_key || transaction.source_label) === filters.value.revenue_type;
                const matchesStatus = !filters.value.status || transaction.status === filters.value.status;
                const matchesEmployee = !filters.value.employee_id || String(transaction.user_id || '') === filters.value.employee_id;

                const transactionDate = transaction.date ? new Date(transaction.date) : null;
                const transactionDay = transactionDate && !Number.isNaN(transactionDate.getTime())
                    ? transactionDate.toISOString().slice(0, 10)
                    : '';
                const matchesStart = !filters.value.start_date || (transactionDay && transactionDay >= filters.value.start_date);
                const matchesEnd = !filters.value.end_date || (transactionDay && transactionDay <= filters.value.end_date);

                return matchesSearch && matchesType && matchesRevenueType && matchesStatus && matchesEmployee && matchesStart && matchesEnd;
            });
        });

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
                revenue_type: '',
                status: '',
                start_date: '',
                end_date: '',
                employee_id: ''
            };
            applyFilters();
        };

        const exportTransactions = () => {
            window.location.href = route('manager.transactions.export', {
                ...filters.value,
                format: 'csv'
            });
        };

        const selectedTransaction = ref(null);
        const viewTransaction = (transaction) => { selectedTransaction.value = transaction; };
        const closeModal = () => { selectedTransaction.value = null; };

        return {
            themeColors,
            filters,
            transactionStats: statData,
            employeeOptions: props.employeeOptions || [],
            transactionTypeOptions,
            revenueTypeOptions,
            filteredTransactions,
            getTypeStyle,
            getStatusStyle,
            formatCurrency,
            formatDate,
            formatType,
            applyFilters,
            clearFilters,
            exportTransactions,
            selectedTransaction,
            viewTransaction,
            closeModal,
        };
    }
};
</script>
