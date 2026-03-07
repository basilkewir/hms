<template>
    <DashboardLayout title="Today's Arrivals" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Today's Arrivals</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Guests expected to check in today.</p>
                </div>
                <Link :href="route('manager.reservations.index')" 
                      class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity border"
                      :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                    ← Back to Reservations
                </Link>
            </div>
        </div>

        <!-- Arrivals List -->
        <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Expected Arrivals</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, border: `1px solid ${themeColors.border}` }">
                    {{ arrivals.length }} guests
                </span>
            </div>
            <div v-if="arrivals.length > 0" class="space-y-3">
                <div v-for="arrival in arrivals" :key="arrival.id"
                     class="p-4 rounded-lg border"
                     :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start flex-1">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center mr-4 flex-shrink-0"
                                 :style="{ backgroundColor: themeColors.primary }">
                                <UserIcon class="h-6 w-6" :style="{ color: '#000' }" />
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <p class="text-base font-semibold" :style="{ color: themeColors.textPrimary }">{{ arrival.guest_name }}</p>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium" :style="getStatusStyle(arrival.status)">
                                        {{ formatStatus(arrival.status) }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                    <div>
                                        <span class="text-xs" :style="{ color: themeColors.textTertiary }">Confirmation:</span>
                                        <p class="font-medium" :style="{ color: themeColors.textPrimary }">{{ arrival.confirmation_number }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs" :style="{ color: themeColors.textTertiary }">Room:</span>
                                        <p class="font-medium" :style="{ color: themeColors.textPrimary }">
                                            <span v-if="arrival.room_number">{{ arrival.room_number }}</span>
                                            <span v-else :style="{ color: themeColors.warning }">Not Assigned</span>
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-xs" :style="{ color: themeColors.textTertiary }">Nights:</span>
                                        <p class="font-medium" :style="{ color: themeColors.textPrimary }">{{ arrival.nights }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs" :style="{ color: themeColors.textTertiary }">Guests:</span>
                                        <p class="font-medium" :style="{ color: themeColors.textPrimary }">
                                            {{ arrival.adults || 1 }}<span v-if="arrival.children > 0"> + {{ arrival.children }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-2 text-xs" :style="{ color: themeColors.textTertiary }">
                                    <span class="font-medium">Arrival:</span> {{ formatDate(arrival.check_in_date) }}
                                    <span v-if="arrival.check_in_time"> at {{ arrival.check_in_time }}</span>
                                </div>
                                <div class="mt-1 text-xs" :style="{ color: themeColors.textTertiary }">
                                    <span class="font-medium">Room Type:</span> {{ arrival.room_type }}
                                </div>
                            </div>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <Link :href="route('manager.checkin', { reservation_id: arrival.id })" 
                                  class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity inline-block text-sm font-medium"
                                  :style="{ backgroundColor: themeColors.primary, color: '#000' }">
                                Check In
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="text-center py-12">
                <p :style="{ color: themeColors.textSecondary }">No arrivals scheduled for today.</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'
import { UserIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    arrivals: Array,
})

const navigation = computed(() => getNavigationForRole('manager'))

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

const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('en-US', { 
        weekday: 'short', 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric' 
    })
}

const formatStatus = (status) => {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getStatusStyle = (status) => {
    const key = (status || '').toLowerCase()
    if (key === 'pending') return { backgroundColor: themeColors.value.warning, color: '#000' }
    if (key === 'confirmed') return { backgroundColor: themeColors.value.primary, color: '#000' }
    if (key === 'checked_in') return { backgroundColor: themeColors.value.success, color: '#000' }
    if (key === 'checked_out') return { backgroundColor: themeColors.value.border, color: themeColors.value.textPrimary }
    if (key === 'cancelled') return { backgroundColor: themeColors.value.danger, color: '#000' }
    return { backgroundColor: themeColors.value.border, color: themeColors.value.textPrimary }
}
</script>
