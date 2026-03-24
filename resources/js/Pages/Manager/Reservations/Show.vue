<template>
    <DashboardLayout title="Reservation Details" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-8" :style="{ backgroundColor: themeColors.card }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Reservation #{{ reservation.reservation_number }}</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Status: 
                        <span class="px-2 py-1 text-xs rounded-full" :style="getStatusStyle(reservation.status)">
                            {{ formatStatus(reservation.status) }}
                        </span>
                    </p>
                </div>
                <div class="flex space-x-3">
                    <button v-if="reservation.status === 'pending' || reservation.status === 'confirmed'" 
                            @click="confirmReservation"
                            class="text-white px-4 py-2 rounded-md font-medium"
                            :style="{ backgroundColor: themeColors.success }">
                        Confirm
                    </button>
                    <button @click="sendConfirmationEmail"
                            class="text-white px-4 py-2 rounded-md font-medium"
                            :style="{ backgroundColor: themeColors.primary }">
                        Send Email
                    </button>
                    <Link :href="route('manager.reservations.edit', reservation.id)" 
                          class="text-white px-4 py-2 rounded-md font-medium"
                          :style="{ backgroundColor: themeColors.warning }">
                        Edit
                    </Link>
                                        <Link v-if="reservation.status === 'checked_in'"
                                                    :href="route('manager.reservations.edit', reservation.id)" 
                                                    class="text-white px-4 py-2 rounded-md font-medium"
                                                    :style="{ backgroundColor: themeColors.secondary }">
                                                Extend Stay
                                        </Link>
                    <Link :href="route('manager.reservations.index')" 
                          class="text-white px-4 py-2 rounded-md font-medium"
                          :style="{ backgroundColor: themeColors.secondary }">
                        Back
                    </Link>
                </div>
            </div>

            <!-- Guest Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="rounded-lg p-4" :style="{ backgroundColor: themeColors.background }">
                    <h3 class="font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Guest Information</h3>
                    <div class="space-y-2 text-sm">
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Name:</span> {{ reservation.guest?.full_name }}</div>
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Email:</span> {{ reservation.guest?.email }}</div>
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Phone:</span> {{ reservation.guest?.phone }}</div>
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Nationality:</span> {{ reservation.guest?.nationality }}</div>
                    </div>
                </div>
                <div class="rounded-lg p-4" :style="{ backgroundColor: themeColors.background }">
                    <h3 class="font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Booking Information</h3>
                    <div class="space-y-2 text-sm">
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Booking Source:</span> {{ formatBookingSource(reservation.booking_source) }}</div>
                        <div v-if="reservation.booking_reference" :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Reference:</span> {{ reservation.booking_reference }}</div>
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Check-in:</span> {{ formatDate(reservation.check_in_date) }}</div>
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Check-out:</span> {{ formatDate(reservation.check_out_date) }}</div>
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Nights:</span> {{ reservation.nights }}</div>
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Guests:</span> {{ reservation.number_of_adults }} adults, {{ reservation.number_of_children }} children</div>
                    </div>
                </div>
            </div>

            <!-- Room Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="rounded-lg p-4" :style="{ backgroundColor: themeColors.background }">
                    <h3 class="font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Room Information</h3>
                    <div class="space-y-2 text-sm">
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Room Type:</span> {{ reservation.room_type?.name }}</div>
                        <div v-if="reservation.room" :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Room Number:</span> {{ reservation.room.room_number }}</div>
                        <div v-else :style="{ color: themeColors.warning }">Room not yet assigned</div>
                    </div>
                </div>
                <div class="rounded-lg p-4" :style="{ backgroundColor: themeColors.background }">
                    <h3 class="font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Pricing</h3>
                    <div class="space-y-2 text-sm">
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Room Rate:</span> {{ formatCurrency(reservation.room_rate) }}/night</div>
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Total Room Charges:</span> {{ formatCurrency(reservation.total_room_charges) }}</div>
                        <div v-if="reservation.discount_amount > 0" :style="{ color: themeColors.textSecondary }">
                            <span class="font-medium" :style="{ color: themeColors.textPrimary }">Discount:</span> -{{ formatCurrency(reservation.discount_amount) }}
                            <span v-if="reservation.discount_reason"> ({{ reservation.discount_reason }})</span>
                        </div>
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Taxes:</span> {{ formatCurrency(reservation.taxes) }}</div>
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Service Charges:</span> {{ formatCurrency(reservation.service_charges) }}</div>
                        <div v-if="(reservation.pos_charges || 0) > 0" :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">POS / Restaurant Charges:</span> {{ formatCurrency(reservation.pos_charges) }}</div>
                        <div class="pt-2 border-t" :style="{ borderColor: themeColors.border }">
                            <span class="font-bold" :style="{ color: themeColors.textPrimary }">Total Amount:</span> <span :style="{ color: themeColors.textPrimary }">{{ formatCurrency(reservation.total_amount) }}</span>
                        </div>
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Paid:</span> {{ formatCurrency(reservation.paid_amount) }}</div>
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Balance:</span> {{ formatCurrency(reservation.balance_amount) }}</div>
                    </div>
                </div>
            </div>

            <!-- Check-in/Check-out Information -->
            <div v-if="reservation.actual_check_in || reservation.actual_check_out || reservation.checked_in_by || reservation.checked_out_by || reservation.status === 'checked_in' || reservation.status === 'checked_out'" class="mb-6">
                <h3 class="font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Check-in/Check-out Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Check-in Information -->
                    <div v-if="reservation.status === 'checked_in' || reservation.actual_check_in || reservation.checked_in_by"
                         class="rounded-lg p-4"
                         :style="{ backgroundColor: 'rgba(34,197,94,0.1)', borderLeft: '4px solid var(--kotel-success)' }">
                        <div class="text-sm">
                            <div class="font-medium" :style="{ color: themeColors.success }">Checked In</div>
                            <div v-if="reservation.actual_check_in" :style="{ color: themeColors.success }" class="mt-1">
                                {{ formatDateTime(reservation.actual_check_in) }}
                            </div>
                            <div v-else-if="reservation.status === 'checked_in'" class="mt-1 text-xs" :style="{ color: themeColors.textTertiary }">
                                Check-in time not recorded
                            </div>
                            <div v-if="reservation.checked_in_by" class="mt-2" :style="{ color: themeColors.textSecondary }">
                                <span class="font-medium">Checked in by:</span> {{ reservation.checked_in_by.name || (reservation.checked_in_by.first_name + ' ' + reservation.checked_in_by.last_name) }}
                            </div>
                            <div v-else-if="reservation.status === 'checked_in'" class="mt-2 text-xs" :style="{ color: themeColors.textTertiary }">
                                Check-in staff information not available
                            </div>
                        </div>
                    </div>
                    <!-- Check-out Information -->
                    <div v-if="reservation.status === 'checked_out' || reservation.actual_check_out || reservation.checked_out_by"
                         class="rounded-lg p-4"
                         :style="{ backgroundColor: 'rgba(59,130,246,0.1)', borderLeft: '4px solid var(--kotel-primary)' }">
                        <div class="text-sm">
                            <div class="font-medium" :style="{ color: themeColors.primary }">Checked Out</div>
                            <div v-if="reservation.actual_check_out" :style="{ color: themeColors.primary }" class="mt-1">
                                {{ formatDateTime(reservation.actual_check_out) }}
                            </div>
                            <div v-else-if="reservation.status === 'checked_out'" class="mt-1 text-xs" :style="{ color: themeColors.textTertiary }">
                                Check-out time not recorded
                            </div>
                            <div v-if="reservation.checked_out_by" class="mt-2" :style="{ color: themeColors.textSecondary }">
                                <span class="font-medium">Checked out by:</span> {{ reservation.checked_out_by.name || (reservation.checked_out_by.first_name + ' ' + reservation.checked_out_by.last_name) }}
                            </div>
                            <div v-else-if="reservation.status === 'checked_out'" class="mt-2 text-xs" :style="{ color: themeColors.textTertiary }">
                                Check-out staff information not available
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Special Requests -->
            <div v-if="reservation.special_requests" class="mb-6">
                <h3 class="font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Special Requests</h3>
                <p class="rounded-lg p-4" :style="{ backgroundColor: themeColors.background, color: themeColors.textSecondary }">{{ reservation.special_requests }}</p>
            </div>

            <!-- Group Booking Info -->
            <div v-if="reservation.is_group_booking && reservation.group_booking" class="mb-6">
                <h3 class="font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Group Booking</h3>
                <div class="rounded-lg p-4" :style="{ backgroundColor: 'rgba(139,92,246,0.1)', borderLeft: '4px solid #8b5cf6' }">
                    <div class="text-sm">
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Group:</span> {{ reservation.group_booking.group_name }}</div>
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Group Number:</span> {{ reservation.group_booking.group_number }}</div>
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Billing Type:</span> {{ formatBillingType(reservation.billing_type) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Room-posted Service and POS Charges -->
        <div v-if="reservation.service_charge_items && reservation.service_charge_items.length > 0"
             class="shadow rounded-lg p-6 mt-6"
             :style="{ backgroundColor: themeColors.card }">
            <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Room-Posted Charges</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b" :style="{ borderColor: themeColors.border }">
                            <th class="text-left py-2 pr-4 font-medium" :style="{ color: themeColors.textSecondary }">Date</th>
                            <th class="text-left py-2 pr-4 font-medium" :style="{ color: themeColors.textSecondary }">Type</th>
                            <th class="text-left py-2 pr-4 font-medium" :style="{ color: themeColors.textSecondary }">Description</th>
                            <th class="text-left py-2 pr-4 font-medium" :style="{ color: themeColors.textSecondary }">Department</th>
                            <th class="text-right py-2 pr-4 font-medium" :style="{ color: themeColors.textSecondary }">Qty</th>
                            <th class="text-right py-2 pr-4 font-medium" :style="{ color: themeColors.textSecondary }">Unit Price</th>
                            <th class="text-right py-2 font-medium" :style="{ color: themeColors.textSecondary }">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in reservation.service_charge_items" :key="item.id"
                            class="border-b" :style="{ borderColor: themeColors.border }">
                            <td class="py-2 pr-4" :style="{ color: themeColors.textSecondary }">{{ item.charge_date }}</td>
                            <td class="py-2 pr-4" :style="{ color: themeColors.textSecondary }">{{ item.charge_type }}</td>
                            <td class="py-2 pr-4" :style="{ color: themeColors.textPrimary }">{{ item.description }}</td>
                            <td class="py-2 pr-4" :style="{ color: themeColors.textSecondary }">{{ item.department || '—' }}</td>
                            <td class="py-2 pr-4 text-right" :style="{ color: themeColors.textPrimary }">{{ item.quantity }}</td>
                            <td class="py-2 pr-4 text-right" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(item.unit_price) }}</td>
                            <td class="py-2 text-right font-medium" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(item.net_amount) }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" class="pt-3 text-right font-semibold" :style="{ color: themeColors.textSecondary }">Total Additional Room Charges:</td>
                            <td class="pt-3 text-right font-bold" :style="{ color: themeColors.primary }">{{ formatCurrency(reservation.additional_room_charges || reservation.service_charges) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Add Service Charge (only visible while guest is checked in) -->
        <div v-if="reservation.status === 'checked_in'" class="shadow rounded-lg p-6 mt-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.primary }">Add Service Charge</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-xs mb-1" :style="{ color: themeColors.textSecondary }">Description</label>
                    <input v-model="serviceChargeForm.description"
                           type="text"
                           class="w-full rounded-md px-3 py-2 text-sm border focus:outline-none focus:ring-2"
                           :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"
                           placeholder="e.g. Laundry service, Room service">
                </div>
                <div>
                    <label class="block text-xs mb-1" :style="{ color: themeColors.textSecondary }">Amount</label>
                    <input v-model.number="serviceChargeForm.amount"
                           type="number" min="0.01" step="0.01"
                           class="w-full rounded-md px-3 py-2 text-sm border focus:outline-none focus:ring-2"
                           :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"
                           placeholder="0.00">
                </div>
                <div>
                    <label class="block text-xs mb-1" :style="{ color: themeColors.textSecondary }">Quantity</label>
                    <input v-model.number="serviceChargeForm.quantity"
                           type="number" min="1" step="1"
                           class="w-full rounded-md px-3 py-2 text-sm border focus:outline-none focus:ring-2"
                           :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                </div>
            </div>
            <div class="mt-3 flex items-center justify-end">
                <button type="button"
                        @click="addServiceCharge"
                        :disabled="isAddingServiceCharge"
                        class="px-4 py-2 rounded-md text-sm font-medium text-white transition-colors"
                        :style="{ backgroundColor: themeColors.primary }">
                    <span v-if="isAddingServiceCharge">Adding...</span>
                    <span v-else>Add Charge</span>
                </button>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, onMounted, watch, ref } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { formatCurrency as formatCurrencyUtil, initializeCurrencySettings, setCurrentCurrency, setCurrencyPosition } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme.js'

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
    hover: `rgba(255, 255, 255, 0.1)`
}))
loadTheme()

