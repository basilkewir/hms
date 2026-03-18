<template>
    <DashboardLayout title="Room Assignment" :user="user">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Room Assignment</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Assign rooms to confirmed reservations and manage room availability.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('manager.reservations.create')" 
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        New Reservation
                    </Link>
                    <button @click="refreshData" 
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{ 
                                backgroundColor: '#8b5cf6',
                            }"
                            @mouseenter="$event.target.style.backgroundColor = '#7c3aed'"
                            @mouseleave="$event.target.style.backgroundColor = '#8b5cf6'">
                        <ArrowPathIcon class="h-4 w-4 mr-2" />
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Assignment Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(250, 204, 21, 0.1)' }">
                        <ClockIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Pending Assignments</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ assignmentStats.pending }}</p>
                    </div>
                </div>
            </div>
            
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                        <CheckCircleIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Available Rooms</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ assignmentStats.available }}</p>
                    </div>
                </div>
            </div>
            
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                        <HomeIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Rooms</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ assignmentStats.totalRooms }}</p>
                    </div>
                </div>
            </div>
            
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(139, 92, 246, 0.1)' }">
                        <CalendarDaysIcon class="h-6 w-6" :style="{ color: '#8b5cf6' }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Today's Check-ins</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ assignmentStats.todayCheckins }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Assignment Area -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Pending Reservations -->
            <div class="rounded-lg overflow-hidden shadow"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <!-- Table Header -->
                <div class="px-6 py-4 border-b"
                     :style="{ 
                         borderColor: themeColors.border,
                         borderBottomWidth: '1px'
                     }">
                    <h3 class="text-lg font-medium"
                        :style="{ color: themeColors.textPrimary }">Pending Room Assignments</h3>
                    <p class="text-sm mt-1"
                       :style="{ color: themeColors.textSecondary }">Reservations waiting for room assignment</p>
                </div>
                
                <!-- Reservations List -->
                <div class="max-h-96 overflow-y-auto">
                    <div v-if="pendingReservations.length === 0" class="p-8 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center"
                             :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                            <CheckCircleIcon class="h-8 w-8" :style="{ color: themeColors.success }" />
                        </div>
                        <p class="text-lg font-medium mb-2"
                           :style="{ color: themeColors.textPrimary }">All Caught Up!</p>
                        <p class="text-sm"
                           :style="{ color: themeColors.textSecondary }">No pending room assignments</p>
                    </div>
                    
                    <div v-else class="divide-y"
                         :style="{ borderColor: themeColors.border }">
                        <div v-for="reservation in pendingReservations" :key="reservation.id"
                             class="p-4 transition-colors cursor-pointer hover:bg-gray-50"
                             :style="{ borderBottomStyle: 'solid', borderBottomWidth: '1px' }"
                             :class="{ 'ring-2 ring-blue-500': selectedReservation?.id === reservation.id }"
                             @click="selectReservation(reservation)">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <p class="font-semibold text-sm"
                                           :style="{ color: themeColors.textPrimary }">{{ reservation.guest?.full_name }}</p>
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                        <div>
                                            <span class="font-medium"
                                                  :style="{ color: themeColors.textSecondary }">Room Type:</span>
                                            <span class="ml-1"
                                                  :style="{ color: themeColors.textPrimary }">{{ reservation.room_type?.name }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium"
                                                  :style="{ color: themeColors.textSecondary }">Check-in:</span>
                                            <span class="ml-1"
                                                  :style="{ color: themeColors.textPrimary }">{{ formatDate(reservation.check_in_date) }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium"
                                                  :style="{ color: themeColors.textSecondary }">Nights:</span>
                                            <span class="ml-1"
                                                  :style="{ color: themeColors.textPrimary }">{{ reservation.nights || 1 }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium"
                                                  :style="{ color: themeColors.textSecondary }">Guests:</span>
                                            <span class="ml-1"
                                                  :style="{ color: themeColors.textPrimary }">
                                                {{ (reservation.number_of_adults || 1) + (reservation.number_of_children || 0) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-sm">
                                        <span class="font-medium"
                                              :style="{ color: themeColors.textSecondary }">Total:</span>
                                        <span class="ml-1 font-semibold"
                                              :style="{ color: themeColors.primary }">
                                            {{ formatCurrency(reservation.total_amount) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4 flex gap-2">
                                    <button @click.stop="selectReservation(reservation)"
                                            class="px-3 py-1 text-sm rounded-md transition-colors font-medium text-white"
                                            :style="{ 
                                                backgroundColor: themeColors.primary,
                                            }"
                                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                                        Select
                                    </button>
                                    <Link :href="route('manager.reservations.show', reservation.id)"
                                          class="px-3 py-1 text-sm rounded-md transition-colors font-medium text-white"
                                          :style="{ 
                                              backgroundColor: '#8b5cf6',
                                          }"
                                          @mouseenter="$event.target.style.backgroundColor = '#7c3aed'"
                                          @mouseleave="$event.target.style.backgroundColor = '#8b5cf6'">
                                        View
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Rooms -->
            <div class="rounded-lg overflow-hidden shadow"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <!-- Table Header -->
                <div class="px-6 py-4 border-b"
                     :style="{ 
                         borderColor: themeColors.border,
                         borderBottomWidth: '1px'
                     }">
                    <h3 class="text-lg font-medium"
                        :style="{ color: themeColors.textPrimary }">Available Rooms</h3>
                    <p class="text-sm mt-1"
                       :style="{ color: themeColors.textSecondary }">
                        {{ selectedReservation ? `Matching ${selectedReservation.room_type?.name}` : 'Clean and ready rooms' }}
                    </p>
                </div>
                
                <!-- Rooms List -->
                <div class="max-h-96 overflow-y-auto">
                    <div v-if="filteredRooms.length === 0" class="p-8 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center"
                             :style="{ backgroundColor: 'rgba(239, 68, 68, 0.1)' }">
                            <XCircleIcon class="h-8 w-8" :style="{ color: themeColors.danger }" />
                        </div>
                        <p class="text-lg font-medium mb-2"
                           :style="{ color: themeColors.textPrimary }">No Available Rooms</p>
                        <p class="text-sm"
                           :style="{ color: themeColors.textSecondary }">
                            {{ selectedReservation ? 'No rooms match the selected reservation type' : 'No clean rooms available' }}
                        </p>
                    </div>
                    
                    <div v-else class="divide-y"
                         :style="{ borderColor: themeColors.border }">
                        <div v-for="room in filteredRooms" :key="room.id"
                             class="p-4 transition-colors cursor-pointer hover:bg-gray-50"
                             :style="{ borderBottomStyle: 'solid', borderBottomWidth: '1px' }"
                             @click="assignRoom(room)">
                            <div class="flex justify-between items-center">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <p class="font-semibold text-sm"
                                           :style="{ color: themeColors.textPrimary }">Room {{ room.room_number }}</p>
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Available
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                        <div>
                                            <span class="font-medium"
                                                  :style="{ color: themeColors.textSecondary }">Type:</span>
                                            <span class="ml-1"
                                                  :style="{ color: themeColors.textPrimary }">{{ room.room_type?.name }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium"
                                                  :style="{ color: themeColors.textSecondary }">Floor:</span>
                                            <span class="ml-1"
                                                  :style="{ color: themeColors.textPrimary }">{{ room.floor_name || 'N/A' }}</span>
                                        </div>
                                        <div v-if="room.building">
                                            <span class="font-medium"
                                                  :style="{ color: themeColors.textSecondary }">Building:</span>
                                            <span class="ml-1"
                                                  :style="{ color: themeColors.textPrimary }">{{ room.building }}</span>
                                        </div>
                                        <div v-if="room.wing">
                                            <span class="font-medium"
                                                  :style="{ color: themeColors.textSecondary }">Wing:</span>
                                            <span class="ml-1"
                                                  :style="{ color: themeColors.textPrimary }">{{ room.wing }}</span>
                                        </div>
                                    </div>
                                    <div v-if="room.features && room.features.length > 0" class="mt-2">
                                        <p class="text-xs font-medium mb-1"
                                           :style="{ color: themeColors.textSecondary }">Features:</p>
                                        <div class="flex flex-wrap gap-1">
                                            <span v-for="feature in room.features.slice(0, 3)" :key="feature"
                                                  class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-800">
                                                {{ feature }}
                                            </span>
                                            <span v-if="room.features.length > 3" class="text-xs text-gray-500">
                                                +{{ room.features.length - 3 }} more
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-4 flex gap-2">
                                    <button @click.stop="assignRoom(room)"
                                            :disabled="!selectedReservation"
                                            class="px-3 py-1 text-sm rounded-md transition-colors font-medium text-white disabled:opacity-50 disabled:cursor-not-allowed"
                                            :style="{ 
                                                backgroundColor: selectedReservation ? themeColors.success : '#9ca3af',
                                            }"
                                            @mouseenter="$event.target.style.backgroundColor = selectedReservation ? '#059669' : '#9ca3af'"
                                            @mouseleave="$event.target.style.backgroundColor = selectedReservation ? themeColors.success : '#9ca3af'">
                                        Assign
                                    </button>
                                    <Link :href="route('manager.rooms.show', room.id)"
                                          class="px-3 py-1 text-sm rounded-md transition-colors font-medium text-white"
                                          :style="{ 
                                              backgroundColor: '#8b5cf6',
                                          }"
                                          @mouseenter="$event.target.style.backgroundColor = '#7c3aed'"
                                          @mouseleave="$event.target.style.backgroundColor = '#8b5cf6'">
                                        Details
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selected Reservation Info -->
        <div v-if="selectedReservation" class="mt-8 rounded-lg p-6 border shadow-sm"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Selected Reservation</h3>
                <div class="flex gap-2">
                    <Link :href="route('manager.reservations.show', selectedReservation.id)"
                          class="px-3 py-1 text-sm rounded-md transition-colors font-medium text-white"
                          :style="{ 
                              backgroundColor: '#8b5cf6',
                          }"
                          @mouseenter="$event.target.style.backgroundColor = '#7c3aed'"
                          @mouseleave="$event.target.style.backgroundColor = '#8b5cf6'">
                        View Details
                    </Link>
                    <button @click="selectedReservation = null"
                            class="text-sm transition-colors"
                            :style="{ color: themeColors.danger }"
                            @mouseenter="$event.target.style.color = '#dc2626'"
                            @mouseleave="$event.target.style.color = themeColors.danger">
                        Clear Selection
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm font-medium mb-1"
                       :style="{ color: themeColors.textSecondary }">Guest</p>
                    <p class="font-semibold"
                       :style="{ color: themeColors.textPrimary }">{{ selectedReservation.guest?.full_name }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium mb-1"
                       :style="{ color: themeColors.textSecondary }">Room Type</p>
                    <p class="font-semibold"
                       :style="{ color: themeColors.textPrimary }">{{ selectedReservation.room_type?.name }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium mb-1"
                       :style="{ color: themeColors.textSecondary }">Stay Duration</p>
                    <p class="font-semibold"
                       :style="{ color: themeColors.textPrimary }">
                        {{ formatDate(selectedReservation.check_in_date) }} - {{ formatDate(selectedReservation.check_out_date) }}
                    </p>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'
import {
    ArrowPathIcon,
    CalendarDaysIcon,
    CheckCircleIcon,
    ClockIcon,
    HomeIcon,
    PlusIcon,
    XCircleIcon
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
    hover: `rgba(255, 255, 255, 0.1)`
}))

// Load theme on mount
loadTheme()

const props = defineProps({
    user: Object,
    pendingReservations: { type: Array, default: () => [] },
    availableRooms: { type: Array, default: () => [] },
    stats: Object,
})

const selectedReservation = ref(null)

// Computed properties
const assignmentStats = computed(() => ({
    pending: props.stats?.pending || 0,
    available: props.stats?.available || 0,
    totalRooms: props.stats?.totalRooms || 0,
    todayCheckins: props.stats?.todayCheckins || 0,
    occupancyRate: props.stats?.occupancyRate || 0,
    cleanRooms: props.stats?.cleanRooms || 0,
    dirtyRooms: props.stats?.dirtyRooms || 0,
}))

const filteredRooms = computed(() => {
    if (!selectedReservation.value) return props.availableRooms
    return props.availableRooms.filter(room => 
        room.room_type_id === selectedReservation.value.room_type_id
    )
})

// Methods
const selectReservation = (reservation) => {
    selectedReservation.value = reservation
}

const assignRoom = (room) => {
    if (!selectedReservation.value) {
        showNotification('Please select a reservation first', 'error')
        return
    }
    
    if (confirm(`Assign Room ${room.room_number} to ${selectedReservation.value.guest?.full_name}?`)) {
        router.post(route('manager.room-assignment.assign'), {
            reservation_id: selectedReservation.value.id,
            room_id: room.id,
        }, {
            onSuccess: () => {
                selectedReservation.value = null
                showNotification('Room assigned successfully', 'success')
            },
            onError: () => {
                showNotification('Failed to assign room', 'error')
            }
        })
    }
}

const refreshData = () => {
    router.reload({
        only: ['pendingReservations', 'availableRooms'],
        onSuccess: () => {
            showNotification('Data refreshed successfully', 'success')
        }
    })
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    })
}

