<template>
    <DashboardLayout title="Purchase Orders" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Purchase Orders</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Manage purchase orders, suppliers, and receiving.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="router.visit(route('manager.purchases.create'))"
                            class="px-4 py-2 rounded-md transition-colors font-medium flex items-center opacity-100 hover:opacity-80"
                            style="background-color: var(--kotel-primary); color: #000000;">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Create Purchase Order
                    </button>
                    <button @click="exportPurchases"
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

        <!-- Purchase Order Stats Cards -->
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
                        <DocumentTextIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Orders</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ purchaseStats.total }}</p>
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
                        <CheckCircleIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Received</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ purchaseStats.received }}</p>
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
                        <ClockIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Pending</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ purchaseStats.pending }}</p>
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
                         :style="{ backgroundColor: 'rgba(139, 92, 246, 0.1)' }">
                        <TruckIcon class="h-6 w-6" :style="{ color: '#8b5cf6' }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">In Transit</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ purchaseStats.inTransit }}</p>
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
                         :style="{ backgroundColor: 'rgba(251, 146, 60, 0.1)' }">
                        <CalendarDaysIcon class="h-6 w-6" :style="{ color: '#fb923c' }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">This Month</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ purchaseStats.thisMonth }}</p>
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
                        <CurrencyDollarIcon class="h-6 w-6" :style="{ color: themeColors.danger }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Value</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ formatCurrency(purchaseStats.totalValue) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Purchase Orders Table -->
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
                    :style="{ color: themeColors.textPrimary }">Recent Purchase Orders</h3>
            </div>

            <!-- Table Content -->
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                PO Number
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Supplier
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Total
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Paid
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="po in purchaseOrders.data" :key="po.id"
                            class="transition-colors"
                            :style="{
                                borderBottomStyle: 'solid',
                                borderBottomWidth: '1px',
                                borderColor: themeColors.border
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ po.po_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium"
                                     :style="{ color: themeColors.textPrimary }">{{ po.supplier.name }}</div>
                                <div class="text-sm"
                                     :style="{ color: themeColors.textSecondary }">{{ po.supplier.contact_person || 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textPrimary }">{{ formatDate(po.order_date) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium"
                                     :style="{ color: themeColors.textPrimary }">
                                    {{ formatCurrency(po.total_amount) }}
                                    <span class="text-xs text-gray-500">({{ po.total_amount }})</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium"
                                     :style="{ color: po.paid_amount >= po.total_amount ? themeColors.success : (po.paid_amount > 0 ? themeColors.warning : themeColors.textSecondary) }">
                                    {{ formatCurrency(po.paid_amount) }}
                                    <span class="text-xs text-gray-500">({{ po.paid_amount }})</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                      :class="getStatusClass(po.status)"
                                      :style="getStatusStyle(po.status)">
                                    {{ po.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center gap-2">
                                    <button @click="viewPurchaseOrder(po)"
                                            class="text-indigo-600 hover:text-indigo-900 font-medium"
                                            :style="{ color: themeColors.primary }">
                                        View
                                    </button>
                                    <button v-if="canEdit(po)" @click="editPurchaseOrder(po)"
                                            class="text-green-600 hover:text-green-900 font-medium"
                                            :style="{ color: themeColors.success }">
                                        Edit
                                    </button>
                                    <button v-if="canReceive(po)" @click="receivePurchaseOrder(po)"
                                            class="text-blue-600 hover:text-blue-900 font-medium"
                                            :style="{ color: '#3b82f6' }">
                                        Receive
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t"
                 :style="{
                     borderColor: themeColors.border,
                     borderTopWidth: '1px'
                 }">
                <Pagination :data="purchaseOrders" />
            </div>
        </div>

        <!-- Create Purchase Order Modal -->
        <DialogModal :show="showCreateModal" @close="showCreateModal = false" max-width="2xl">
            <template #title>Create Purchase Order</template>
            <template #content>
                <div class="space-y-4">
                    <p class="text-gray-600">Create purchase order form will go here.</p>
                </div>
            </template>
            <template #footer>
                <button @click="showCreateModal = false" class="px-4 py-2 border border-gray-300 rounded-md">Cancel</button>
            </template>
        </DialogModal>

        </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DialogModal from '@/Components/DialogModal.vue'
import Pagination from '@/Components/Pagination.vue'
import { router } from '@inertiajs/vue3'
import { formatCurrency } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme.js'
import {
    DocumentArrowDownIcon,
    DocumentTextIcon,
    CheckCircleIcon,
    ClockIcon,
    TruckIcon,
    CalendarDaysIcon,
    CurrencyDollarIcon,
    PlusIcon
} from '@heroicons/vue/24/outline'

// Initialize theme
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
    hover: `var(--kotel-hover)`
}))

