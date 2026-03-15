<template>
    <DashboardLayout title="Hall Bookings">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Hall Bookings</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Manage standalone hall bookings (separate from room reservations).</p>
                </div>
                <Link :href="route(`${routePrefix}.hall-bookings.create`)"
                      class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                      :style="{ backgroundColor: themeColors.primary }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                    <PlusIcon class="h-4 w-4 mr-2" />
                    New Hall Booking
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm" :style="cardStyle">
                <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Total</p>
                <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ stats?.total || 0 }}</p>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="cardStyle">
                <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Pending</p>
                <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ stats?.pending || 0 }}</p>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="cardStyle">
                <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Confirmed</p>
                <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ stats?.confirmed || 0 }}</p>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="cardStyle">
                <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Completed</p>
                <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ stats?.completed || 0 }}</p>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="cardStyle">
                <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Cancelled</p>
                <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ stats?.cancelled || 0 }}</p>
            </div>
        </div>

        <div class="shadow rounded-lg p-6" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div v-if="bookings?.data?.length" class="overflow-x-auto">
                <table class="min-w-full">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Booking #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Hall</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Event Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Balance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody :style="{ backgroundColor: themeColors.card }">
                        <tr v-for="b in bookings.data" :key="b.id" class="border-t" :style="{ borderColor: themeColors.border }">
                            <td class="px-6 py-4 text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ b.booking_number }}</td>
                            <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textPrimary }">{{ b.hall?.name || 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textPrimary }">{{ formatDate(b.event_date) }}</td>
                            <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textPrimary }">{{ b.start_time }} - {{ b.end_time }}</td>
                            <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textPrimary }">{{ b.contact_name }}</td>
                            <td class="px-6 py-4 text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(b.total_amount) }}</td>
                            <td class="px-6 py-4 text-sm font-medium"
                                :style="{ color: (b.total_amount - b.paid_amount) > 0 ? themeColors.danger : themeColors.success }">
                                {{ formatCurrency(Math.max(0, b.total_amount - b.paid_amount)) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full" :style="getStatusStyle(b.status)">{{ formatStatus(b.status) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm">
                                <div class="flex items-center justify-end gap-3">
                                    <Link :href="route(`${routePrefix}.hall-bookings.show`, b.id)"
                                          class="hover:opacity-80 font-medium" :style="{ color: themeColors.primary }">View</Link>
                                    <Link :href="route(`${routePrefix}.hall-bookings.edit`, b.id)"
                                          class="hover:opacity-80 font-medium" :style="{ color: themeColors.warning }">Edit</Link>
                                    <button type="button" @click="deleteBooking(b)"
                                            class="hover:opacity-80 font-medium" :style="{ color: themeColors.danger }">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="text-center py-8" :style="{ color: themeColors.textSecondary }">
                No hall bookings yet.
            </div>

            <Pagination v-if="bookings" :links="bookings.links" class="mt-6" />
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { PlusIcon } from '@heroicons/vue/24/outline'
import { formatCurrency } from '@/Utils/currency.js'

const props = defineProps({
    user: Object,
    bookings: Object,
    stats: Object,
    routePrefix: { type: String, default: 'admin' },
    filters: Object,
})

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

const cardStyle = computed(() => ({
    backgroundColor: themeColors.value.card,
    borderColor: themeColors.value.border,
    borderStyle: 'solid',
    borderWidth: '1px'
}))

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const formatStatus = (status) => {
    return (status || '').replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const deleteBooking = (booking) => {
    if (confirm(`Delete booking ${booking.booking_number}? This cannot be undone.`)) {
        router.delete(route(`${props.routePrefix}.hall-bookings.destroy`, booking.id))
    }
}

const getStatusStyle = (status) => {
    const styles = {
        pending: { backgroundColor: 'rgba(250, 204, 21, 0.15)', color: themeColors.value.warning },
        confirmed: { backgroundColor: 'rgba(59, 130, 246, 0.15)', color: themeColors.value.primary },
        completed: { backgroundColor: 'rgba(34, 197, 94, 0.15)', color: themeColors.value.success },
        cancelled: { backgroundColor: 'rgba(239, 68, 68, 0.15)', color: themeColors.value.danger },
    }

    return styles[status] || { backgroundColor: themeColors.value.background, color: themeColors.value.textSecondary }
}
</script>
