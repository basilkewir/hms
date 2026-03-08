<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme.js'
import {
    UserIcon,
    CalendarDaysIcon,
    HomeIcon,
    CreditCardIcon,
    CheckCircleIcon,
    ClockIcon,
    MagnifyingGlassIcon,
    KeyIcon,
    XMarkIcon,
    UserPlusIcon,
} from '@heroicons/vue/24/outline'

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
    hover: `var(--kotel-primary-hover)`
}))

// Load theme on mount
loadTheme()

// Props from backend
const props = defineProps({
    user: Object,
    navigation: Array,
    pendingReservations: Array,
    availableRooms: Array,
    todayCheckIns: Array,
})

// Data
const searchQuery = ref('')
const selectedReservation = ref(null)
const loading = ref(false)

const checkInForm = ref({
    roomNumber: '',
    keyCardId: '',
})

const navigation = computed(() => getNavigationForRole('admin'))

// Computed properties
const filteredReservations = computed(() => {
    const reservations = props.pendingReservations || []
    if (!searchQuery.value) return reservations
    const query = searchQuery.value.toLowerCase()
    return reservations.filter(reservation =>
        reservation.guest_name?.toLowerCase().includes(query) ||
        reservation.reservation_number?.toLowerCase().includes(query) ||
        reservation.room?.number?.toLowerCase().includes(query)
    )
})

const todaysArrivals = computed(() => {
    return props.todayCheckIns || []
})

const todayCheckins = computed(() => {
    return (props.todayCheckIns || []).filter(r => r.status === 'confirmed' || r.status === 'pending')
})

const checkedInToday = computed(() => {
    return (props.todayCheckIns || []).filter(r => r.status === 'checked_in')
})

// Filtered available rooms (avoiding duplication of reserved room)
const filteredAvailableRooms = computed(() => {
    if (!selectedReservation.value) return props.availableRooms || []

    const reservedRoomNumber = selectedReservation.value.roomNumber || selectedReservation.value.room?.number
    if (!reservedRoomNumber || reservedRoomNumber === 'TBA') {
        return props.availableRooms || []
    }

    return (props.availableRooms || []).filter(room => room.number !== reservedRoomNumber)
})

// Auto-select reservation if provided
onMounted(() => {
    if (props.selectedReservationId) {
        const reservation = props.todaysArrivals?.find(r => r.id === props.selectedReservationId) ||
                          props.allReservations?.find(r => r.id === props.selectedReservationId)
        if (reservation) {
            selectReservation(reservation)
        }
    }
})

// Methods
const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const formatTime = (time) => {
    if (!time) return 'N/A'
    try {
        const date = new Date(time)
        return date.toLocaleDateString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        })
    } catch (e) {
        return time
    }
}

