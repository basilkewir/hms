<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'
import { CheckCircleIcon, PencilIcon, HomeIcon } from '@heroicons/vue/24/outline'

const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background:    'var(--kotel-background)',
    card:          'var(--kotel-card)',
    border:        'var(--kotel-border)',
    textPrimary:   'var(--kotel-text-primary)',
    textSecondary: 'var(--kotel-text-secondary)',
    textTertiary:  'var(--kotel-text-tertiary)',
    primary:       'var(--kotel-primary)',
    secondary:     'var(--kotel-secondary)',
    success:       'var(--kotel-success)',
    warning:       'var(--kotel-warning)',
    danger:        'var(--kotel-danger)',
}))
loadTheme()

const props = defineProps({
    user: Object,
    room: Object,
})

// Determines route prefix based on role so buttons work for both admin and manager
const routePrefix = computed(() => {
    const roles = props.user?.roles ?? []
    if (roles.some(r => r.name === 'admin')) return 'admin'
    return 'manager'
})

const getStatusStyle = (status) => {
    const map = {
        available:    { backgroundColor: 'var(--kotel-success)',   color: 'white' },
        occupied:     { backgroundColor: 'var(--kotel-primary)',   color: 'white' },
        cleaning:     { backgroundColor: 'var(--kotel-warning)',   color: 'white' },
        maintenance:  { backgroundColor: 'var(--kotel-danger)',    color: 'white' },
        out_of_order: { backgroundColor: 'var(--kotel-secondary)', color: 'white' },
    }
    return map[status] || { backgroundColor: 'var(--kotel-secondary)', color: 'white' }
}

const getHousekeepingStyle = (status) => {
    const map = {
        clean:                { backgroundColor: 'var(--kotel-success)',   color: 'white' },
        dirty:                { backgroundColor: 'var(--kotel-danger)',    color: 'white' },
        inspected:            { backgroundColor: 'var(--kotel-primary)',   color: 'white' },
        maintenance_required: { backgroundColor: 'var(--kotel-warning)',   color: 'white' },
        waiting_for_check:    { backgroundColor: 'var(--kotel-secondary)', color: 'white' },
    }
    return map[status] || { backgroundColor: 'var(--kotel-secondary)', color: 'white' }
}

const formatLabel = (str) => str ? str.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A'

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}
</script>

<template>
    <Head :title="`Room ${room.number} - Details`" />

    <DashboardLayout :title="`Room ${room.number}`">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">

            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">
                        Room {{ room.number }}
                    </h1>
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="text-sm" :style="{ color: themeColors.textSecondary }">Status:
                            <span class="px-2 py-1 text-xs rounded-full ml-1 font-medium"
                                  :style="getStatusStyle(room.status)">
                                {{ formatLabel(room.status) }}
                            </span>
                        </span>
                        <span class="text-sm" :style="{ color: themeColors.textSecondary }">Housekeeping:
                            <span class="px-2 py-1 text-xs rounded-full ml-1 font-medium"
                                  :style="getHousekeepingStyle(room.housekeeping_status)">
                                {{ formatLabel(room.housekeeping_status) }}
                            </span>
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <Link :href="route(`${routePrefix}.rooms.edit`, room.id)"
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                          :style="{ backgroundColor: themeColors.warning }">
                        <PencilIcon class="h-4 w-4 inline mr-1" />
                        Edit
                    </Link>
                    <Link :href="route(`${routePrefix}.rooms.index`)"
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                          :style="{ backgroundColor: themeColors.primary }">
                        Back
                    </Link>
                </div>
            </div>

            <!-- Room Details -->
            <div class="mb-8">
                <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Room Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Info -->
                    <div class="rounded-lg p-4 border"
                         :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '110px' }">Room Number:</span>
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ room.number }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '110px' }">Room Type:</span>
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ room.type }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '110px' }">Floor:</span>
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ room.floor }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '110px' }">Capacity:</span>
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ room.capacity }} guests</span>
                            </div>
                        </div>
                    </div>
                    <!-- Rates & Housekeeping -->
                    <div class="rounded-lg p-4 border"
                         :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '110px' }">Nightly Rate:</span>
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(room.rate) }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '110px' }">Last Cleaned:</span>
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ room.last_cleaned || 'Not recorded' }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '110px' }">Occupancy:</span>
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ room.guest ? 'Occupied' : 'Vacant' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Guest -->
            <div v-if="room.guest" class="mb-8">
                <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Current Guest</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="rounded-lg p-4 border"
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.08)', borderColor: themeColors.success, borderStyle: 'solid', borderWidth: '1px' }">
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '110px' }">Guest Name:</span>
                                <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ room.guest }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '110px' }">Check-in:</span>
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ formatDate(room.check_in) }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '110px' }">Check-out:</span>
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">{{ formatDate(room.check_out) }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-sm font-medium mr-3"
                                      :style="{ color: themeColors.textSecondary, minWidth: '110px' }">Reservation:</span>
                                <Link v-if="room.reservation_id"
                                      :href="route(`${routePrefix}.reservations.show`, room.reservation_id)"
                                      class="text-sm font-medium"
                                      :style="{ color: themeColors.primary }">#{{ room.reservation_id }}</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amenities -->
            <div v-if="room.amenities && room.amenities.length > 0" class="mb-8">
                <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Room Amenities</h3>
                <div class="rounded-lg p-4 border"
                     :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        <div v-for="amenity in room.amenities" :key="amenity.id ?? amenity"
                             class="flex items-center gap-2">
                            <CheckCircleIcon class="h-4 w-4 flex-shrink-0" :style="{ color: themeColors.success }" />
                            <span class="text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ amenity.name ?? amenity }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Special Features -->
            <div v-if="room.special_features" class="mb-8">
                <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Special Features</h3>
                <div class="rounded-lg p-4 border"
                     :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                    <p class="text-sm whitespace-pre-wrap" :style="{ color: themeColors.textPrimary }">{{ room.special_features }}</p>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="room.notes" class="mb-8">
                <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Notes</h3>
                <div class="rounded-lg p-4 border"
                     :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                    <p class="text-sm whitespace-pre-wrap" :style="{ color: themeColors.textPrimary }">{{ room.notes }}</p>
                </div>
            </div>

        </div>
    </DashboardLayout>
</template>
