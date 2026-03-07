<template>
    <DashboardLayout title="Waitlist Management">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Waitlist Management</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Manage guest waitlist entries and availability</p>
                </div>
                <div class="flex space-x-4">
                    <Link :href="route(`${routePrefix}.waitlist.create`)"
                          class="px-4 py-2 rounded-md transition-colors text-sm font-medium"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                              color: 'white'
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        Add Waitlist Entry
                    </Link>
                    <button @click="autoNotify"
                            class="px-4 py-2 rounded-md transition-colors text-sm font-medium"
                            :style="{ 
                                backgroundColor: themeColors.success,
                                color: 'white'
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                        Auto Notify
                    </button>
                    <Link :href="route(`${routePrefix}.waitlist.check-availability`)"
                          class="px-4 py-2 rounded-md transition-colors text-sm font-medium"
                          :style="{ 
                                backgroundColor: themeColors.warning,
                                color: 'white'
                            }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.warning">
                        Check Availability
                    </Link>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="p-4 rounded-lg"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="text-sm"
                         :style="{ color: themeColors.textSecondary }">Total Entries</div>
                    <div class="text-2xl font-bold mt-1"
                         :style="{ color: themeColors.textPrimary }">{{ stats?.total || 0 }}</div>
                </div>
                <div class="p-4 rounded-lg"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="text-sm"
                         :style="{ color: themeColors.textSecondary }">Active</div>
                    <div class="text-2xl font-bold mt-1"
                         :style="{ color: themeColors.primary }">{{ stats?.active || 0 }}</div>
                </div>
                <div class="p-4 rounded-lg"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="text-sm"
                         :style="{ color: themeColors.textSecondary }">Notified</div>
                    <div class="text-2xl font-bold mt-1"
                         :style="{ color: themeColors.warning }">{{ stats?.notified || 0 }}</div>
                </div>
                <div class="p-4 rounded-lg"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="text-sm"
                         :style="{ color: themeColors.textSecondary }">Converted</div>
                    <div class="text-2xl font-bold mt-1"
                         :style="{ color: themeColors.success }">{{ stats?.converted || 0 }}</div>
                </div>
                <div class="p-4 rounded-lg"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="text-sm"
                         :style="{ color: themeColors.textSecondary }">Expired</div>
                    <div class="text-2xl font-bold mt-1"
                         :style="{ color: themeColors.danger }">{{ stats?.expired || 0 }}</div>
                </div>
            </div>

            <!-- Waitlist Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full"
                       :style="{ 
                           borderColor: themeColors.border,
                           borderWidth: '1px',
                           borderStyle: 'solid'
                       }">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Guest
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Room Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Dates
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Priority
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="waitlist in waitlists?.data || []" :key="waitlist?.id"
                            :style="{ 
                                borderColor: themeColors.border,
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium"
                                         :style="{ color: themeColors.textPrimary }">{{ waitlist?.guest_name || 'N/A' }}</div>
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textSecondary }">{{ waitlist?.guest_email || 'N/A' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textPrimary }">{{ waitlist?.room_type || 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textPrimary }">{{ waitlist?.requested_dates || 'N/A' }}</div>
                                <div class="text-sm"
                                     :style="{ color: themeColors.textSecondary }">{{ waitlist?.requested_nights || 0 }} nights</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="getPriorityStyle(waitlist?.priority || 0)">
                                    {{ waitlist?.priority || 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="getStatusStyle(waitlist?.status || 'active')">
                                    {{ waitlist?.status || 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <Link :href="route(`${routePrefix}.waitlist.show`, waitlist?.id)"
                                          class="transition-colors"
                                          :style="{ color: themeColors.primary }"
                                          @mouseenter="$event.target.style.color = themeColors.hover"
                                          @mouseleave="$event.target.style.color = themeColors.primary">
                                        View
                                    </Link>
                                    <button @click="notifyGuest(waitlist?.id)"
                                            v-if="waitlist?.status === 'active'"
                                            class="transition-colors"
                                            :style="{ color: themeColors.success }"
                                            @mouseenter="$event.target.style.color = themeColors.hover"
                                            @mouseleave="$event.target.style.color = themeColors.success">
                                        Notify
                                    </button>
                                    <button @click="convertToReservation(waitlist?.id)"
                                            v-if="waitlist?.can_convert"
                                            class="transition-colors"
                                            :style="{ color: themeColors.primary }"
                                            @mouseenter="$event.target.style.color = themeColors.hover"
                                            @mouseleave="$event.target.style.color = themeColors.primary">
                                        Convert
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6" v-if="waitlists?.links">
                <nav class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <Link :href="waitlists?.prev_page_url"
                              class="relative inline-flex items-center px-4 py-2 rounded-md text-sm font-medium transition-colors"
                              :style="{ 
                                  backgroundColor: themeColors.background,
                                  borderColor: themeColors.border,
                                  color: themeColors.textPrimary,
                                  borderWidth: '1px',
                                  borderStyle: 'solid'
                              }"
                              @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                              @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                            Previous
                        </Link>
                        <Link :href="waitlists?.next_page_url"
                              class="ml-3 relative inline-flex items-center px-4 py-2 rounded-md text-sm font-medium transition-colors"
                              :style="{ 
                                  backgroundColor: themeColors.background,
                                  borderColor: themeColors.border,
                                  color: themeColors.textPrimary,
                                  borderWidth: '1px',
                                  borderStyle: 'solid'
                              }"
                              @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                              @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                            Next
                        </Link>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm"
                               :style="{ color: themeColors.textSecondary }">
                                Showing
                                <span class="font-medium"
                                      :style="{ color: themeColors.textPrimary }">{{ waitlists?.from || 0 }}</span>
                                to
                                <span class="font-medium"
                                      :style="{ color: themeColors.textPrimary }">{{ waitlists?.to || 0 }}</span>
                                of
                                <span class="font-medium"
                                      :style="{ color: themeColors.textPrimary }">{{ waitlists?.total || 0 }}</span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <template v-for="link in waitlists?.links" :key="link.label">
                                    <Link v-if="link?.url && link?.label !== '...'"
                                          :href="link.url"
                                          class="relative inline-flex items-center px-4 py-2 border text-sm font-medium transition-colors"
                                          :style="link.active ? { 
                                              backgroundColor: themeColors.primary,
                                              borderColor: themeColors.primary,
                                              color: 'white'
                                          } : { 
                                              backgroundColor: themeColors.background,
                                              borderColor: themeColors.border,
                                              color: themeColors.textPrimary,
                                              borderWidth: '1px',
                                              borderStyle: 'solid'
                                          }"
                                          @mouseenter="!link.active && ($event.target.style.backgroundColor = themeColors.hover)"
                                          @mouseleave="!link.active && ($event.target.style.backgroundColor = themeColors.background)">
                                        {{ link.label }}
                                    </Link>
                                    <span v-else-if="link?.label === '...'"
                                          class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                          :style="{ 
                                              backgroundColor: themeColors.background,
                                              borderColor: themeColors.border,
                                              color: themeColors.textPrimary,
                                              borderWidth: '1px',
                                              borderStyle: 'solid'
                                          }">
                                        ...
                                    </span>
                                </template>
                            </nav>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { formatCurrency } from '@/Utils/currency';
import { useTheme } from '@/Composables/useTheme.js';
import { computed } from 'vue';

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
    waitlists: Object,
    stats: Object,
    routePrefix: { type: String, default: 'admin' },
});

