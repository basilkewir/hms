<template>
    <DashboardLayout title="Create Quote" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Create Quote</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Create quote for customers with full CRUD functionality.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('front-desk.quotes.index')"
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{
                              backgroundColor: '#6b7280',
                          }"
                          @mouseenter="$event.target.style.backgroundColor = '#4b5563'"
                          @mouseleave="$event.target.style.backgroundColor = '#6b7280'">
                        <ArrowLeftIcon class="h-4 w-4 mr-2" />
                        Back to Quotes
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
                            {{ reservation.guest_name }} • Room {{ reservation.room_number }} • Check-in: {{ reservation.check_in_date }}
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
                        <input v-model="form.customer_phone" type="tel"
                               class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                               placeholder="+1234567890">
                        <div v-if="errors.customer_phone" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                            {{ errors.customer_phone }}
                        </div>
                    </div>
                </div>

                <!-- Quote Details -->
                <div class="space-y-4">
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
                        </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                                    Subtotal (Auto-calculated)
                                </label>
                                <div class="px-3 py-2 border rounded-md text-sm font-semibold"
                                     :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                                    ${{ subtotal.toFixed(2) }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                                    Tax (%)
                                </label>
                                <input v-model.number="form.tax_percentage" type="number" step="0.01" min="0" max="100"
                                       class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                       :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                       placeholder="0.00"
                                       @input="updateTotalAmount">
                                <p class="mt-1 text-xs" :style="{ color: themeColors.textSecondary }">
                                    Tax Amount: ${{ taxAmount.toFixed(2) }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                                    Total Amount (Auto-calculated) *
                                </label>
                                <div class="px-3 py-2 border rounded-md text-sm font-bold"
                                     :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.success }">
                                    ${{ form.total_amount ? parseFloat(form.total_amount).toFixed(2) : '0.00' }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                                    Status
                                </label>
                                <select v-model="form.status"
                                        class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                        :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                                    <option value="draft">Draft</option>
                                    <option value="sent">Sent</option>
                                </select>
                            </div>
                        </div>

                        <!-- Quote Items -->
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                                Quote Items
                            </label>
                            <div v-for="(item, index) in form.items" :key="index" class="space-y-2 mb-4 p-4 border rounded-md"
                                 :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-2">
                                    <input v-model="item.description" type="text" placeholder="Item description"
                                           class="px-3 py-2 border rounded-md text-sm focus:outline-none"
                                           :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                                    <input v-model.number="item.quantity" type="number" min="1" placeholder="Qty"
                                           class="px-3 py-2 border rounded-md text-sm focus:outline-none"
                                           :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                           @input="updateTotalAmount">
                                    <input v-model.number="item.unit_price" type="number" step="0.01" min="0" placeholder="Unit price"
                                           class="px-3 py-2 border rounded-md text-sm focus:outline-none"
                                           :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                           @input="updateTotalAmount">
                                    <div class="px-3 py-2 border rounded-md text-sm font-semibold"
                                         :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                                        ${{ calculateItemTotal(item).toFixed(2) }}
                                    </div>
                                    <button type="button" @click="removeItem(index)"
                                            class="px-3 py-2 rounded-md text-sm font-medium text-white"
                                            :style="{ backgroundColor: themeColors.danger }">
                                        Remove
                                    </button>
                                </div>
                            </div>
                            <button type="button" @click="addItem"
                                    class="px-4 py-2 rounded-md text-sm font-medium text-white"
                                    :style="{ backgroundColor: themeColors.primary }">
                                + Add Item
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                            Notes
                        </label>
                        <textarea v-model="form.notes" rows="4"
                                  class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                  :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                  placeholder="Additional notes for this quote..."></textarea>
                        <div v-if="errors.notes" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                            {{ errors.notes }}
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" :disabled="processing"
                                class="px-4 py-2 rounded-md font-medium text-white transition-colors disabled:opacity-50"
                                :style="{ backgroundColor: themeColors.primary }">
                            <span v-if="processing">Creating...</span>
                            <span v-else>Create Quote</span>
                        </button>
                        <button type="button" @click="previewQuote"
                                class="px-4 py-2 rounded-md font-medium text-white transition-colors"
                                :style="{ backgroundColor: themeColors.warning }">
                            👁 Preview
                        </button>
                        <button type="button" @click="printQuote"
                                class="px-4 py-2 rounded-md font-medium text-white transition-colors"
                                :style="{ backgroundColor: themeColors.secondary }">
                            🖨️ Print
                        </button>
                        <button type="button" @click="exportToPDF"
                                class="px-4 py-2 rounded-md font-medium text-white transition-colors"
                                :style="{ backgroundColor: '#d97706' }">
                            📄 Export PDF
                        </button>
                        <button type="button" @click="$inertia.visit(route('front-desk.quotes.index'))"
                                class="px-4 py-2 rounded-md font-medium transition-colors"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'
import PDFExporter from '@/Utils/PDFExporter.js'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

// Initialize theme
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
    hover: 'rgba(255, 255, 255, 0.1)'
}))

