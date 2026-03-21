<template>
    <DashboardLayout title="Reservation Details">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <!-- Header Section -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Reservation #{{ reservation.reservation_number }}</h1>
                    <div class="flex items-center gap-4">
                        <p class="text-sm"
                           :style="{ color: themeColors.textSecondary }">Status:
                            <span class="px-2 py-1 text-xs rounded-full ml-1" :style="getStatusStyle(reservation.status)">
                                {{ formatStatus(reservation.status) }}
                            </span>
                        </p>
                        <p class="text-sm"
                           :style="{ color: themeColors.textSecondary }">
                            Created: {{ formatDate(reservation.created_at) }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button v-if="reservation.status === 'pending' || reservation.status === 'confirmed'"
                            @click="confirmReservation"
                            class="px-4 py-2 rounded-md transition-colors text-white font-medium"
                            :style="{ 
                                backgroundColor: themeColors.success,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                        Confirm
                    </button>
                    <button @click="sendConfirmationEmail"
                            class="px-4 py-2 rounded-md transition-colors font-medium"
                            :style="{ 
                                backgroundColor: themeColors.secondary,
                                color: themeColors.textPrimary 
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        Send Email
                    </button>
                    <Link :href="route('admin.reservations.service-charges', reservation.id)"
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                          :style="{ 
                              backgroundColor: '#8b5cf6',
                          }"
                          @mouseenter="$event.target.style.backgroundColor = '#7c3aed'"
                          @mouseleave="$event.target.style.backgroundColor = '#8b5cf6'">
                        Service Charges
                    </Link>
                    <Link :href="route('admin.reservations.edit', reservation.id)"
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                          :style="{ 
                              backgroundColor: themeColors.warning,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = '#d97706'"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.warning">
                        Edit
                    </Link>
                    <Link :href="route('admin.reservations.index')"
                          class="px-4 py-2 rounded-md transition-colors font-medium"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                              color: 'white'
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        Back
                    </Link>
                </div>
            </div>

            <!-- Guest Information Section -->
            <div class="mb-8">
                <h3 class="text-lg font-medium mb-4"
                    :style="{ color: themeColors.textPrimary }">Guest Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="rounded-lg p-4 border"
                         :style="{ 
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '80px' }">Name:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ reservation.guest?.full_name || 'N/A' }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '80px' }">Email:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ reservation.guest?.email || 'N/A' }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '80px' }">Phone:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ reservation.guest?.phone || 'N/A' }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '80px' }">Nationality:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ reservation.guest?.nationality || 'N/A' }}</span>
                            </div>
                            <div v-if="reservation.guest?.guest_type" class="flex items-center gap-2 mt-3">
                                <span class="inline-block w-3 h-3 rounded-full" :style="{ backgroundColor: reservation.guest.guest_type.color }"></span>
                                <span class="text-sm font-medium"
                                      :style="{ color: themeColors.textPrimary }">{{ reservation.guest.guest_type.name }}</span>
                                <span v-if="reservation.guest.is_vip" class="text-sm font-medium"
                                      :style="{ color: themeColors.warning }">⭐ VIP</span>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg p-4 border"
                         :style="{ 
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '120px' }">Booking Source:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ formatBookingSource(reservation.booking_source) }}</span>
                            </div>
                            <div v-if="reservation.booking_reference" class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '120px' }">Reference:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ reservation.booking_reference }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '120px' }">Check-in:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ formatDate(reservation.check_in_date) }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '120px' }">Check-out:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ formatDate(reservation.check_out_date) }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '120px' }">Nights:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ reservation.nights }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '120px' }">Guests:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">
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
                <h3 class="text-lg font-medium mb-4"
                    :style="{ color: themeColors.textPrimary }">Room & Pricing Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="rounded-lg p-4 border"
                         :style="{ 
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <h4 class="font-medium mb-3 text-sm"
                            :style="{ color: themeColors.textPrimary }">Room Details</h4>
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '100px' }">Room Type:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ reservation.room_type?.name || 'N/A' }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '100px' }">Room Number:</span>
                                <span class="text-sm"
                                      :style="{ color: reservation.room ? themeColors.textPrimary : themeColors.warning }">
                                    {{ reservation.room?.room_number || 'Not yet assigned' }}
                                </span>
                            </div>
                            <div v-if="reservation.room_type" class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '100px' }">Capacity:</span>
                                <span class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">{{ reservation.room_type.capacity }} guests</span>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg p-4 border"
                         :style="{ 
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <h4 class="font-medium mb-3 text-sm"
                            :style="{ color: themeColors.textPrimary }">Pricing Breakdown</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span :style="{ color: themeColors.textSecondary }">Room Rate:</span>
                                <span class="font-medium"
                                      :style="{ color: themeColors.textPrimary }">{{ formatCurrency(reservation.room_rate) }}/night</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span :style="{ color: themeColors.textSecondary }">Nights:</span>
                                <span class="font-medium"
                                      :style="{ color: themeColors.textPrimary }">{{ reservation.nights }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span :style="{ color: themeColors.textSecondary }">Room Charges:</span>
                                <span class="font-medium"
                                      :style="{ color: themeColors.textPrimary }">{{ formatCurrency(reservation.total_room_charges) }}</span>
                            </div>
                            <div v-if="reservation.discount_amount > 0" class="flex justify-between text-sm"
                                 :style="{ color: themeColors.danger }">
                                <span :style="{ color: themeColors.textSecondary }">Discount:</span>
                                <span>-{{ formatCurrency(reservation.discount_amount) }}
                                    <span v-if="reservation.discount_reason" class="text-xs ml-1">({{ reservation.discount_reason }})</span>
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span :style="{ color: themeColors.textSecondary }">Taxes:</span>
                                <span class="font-medium"
                                      :style="{ color: themeColors.textPrimary }">{{ formatCurrency(reservation.taxes) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span :style="{ color: themeColors.textSecondary }">Service Charges:</span>
                                <span class="font-medium"
                                      :style="{ color: themeColors.textPrimary }">{{ formatCurrency(reservation.service_charges) }}</span>
                            </div>
                            <div class="flex justify-between text-sm font-semibold pt-2 border-t"
                                 :style="{ 
                                     borderTopColor: themeColors.border,
                                     borderTopWidth: '1px'
                                 }">
                                <span :style="{ color: themeColors.textSecondary }">Total Amount:</span>
                                <span :style="{ color: themeColors.textPrimary }">{{ formatCurrency(reservation.total_amount) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span :style="{ color: themeColors.textSecondary }">Paid:</span>
                                <span class="font-medium"
                                      :style="{ color: themeColors.success }">{{ formatCurrency(reservation.paid_amount) }}</span>
                            </div>
                            <div class="flex justify-between text-sm font-medium">
                                <span :style="{ color: themeColors.textSecondary }">Balance:</span>
                                <span :style="{ 
                                    color: reservation.balance_amount > 0 ? themeColors.danger : themeColors.success 
                                }">{{ formatCurrency(reservation.balance_amount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Check-in/Check-out Information -->
            <div v-if="hasCheckInOutInfo" class="mb-8">
                <h3 class="text-lg font-medium mb-4"
                    :style="{ color: themeColors.textPrimary }">Check-in/Check-out Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Check-in Information -->
                    <div v-if="hasCheckInInfo" class="rounded-lg p-4 border"
                         :style="{ 
                             backgroundColor: 'rgba(34, 197, 94, 0.1)',
                             borderColor: themeColors.success,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="space-y-2">
                            <div class="font-medium text-sm"
                                 :style="{ color: themeColors.success }">Checked In</div>
                            <div v-if="reservation.actual_check_in" class="text-sm"
                                 :style="{ color: themeColors.textPrimary }">
                                {{ formatDateTime(reservation.actual_check_in) }}
                            </div>
                            <div v-else class="text-sm"
                                 :style="{ color: themeColors.textTertiary }">
                                Check-in time not recorded
                            </div>
                            <div v-if="reservation.checked_in_by" class="text-sm pt-2">
                                <span class="font-medium"
                                      :style="{ color: themeColors.textSecondary }">Checked in by:</span>
                                <span class="ml-1"
                                      :style="{ color: themeColors.textPrimary }">
                                    {{ reservation.checked_in_by.name || (reservation.checked_in_by.first_name + ' ' + reservation.checked_in_by.last_name) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- Check-out Information -->
                    <div v-if="hasCheckOutInfo" class="rounded-lg p-4 border"
                         :style="{ 
                             backgroundColor: 'rgba(59, 130, 246, 0.1)',
                             borderColor: themeColors.primary,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="space-y-2">
                            <div class="font-medium text-sm"
                                 :style="{ color: themeColors.primary }">Checked Out</div>
                            <div v-if="reservation.actual_check_out" class="text-sm"
                                 :style="{ color: themeColors.textPrimary }">
                                {{ formatDateTime(reservation.actual_check_out) }}
                            </div>
                            <div v-else class="text-sm"
                                 :style="{ color: themeColors.textTertiary }">
                                Check-out time not recorded
                            </div>
                            <div v-if="reservation.checked_out_by" class="text-sm pt-2">
                                <span class="font-medium"
                                      :style="{ color: themeColors.textSecondary }">Checked out by:</span>
                                <span class="ml-1"
                                      :style="{ color: themeColors.textPrimary }">
                                    {{ reservation.checked_out_by.name || (reservation.checked_out_by.first_name + ' ' + reservation.checked_out_by.last_name) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Special Requests -->
            <div v-if="reservation.special_requests" class="mb-8">
                <h3 class="text-lg font-medium mb-4"
                    :style="{ color: themeColors.textPrimary }">Special Requests</h3>
                <div class="rounded-lg p-4 border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }">
                    <p class="text-sm whitespace-pre-wrap"
                       :style="{ color: themeColors.textPrimary }">{{ reservation.special_requests }}</p>
                </div>
            </div>

            <!-- Group Booking Information -->
            <div v-if="reservation.is_group_booking && reservation.group_booking" class="mb-8">
                <h3 class="text-lg font-medium mb-4"
                    :style="{ color: themeColors.textPrimary }">Group Booking Information</h3>
                <div class="rounded-lg p-4 border"
                     :style="{ 
                         backgroundColor: 'rgba(139, 92, 246, 0.1)',
                         borderColor: '#8b5cf6',
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">Group Name:</span>
                            <div class="text-sm mt-1"
                                 :style="{ color: themeColors.textPrimary }">{{ reservation.group_booking.group_name }}</div>
                        </div>
                        <div>
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">Group Number:</span>
                            <div class="text-sm mt-1"
                                 :style="{ color: themeColors.textPrimary }">{{ reservation.group_booking.group_number }}</div>
                        </div>
                        <div>
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">Billing Type:</span>
                            <div class="text-sm mt-1"
                                 :style="{ color: themeColors.textPrimary }">{{ formatBillingType(reservation.billing_type) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Preferences -->
            <div v-if="hasAdditionalPreferences" class="mb-8">
                <h3 class="text-lg font-medium mb-4"
                    :style="{ color: themeColors.textPrimary }">Additional Preferences</h3>
                <div class="rounded-lg p-4 border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div v-if="reservation.early_check_in_requested" class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-2"
                                  :style="{ backgroundColor: themeColors.success }"></span>
                            <span class="text-sm"
                                  :style="{ color: themeColors.textPrimary }">Early Check-in</span>
                        </div>
                        <div v-if="reservation.late_check_out_requested" class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-2"
                                  :style="{ backgroundColor: themeColors.success }"></span>
                            <span class="text-sm"
                                  :style="{ color: themeColors.textPrimary }">Late Check-out</span>
                        </div>
                        <div v-if="reservation.breakfast_included" class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-2"
                                  :style="{ backgroundColor: themeColors.success }"></span>
                            <span class="text-sm"
                                  :style="{ color: themeColors.textPrimary }">Breakfast</span>
                        </div>
                        <div v-if="reservation.wifi_included" class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-2"
                                  :style="{ backgroundColor: themeColors.success }"></span>
                            <span class="text-sm"
                                  :style="{ color: themeColors.textPrimary }">WiFi</span>
                        </div>
                        <div v-if="reservation.parking_required" class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-2"
                                  :style="{ backgroundColor: themeColors.success }"></span>
                            <span class="text-sm"
                                  :style="{ color: themeColors.textPrimary }">Parking</span>
                        </div>
                        <div v-if="reservation.airport_pickup" class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-2"
                                  :style="{ backgroundColor: themeColors.success }"></span>
                            <span class="text-sm"
                                  :style="{ color: themeColors.textPrimary }">Airport Pickup</span>
                        </div>
                        <div v-if="reservation.airport_drop" class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-2"
                                  :style="{ backgroundColor: themeColors.success }"></span>
                            <span class="text-sm"
                                  :style="{ color: themeColors.textPrimary }">Airport Drop</span>
                        </div>
                        <div v-if="reservation.preferred_check_in_time" class="flex items-center">
                            <span class="text-sm font-medium mr-2"
                                  :style="{ color: themeColors.textSecondary }">Preferred Check-in:</span>
                            <span class="text-sm"
                                  :style="{ color: themeColors.textPrimary }">{{ reservation.preferred_check_in_time }}</span>
                        </div>
                    </div>
                </div>
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
import { computed, ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
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
    textTertiary: `var(--kotel-text-tertiary)`,
    primary: `var(--kotel-primary)`,
    secondary: `var(--kotel-secondary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
    hover: `rgba(255, 255, 255, 0.1)`
}))

// Load theme on mount
loadTheme()

const props = defineProps({
    user: Object,
    reservation: Object,
})

// Computed properties for conditional rendering
const hasCheckInOutInfo = computed(() => {
    return props.reservation.actual_check_in || 
           props.reservation.actual_check_out || 
           props.reservation.checked_in_by || 
           props.reservation.checked_out_by || 
           props.reservation.status === 'checked_in' || 
           props.reservation.status === 'checked_out'
})

const hasCheckInInfo = computed(() => {
    return props.reservation.status === 'checked_in' || 
           props.reservation.actual_check_in || 
           props.reservation.checked_in_by
})

const hasCheckOutInfo = computed(() => {
    return props.reservation.status === 'checked_out' || 
           props.reservation.actual_check_out || 
           props.reservation.checked_out_by
})

const hasAdditionalPreferences = computed(() => {
    return props.reservation.early_check_in_requested ||
           props.reservation.late_check_out_requested ||
           props.reservation.breakfast_included ||
           props.reservation.wifi_included ||
           props.reservation.parking_required ||
           props.reservation.airport_pickup ||
           props.reservation.airport_drop ||
           props.reservation.preferred_check_in_time ||
           props.reservation.preferred_check_out_time
})

// Utility functions
const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const formatDateTime = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const formatStatus = (status) => {
    return status ? status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A'
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
    return sources[source] || source || 'N/A'
}

const formatBillingType = (type) => {
    return type ? type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A'
}

const getStatusStyle = (status) => {
    const styles = {
        pending: {
            backgroundColor: `var(--kotel-warning)`,
            color: 'white'
        },
        confirmed: {
            backgroundColor: `var(--kotel-primary)`,
            color: 'white'
        },
        checked_in: {
            backgroundColor: `var(--kotel-success)`,
            color: 'white'
        },
        checked_out: {
            backgroundColor: `var(--kotel-secondary)`,
            color: 'white'
        },
        cancelled: {
            backgroundColor: `var(--kotel-danger)`,
            color: 'white'
        },
        no_show: {
            backgroundColor: `var(--kotel-danger)`,
            color: 'white'
        },
        modified: {
            backgroundColor: `var(--kotel-warning)`,
            color: 'white'
        },
    }
    return styles[status] || styles['pending']
}

// Action methods
const confirmReservation = () => {
    router.post(route('admin.reservations.confirm', props.reservation.id))
}

const sendConfirmationEmail = () => {
    router.post(route('admin.reservations.send-confirmation', props.reservation.id))
}

const serviceChargeForm = ref({ description: '', amount: null, quantity: 1 })
const isAddingServiceCharge = ref(false)

const addServiceCharge = () => {
    if (!serviceChargeForm.value.description || !serviceChargeForm.value.amount || serviceChargeForm.value.amount <= 0) return
    isAddingServiceCharge.value = true
    router.post(route('admin.checkout.service-charge'), {
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
</script>

<style scoped>
/* Fix placeholder colors for inputs */
input::placeholder,
textarea::placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input::-webkit-input-placeholder,
textarea::-webkit-input-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input::-moz-placeholder,
textarea::-moz-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input:-ms-input-placeholder,
textarea:-ms-input-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

/* Fix placeholder colors for select options */
select option:disabled,
select option[disabled] {
    color: var(--kotel-text-tertiary) !important;
}

select option[value=""] {
    color: var(--kotel-text-tertiary) !important;
}

/* Custom animations and transitions */
.transition-colors {
    transition-property: background-color, border-color, color;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Hover effects for interactive elements */
button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

button:active {
    transform: translateY(0);
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

/* Status badge improvements */
.rounded-full {
    border-radius: 9999px;
}

/* Card shadow improvements */
.shadow {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

.rounded-lg {
    border-radius: 0.5rem;
}

.rounded-md {
    border-radius: 0.375rem;
}

/* Grid utilities */
.grid {
    display: grid;
}

.grid-cols-1 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
}

.grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.grid-cols-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.grid-cols-4 {
    grid-template-columns: repeat(4, minmax(0, 1fr));
}

.gap-3 {
    gap: 0.75rem;
}

.gap-4 {
    gap: 1rem;
}

.gap-6 {
    gap: 1.5rem;
}

/* Flex utilities */
.flex {
    display: flex;
}

.items-center {
    align-items: center;
}

.items-start {
    align-items: flex-start;
}

.justify-between {
    justify-content: space-between;
}

.justify-end {
    justify-content: flex-end;
}

/* Spacing utilities */
.p-4 {
    padding: 1rem;
}

.p-6 {
    padding: 1.5rem;
}

.px-4 {
    padding-left: 1rem;
    padding-right: 1rem;
}

.py-2 {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem}

.mb-2 {
    margin-bottom: 0.5rem;
}

.mb-3 {
    margin-bottom: 0.75rem;
}

.mb-4 {
    margin-bottom: 1rem;
}

.mb-6 {
    margin-bottom: 1.5rem;
}

.mb-8 {
    margin-bottom: 2rem;
}

.mr-2 {
    margin-right: 0.5rem;
}

.mr-3 {
    margin-right: 0.75rem;
}

.ml-1 {
    margin-left: 0.25rem;
}

.mt-1 {
    margin-top: 0.25rem;
}

.mt-2 {
    margin-top: 0.5rem;
}

.mt-3 {
    margin-top: 0.75rem;
}

.pt-2 {
    padding-top: 0.5rem;
}

/* Text utilities */
.text-sm {
    font-size: 0.875rem;
    line-height: 1.25rem;
}

.text-xs {
    font-size: 0.75rem;
    line-height: 1rem;
}

.text-lg {
    font-size: 1.125rem;
    line-height: 1.75rem;
}

.text-2xl {
    font-size: 1.5rem;
    line-height: 2rem;
}

.font-medium {
    font-weight: 500;
}

.font-semibold {
    font-weight: 600;
}

.font-bold {
    font-weight: 700;
}

.whitespace-pre-wrap {
    white-space: pre-wrap;
}

/* Width utilities */
.w-2 {
    width: 0.5rem;
}

.w-3 {
    width: 0.75rem;
}

.w-4 {
    width: 1rem;
}

.min-w-0 {
    min-width: 0px;
}

/* Display utilities */
.block {
    display: block;
}

.inline {
    display: inline;
}

.inline-block {
    display: inline-block;
}

/* Border utilities */
.border {
    border-width: 1px;
}

.border-t {
    border-top-width: 1px;
}

/* Position utilities */
.relative {
    position: relative;
}

/* Responsive utilities */
@media (min-width: 768px) {
    .md\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    
    .md\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    
    .md\:grid-cols-4 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
    
    .md\:col-span-2 {
        grid-column: span 2 / span 2;
    }
    
    .md\:col-span-3 {
        grid-column: span 3 / span 3;
    }
}
</style>
