<template>
    <DashboardLayout title="Invoice Details" :user="user" :navigation="navigation">
        <div class="space-y-6">
            <!-- Header -->
            <div :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }"
                 class="rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Invoice {{ invoice.folio_number }}</h1>
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">Issued {{ formatDate(invoice.folio_date) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-sm font-medium"
                              :style="getStatusStyle(invoice.status)">
                            {{ invoice.status }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Invoice Information -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Customer Information -->
                <div class="bg-white shadow rounded-lg p-6" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                    <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Customer Information</h3>
                    <div class="space-y-2">
                        <div>
                            <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Name</p>
                            <p class="text-sm" :style="{ color: themeColors.textPrimary }">{{ invoice.customer_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Email</p>
                            <p class="text-sm" :style="{ color: themeColors.textPrimary }">{{ invoice.customer_email || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Phone</p>
                            <p class="text-sm" :style="{ color: themeColors.textPrimary }">{{ invoice.customer_phone || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Type</p>
                            <p class="text-sm" :style="{ color: themeColors.textPrimary }">{{ invoice.invoice_type }}</p>
                        </div>
                    </div>
                </div>

                <!-- Room Information (if guest invoice) -->
                <div v-if="invoice.room" class="bg-white shadow rounded-lg p-6" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                    <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Room Information</h3>
                    <div class="space-y-2">
                        <div>
                            <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Room Number</p>
                            <p class="text-sm" :style="{ color: themeColors.textPrimary }">{{ invoice.room_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Reservation</p>
                            <p class="text-sm" :style="{ color: themeColors.textPrimary }">{{ invoice.reservation?.id || 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="bg-white shadow rounded-lg p-6" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                    <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Financial Summary</h3>
                    <div class="space-y-2">
                        <div>
                            <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Total Amount</p>
                            <p class="text-lg font-bold" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(invoice.total_amount) }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Paid Amount</p>
                            <p class="text-sm" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(invoice.paid_amount) }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Balance</p>
                            <p class="text-lg font-bold" :style="{ color: getBalanceColor(invoice.balance_amount) }">
                                {{ formatCurrency(invoice.balance_amount) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charges -->
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

            <!-- Actions -->
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
import PDFExporter from '@/Utils/PDFExporter.js'
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

const markAsPaid = () => {
    const form = useForm({})
    form.post(route('front-desk.invoices.markPaid', props.invoice.id), {
        onSuccess: () => {
            // Page will reload automatically
        }
    })
}

const printInvoice = () => {
    const html = PDFExporter.generateInvoicePDF({
        invoice_number: props.invoice.folio_number || 'N/A',
        customer_name: props.invoice.customer_name,
        customer_email: props.invoice.customer_email,
        customer_phone: props.invoice.customer_phone,
        due_date: props.invoice.due_date,
        items: props.invoice.charges?.length ? props.invoice.charges.map(charge => ({
            description: charge.description,
            quantity: 1,
            unit_price: charge.total_amount || 0
        })) : [],
        subtotal: props.invoice.subtotal || props.invoice.total_amount,
        tax_amount: 0,
        total_amount: props.invoice.total_amount,
        tax_percentage: 0,
        balance_due: props.invoice.balance_amount,
        status: props.invoice.status,
        notes: props.invoice.notes || '',
        created_at: props.invoice.folio_date || new Date().toISOString()
    })
    const printWindow = window.open('', '', 'width=900,height=600')
    printWindow.document.write(html)
    printWindow.document.close()
    setTimeout(() => {
        printWindow.print()
    }, 250)
}

const getStatusStyle = (status) => {
    const styles = {
        open: { backgroundColor: '#3b82f6', color: 'white' },
        paid: { backgroundColor: '#10b981', color: 'white' },
        overdue: { backgroundColor: '#ef4444', color: 'white' }
    }
    return styles[status] || { backgroundColor: '#6b7280', color: 'white' }
}

const getBalanceColor = (balance) => {
    return balance > 0 ? '#ef4444' : '#10b981'
}
</script>
