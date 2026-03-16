<template>
    <DashboardLayout title="Sales Overview" :user="user">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Sales Overview</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">System-wide sales analytics and management.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="exportSales" 
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{ 
                                backgroundColor: '#8b5cf6',
                            }"
                            @mouseenter="$event.target.style.backgroundColor = '#7c3aed'"
                            @mouseleave="$event.target.style.backgroundColor = '#8b5cf6'">
                        📥 Export Sales
                    </button>
                </div>
            </div>
        </div>

        <!-- Sales Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
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
                        <span class="text-2xl">💰</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Sales</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ totalSalesCount }}</p>
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
                        <span class="text-2xl">✅</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Completed</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ completedSales }}</p>
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
                         :style="{ backgroundColor: 'rgba(250, 204, 21, 0.1)' }">
                        <span class="text-2xl">💳</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Cash Sales</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ cashSales }}</p>
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
                        <span class="text-2xl">💳</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Card Sales</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ cardSales }}</p>
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
                         :style="{ backgroundColor: 'rgba(168, 85, 247, 0.1)' }">
                        <span class="text-2xl">📊</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Revenue</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ formatCurrency(totalRevenue) }}</p>
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
                        <span class="text-2xl">📈</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Profit</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ formatCurrency(totalProfit) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="rounded-lg p-6 border shadow-sm mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textPrimary }">Date From</label>
                    <div class="relative">
                        <input type="date" v-model="filters.date_from" ref="dateFromInput"
                               class="w-full px-3 py-2 pr-10 border rounded-md focus:outline-none focus:ring-2 transition-colors cursor-pointer"
                               :style="{
                                   backgroundColor: themeColors.background,
                                   borderWidth: '1px',
                                   borderStyle: 'solid',
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary
                               }">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
                             :style="{ color: themeColors.textSecondary }">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="absolute inset-0 cursor-pointer" @click="openDatePicker('dateFrom')"></div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textPrimary }">Date To</label>
                    <div class="relative">
                        <input type="date" v-model="filters.date_to" ref="dateToInput"
                               class="w-full px-3 py-2 pr-10 border rounded-md focus:outline-none focus:ring-2 transition-colors cursor-pointer"
                               :style="{
                                   backgroundColor: themeColors.background,
                                   borderWidth: '1px',
                                   borderStyle: 'solid',
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary
                               }">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
                             :style="{ color: themeColors.textSecondary }">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="absolute inset-0 cursor-pointer" @click="openDatePicker('dateTo')"></div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textPrimary }">Payment Method</label>
                    <select v-model="filters.payment_method"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 transition-colors"
                            :style="{
                                backgroundColor: themeColors.background,
                                borderWidth: '1px',
                                borderStyle: 'solid',
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary
                            }">
                        <option value="">All Methods</option>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="mobile">Mobile Payment</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textPrimary }">Customer</label>
                    <select v-model="filters.customer_id"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 transition-colors"
                            :style="{
                                backgroundColor: themeColors.background,
                                borderWidth: '1px',
                                borderStyle: 'solid',
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary
                            }">
                        <option value="">All Customers</option>
                        <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                            {{ customer.first_name }} {{ customer.last_name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textPrimary }">Search by Name</label>
                    <input type="text" v-model="filters.customer_name"
                           placeholder="Type client name..."
                           class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 transition-colors"
                           :style="{
                               backgroundColor: themeColors.background,
                               borderWidth: '1px',
                               borderStyle: 'solid',
                               borderColor: themeColors.border,
                               color: themeColors.textPrimary
                           }" />
                </div>
            </div>
        </div>

        <!-- Sales Table -->
        <div class="rounded-lg overflow-hidden shadow-sm"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="px-6 py-4 border-b"
                 :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-semibold"
                    :style="{ color: themeColors.textPrimary }">Sales Transactions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Sale #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Staff</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Room</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Subtotal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Tax</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Discount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Payment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y"
                          :style="{ borderColor: themeColors.border }">
                        <tr v-for="sale in paginatedSales" :key="sale.id"
                            class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium"
                                     :style="{ color: themeColors.textPrimary }">{{ sale.sale_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textPrimary }">{{ formatDate(sale.created_at) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ sale.is_walk_in ? 'Walk-In' : (sale.customer ? (sale.customer.first_name + ' ' + sale.customer.last_name).trim() : (sale.customer_name || 'Guest')) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                    {{ sale.user ? (sale.user.first_name + ' ' + sale.user.last_name).trim() : '—' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm" :style="{ color: sale.is_charged_to_room ? themeColors.primary : themeColors.textSecondary }">
                                    {{ sale.room?.room_number ? 'Room ' + sale.room.room_number : (sale.is_charged_to_room ? 'Room Charge' : '—') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textPrimary }">{{ sale.items?.length || 0 }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textPrimary }">{{ formatCurrency(sale.subtotal) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textPrimary }">{{ formatCurrency(sale.tax_amount) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textPrimary }">{{ formatCurrency(sale.discount_amount) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium"
                                     :style="{ color: themeColors.textPrimary }">{{ formatCurrency(sale.total_amount) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                          :style="getPaymentMethodStyle(sale.payment_method)">
                                        {{ getPaymentMethodLabel(sale.payment_method) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                          :style="getPaymentStatusStyle(sale.payment_status)">
                                        {{ getPaymentStatusLabel(sale.payment_status) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium space-x-2">
                                    <button @click="viewSale(sale)" 
                                            class="text-blue-600 hover:text-blue-800 text-sm">
                                        View
                                    </button>
                                    <button @click="printSale(sale)" 
                                            class="text-green-600 hover:text-green-800 text-sm">
                                        Print
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div v-if="sales.length === 0" class="text-center py-8">
                    <div class="text-gray-400 text-lg">📋</div>
                    <p class="text-gray-500 mt-2">No sales found</p>
                </div>
            </div>
            
            <!-- Pagination -->
            <div v-if="totalPages > 1" class="px-6 py-4 border-t flex items-center justify-between"
                 :style="{ borderColor: themeColors.border }">
                <div class="text-sm text-gray-700">
                    Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, filteredSales.length) }} 
                    of {{ filteredSales.length }} results
                </div>
                <div class="flex space-x-2">
                    <button @click="currentPage = Math.max(1, currentPage - 1)"
                            :disabled="currentPage === 1"
                            class="px-3 py-1 text-sm border rounded-md transition-colors"
                            :style="{
                                backgroundColor: currentPage === 1 ? themeColors.background : themeColors.card,
                                borderColor: themeColors.border,
                                color: currentPage === 1 ? themeColors.textTertiary : themeColors.textPrimary,
                                opacity: currentPage === 1 ? 0.5 : 1
                            }">
                        Previous
                    </button>
                    <span class="px-3 py-1 text-sm"
                          :style="{ color: themeColors.textPrimary }">
                        Page {{ currentPage }} of {{ totalPages }}
                    </span>
                    <button @click="currentPage = Math.min(totalPages, currentPage + 1)"
                            :disabled="currentPage === totalPages"
                            class="px-3 py-1 text-sm border rounded-md transition-colors"
                            :style="{
                                backgroundColor: currentPage === totalPages ? themeColors.background : themeColors.card,
                                borderColor: themeColors.border,
                                color: currentPage === totalPages ? themeColors.textTertiary : themeColors.textPrimary,
                                opacity: currentPage === totalPages ? 0.5 : 1
                            }">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
<script setup>
import { ref, computed, watch } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { usePage, router } from '@inertiajs/vue3'

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
    info: `var(--kotel-info)`,
    hover: `var(--kotel-primary-hover)`
}))

loadTheme()

// Props
const props = defineProps({
    user: Object,
    sales: Array,
    customers: Array,
    stats: Object
})

// Data
const filters = ref({
    date_from: '',
    date_to: '',
    payment_method: '',
    customer_id: '',
    customer_name: ''
})
const currentPage = ref(1)
const perPage = ref(10)

// Refs for date inputs
const dateFromInput = ref(null)
const dateToInput = ref(null)

// Computed properties
const filteredSales = computed(() => {
    let filtered = props.sales

    // Date filter
    if (filters.value.date_from) {
        const fromDate = new Date(filters.value.date_from)
        filtered = filtered.filter(sale => new Date(sale.created_at) >= fromDate)
    }

    if (filters.value.date_to) {
        const toDate = new Date(filters.value.date_to)
        toDate.setHours(23, 59, 59, 999) // End of day
        filtered = filtered.filter(sale => new Date(sale.created_at) <= toDate)
    }

    // Payment method filter
    if (filters.value.payment_method) {
        filtered = filtered.filter(sale => sale.payment_method === filters.value.payment_method)
    }

    // Customer filter by linked customer record
    if (filters.value.customer_id) {
        filtered = filtered.filter(sale => sale.customer_id == filters.value.customer_id)
    }

    // Customer filter by name text search (covers walk-ins & all sales)
    if (filters.value.customer_name) {
        const q = filters.value.customer_name.toLowerCase()
        filtered = filtered.filter(sale => {
            const name = sale.is_walk_in
                ? 'walk-in'
                : (sale.customer
                    ? (sale.customer.first_name + ' ' + sale.customer.last_name).trim()
                    : (sale.customer_name || 'guest')
                )
            return name.toLowerCase().includes(q)
        })
    }

    return filtered
})

const totalPages = computed(() => {
    return Math.ceil(filteredSales.value.length / perPage.value)
})

const paginatedSales = computed(() => {
    const start = (currentPage.value - 1) * perPage.value
    const end = start + perPage.value
    return filteredSales.value.slice(start, end)
})

const completedSales = computed(() => props.stats?.completed_count ?? props.sales.filter(s => s.payment_status === 'paid').length)

const cashSales = computed(() => props.stats?.cash_count ?? props.sales.filter(s => s.payment_method === 'cash').length)

const cardSales = computed(() => props.stats?.card_count ?? props.sales.filter(s => s.payment_method === 'card').length)

const totalRevenue = computed(() => props.stats?.total_revenue ?? props.sales.reduce((t, s) => t + (s.total_amount || 0), 0))

const totalProfit = computed(() => props.stats?.total_profit ?? props.sales.reduce((t, s) => {
    const cost = s.items?.reduce((c, i) => c + ((i.unit_cost || 0) * (i.quantity || 0)), 0) ?? 0
    return t + (s.total_amount - cost)
}, 0))

const totalSalesCount = computed(() => props.stats?.total_count ?? props.sales.length)

// Watch for filter changes and reset pagination
watch([filters], () => {
    currentPage.value = 1
}, { deep: true })

// Methods
const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString()
}

// Get settings from page props for currency formatting
const page = usePage()
const settings = computed(() => page?.props?.settings || {})

const formatCurrency = (amount) => {
    const useCurrency = settings.value?.currency || 'USD'
    const usePosition = settings.value?.currency_position || 'prefix'
    const formattedAmount = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: useCurrency
    }).format(amount || 0)
    
    // Handle currency position
    if (usePosition === 'suffix') {
        return formattedAmount.replace(/[A-Z]{3}/, '').trim() + ' ' + useCurrency
    }
    
    return formattedAmount
}

