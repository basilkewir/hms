<template>
    <DashboardLayout title="Reservation Details" :user="user" :navigation="navigation">
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold" style="color: #111827;">Reservation #{{ reservation.reservation_number }}</h1>
                    <p class="mt-2" style="color: #4b5563;">Status: 
                        <span class="px-2 py-1 text-xs rounded-full" :style="getStatusStyle(reservation.status)">
                            {{ formatStatus(reservation.status) }}
                        </span>
                    </p>
                </div>
                <div class="flex space-x-3">
                    <button v-if="reservation.status === 'pending' || reservation.status === 'confirmed'" 
                            @click="confirmReservation"
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                        Confirm
                    </button>
                    <button @click="sendConfirmationEmail"
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        Send Email
                    </button>
                    <Link :href="route('manager.reservations.edit', reservation.id)" 
                          class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700">
                        Edit
                    </Link>
                    <Link :href="route('manager.reservations.index')" 
                          class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                        Back
                    </Link>
                </div>
            </div>

            <!-- Guest Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="rounded-lg p-4" style="background-color: #f9fafb;">
                    <h3 class="font-semibold mb-3" style="color: #111827;">Guest Information</h3>
                    <div class="space-y-2 text-sm">
                        <div style="color: #374151;"><span class="font-medium" style="color: #111827;">Name:</span> {{ reservation.guest?.full_name }}</div>
                        <div style="color: #374151;"><span class="font-medium" style="color: #111827;">Email:</span> {{ reservation.guest?.email }}</div>
                        <div style="color: #374151;"><span class="font-medium" style="color: #111827;">Phone:</span> {{ reservation.guest?.phone }}</div>
                        <div style="color: #374151;"><span class="font-medium" style="color: #111827;">Nationality:</span> {{ reservation.guest?.nationality }}</div>
                    </div>
                </div>
                <div class="rounded-lg p-4" style="background-color: #f9fafb;">
                    <h3 class="font-semibold mb-3" style="color: #111827;">Booking Information</h3>
                    <div class="space-y-2 text-sm">
                        <div style="color: #374151;"><span class="font-medium" style="color: #111827;">Booking Source:</span> {{ formatBookingSource(reservation.booking_source) }}</div>
                        <div v-if="reservation.booking_reference" style="color: #374151;"><span class="font-medium" style="color: #111827;">Reference:</span> {{ reservation.booking_reference }}</div>
                        <div style="color: #374151;"><span class="font-medium" style="color: #111827;">Check-in:</span> {{ formatDate(reservation.check_in_date) }}</div>
                        <div style="color: #374151;"><span class="font-medium" style="color: #111827;">Check-out:</span> {{ formatDate(reservation.check_out_date) }}</div>
                        <div style="color: #374151;"><span class="font-medium" style="color: #111827;">Nights:</span> {{ reservation.nights }}</div>
                        <div style="color: #374151;"><span class="font-medium" style="color: #111827;">Guests:</span> {{ reservation.number_of_adults }} adults, {{ reservation.number_of_children }} children</div>
                    </div>
                </div>
            </div>

            <!-- Room Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="rounded-lg p-4" style="background-color: #f9fafb;">
                    <h3 class="font-semibold mb-3" style="color: #111827;">Room Information</h3>
                    <div class="space-y-2 text-sm">
                        <div style="color: #374151;"><span class="font-medium" style="color: #111827;">Room Type:</span> {{ reservation.room_type?.name }}</div>
                        <div v-if="reservation.room" style="color: #374151;"><span class="font-medium" style="color: #111827;">Room Number:</span> {{ reservation.room.room_number }}</div>
                        <div v-else style="color: #d97706;">Room not yet assigned</div>
                    </div>
                </div>
                <div class="rounded-lg p-4" style="background-color: #f9fafb;">
                    <h3 class="font-semibold mb-3" style="color: #111827;">Pricing</h3>
                    <div class="space-y-2 text-sm">
                        <div style="color: #374151;"><span class="font-medium" style="color: #111827;">Room Rate:</span> {{ formatCurrency(reservation.room_rate) }}/night</div>
                        <div style="color: #374151;"><span class="font-medium" style="color: #111827;">Total Room Charges:</span> {{ formatCurrency(reservation.total_room_charges) }}</div>
                        <div v-if="reservation.discount_amount > 0" style="color: #374151;">
                            <span class="font-medium" style="color: #111827;">Discount:</span> -{{ formatCurrency(reservation.discount_amount) }}
                            <span v-if="reservation.discount_reason"> ({{ reservation.discount_reason }})</span>
                        </div>
                        <div style="color: #374151;"><span class="font-medium" style="color: #111827;">Taxes:</span> {{ formatCurrency(reservation.taxes) }}</div>
                        <div style="color: #374151;"><span class="font-medium" style="color: #111827;">Service Charges:</span> {{ formatCurrency(reservation.service_charges) }}</div>
                        <div class="pt-2 border-t" style="border-color: #d1d5db;">
                            <span class="font-bold" style="color: #111827;">Total Amount:</span> <span style="color: #111827;">{{ formatCurrency(reservation.total_amount) }}</span>
                        </div>
                        <div style="color: #374151;"><span class="font-medium" style="color: #111827;">Paid:</span> {{ formatCurrency(reservation.paid_amount) }}</div>
                        <div style="color: #374151;"><span class="font-medium" style="color: #111827;">Balance:</span> {{ formatCurrency(reservation.balance_amount) }}</div>
                    </div>
                </div>
            </div>

            <!-- Check-in/Check-out Information -->
            <div v-if="reservation.actual_check_in || reservation.actual_check_out || reservation.checked_in_by || reservation.checked_out_by || reservation.status === 'checked_in' || reservation.status === 'checked_out'" class="mb-6">
                <h3 class="font-semibold mb-3" style="color: #111827;">Check-in/Check-out Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Check-in Information -->
                    <div v-if="reservation.status === 'checked_in' || reservation.actual_check_in || reservation.checked_in_by" class="rounded-lg p-4" style="background-color: #f0fdf4;">
                        <div class="text-sm">
                            <div class="font-medium" style="color: #14532d;">Checked In</div>
                            <div v-if="reservation.actual_check_in" style="color: #15803d;" class="mt-1">
                                {{ formatDateTime(reservation.actual_check_in) }}
                            </div>
                            <div v-else-if="reservation.status === 'checked_in'" class="mt-1 text-xs" style="color: #6b7280;">
                                Check-in time not recorded
                            </div>
                            <div v-if="reservation.checked_in_by" class="mt-2" style="color: #16a34a;">
                                <span class="font-medium">Checked in by:</span> {{ reservation.checked_in_by.name || (reservation.checked_in_by.first_name + ' ' + reservation.checked_in_by.last_name) }}
                            </div>
                            <div v-else-if="reservation.status === 'checked_in'" class="mt-2 text-xs" style="color: #6b7280;">
                                Check-in staff information not available
                            </div>
                        </div>
                    </div>
                    <!-- Check-out Information -->
                    <div v-if="reservation.status === 'checked_out' || reservation.actual_check_out || reservation.checked_out_by" class="rounded-lg p-4" style="background-color: #eff6ff;">
                        <div class="text-sm">
                            <div class="font-medium" style="color: #1e3a8a;">Checked Out</div>
                            <div v-if="reservation.actual_check_out" style="color: #1d4ed8;" class="mt-1">
                                {{ formatDateTime(reservation.actual_check_out) }}
                            </div>
                            <div v-else-if="reservation.status === 'checked_out'" class="mt-1 text-xs" style="color: #6b7280;">
                                Check-out time not recorded
                            </div>
                            <div v-if="reservation.checked_out_by" class="mt-2" style="color: #2563eb;">
                                <span class="font-medium">Checked out by:</span> {{ reservation.checked_out_by.name || (reservation.checked_out_by.first_name + ' ' + reservation.checked_out_by.last_name) }}
                            </div>
                            <div v-else-if="reservation.status === 'checked_out'" class="mt-2 text-xs" style="color: #6b7280;">
                                Check-out staff information not available
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Special Requests -->
            <div v-if="reservation.special_requests" class="mb-6">
                <h3 class="font-semibold mb-3" style="color: #111827;">Special Requests</h3>
                <p class="rounded-lg p-4" style="background-color: #f9fafb; color: #374151;">{{ reservation.special_requests }}</p>
            </div>

            <!-- Group Booking Info -->
            <div v-if="reservation.is_group_booking && reservation.group_booking" class="mb-6">
                <h3 class="font-semibold mb-3" style="color: #111827;">Group Booking</h3>
                <div class="rounded-lg p-4" style="background-color: #faf5ff;">
                    <div class="text-sm">
                        <div style="color: #374151;"><span class="font-medium" style="color: #111827;">Group:</span> {{ reservation.group_booking.group_name }}</div>
                        <div style="color: #374151;"><span class="font-medium" style="color: #111827;">Group Number:</span> {{ reservation.group_booking.group_number }}</div>
                        <div style="color: #374151;"><span class="font-medium" style="color: #111827;">Billing Type:</span> {{ formatBillingType(reservation.billing_type) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { formatCurrency as formatCurrencyUtil, initializeCurrencySettings, setCurrentCurrency, setCurrencyPosition } from '@/Utils/currency.js'

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
        pending: { backgroundColor: '#fef3c7', color: '#92400e' },
        confirmed: { backgroundColor: '#dbeafe', color: '#1e40af' },
        checked_in: { backgroundColor: '#d1fae5', color: '#065f46' },
        checked_out: { backgroundColor: '#f3f4f6', color: '#1f2937' },
        cancelled: { backgroundColor: '#fee2e2', color: '#991b1b' },
        no_show: { backgroundColor: '#fee2e2', color: '#991b1b' },
        modified: { backgroundColor: '#fed7aa', color: '#9a3412' },
    }
    return statusMap[status] || { backgroundColor: '#f3f4f6', color: '#1f2937' }
}

const confirmReservation = () => {
    router.post(route('manager.reservations.confirm', props.reservation.id))
}

const sendConfirmationEmail = () => {
    router.post(route('manager.reservations.send-confirmation', props.reservation.id))
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
