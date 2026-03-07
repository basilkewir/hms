<template>
    <DashboardLayout title="Guest Check-In" :user="user" :navigation="navigation">
        <!-- Check-In Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Guest Check-In</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Process guest arrivals and room assignments.</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('manager.reservations.create')" 
                          class="px-4 py-2 rounded-md transition-colors"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                              color: themeColors.background 
                          }">
                        <UserPlusIcon class="h-4 w-4 mr-2 inline" />
                        Walk-in Guest
                    </Link>
                </div>
            </div>
        </div>

        <!-- Today's Arrivals -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <h3 class="text-lg font-medium mb-4"
                :style="{ color: themeColors.textPrimary }">Today's Expected Arrivals</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="arrival in (todaysArrivals || [])" :key="arrival.id"
                     class="rounded-lg p-4 cursor-pointer transition-colors"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }"
                     @click="selectGuest(arrival)"
                     @mouseenter="$event.target.style.borderColor = themeColors.primary"
                     @mouseleave="$event.target.style.borderColor = themeColors.border">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium"
                            :style="{ color: themeColors.textPrimary }">{{ arrival.guestName }}</h4>
                        <span class="text-xs px-2 py-1 rounded-full"
                              :class="getStatusColor(arrival.status)">
                            {{ arrival.status }}
                        </span>
                    </div>
                    <div class="text-sm space-y-1"
                         :style="{ color: themeColors.textSecondary }">
                        <p><strong :style="{ color: themeColors.textPrimary }">Room:</strong> {{ arrival.roomNumber }}</p>
                        <p><strong :style="{ color: themeColors.textPrimary }">Nights:</strong> {{ arrival.nights }}</p>
                        <p><strong :style="{ color: themeColors.textPrimary }">Guests:</strong> {{ arrival.guestCount }}</p>
                        <p><strong :style="{ color: themeColors.textPrimary }">Arrival:</strong> {{ formatTime(arrival.arrivalTime) }}</p>
                    </div>
                    <div class="mt-3">
                        <button v-if="arrival.status === 'pending'" 
                                @click.stop="startCheckIn(arrival)"
                                class="w-full px-3 py-2 rounded-md text-sm transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.primary,
                                    color: themeColors.background 
                                }">
                            Start Check-In
                        </button>
                        <button v-else-if="arrival.status === 'checked_in'" 
                                class="w-full px-3 py-2 rounded-md text-sm cursor-not-allowed"
                                :style="{ 
                                    backgroundColor: themeColors.success,
                                    color: themeColors.background,
                                    opacity: '0.7'
                                }">
                            Checked In
                        </button>
                    </div>
                </div>
            </div>
            <div v-if="(todaysArrivals || []).length === 0" class="text-center py-8 text-gray-500">
                No arrivals scheduled for today.
            </div>
        </div>

        <!-- Check-In Form -->
        <div v-if="selectedGuest" class="shadow rounded-lg p-6"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Check-In: {{ selectedGuest.guestName }}</h3>
                <button @click="selectedGuest = null"
                        :style="{ color: themeColors.textSecondary }">
                    <XMarkIcon class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="processCheckIn" class="space-y-6">
                <!-- Room Assignment -->
                <div>
                    <h4 class="text-md font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Room Assignment</h4>
                    <div v-if="selectedGuest.reservedRoomAvailable && selectedGuest.roomNumber && selectedGuest.roomNumber !== 'TBA'" 
                         class="mb-4 p-3 rounded-md"
                         :style="{ 
                             backgroundColor: 'rgba(34, 197, 94, 0.1)',
                             borderColor: themeColors.success,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <p class="text-sm"
                           :style="{ color: themeColors.success }">
                            <strong>Reserved Room:</strong> Room {{ selectedGuest.roomNumber }} is available and clean. 
                            This room will be automatically assigned.
                        </p>
                    </div>
                    <div v-else-if="selectedGuest.roomNumber && selectedGuest.roomNumber !== 'TBA'" 
                         class="mb-4 p-3 rounded-md"
                         :style="{ 
                             backgroundColor: 'rgba(251, 191, 36, 0.1)',
                             borderColor: themeColors.warning,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <p class="text-sm"
                           :style="{ color: themeColors.warning }">
                            <strong>Note:</strong> Reserved room {{ selectedGuest.roomNumber }} is not available or not clean. 
                            Please select an alternative room.
                        </p>
                        <p class="text-xs mt-1"
                           :style="{ color: themeColors.warning, opacity: 0.8 }">
                            Room Status: {{ selectedGuest.reservedRoomStatus || 'N/A' }} | 
                            Housekeeping: {{ selectedGuest.reservedRoomHousekeepingStatus || 'N/A' }}
                        </p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                <option v-if="selectedGuest.roomNumber && selectedGuest.roomNumber !== 'TBA'" 
                                        :value="selectedGuest.roomNumber">
                                    {{ selectedGuest.roomNumber }} - {{ selectedGuest.room_type }} (Reserved)
                                </option>
                                <option v-for="room in filteredAvailableRooms" 
                                        :key="room.room_number ?? room.number" 
                                        :value="room.room_number ?? room.number">
                                    {{ room.room_number ?? room.number }} - {{ room.type }}
                                </option>
                            </select>
                            <div v-if="selectedGuest.roomNumber && selectedGuest.roomNumber !== 'TBA'" 
                                 class="mt-2 text-xs"
                                 :style="{ color: themeColors.textTertiary }">
                                Reserved room: {{ selectedGuest.roomNumber }} 
                                <span v-if="selectedGuest.reservedRoomAvailable" :style="{ color: themeColors.success }">
                                    ✓ Available & Clean
                                </span>
                                <span v-else :style="{ color: themeColors.warning }">
                                    ⚠ May need attention
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6"
                     :style="{ borderTop: `1px solid ${themeColors.border}` }">
                    <button type="button" @click="selectedGuest = null"
                            class="px-6 py-2 rounded-md transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.background,
                                color: themeColors.textPrimary,
                                borderColor: themeColors.border,
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }">
                        Cancel
                    </button>
                    <button type="submit" :disabled="isProcessing"
                            class="px-6 py-2 rounded-md transition-colors"
                            :style="{ 
                                backgroundColor: isProcessing ? themeColors.border : themeColors.primary,
                                color: isProcessing ? themeColors.textTertiary : themeColors.background,
                                opacity: isProcessing ? 0.7 : 1
                            }">
                        <span v-if="isProcessing">Processing...</span>
                        <span v-else>Complete Check-In</span>
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme.js'
import { UserPlusIcon, XMarkIcon } from '@heroicons/vue/24/outline'

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
    todaysArrivals: { type: Array, default: () => [] },
    availableRooms: { type: Array, default: () => [] },
    availableKeyCards: { type: Array, default: () => [] },
    allReservations: { type: Array, default: () => [] },
    selectedReservationId: { type: Number, default: null },
})