const showNotification = (message, type = 'info') => {
    // Create notification element
    const notification = document.createElement('div')
    notification.textContent = message
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 text-white font-medium transition-all duration-300`
    
    // Set background color based on type
    if (type === 'success') {
        notification.style.backgroundColor = '#10b981'
    } else if (type === 'error') {
        notification.style.backgroundColor = '#ef4444'
    } else {
        notification.style.backgroundColor = '#3b82f6'
    }
    
    // Add to page
    document.body.appendChild(notification)
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0'
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification)
            }
        }, 300)
    }, 3000)
}
</script>

<style scoped>
/* Status badge colors - Ensure they override theme */
.bg-yellow-100 {
    background-color: rgb(254 249 195) !important;
}

.text-yellow-800 {
    color: rgb(133 77 14) !important;
}

.bg-green-100 {
    background-color: rgb(220 252 231) !important;
}

.text-green-800 {
    color: rgb(22 101 52) !important;
}

.bg-blue-100 {
    background-color: rgb(219 234 254) !important;
}

.text-blue-800 {
    color: rgb(30 64 175) !important;
}

/* Hover states */
.hover\:bg-gray-50:hover {
    background-color: rgba(249, 250, 251, 0.5) !important;
}

/* Custom scrollbar */
.max-h-96::-webkit-scrollbar {
    width: 6px;
}

.max-h-96::-webkit-scrollbar-track {
    background: var(--kotel-background);
}

.max-h-96::-webkit-scrollbar-thumb {
    background: var(--kotel-border);
    border-radius: 3px;
}

.max-h-96::-webkit-scrollbar-thumb:hover {
    background: var(--kotel-text-tertiary);
}
</style>