const getStatusColor = (status) => {
    const colors = {
        'confirmed': 'bg-blue-100 text-blue-800',
        'checked_in': 'bg-green-100 text-green-800',
        'checked_out': 'bg-gray-100 text-gray-800',
        'cancelled': 'bg-red-100 text-red-800',
        'pending': 'bg-yellow-100 text-yellow-800',
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const selectReservation = (reservation) => {
    selectedReservation.value = reservation
    // Set the reserved room if it exists (check both roomNumber and room.number)
    const roomNumber = reservation.roomNumber || reservation.room?.number
    if (roomNumber && roomNumber !== 'TBA') {
        checkInForm.value.roomNumber = roomNumber
    } else {
        checkInForm.value.roomNumber = ''
    }
    checkInForm.value.keyCardId = ''
}

const performCheckin = () => {
    if (!checkInForm.value.roomNumber) {
        alert('Please select a room')
        return
    }

    loading.value = true

    router.post(route('admin.checkin.store'), {
        reservation_id: selectedReservation.value.id,
        room_number: checkInForm.value.roomNumber,
        key_card_id: checkInForm.value.keyCardId || null,
    }, {
        onSuccess: () => {
            loading.value = false
            selectedReservation.value = null
        },
        onError: () => {
            loading.value = false
        }
    })
}

const assignRoom = (reservation) => {
    selectReservation(reservation)
}
</script>

<template>
    <Head title="Check-in Management" />

    <DashboardLayout :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Check-in Management</h1>
                    <p class="mt-1"
                       :style="{ color: themeColors.textSecondary }">Manage guest check-ins and room assignments</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('admin.reservations.create')"
                          class="px-4 py-2 rounded-md transition-colors inline-flex items-center"
                          :style="{
                              backgroundColor: themeColors.primary,
                              color: '#ffffff'
                          }">
                        <UserPlusIcon class="h-4 w-4 mr-2" />
                        Walk-in Guest
                    </Link>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="shadow rounded-lg p-6"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <CalendarDaysIcon class="h-8 w-8" :style="{ color: themeColors.primary }" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Today's Check-ins</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">
                            {{ todayCheckins.length }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="shadow rounded-lg p-6"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <ClockIcon class="h-8 w-8" :style="{ color: themeColors.warning }" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Pending Check-ins</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">
                            {{ filteredReservations.length }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="shadow rounded-lg p-6"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <CheckCircleIcon class="h-8 w-8" :style="{ color: themeColors.success }" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Checked-in Today</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">
                            {{ checkedInToday.length }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Arrivals -->
        <div class="shadow rounded-lg p-6 mb-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <h3 class="text-lg font-medium mb-4"
                :style="{ color: themeColors.textPrimary }">Today's Expected Arrivals</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="arrival in todaysArrivals" :key="arrival.id"
                     class="rounded-lg p-4 cursor-pointer transition-colors"
                     :style="{
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }"
                     @click="selectReservation(arrival)"
                     @mouseenter="$event.target.style.borderColor = themeColors.primary"
                     @mouseleave="$event.target.style.borderColor = themeColors.border">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium"
                            :style="{ color: themeColors.textPrimary }">{{ arrival.guest_name }}</h4>
                        <span class="text-xs px-2 py-1 rounded-full"
                              :class="getStatusColor(arrival.status)">
                            {{ arrival.status }}
                        </span>
                    </div>
                    <div class="text-sm space-y-1"
                         :style="{ color: themeColors.textSecondary }">
                        <p><strong :style="{ color: themeColors.textPrimary }">Room:</strong> {{ arrival.room?.number || 'TBA' }}</p>
                        <p><strong :style="{ color: themeColors.textPrimary }">Nights:</strong> {{ arrival.nights || '-' }}</p>
                        <p><strong :style="{ color: themeColors.textPrimary }">Guests:</strong> {{ arrival.guests_count || 1 }}</p>
                        <p><strong :style="{ color: themeColors.textPrimary }">Arrival:</strong> {{ formatTime(arrival.check_in_date) }}</p>
                    </div>
                    <div class="mt-3">
                        <button v-if="arrival.status === 'confirmed' || arrival.status === 'pending'"
                                @click.stop="selectReservation(arrival)"
                                class="w-full px-3 py-2 rounded-md text-sm transition-colors"
                                :style="{
                                    backgroundColor: themeColors.primary,
                                    color: '#ffffff'
                                }">
                            Start Check-In
                        </button>
                        <button v-else-if="arrival.status === 'checked_in'"
                                class="w-full px-3 py-2 rounded-md text-sm cursor-not-allowed"
                                :style="{
                                    backgroundColor: themeColors.success,
                                    color: '#ffffff',
                                    opacity: '0.7'
                                }">
                            Checked In
                        </button>
                    </div>
                </div>
            </div>
            <div v-if="todaysArrivals.length === 0" class="text-center py-8"
                 :style="{ color: themeColors.textTertiary }">
                No arrivals scheduled for today.
            </div>
        </div>

        <!-- Search and All Reservations -->
        <div class="shadow rounded-lg p-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">All Pending Check-ins</h3>
                <div class="relative">
                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5"
                                         :style="{ color: themeColors.textTertiary }" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search reservations..."
                        class="pl-10 pr-4 py-2 rounded-md focus:outline-none transition-colors"
                        :style="{
                            backgroundColor: themeColors.background,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary,
                            borderWidth: '1px',
                            borderStyle: 'solid'
                        }"
                    />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ borderBottom: `1px solid ${themeColors.border}` }">
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Reservation
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Guest
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Room
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Dates
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Amount
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Status
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="reservation in filteredReservations" :key="reservation.id"
                            class="cursor-pointer transition-colors"
                            :style="{ borderBottom: `1px solid ${themeColors.border}` }"
                            @click="selectReservation(reservation)"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.background"
                            @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium"
                                     :style="{ color: themeColors.textPrimary }">
                                    {{ reservation.reservation_number }}
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <UserIcon class="h-5 w-5 mr-2" :style="{ color: themeColors.textTertiary }" />
                                    <div>
                                        <div class="text-sm font-medium"
                                             :style="{ color: themeColors.textPrimary }">
                                            {{ reservation.guest_name }}
                                        </div>
                                        <div class="text-sm" :style="{ color: themeColors.textTertiary }">
                                            {{ reservation.guests_count || 1 }} guests
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <HomeIcon class="h-5 w-5 mr-2" :style="{ color: themeColors.textTertiary }" />
                                    <div>
                                        <div class="text-sm font-medium"
                                             :style="{ color: themeColors.textPrimary }">
                                            Room {{ reservation.room?.number || 'TBA' }}
                                        </div>
                                        <div class="text-sm" :style="{ color: themeColors.textTertiary }">
                                            {{ reservation.room_type?.name || '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ formatDate(reservation.check_in_date) }}
                                </div>
                                <div class="text-sm" :style="{ color: themeColors.textTertiary }">
                                    to {{ formatDate(reservation.check_out_date) }}
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium"
                                     :style="{ color: themeColors.textPrimary }">
                                    {{ formatCurrency(reservation.total_amount) }}
                                </div>
                                <div class="text-sm" :style="{ color: themeColors.textTertiary }">
                                    Balance: {{ formatCurrency(reservation.balance_amount) }}
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                      :class="getStatusColor(reservation.status)">
                                    {{ reservation.status.replace('_', ' ').toUpperCase() }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                <button
                                    @click.stop="selectReservation(reservation)"
                                    class="inline-flex items-center px-3 py-1 rounded-md text-sm transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.primary,
                                        color: '#ffffff'
                                    }">
                                    <CheckCircleIcon class="h-4 w-4 mr-1" />
                                    Check-in
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="filteredReservations.length === 0" class="text-center py-8">
                    <CheckCircleIcon class="mx-auto h-12 w-12" :style="{ color: themeColors.textTertiary }" />
                    <h3 class="mt-2 text-sm font-medium" :style="{ color: themeColors.textPrimary }">No pending check-ins</h3>
                    <p class="mt-1 text-sm" :style="{ color: themeColors.textSecondary }">
                        All guests have been checked in or there are no reservations.
                    </p>
                </div>
            </div>
        </div>

        <!-- Check-In Form Modal -->
        <div v-if="selectedReservation"
             class="fixed inset-0 flex items-center justify-center z-50"
             :style="{ backgroundColor: 'rgba(0, 0, 0, 0.5)' }">
            <div class="rounded-lg max-w-2xl w-full mx-4 max-h-screen overflow-y-auto"
                 :style="{ backgroundColor: themeColors.card }">
                <div class="px-6 py-4 flex items-center justify-between"
                     :style="{ borderBottom: `1px solid ${themeColors.border}` }">
                    <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">
                        Check-In: {{ selectedReservation.guest_name }}
                    </h3>
                    <button @click="selectedReservation = null"
                            :style="{ color: themeColors.textSecondary }"
                            @mouseenter="$event.target.style.color = themeColors.textPrimary"
                            @mouseleave="$event.target.style.color = themeColors.textSecondary">
                        <XMarkIcon class="h-5 w-5" />
                    </button>
                </div>

                <div class="px-6 py-4">
                    <!-- Room Status Info -->
                    <div v-if="selectedReservation.reservedRoomAvailable && (selectedReservation.roomNumber || selectedReservation.room?.number) && (selectedReservation.roomNumber || selectedReservation.room?.number) !== 'TBA'"
                         class="mb-4 p-3 rounded-md"
                         :style="{
                             backgroundColor: 'rgba(34, 197, 94, 0.1)',
                             borderColor: themeColors.success,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <p class="text-sm" :style="{ color: themeColors.success }">
                            <strong>Reserved Room:</strong> Room {{ selectedReservation.roomNumber || selectedReservation.room?.number }} is available and clean.
                            This room will be automatically assigned.
                        </p>
                    </div>
                    <div v-else-if="(selectedReservation.roomNumber || selectedReservation.room?.number) && (selectedReservation.roomNumber || selectedReservation.room?.number) !== 'TBA'"
                         class="mb-4 p-3 rounded-md"
                         :style="{
                             backgroundColor: 'rgba(251, 191, 36, 0.1)',
                             borderColor: themeColors.warning,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <p class="text-sm" :style="{ color: themeColors.warning }">
                            <strong>Note:</strong> Reserved room {{ selectedReservation.roomNumber || selectedReservation.room?.number }} is not available or not clean.
                            Please select an alternative room.
                        </p>
                    </div>

                    <!-- Reservation Details -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <h4 class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Reservation Number</h4>
                            <p class="text-sm" :style="{ color: themeColors.textPrimary }">{{ selectedReservation.reservation_number }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Guest Name</h4>
                            <p class="text-sm" :style="{ color: themeColors.textPrimary }">{{ selectedReservation.guest_name }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Room Type</h4>
                            <p class="text-sm" :style="{ color: themeColors.textPrimary }">{{ selectedReservation.room_type?.name || selectedReservation.room_type || 'N/A' }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Guests</h4>
                            <p class="text-sm" :style="{ color: themeColors.textPrimary }">{{ selectedReservation.guests_count || 1 }} guests</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Check-in Date</h4>
                            <p class="text-sm" :style="{ color: themeColors.textPrimary }">{{ formatDate(selectedReservation.check_in_date) }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Check-out Date</h4>
                            <p class="text-sm" :style="{ color: themeColors.textPrimary }">{{ formatDate(selectedReservation.check_out_date) }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Total Amount</h4>
                            <p class="text-sm" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(selectedReservation.total_amount) }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Balance Amount</h4>
                            <p class="text-sm" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(selectedReservation.balance_amount) }}</p>
                        </div>
                    </div>

                    <!-- Room Assignment Form -->
                    <form @submit.prevent="performCheckin" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textPrimary }">Room Number *</label>
                            <select v-model="checkInForm.roomNumber" required
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="">Select Room</option>
                                <!-- Show reserved room first if it exists -->
                                <option v-if="(selectedReservation.roomNumber || selectedReservation.room?.number) && (selectedReservation.roomNumber || selectedReservation.room?.number) !== 'TBA'"
                                        :value="selectedReservation.roomNumber || selectedReservation.room?.number">
                                    {{ selectedReservation.roomNumber || selectedReservation.room?.number }} - {{ selectedReservation.room_type?.name || selectedReservation.room_type || 'Standard' }} (Reserved)
                                </option>
                                <!-- Show other available rooms -->
                                <option v-for="room in filteredAvailableRooms"
                                        :key="room.id"
                                        :value="room.number">
                                    {{ room.number }} - {{ room.type || room.room_type?.name || 'Standard' }}
                                </option>
                            </select>
                        </div>

                        <!-- Key Card Assignment -->
                        <div v-if="availableKeyCards && availableKeyCards.length > 0">
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textPrimary }">Key Card (Optional)</label>
                            <select v-model="checkInForm.keyCardId"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="">Select Key Card</option>
                                <option v-for="card in availableKeyCards" :key="card.id" :value="card.id">
                                    {{ card.card_number }} - {{ card.card_type }}
                                </option>
                            </select>
                        </div>

                        <div class="flex items-center justify-end space-x-3 pt-4"
                             :style="{ borderTop: `1px solid ${themeColors.border}` }">
                            <button type="button" @click="selectedReservation = null"
                                    class="px-4 py-2 rounded-md transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        color: themeColors.textPrimary,
                                        borderColor: themeColors.border,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                Cancel
                            </button>
                            <button type="submit"
                                    :disabled="loading"
                                    class="px-4 py-2 rounded-md transition-colors"
                                    :style="{
                                        backgroundColor: loading ? themeColors.border : themeColors.primary,
                                        color: '#ffffff',
                                        opacity: loading ? 0.7 : 1
                                    }">
                                <span v-if="loading">Processing...</span>
                                <span v-else>Complete Check-In</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

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
</style>
