<template>
    <DashboardLayout title="Guest Details" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-1"
                        :style="{ color: themeColors.textPrimary }">Guest Details</h1>
                    <p :style="{ color: themeColors.textSecondary }">{{ guest.first_name }} {{ guest.last_name }}</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('front-desk.guests.edit', guest.id)"
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                          :style="{ backgroundColor: themeColors.warning }">
                        Edit Guest
                    </Link>
                    <Link :href="route('front-desk.guests.index')"
                          class="px-4 py-2 rounded-md transition-colors font-medium"
                          :style="{ backgroundColor: themeColors.primary, color: 'white' }">
                        Back to Guests
                    </Link>
                </div>
            </div>
        </div>

        <!-- Guest Information -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2 shadow rounded-lg p-6"
                 :style="{ backgroundColor: themeColors.card }">
                <h2 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Personal Information</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Guest ID</label>
                        <p class="mt-1 text-sm"
                           :style="{ color: themeColors.textPrimary }">{{ guest.guest_id || '—' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Status</label>
                        <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :style="getStatusStyle(guest.current_status || 'no_reservations')">
                            {{ formatStatus(guest.current_status || 'no_reservations') }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Title</label>
                        <p class="mt-1 text-sm"
                           :style="{ color: themeColors.textPrimary }">{{ guest.title || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Full Name</label>
                        <p class="mt-1 text-sm"
                           :style="{ color: themeColors.textPrimary }">{{ guest.first_name }} {{ guest.last_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Date of Birth</label>
                        <p class="mt-1 text-sm"
                           :style="{ color: themeColors.textPrimary }">{{ formatDate(guest.date_of_birth) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Gender</label>
                        <p class="mt-1 text-sm capitalize"
                           :style="{ color: themeColors.textPrimary }">{{ guest.gender || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Nationality</label>
                        <p class="mt-1 text-sm"
                           :style="{ color: themeColors.textPrimary }">{{ guest.nationality || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Occupation</label>
                        <p class="mt-1 text-sm"
                           :style="{ color: themeColors.textPrimary }">{{ guest.occupation || 'N/A' }}</p>
                    </div>
                    <div v-if="guest.guest_type">
                        <label class="block text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Guest Type</label>
                        <p class="mt-1 text-sm"
                           :style="{ color: themeColors.textPrimary }">{{ guest.guest_type.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">VIP</label>
                        <p class="mt-1 text-sm"
                           :style="{ color: themeColors.textPrimary }">{{ guest.is_vip ? 'Yes' : 'No' }}</p>
                    </div>
                </div>

                <h2 class="text-lg font-semibold mb-4 mt-6"
                    :style="{ color: themeColors.textPrimary }">Contact Information</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Email</label>
                        <p class="mt-1 text-sm"
                           :style="{ color: themeColors.textPrimary }">{{ guest.email || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Phone</label>
                        <p class="mt-1 text-sm"
                           :style="{ color: themeColors.textPrimary }">{{ guest.phone || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Alternate Phone</label>
                        <p class="mt-1 text-sm"
                           :style="{ color: themeColors.textPrimary }">{{ guest.alternate_phone || 'N/A' }}</p>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Address</label>
                        <p class="mt-1 text-sm"
                           :style="{ color: themeColors.textPrimary }">{{ formatAddress(guest) }}</p>
                    </div>
                </div>

                <h2 class="text-lg font-semibold mb-4 mt-6"
                    :style="{ color: themeColors.textPrimary }">Emergency Contact</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Name</label>
                        <p class="mt-1 text-sm"
                           :style="{ color: themeColors.textPrimary }">{{ guest.emergency_contact_name || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Phone</label>
                        <p class="mt-1 text-sm"
                           :style="{ color: themeColors.textPrimary }">{{ guest.emergency_contact_phone || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Relationship</label>
                        <p class="mt-1 text-sm"
                           :style="{ color: themeColors.textPrimary }">{{ guest.emergency_contact_relationship || 'N/A' }}</p>
                    </div>
                </div>

                <h2 class="text-lg font-semibold mb-4 mt-6"
                    :style="{ color: themeColors.textPrimary }">Identification</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">ID Type</label>
                        <p class="mt-1 text-sm capitalize"
                           :style="{ color: themeColors.textPrimary }">{{ guest.id_type?.replace('_', ' ') || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">ID Number</label>
                        <p class="mt-1 text-sm"
                           :style="{ color: themeColors.textPrimary }">{{ guest.id_number || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Issuing Authority</label>
                        <p class="mt-1 text-sm"
                           :style="{ color: themeColors.textPrimary }">{{ guest.id_issuing_authority || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Issue Date</label>
                        <p class="mt-1 text-sm"
                           :style="{ color: themeColors.textPrimary }">{{ formatDate(guest.id_issue_date) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Expiry Date</label>
                        <p class="mt-1 text-sm"
                           :style="{ color: themeColors.textPrimary }">{{ formatDate(guest.id_expiry_date) }}</p>
                    </div>
                </div>

                <div v-if="guest.special_requests || guest.dietary_restrictions || guest.notes" class="mt-6">
                    <h2 class="text-lg font-semibold mb-4"
                        :style="{ color: themeColors.textPrimary }">Additional Information</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div v-if="guest.purpose_of_visit">
                            <label class="block text-sm font-medium"
                                   :style="{ color: themeColors.textSecondary }">Purpose of Visit</label>
                            <p class="mt-1 text-sm"
                               :style="{ color: themeColors.textPrimary }">{{ guest.purpose_of_visit }}</p>
                        </div>
                        <div v-if="guest.special_requests" class="col-span-2">
                            <label class="block text-sm font-medium"
                                   :style="{ color: themeColors.textSecondary }">Special Requests</label>
                            <p class="mt-1 text-sm"
                               :style="{ color: themeColors.textPrimary }">{{ guest.special_requests }}</p>
                        </div>
                        <div v-if="guest.dietary_restrictions" class="col-span-2">
                            <label class="block text-sm font-medium"
                                   :style="{ color: themeColors.textSecondary }">Dietary Restrictions</label>
                            <p class="mt-1 text-sm"
                               :style="{ color: themeColors.textPrimary }">{{ guest.dietary_restrictions }}</p>
                        </div>
                        <div v-if="guest.notes" class="col-span-2">
                            <label class="block text-sm font-medium"
                                   :style="{ color: themeColors.textSecondary }">Notes</label>
                            <p class="mt-1 text-sm"
                               :style="{ color: themeColors.textPrimary }">{{ guest.notes }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Reservation History -->
                <div class="shadow rounded-lg p-6"
                     :style="{ backgroundColor: themeColors.card }">
                    <h2 class="text-lg font-semibold mb-4"
                        :style="{ color: themeColors.textPrimary }">Reservation History</h2>
                    <div v-if="guest.reservations && guest.reservations.length > 0" class="space-y-3">
                        <div v-for="reservation in guest.reservations.slice(0, 5)" :key="reservation.id"
                             class="border-b pb-3"
                             :style="{ borderColor: themeColors.border }">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-medium"
                                       :style="{ color: themeColors.textPrimary }">{{ reservation.reservation_number }}</p>
                                    <p class="text-xs"
                                       :style="{ color: themeColors.textSecondary }">
                                        {{ formatDate(reservation.check_in_date) }} – {{ formatDate(reservation.check_out_date) }}
                                    </p>
                                    <p class="text-xs"
                                       :style="{ color: themeColors.textSecondary }"
                                       v-if="reservation.room">
                                        Room {{ reservation.room.room_number }}
                                    </p>
                                </div>
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full"
                                      :style="getStatusStyle(reservation.status)">
                                    {{ formatStatus(reservation.status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm"
                       :style="{ color: themeColors.textSecondary }">No reservation history</p>
                </div>

                <!-- Quick Actions -->
                <div class="shadow rounded-lg p-6"
                     :style="{ backgroundColor: themeColors.card }">
                    <h2 class="text-lg font-semibold mb-4"
                        :style="{ color: themeColors.textPrimary }">Quick Actions</h2>
                    <div class="space-y-2">
                        <Link :href="route('front-desk.reservations.create', { guest_id: guest.id })"
                              class="block w-full text-center px-4 py-2 rounded-md transition-colors font-medium text-white"
                              :style="{ backgroundColor: themeColors.primary }">
                            Create Reservation
                        </Link>
                        <Link :href="route('front-desk.guests.edit', guest.id)"
                              class="block w-full text-center px-4 py-2 rounded-md transition-colors font-medium text-white"
                              :style="{ backgroundColor: themeColors.success }">
                            Edit Guest
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
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
    guest: Object,
    navigation: Array,
})

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
    })
}

const formatAddress = (guest) => {
    const parts = [guest.address, guest.city, guest.state, guest.country, guest.postal_code].filter(Boolean)
    return parts.length > 0 ? parts.join(', ') : 'N/A'
}

const formatStatus = (status) => {
    if (!status) return 'N/A'
    return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getStatusStyle = (status) => {
    const styles = {
        verified:        { backgroundColor: `var(--kotel-success)`, color: 'white' },
        pending:         { backgroundColor: `var(--kotel-warning)`, color: 'white' },
        flagged:         { backgroundColor: `var(--kotel-danger)`, color: 'white' },
        checked_in:      { backgroundColor: `var(--kotel-success)`, color: 'white' },
        checked_out:     { backgroundColor: `var(--kotel-secondary)`, color: 'white' },
        confirmed:       { backgroundColor: `var(--kotel-primary)`, color: 'white' },
        cancelled:       { backgroundColor: `var(--kotel-danger)`, color: 'white' },
        no_reservations: { backgroundColor: `var(--kotel-secondary)`, color: 'white' },
        no_show:         { backgroundColor: `var(--kotel-warning)`, color: 'white' },
    }
    return styles[status] || { backgroundColor: `var(--kotel-secondary)`, color: 'white' }
}
</script>