// Load theme on mount
loadTheme()

// Get today's date for date picker validation
const today = computed(() => {
    return new Date().toISOString().split('T')[0]
})

const props = defineProps({
    user: Object,
    navigation: Array,
    reservations: Array,
    errors: Object
})

const form = useForm({
    quote_type: 'guest',
    reservation_id: null,
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    valid_until: '',
    total_amount: '',
    tax_percentage: 0,
    status: 'draft',
    notes: '',
    items: [
        { description: '', quantity: 1, unit_price: 0 }
    ]
})

const processing = ref(false)
const validUntilInput = ref(null)

// Computed subtotal
const subtotal = computed(() => {
    return form.items.reduce((total, item) => total + calculateItemTotal(item), 0)
})

// Computed tax amount
const taxAmount = computed(() => {
    return subtotal.value * (form.tax_percentage / 100)
})

const switchQuoteType = () => {
    // Clear form fields when switching type
    form.reservation_id = ''
    form.customer_name = ''
    form.customer_email = ''
    form.customer_phone = ''
    form.items = [{ description: '', quantity: 1, unit_price: 0 }]
    updateTotalAmount()
}

const addItem = () => {
    form.items.push({ description: '', quantity: 1, unit_price: 0 })
}

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1)
        updateTotalAmount()
    }
}

const calculateItemTotal = (item) => {
    return (item.quantity || 0) * (item.unit_price || 0)
}

const updateTotalAmount = () => {
    const total = subtotal.value + taxAmount.value
    form.total_amount = parseFloat(total.toFixed(2))
}

const openDatePicker = () => {
    // Focus the input to trigger native date picker
    if (validUntilInput.value) {
        validUntilInput.value.focus()
        // Try to use showPicker() if available, but wrap in try-catch as it requires user gesture
        try {
            if (typeof validUntilInput.value.showPicker === 'function') {
                validUntilInput.value.showPicker()
            }
        } catch (e) {
            // showPicker() requires user gesture, just focus is enough for native picker
            console.debug('Date picker focused, native picker should appear on interaction')
        }
    }
}

const generateQuoteHTML = () => {
    const customerName = form.quote_type === 'guest'
        ? (form.customer_name || 'Guest')
        : form.customer_name

    const itemsHTML = form.items.map(item => `
        <tr>
            <td style="border: 1px solid #ddd; padding: 8px;">${item.description || ''}</td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">${item.quantity || 0}</td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">$${(item.unit_price || 0).toFixed(2)}</td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">$${calculateItemTotal(item).toFixed(2)}</td>
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

const previewQuote = () => {
    const html = generateQuoteHTML()
    const printWindow = window.open('', '', 'width=900,height=600')
    printWindow.document.write(html)
    printWindow.document.close()
}

const printQuote = () => {
    const html = PDFExporter.generateQuotePDF({
        quote_number: 'Draft',
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
        created_at: new Date().toISOString()
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
        // Check if html2pdf library is available
        if (typeof html2pdf !== 'undefined') {
            const html = PDFExporter.generateQuotePDF({
                quote_number: 'Draft',
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
                created_at: new Date().toISOString()
            })
            const element = document.createElement('div')
            element.innerHTML = html
            const opt = {
                margin: 10,
                filename: `quote-draft-${new Date().toISOString().split('T')[0]}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { orientation: 'portrait', unit: 'mm', format: 'a4' }
            }
            html2pdf().set(opt).from(html).save()
        } else {
            // Fallback: generate printable version
            alert('PDF export requires html2pdf library. Using print dialog instead.')
            printQuote()
        }
    } catch (error) {
        console.error('Error exporting to PDF:', error)
        alert('Error exporting to PDF. Please try again.')
    }
}

const submitQuote = () => {
    // Validate required fields
    if (!form.valid_until) {
        console.warn('Valid until date is required')
        return
    }
    if (!form.total_amount || parseFloat(form.total_amount) <= 0) {
        console.warn('Total amount must be greater than 0')
        return
    }

    // Validate based on quote type
    if (form.quote_type === 'guest' && !form.reservation_id) {
        console.warn('Reservation must be selected for guest quotes')
        return
    }
    if (form.quote_type === 'outsider' && !form.customer_name) {
        console.warn('Customer name is required for outsider quotes')
        return
    }

    processing.value = true
    console.log('Submitting quote form:', form)

    form.post(route('front-desk.quotes.store'), {
        onSuccess: () => {
            processing.value = false
            console.log('Quote created successfully')
        },
        onError: (error) => {
            processing.value = false
            console.error('Error creating quote:', error)
        }
    })
}
</script>
