<template>
    <div class="space-y-8">
        <!-- Today's Activities -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="(value, key) in todaysActivities" :key="key"
                 class="shadow rounded-lg p-6 transition-all hover:shadow-lg cursor-pointer"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }"
                 @click="$emit('navigate', getActivityRoute(key))">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">{{ getActivityLabel(key) }}</p>
                        <p class="text-2xl font-bold mt-2"
                           :style="{ color: themeColors.textPrimary }">{{ value }}</p>
                    </div>
                    <div class="p-3 rounded-full"
                         :style="{ backgroundColor: `${themeColors.primary}20` }">
                        <component :is="getActivityIcon(key)" class="h-6 w-6"
                                   :style="{ color: themeColors.primary }" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Arrivals & Departures Lists -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Arrivals -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Today's Arrivals</h2>
                    <button class="text-sm font-medium transition-colors"
                            :style="{ color: themeColors.primary }"
                            @click="$emit('navigate', '/front-desk/reservations')">
                        View All
                    </button>
                </div>
                <div class="space-y-4">
                    <div v-for="arrival in arrivals" :key="arrival.id"
                         class="flex items-center p-4 rounded-lg transition-colors hover:bg-gray-50"
                         :style="{ backgroundColor: themeColors.card }">
                        <div class="p-2 rounded-full mr-4"
                             :style="{ backgroundColor: `${themeColors.success}20` }">
                            <ArrowRightOnRectangleIcon class="h-5 w-5"
                                                     :style="{ color: themeColors.success }" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textPrimary }">{{ arrival.guest_name }}</p>
                            <p class="text-xs"
                               :style="{ color: themeColors.textTertiary }">Room {{ arrival.room_number }} • {{ arrival.room_type }}</p>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full"
                              :style="{
                                  backgroundColor: arrival.checked_in ? 'rgba(16, 185, 129, 0.1)' : 'rgba(245, 158, 11, 0.1)',
                                  color: arrival.checked_in ? '#10B981' : '#F59E0B'
                              }">
                            {{ arrival.checked_in ? 'Checked In' : 'Pending' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Departures -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Today's Departures</h2>
                    <button class="text-sm font-medium transition-colors"
                            :style="{ color: themeColors.primary }"
                            @click="$emit('navigate', '/front-desk/reservations')">
                        View All
                    </button>
                </div>
                <div class="space-y-4">
                    <div v-for="departure in departures" :key="departure.id"
                         class="flex items-center p-4 rounded-lg transition-colors hover:bg-gray-50"
                         :style="{ backgroundColor: themeColors.card }">
                        <div class="p-2 rounded-full mr-4"
                             :style="{ backgroundColor: `${themeColors.danger}20` }">
                            <ArrowLeftOnRectangleIcon class="h-5 w-5"
                                                     :style="{ color: themeColors.danger }" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textPrimary }">{{ departure.guest_name }}</p>
                            <p class="text-xs"
                               :style="{ color: themeColors.textTertiary }">Room {{ departure.room_number }} • {{ departure.room_type }}</p>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full"
                              :style="{
                                  backgroundColor: departure.checked_out ? 'rgba(16, 185, 129, 0.1)' : 'rgba(245, 158, 11, 0.1)',
                                  color: departure.checked_out ? '#10B981' : '#F59E0B'
                              }">
                            {{ departure.checked_out ? 'Checked Out' : 'Pending' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Room Status & Guest Requests -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Room Status -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <h2 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Room Status</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div v-for="(status, key) in roomStatus" :key="key"
                         class="p-4 rounded-lg text-center cursor-pointer transition-colors hover:opacity-80"
                         :style="{
                             backgroundColor: `${getRoomStatusColor(key)}20`,
                             borderStyle: 'solid',
                             borderWidth: '1px',
                             borderColor: getRoomStatusColor(key)
                         }"
                         @click="$emit('navigate', '/front-desk/rooms')">
                        <p class="text-2xl font-bold"
                           :style="{ color: getRoomStatusColor(key) }">{{ status }}</p>
                        <p class="text-sm mt-1"
                           :style="{ color: themeColors.textSecondary }">{{ formatRoomStatusKey(key) }}</p>
                    </div>
                </div>
            </div>

            <!-- Guest Requests -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Guest Requests</h2>
                    <button class="text-sm font-medium transition-colors"
                            :style="{ color: themeColors.primary }"
                            @click="$emit('navigate', '/front-desk/services')">
                        View All
                    </button>
                </div>
                <div class="space-y-4">
                    <div v-for="request in guestRequests" :key="request.id"
                         class="flex items-center p-4 rounded-lg transition-colors hover:bg-gray-50"
                         :style="{ backgroundColor: themeColors.card }">
                        <div class="p-2 rounded-full mr-4"
                             :style="{ backgroundColor: `${getRequestStatusColor(request.status)}20` }">
                            <component :is="getRequestIcon(request.type)" class="h-5 w-5"
                                       :style="{ color: getRequestStatusColor(request.status) }" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textPrimary }">{{ request.description }}</p>
                            <p class="text-xs"
                               :style="{ color: themeColors.textTertiary }">Room {{ request.room_number }} • {{ formatDate(request.created_at) }}</p>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full"
                              :style="{
                                  backgroundColor: `${getRequestStatusColor(request.status)}20`,
                                  color: getRequestStatusColor(request.status)
                              }">
                            {{ request.status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import {
    ArrowRightOnRectangleIcon,
    ArrowLeftOnRectangleIcon,
    HomeIcon,
    UserIcon,
    CalendarDaysIcon,
    BellIcon,
    WrenchScrewdriverIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    data: Object,
    themeColors: Object
})

const emit = defineEmits(['navigate'])

// Extract data from props
const todaysActivities = ref(props.data?.todaysActivities || {})
const arrivals = ref(props.data?.arrivals || [])
const departures = ref(props.data?.departures || [])
const roomStatus = ref(props.data?.roomStatus || {})
const guestRequests = ref(props.data?.guestRequests || [])

// Methods
const getActivityLabel = (key) => {
    const labels = {
        arrivals: 'Arrivals',
        departures: 'Departures',
        currentGuests: 'Current Guests',
        availableRooms: 'Available Rooms'
    }
    return labels[key] || key
}

const getActivityIcon = (key) => {
    const icons = {
        arrivals: ArrowRightOnRectangleIcon,
        departures: ArrowLeftOnRectangleIcon,
        currentGuests: UserIcon,
        availableRooms: HomeIcon
    }
    return icons[key] || CalendarDaysIcon
}

const getActivityRoute = (key) => {
    const routes = {
        arrivals: '/front-desk/reservations',
        departures: '/front-desk/reservations',
        currentGuests: '/front-desk/guests',
        availableRooms: '/front-desk/rooms'
    }
    return routes[key] || '/front-desk/dashboard'
}

const formatRoomStatusKey = (key) => {
    const formatted = {
        available: 'Available',
        occupied: 'Occupied',
        cleaning: 'Cleaning',
        maintenance: 'Maintenance'
    }
    return formatted[key] || key
}

const getRoomStatusColor = (key) => {
    const colors = {
        available: '#10B981',
        occupied: '#3B82F6',
        cleaning: '#F59E0B',
        maintenance: '#EF4444'
    }
    return colors[key] || '#6B7280'
}

const getRequestIcon = (type) => {
    const icons = {
        concierge: BellIcon,
        maintenance: WrenchScrewdriverIcon,
        housekeeping: HomeIcon
    }
    return icons[type] || BellIcon
}

const getRequestStatusColor = (status) => {
    const colors = {
        pending: '#F59E0B',
        in_progress: '#3B82F6',
        completed: '#10B981'
    }
    return colors[status] || '#6B7280'
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    })
}

// Watch for data changes
watch(() => props.data, (newData) => {
    if (newData) {
        todaysActivities.value = newData.todaysActivities || {}
        arrivals.value = newData.arrivals || []
        departures.value = newData.departures || []
        roomStatus.value = newData.roomStatus || {}
        guestRequests.value = newData.guestRequests || []
    }
}, { deep: true })
</script>
