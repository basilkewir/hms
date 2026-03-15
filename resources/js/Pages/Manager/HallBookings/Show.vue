<template>
    <DashboardLayout title="Hall Booking Details" :user="user" :navigation="navigation">
        <!-- Flash message -->
        <div v-if="$page.props.flash?.success"
             class="mb-4 p-4 rounded-lg text-sm font-medium"
             :style="{ backgroundColor: 'rgba(34,197,94,0.12)', color: themeColors.success, border: '1px solid', borderColor: themeColors.success }">
            {{ $page.props.flash.success }}
        </div>

        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">
                        {{ booking.booking_number }}
                    </h1>
                    <p class="text-sm mt-1" :style="{ color: themeColors.textSecondary }">
                        Hall Booking · Created {{ formatDate(booking.created_at) }}
                    </p>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="px-3 py-1 text-sm font-semibold rounded-full" :style="statusStyle(booking.status)">
                        {{ formatStatus(booking.status) }}
                    </span>
                    <Link :href="route(`${routePrefix}.hall-bookings.edit`, booking.id)"
                          class="px-4 py-2 rounded-md text-sm font-medium transition-colors"
                          :style="{ backgroundColor: themeColors.warning, color: '#fff' }">
                        Edit
                    </Link>
                    <button @click="confirmDelete"
                            class="px-4 py-2 rounded-md text-sm font-medium transition-colors"
                            :style="{ backgroundColor: themeColors.danger, color: '#fff' }">
                        Delete
                    </button>
                    <Link :href="route(`${routePrefix}.hall-bookings.index`)"
                          class="px-4 py-2 rounded-md text-sm font-medium transition-colors"
                          :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }">
                        Back
                    </Link>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Booking Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Event Info -->
                <div class="rounded-lg border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                    <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                        <h3 class="text-base font-semibold" :style="{ color: themeColors.textPrimary }">Event Information</h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <p class="text-xs uppercase tracking-wide" :style="{ color: themeColors.textTertiary }">Hall</p>
                            <p class="font-medium mt-1" :style="{ color: themeColors.textPrimary }">
                                {{ booking.hall?.name || 'N/A' }}
                                <span v-if="booking.hall?.code" class="text-xs ml-1" :style="{ color: themeColors.textTertiary }">({{ booking.hall.code }})</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide" :style="{ color: themeColors.textTertiary }">Event Date</p>
                            <p class="font-medium mt-1" :style="{ color: themeColors.textPrimary }">{{ formatDate(booking.event_date) }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide" :style="{ color: themeColors.textTertiary }">Time</p>
                            <p class="font-medium mt-1" :style="{ color: themeColors.textPrimary }">{{ booking.start_time }} – {{ booking.end_time }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide" :style="{ color: themeColors.textTertiary }">Attendees</p>
                            <p class="font-medium mt-1" :style="{ color: themeColors.textPrimary }">{{ booking.attendees }}</p>
                        </div>
                        <div v-if="booking.notes" class="md:col-span-2">
                            <p class="text-xs uppercase tracking-wide" :style="{ color: themeColors.textTertiary }">Notes</p>
                            <p class="mt-1 text-sm" :style="{ color: themeColors.textSecondary }">{{ booking.notes }}</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="rounded-lg border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                    <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                        <h3 class="text-base font-semibold" :style="{ color: themeColors.textPrimary }">Contact Information</h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <p class="text-xs uppercase tracking-wide" :style="{ color: themeColors.textTertiary }">Contact Name</p>
                            <p class="font-medium mt-1" :style="{ color: themeColors.textPrimary }">{{ booking.contact_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide" :style="{ color: themeColors.textTertiary }">Email</p>
                            <p class="font-medium mt-1" :style="{ color: themeColors.textPrimary }">{{ booking.contact_email || '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide" :style="{ color: themeColors.textTertiary }">Phone</p>
                            <p class="font-medium mt-1" :style="{ color: themeColors.textPrimary }">{{ booking.contact_phone || '—' }}</p>
                        </div>
                        <div v-if="booking.guest">
                            <p class="text-xs uppercase tracking-wide" :style="{ color: themeColors.textTertiary }">Linked Guest</p>
                            <p class="font-medium mt-1" :style="{ color: themeColors.textPrimary }">
                                {{ booking.guest.first_name }} {{ booking.guest.last_name }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Financial Summary + Payment -->
            <div class="space-y-6">
                <!-- Financial Summary -->
                <div class="rounded-lg border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                    <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                        <h3 class="text-base font-semibold" :style="{ color: themeColors.textPrimary }">Payment Summary</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm" :style="{ color: themeColors.textSecondary }">Total Amount</span>
                            <span class="font-semibold" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(booking.total_amount) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm" :style="{ color: themeColors.textSecondary }">Paid</span>
                            <span class="font-semibold" :style="{ color: themeColors.success }">{{ formatCurrency(booking.paid_amount) }}</span>
                        </div>
                        <div class="border-t pt-4" :style="{ borderColor: themeColors.border }">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Balance Due</span>
                                <span class="text-lg font-bold" :style="{ color: balanceDue > 0 ? themeColors.danger : themeColors.success }">
                                    {{ formatCurrency(balanceDue) }}
                                </span>
                            </div>
                        </div>
                        <!-- Progress bar -->
                        <div class="w-full rounded-full h-2" :style="{ backgroundColor: themeColors.border }">
                            <div class="h-2 rounded-full transition-all"
                                 :style="{ width: paymentProgress + '%', backgroundColor: themeColors.success }"></div>
                        </div>
                        <p class="text-xs text-center" :style="{ color: themeColors.textTertiary }">
                            {{ paymentProgress }}% paid
                        </p>
                    </div>
                </div>

                <!-- Record Payment -->
                <div v-if="booking.status !== 'cancelled' && booking.status !== 'completed'"
                     class="rounded-lg border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                    <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                        <h3 class="text-base font-semibold" :style="{ color: themeColors.textPrimary }">Record Payment</h3>
                    </div>
                    <form @submit.prevent="submitPayment" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Amount *</label>
                            <input v-model.number="paymentForm.amount"
                                   type="number" min="0.01" step="0.01"
                                   :placeholder="balanceDue > 0 ? balanceDue.toFixed(2) : '0.00'"
                                   required
                                   class="w-full rounded-md px-3 py-2 text-sm focus:outline-none"
                                   :style="inputStyle" />
                            <p v-if="paymentErrors.amount" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ paymentErrors.amount }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Payment Method *</label>
                            <select v-model="paymentForm.payment_method" required
                                    class="w-full rounded-md px-3 py-2 text-sm focus:outline-none"
                                    :style="inputStyle">
                                <option value="">Select method</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                            </select>
                            <p v-if="paymentErrors.payment_method" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ paymentErrors.payment_method }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Notes</label>
                            <textarea v-model="paymentForm.notes" rows="2"
                                      class="w-full rounded-md px-3 py-2 text-sm focus:outline-none"
                                      :style="inputStyle"></textarea>
                        </div>
                        <!-- Quick-fill balance button -->
                        <button v-if="balanceDue > 0"
                                type="button"
                                @click="paymentForm.amount = balanceDue"
                                class="text-xs underline"
                                :style="{ color: themeColors.primary }">
                            Pay full balance ({{ formatCurrency(balanceDue) }})
                        </button>
                        <button type="submit"
                                :disabled="paymentSubmitting"
                                class="w-full py-2 rounded-md text-sm font-medium text-white transition-colors disabled:opacity-50"
                                :style="{ backgroundColor: themeColors.success }">
                            {{ paymentSubmitting ? 'Processing…' : 'Record Payment' }}
                        </button>
                    </form>
                </div>

                <!-- Fully paid badge -->
                <div v-else-if="balanceDue <= 0 && booking.status !== 'cancelled'"
                     class="rounded-lg border p-4 text-center"
                     :style="{ backgroundColor: 'rgba(34,197,94,0.08)', borderColor: themeColors.success }">
                    <p class="font-semibold text-sm" :style="{ color: themeColors.success }">Fully Paid</p>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'
import { formatCurrency } from '@/Utils/currency.js'

const props = defineProps({
    user:         Object,
    booking:      Object,
    routePrefix:  { type: String, default: 'admin' },
})

const { currentTheme } = useTheme()
const navigation = computed(() => getNavigationForRole('admin'))

const themeColors = computed(() => ({
    background:    `var(--kotel-background)`,
    card:          `var(--kotel-card)`,
    border:        `var(--kotel-border)`,
    textPrimary:   `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary:  `var(--kotel-text-tertiary)`,
    primary:       `var(--kotel-primary)`,
    secondary:     `var(--kotel-secondary)`,
    success:       `var(--kotel-success)`,
    warning:       `var(--kotel-warning)`,
    danger:        `var(--kotel-danger)`,
    hover:         currentTheme.value.theme_mode === 'dark' ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.02)',
}))

const inputStyle = computed(() => ({
    backgroundColor: themeColors.value.background,
    borderColor:     themeColors.value.border,
    color:           themeColors.value.textPrimary,
    borderWidth:     '1px',
    borderStyle:     'solid',
}))

const balanceDue = computed(() =>
    Math.max(0, (Number(props.booking.total_amount) || 0) - (Number(props.booking.paid_amount) || 0))
)

const paymentProgress = computed(() => {
    const total = Number(props.booking.total_amount) || 0
    if (total <= 0) return 100
    return Math.min(100, Math.round((Number(props.booking.paid_amount) / total) * 100))
})

const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '—'

const formatStatus = (s) => (s || '').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())

const statusStyle = (status) => {
    const map = {
        pending:   { backgroundColor: 'rgba(250,204,21,0.15)', color: themeColors.value.warning },
        confirmed: { backgroundColor: 'rgba(59,130,246,0.15)',  color: themeColors.value.primary },
        completed: { backgroundColor: 'rgba(34,197,94,0.15)',   color: themeColors.value.success },
        cancelled: { backgroundColor: 'rgba(239,68,68,0.15)',   color: themeColors.value.danger },
    }
    return map[status] || { backgroundColor: themeColors.value.background, color: themeColors.value.textSecondary }
}

const paymentForm = reactive({ amount: '', payment_method: '', notes: '' })
const paymentErrors = ref({})
const paymentSubmitting = ref(false)

const submitPayment = () => {
    paymentErrors.value = {}
    paymentSubmitting.value = true
    router.post(route(`${props.routePrefix}.hall-bookings.process-payment`, props.booking.id), paymentForm, {
        onSuccess: () => {
            paymentForm.amount = ''
            paymentForm.payment_method = ''
            paymentForm.notes = ''
            paymentSubmitting.value = false
        },
        onError: (errors) => {
            paymentErrors.value = errors
            paymentSubmitting.value = false
        },
    })
}

const confirmDelete = () => {
    if (confirm(`Delete booking ${props.booking.booking_number}? This cannot be undone.`)) {
        router.delete(route(`${props.routePrefix}.hall-bookings.destroy`, props.booking.id))
    }
}
</script>
