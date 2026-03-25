<template>
    <DashboardLayout title="Front Desk Dashboard" :user="user">
        <!-- Dashboard Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Front Desk Operations</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Handle guest check-ins, reservations, and provide excellent customer service.</p>
                </div>
                <div class="text-right">
                    <p class="text-sm"
                       :style="{ color: themeColors.textTertiary }">{{ currentDateTime }}</p>
                    <p class="text-lg font-semibold"
                       :style="{ color: themeColors.textPrimary }">{{ user.full_name }}</p>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <h2 class="text-lg font-semibold mb-4"
                :style="{ color: themeColors.textPrimary }">Today's Activities</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="rounded-lg shadow-sm p-4 transition-shadow cursor-pointer"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <CalendarDaysIcon class="h-8 w-8"
                                             :style="{ color: themeColors.primary }" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Today's Arrivals</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: themeColors.textPrimary }">{{ todaysActivities.arrivals }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-lg shadow-sm p-4 transition-shadow cursor-pointer"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <CalendarDaysIcon class="h-8 w-8"
                                             :style="{ color: themeColors.danger }" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Today's Departures</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: themeColors.textPrimary }">{{ todaysActivities.departures }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-lg shadow-sm p-4 transition-shadow cursor-pointer"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <UserGroupIcon class="h-8 w-8"
                                             :style="{ color: themeColors.success }" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Current Guests</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: themeColors.textPrimary }">{{ todaysActivities.currentGuests }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-lg shadow-sm p-4 transition-shadow cursor-pointer"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <HomeIcon class="h-8 w-8"
                                     :style="{ color: themeColors.warning }" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Available Rooms</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: themeColors.textPrimary }">{{ todaysActivities.availableRooms }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
            <Link :href="route('front-desk.quick-checkin')"
                  class="shadow rounded-lg p-6 text-center transition-colors"
                  :style="{ 
                      backgroundColor: themeColors.primary,
                      color: 'white'
                  }"
                  @mouseenter="$event.target.style.opacity = '0.8'"
                  @mouseleave="$event.target.style.opacity = '1'">
                <KeyIcon class="h-12 w-12 mx-auto mb-4" />
                <h3 class="text-lg font-semibold">Quick Check-in</h3>
                <p class="text-sm opacity-80 mt-2">Process guest arrivals</p>
            </Link>
            <Link :href="route('front-desk.checkout')"
                  class="shadow rounded-lg p-6 text-center transition-colors"
                  :style="{ 
                      backgroundColor: themeColors.danger,
                      color: 'white'
                  }"
                  @mouseenter="$event.target.style.opacity = '0.8'"
                  @mouseleave="$event.target.style.opacity = '1'">
                <KeyIcon class="h-12 w-12 mx-auto mb-4" />
                <h3 class="text-lg font-semibold">Quick Check-out</h3>
                <p class="text-sm opacity-80 mt-2">Process guest departures</p>
            </Link>
            <Link :href="route('front-desk.reservations.create')"
                  class="shadow rounded-lg p-6 text-center transition-colors"
                  :style="{ 
                      backgroundColor: themeColors.success,
                      color: 'white'
                  }"
                  @mouseenter="$event.target.style.opacity = '0.8'"
                  @mouseleave="$event.target.style.opacity = '1'">
                <PlusIcon class="h-12 w-12 mx-auto mb-4" />
                <h3 class="text-lg font-semibold">New Reservation</h3>
                <p class="text-sm opacity-80 mt-2">Create booking</p>
            </Link>
            <Link :href="route('front-desk.payments.process')"
                  class="shadow rounded-lg p-6 text-center transition-colors"
                  :style="{ 
                      backgroundColor: themeColors.warning,
                      color: 'white'
                  }"
                  @mouseenter="$event.target.style.opacity = '0.8'"
                  @mouseleave="$event.target.style.opacity = '1'">
                <CreditCardIcon class="h-12 w-12 mx-auto mb-4" />
                <h3 class="text-lg font-semibold">Process Payment</h3>
                <p class="text-sm opacity-80 mt-2">Handle transactions</p>
            </Link>
        </div>

        <!-- Today's Schedule -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Arrivals -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Today's Arrivals</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :style="{ 
                              backgroundColor: 'rgba(59, 130, 246, 0.1)',
                              color: 'rgb(59, 130, 246)',
                              border: '1px solid rgb(59, 130, 246)'
                          }">
                        {{ arrivals.length }} guests
                    </span>
                </div>
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    <div v-for="arrival in arrivals" :key="arrival.id"
                         class="flex items-center justify-between p-3 rounded-lg"
                         :style="{ 
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3"
                                 :style="{ 
                                     backgroundColor: themeColors.primary,
                                     color: 'white'
                                 }">
                                <UserIcon class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ arrival.guest_name }}</p>
                                <p class="text-xs"
                                   :style="{ color: themeColors.textTertiary }">Room {{ arrival.room_number }} • {{ arrival.room_type }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs"
                               :style="{ color: themeColors.textTertiary }">{{ formatTime(arrival.expected_arrival) }}</p>
                            <button v-if="!arrival.checked_in" 
                                    class="text-xs px-2 py-1 rounded mt-1 transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.primary,
                                        color: 'white'
                                    }"
                                    @mouseenter="$event.target.style.opacity = '0.8'"
                                    @mouseleave="$event.target.style.opacity = '1'">
                                Check In
                            </button>
                            <span v-else class="text-xs px-2 py-1 rounded"
                                  :style="{ 
                                      backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                      color: 'rgb(16, 185, 129)',
                                      border: '1px solid rgb(16, 185, 129)'
                                  }">
                                Checked In
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Departures -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Today's Departures</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :style="{ 
                              backgroundColor: 'rgba(239, 68, 68, 0.1)',
                              color: 'rgb(239, 68, 68)',
                              border: '1px solid rgb(239, 68, 68)'
                          }">
                        {{ departures.length }} guests
                    </span>
                </div>
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    <div v-for="departure in departures" :key="departure.id"
                         class="flex items-center justify-between p-3 rounded-lg"
                         :style="{ 
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3"
                                 :style="{ 
                                     backgroundColor: themeColors.danger,
                                     color: 'white'
                                 }">
                                <UserIcon class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ departure.guest_name }}</p>
                                <p class="text-xs"
                                   :style="{ color: themeColors.textTertiary }">Room {{ departure.room_number }} • {{ departure.room_type }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs"
                               :style="{ color: themeColors.textTertiary }">{{ formatTime(departure.expected_departure) }}</p>
                            <button v-if="!departure.checked_out" 
                                    class="text-xs px-2 py-1 rounded mt-1 transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.danger,
                                        color: 'white'
                                    }"
                                    @mouseenter="$event.target.style.opacity = '0.8'"
                                    @mouseleave="$event.target.style.opacity = '1'">
                                Check Out
                            </button>
                            <span v-else class="text-xs px-2 py-1 rounded"
                                  :style="{ 
                                      backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                      color: 'rgb(16, 185, 129)',
                                      border: '1px solid rgb(16, 185, 129)'
                                  }">
                                Checked Out
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Room Status & Guest Requests -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Room Status Overview -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Room Status Overview</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-4 rounded-lg"
                         :style="{ 
                             backgroundColor: 'rgba(16, 185, 129, 0.1)',
                             border: '1px solid rgb(16, 185, 129)'
                         }">
                        <div class="text-2xl font-bold"
                             :style="{ color: 'rgb(16, 185, 129)' }">{{ roomStatus.available }}</div>
                        <div class="text-sm"
                             :style="{ color: themeColors.textSecondary }">Available</div>
                    </div>
                    <div class="text-center p-4 rounded-lg"
                         :style="{ 
                             backgroundColor: 'rgba(59, 130, 246, 0.1)',
                             border: '1px solid rgb(59, 130, 246)'
                         }">
                        <div class="text-2xl font-bold"
                             :style="{ color: 'rgb(59, 130, 246)' }">{{ roomStatus.occupied }}</div>
                        <div class="text-sm"
                             :style="{ color: themeColors.textSecondary }">Occupied</div>
                    </div>
                    <div class="text-center p-4 rounded-lg"
                         :style="{ 
                             backgroundColor: 'rgba(245, 158, 11, 0.1)',
                             border: '1px solid rgb(245, 158, 11)'
                         }">
                        <div class="text-2xl font-bold"
                             :style="{ color: 'rgb(245, 158, 11)' }">{{ roomStatus.cleaning }}</div>
                        <div class="text-sm"
                             :style="{ color: themeColors.textSecondary }">Cleaning</div>
                    </div>
                    <div class="text-center p-4 rounded-lg"
                         :style="{ 
                             backgroundColor: 'rgba(239, 68, 68, 0.1)',
                             border: '1px solid rgb(239, 68, 68)'
                         }">
                        <div class="text-2xl font-bold"
                             :style="{ color: 'rgb(239, 68, 68)' }">{{ roomStatus.maintenance }}</div>
                        <div class="text-sm"
                             :style="{ color: themeColors.textSecondary }">Maintenance</div>
                    </div>
                </div>
            </div>

            <!-- Recent Guest Requests -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Recent Guest Requests</h3>
                <div class="space-y-3">
                    <div v-for="request in guestRequests" :key="request.id"
                         class="flex items-center justify-between p-3 rounded-lg"
                         :style="{ 
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="flex items-center">
                            <component :is="getRequestIcon(request.type)" 
                                      class="h-5 w-5 mr-3"
                                      :style="{ color: themeColors.textTertiary }" />
                            <div>
                                <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ request.description }}</p>
                                <p class="text-xs"
                                   :style="{ color: themeColors.textTertiary }">Room {{ request.room_number }} • {{ formatTime(request.created_at) }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                  :style="getRequestStatusStyle(request.status)">
                                {{ request.status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import {
    CalendarDaysIcon,
    UserGroupIcon,
    HomeIcon,
    KeyIcon,
    PlusIcon,
    CreditCardIcon,
    UserIcon,
    BellIcon,
    WrenchScrewdriverIcon,
    ClipboardDocumentListIcon
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
    todaysActivities: Object,
    arrivals: Array,
    departures: Array,
    roomStatus: Object,
    guestRequests: Array,
})

const currentDateTime = computed(() => {
    return new Date().toLocaleString()
})

const formatTime = (time) => {
    return new Date(time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

const getRequestIcon = (type) => {
    const icons = {
        housekeeping: ClipboardDocumentListIcon,
        maintenance: WrenchScrewdriverIcon,
        concierge: BellIcon,
        general: BellIcon,
    }
    return icons[type] || BellIcon
}

const getRequestStatusStyle = (status) => {
    const styles = {
        pending: 'rgba(245, 158, 11, 0.1) rgb(245, 158, 11) 1px solid rgb(245, 158, 11)',
        in_progress: 'rgba(59, 130, 246, 0.1) rgb(59, 130, 246) 1px solid rgb(59, 130, 246)',
        completed: 'rgba(16, 185, 129, 0.1) rgb(16, 185, 129) 1px solid rgb(16, 185, 129)',
        cancelled: 'rgba(239, 68, 68, 0.1) rgb(239, 68, 68) 1px solid rgb(239, 68, 68)',
    }
    return styles[status] || 'rgba(107, 114, 128, 0.1) rgb(107, 114, 128) 1px solid rgb(107, 114, 128)'
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
