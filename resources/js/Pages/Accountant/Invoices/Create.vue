<template>
    <DashboardLayout title="Create Invoice" :user="user">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Create Invoice</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Create invoice for checked-in guests or outsiders.</p>
                </div>
                <Link :href="route('accountant.invoices.index')" 
                      class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                      :style="{ 
                          backgroundColor: themeColors.secondary,
                      }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                    Back to Invoices
                </Link>
            </div>
        </div>

        <div class="rounded-lg shadow p-6"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <form @submit.prevent="submitInvoice" class="space-y-6">
                <!-- Invoice Type Selection -->
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textPrimary }">Invoice Type *</label>
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
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textPrimary }">Select Checked-in Guest *</label>
                    <select v-model="form.reservation_id" required 
                            class="w-full rounded-md focus:outline-none transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary
                            }">
                        <option value="">Select checked-in guest</option>
                        <option v-for="reservation in reservations" :key="reservation.id" :value="reservation.id">
                            {{ reservation.guest_name }} • Room {{ reservation.room_number }} • Check-in: {{ reservation.check_in_date }}
                        </option>
                    </select>
                </div>

                <!-- Outsider Information -->
                <div v-if="form.invoice_type === 'outsider'" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textPrimary }">Customer Name *</label>
                            <input v-model="form.customer_name" type="text" required
                                   class="w-full rounded-md focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }"
                                   placeholder="Enter customer name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textPrimary }">Email</label>
                            <input v-model="form.customer_email" type="email"
                                   class="w-full rounded-md focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }"
                                   placeholder="customer@example.com">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textPrimary }">Phone</label>
                        <input v-model="form.customer_phone" type="tel"
                               class="w-full rounded-md focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary
                               }"
                               placeholder="+1234567890">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-lg p-4"
                         :style="{ backgroundColor: themeColors.background }">
                        <p class="text-xs mb-1" :style="{ color: themeColors.textTertiary }">Total Amount</p>
                        <p class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(selectedReservation?.total_amount || 0) }}</p>
                    </div>
                    <div class="rounded-lg p-4"
                         :style="{ backgroundColor: themeColors.background }">
                        <p class="text-xs mb-1" :style="{ color: themeColors.textTertiary }">Due Date</p>
                        <p class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">{{ selectedReservation?.check_out_date || 'N/A' }}</p>
                    </div>
                    <div class="rounded-lg p-4"
                         :style="{ backgroundColor: themeColors.background }">
                        <p class="text-xs mb-1" :style="{ color: themeColors.textTertiary }">Guest</p>
                        <p class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">{{ selectedReservation?.guest_name || 'N/A' }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textPrimary }">Notes</label>
                    <textarea v-model="form.notes" rows="4" 
                              class="w-full rounded-md focus:outline-none transition-colors"
                              :style="{ 
                                  backgroundColor: themeColors.background,
                                  borderColor: themeColors.border,
                                  color: themeColors.textPrimary
                              }"
                              placeholder="Optional notes for the invoice"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" 
                            class="px-6 py-2 rounded-md transition-colors font-medium text-white"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        Create Invoice
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'

// Initialize theme
const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    primary: '#3b82f6',
    secondary: '#6b7280',
    success: '#10b981',
    danger: '#ef4444',
    warning: '#f59e0b',
    textTertiary: '#9ca3af',
    hover: '#2563eb'
}))

const props = defineProps({
    user: Object,
    reservations: Array,
    errors: Object
})

const form = useForm({
    invoice_type: 'guest',
    reservation_id: '',
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    notes: ''
})

const selectedReservation = computed(() => {
    return props.reservations.find(r => r.id === form.reservation_id)
})

const switchInvoiceType = () => {
    // Clear form fields when switching type
    form.reservation_id = ''
    form.customer_name = ''
    form.customer_email = ''
    form.customer_phone = ''
}

const submitInvoice = () => {
    // Validate based on invoice type
    if (form.invoice_type === 'guest' && !form.reservation_id) {
        return
    }
    if (form.invoice_type === 'outsider' && !form.customer_name) {
        return
    }
    
    form.post(route('accountant.invoices.store'))
}
</script>
