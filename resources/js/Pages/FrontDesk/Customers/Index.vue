<template>
    <DashboardLayout title="Customers" :user="user">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Customers</h1>
                    <p class="text-sm mt-2"
                       :style="{ color: themeColors.textSecondary }">Manage customer information and reservations.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('front-desk.guests.create')" 
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <UserPlusIcon class="h-4 w-4 mr-2" />
                        Add New Customer
                    </Link>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="flex-shrink-0"
                         :style="{ color: themeColors.primary }">
                        <UserGroupIcon class="h-8 w-8" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Total Customers</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.total || 0 }}</p>
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
                    <div class="flex-shrink-0"
                         :style="{ color: themeColors.success }">
                        <CheckCircleIcon class="h-8 w-8" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Active Guests</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.active || 0 }}</p>
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
                    <div class="flex-shrink-0"
                         :style="{ color: themeColors.warning }">
                        <ClockIcon class="h-8 w-8" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Pending Check-ins</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.pending || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <Link :href="route('front-desk.guests.current')"
                  class="rounded-lg p-6 border shadow-sm hover:shadow-md transition-shadow cursor-pointer"
                  :style="{ 
                      backgroundColor: themeColors.card,
                      borderColor: themeColors.border,
                      borderStyle: 'solid',
                      borderWidth: '1px'
                  }">
                <div class="flex items-center">
                    <div class="flex-shrink-0"
                         :style="{ color: themeColors.primary }">
                        <UserIcon class="h-8 w-8" />
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium"
                            :style="{ color: themeColors.textPrimary }">Current Guests</h3>
                        <p class="text-sm mt-1"
                           :style="{ color: themeColors.textSecondary }">View currently staying guests</p>
                    </div>
                </div>
            </Link>

            <Link :href="route('front-desk.reservations.arrivals')"
                  class="rounded-lg p-6 border shadow-sm hover:shadow-md transition-shadow cursor-pointer"
                  :style="{ 
                      backgroundColor: themeColors.card,
                      borderColor: themeColors.border,
                      borderStyle: 'solid',
                      borderWidth: '1px'
                  }">
                <div class="flex items-center">
                    <div class="flex-shrink-0"
                         :style="{ color: themeColors.success }">
                        <CalendarDaysIcon class="h-8 w-8" />
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium"
                            :style="{ color: themeColors.textPrimary }">Today's Arrivals</h3>
                        <p class="text-sm mt-1"
                           :style="{ color: themeColors.textSecondary }">Manage guest check-ins</p>
                    </div>
                </div>
            </Link>

            <Link :href="route('front-desk.reservations.departures')"
                  class="rounded-lg p-6 border shadow-sm hover:shadow-md transition-shadow cursor-pointer"
                  :style="{ 
                      backgroundColor: themeColors.card,
                      borderColor: themeColors.border,
                      borderStyle: 'solid',
                      borderWidth: '1px'
                  }">
                <div class="flex items-center">
                    <div class="flex-shrink-0"
                         :style="{ color: themeColors.warning }">
                        <ArrowPathIcon class="h-8 w-8" />
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium"
                            :style="{ color: themeColors.textPrimary }">Today's Departures</h3>
                        <p class="text-sm mt-1"
                           :style="{ color: themeColors.textSecondary }">Manage guest check-outs</p>
                    </div>
                </div>
            </Link>
        </div>

        <!-- Recent Activity -->
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
                <h2 class="text-lg font-semibold"
                    :style="{ color: themeColors.textPrimary }">Quick Navigation</h2>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Link :href="route('front-desk.guests.index')"
                          class="flex items-center p-4 rounded-lg border hover:shadow-md transition-shadow"
                          :style="{ 
                              backgroundColor: themeColors.background,
                              borderColor: themeColors.border,
                              borderStyle: 'solid',
                              borderWidth: '1px'
                          }">
                        <UsersIcon class="h-6 w-6 mr-3"
                                  :style="{ color: themeColors.primary }" />
                        <div>
                            <div class="font-medium"
                                 :style="{ color: themeColors.textPrimary }">Guest Management</div>
                            <div class="text-sm"
                                 :style="{ color: themeColors.textSecondary }">View and manage all guests</div>
                        </div>
                    </Link>

                    <Link :href="route('front-desk.reservations.index')"
                          class="flex items-center p-4 rounded-lg border hover:shadow-md transition-shadow"
                          :style="{ 
                              backgroundColor: themeColors.background,
                              borderColor: themeColors.border,
                              borderStyle: 'solid',
                              borderWidth: '1px'
                          }">
                        <CalendarDaysIcon class="h-6 w-6 mr-3"
                                        :style="{ color: themeColors.primary }" />
                        <div>
                            <div class="font-medium"
                                 :style="{ color: themeColors.textPrimary }">Reservations</div>
                            <div class="text-sm"
                                 :style="{ color: themeColors.textSecondary }">Manage booking reservations</div>
                        </div>
                    </Link>

                    <Link :href="route('front-desk.checkin')"
                          class="flex items-center p-4 rounded-lg border hover:shadow-md transition-shadow"
                          :style="{ 
                              backgroundColor: themeColors.background,
                              borderColor: themeColors.border,
                              borderStyle: 'solid',
                              borderWidth: '1px'
                          }">
                        <CheckCircleIcon class="h-6 w-6 mr-3"
                                        :style="{ color: themeColors.success }" />
                        <div>
                            <div class="font-medium"
                                 :style="{ color: themeColors.textPrimary }">Check-in</div>
                            <div class="text-sm"
                                 :style="{ color: themeColors.textSecondary }">Process guest check-ins</div>
                        </div>
                    </Link>

                    <Link :href="route('front-desk.checkout')"
                          class="flex items-center p-4 rounded-lg border hover:shadow-md transition-shadow"
                          :style="{ 
                              backgroundColor: themeColors.background,
                              borderColor: themeColors.border,
                              borderStyle: 'solid',
                              borderWidth: '1px'
                          }">
                        <ArrowPathIcon class="h-6 w-6 mr-3"
                                      :style="{ color: themeColors.warning }" />
                        <div>
                            <div class="font-medium"
                                 :style="{ color: themeColors.textPrimary }">Check-out</div>
                            <div class="text-sm"
                                 :style="{ color: themeColors.textSecondary }">Process guest check-outs</div>
                        </div>
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
import { useTheme } from '@/Composables/useTheme.js'
import {
    UserPlusIcon,
    UserGroupIcon,
    CheckCircleIcon,
    ClockIcon,
    UserIcon,
    CalendarDaysIcon,
    ArrowPathIcon,
    UsersIcon
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
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            active: 0,
            pending: 0
        })
    }
})
</script>