const getPaymentMethodStyle = (method) => {
    const colors = {
        cash: '#000000',
        card: '#000000',
        bank_transfer: '#000000',
        mobile: '#000000',
    }
    return { backgroundColor: colors[method] || '#000000', color: '#ffffff' }
}

const getPaymentMethodLabel = (method) => {
    const labels = {
        'cash': 'Cash',
        'card': 'Card',
        'bank_transfer': 'Bank Transfer',
        'mobile': 'Mobile'
    }
    return labels[method] || method
}

const getPaymentStatusStyle = (status) => {
    return { backgroundColor: '#000000', color: '#ffffff' }
}

const getPaymentStatusLabel = (status) => {
    const labels = {
        'paid': 'Paid',
        'pending': 'Pending',
        'failed': 'Failed'
    }
    return labels[status] || status
}

const exportSales = () => {
    const headers = ['Sale #', 'Date', 'Customer', 'Staff', 'Room', 'Items', 'Subtotal', 'Tax', 'Discount', 'Total', 'Payment Method', 'Payment Status']
    const rows = filteredSales.value.map(sale => [
        sale.sale_number || '',
        formatDate(sale.created_at),
        sale.is_walk_in ? 'Walk-In' : (sale.customer ? (sale.customer.first_name + ' ' + sale.customer.last_name).trim() : (sale.customer_name || 'Guest')),
        sale.user ? (sale.user.first_name + ' ' + sale.user.last_name).trim() : '—',
        sale.room?.room_number ? 'Room ' + sale.room.room_number : (sale.is_charged_to_room ? 'Room Charge' : '—'),
        sale.items?.length || 0,
        parseFloat(sale.subtotal) || 0,
        parseFloat(sale.tax_amount) || 0,
        parseFloat(sale.discount_amount) || 0,
        parseFloat(sale.total_amount) || 0,
        getPaymentMethodLabel(sale.payment_method),
        getPaymentStatusLabel(sale.payment_status)
    ])

    const csvContent = [headers, ...rows]
        .map(row => row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(','))
        .join('\n')

    const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' })
    const url  = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href     = url
    link.download = `sales-${new Date().toISOString().split('T')[0]}.csv`
    link.style.visibility = 'hidden'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(url)
}

const viewSale = (sale) => {
    router.visit(`/pos/sales/${sale.id}`)
}

const printSale = (sale) => {
    window.open(`/pos/sales/${sale.id}?print=1`, '_blank')
}

const openDatePicker = (inputName) => {
    if (inputName === 'dateFrom' && dateFromInput.value) {
        dateFromInput.value.showPicker?.()
    } else if (inputName === 'dateTo' && dateToInput.value) {
        dateToInput.value.showPicker?.()
    }
}
</script>

    
  
