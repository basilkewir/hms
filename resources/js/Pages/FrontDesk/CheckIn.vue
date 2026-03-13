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
                    <Link :href="route('front-desk.reservations.create')"
                          class="px-4 py-2 rounded-md transition-colors"
                          :style="{
                              backgroundColor: themeColors.primary,
                              color: themeColors.background
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
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
                <div v-for="arrival in todaysArrivals" :key="arrival.id"
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
            <div v-if="todaysArrivals.length === 0" class="text-center py-8"
                 :style="{ color: themeColors.textTertiary }">
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
                        :style="{ color: themeColors.textSecondary }"
                        @mouseenter="$event.target.style.color = themeColors.textPrimary"
                        @mouseleave="$event.target.style.color = themeColors.textSecondary">
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
                                <!-- Show reserved room first if it exists -->
                                <option v-if="selectedGuest.roomNumber && selectedGuest.roomNumber !== 'TBA'"
                                        :value="selectedGuest.roomNumber"
                                        :selected="checkInForm.roomNumber === selectedGuest.roomNumber">
                                    {{ selectedGuest.roomNumber }} - {{ selectedGuest.room_type }} (Reserved)
                                </option>
                                <!-- Show other available rooms -->
                                <option v-for="room in filteredAvailableRooms"
                                        :key="room.number"
                                        :value="room.number">
                                    {{ room.number }} - {{ room.type }}
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

                <!-- Key Card Assignment -->
                <div>
                    <h4 class="text-md font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Key Card Assignment <span class="text-sm font-normal" :style="{ color: themeColors.textTertiary }">(optional)</span></h4>
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Assign Key Card</label>
                        <select v-model="checkInForm.keyCardId"
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="">No key card</option>
                            <option v-for="card in availableKeyCards" :key="card.id" :value="card.id">
                                {{ card.card_number }} — {{ card.card_type || 'Standard' }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Payment at Check-In -->
                <div>
                    <h4 class="text-md font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Payment at Check-In <span class="text-sm font-normal" :style="{ color: themeColors.textTertiary }">(optional)</span></h4>

                    <!-- Folio Summary -->
                    <div class="p-4 rounded-lg mb-4"
                         :style="{
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderWidth: '1px',
                             borderStyle: 'solid'
                         }">
                        <div class="flex justify-between text-sm mb-2">
                            <span :style="{ color: themeColors.textSecondary }">Room Rate / night</span>
                            <span :style="{ color: themeColors.textPrimary }">{{ formatMoney(selectedGuest?.room_rate || 0) }}</span>
                        </div>
                        <div class="flex justify-between text-sm mb-2">
                            <span :style="{ color: themeColors.textSecondary }">Nights</span>
                            <span :style="{ color: themeColors.textPrimary }">{{ selectedGuest?.nights || 0 }}</span>
                        </div>
                        <div class="flex justify-between text-sm mb-2 pt-2"
                             :style="{ borderTop: `1px solid ${themeColors.border}`, color: themeColors.textPrimary }">
                            <span class="font-semibold">Total Amount</span>
                            <span class="font-semibold">{{ formatMoney(selectedGuest?.total_amount || 0) }}</span>
                        </div>
                        <div v-if="(selectedGuest?.paid_amount || 0) > 0" class="flex justify-between text-sm mb-2">
                            <span :style="{ color: themeColors.success }">Already Paid</span>
                            <span :style="{ color: themeColors.success }">- {{ formatMoney(selectedGuest.paid_amount) }}</span>
                        </div>
                        <div class="flex justify-between font-semibold text-base pt-2"
                             :style="{ borderTop: `1px solid ${themeColors.border}`, color: themeColors.textPrimary }">
                            <span>Balance Due</span>
                            <span :style="{ color: balanceDue > 0 ? themeColors.warning : themeColors.success }">
                                {{ formatMoney(balanceDue) }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Payment Method</label>
                            <select v-model="checkInForm.paymentMethod"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="cash">Cash</option>
                                <option value="card">Credit / Debit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Amount to Pay Now</label>
                            <div class="relative">
                                <input type="number" v-model.number="checkInForm.paymentAmount"
                                       min="0" step="0.01" placeholder="0.00"
                                       class="w-full rounded-md px-3 py-2 pl-8 focus:outline-none transition-colors"
                                       :style="{
                                           backgroundColor: themeColors.background,
                                           borderColor: themeColors.border,
                                           color: themeColors.textPrimary,
                                           borderWidth: '1px',
                                           borderStyle: 'solid'
                                       }" />
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm"
                                      :style="{ color: themeColors.textTertiary }">$</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick payment shortcuts -->
                    <div class="flex flex-wrap gap-2 mt-3">
                        <button type="button" @click="checkInForm.paymentAmount = 0"
                                class="px-3 py-1 rounded text-xs transition-colors"
                                :style="{ backgroundColor: themeColors.background, color: themeColors.textSecondary, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                            No Payment
                        </button>
                        <button type="button" @click="checkInForm.paymentAmount = Math.round(balanceDue * 0.5 * 100) / 100"
                                class="px-3 py-1 rounded text-xs transition-colors"
                                :style="{ backgroundColor: themeColors.background, color: themeColors.textSecondary, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                            50% Deposit ({{ formatMoney(balanceDue * 0.5) }})
                        </button>
                        <button type="button" @click="checkInForm.paymentAmount = balanceDue"
                                class="px-3 py-1 rounded text-xs font-medium transition-colors"
                                :style="{ backgroundColor: themeColors.primary, color: themeColors.background }">
                            Pay Full ({{ formatMoney(balanceDue) }})
                        </button>
                    </div>

                    <!-- Balance indicator -->
                    <div v-if="checkInForm.paymentAmount > 0" class="mt-3 text-sm"
                         :style="{ color: balanceAfterPayment <= 0 ? themeColors.success : themeColors.warning }">
                        <span v-if="balanceAfterPayment <= 0">✓ Fully paid — no balance due at checkout</span>
                        <span v-else>Remaining balance at checkout: {{ formatMoney(balanceAfterPayment) }}</span>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6"
                     :style="{
                         borderTop: `1px solid ${themeColors.border}`
                     }">
                    <button type="button" @click="selectedGuest = null"
                            class="px-6 py-2 rounded-md transition-colors"
                            :style="{
                                backgroundColor: themeColors.background,
                                color: themeColors.textPrimary,
                                borderColor: themeColors.border,
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                        Cancel
                    </button>
                    <button type="submit" :disabled="isProcessing"
                            class="px-6 py-2 rounded-md transition-colors"
                            :style="{
                                backgroundColor: isProcessing ? themeColors.border : themeColors.primary,
                                color: isProcessing ? themeColors.textTertiary : themeColors.background,
                                opacity: isProcessing ? 0.7 : 1
                            }"
                            @mouseenter="!isProcessing && ($event.target.style.backgroundColor = themeColors.hover)"
                            @mouseleave="!isProcessing && ($event.target.style.backgroundColor = themeColors.primary)">
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
    todaysArrivals: Array,
    availableRooms: Array,
    availableKeyCards: Array,
    allReservations: Array,
    selectedReservationId: Number,
})

