<template>
    <DashboardLayout title="Reservation Details" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <!-- Header Section -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Reservation #{{ reservation.reservation_number }}</h1>
                    <div class="flex items-center gap-4">
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">Status:
                            <span class="px-2 py-1 text-xs rounded-full ml-1" :style="getStatusStyle(reservation.status)">
                                {{ formatStatus(reservation.status) }}
                            </span>
                        </p>
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                            Created: {{ formatDate(reservation.created_at) }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button v-if="reservation.status === 'confirmed'"
                            @click="startCheckIn"
                            class="px-4 py-2 rounded-md transition-colors text-white font-medium"
                            :style="{ backgroundColor: themeColors.success }">
                        Check In
                    </button>
                    <button v-if="reservation.status === 'checked_in'"
                            @click="startCheckOut"
                            class="px-4 py-2 rounded-md transition-colors text-white font-medium"
                            :style="{ backgroundColor: themeColors.warning }">
                        Check Out
                    </button>
                    <Link :href="route('front-desk.reservations.edit', reservation.id)"
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                          :style="{ backgroundColor: themeColors.warning }">
                        Edit
                    </Link>
                    <Link :href="route('front-desk.reservations.index')"
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                          :style="{ backgroundColor: themeColors.primary }">
                        Back
                    </Link>
                </div>
            </div>

            <!-- Guest Information Section -->
            <div class="mb-8">
                <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Guest Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="rounded-lg p-4 border"
                         :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3" :style="{ color: themeColors.textSecondary, minWidth: '80px' }">Name:</span>
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ reservation.guest?.full_name || 'N/A' }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3" :style="{ color: themeColors.textSecondary, minWidth: '80px' }">Email:</span>
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ reservation.guest?.email || 'N/A' }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3" :style="{ color: themeColors.textSecondary, minWidth: '80px' }">Phone:</span>
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ reservation.guest?.phone || 'N/A' }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3" :style="{ color: themeColors.textSecondary, minWidth: '80px' }">Nationality:</span>
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ reservation.guest?.nationality || 'N/A' }}</span>
                            </div>
                            <div v-if="reservation.guest?.guest_type" class="flex items-center gap-2 mt-3">
                                <span class="inline-block w-3 h-3 rounded-full" :style="{ backgroundColor: reservation.guest.guest_type.color }"></span>
                                <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ reservation.guest.guest_type.name }}</span>
                                <span v-if="reservation.guest.is_vip" class="text-sm font-medium" :style="{ color: themeColors.warning }">⭐ VIP</span>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg p-4 border"
                         :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3" :style="{ color: themeColors.textSecondary, minWidth: '120px' }">Booking Source:</span>
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ formatBookingSource(reservation.booking_source) }}</span>
                            </div>
                            <div v-if="reservation.booking_reference" class="flex items-start">
                                <span class="text-sm font-medium mr-3" :style="{ color: themeColors.textSecondary, minWidth: '120px' }">Reference:</span>
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ reservation.booking_reference }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3" :style="{ color: themeColors.textSecondary, minWidth: '120px' }">Check-in:</span>
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ formatDate(reservation.check_in_date) }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3" :style="{ color: themeColors.textSecondary, minWidth: '120px' }">Check-out:</span>
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ formatDate(reservation.check_out_date) }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3" :style="{ color: themeColors.textSecondary, minWidth: '120px' }">Nights:</span>
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ reservation.nights }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3" :style="{ color: themeColors.textSecondary, minWidth: '120px' }">Guests:</span>
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ reservation.number_of_adults }} adults
                                    <span v-if="reservation.number_of_children > 0">, {{ reservation.number_of_children }} children</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Room & Pricing Section -->
            <div class="mb-8">
                <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Room & Pricing Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="rounded-lg p-4 border"
                         :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                        <h4 class="font-medium mb-3 text-sm" :style="{ color: themeColors.textPrimary }">Room Details</h4>
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3" :style="{ color: themeColors.textSecondary, minWidth: '100px' }">Room Type:</span>
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ reservation.room_type?.name || 'N/A' }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3" :style="{ color: themeColors.textSecondary, minWidth: '100px' }">Room Number:</span>
                                <span class="text-sm" :style="{ color: reservation.room ? themeColors.textPrimary : themeColors.warning }">
                                    {{ reservation.room?.room_number || 'Not yet assigned' }}
                                </span>
                            </div>
                            <div v-if="reservation.room?.floor" class="flex items-start">
                                <span class="text-sm font-medium mr-3" :style="{ color: themeColors.textSecondary, minWidth: '100px' }">Floor:</span>
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ reservation.room.floor }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg p-4 border"
                         :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                        <h4 class="font-medium mb-3 text-sm" :style="{ color: themeColors.textPrimary }">Pricing Breakdown</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span :style="{ color: themeColors.textSecondary }">Room Rate:</span>
                                <span class="font-medium" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(reservation.room_rate) }}/night</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span :style="{ color: themeColors.textSecondary }">Nights:</span>
                                <span class="font-medium" :style="{ color: themeColors.textPrimary }">{{ reservation.nights }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span :style="{ color: themeColors.textSecondary }">Room Charges:</span>
                                <span class="font-medium" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(reservation.total_room_charges) }}</span>
                            </div>
                            <div v-if="reservation.discount_amount > 0" class="flex justify-between text-sm">
                                <span :style="{ color: themeColors.textSecondary }">Discount:</span>
                                <span :style="{ color: themeColors.danger }">-{{ formatCurrency(reservation.discount_amount) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span :style="{ color: themeColors.textSecondary }">Taxes:</span>
                                <span class="font-medium" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(reservation.taxes) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span :style="{ color: themeColors.textSecondary }">Service Charges:</span>
                                <span class="font-medium" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(reservation.service_charges) }}</span>
                            </div>
                            <div class="flex justify-between text-sm font-semibold pt-2 border-t"
                                 :style="{ borderTopColor: themeColors.border, borderTopWidth: '1px' }">
                                <span :style="{ color: themeColors.textSecondary }">Total Amount:</span>
                                <span :style="{ color: themeColors.textPrimary }">{{ formatCurrency(reservation.total_amount) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span :style="{ color: themeColors.textSecondary }">Paid:</span>
                                <span class="font-medium" :style="{ color: themeColors.success }">{{ formatCurrency(reservation.paid_amount) }}</span>
                            </div>
                            <div class="flex justify-between text-sm font-medium">
                                <span :style="{ color: themeColors.textSecondary }">Balance:</span>
                                <span :style="{ color: reservation.balance_amount > 0 ? themeColors.danger : themeColors.success }">{{ formatCurrency(reservation.balance_amount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Check-in/Check-out Information -->
            <div v-if="hasCheckInOutInfo" class="mb-8">
                <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Check-in/Check-out Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div v-if="hasCheckInInfo" class="rounded-lg p-4 border"
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)', borderColor: themeColors.success, borderStyle: 'solid', borderWidth: '1px' }">
                        <div class="space-y-2">
                            <div class="font-medium text-sm" :style="{ color: themeColors.success }">Checked In</div>
                            <div v-if="reservation.actual_check_in" class="text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ formatDateTime(reservation.actual_check_in) }}
                            </div>
                            <div v-else class="text-sm" :style="{ color: themeColors.textTertiary }">Check-in time not recorded</div>
                            <div v-if="reservation.checked_in_by" class="text-sm pt-2">
                                <span class="font-medium" :style="{ color: themeColors.textSecondary }">Checked in by:</span>
                                <span class="ml-1" :style="{ color: themeColors.textPrimary }">
                                    {{ reservation.checked_in_by.name || (reservation.checked_in_by.first_name + ' ' + reservation.checked_in_by.last_name) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="hasCheckOutInfo" class="rounded-lg p-4 border"
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)', borderColor: themeColors.primary, borderStyle: 'solid', borderWidth: '1px' }">
                        <div class="space-y-2">
                            <div class="font-medium text-sm" :style="{ color: themeColors.primary }">Checked Out</div>
                            <div v-if="reservation.actual_check_out" class="text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ formatDateTime(reservation.actual_check_out) }}
                            </div>
                            <div v-else class="text-sm" :style="{ color: themeColors.textTertiary }">Check-out time not recorded</div>
                            <div v-if="reservation.checked_out_by" class="text-sm pt-2">
                                <span class="font-medium" :style="{ color: themeColors.textSecondary }">Checked out by:</span>
                                <span class="ml-1" :style="{ color: themeColors.textPrimary }">
                                    {{ reservation.checked_out_by.name || (reservation.checked_out_by.first_name + ' ' + reservation.checked_out_by.last_name) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Special Requests -->
            <div v-if="reservation.special_requests" class="mb-8">
                <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Special Requests</h3>
                <div class="rounded-lg p-4 border"
                     :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                    <p class="text-sm whitespace-pre-wrap" :style="{ color: themeColors.textPrimary }">{{ reservation.special_requests }}</p>
                </div>
            </div>

            <!-- Additional Preferences -->
            <div v-if="hasAdditionalPreferences" class="mb-8">
                <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Additional Preferences</h3>
                <div class="rounded-lg p-4 border"
                     :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div v-if="reservation.early_check_in_requested" class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-2" :style="{ backgroundColor: themeColors.success }"></span>
                            <span class="text-sm" :style="{ color: themeColors.textPrimary }">Early Check-in</span>
                        </div>
                        <div v-if="reservation.late_check_out_requested" class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-2" :style="{ backgroundColor: themeColors.success }"></span>
                            <span class="text-sm" :style="{ color: themeColors.textPrimary }">Late Check-out</span>
                        </div>
                        <div v-if="reservation.breakfast_included" class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-2" :style="{ backgroundColor: themeColors.success }"></span>
                            <span class="text-sm" :style="{ color: themeColors.textPrimary }">Breakfast</span>
                        </div>
                        <div v-if="reservation.wifi_included" class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-2" :style="{ backgroundColor: themeColors.success }"></span>
                            <span class="text-sm" :style="{ color: themeColors.textPrimary }">WiFi</span>
                        </div>
                        <div v-if="reservation.parking_required" class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-2" :style="{ backgroundColor: themeColors.success }"></span>
                            <span class="text-sm" :style="{ color: themeColors.textPrimary }">Parking</span>
                        </div>
                        <div v-if="reservation.airport_pickup" class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-2" :style="{ backgroundColor: themeColors.success }"></span>
                            <span class="text-sm" :style="{ color: themeColors.textPrimary }">Airport Pickup</span>
                        </div>
                        <div v-if="reservation.airport_drop" class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-2" :style="{ backgroundColor: themeColors.success }"></span>
                            <span class="text-sm" :style="{ color: themeColors.textPrimary }">Airport Drop</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'

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
    navigation: Array,
    reservation: Object,
})

const hasCheckInOutInfo = computed(() =>
    props.reservation.actual_check_in || props.reservation.actual_check_out ||
    props.reservation.checked_in_by || props.reservation.checked_out_by ||
    props.reservation.status === 'checked_in' || props.reservation.status === 'checked_out'
)
const hasCheckInInfo = computed(() =>
    props.reservation.status === 'checked_in' || props.reservation.actual_check_in || props.reservation.checked_in_by
)
const hasCheckOutInfo = computed(() =>
    props.reservation.status === 'checked_out' || props.reservation.actual_check_out || props.reservation.checked_out_by
)
const hasAdditionalPreferences = computed(() =>
    props.reservation.early_check_in_requested || props.reservation.late_check_out_requested ||
    props.reservation.breakfast_included || props.reservation.wifi_included ||
    props.reservation.parking_required || props.reservation.airport_pickup ||
    props.reservation.airport_drop
)

const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A'
const formatDateTime = (d) => d ? new Date(d).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A'
const formatStatus = (s) => s ? s.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A'
const formatBookingSource = (s) => ({ walk_in: 'Walk-in', phone: 'Phone', email: 'Email', website: 'Website', booking_com: 'Booking.com', expedia: 'Expedia', agoda: 'Agoda', travel_agent: 'Travel Agent', corporate: 'Corporate' }[s] || s || 'N/A')

const getStatusStyle = (status) => {
    const map = {
        pending: { backgroundColor: `var(--kotel-warning)`, color: 'white' },
        confirmed: { backgroundColor: `var(--kotel-primary)`, color: 'white' },
        checked_in: { backgroundColor: `var(--kotel-success)`, color: 'white' },
        checked_out: { backgroundColor: `var(--kotel-secondary)`, color: 'white' },
        cancelled: { backgroundColor: `var(--kotel-danger)`, color: 'white' },
        no_show: { backgroundColor: `var(--kotel-danger)`, color: 'white' },
    }
    return map[status] || map['pending']
}

const startCheckIn = () => router.visit(route('front-desk.checkin') + `?reservation_id=${props.reservation.id}`)
const startCheckOut = () => router.visit(route('front-desk.checkout') + `?reservation_id=${props.reservation.id}`)
</script>

<style scoped>
.transition-colors {
    transition-property: background-color, border-color, color;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}
button:hover { transform: translateY(-1px); }
button:active { transform: translateY(0); }
</style>
