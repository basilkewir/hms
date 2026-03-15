<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import {
    UserIcon,
    CalendarDaysIcon,
    HomeIcon,
    CreditCardIcon,
    CheckCircleIcon,
    ClockIcon,
    MagnifyingGlassIcon,
    DocumentTextIcon,
    KeyIcon,
    BellIcon,
    PlusIcon,
    DocumentArrowDownIcon
} from '@heroicons/vue/24/outline'

// Theme system
const { themeColors } = useTheme()

// Props
const props = defineProps({
    user: Object,
    navigation: Object,
    pendingReservations: Array,
    availableRooms: Array,
    todayCheckIns: Array
})

// Data
const searchQuery = ref('')
const selectedReservation = ref(null)
const loading = ref(false)

// Computed properties
const filteredReservations = computed(() => {
    if (!searchQuery.value) return props.pendingReservations
    
    const query = searchQuery.value.toLowerCase()
    return props.pendingReservations.filter(reservation => 
        reservation.guest_name.toLowerCase().includes(query) ||
        reservation.room_number.toLowerCase().includes(query) ||
        reservation.guest_email?.toLowerCase().includes(query)
    )
})

const filteredRooms = computed(() => {
    if (!searchQuery.value) return props.availableRooms
    
    const query = searchQuery.value.toLowerCase()
    return props.availableRooms.filter(room => 
        room.room_number.toLowerCase().includes(query) ||
        room.room_type.toLowerCase().includes(query)
    )
})

// Methods
const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString()
}

const formatTime = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleTimeString()
}

const formatCurrency = (amount) => {
    if (!amount) return '0 FCFA'
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF'
    }).format(amount)
}

const checkInGuest = (reservation) => {
    loading.value = true
    // In a real implementation, this would make an API call
    setTimeout(() => {
        loading.value = false
        alert(`Checking in ${reservation.guest_name} to room ${reservation.room_number}`)
    }, 1000)
}

const assignRoom = (reservation, room) => {
    // In a real implementation, this would make an API call
    alert(`Assigning ${reservation.guest_name} to room ${room.room_number}`)
}

const exportCheckIns = () => {
    alert('Exporting check-in data...')
}
</script>

