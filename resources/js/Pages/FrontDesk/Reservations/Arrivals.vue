<template>
    <DashboardLayout title="Today's Arrivals" :user="user">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Today's Arrivals</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Guests expected to check in today.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('front-desk.checkin')" 
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <CheckCircleIcon class="h-4 w-4 mr-2" />
                        Process Check-In
                    </Link>
                    <Link :href="route('front-desk.reservations.index')" 
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ 
                              backgroundColor: themeColors.secondary,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        <ArrowPathIcon class="h-4 w-4 mr-2" />
                        Back to Reservations
                    </Link>
                </div>
            </div>
        </div>

        <!-- Arrival Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                        <CalendarDaysIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Arrivals</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ arrivalStats.total }}</p>
                    </div>
                </div>
            </div>
            
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                        <CheckCircleIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Confirmed</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ arrivalStats.confirmed }}</p>
                    </div>
                </div>
            </div>
            
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(250, 204, 21, 0.1)' }">
                        <ClockIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Pending</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ arrivalStats.pending }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(239, 68, 68, 0.1)' }">
                        <HomeIcon class="h-6 w-6" :style="{ color: themeColors.danger }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">No Room</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ arrivalStats.noRoom }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Arrivals Table -->
        <div class="shadow rounded-lg overflow-hidden"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <div class="px-6 py-4 border-b"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderBottomWidth: '1px'
                 }">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Expected Arrivals</h2>
                    <div class="text-sm"
                         :style="{ color: themeColors.textSecondary }">
                        {{ arrivals.length }} guests
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Guest
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Confirmation
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Dates
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Room
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Total
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="arrival in arrivals" :key="arrival.id" 
                            class="transition-colors"
                            :style="{ 
                                borderBottomStyle: 'solid',
                                borderBottomWidth: '1px',
                                borderColor: themeColors.border
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium"
                                     :style="{ color: themeColors.textPrimary }">{{ arrival.guest_name }}</div>
                                <div class="text-sm"
                                     :style="{ color: themeColors.textSecondary }">{{ arrival.guest_email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ arrival.confirmation_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textPrimary }">{{ formatDate(arrival.check_in_date) }}</div>
                                <div class="text-sm"
                                     :style="{ color: themeColors.textSecondary }">to {{ formatDate(arrival.check_out_date) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: arrival.room_number ? themeColors.textPrimary : themeColors.warning }">
                                {{ arrival.room_number || 'Not Assigned' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(arrival.total_amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusBadgeClass(arrival.status)">
                                    {{ formatStatus(arrival.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link :href="route('front-desk.reservations.show', arrival.id)"
                                      class="mr-3 transition-colors"
                                      :style="{ color: themeColors.primary }"
                                      @mouseenter="$event.target.style.color = themeColors.hover"
                                      @mouseleave="$event.target.style.color = themeColors.primary">View</Link>
                                <Link :href="route('front-desk.reservations.edit', arrival.id)"
                                      class="mr-3 transition-colors"
                                      :style="{ color: themeColors.success }"
                                      @mouseenter="$event.target.style.color = themeColors.hover"
                                      @mouseleave="$event.target.style.color = themeColors.success">Edit</Link>
                                <Link v-if="arrival.status === 'confirmed'" :href="route('front-desk.checkin')"
                                      class="transition-colors"
                                      :style="{ color: '#8b5cf6' }"
                                      @mouseenter="$event.target.style.color = '#7c3aed'"
                                      @mouseleave="$event.target.style.color = '#8b5cf6'">Check-In</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Empty State -->
            <div v-if="!arrivals || arrivals.length === 0" class="text-center py-12">
                <CalendarDaysIcon class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium" :style="{ color: themeColors.textPrimary }">No arrivals today</h3>
                <p class="mt-1 text-sm" :style="{ color: themeColors.textSecondary }">No guests are scheduled to check in today.</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import {
    CalendarDaysIcon,
    CheckCircleIcon,
    ClockIcon,
    HomeIcon,
    ArrowPathIcon
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
    arrivals: Array,
})

const arrivalStats = computed(() => {
    if (!props.arrivals) {
        return {
            total: 0,
            confirmed: 0,
            pending: 0,
            noRoom: 0
        }
    }

    const stats = {
        total: props.arrivals.length,
        confirmed: 0,
        pending: 0,
        noRoom: 0
    }

    props.arrivals.forEach(arrival => {
        switch (arrival.status) {
            case 'confirmed':
                stats.confirmed++
                break
            case 'pending':
                stats.pending++
                break
        }
        
        if (!arrival.room_number) {
            stats.noRoom++
        }
    })

    return stats
})

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'confirmed':
            return 'bg-green-100 text-green-800'
        case 'pending':
            return 'bg-yellow-100 text-yellow-800'
        case 'cancelled':
            return 'bg-red-100 text-red-800'
        default:
            return 'bg-gray-100 text-gray-800'
    }
}

const formatStatus = (status) => {
    switch (status) {
        case 'confirmed':
            return 'Confirmed'
        case 'pending':
            return 'Pending'
        case 'cancelled':
            return 'Cancelled'
        default:
            return status || 'Unknown'
    }
}

const formatDate = (date) => {
    if (!date) return 'N/A'
    try {
        const d = new Date(date)
        return d.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric'
        })
    } catch (e) {
        return date
    }
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount || 0)
}
</script>
