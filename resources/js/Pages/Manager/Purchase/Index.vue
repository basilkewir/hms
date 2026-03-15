<template>
    <DashboardLayout title="Purchase Management" :user="user">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Purchase Management</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Manage hotel purchases, suppliers, and inventory orders.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('manager.purchases.create')"
                          class="px-4 py-2 rounded-md font-medium text-white flex items-center transition-colors"
                          :style="{ backgroundColor: themeColors.primary }">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        New Purchase
                    </Link>
                    <Link :href="route('manager.suppliers.index')"
                          class="px-4 py-2 rounded-md font-medium text-white flex items-center transition-colors"
                          style="background-color: #8b5cf6">
                        <BuildingStorefrontIcon class="h-4 w-4 mr-2" />
                        Suppliers
                    </Link>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         style="background-color: rgba(59,130,246,0.1)">
                        <ShoppingCartIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Total Orders</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ purchaseOrders?.total || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         style="background-color: rgba(250,204,21,0.1)">
                        <ClockIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Pending</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ pendingCount }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         style="background-color: rgba(34,197,94,0.1)">
                        <CheckCircleIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Received</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ receivedCount }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         style="background-color: rgba(139,92,246,0.1)">
                        <CurrencyDollarIcon class="h-6 w-6" style="color: #8b5cf6" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Total Amount</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(totalAmount) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-lg overflow-hidden shadow"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Purchase Orders</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Order #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Paid</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in purchaseOrders?.data" :key="order.id"
                            class="transition-colors"
                            :style="{ borderBottomWidth: '1px', borderBottomStyle: 'solid', borderColor: themeColors.border }"
                            @mouseenter="e => e.currentTarget.style.backgroundColor = themeColors.hover"
                            @mouseleave="e => e.currentTarget.style.backgroundColor = 'transparent'">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ order.order_number || order.purchase_number || '#' + order.id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                    {{ order.supplier?.name || '—' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textSecondary }">
                                {{ formatDate(order.purchase_date || order.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(order.total_amount || 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.success }">
                                {{ formatCurrency(order.paid_amount || 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusClass(order.status)">
                                    {{ formatStatus(order.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link :href="route('manager.purchases.show', order.id)"
                                      class="mr-3 transition-colors" :style="{ color: themeColors.primary }">View</Link>
                                <Link v-if="['pending','in_transit'].includes(order.status)"
                                      :href="route('manager.purchases.edit', order.id)"
                                      class="transition-colors" :style="{ color: themeColors.success }">Edit</Link>
                            </td>
                        </tr>
                        <tr v-if="!purchaseOrders?.data?.length">
                            <td colspan="7" class="px-6 py-12 text-center">
                                <ShoppingCartIcon class="mx-auto h-12 w-12 mb-3" :style="{ color: themeColors.textTertiary }" />
                                <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">No purchase orders found</p>
                                <p class="text-sm mt-1" :style="{ color: themeColors.textSecondary }">Create your first purchase order to get started.</p>
                                <Link :href="route('manager.purchases.create')"
                                      class="inline-flex items-center mt-4 px-4 py-2 rounded-md text-sm font-medium text-white"
                                      :style="{ backgroundColor: themeColors.primary }">
                                    <PlusIcon class="h-4 w-4 mr-2" /> New Purchase
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div v-if="purchaseOrders?.links" class="px-6 py-4 border-t" :style="{ borderColor: themeColors.border }">
                <Pagination :links="purchaseOrders.links" />
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { formatCurrency } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme.js'
import { PlusIcon, ShoppingCartIcon, ClockIcon, CheckCircleIcon, CurrencyDollarIcon, BuildingStorefrontIcon } from '@heroicons/vue/24/outline'

const { loadTheme } = useTheme()
loadTheme()

const themeColors = computed(() => ({
    background:    'var(--kotel-background)',
    card:          'var(--kotel-card)',
    border:        'var(--kotel-border)',
    textPrimary:   'var(--kotel-text-primary)',
    textSecondary: 'var(--kotel-text-secondary)',
    textTertiary:  'var(--kotel-text-tertiary)',
    primary:       'var(--kotel-primary)',
    success:       'var(--kotel-success)',
    warning:       'var(--kotel-warning)',
    danger:        'var(--kotel-danger)',
    hover:         'rgba(255,255,255,0.05)',
}))

const props = defineProps({
    user:           Object,
    purchaseOrders: Object,
    suppliers:      Array,
    products:       Array,
})

const pendingCount  = computed(() => props.purchaseOrders?.data?.filter(o => ['pending','in_transit'].includes(o.status)).length ?? 0)
const receivedCount = computed(() => props.purchaseOrders?.data?.filter(o => o.status === 'received').length ?? 0)
const totalAmount   = computed(() => props.purchaseOrders?.data?.reduce((s, o) => s + (parseFloat(o.total_amount) || 0), 0) ?? 0)

const formatDate = (d) => d ? new Date(d).toLocaleDateString() : '—'

const formatStatus = (s) => (s || 'pending').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())

const getStatusClass = (status) => ({
    pending:    'bg-yellow-100 text-yellow-800',
    in_transit: 'bg-blue-100 text-blue-800',
    received:   'bg-green-100 text-green-800',
    cancelled:  'bg-red-100 text-red-800',
    partial:    'bg-orange-100 text-orange-800',
}[status] || 'bg-gray-100 text-gray-800')
</script>