const props = defineProps({
    user: Object,
    navigation: Array,
    purchaseOrders: Object,
    suppliers: Array,
    products: Array
})

// State
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showViewModal = ref(false)

// Computed Properties
const purchaseStats = computed(() => ({
    total: props.purchaseOrders?.total || 0,
    received: props.purchaseOrders?.data?.filter(po => po.status === 'received').length || 0,
    pending: props.purchaseOrders?.data?.filter(po => po.status === 'pending').length || 0,
    inTransit: props.purchaseOrders?.data?.filter(po => po.status === 'in_transit').length || 0,
    thisMonth: props.purchaseOrders?.data?.filter(po => {
        const orderDate = new Date(po.order_date)
        const now = new Date()
        return orderDate.getMonth() === now.getMonth() && orderDate.getFullYear() === now.getFullYear()
    }).length || 0,
    totalValue: props.purchaseOrders?.data?.reduce((sum, po) => sum + (po.total_amount || 0), 0) || 0
}))

// Methods
const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const getStatusClass = (status) => {
    const statusClasses = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'in_transit': 'bg-blue-100 text-blue-800',
        'received': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    }
    return statusClasses[status] || 'bg-gray-100 text-gray-800'
}

const getStatusStyle = (status) => {
    const statusStyles = {
        'pending': { backgroundColor: 'rgba(250, 204, 21, 0.1)', color: '#ca8a04' },
        'in_transit': { backgroundColor: 'rgba(59, 130, 246, 0.1)', color: '#1d4ed8' },
        'received': { backgroundColor: 'rgba(34, 197, 94, 0.1)', color: '#16a34a' },
        'cancelled': { backgroundColor: 'rgba(239, 68, 68, 0.1)', color: '#dc2626' }
    }
    return statusStyles[status] || { backgroundColor: 'rgba(107, 114, 128, 0.1)', color: '#6b7280' }
}

const canEdit = (po) => {
    return po.status === 'pending' || po.status === 'in_transit'
}

const canReceive = (po) => {
    return po.status === 'in_transit'
}

const viewPurchaseOrder = (po) => {
    router.visit(route('manager.purchases.show', po.id))
}

const editPurchaseOrder = (po) => {
    router.visit(route('manager.purchases.edit', po.id))
}

const receivePurchaseOrder = (po) => {
    // Implementation for receiving purchase order
    console.log('Receive purchase order:', po)
}

const exportPurchases = () => {
    // Implementation for exporting purchases
    console.log('Export purchases')
}
</script>

<style scoped>
/* Custom animations and transitions */
.transition-colors {
    transition-property: background-color, border-color, color;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Hover effects for interactive elements */
button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

button:active {
    transform: translateY(0);
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

/* Status badge improvements */
.rounded-full {
    border-radius: 9999px;
}

.inline-flex {
    display: inline-flex;
}

/* Card shadow improvements */
.shadow {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

.shadow-sm {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.rounded-lg {
    border-radius: 0.5rem;
}
</style>
