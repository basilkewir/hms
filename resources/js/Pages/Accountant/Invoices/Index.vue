<template>
    <DashboardLayout title="Invoice Management" :user="user">
        <!-- Page Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Invoice Management</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">View customer invoices.</p>
                </div>
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
                    <button @click="exportInvoices"
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
                    <button @click="printInvoices"
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

        <!-- Invoice Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                        <DocumentTextIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Invoices</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ invoiceStats.total }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                        <CurrencyDollarIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Amount</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ formatCurrency(invoiceStats.totalAmount || 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(245, 158, 11, 0.1)' }">
                        <ClockIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Pending</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ invoiceStats.pending }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(239, 68, 68, 0.1)' }">
                        <ExclamationTriangleIcon class="h-6 w-6" :style="{ color: themeColors.danger }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Overdue</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ invoiceStats.overdue }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <Link :href="route('accountant.invoices.overdue')" 
                  class="rounded-lg p-4 border shadow-sm transition-colors"
                  :style="{ 
                      backgroundColor: themeColors.card,
                      borderColor: themeColors.border,
                      borderStyle: 'solid',
                      borderWidth: '1px'
                  }"
                  @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                  @mouseleave="$event.target.style.backgroundColor = themeColors.card">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3"
                         :style="{ backgroundColor: 'rgba(239, 68, 68, 0.1)' }">
                        <ExclamationTriangleIcon class="h-5 w-5" :style="{ color: themeColors.danger }" />
                    </div>
                    <div>
                        <h3 class="font-medium" :style="{ color: themeColors.textPrimary }">Overdue Invoices</h3>
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">{{ invoiceStats.overdue }} invoices overdue</p>
                    </div>
                </div>
            </Link>

            <Link :href="route('accountant.invoices.paid')" 
                  class="rounded-lg p-4 border shadow-sm transition-colors"
                  :style="{ 
                      backgroundColor: themeColors.card,
                      borderColor: themeColors.border,
                      borderStyle: 'solid',
                      borderWidth: '1px'
                  }"
                  @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                  @mouseleave="$event.target.style.backgroundColor = themeColors.card">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3"
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                        <CheckCircleIcon class="h-5 w-5" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <h3 class="font-medium" :style="{ color: themeColors.textPrimary }">Paid Invoices</h3>
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">View payment history</p>
                    </div>
                </div>
            </Link>

            <button @click="sendReminders" 
                    class="rounded-lg p-4 border shadow-sm transition-colors"
                    :style="{ 
                        backgroundColor: themeColors.card,
                        borderColor: themeColors.border,
                        borderStyle: 'solid',
                        borderWidth: '1px'
                    }"
                    @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                    @mouseleave="$event.target.style.backgroundColor = themeColors.card">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3"
                         :style="{ backgroundColor: 'rgba(139, 92, 246, 0.1)' }">
                        <EnvelopeIcon class="h-5 w-5" :style="{ color: '#8b5cf6' }" />
                    </div>
                    <div>
                        <h3 class="font-medium" :style="{ color: themeColors.textPrimary }">Send Reminders</h3>
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">Email payment reminders</p>
                    </div>
                </div>
            </button>
        </div>

        <!-- Filters -->
        <div class="rounded-lg p-6 mb-8 border shadow-sm"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <div class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Status Filter</label>
                    <select v-model="selectedStatus" 
                            class="w-full px-3 py-2 rounded-md focus:outline-none transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary
                            }">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="sent">Sent</option>
                        <option value="paid">Paid</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="rounded-lg overflow-hidden shadow"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderBottomWidth: '1px'
                 }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Recent Invoices</h3>
            </div>
            
            <!-- Table Content -->
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Invoice</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="invoice in filteredInvoices" :key="invoice.id" 
                            class="transition-colors"
                            :style="{ 
                                borderBottomStyle: 'solid',
                                borderBottomWidth: '1px',
                                borderColor: themeColors.border
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ invoice.invoice_number }}</div>
                                <div class="text-xs" :style="{ color: themeColors.textTertiary }">{{ formatDate(invoice.issue_date) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm" :style="{ color: themeColors.textPrimary }">{{ invoice.customer_name }}</div>
                                <div class="text-xs" :style="{ color: themeColors.textTertiary }">{{ invoice.customer_email || 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(invoice.total_amount) }}</div>
                                <div class="text-xs" :style="{ color: themeColors.textTertiary }">Balance: {{ formatCurrency(invoice.balance_amount) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(invoice.status)">
                                    {{ formatStatus(invoice.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click="viewInvoice(invoice)" 
                                        class="mr-3 transition-colors"
                                        :style="{ color: themeColors.primary }"
                                        @mouseenter="$event.target.style.color = themeColors.hover"
                                        @mouseleave="$event.target.style.color = themeColors.primary">View</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="filteredInvoices.length === 0" class="text-center py-8">
                    <div>
                        <DocumentTextIcon class="mx-auto h-12 w-12" :style="{ color: themeColors.textTertiary }" />
                        <h3 class="mt-2 text-sm font-medium" :style="{ color: themeColors.textPrimary }">No invoices found</h3>
                        <p class="mt-1 text-sm" :style="{ color: themeColors.textTertiary }">Get started by creating your first invoice.</p>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'
import {
    PlusIcon,
    DocumentTextIcon,
    CurrencyDollarIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    EnvelopeIcon,
    DocumentArrowDownIcon,
    PrinterIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    invoiceStats: Object,
    invoices: Array,
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

const selectedStatus = ref(props.filters?.status || '')
const selectedFormat = ref('xlsx')
const invoiceStats = computed(() => props.invoiceStats || { total: 0, totalAmount: 0, pending: 0, overdue: 0 })
const invoices = computed(() => props.invoices || [])

const filteredInvoices = computed(() => {
    if (!selectedStatus.value) return invoices.value
    return invoices.value.filter(invoice => invoice.status === selectedStatus.value)
})

const getStatusColor = (status) => {
    const colors = {
        draft: 'bg-gray-700 text-gray-300',
        sent: 'bg-blue-900 text-blue-200',
        paid: 'bg-green-900 text-green-200',
        overdue: 'bg-red-900 text-red-200'
    }
    return colors[status] || 'bg-gray-700 text-gray-300'
}

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const sendReminders = () => {
    router.post(route('accountant.invoices.sendReminders'))
}

const viewInvoice = (invoice) => {
    router.get(route('accountant.invoices.show', invoice.id))
}

const exportInvoices = () => {
    const params = new URLSearchParams()
    
    if (selectedStatus.value) params.append('status', selectedStatus.value)
    params.append('format', selectedFormat.value)
    
    const queryString = params.toString()
    const url = queryString ? `?${queryString}` : ''
    
    window.location.href = route('accountant.invoices.export') + url
}

const printInvoices = () => {
    const params = new URLSearchParams()
    
    if (selectedStatus.value) params.append('status', selectedStatus.value)
    params.append('format', 'print')
    
    const queryString = params.toString()
    const url = queryString ? `?${queryString}` : ''
    
    window.open(route('accountant.invoices.export') + url, '_blank')
}

watch(selectedStatus, (value) => {
    router.get(route('accountant.invoices.index'), { status: value || null }, { preserveState: true, replace: true })
})
</script>