<template>
    <DashboardLayout title="Check-in Management" :user="user">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Check-in Management</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Manage guest check-ins and room assignments</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link href="/admin/reservations/create" 
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        New Reservation
                    </Link>
                    <button @click="exportCheckIns" 
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{ 
                                backgroundColor: '#8b5cf6',
                            }"
                            @mouseenter="$event.target.style.backgroundColor = '#7c3aed'"
                            @mouseleave="$event.target.style.backgroundColor = '#8b5cf6'">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                        Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                  }">
                <div class="flex items-center">
                    <div class="p-3 rounded-full mr-4"
                         :style="{ backgroundColor: themeColors.primary + '20' }">
                        <CalendarDaysIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <div class="text-sm font-medium mb-2"
                            :style="{ color: themeColors.textSecondary }">Pending Check-ins</div>
                        <div class="text-3xl font-bold"
                             :style="{ color: themeColors.textPrimary }">{{ pendingReservations?.length || 0 }}</div>
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
                    <div class="p-3 rounded-full mr-4"
                         :style="{ backgroundColor: themeColors.success + '20' }">
                        <CheckCircleIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <div class="text-sm font-medium mb-2"
                            :style="{ color: themeColors.textSecondary }">Today's Check-ins</div>
                        <div class="text-3xl font-bold"
                             :style="{ color: themeColors.textPrimary }">{{ todayCheckIns?.length || 0 }}</div>
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
                    <div class="p-3 rounded-full mr-4"
                         :style="{ backgroundColor: themeColors.warning + '20' }">
                        <HomeIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <div class="text-sm font-medium mb-2"
                            :style="{ color: themeColors.textSecondary }">Available Rooms</div>
                        <div class="text-3xl font-bold"
                             :style="{ color: themeColors.textPrimary }">{{ availableRooms?.length || 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
              }">
            <div class="relative">
                <MagnifyingGlassIcon class="absolute left-3 top-3 h-5 w-5"
                    :style="{ color: themeColors.textSecondary }" />
                <input 
                    v-model="searchQuery"
                    type="text" 
                    placeholder="Search reservations, guests, or rooms..."
                    class="w-full pl-10 pr-4 py-3 rounded-lg border focus:outline-none focus:ring-2"
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Pending Reservations -->
            <div class="rounded-lg border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                  }">
                <div class="p-6 border-b"
                     :style="{ borderColor: themeColors.border }">
                    <h3 class="text-lg font-semibold mb-4"
                        :style="{ color: themeColors.textPrimary }">Pending Check-ins</h3>
                </div>
                <div class="p-6">
                    <div v-if="filteredReservations.length === 0" class="text-center py-8"
                        :style="{ color: themeColors.textSecondary }">
                        No pending reservations for today
                    </div>
                    <div v-else class="space-y-4">
                        <div v-for="reservation in filteredReservations" :key="reservation.id"
                             class="border rounded-lg p-4 transition-colors hover:opacity-80"
                             :style="{ 
                                 borderColor: themeColors.border,
                                 borderStyle: 'solid',
                                 borderWidth: '1px'
                             }">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3"
                                         :style="{ backgroundColor: themeColors.primary + '20' }">
                                        <UserIcon class="h-5 w-5" :style="{ color: themeColors.primary }" />
                                    </div>
                                    <div>
                                        <div class="font-medium"
                                             :style="{ color: themeColors.textPrimary }">{{ reservation.guest_name }}</div>
                                        <div class="text-sm"
                                             :style="{ color: themeColors.textSecondary }">{{ reservation.guest_email }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium"
                                         :style="{ color: themeColors.textSecondary }">Room {{ reservation.room_number }}</div>
                                    <div class="text-lg font-bold"
                                         :style="{ color: themeColors.primary }">{{ formatCurrency(reservation.total_amount) }}</div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span :style="{ color: themeColors.textSecondary }">Check-in:</span>
                                    <span :style="{ color: themeColors.textPrimary }">{{ formatDate(reservation.check_in_date) }}</span>
                                </div>
                                <div>
                                    <span :style="{ color: themeColors.textSecondary }">Check-out:</span>
                                    <span :style="{ color: themeColors.textPrimary }">{{ formatDate(reservation.check_out_date) }}</span>
                                </div>
                            </div>
                            <div class="flex gap-2 mt-4">
                                <button @click="checkInGuest(reservation)"
                                    class="px-4 py-2 rounded-md font-medium text-white transition-colors flex-1"
                                    :style="{
                                        backgroundColor: themeColors.success
                                    }"
                                    @mouseenter="$event.target.style.backgroundColor = themeColors.success + 'dd'"
                                    @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                                    <CheckCircleIcon class="h-4 w-4 mr-2" />
                                    Check In
                                </button>
                                <button @click="selectedReservation = reservation"
                                    class="px-4 py-2 rounded-md font-medium transition-colors flex-1"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        color: themeColors.textPrimary,
                                        borderColor: themeColors.border,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                    <KeyIcon class="h-4 w-4 mr-2" />
                                    Assign Room
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Rooms -->
            <div class="rounded-lg border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                  }">
                <div class="p-6 border-b"
                     :style="{ borderColor: themeColors.border }">
                    <h3 class="text-lg font-semibold mb-4"
                        :style="{ color: themeColors.textPrimary }">Available Rooms</h3>
                </div>
                <div class="p-6">
                    <div v-if="filteredRooms.length === 0" class="text-center py-8"
                        :style="{ color: themeColors.textSecondary }">
                        No available rooms
                    </div>
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="room in filteredRooms" :key="room.id"
                             class="border rounded-lg p-4 transition-colors hover:opacity-80 cursor-pointer"
                             :style="{ 
                                 borderColor: themeColors.border,
                                 borderStyle: 'solid',
                                 borderWidth: '1px'
                             }"
                             @click="selectedReservation ? assignRoom(selectedReservation, room) : null">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <div class="font-medium text-lg"
                                         :style="{ color: themeColors.textPrimary }">{{ room.room_number }}</div>
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textSecondary }">{{ room.room_type }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold"
                                         :style="{ color: themeColors.primary }">{{ formatCurrency(room.price_per_night) }}</div>
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textSecondary }">per night</div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span :style="{ color: themeColors.textSecondary }">Floor:</span>
                                    <span :style="{ color: themeColors.textPrimary }">{{ room.floor }}</span>
                                </div>
                                <div>
                                    <span :style="{ color: themeColors.textSecondary }">Capacity:</span>
                                    <span :style="{ color: themeColors.textPrimary }">{{ room.capacity }} guests</span>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="px-2 py-1 rounded text-xs font-medium"
                                    :style="{
                                        backgroundColor: themeColors.success,
                                        color: 'white'
                                    }">
                                    Available
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Check-ins -->
        <div v-if="todayCheckIns && todayCheckIns.length > 0" class="rounded-lg border shadow-sm mt-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
              }">
            <div class="p-6 border-b"
                 :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Today's Check-ins</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div v-for="checkIn in todayCheckIns" :key="checkIn.id"
                         class="border rounded-lg p-4"
                         :style="{ 
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3"
                                     :style="{ backgroundColor: themeColors.success + '20' }">
                                    <CheckCircleIcon class="h-5 w-5" :style="{ color: themeColors.success }" />
                                </div>
                                <div>
                                    <div class="font-medium"
                                         :style="{ color: themeColors.textPrimary }">{{ checkIn.guest_name }}</div>
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textSecondary }">Room {{ checkIn.room_number }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textSecondary }">{{ formatTime(checkIn.check_in_time) }}</div>
                                <div class="text-lg font-bold"
                                     :style="{ color: themeColors.primary }">{{ formatCurrency(checkIn.total_amount) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
/* Component specific styles */
</style>
