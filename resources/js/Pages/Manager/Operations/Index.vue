<template>
    <DashboardLayout title="Operations Overview" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-6" :style="{ backgroundColor: themeColors.card }">
            <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Operations Overview</h1>
            <p class="text-sm" :style="{ color: themeColors.textSecondary }">Today's operational summary.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Arrivals Today</p>
                <p class="text-3xl font-bold" :style="{ color: themeColors.primary }">{{ summary?.arrivals ?? 0 }}</p>
                <Link href="/manager/reservations/checkins" class="text-sm mt-2 inline-block hover:underline" :style="{ color: themeColors.primary }">View check-ins →</Link>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Departures Today</p>
                <p class="text-3xl font-bold" :style="{ color: themeColors.primary }">{{ summary?.departures ?? 0 }}</p>
                <Link href="/manager/reservations/checkouts" class="text-sm mt-2 inline-block hover:underline" :style="{ color: themeColors.primary }">View check-outs →</Link>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Pending Tasks</p>
                <p class="text-3xl font-bold" :style="{ color: themeColors.warning }">{{ summary?.pendingTasks ?? 0 }}</p>
                <Link href="/manager/maintenance-requests" class="text-sm mt-2 inline-block hover:underline" :style="{ color: themeColors.primary }">View requests →</Link>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Quick Actions</h2>
                <div class="space-y-3">
                    <Link href="/manager/reservations/create" class="flex items-center gap-3 p-3 rounded-lg transition-colors hover:opacity-80" :style="{ backgroundColor: themeColors.background }">
                        <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">New Reservation</span>
                    </Link>
                    <Link href="/manager/checkin" class="flex items-center gap-3 p-3 rounded-lg transition-colors hover:opacity-80" :style="{ backgroundColor: themeColors.background }">
                        <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">Check In Guest</span>
                    </Link>
                    <Link href="/manager/checkout" class="flex items-center gap-3 p-3 rounded-lg transition-colors hover:opacity-80" :style="{ backgroundColor: themeColors.background }">
                        <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">Check Out Guest</span>
                    </Link>
                    <Link href="/manager/rooms/status" class="flex items-center gap-3 p-3 rounded-lg transition-colors hover:opacity-80" :style="{ backgroundColor: themeColors.background }">
                        <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">Room Status</span>
                    </Link>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Management</h2>
                <div class="space-y-3">
                    <Link href="/manager/waitlist" class="flex items-center gap-3 p-3 rounded-lg transition-colors hover:opacity-80" :style="{ backgroundColor: themeColors.background }">
                        <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">Waitlist</span>
                    </Link>
                    <Link href="/manager/group-bookings" class="flex items-center gap-3 p-3 rounded-lg transition-colors hover:opacity-80" :style="{ backgroundColor: themeColors.background }">
                        <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">Group Bookings</span>
                    </Link>
                    <Link href="/manager/channel-manager" class="flex items-center gap-3 p-3 rounded-lg transition-colors hover:opacity-80" :style="{ backgroundColor: themeColors.background }">
                        <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">Channel Manager</span>
                    </Link>
                    <Link href="/manager/housekeeping-tasks" class="flex items-center gap-3 p-3 rounded-lg transition-colors hover:opacity-80" :style="{ backgroundColor: themeColors.background }">
                        <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">Housekeeping Tasks</span>
                    </Link>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme'

const { currentTheme } = useTheme()
const themeColors = computed(() => ({
    background: 'var(--kotel-background)',
    card: 'var(--kotel-card)',
    border: 'var(--kotel-border)',
    textPrimary: 'var(--kotel-text-primary)',
    textSecondary: 'var(--kotel-text-secondary)',
    primary: 'var(--kotel-primary)',
    warning: 'var(--kotel-warning)',
    hover: currentTheme.value?.theme_mode === 'dark' ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.02)',
}))

defineProps({
    user: Object,
    navigation: Array,
    summary: { type: Object, default: () => ({ arrivals: 0, departures: 0, pendingTasks: 0 }) },
})
</script>
