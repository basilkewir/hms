<template>
    <DashboardLayout title="Create Invoice" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Create Invoice</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Create invoice for checked-in guests or outsiders with full CRUD functionality.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('manager.invoices.index')" 
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ 
                              backgroundColor: '#6b7280',
                          }"
                          @mouseenter="$event.target.style.backgroundColor = '#4b5563'"
                          @mouseleave="$event.target.style.backgroundColor = '#6b7280'">
                        <ArrowLeftIcon class="h-4 w-4 mr-2" />
                        Back to Invoices
                    </Link>
                </div>
            </div>

            <!-- Form -->
            <div class="rounded-lg border p-6 shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <form @submit.prevent="submitInvoice" class="space-y-6">
                    <!-- Invoice Type Selection -->
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                            Invoice Type *
                        </label>
                        <div class="flex gap-4">
                            <label class="flex items-center">
                                <input type="radio" v-model="form.invoice_type" value="guest" 
                                       class="mr-2" @change="switchInvoiceType">
                                <span :style="{ color: themeColors.textPrimary }">Checked-in Guest</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" v-model="form.invoice_type" value="outsider" 
                                       class="mr-2" @change="switchInvoiceType">
                                <span :style="{ color: themeColors.textPrimary }">Outsider</span>
                            </label>
                        </div>
                    </div>

                    <!-- Guest Selection (for checked-in guests) -->
                    <div v-if="form.invoice_type === 'guest'">
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
                    <div v-if="form.invoice_type === 'outsider'" class="space-y-4">
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
                                    Email
                                </label>
                                <input v-model="form.customer_email" type="email"
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

                        <!-- Invoice Items -->
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <label class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                    Invoice Items *
                                </label>
                                <button type="button" @click="addItem"
                                        class="px-3 py-1 rounded-md text-sm font-medium transition-colors"
                                        :style="{ backgroundColor: themeColors.primary, color: 'white' }"
                                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                        @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                                    + Add Item
                                </button>
                            </div>
                            
                            <div class="space-y-3">
                                <div v-for="(item, index) in form.items" :key="index" 
                                     class="p-4 border rounded-lg"
                                     :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">
                                                Description *
                                            </label>
                                            <input v-model="item.description" type="text" required
                                                   class="w-full px-2 py-1 border rounded text-sm focus:outline-none"
                                                   :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                                   placeholder="Item description">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">
                                                Quantity *
                                            </label>
                                            <input v-model.number="item.quantity" type="number" min="1" required
                                                   class="w-full px-2 py-1 border rounded text-sm focus:outline-none"
                                                   :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                                   placeholder="1">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">
                                                Unit Price *
                                            </label>
                                            <input v-model.number="item.unit_price" type="number" min="0" step="0.01" required
                                                   class="w-full px-2 py-1 border rounded text-sm focus:outline-none"
                                                   :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                                   placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between mt-3">
                                        <div class="text-sm" :style="{ color: themeColors.textSecondary }">
                                            Item Total: <span class="font-medium" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(calculateItemTotal(item)) }}</span>
                                        </div>
                                        <button v-if="form.items.length > 1" type="button" @click="removeItem(index)"
                                                class="px-2 py-1 rounded text-sm transition-colors"
                                                :style="{ backgroundColor: themeColors.danger, color: 'white' }"
                                                @mouseenter="$event.target.style.backgroundColor = '#dc2626'"
                                                @mouseleave="$event.target.style.backgroundColor = themeColors.danger">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 p-3 border rounded-lg"
                                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">Subtotal:</span>
                                    <span class="text-lg font-bold" :style="{ color: themeColors.primary }">{{ formatCurrency(calculateSubtotal()) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                            Notes
                        </label>
                        <textarea v-model="form.notes" rows="4" 
                                  class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                  :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                  placeholder="Additional notes for this invoice..."></textarea>
                        <div v-if="errors.notes" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                            {{ errors.notes }}
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" :disabled="processing"
                                class="px-4 py-2 rounded-md font-medium text-white transition-colors disabled:opacity-50"
                                :style="{ backgroundColor: themeColors.primary }">
                            <span v-if="processing">Creating...</span>
                            <span v-else>Create Invoice</span>
                        </button>
                        <button type="button" @click="$inertia.visit(route('manager.invoices.index'))"
                                class="px-4 py-2 rounded-md font-medium transition-colors"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme.js'
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

const props = defineProps({
    user: Object,
    navigation: Array,
    reservations: Array,
    errors: Object
})

const form = useForm({
    invoice_type: 'guest',
    reservation_id: '',
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    notes: '',
    items: [
        { description: '', quantity: 1, unit_price: 0 }
    ]
})

const processing = ref(false)

const switchInvoiceType = () => {
    // Clear form fields when switching type
    form.reservation_id = ''
    form.customer_name = ''
    form.customer_email = ''
    form.customer_phone = ''
    form.items = [{ description: '', quantity: 1, unit_price: 0 }]
}

const addItem = () => {
    form.items.push({ description: '', quantity: 1, unit_price: 0 })
}

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1)
    }
}

const calculateItemTotal = (item) => {
    return (item.quantity || 0) * (item.unit_price || 0)
}

const calculateSubtotal = () => {
    return form.items.reduce((total, item) => total + calculateItemTotal(item), 0)
}

const submitInvoice = () => {
    console.log('Submit invoice called', {
        invoice_type: form.invoice_type,
        customer_name: form.customer_name,
        items: form.items,
        processing: processing.value
    })
    
    processing.value = true
    
    // Validate based on invoice type
    if (form.invoice_type === 'guest' && !form.reservation_id) {
        console.log('Guest validation failed - no reservation_id')
        processing.value = false
        return
    }
    if (form.invoice_type === 'outsider') {
        if (!form.customer_name) {
            console.log('Outsider validation failed - no customer_name')
            processing.value = false
            return
        }
        // Validate items for outsider invoices
        const validItems = form.items.filter(item => item.description && item.quantity > 0 && item.unit_price > 0)
        if (validItems.length === 0) {
            console.log('Outsider validation failed - no valid items', { items: form.items, validItems })
            processing.value = false
            return
        }
        
        // Update form items to only include valid items
        form.items = validItems
    } else {
        // For guest invoices, remove customer fields and items from submission
        const submissionData = {
            invoice_type: form.invoice_type,
            reservation_id: form.reservation_id,
            notes: form.notes
        }
        
        console.log('Validation passed, submitting guest invoice...')
        form.transform(() => submissionData).post(route('manager.invoices.store'), {
            onSuccess: () => {
                console.log('Form submission successful')
                processing.value = false
            },
            onError: (errors) => {
                console.log('Form submission failed', errors)
                processing.value = false
            }
        })
        return
    }
    
    console.log('Validation passed, submitting form...')
    form.post(route('manager.invoices.store'), {
        onSuccess: () => {
            console.log('Form submission successful')
            processing.value = false
        },
        onError: (errors) => {
            console.log('Form submission failed', errors)
            processing.value = false
        }
    })
}
</script>
