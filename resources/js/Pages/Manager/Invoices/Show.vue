<template>
    <DashboardLayout title="Invoice Details" :user="user" :navigation="navigation">
        <div class="bg-white shadow rounded-lg p-6 mb-8" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Invoice {{ invoice.folio_number }}</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Issued {{ formatDate(invoice.folio_date) }}</p>
                </div>
                <Link :href="route('manager.invoices.index')" class="px-4 py-2 rounded-md font-medium text-white transition-colors"
                      :style="{ backgroundColor: themeColors.primary }">
                    Back to Invoices
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white shadow rounded-lg p-6" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                <p class="text-sm" :style="{ color: themeColors.textSecondary }">Customer</p>
                <p class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">
                    {{ invoice.reservation ? invoice.reservation.guest?.first_name + ' ' + invoice.reservation.guest?.last_name : invoice.customer_name }}
                </p>
                <p class="text-sm" :style="{ color: themeColors.textSecondary }">{{ invoice.customer_email || 'N/A' }}</p>
                <p class="text-sm mt-2" :style="{ color: themeColors.textSecondary }">
                    {{ invoice.reservation ? 'Room ' + invoice.reservation.room?.room_number : 'Outsider' }}
                </p>
            </div>
            <div class="bg-white shadow rounded-lg p-6" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                <p class="text-sm" :style="{ color: themeColors.textSecondary }">Total Amount</p>
                <p class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(invoice.total_amount || 0) }}</p>
                <p class="text-sm mt-2" :style="{ color: themeColors.textSecondary }">Paid {{ formatCurrency(invoice.paid_amount || 0) }}</p>
                <p class="text-sm" :style="{ color: themeColors.textSecondary }">Balance {{ formatCurrency(invoice.balance_amount || 0) }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-6" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                <p class="text-sm" :style="{ color: themeColors.textSecondary }">Status</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="getStatusColor(invoice.status)">
                    {{ formatStatus(invoice.status) }}
                </span>
                <p class="text-sm mt-4" :style="{ color: themeColors.textSecondary }">Notes</p>
                <p class="text-sm" :style="{ color: themeColors.textPrimary }">{{ invoice.notes || 'N/A' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white shadow rounded-lg p-6" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Charges</h3>
                <div v-if="!invoice.charges || invoice.charges.length === 0" class="text-sm" :style="{ color: themeColors.textSecondary }">No charges recorded.</div>
                <ul v-else class="space-y-3">
                    <li v-for="charge in invoice.charges" :key="charge.id" class="flex justify-between text-sm" :style="{ color: themeColors.textPrimary }">
                        <span>{{ charge.description }}</span>
                        <span>{{ formatCurrency(charge.total_amount || 0) }}</span>
                    </li>
                </ul>
            </div>

            <div class="bg-white shadow rounded-lg p-6 actions-section" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Actions</h3>
                <div class="space-y-3">
                    <button v-if="invoice.status !== 'paid'" 
                            @click="markAsPaid"
                            class="w-full px-4 py-2 rounded-md font-medium text-white transition-colors"
                            :style="{ backgroundColor: themeColors.success }">
                        Mark as Paid
                    </button>
                    <button @click="printInvoice" class="w-full px-4 py-2 rounded-md font-medium transition-colors"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                        Print Invoice
                    </button>
                    <button class="w-full px-4 py-2 rounded-md font-medium transition-colors"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                        Send Email
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<style>
@media print {
    /* Hide navigation and unnecessary elements */
    nav, header, .actions-section, button {
        display: none !important;
    }
    
    /* Optimize page layout for printing */
    body {
        background: white !important;
        color: black !important;
    }
    
    /* Ensure all content is visible */
    .bg-white, [style*="background-color"] {
        background: white !important;
        color: black !important;
        border: 1px solid #ccc !important;
    }
    
    /* Remove shadows and transitions */
    * {
        box-shadow: none !important;
        transition: none !important;
    }
    
    /* Ensure text is readable */
    [style*="color"] {
        color: black !important;
    }
    
    /* Page break for better printing */
    .print-break {
        page-break-before: always;
    }
    
    /* Ensure all cards are visible */
    .grid > div {
        background: white !important;
        border: 1px solid #ccc !important;
        margin-bottom: 1rem;
    }
}
</style>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme'
import { formatCurrency } from '@/Utils/currency'
import { computed } from 'vue'

const props = defineProps({
    user: Object,
    navigation: Array,
    invoice: Object
})

const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    primary: `var(--kotel-primary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
}))

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const formatStatus = (status) => {
    if (!status) return 'Unknown'
    return status.charAt(0).toUpperCase() + status.slice(1)
}

const getStatusColor = (status) => {
    switch (status) {
        case 'paid':
            return 'bg-green-100 text-green-800'
        case 'overdue':
            return 'bg-red-100 text-red-800'
        case 'sent':
            return 'bg-blue-100 text-blue-800'
        case 'open':
            return 'bg-yellow-100 text-yellow-800'
        default:
            return 'bg-gray-100 text-gray-800'
    }
}

const markAsPaid = () => {
    const form = useForm({})
    form.post(route('manager.invoices.markPaid', props.invoice.id), {
        onSuccess: () => {
            // Page will reload automatically
        }
    })
}

const printInvoice = () => {
    window.print()
}
</script>
