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
                        <tr v-for="transaction in recentTransactions" :key="transaction.id" 
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :class="transaction.type === 'refund' ? 'text-red-600' : 'text-green-600'">
                                {{ transaction.type === 'refund' ? '-' : '+' }}{{ formatCurrency(transaction.amount || 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatPaymentMethod(transaction.payment_method) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(transaction.status)">
                                    {{ formatStatus(transaction.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDateTime(transaction.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button @click="viewTransaction(transaction)" class="text-blue-600 hover:text-blue-900">View</button>
                                    <button v-if="transaction.status === 'pending'" 
                                            @click="processTransaction(transaction)" 
                                            class="text-green-600 hover:text-green-900">Process</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
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
const selectedFormat = ref('xlsx')

const getTypeColor = (type) => {
    const colors = {
        payment: 'bg-green-100 text-green-800',
        refund: 'bg-red-100 text-red-800',
        deposit: 'bg-blue-100 text-blue-800',
        fee: 'bg-yellow-100 text-yellow-800'
    }
    return colors[type] || 'bg-gray-100 text-gray-800'
}

const getStatusColor = (status) => {
    const colors = {
        completed: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        failed: 'bg-red-100 text-red-800',
        cancelled: 'bg-gray-100 text-gray-800'
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const formatType = (type) => {
    return type.charAt(0).toUpperCase() + type.slice(1)
}

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1)
}

const formatPaymentMethod = (method) => {
    return method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
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
    router.get(route('accountant.transactions.index'), {
        transaction_id: transaction.id
    }, {
        preserveScroll: true,
        preserveState: true
    })
}

const processTransaction = (transaction) => {
    router.post(route('accountant.transactions.process', { payment: transaction.id }), {}, {
        preserveScroll: true
    })
}
</script>
