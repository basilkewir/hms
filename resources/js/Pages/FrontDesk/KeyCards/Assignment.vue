<template>
    <DashboardLayout title="Key Card Assignment" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Key Card Assignment</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Assign key cards to checked-in guests and manage room access.</p>
                </div>
                <Link :href="route('front-desk.key-cards.index')" 
                      class="px-4 py-2 rounded-md transition-colors font-medium flex items-center"
                      :style="{ 
                          backgroundColor: themeColors.background,
                          color: themeColors.textPrimary,
                          borderColor: themeColors.border,
                          borderWidth: '1px',
                          borderStyle: 'solid'
                      }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                    <ArrowLeftIcon class="h-4 w-4 mr-2" />
                    Back to Key Cards
                </Link>
            </div>
        </div>

        <!-- Assignment Form -->
        <div class="rounded-lg overflow-hidden shadow mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <!-- Form Header -->
            <div class="px-6 py-4 border-b"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderBottomWidth: '1px'
                 }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Assign New Key Card</h3>
            </div>
            
            <!-- Form Content -->
            <div class="p-6">
                <form @submit.prevent="assignKeyCard" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Select Key Card *</label>
                            <select v-model="form.key_card_id" required
                                    class="w-full rounded-md px-3 py-2 focus:outline-none focus:ring-2 transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="">Select an available key card</option>
                                <option v-for="card in availableKeyCards" :key="card.id" :value="card.id">
                                    {{ card.card_number }} - {{ formatCardType(card.card_type) }}
                                </option>
                            </select>
                            <p class="text-xs mt-1"
                               :style="{ color: themeColors.textTertiary }">Only available key cards are shown</p>
                            <p v-if="form.errors.key_card_id" 
                               class="mt-1 text-sm"
                               :style="{ color: themeColors.danger }">{{ form.errors.key_card_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Select Reservation *</label>
                            <select v-model="form.reservation_id" required
                                    @change="loadReservationDetails"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none focus:ring-2 transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="">Select a checked-in reservation</option>
                                <option v-for="reservation in checkedInReservations" :key="reservation.id" :value="reservation.id">
                                    {{ reservation.reservation_number }} - {{ reservation.guest?.full_name }} (Room {{ reservation.room?.room_number }})
                                </option>
                            </select>
                            <p class="text-xs mt-1"
                               :style="{ color: themeColors.textTertiary }">Only checked-in reservations are shown</p>
                            <p v-if="form.errors.reservation_id" 
                               class="mt-1 text-sm"
                               :style="{ color: themeColors.danger }">{{ form.errors.reservation_id }}</p>
                        </div>
                    </div>

                    <!-- Reservation Details -->
                    <div v-if="selectedReservation" class="rounded-lg p-4 border"
                         :style="{ 
                             backgroundColor: 'rgba(59, 130, 246, 0.1)',
                             borderColor: themeColors.primary,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <h4 class="font-semibold mb-3"
                            :style="{ color: themeColors.primary }">Reservation Details:</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <span class="font-medium text-sm"
                                      :style="{ color: themeColors.textPrimary }">Guest:</span>
                                <span class="text-sm ml-1"
                                      :style="{ color: themeColors.textSecondary }">{{ selectedReservation.guest?.full_name }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-sm"
                                      :style="{ color: themeColors.textPrimary }">Room:</span>
                                <span class="text-sm ml-1"
                                      :style="{ color: themeColors.textSecondary }">{{ selectedReservation.room?.room_number }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-sm"
                                      :style="{ color: themeColors.textPrimary }">Check-in:</span>
                                <span class="text-sm ml-1"
                                      :style="{ color: themeColors.textSecondary }">{{ formatDate(selectedReservation.check_in_date) }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-sm"
                                      :style="{ color: themeColors.textPrimary }">Check-out:</span>
                                <span class="text-sm ml-1"
                                      :style="{ color: themeColors.textSecondary }">{{ formatDate(selectedReservation.check_out_date) }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="selectedReservation">
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Select Room *</label>
                        <select v-model="form.room_id" required
                                class="w-full rounded-md px-3 py-2 focus:outline-none focus:ring-2 transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="">Select a room</option>
                            <option v-for="room in availableRooms" :key="room.id" :value="room.id">
                                {{ room.room_number }} - {{ room.room_type?.name || 'Standard' }}
                            </option>
                        </select>
                        <p class="text-xs mt-1"
                           :style="{ color: themeColors.textTertiary }">Select the room for this assignment</p>
                        <p v-if="form.errors.room_id" 
                           class="mt-1 text-sm"
                           :style="{ color: themeColors.danger }">{{ form.errors.room_id }}</p>
                    </div>

                    <div class="flex items-center justify-end space-x-4 pt-6 border-t"
                         :style="{ 
                             borderColor: themeColors.border,
                             borderTopWidth: '1px'
                         }">
                        <Link :href="route('front-desk.key-cards.index')" 
                              class="px-6 py-2 rounded-md transition-colors font-medium"
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
                        </Link>
                        <button type="submit" :disabled="form.processing"
                                class="px-6 py-2 rounded-md transition-colors font-medium text-white"
                                :style="{
                                    backgroundColor: form.processing ? themeColors.border : '#10b981',
                                    opacity: form.processing ? 0.7 : 1
                                }"
                                @mouseenter="!form.processing && ($event.target.style.backgroundColor = '#059669')"
                                @mouseleave="!form.processing && ($event.target.style.backgroundColor = '#10b981')">
                            <span v-if="form.processing">Assigning...</span>
                            <span v-else>Assign Key Card</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Recent Assignments -->
        <div class="rounded-lg overflow-hidden shadow"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <!-- Recent Header -->
            <div class="px-6 py-4 border-b"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderBottomWidth: '1px'
                 }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Recent Assignments</h3>
            </div>
            
            <!-- Recent Content -->
            <div class="p-6">
                <div class="text-center py-8"
                     :style="{ color: themeColors.textTertiary }">
                    <CreditCardIcon class="w-12 h-12 mx-auto mb-4 opacity-50" />
                    <p>Recent assignments will appear here</p>
                    <p class="text-sm mt-2">This section will show the latest key card assignments</p>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme.js'
import {
    ArrowLeftIcon,
    CreditCardIcon
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
    availableKeyCards: Array,
    checkedInReservations: Array,
    availableRooms: Array,
})

const navigation = computed(() => getNavigationForRole('front_desk'))

// Assignment form
const form = useForm({
    key_card_id: '',
    reservation_id: '',
    room_id: '',
})

// Selected reservation details
const selectedReservation = ref(null)

// Load reservation details when selected
const loadReservationDetails = () => {
    const reservation = props.checkedInReservations.find(r => r.id === form.reservation_id)
    selectedReservation.value = reservation
    
    // Auto-select the room if reservation has a room
    if (reservation && reservation.room_id) {
        form.room_id = reservation.room_id
    }
}

// Assign key card
const assignKeyCard = () => {
    form.post(route('front-desk.key-cards.assign'), {
        onSuccess: () => {
            form.reset()
            selectedReservation.value = null
            router.reload()
        },
        onError: (errors) => {
            console.error('Assignment error:', errors)
        }
    })
}

const formatCardType = (type) => {
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString()
}
</script>