const props = defineProps({
    user: Object,
    reservation: Object,
})

const navigation = computed(() => {
    // Determine role from user's roles
    const userRoles = props.user?.roles || []
    
    // Check if user has manager role (but not admin, as admin should use admin nav)
    const hasManagerRole = userRoles.some(role => role.name === 'manager')
    const hasAdminRole = userRoles.some(role => role.name === 'admin')
    
    // If user is manager (and not admin), use manager navigation
    if (hasManagerRole && !hasAdminRole) {
        return getNavigationForRole('manager')
    }
    
    // For manager routes, always use manager navigation
    // This ensures manager routes show manager sidebar even if user has admin role
    return getNavigationForRole('manager')
})

// Get settings from page props for currency formatting
const page = usePage()
const settings = computed(() => page.props.settings || {})

// Watch for settings changes and update currency
watch(settings, (newSettings) => {
    if (newSettings.currency) {
        setCurrentCurrency(newSettings.currency)
    }
    if (newSettings.currency_position) {
        setCurrencyPosition(newSettings.currency_position)
    }
}, { immediate: true, deep: true })

// Format currency with current settings
const formatCurrency = (amount) => {
    const currency = settings.value?.currency || 'USD'
    const position = settings.value?.currency_position || 'prefix'
    return formatCurrencyUtil(amount, currency, position)
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const formatDateTime = (dateString) => {
    return new Date(dateString).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const formatStatus = (status) => {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatBookingSource = (source) => {
    const sources = {
        'walk_in': 'Walk-in',
        'phone': 'Phone',
        'email': 'Email',
        'website': 'Website',
        'booking_com': 'Booking.com',
        'expedia': 'Expedia',
        'agoda': 'Agoda',
        'travel_agent': 'Travel Agent',
        'corporate': 'Corporate',
    }
    return sources[source] || source
}

const formatBillingType = (type) => {
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getStatusStyle = (status) => {
    const statusMap = {
        pending:    { backgroundColor: `var(--kotel-warning)`,   color: 'white' },
        confirmed:  { backgroundColor: `var(--kotel-primary)`,   color: 'white' },
        checked_in: { backgroundColor: `var(--kotel-success)`,   color: 'white' },
        checked_out:{ backgroundColor: `var(--kotel-secondary)`, color: 'white' },
        cancelled:  { backgroundColor: `var(--kotel-danger)`,    color: 'white' },
        no_show:    { backgroundColor: `var(--kotel-danger)`,    color: 'white' },
        modified:   { backgroundColor: `var(--kotel-warning)`,   color: 'white' },
    }
    return statusMap[status] || { backgroundColor: `var(--kotel-secondary)`, color: 'white' }
}

const confirmReservation = () => {
    router.post(route('manager.reservations.confirm', props.reservation.id))
}

const sendConfirmationEmail = () => {
    router.post(route('manager.reservations.send-confirmation', props.reservation.id))
}

const serviceChargeForm = ref({ description: '', amount: null, quantity: 1 })
const isAddingServiceCharge = ref(false)

const addServiceCharge = () => {
    if (!serviceChargeForm.value.description || !serviceChargeForm.value.amount || serviceChargeForm.value.amount <= 0) return
    isAddingServiceCharge.value = true
    router.post(route('manager.checkout.service-charge'), {
        reservation_id: props.reservation.id,
        description: serviceChargeForm.value.description,
        amount: parseFloat(serviceChargeForm.value.amount),
        quantity: parseInt(serviceChargeForm.value.quantity || 1, 10),
    }, {
        onSuccess: () => {
            serviceChargeForm.value = { description: '', amount: null, quantity: 1 }
            isAddingServiceCharge.value = false
        },
        onError: () => { isAddingServiceCharge.value = false },
    })
}

// Initialize currency settings on component mount
onMounted(() => {
    initializeCurrencySettings()
    // Also set from computed settings
    if (settings.value.currency) {
        setCurrentCurrency(settings.value.currency)
    }
    if (settings.value.currency_position) {
        setCurrencyPosition(settings.value.currency_position)
    }
})
</script>
