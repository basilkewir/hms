<template>
    <DashboardLayout title="Today's Departures" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Today's Departures</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Guests expected to check out today.</p>
                </div>
                <Link :href="route('manager.reservations.index')" 
                      class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity border"
                      :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                    ← Back to Reservations
                </Link>
            </div>
        </div>

        <!-- Departures List -->
        <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Expected Departures</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, border: `1px solid ${themeColors.border}` }">
                    {{ departures.length }} guests
                </span>
            </div>
            <div v-if="departures.length > 0" class="space-y-3">
                <div v-for="departure in departures" :key="departure.id"
                     class="flex items-center justify-between p-3 rounded-lg"
                     :style="{ backgroundColor: themeColors.background }">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3"
                             :style="{ backgroundColor: themeColors.danger }">
                            <UserIcon class="h-5 w-5" :style="{ color: '#000' }" />
                        </div>
                        <div>
                            <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ departure.guest_name }}</p>
                            <p class="text-xs" :style="{ color: themeColors.textTertiary }">Room {{ departure.room_number }} • {{ departure.room_type }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs" :style="{ color: themeColors.textTertiary }">{{ formatTime(departure.expected_departure) }}</p>
                        <Link :href="route('manager.checkout', { reservation_id: departure.id })" 
                              class="text-xs px-2 py-1 rounded mt-1 hover:opacity-90 transition-opacity inline-block"
                              :style="{ backgroundColor: themeColors.danger, color: '#000' }">
                            Check Out
                        </Link>
                    </div>
                </div>
            </div>
            <div v-else class="text-center py-12">
                <p :style="{ color: themeColors.textSecondary }">No departures scheduled for today.</p>
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
    departures: Array,
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

const formatTime = (time) => {
    return new Date(time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
</script>
