<template>
    <DashboardLayout title="Reservations" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Reservations Management</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Manage guest reservations and bookings.</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('front-desk.reservations.create')" 
                          class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity"
                          :style="{ backgroundColor: themeColors.primary, color: '#000' }">
                        <PlusIcon class="h-4 w-4 mr-2 inline" />
                        New Reservation
                    </Link>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <CalendarDaysIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.primary }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Today's Arrivals</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ reservationStats.arrivals }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <ArrowRightOnRectangleIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.success }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Today's Departures</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ reservationStats.departures }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <ClockIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.warning }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Pending Check-ins</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ reservationStats.pendingCheckins }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <HomeIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.secondary }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Occupied Rooms</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ reservationStats.occupiedRooms }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <Link :href="route('front-desk.reservations.arrivals')" 
                  class="rounded-lg p-4 transition-colors border"
                  :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                  @mouseenter="$event.currentTarget.style.backgroundColor = themeColors.hover"
                  @mouseleave="$event.currentTarget.style.backgroundColor = themeColors.card">
                <div class="flex items-center">
                    <ArrowDownOnSquareIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.primary }" />
                    <div>
                        <h3 class="font-medium" :style="{ color: themeColors.textPrimary }">Today's Arrivals</h3>
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">Check-in guests arriving today</p>
                    </div>
                </div>
            </Link>

            <Link :href="route('front-desk.reservations.departures')" 
                  class="rounded-lg p-4 transition-colors border"
                  :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                  @mouseenter="$event.currentTarget.style.backgroundColor = themeColors.hover"
                  @mouseleave="$event.currentTarget.style.backgroundColor = themeColors.card">
                <div class="flex items-center">
                    <ArrowUpOnSquareIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.success }" />
                    <div>
                        <h3 class="font-medium" :style="{ color: themeColors.textPrimary }">Today's Departures</h3>
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">Check-out guests leaving today</p>
                    </div>
                </div>
            </Link>

            <Link :href="route('front-desk.room-assignment')" 
                  class="rounded-lg p-4 transition-colors border"
                  :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                  @mouseenter="$event.currentTarget.style.backgroundColor = themeColors.hover"
                  @mouseleave="$event.currentTarget.style.backgroundColor = themeColors.card">
                <div class="flex items-center">
                    <HomeIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.secondary }" />
                    <div>
                        <h3 class="font-medium" :style="{ color: themeColors.textPrimary }">Room Assignment</h3>
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">Assign rooms to reservations</p>
                    </div>
                </div>
            </Link>
        </div>

        <!-- Reservations Table -->
        <div class="shadow rounded-lg overflow-hidden border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">All Reservations</h3>
                    <div class="flex space-x-2">
                        <select v-model="selectedStatus" 
                                class="border rounded-md px-3 py-1 text-sm focus:outline-none"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                            <option value="">All Status</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="checked_in">Checked In</option>
                            <option value="checked_out">Checked Out</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Confirmation
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Guest
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Dates
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Room
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Guests
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="reservation in filteredReservations" :key="reservation.id" class="transition-colors" :style="hoveredRow === reservation.id ? { backgroundColor: themeColors.hover } : {}" @mouseenter="hoveredRow = reservation.id" @mouseleave="hoveredRow = null">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                {{ reservation.confirmation_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ reservation.guest_name }}</div>
                                <div class="text-sm" :style="{ color: themeColors.textSecondary }">{{ reservation.guest_email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm" :style="{ color: themeColors.textPrimary }">{{ formatDate(reservation.check_in_date) }}</div>
                                <div class="text-sm" :style="{ color: themeColors.textSecondary }">to {{ formatDate(reservation.check_out_date) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm" :style="{ color: themeColors.textPrimary }">{{ reservation.room_number || 'TBA' }}</div>
                                <div class="text-sm" :style="{ color: themeColors.textSecondary }">{{ reservation.room_type }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ reservation.adults }} adults, {{ reservation.children }} children
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="getStatusPillStyle(reservation.status)">
                                    {{ formatStatus(reservation.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <Link :href="route('front-desk.reservations.show', reservation.id)" class="hover:opacity-80 transition-opacity" :style="{ color: themeColors.primary }">View</Link>
                                    <button v-if="reservation.status === 'confirmed'" 
                                            @click="checkIn(reservation)" 
                                            class="hover:opacity-80 transition-opacity" :style="{ color: themeColors.success }">Check In</button>
                                    <button v-if="reservation.status === 'checked_in'" 
                                            @click="checkOut(reservation)" 
                                            class="hover:opacity-80 transition-opacity" :style="{ color: themeColors.warning }">Check Out</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="paginationLinks.length > 1" class="px-6 py-4 border-t flex flex-col md:flex-row md:items-center md:justify-between gap-3" :style="{ borderColor: themeColors.border }">
                <div class="text-sm" :style="{ color: themeColors.textSecondary }">
                    Showing {{ paginationMeta.from }} to {{ paginationMeta.to }} of {{ paginationMeta.total }} reservations
                </div>
                <div class="flex items-center gap-1 flex-wrap">
                    <Link
                        v-for="(link, idx) in paginationLinks"
                        :key="idx"
                        :href="link.url || ''"
                        :class="['px-3 py-1 rounded-md text-sm border', !link.url ? 'opacity-50 cursor-not-allowed' : '']"
                        :style="link.active
                            ? { backgroundColor: themeColors.primary, color: '#000', borderColor: themeColors.primary }
                            : { backgroundColor: themeColors.card, color: themeColors.textPrimary, borderColor: themeColors.border }"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'
import {
    PlusIcon,
    CalendarDaysIcon,
    ArrowRightOnRectangleIcon,
    ClockIcon,
    HomeIcon,
    ArrowDownOnSquareIcon,
    ArrowUpOnSquareIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    reservations: [Array, Object],
    reservationStats: Object,
})

const navigation = computed(() => getNavigationForRole('front_desk'))
const selectedStatus = ref('')
const hoveredRow = ref(null)

const { currentTheme } = useTheme()

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
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)'
}))

const filteredReservations = computed(() => {
    if (!selectedStatus.value) return reservationsData.value
    return reservationsData.value.filter(reservation => reservation.status === selectedStatus.value)
})

const reservationsData = computed(() => {
    if (Array.isArray(props.reservations)) return props.reservations
    return props.reservations?.data || []
})

const paginationLinks = computed(() => {
    if (Array.isArray(props.reservations)) return []
    return props.reservations?.links || []
})

const paginationMeta = computed(() => {
    if (Array.isArray(props.reservations)) {
        return {
            from: reservationsData.value.length ? 1 : 0,
            to: reservationsData.value.length,
            total: reservationsData.value.length,
        }
    }

    return {
        from: props.reservations?.from || 0,
        to: props.reservations?.to || 0,
        total: props.reservations?.total || 0,
    }
})

const getStatusColor = (status) => {
    const colors = {
        confirmed: 'bg-blue-100 text-blue-800',
        checked_in: 'bg-green-100 text-green-800',
        checked_out: 'bg-gray-100 text-gray-800',
        cancelled: 'bg-red-100 text-red-800'
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const getStatusPillStyle = (status) => {
    const key = (status || '').toLowerCase()
    if (key === 'confirmed') return { backgroundColor: themeColors.value.primary, color: '#000' }
    if (key === 'checked_in') return { backgroundColor: themeColors.value.success, color: '#000' }
    if (key === 'checked_out') return { backgroundColor: themeColors.value.border, color: themeColors.value.textPrimary }
    if (key === 'cancelled') return { backgroundColor: themeColors.value.danger, color: '#000' }
    return { backgroundColor: themeColors.value.border, color: themeColors.value.textPrimary }
}

const formatStatus = (status) => {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const viewReservation = (reservation) => {
    alert(`View reservation: ${reservation.confirmation_number}`)
}

const checkIn = (reservation) => {
    router.visit(`/front-desk/checkin?reservation_id=${reservation.id}`)
}

const checkOut = (reservation) => {
    router.visit(`/front-desk/checkout?reservation_id=${reservation.id}`)
}
</script>
