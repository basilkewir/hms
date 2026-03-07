<template>
    <DashboardLayout title="Today's Departures" :user="user">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Today's Departures</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Guests expected to check out today.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('front-desk.checkout')" 
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ 
                              backgroundColor: themeColors.danger,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = '#dc2626'"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.danger">
                        <XCircleIcon class="h-4 w-4 mr-2" />
                        Process Check-Out
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

        <!-- Departure Stats Cards -->
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
                         :style="{ backgroundColor: 'rgba(239, 68, 68, 0.1)' }">
                        <CalendarDaysIcon class="h-6 w-6" :style="{ color: themeColors.danger }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Departures</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ departureStats.total }}</p>
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
                           :style="{ color: themeColors.textSecondary }">Ready</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ departureStats.ready }}</p>
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
                           :style="{ color: themeColors.textPrimary }">{{ departureStats.pending }}</p>
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
                         :style="{ backgroundColor: 'rgba(156, 163, 175, 0.1)' }">
                        <UserIcon class="h-6 w-6" :style="{ color: themeColors.textTertiary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Late</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ departureStats.late }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Departures Table -->
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
                        :style="{ color: themeColors.textPrimary }">Expected Departures</h2>
                    <div class="text-sm"
                         :style="{ color: themeColors.textSecondary }">
                        {{ departures.length }} guests
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
                                Balance
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
                        <tr v-for="departure in departures" :key="departure.id" 
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
                                     :style="{ color: themeColors.textPrimary }">{{ departure.guest_name }}</div>
                                <div class="text-sm"
                                     :style="{ color: themeColors.textSecondary }">{{ departure.guest_email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ departure.confirmation_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textPrimary }">{{ formatDate(departure.check_in_date) }}</div>
                                <div class="text-sm"
                                     :style="{ color: themeColors.textSecondary }">to {{ formatDate(departure.check_out_date) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ departure.room_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(departure.total_amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: getBalanceColor(departure.balance) }">
                                {{ formatCurrency(departure.balance) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusBadgeClass(departure.status)">
                                    {{ formatStatus(departure.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link :href="route('front-desk.reservations.show', departure.id)"
                                      class="mr-3 transition-colors"
                                      :style="{ color: themeColors.primary }"
                                      @mouseenter="$event.target.style.color = themeColors.hover"
                                      @mouseleave="$event.target.style.color = themeColors.primary">View</Link>
                                <Link :href="route('front-desk.reservations.edit', departure.id)"
                                      class="mr-3 transition-colors"
                                      :style="{ color: themeColors.success }"
                                      @mouseenter="$event.target.style.color = themeColors.hover"
                                      @mouseleave="$event.target.style.color = themeColors.success">Edit</Link>
                                <button @click="checkOut(departure)"
                                        class="px-3 py-1 rounded-md text-xs font-medium transition-colors text-white"
                                        :style="{ backgroundColor: themeColors.danger }"
                                        @mouseenter="$event.target.style.backgroundColor = '#dc2626'"
                                        @mouseleave="$event.target.style.backgroundColor = themeColors.danger">
                                    Check Out
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Empty State -->
            <div v-if="!departures || departures.length === 0" class="text-center py-12">
                <CalendarDaysIcon class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium" :style="{ color: themeColors.textPrimary }">No departures today</h3>
                <p class="mt-1 text-sm" :style="{ color: themeColors.textSecondary }">No guests are scheduled to check out today.</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import {
    CalendarDaysIcon,
    CheckCircleIcon,
    ClockIcon,
    UserIcon,
    ArrowPathIcon,
    XCircleIcon
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
    departures: Array,
})

const departureStats = computed(() => {
    if (!props.departures) {
        return {
            total: 0,
            ready: 0,
            pending: 0,
            late: 0
        }
    }

    const stats = {
        total: props.departures.length,
        ready: 0,
        pending: 0,
        late: 0
    }

    props.departures.forEach(departure => {
        switch (departure.status) {
            case 'confirmed':
            case 'checked_in':
                stats.ready++
                break
            case 'pending':
                stats.pending++
                break
        }
        
        // Check if departure is late (past expected checkout time)
        if (departure.check_out_date) {
            const checkoutDate = new Date(departure.check_out_date)
            const now = new Date()
            if (checkoutDate < now) {
                stats.late++
            }
        }
    })

    return stats
})

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'confirmed':
        case 'checked_in':
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
        case 'checked_in':
            return 'Ready'
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

const getBalanceColor = (balance) => {
    const amount = parseFloat(balance?.replace(/,/g, '') || 0)
    if (amount > 0) return themeColors.danger
    if (amount < 0) return themeColors.warning
    return themeColors.success
}

const checkOut = (departure) => {
    router.visit(route('front-desk.checkout'), {
        method: 'post',
        data: {
            reservation_id: departure.id
        }
    })
}
</script>
