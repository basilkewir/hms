<template>
    <DashboardLayout title="Channel Manager">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Channel Manager</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Manage OTA bookings and channel inventory</p>
                </div>
                <div class="flex space-x-3">
                    <Link href="/admin/channel-manager/create"
                          class="px-4 py-2 rounded-md transition-colors text-sm font-medium"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                              color: 'white'
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4 mr-2 inline" />
                        Add OTA Booking
                    </Link>
                    <button @click="syncInventory"
                            class="px-4 py-2 rounded-md transition-colors text-sm font-medium"
                            :style="{ 
                                backgroundColor: themeColors.success,
                                color: 'white'
                            }"
                            @mouseenter="$event.target.style.backgroundColor = 'rgba(255, 255, 255, 0.1)'"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                        <ArrowPathIcon class="h-4 w-4 mr-2 inline" />
                        Sync Inventory
                    </button>
                </div>
            </div>

                    <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
                <div class="p-4 rounded-lg border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="text-sm font-medium"
                        :style="{ color: themeColors.textSecondary }">Total OTA Bookings</div>
                    <div class="text-2xl font-bold mt-1"
                         :style="{ color: themeColors.textPrimary }">{{ stats.total }}</div>
                </div>
                <div class="p-4 rounded-lg border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="text-sm font-medium"
                        :style="{ color: themeColors.textSecondary }">Booking.com</div>
                    <div class="text-2xl font-bold mt-1"
                         :style="{ color: themeColors.primary }">{{ stats.booking_com }}</div>
                </div>
                <div class="p-4 rounded-lg border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="text-sm font-medium"
                        :style="{ color: themeColors.textSecondary }">Expedia</div>
                    <div class="text-2xl font-bold mt-1"
                         :style="{ color: themeColors.warning }">{{ stats.expedia }}</div>
                </div>
                <div class="p-4 rounded-lg border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="text-sm font-medium"
                        :style="{ color: themeColors.textSecondary }">Agoda</div>
                    <div class="text-2xl font-bold mt-1"
                         :style="{ color: themeColors.danger }">{{ stats.agoda }}</div>
                </div>
                <div class="p-4 rounded-lg border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="text-sm font-medium"
                        :style="{ color: themeColors.textSecondary }">Travel Agent</div>
                    <div class="text-2xl font-bold mt-1"
                         :style="{ color: themeColors.success }">{{ stats.travel_agent }}</div>
                </div>
                <div class="p-4 rounded-lg border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="text-sm font-medium"
                        :style="{ color: themeColors.textSecondary }">Corporate</div>
                    <div class="text-2xl font-bold mt-1"
                         :style="{ color: themeColors.primary }">{{ stats.corporate }}</div>
                </div>
            </div>

                    <!-- Filters and Search -->
            <div class="shadow rounded-lg p-6 mb-8"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text"
                               v-model="search"
                               placeholder="Search reservations, guests, booking references..."
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                    </div>
                    <select v-model="bookingSourceFilter" 
                            class="px-3 py-2 rounded-md focus:outline-none transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary,
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }">
                        <option value="">All Booking Sources</option>
                        <option value="booking_com">Booking.com</option>
                        <option value="expedia">Expedia</option>
                        <option value="agoda">Agoda</option>
                        <option value="travel_agent">Travel Agent</option>
                        <option value="corporate">Corporate</option>
                    </select>
                    <select v-model="statusFilter" 
                            class="px-3 py-2 rounded-md focus:outline-none transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary,
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="checked_in">Checked In</option>
                        <option value="checked_out">Checked Out</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="no_show">No Show</option>
                    </select>
                </div>
            </div>

                    <!-- Reservations Table -->
            <div class="shadow rounded-lg overflow-hidden"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
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
                                    :style="{ color: themeColors.textSecondary }">Reservation</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Guest</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Dates</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Commission</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="border-b"
                               :style="{ 
                                   backgroundColor: themeColors.card,
                                   borderColor: themeColors.border 
                               }">
                            <tr v-for="reservation in filteredReservations" :key="reservation.id" 
                                class="transition-colors"
                                :style="{ 
                                    '&:hover': {
                                        backgroundColor: themeColors.hover
                                    }
                                }">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium"
                                         :style="{ color: themeColors.textPrimary }">{{ reservation.reservation_number }}</div>
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textSecondary }">{{ reservation.booking_reference }}</div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :style="{ 
                                              backgroundColor: themeColors.primary + '20',
                                              color: themeColors.primary
                                          }">
                                        {{ bookingSources[reservation.booking_source] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium"
                                         :style="{ color: themeColors.textPrimary }">{{ reservation.guest_name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm"
                                    :style="{ color: themeColors.textSecondary }">
                                    <div>{{ reservation.check_in_date }}</div>
                                    <div :style="{ color: themeColors.textTertiary }">→</div>
                                    <div>{{ reservation.check_out_date }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textPrimary }">{{ reservation.room_type }}</div>
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textSecondary }">{{ reservation.room_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm"
                                    :style="{ color: themeColors.textPrimary }">
                                    {{ formatCurrency(reservation.total_amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                          :style="{ 
                                              backgroundColor: getStatusColor(reservation.status) + '20',
                                              color: getStatusColor(reservation.status)
                                          }">
                                        {{ reservation.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div :style="{ color: themeColors.textPrimary }">{{ formatCurrency(reservation.commission_amount) }}</div>
                                    <div :style="{ color: themeColors.textSecondary }">{{ reservation.commission_rate }}%</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <Link :href="`/admin/channel-manager/${reservation.id}`"
                                          class="mr-4 transition-colors"
                                          :style="{ color: themeColors.primary }"
                                          @mouseenter="$event.target.style.color = themeColors.hover"
                                          @mouseleave="$event.target.style.color = themeColors.primary">
                                        View
                                    </Link>
                                    <Link :href="`/admin/channel-manager/${reservation.id}/edit`"
                                          class="mr-4 transition-colors"
                                          :style="{ color: themeColors.warning }"
                                          @mouseenter="$event.target.style.color = 'rgba(255, 255, 255, 0.1)'"
                                          @mouseleave="$event.target.style.color = themeColors.warning">
                                        Edit
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t"
                     :style="{ borderColor: themeColors.border }">
                    <div class="flex justify-between items-center">
                        <div class="text-sm"
                             :style="{ color: themeColors.textSecondary }">
                            Showing {{ reservations.from }} to {{ reservations.to }} of {{ reservations.total }} results
                        </div>
                        <div class="flex space-x-2">
                            <button v-if="reservations.prev_page_url"
                                    @click="loadPage(reservations.current_page - 1)"
                                    class="px-3 py-1 rounded-md transition-colors"
                                    :style="{ 
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        backgroundColor: themeColors.background,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }"
                                    @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                    @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                                Previous
                            </button>
                            <button v-if="reservations.next_page_url"
                                    @click="loadPage(reservations.current_page + 1)"
                                    class="px-3 py-1 rounded-md transition-colors"
                                    :style="{ 
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        backgroundColor: themeColors.background,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }"
                                    @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                    @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { computed, ref } from 'vue'
import { PlusIcon, ArrowPathIcon } from '@heroicons/vue/24/outline'

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
    reservations: Object,
    stats: Object,
    commission_rates: Object
});

const search = ref('')
const bookingSourceFilter = ref('')
const statusFilter = ref('')

const bookingSources = {
    'booking_com': 'Booking.com',
    'expedia': 'Expedia',
    'agoda': 'Agoda',
    'travel_agent': 'Travel Agent',
    'corporate': 'Corporate'
}

const filteredReservations = computed(() => {
    let filtered = props.reservations.data.filter(reservation => {
        const matchesSearch = reservation.reservation_number.toLowerCase().includes(search.value.toLowerCase()) ||
                             reservation.guest_name.toLowerCase().includes(search.value.toLowerCase()) ||
                             reservation.booking_reference.toLowerCase().includes(search.value.toLowerCase());

        const matchesBookingSource = !bookingSourceFilter.value || reservation.booking_source === bookingSourceFilter.value;
        const matchesStatus = !statusFilter.value || reservation.status === statusFilter.value;

        return matchesSearch && matchesBookingSource && matchesStatus;
    });

    return filtered;
});

const getStatusColor = (status) => {
    const colors = {
        'pending': themeColors.value.warning,
        'confirmed': themeColors.value.success,
        'checked_in': themeColors.value.primary,
        'checked_out': themeColors.value.textSecondary,
        'cancelled': themeColors.value.danger,
        'no_show': themeColors.value.warning
    };
    return colors[status] || colors['pending'];
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-CM', {
        style: 'currency',
        currency: 'XAF'
    }).format(amount);
};

const loadPage = (page) => {
    router.get(`/admin/channel-manager?page=${page}`);
};

const syncInventory = async () => {
    try {
        const response = await fetch('/admin/channel-manager/sync-inventory', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();
        if (data.success) {
            // You would typically use a notification system here
            alert(data.data.message);
        }
    } catch (error) {
        alert('Failed to sync inventory');
    }
};
</script>