const navigation = computed(() => getNavigationForRole('front_desk'))

const selectedGuest = ref(null)
const isProcessing = ref(false)

// Define checkInForm before auto-selection
const checkInForm = ref({
    roomNumber: '',
    keyCardId: '',
    paymentAmount: 0,
    paymentMethod: 'cash',
})

// The actual total amount from the reservation (already accounts for discounts etc.)
const balanceDue = computed(() => {
    if (!selectedGuest.value) return 0
    return parseFloat(selectedGuest.value.balance_amount ?? selectedGuest.value.total_amount ?? 0)
})

// Keep estimatedTotal alias for any other references
const estimatedTotal = computed(() => balanceDue.value)

const balanceAfterPayment = computed(() => {
    return Math.max(0, Math.round((balanceDue.value - (checkInForm.value.paymentAmount || 0)) * 100) / 100)
})

const formatMoney = (amount) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount || 0)
}

// Computed property for filtered rooms to avoid duplication
const filteredAvailableRooms = computed(() => {
    if (!selectedGuest.value) return props.availableRooms

    const reservedRoomNumber = selectedGuest.value.roomNumber
    if (!reservedRoomNumber || reservedRoomNumber === 'TBA') {
        return props.availableRooms
    }

    // Filter out the reserved room to avoid duplication
    return props.availableRooms.filter(room => room.number !== reservedRoomNumber)
})

// Auto-select reservation if provided
if (props.selectedReservationId) {
    const reservation = props.todaysArrivals.find(r => r.id === props.selectedReservationId) ||
                      props.allReservations?.find(r => r.id === props.selectedReservationId)
    if (reservation) {
        selectedGuest.value = reservation
        // Always set the reserved room if it exists, regardless of availability
        // The user can change it if needed
        if (reservation.roomNumber && reservation.roomNumber !== 'TBA') {
            checkInForm.value.roomNumber = reservation.roomNumber
        }

        // Debug logging
        console.log('Auto-selected reservation:', reservation)
        console.log('Auto-setting room number to:', reservation.roomNumber)
        console.log('Available rooms:', props.availableRooms)
    }
}

const selectGuest = (guest) => {
    selectedGuest.value = guest
    // Always set the reserved room if it exists, regardless of availability
    // The user can change it if needed
    if (guest.roomNumber && guest.roomNumber !== 'TBA') {
        checkInForm.value.roomNumber = guest.roomNumber
    } else {
        checkInForm.value.roomNumber = ''
    }
    checkInForm.value.keyCardId = ''
    checkInForm.value.paymentAmount = 0
    checkInForm.value.paymentMethod = 'cash'

    // Debug logging to help troubleshoot
    console.log('Selected guest:', guest)
    console.log('Setting room number to:', guest.roomNumber)
    console.log('Check-in form room number:', checkInForm.value.roomNumber)
}

const startCheckIn = (guest) => {
    selectGuest(guest)
}

const processCheckIn = () => {
    if (!checkInForm.value.roomNumber) {
        alert('Please select a room')
        return
    }

    isProcessing.value = true

    const payload = {
        reservation_id: selectedGuest.value.id,
        room_number: checkInForm.value.roomNumber,
    }

    if (checkInForm.value.keyCardId) {
        payload.key_card_id = checkInForm.value.keyCardId
    }

    if (checkInForm.value.paymentAmount > 0) {
        payload.payment_amount = checkInForm.value.paymentAmount
        payload.payment_method = checkInForm.value.paymentMethod
    }

    router.post(route('front-desk.checkin.store'), payload, {
        onSuccess: () => {
            isProcessing.value = false
            selectedGuest.value = null
        },
        onError: () => {
            isProcessing.value = false
        }
    })
}

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800',
        checked_in: 'bg-green-100 text-green-800',
        no_show: 'bg-red-100 text-red-800',
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
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
</style>
