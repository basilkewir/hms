<template>
    <DashboardLayout title="Edit Quote" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Edit Quote #{{ quote.quote_number }}</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Update quote details and items.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('front-desk.quotes.show', quote.id)"
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{
                              backgroundColor: '#6b7280',
                          }"
                          @mouseenter="$event.target.style.backgroundColor = '#4b5563'"
                          @mouseleave="$event.target.style.backgroundColor = '#6b7280'">
                        <ArrowLeftIcon class="h-4 w-4 mr-2" />
                        Back to Quote
                    </Link>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="rounded-lg border p-6 shadow-sm"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
            <form @submit.prevent="submitQuote" class="space-y-6">
                <!-- Quote Type Selection -->
                <div>
                    <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                        Quote Type *
                    </label>
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input type="radio" v-model="form.quote_type" value="guest"
                                   class="mr-2" @change="switchQuoteType">
                            <span :style="{ color: themeColors.textPrimary }">Checked-in Guest</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" v-model="form.quote_type" value="outsider"
                                   class="mr-2" @change="switchQuoteType">
                            <span :style="{ color: themeColors.textPrimary }">Outsider</span>
                        </label>
                    </div>
                </div>

                <!-- Guest Selection (for checked-in guests) -->
                <div v-if="form.quote_type === 'guest'">
                    <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                        Select Checked-in Guest *
                    </label>
                    <select v-model="form.reservation_id" required
                            class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                        <option value="">Select checked-in guest</option>
                        <option v-for="reservation in reservations" :key="reservation.id" :value="reservation.id">
                            {{ reservation.guest_name }} • Room {{ reservation.room_number }} • Check-in: {{ reservation.check_out_date }}
                        </option>
                    </select>
                    <div v-if="errors.reservation_id" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                        {{ errors.reservation_id }}
                    </div>
                </div>

                <!-- Outsider Information -->
                <div v-if="form.quote_type === 'outsider'" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                                Customer Name *
                            </label>
                            <input v-model="form.customer_name" type="text" required
                                   class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                   placeholder="Enter customer name">
                            <div v-if="errors.customer_name" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                {{ errors.customer_name }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                                Email *
                            </label>
                            <input v-model="form.customer_email" type="email" required
                                   class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                   placeholder="customer@example.com">
                            <div v-if="errors.customer_email" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                {{ errors.customer_email }}
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                            Phone
                        </label>
                        <input v-model="form.customer_phone" type="text"
                               class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                               placeholder="Enter phone number">
                    </div>
                </div>

                <!-- Quote Details -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">
                            Valid Until *
                        </label>
                        <div class="relative">
                            <input
                                v-model="form.valid_until"
                                type="date"
                                required
                                :min="today"
                                class="w-full px-3 py-2 pr-10 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                placeholder="Select date" />
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-4 w-4" :style="{ color: themeColors.textSecondary }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <div v-if="errors.valid_until" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                            {{ errors.valid_until }}
                        </div>
                    </div>                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                            Status
                        </label>
                        <select v-model="form.status"
                                class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                            <option value="draft">Draft</option>
                            <option value="sent">Sent</option>
                            <option value="accepted">Accepted</option>
                            <option value="rejected">Rejected</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                            Tax (%)
                        </label>
                        <input v-model.number="form.tax_percentage" type="number" min="0" max="100" step="0.01"
                               class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                               @input="updateTotalAmount"
                               placeholder="0.00">
                    </div>
                </div>

                <!-- Quote Summary -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 rounded-md border"
                     :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                    <div>
                        <p class="text-xs" :style="{ color: themeColors.textSecondary }">Subtotal</p>
                        <p class="text-lg font-bold" :style="{ color: themeColors.textPrimary }">
                            {{ formatCurrency(subtotal) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs" :style="{ color: themeColors.textSecondary }">Tax Amount</p>
                        <p class="text-lg font-bold" :style="{ color: themeColors.textPrimary }">
                            {{ formatCurrency(taxAmount) }}
                        </p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xs" :style="{ color: themeColors.textSecondary }">Total Amount (Read-only)</p>
                        <p class="text-lg font-bold rounded px-2 py-1"
                           :style="{ color: 'white', backgroundColor: themeColors.success }">
                            {{ formatCurrency(form.total_amount) }}
                        </p>
                    </div>
                </div>

                <!-- Quote Items -->
                <div class="border-t pt-6" :style="{ borderColor: themeColors.border }">
                    <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Quote Items *</h3>

                    <div class="overflow-x-auto mb-4">
                        <table class="w-full text-sm">
                            <thead>
                                <tr :style="{ backgroundColor: themeColors.background }">
                                    <th class="px-3 py-2 text-left font-medium" :style="{ color: themeColors.textSecondary }">Description</th>
                                    <th class="px-3 py-2 text-left font-medium" :style="{ color: themeColors.textSecondary }">Qty</th>
                                    <th class="px-3 py-2 text-left font-medium" :style="{ color: themeColors.textSecondary }">Unit Price</th>
                                    <th class="px-3 py-2 text-left font-medium" :style="{ color: themeColors.textSecondary }">Total</th>
                                    <th class="px-3 py-2 text-center font-medium" :style="{ color: themeColors.textSecondary }">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y" :style="{ borderColor: themeColors.border }">
                                <tr v-for="(item, index) in form.items" :key="index">
                                    <td class="px-3 py-2">
                                        <input v-model="item.description" type="text"
                                               class="w-full px-2 py-1 border rounded text-sm"
                                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                               placeholder="Item description">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input v-model.number="item.quantity" type="number" min="1" step="0.01"
                                               class="w-full px-2 py-1 border rounded text-sm"
                                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                               @input="updateTotalAmount"
                                               placeholder="1">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input v-model.number="item.unit_price" type="number" min="0" step="0.01"
                                               class="w-full px-2 py-1 border rounded text-sm"
                                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                               @input="updateTotalAmount"
                                               placeholder="0.00">
                                    </td>
                                    <td class="px-3 py-2 text-right font-medium" :style="{ color: themeColors.textPrimary }">
                                        {{ formatCurrency(calculateItemTotal(item)) }}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <button type="button" @click="removeItem(index)"
                                                class="text-red-600 hover:text-red-800 font-medium">
                                            ✕
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <button type="button" @click="addItem"
                            class="px-4 py-2 rounded-md font-medium text-white flex items-center"
                            :style="{ backgroundColor: themeColors.primary }">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        + Add Item
                    </button>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                        Notes
                    </label>
                    <textarea v-model="form.notes" rows="4"
                              class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                              :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                              placeholder="Add any additional notes..."></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4 pt-4">
                    <button type="submit" :disabled="processing"
                            class="px-6 py-2 rounded-md font-medium text-white transition-opacity flex items-center"
                            :style="{ backgroundColor: themeColors.success, opacity: processing ? 0.5 : 1 }">
                        <CheckIcon class="h-4 w-4 mr-2" />
                        {{ processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                    <button type="button" @click="printQuote"
                            class="px-6 py-2 rounded-md font-medium text-white transition-opacity flex items-center"
                            :style="{ backgroundColor: '#0ea5e9' }">
                        🖨️ Print
                    </button>
                    <button type="button" @click="exportToPDF"
                            class="px-6 py-2 rounded-md font-medium text-white transition-opacity flex items-center"
                            :style="{ backgroundColor: '#f97316' }">
                        📄 Export PDF
                    </button>
                    <Link :href="route('front-desk.quotes.show', quote.id)"
                          class="px-6 py-2 rounded-md font-medium transition-colors flex items-center"
                          :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                        ✕ Cancel
                    </Link>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme.js'
import PDFExporter from '@/Utils/PDFExporter.js'
import {
    ArrowLeftIcon,
    PlusIcon,
    CheckIcon,
} from '@heroicons/vue/24/outline'

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
}))

loadTheme()

const today = computed(() => {
    return new Date().toISOString().split('T')[0]
})

const props = defineProps({
    user: Object,
    navigation: Array,
    quote: {
        type: Object,
        required: true
    },
    reservations: Array,
    errors: Object
})

const processing = ref(false)

const form = useForm({
    quote_type: props.quote.quote_type || 'outsider',
    reservation_id: props.quote.reservation_id || '',
    customer_name: props.quote.customer_name || '',
    customer_email: props.quote.customer_email || '',
    customer_phone: props.quote.customer_phone || '',
    valid_until: props.quote.valid_until || '',
    status: props.quote.status || 'draft',
    tax_percentage: props.quote.tax_percentage || 0,
    total_amount: props.quote.total_amount || 0,
    items: props.quote.items?.length ? props.quote.items.map(item => ({
        description: item.description,
        quantity: item.quantity,
        unit_price: item.unit_price
    })) : [{ description: '', quantity: 1, unit_price: 0 }],
    notes: props.quote.notes || '',
})

const errors = computed(() => props.errors || {})

const subtotal = computed(() => {
    return form.items.reduce((total, item) => total + calculateItemTotal(item), 0)
})

const taxAmount = computed(() => {
    return subtotal.value * (form.tax_percentage / 100)
})

const calculateItemTotal = (item) => {
    return (item.quantity || 0) * (item.unit_price || 0)
}

const updateTotalAmount = () => {
    const total = subtotal.value + taxAmount.value
    form.total_amount = parseFloat(total.toFixed(2))
}

const switchQuoteType = () => {
    if (form.quote_type === 'guest') {
        form.customer_name = ''
        form.customer_email = ''
        form.customer_phone = ''
    } else {
        form.reservation_id = ''
    }
}

const addItem = () => {
    form.items.push({
        description: '',
        quantity: 1,
        unit_price: 0
    })
}

const removeItem = (index) => {
    form.items.splice(index, 1)
    updateTotalAmount()
}

const generateQuoteHTML = () => {
    const customerName = form.quote_type === 'guest' ? 'Guest' : form.customer_name

    const itemsHTML = form.items
        .filter(item => item.description && item.quantity && item.unit_price)
        .map(item => `
            <tr>
                <td>${item.description}</td>
                <td style="text-align: center;">${item.quantity}</td>
                <td style="text-align: right;">$${parseFloat(item.unit_price).toFixed(2)}</td>
                <td style="text-align: right;">$${(item.quantity * item.unit_price).toFixed(2)}</td>
            </tr>
    `).join('')

    return `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Quote</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { margin: 0; color: #333; }
                .quote-details { margin-bottom: 20px; }
                .quote-details p { margin: 5px 0; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th { background-color: #f5f5f5; border: 1px solid #ddd; padding: 10px; text-align: left; font-weight: bold; }
                td { border: 1px solid #ddd; padding: 8px; }
                .totals { margin-top: 20px; text-align: right; }
                .total-row { font-size: 16px; font-weight: bold; margin-top: 10px; }
                .notes { margin-top: 30px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>QUOTE</h1>
                <p style="color: #999;">Hotel Management System</p>
            </div>

            <div class="quote-details">
                <p><strong>Customer:</strong> ${customerName}</p>
                <p><strong>Email:</strong> ${form.customer_email || 'N/A'}</p>
                <p><strong>Phone:</strong> ${form.customer_phone || 'N/A'}</p>
                <p><strong>Valid Until:</strong> ${form.valid_until || 'N/A'}</p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="text-align: center;">Quantity</th>
                        <th style="text-align: right;">Unit Price</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${itemsHTML}
                </tbody>
            </table>

            <div class="totals">
                <div>Subtotal: <strong>$${subtotal.value.toFixed(2)}</strong></div>
                <div>Tax (${form.tax_percentage}%): <strong>$${taxAmount.value.toFixed(2)}</strong></div>
                <div class="total-row">Total: $${form.total_amount ? parseFloat(form.total_amount).toFixed(2) : '0.00'}</div>
            </div>

            ${form.notes ? `<div class="notes"><strong>Notes:</strong><p>${form.notes}</p></div>` : ''}
        </body>
        </html>
    `
}

const printQuote = () => {
    const html = PDFExporter.generateQuotePDF({
        quote_number: props.quote.quote_number || 'N/A',
        customer_name: form.quote_type === 'guest' ? 'Guest' : form.customer_name,
        customer_email: form.customer_email,
        customer_phone: form.customer_phone,
        valid_until: form.valid_until,
        items: form.items.filter(item => item.description && item.quantity && item.unit_price),
        subtotal: subtotal.value,
        tax_amount: taxAmount.value,
        total_amount: form.total_amount,
        tax_percentage: form.tax_percentage,
        notes: form.notes,
        created_at: props.quote.created_at || new Date().toISOString()
    })
    const printWindow = window.open('', '', 'width=900,height=600')
    printWindow.document.write(html)
    printWindow.document.close()
    setTimeout(() => {
        printWindow.print()
    }, 250)
}

const exportToPDF = async () => {
    try {
        if (typeof html2pdf !== 'undefined') {
            const html = PDFExporter.generateQuotePDF({
                quote_number: props.quote.quote_number || 'N/A',
                customer_name: form.quote_type === 'guest' ? 'Guest' : form.customer_name,
                customer_email: form.customer_email,
                customer_phone: form.customer_phone,
                valid_until: form.valid_until,
                items: form.items.filter(item => item.description && item.quantity && item.unit_price),
                subtotal: subtotal.value,
                tax_amount: taxAmount.value,
                total_amount: form.total_amount,
                tax_percentage: form.tax_percentage,
                notes: form.notes,
                created_at: props.quote.created_at || new Date().toISOString()
            })
            const element = document.createElement('div')
            element.innerHTML = html
            const opt = {
                margin: 10,
                filename: `quote-${props.quote.quote_number || 'draft'}-${new Date().toISOString().split('T')[0]}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { orientation: 'portrait', unit: 'mm', format: 'a4' }
            }
            html2pdf().set(opt).from(html).save()
        } else {
            alert('PDF export requires html2pdf library. Using print dialog instead.')
            printQuote()
        }
    } catch (error) {
        console.error('Error exporting to PDF:', error)
        alert('Error exporting to PDF. Please try again.')
    }
}

const submitQuote = () => {
    if (!form.valid_until) {
        console.warn('Valid until date is required')
        return
    }
    if (!form.total_amount || parseFloat(form.total_amount) <= 0) {
        console.warn('Total amount must be greater than 0')
        return
    }
    if (form.quote_type === 'guest' && !form.reservation_id) {
        console.warn('Reservation must be selected for guest quotes')
        return
    }
    if (form.quote_type === 'outsider' && !form.customer_name) {
        console.warn('Customer name is required for outsider quotes')
        return
    }

    processing.value = true

    form.put(route('front-desk.quotes.update', props.quote.id), {
        onSuccess: () => {
            processing.value = false
        },
        onError: (error) => {
            processing.value = false
            console.error('Error updating quote:', error)
        }
    })
}
</script>
