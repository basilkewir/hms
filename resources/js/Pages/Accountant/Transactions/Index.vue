<template>
    <DashboardLayout title="Transaction Management" :user="user">
        <!-- Page Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Transaction Management</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Monitor and manage all financial transactions.</p>
                </div>
                <div class="flex space-x-3">
                    <div class="flex space-x-3">
                        <select v-model="selectedFormat"
                                class="rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary
                                }">
                            <option value="xlsx">Excel (.xlsx)</option>
                            <option value="csv">CSV (.csv)</option>
                            <option value="pdf">PDF (.pdf)</option>
                        </select>
                        <button @click="exportTransactions"
                                class="px-4 py-2 rounded-md transition-colors flex items-center"
                                :style="{
                                    backgroundColor: themeColors.primary,
                                    color: '#ffffff'
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                            <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                            Export
                        </button>
                        <button @click="printTransactions"
                                class="px-4 py-2 rounded-md transition-colors flex items-center"
                                :style="{
                                    backgroundColor: themeColors.success,
                                    color: '#ffffff'
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                            <PrinterIcon class="h-4 w-4 mr-2" />
                            Print
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <div class="flex items-center">
                    <div class="p-3 rounded-full mr-4"
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                        <CurrencyDollarIcon class="h-6 w-6"
                                            :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Today's Revenue</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ formatCurrency(transactionStats.todayRevenue || 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <div class="flex items-center">
                    <div class="p-3 rounded-full mr-4"
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                        <CreditCardIcon class="h-6 w-6"
                                        :style="{ color: '#3B82F6' }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Transactions</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ transactionStats.totalTransactions }}</p>
                    </div>
                </div>
            </div>
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <div class="flex items-center">
                    <div class="p-3 rounded-full mr-4"
                         :style="{ backgroundColor: 'rgba(245, 158, 11, 0.1)' }">
                        <ClockIcon class="h-6 w-6"
                                   :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Pending</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ transactionStats.pending }}</p>
                    </div>
                </div>
            </div>
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <div class="flex items-center">
                    <div class="p-3 rounded-full mr-4"
                         :style="{ backgroundColor: 'rgba(239, 68, 68, 0.1)' }">
                        <ExclamationTriangleIcon class="h-6 w-6"
                                             :style="{ color: themeColors.danger }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Failed</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ transactionStats.failed }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <Link :href="route('accountant.transactions.payments')"
                  class="shadow rounded-lg p-4 transition-colors flex items-center"
                  :style="{
                      backgroundColor: themeColors.card,
                      borderColor: themeColors.border
                  }"
                  @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                  @mouseleave="$event.target.style.backgroundColor = themeColors.card">
                <div class="p-3 rounded-full mr-3"
                     :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                    <CurrencyDollarIcon class="h-6 w-6"
                                        :style="{ color: themeColors.success }" />
                </div>
                <div>
                    <h3 class="font-medium mb-1"
                        :style="{ color: themeColors.textPrimary }">Payments</h3>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">View all payments</p>
                </div>
            </Link>

            <Link :href="route('accountant.transactions.refunds')"
                  class="shadow rounded-lg p-4 transition-colors flex items-center"
                  :style="{
                      backgroundColor: themeColors.card,
                      borderColor: themeColors.border
                  }"
                  @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                  @mouseleave="$event.target.style.backgroundColor = themeColors.card">
                <div class="p-3 rounded-full mr-3"
                     :style="{ backgroundColor: 'rgba(239, 68, 68, 0.1)' }">
                    <ArrowUturnLeftIcon class="h-6 w-6"
                                        :style="{ color: themeColors.danger }" />
                </div>
                <div>
                    <h3 class="font-medium mb-1"
                        :style="{ color: themeColors.textPrimary }">Refunds</h3>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Process refunds</p>
                </div>
            </Link>

            <Link :href="route('accountant.transactions.pending')"
                  class="shadow rounded-lg p-4 transition-colors flex items-center"
                  :style="{
                      backgroundColor: themeColors.card,
                      borderColor: themeColors.border
                  }"
                  @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                  @mouseleave="$event.target.style.backgroundColor = themeColors.card">
                <div class="p-3 rounded-full mr-3"
                     :style="{ backgroundColor: 'rgba(245, 158, 11, 0.1)' }">
                    <ClockIcon class="h-6 w-6"
                               :style="{ color: themeColors.warning }" />
                </div>
                <div>
                    <h3 class="font-medium mb-1"
                        :style="{ color: themeColors.textPrimary }">Pending</h3>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Review pending</p>
                </div>
            </Link>

            <button @click="reconcileTransactions"
                    class="shadow rounded-lg p-4 transition-colors flex items-center"
                    :style="{
                        backgroundColor: themeColors.card,
                        borderColor: themeColors.border
                    }"
                    @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                    @mouseleave="$event.target.style.backgroundColor = themeColors.card">
                <div class="p-3 rounded-full mr-3"
                     :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                    <CheckCircleIcon class="h-6 w-6"
                                     :style="{ color: '#3B82F6' }" />
                </div>
                    <div>
                    <h3 class="font-medium mb-1"
                        :style="{ color: themeColors.textPrimary }">Reconcile</h3>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Bank reconciliation</p>
                </div>
            </button>
        </div>

        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Search</label>
                    <input v-model="filters.search" type="text" placeholder="Search transactions..."
                           class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                           :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Type</label>
                    <select v-model="filters.type"
                            class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        <option value="">All Types</option>
                        <option v-for="type in transactionTypeOptions" :key="type" :value="type">
                            {{ formatType(type) }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Revenue Type</label>
                    <select v-model="filters.revenue_type"
                            class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        <option value="">All Revenue Types</option>
                        <option v-for="option in revenueTypeOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Status</label>
                    <select v-model="filters.status"
                            class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        <option value="">All Status</option>
                        <option v-for="status in statusOptions" :key="status" :value="status">
                            {{ formatStatus(status) }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Payment Method</label>
                    <select v-model="filters.payment_method"
                            class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        <option value="">All Methods</option>
                        <option v-for="method in paymentMethodOptions" :key="method" :value="method">
                            {{ formatPaymentMethod(method) }}
                        </option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button @click="clearFilters"
                            class="w-full px-4 py-2 rounded-md transition-colors"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, border: '1px solid ' + themeColors.border }">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="shadow rounded-lg overflow-hidden"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="px-6 py-4 border-b"
                 :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Recent Transactions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y"
                     :style="{ borderColor: themeColors.border }">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Transaction ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Guest/Reference
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Revenue Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Payment Method
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y"
                           :style="{ borderColor: themeColors.border }">
                        <tr v-for="transaction in filteredTransactions" :key="transaction.id"
                            class="hover:bg-opacity-50 transition-colors"
                            :style="{ hover: { backgroundColor: themeColors.hover } }">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ transaction.transaction_id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium"
                                     :style="{ color: themeColors.textPrimary }">{{ transaction.guest_name }}</div>
                                <div class="text-sm"
                                     :style="{ color: themeColors.textSecondary }">{{ transaction.reference }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getTypeColor(transaction.type)">
                                    {{ formatType(transaction.type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ transaction.source_label || formatType(transaction.type) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :class="transaction.type === 'refund' ? 'text-red-600' : 'text-green-600'">
                                {{ transaction.type === 'refund' ? '-' : '+' }}{{ formatCurrency(transaction.amount || 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                {{ formatPaymentMethod(transaction.payment_method) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(transaction.status)">
                                    {{ formatStatus(transaction.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                {{ formatDateTime(transaction.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button @click="viewTransaction(transaction)" class="text-blue-600 hover:text-blue-900">View</button>
                                    <button v-if="transaction.type === 'payment' && transaction.status === 'pending'"
                                            @click="processTransaction(transaction)"
                                            class="text-green-600 hover:text-green-900">Process</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Transaction Detail Modal -->
        <Teleport to="body">
            <div v-if="selectedTransaction" class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 @click.self="closeModal">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black bg-opacity-60" @click="closeModal"></div>

                <!-- Modal Card -->
                <div class="relative w-full max-w-lg rounded-xl shadow-2xl z-10"
                     :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, border: '1px solid' }">

                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b"
                         :style="{ borderColor: themeColors.border }">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg font-bold"
                                 :style="{ backgroundColor: themeColors.primary + '20', color: themeColors.primary }">
                                💳
                            </div>
                            <div>
                                <h2 class="text-lg font-bold" :style="{ color: themeColors.textPrimary }">
                                    Transaction Details
                                </h2>
                                <p class="text-xs font-mono" :style="{ color: themeColors.textSecondary }">
                                    {{ selectedTransaction.transaction_id }}
                                </p>
                            </div>
                        </div>
                        <button @click="closeModal"
                                class="w-8 h-8 rounded-full flex items-center justify-center text-lg font-bold transition-colors hover:bg-red-500 hover:text-white"
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
                                <p class="text-lg font-bold"
                                   :class="selectedTransaction.type === 'refund' ? 'text-red-500' : 'text-green-500'">
                                    {{ selectedTransaction.type === 'refund' ? '-' : '+' }}{{ formatCurrency(selectedTransaction.amount || 0) }}
                                </p>
                            </div>
                            <div class="rounded-lg p-3" :style="{ backgroundColor: themeColors.background }">
                                <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Type</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getTypeColor(selectedTransaction.type)">
                                    {{ formatType(selectedTransaction.type) }}
                                </span>
                            </div>
                            <div class="rounded-lg p-3" :style="{ backgroundColor: themeColors.background }">
                                <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Status</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(selectedTransaction.status)">
                                    {{ formatStatus(selectedTransaction.status) }}
                                </span>
                            </div>
                            <div class="rounded-lg p-3" :style="{ backgroundColor: themeColors.background }">
                                <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Payment Method</p>
                                <p class="text-sm font-semibold" :style="{ color: themeColors.textPrimary }">{{ formatPaymentMethod(selectedTransaction.payment_method) }}</p>
                            </div>
                            <div class="rounded-lg p-3" :style="{ backgroundColor: themeColors.background }">
                                <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Date & Time</p>
                                <p class="text-sm" :style="{ color: themeColors.textPrimary }">{{ formatDateTime(selectedTransaction.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="flex items-center justify-end gap-3 px-6 py-4 border-t"
                         :style="{ borderColor: themeColors.border }">
                        <button @click="closeModal"
                                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                                :style="{ backgroundColor: themeColors.background, color: themeColors.textSecondary, border: '1px solid ' + themeColors.border }">
                            Close
                        </button>
                        <button v-if="selectedTransaction.type === 'payment' && selectedTransaction.status === 'pending'"
                                @click="processTransaction(selectedTransaction)"
                                class="px-4 py-2 rounded-lg text-sm font-medium text-white transition-colors bg-green-600 hover:bg-green-700">
                            ✓ Mark as Processed
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'
import {
    DocumentArrowDownIcon,
    CurrencyDollarIcon,
    CreditCardIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    ArrowUturnLeftIcon,
    CheckCircleIcon,
    PlusIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    transactionStats: Object,
    recentTransactions: Object, // Changed to Object for pagination
    filters: Object,
})

const { loadTheme } = useTheme();
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
    hover: `rgba(255, 255, 255, 0.1)`
}));

loadTheme();

const transactionStats = computed(() => props.transactionStats || {
    todayRevenue: 0,
    totalTransactions: 0,
    pending: 0,
    failed: 0
})

const recentTransactions = computed(() => props.recentTransactions?.data || [])
const pagination = computed(() => ({
    current_page: props.recentTransactions?.current_page || 1,
    last_page: props.recentTransactions?.last_page || 1,
    per_page: props.recentTransactions?.per_page || 15,
    total: props.recentTransactions?.total || 0,
    from: props.recentTransactions?.from || 0,
    to: props.recentTransactions?.to || 0,
}))
const filters = ref({
    search: '',
    type: props.filters?.type || '',
    revenue_type: props.filters?.revenue_type || '',
    status: props.filters?.status || '',
    payment_method: '',
})
const selectedFormat = ref('xlsx')
const selectedTransaction = ref(null)

const transactionTypeOptions = computed(() => {
    return [...new Set(recentTransactions.value.map(transaction => transaction.type).filter(Boolean))].sort()
})

const revenueTypeOptions = computed(() => {
    return [...new Map(recentTransactions.value
        .filter(transaction => transaction.source_key || transaction.source_label)
        .map(transaction => [
            transaction.source_key || transaction.source_label,
            {
                value: transaction.source_key || transaction.source_label,
                label: transaction.source_label || formatType(transaction.type),
            }
        ])).values()].sort((left, right) => left.label.localeCompare(right.label))
})

const statusOptions = computed(() => {
    return [...new Set(recentTransactions.value.map(transaction => transaction.status).filter(Boolean))].sort()
})

const paymentMethodOptions = computed(() => {
    return [...new Set(recentTransactions.value.map(transaction => transaction.payment_method).filter(Boolean))].sort()
})

const filteredTransactions = computed(() => {
    return recentTransactions.value.filter(transaction => {
        const search = filters.value.search?.toLowerCase() || ''
        const matchesSearch = !search || [
            transaction.transaction_id,
            transaction.guest_name,
            transaction.reference,
            transaction.source_label,
        ].filter(Boolean).join(' ').toLowerCase().includes(search)

        const matchesType = !filters.value.type || transaction.type === filters.value.type
        const matchesRevenueType = !filters.value.revenue_type || (transaction.source_key || transaction.source_label) === filters.value.revenue_type
        const matchesStatus = !filters.value.status || transaction.status === filters.value.status
        const matchesPaymentMethod = !filters.value.payment_method || transaction.payment_method === filters.value.payment_method

        return matchesSearch && matchesType && matchesRevenueType && matchesStatus && matchesPaymentMethod
    })
})

const openModal = (transaction) => {
    selectedTransaction.value = transaction
}

const closeModal = () => {
    selectedTransaction.value = null
}

const getTypeColor = (type) => {
    const colors = {
        payment:      'bg-green-600 text-white',
        refund:       'bg-red-600 text-white',
        deposit:      'bg-blue-600 text-white',
        fee:          'bg-yellow-500 text-white',
        folio_charge: 'bg-purple-600 text-white',
        room_charge:  'bg-indigo-600 text-white',
        charge:       'bg-indigo-600 text-white',
        sale:         'bg-teal-600 text-white',
        expense:      'bg-orange-600 text-white',
        transfer:     'bg-cyan-600 text-white',
        adjustment:   'bg-pink-600 text-white',
    }
    return colors[type?.toLowerCase()] || 'bg-slate-500 text-white'
}

const getStatusColor = (status) => {
    const colors = {
        completed: 'bg-green-600 text-white',
        complete:  'bg-green-600 text-white',
        paid:      'bg-green-600 text-white',
        active:    'bg-blue-600 text-white',
        pending:   'bg-yellow-500 text-white',
        failed:    'bg-red-600 text-white',
        cancelled: 'bg-slate-500 text-white',
        canceled:  'bg-slate-500 text-white',
        refunded:  'bg-red-500 text-white',
        voided:    'bg-slate-500 text-white',
        draft:     'bg-slate-500 text-white',
        overdue:   'bg-orange-600 text-white',
    }
    return colors[status?.toLowerCase()] || 'bg-slate-500 text-white'
}

const formatType = (type) => {
    if (!type) return 'Unknown'
    return type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const clearFilters = () => {
    filters.value = {
        search: '',
        type: '',
        revenue_type: '',
        status: '',
        payment_method: '',
    }
}

const formatStatus = (status) => {
    if (!status) return 'Unknown'
    return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatPaymentMethod = (method) => {
    if (!method) return '—'
    return method.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDateTime = (date) => {
    return new Date(date).toLocaleString()
}

const exportTransactions = () => {
    const params = new URLSearchParams()
    params.append('format', selectedFormat.value)

    const queryString = params.toString()
    const url = queryString ? `?${queryString}` : ''

    window.location.href = route('accountant.transactions.export') + url
}

const printTransactions = () => {
    const params = new URLSearchParams()
    params.append('format', 'print')

    const queryString = params.toString()
    const url = queryString ? `?${queryString}` : ''

    window.open(route('accountant.transactions.export') + url, '_blank')
}

const reconcileTransactions = () => {
    router.get(route('accountant.transactions.index'), {}, {
        preserveScroll: true,
        preserveState: true
    })
}

const viewTransaction = (transaction) => {
    openModal(transaction)
}

const processTransaction = (transaction) => {
    if (!confirm(`Mark transaction ${transaction.transaction_id} as processed?`)) return
    router.post(route('accountant.transactions.process', { payment: transaction.source_id }), {}, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal()
        }
    })
}
</script>