const getStatusStyle = (status) => {
    const styles = {
        'active': {
            backgroundColor: `var(--kotel-primary)`,
            color: 'white'
        },
        'notified': {
            backgroundColor: `var(--kotel-warning)`,
            color: 'white'
        },
        'converted': {
            backgroundColor: `var(--kotel-success)`,
            color: 'white'
        },
        'expired': {
            backgroundColor: `var(--kotel-danger)`,
            color: 'white'
        },
        'cancelled': {
            backgroundColor: `var(--kotel-secondary)`,
            color: 'white'
        }
    };
    return styles[status] || styles['active'];
};

const getPriorityStyle = (priority) => {
    if (priority >= 8) {
        return {
            backgroundColor: `var(--kotel-danger)`,
            color: 'white'
        };
    }
    if (priority >= 5) {
        return {
            backgroundColor: `var(--kotel-warning)`,
            color: 'white'
        };
    }
    return {
        backgroundColor: `var(--kotel-success)`,
        color: 'white'
    };
};

const autoNotify = async () => {
    if (confirm('Send automatic notifications to all eligible waitlist entries?')) {
        try {
            const response = await fetch(route(`${props.routePrefix}.waitlist.auto-notify`), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();
            if (data.success) {
                alert(`Auto-notification completed. ${data.notifications_sent} notifications sent.`);
                window.location.reload();
            } else {
                alert('Failed to complete auto-notification');
            }
        } catch (error) {
            alert('An error occurred during auto-notification');
        }
    }
};

const notifyGuest = async (waitlistId) => {
    if (confirm('Send notification to guest about room availability?')) {
        try {
            const response = await fetch(route(`${props.routePrefix}.waitlist.notify`, waitlistId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Failed to send notification');
            }
        } catch (error) {
            alert('An error occurred while sending notification');
        }
    }
};

const convertToReservation = async (waitlistId) => {
    if (confirm('Convert this waitlist entry to a reservation?')) {
        try {
            const response = await fetch(route(`${props.routePrefix}.waitlist.convert`, waitlistId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();
            if (data.success) {
                window.location.href = data.redirect || '/admin/reservations';
            } else {
                alert(data.message || 'Failed to convert waitlist entry');
            }
        } catch (error) {
            alert('An error occurred while converting the waitlist entry');
        }
    }
};
</script>
