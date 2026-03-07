<template>
    <DashboardLayout title="Group Bookings">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Group Bookings</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Manage group reservations and consolidated billing.</p>
                </div>
                <div class="flex space-x-3">
                    <Link href="/admin/group-bookings/create" 
                          class="px-4 py-2 rounded-md transition-colors text-sm font-medium"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                              color: 'white'
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4 mr-2 inline" />
                        New Group Booking
                    </Link>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="p-4 rounded-lg border"
                 :style="{ 
                     backgroundColor: themeColors.background,
                     borderColor: themeColors.border,
                     borderWidth: '1px',
                     borderStyle: 'solid'
                 }">
                <div class="flex items-center">
                    <div class="p-2 rounded-md mr-3"
                         :style="{ backgroundColor: themeColors.primary + '20' }">
                        <UserGroupIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <h3 class="text-sm font-medium"
                            :style="{ color: themeColors.textSecondary }">Total Groups</h3>
                        <p class="text-2xl font-bold mt-1"
                           :style="{ color: themeColors.textPrimary }">{{ stats.total }}</p>
                    </div>
                </div>
            </div>
            <div class="p-4 rounded-lg border"
                 :style="{ 
                     backgroundColor: themeColors.background,
                     borderColor: themeColors.border,
                     borderWidth: '1px',
                     borderStyle: 'solid'
                 }">
                <div class="flex items-center">
                    <div class="p-2 rounded-md mr-3"
                         :style="{ backgroundColor: themeColors.warning + '20' }">
                        <ClockIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <h3 class="text-sm font-medium"
                            :style="{ color: themeColors.textSecondary }">Pending</h3>
                        <p class="text-2xl font-bold mt-1"
                           :style="{ color: themeColors.textPrimary }">{{ stats.pending }}</p>
                    </div>
                </div>
            </div>
            <div class="p-4 rounded-lg border"
                 :style="{ 
                     backgroundColor: themeColors.background,
                     borderColor: themeColors.border,
                     borderWidth: '1px',
                     borderStyle: 'solid'
                 }">
                <div class="flex items-center">
                    <div class="p-2 rounded-md mr-3"
                         :style="{ backgroundColor: themeColors.success + '20' }">
                        <CheckCircleIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <h3 class="text-sm font-medium"
                            :style="{ color: themeColors.textSecondary }">Confirmed</h3>
                        <p class="text-2xl font-bold mt-1"
                           :style="{ color: themeColors.textPrimary }">{{ stats.confirmed }}</p>
                    </div>
                </div>
            </div>
            <div class="p-4 rounded-lg border"
                 :style="{ 
                     backgroundColor: themeColors.background,
                     borderColor: themeColors.border,
                     borderWidth: '1px',
                     borderStyle: 'solid'
                 }">
                <div class="flex items-center">
                    <div class="p-2 rounded-md mr-3"
                         :style="{ backgroundColor: themeColors.warning + '20' }">
                        <HomeIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <h3 class="text-sm font-medium"
                            :style="{ color: themeColors.textSecondary }">Checked In</h3>
                        <p class="text-2xl font-bold mt-1"
                           :style="{ color: themeColors.textPrimary }">{{ stats.checked_in }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Group Bookings Table -->
        <div class="shadow rounded-lg overflow-hidden"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="px-6 py-4 border-b"
                 :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">All Group Bookings</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full"
                       :style="{ borderColor: themeColors.border }">
                    <thead class="border-b"
                           :style="{ 
                               backgroundColor: themeColors.background,
                               borderColor: themeColors.border 
                           }">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Group Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Group Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Primary Guest</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Dates</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Rooms</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Total Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Billing Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-b"
                           :style="{ 
                               backgroundColor: themeColors.card,
                               borderColor: themeColors.border 
                           }">
                        <tr v-for="group in groupBookings.data" :key="group.id" 
                            class="transition-colors"
                            :style="{ 
                                '&:hover': {
                                    backgroundColor: themeColors.hover
                                }
                            }">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">{{ group.group_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">{{ group.group_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                {{ group.primary_guest?.name || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                {{ formatDate(group.check_in_date) }} - {{ formatDate(group.check_out_date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">{{ group.total_rooms }} rooms</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">{{ formatCurrency(group.total_group_amount) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs rounded-full"
                                      :style="{ 
                                          backgroundColor: themeColors.primary + '20',
                                          color: themeColors.primary
                                      }">
                                    {{ formatBillingType(group.billing_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                      :style="{ 
                                          backgroundColor: getStatusColor(group.status) + '20',
                                          color: getStatusColor(group.status)
                                      }">
                                    {{ formatStatus(group.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <Link :href="`/admin/group-bookings/${group.id}`" 
                                          class="transition-colors"
                                          :style="{ color: themeColors.primary }"
                                          @mouseenter="$event.target.style.color = themeColors.hover"
                                          @mouseleave="$event.target.style.color = themeColors.primary">View</Link>
                                    <Link :href="`/admin/group-bookings/${group.id}/edit`" 
                                          class="transition-colors"
                                          :style="{ color: themeColors.warning }"
                                          @mouseenter="$event.target.style.color = 'rgba(255, 255, 255, 0.1)'"
                                          @mouseleave="$event.target.style.color = themeColors.warning">Edit</Link>
                                    <button @click="deleteGroupBooking(group)" 
                                            class="transition-colors"
                                            :style="{ color: themeColors.danger }"
                                            @mouseenter="$event.target.style.color = 'rgba(255, 255, 255, 0.1)'"
                                            @mouseleave="$event.target.style.color = themeColors.danger">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="groupBookings.links" 
                 class="px-6 py-4 border-t"
                 :style="{ borderColor: themeColors.border }">
                <Pagination :links="groupBookings.links" />
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { PlusIcon, UserGroupIcon, ClockIcon, CheckCircleIcon, HomeIcon } from '@heroicons/vue/24/outline'

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

import { getNavigationForRole } from '@/Utils/navigation.js'
import { formatCurrency } from '@/Utils/currency.js'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    user: Object,
    groupBookings: Object,
    stats: Object,
})

const navigation = computed(() => getNavigationForRole('admin'))

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const formatStatus = (status) => {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatBillingType = (type) => {
    return type.charAt(0).toUpperCase() + type.slice(1)
}

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-blue-100 text-blue-800',
        checked_in: 'bg-green-100 text-green-800',
        checked_out: 'bg-gray-100 text-gray-800',
        cancelled: 'bg-red-100 text-red-800',
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const getBillingTypeColor = (type) => {
    const colors = {
        consolidated: 'bg-purple-100 text-purple-800',
        individual: 'bg-blue-100 text-blue-800',
        split: 'bg-green-100 text-green-800',
    }
    return colors[type] || 'bg-gray-100 text-gray-800'
}

const deleteGroupBooking = (group) => {
    if (confirm(`Are you sure you want to delete group booking ${group.group_number}?`)) {
        router.delete(route('admin.group-bookings.destroy', group.id))
    }
}
</script>