const navigation = computed(() => getNavigationForRole('manager'))

const selectedGuest = ref(null)
const isProcessing = ref(false)

const checkInForm = ref({ roomNumber: '' })

const filteredAvailableRooms = computed(() => {
    if (!selectedGuest.value) return props.availableRooms
    const reserved = selectedGuest.value.roomNumber
    if (!reserved || reserved === 'TBA') return props.availableRooms
    return props.availableRooms.filter(r => (r.room_number ?? r.number) !== reserved)
})

if (props.selectedReservationId) {
    const reservation = (props.todaysArrivals || []).find(r => r.id === props.selectedReservationId) ??
                        props.allReservations?.find(r => r.id === props.selectedReservationId)
    if (reservation) {
        selectedGuest.value = reservation
        if (reservation.roomNumber && reservation.roomNumber !== 'TBA') {
            checkInForm.value.roomNumber = reservation.roomNumber
        }
    }
}

const selectGuest = (guest) => {
    selectedGuest.value = guest
    if (guest.roomNumber && guest.roomNumber !== 'TBA') {
        checkInForm.value.roomNumber = guest.roomNumber
    } else {
        checkInForm.value.roomNumber = ''
    }
}

const startCheckIn = (guest) => selectGuest(guest)

const processCheckIn = () => {
    if (!checkInForm.value.roomNumber) {
        alert('Please select a room')
        return
    }
    isProcessing.value = true
    router.post(route('manager.checkin.store'), {
        reservation_id: selectedGuest.value.id,
        room_number: checkInForm.value.roomNumber,
    }, {
        onSuccess: () => { isProcessing.value = false; selectedGuest.value = null },
        onError:   () => { isProcessing.value = false },
    })
}

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-blue-100 text-blue-800',
        checked_in: 'bg-green-100 text-green-800',
        no_show: 'bg-red-100 text-red-800',
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const formatTime = (time) => {
    if (!time) return 'N/A'
    try {
        return new Date(time).toLocaleDateString('en-US', {
            weekday: 'short', month: 'short', day: 'numeric',
            year: 'numeric', hour: '2-digit', minute: '2-digit'
        })
    } catch { return time }
}
</script>
