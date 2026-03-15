<template>
    <DashboardLayout title="Group Booking Details">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">{{ groupBooking.group_name }}</h1>
                    <div class="flex items-center gap-4">
                        <p class="text-sm"
                           :style="{ color: themeColors.textSecondary }">Group Number: {{ groupBooking.group_number }}</p>
                        <p class="text-sm"
                           :style="{ color: themeColors.textSecondary }">Status:
                            <span class="px-2 py-1 text-xs rounded-full ml-1" :style="getStatusStyle(groupBooking.status)">
                                {{ formatStatus(groupBooking.status) }}
                            </span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.group-bookings.edit', groupBooking.id)"
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                          :style="{ 
                              backgroundColor: themeColors.warning,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = '#d97706'"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.warning">
                        Edit
                    </Link>
                    <Link :href="route('admin.group-bookings.index')"
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

            <!-- Group Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="rounded-lg p-4 border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }">
                    <h3 class="font-medium mb-3 text-sm"
                        :style="{ color: themeColors.textPrimary }">Booking Information</h3>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <span class="text-sm font-medium mr-3"
                                  :style="{ color: themeColors.textSecondary, minWidth: '110px' }">Check-in:</span>
                            <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ formatDate(groupBooking.check_in_date) }}</span>
                        </div>
                        <div class="flex items-start">
                            <span class="text-sm font-medium mr-3"
                                  :style="{ color: themeColors.textSecondary, minWidth: '110px' }">Check-out:</span>
                            <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ formatDate(groupBooking.check_out_date) }}</span>
                        </div>
                        <div class="flex items-start">
                            <span class="text-sm font-medium mr-3"
                                  :style="{ color: themeColors.textSecondary, minWidth: '110px' }">Total Rooms:</span>
                            <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ groupBooking.total_rooms }}</span>
                        </div>
                        <div class="flex items-start">
                            <span class="text-sm font-medium mr-3"
                                  :style="{ color: themeColors.textSecondary, minWidth: '110px' }">Total Guests:</span>
                            <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ groupBooking.total_guests }}</span>
                        </div>
                        <div class="flex items-start">
                            <span class="text-sm font-medium mr-3"
                                  :style="{ color: themeColors.textSecondary, minWidth: '110px' }">Adults:</span>
                            <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ groupBooking.total_adults }}</span>
                        </div>
                        <div class="flex items-start">
                            <span class="text-sm font-medium mr-3"
                                  :style="{ color: themeColors.textSecondary, minWidth: '110px' }">Children:</span>
                            <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ groupBooking.total_children }}</span>
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
                    <h3 class="font-medium mb-3 text-sm"
                        :style="{ color: themeColors.textPrimary }">Billing Information</h3>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <span class="text-sm font-medium mr-3"
                                  :style="{ color: themeColors.textSecondary, minWidth: '110px' }">Billing Type:</span>
                            <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ formatBillingType(groupBooking.billing_type) }}</span>
                        </div>
                        <div class="flex items-start">
                            <span class="text-sm font-medium mr-3"
                                  :style="{ color: themeColors.textSecondary, minWidth: '110px' }">Total Amount:</span>
                            <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(groupBooking.total_group_amount) }}</span>
                        </div>
                        <div class="flex items-start">
                            <span class="text-sm font-medium mr-3"
                                  :style="{ color: themeColors.textSecondary, minWidth: '110px' }">Paid Amount:</span>
                            <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(groupBooking.paid_amount) }}</span>
                        </div>
                        <div class="flex items-start">
                            <span class="text-sm font-medium mr-3"
                                  :style="{ color: themeColors.textSecondary, minWidth: '110px' }">Balance:</span>
                            <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(groupBooking.balance_amount) }}</span>
                        </div>
                        <div v-if="groupBooking.group_discount_percentage > 0" class="flex items-start">
                            <span class="text-sm font-medium mr-3"
                                  :style="{ color: themeColors.textSecondary, minWidth: '110px' }">Discount:</span>
                            <span class="text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ groupBooking.group_discount_percentage }}% ({{ formatCurrency(groupBooking.group_discount_amount) }})
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reservations -->
            <div class="mb-8">
                <h3 class="text-lg font-medium mb-4"
                    :style="{ color: themeColors.textPrimary }">Reservations ({{ groupBooking.reservations?.length || 0 }})</h3>

                <div v-if="groupBooking.reservations && groupBooking.reservations.length > 0" class="overflow-x-auto rounded-lg border"
                     :style="{ 
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }">
                    <table class="min-w-full">
                        <thead :style="{ backgroundColor: themeColors.background }">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase"
                                    :style="{ color: themeColors.textSecondary }">Reservation #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase"
                                    :style="{ color: themeColors.textSecondary }">Guest</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase"
                                    :style="{ color: themeColors.textSecondary }">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase"
                                    :style="{ color: themeColors.textSecondary }">Status</th>
                            </tr>
                        </thead>
                        <tbody :style="{ backgroundColor: themeColors.card }">
                            <tr v-for="reservation in groupBooking.reservations" :key="reservation.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm"
                                    :style="{ color: themeColors.textPrimary }">{{ reservation.reservation_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm"
                                    :style="{ color: themeColors.textPrimary }">{{ reservation.guest?.full_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm"
                                    :style="{ color: themeColors.textPrimary }">{{ reservation.room?.room_number || 'TBA' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full" :style="getStatusStyle(reservation.status)">
                                        {{ formatStatus(reservation.status) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-8"
                     :style="{ color: themeColors.textSecondary }">
                    No reservations assigned to this group yet.
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme.js'

const props = defineProps({
    user: Object,
    groupBooking: Object,
})

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

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const formatBillingType = (type) => {
    return type.charAt(0).toUpperCase() + type.slice(1)
}

const formatStatus = (status) => {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getStatusStyle = (status) => {
    const styles = {
        pending: { backgroundColor: 'rgba(250, 204, 21, 0.15)', color: themeColors.value.warning },
        confirmed: { backgroundColor: 'rgba(59, 130, 246, 0.15)', color: themeColors.value.primary },
        checked_in: { backgroundColor: 'rgba(34, 197, 94, 0.15)', color: themeColors.value.success },
        checked_out: { backgroundColor: 'rgba(156, 163, 175, 0.15)', color: themeColors.value.textSecondary },
        cancelled: { backgroundColor: 'rgba(239, 68, 68, 0.15)', color: themeColors.value.danger },
    }

    return styles[status] || { backgroundColor: themeColors.value.background, color: themeColors.value.textSecondary }
}
</script>
