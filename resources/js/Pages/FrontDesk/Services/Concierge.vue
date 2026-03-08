<template>
    <DashboardLayout title="Concierge Services" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-6" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Concierge Services</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Manage guest concierge requests and hall bookings.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button v-if="activeTab === 'requests'" @click="showNewRequest = true"
                            class="px-4 py-2 rounded-md font-medium text-white flex items-center gap-2"
                            :style="{ backgroundColor: themeColors.primary }">
                        <PlusIcon class="h-4 w-4" /> New Request
                    </button>
                    <button v-if="activeTab === 'halls'" @click="showNewHallBooking = true"
                            class="px-4 py-2 rounded-md font-medium text-white flex items-center gap-2"
                            :style="{ backgroundColor: '#8b5cf6' }">
                        <PlusIcon class="h-4 w-4" /> Book Hall
                    </button>
                    <button @click="exportData"
                            class="px-4 py-2 rounded-md font-medium text-white flex items-center gap-2"
                            :style="{ backgroundColor: '#6b7280' }">
                        <DocumentArrowDownIcon class="h-4 w-4" /> Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="mb-6 border-b" :style="{ borderColor: themeColors.border }">
            <nav class="flex gap-6">
                <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                        class="pb-3 px-1 text-sm font-medium border-b-2 transition-colors"
                        :class="activeTab === tab.id ? 'border-blue-500' : 'border-transparent'"
                        :style="{ color: activeTab === tab.id ? themeColors.primary : themeColors.textSecondary }">
                    {{ tab.label }}
                    <span class="ml-1 px-2 py-0.5 rounded-full text-xs"
                          :style="{ backgroundColor: activeTab === tab.id ? themeColors.primary : themeColors.border, color: activeTab === tab.id ? '#fff' : themeColors.textSecondary }">
                        {{ tab.count }}
                    </span>
                </button>
            </nav>
        </div>

        <!-- ══ TAB: CONCIERGE REQUESTS ══ -->
        <template v-if="activeTab === 'requests'">
            <!-- Stats Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div v-for="stat in conciergeStats" :key="stat.label"
                     class="rounded-lg p-5 border shadow-sm flex items-center gap-4"
                     :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                    <span class="text-3xl">{{ stat.icon }}</span>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">{{ stat.label }}</p>
                        <p class="text-2xl font-bold mt-0.5" :style="{ color: stat.color }">{{ stat.value }}</p>
                    </div>
                </div>
            </div>

            <!-- Search/Filter Bar -->
            <div class="flex gap-3 mb-4">
                <input v-model="requestSearch" type="text" placeholder="Search requests..."
                       class="flex-1 rounded-md px-3 py-2 text-sm border"
                       :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                <select v-model="requestStatusFilter"
                        class="rounded-md px-3 py-2 text-sm border"
                        :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>

            <!-- Requests Table -->
            <div class="shadow rounded-lg overflow-hidden" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr :style="{ backgroundColor: 'rgba(0,0,0,0.04)' }">
                                <th v-for="h in ['Request #','Guest','Room','Service Type','Status','Requested','Actions']" :key="h"
                                    class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                    :style="{ color: themeColors.textTertiary }">{{ h }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredRequests.length === 0">
                                <td colspan="7" class="px-5 py-8 text-center text-sm" :style="{ color: themeColors.textTertiary }">No requests found</td>
                            </tr>
                            <tr v-for="req in filteredRequests" :key="req.id" class="border-t transition-colors hover:bg-black/5"
                                :style="{ borderColor: themeColors.border }">
                                <td class="px-5 py-3 text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ req.request_number }}</td>
                                <td class="px-5 py-3 text-sm" :style="{ color: themeColors.textPrimary }">{{ req.guest_name }}</td>
                                <td class="px-5 py-3 text-sm" :style="{ color: themeColors.textSecondary }">{{ req.room_number || '—' }}</td>
                                <td class="px-5 py-3 text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :class="getServiceTypeClass(req.service_type)">{{ req.service_type }}</span>
                                </td>
                                <td class="px-5 py-3 text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :class="getStatusClass(req.status)">{{ formatStatus(req.status) }}</span>
                                </td>
                                <td class="px-5 py-3 text-xs" :style="{ color: themeColors.textSecondary }">{{ formatDateTime(req.requested_at) }}</td>
                                <td class="px-5 py-3 text-sm">
                                    <button v-if="req.status !== 'completed'" @click="advanceStatus(req)"
                                            class="text-xs px-3 py-1 rounded font-medium text-white"
                                            :style="{ backgroundColor: req.status === 'pending' ? '#3b82f6' : '#10b981' }">
                                        {{ req.status === 'pending' ? 'Start' : 'Complete' }}
                                    </button>
                                    <span v-else class="text-xs" :style="{ color: themeColors.textTertiary }">Done</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="requests?.links?.length" class="px-5 py-4 border-t" :style="{ borderColor: themeColors.border }">
                    <Pagination :links="requests.links" />
                </div>
            </div>
        </template>

        <!-- ══ TAB: HALL BOOKINGS ══ -->
        <template v-if="activeTab === 'halls'">
            <!-- Hall Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div v-for="stat in hallStats" :key="stat.label"
                     class="rounded-lg p-5 border shadow-sm flex items-center gap-4"
                     :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                    <span class="text-3xl">{{ stat.icon }}</span>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">{{ stat.label }}</p>
                        <p class="text-2xl font-bold mt-0.5" :style="{ color: stat.color }">{{ stat.value }}</p>
                    </div>
                </div>
            </div>

            <!-- Available Halls -->
            <div v-if="halls.length" class="mb-6">
                <h3 class="text-sm font-semibold uppercase tracking-wider mb-3" :style="{ color: themeColors.textSecondary }">Available Halls</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="hall in halls" :key="hall.id"
                         class="rounded-lg p-5 border shadow-sm"
                         :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h4 class="font-semibold" :style="{ color: themeColors.textPrimary }">{{ hall.name }}</h4>
                                <p class="text-xs mt-0.5" :style="{ color: themeColors.textSecondary }">{{ hall.code }} · {{ hall.type }}</p>
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-800">Active</span>
                        </div>
                        <div class="flex items-center justify-between text-sm mb-4">
                            <span :style="{ color: themeColors.textSecondary }">👥 Capacity: {{ hall.capacity }}</span>
                            <span class="font-semibold" :style="{ color: themeColors.primary }">SAR {{ formatNumber(hall.base_price) }}/hr</span>
                        </div>
                        <button @click="prefillHall(hall)"
                                class="w-full py-2 text-sm rounded-md font-medium text-white"
                                :style="{ backgroundColor: '#8b5cf6' }">
                            Book This Hall
                        </button>
                    </div>
                </div>
            </div>

            <!-- Search/Filter -->
            <div class="flex gap-3 mb-4">
                <input v-model="hallSearch" type="text" placeholder="Search hall bookings..."
                       class="flex-1 rounded-md px-3 py-2 text-sm border"
                       :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                <select v-model="hallStatusFilter"
                        class="rounded-md px-3 py-2 text-sm border"
                        :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <!-- Hall Bookings Table -->
            <div class="shadow rounded-lg overflow-hidden" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr :style="{ backgroundColor: 'rgba(0,0,0,0.04)' }">
                                <th v-for="h in ['Booking #','Hall','Contact','Event Date','Attendees','Amount','Status','Actions']" :key="h"
                                    class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                    :style="{ color: themeColors.textTertiary }">{{ h }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredHallBookings.length === 0">
                                <td colspan="8" class="px-5 py-8 text-center text-sm" :style="{ color: themeColors.textTertiary }">No hall bookings found</td>
                            </tr>
                            <tr v-for="b in filteredHallBookings" :key="b.id" class="border-t transition-colors hover:bg-black/5"
                                :style="{ borderColor: themeColors.border }">
                                <td class="px-5 py-3 text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ b.booking_number }}</td>
                                <td class="px-5 py-3 text-sm" :style="{ color: themeColors.textPrimary }">{{ b.hall_name || '—' }}</td>
                                <td class="px-5 py-3 text-sm">
                                    <div :style="{ color: themeColors.textPrimary }">{{ b.contact_name }}</div>
                                    <div class="text-xs" :style="{ color: themeColors.textSecondary }">{{ b.contact_phone }}</div>
                                </td>
                                <td class="px-5 py-3 text-sm" :style="{ color: themeColors.textSecondary }">{{ b.event_date ? new Date(b.event_date).toLocaleDateString() : '—' }}</td>
                                <td class="px-5 py-3 text-sm" :style="{ color: themeColors.textSecondary }">{{ b.attendees }}</td>
                                <td class="px-5 py-3 text-sm font-semibold" :style="{ color: themeColors.primary }">SAR {{ formatNumber(b.total_amount) }}</td>
                                <td class="px-5 py-3 text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :class="getHallStatusClass(b.status)">{{ formatStatus(b.status) }}</span>
                                </td>
                                <td class="px-5 py-3 text-sm">
                                    <div class="flex gap-2">
                                        <button v-if="b.status === 'pending'" @click="updateHallStatus(b, 'confirmed')"
                                                class="text-xs px-2 py-1 rounded font-medium text-white bg-blue-500">Confirm</button>
                                        <button v-if="b.status === 'confirmed'" @click="updateHallStatus(b, 'completed')"
                                                class="text-xs px-2 py-1 rounded font-medium text-white bg-green-500">Complete</button>
                                        <button v-if="b.status !== 'completed' && b.status !== 'cancelled'" @click="updateHallStatus(b, 'cancelled')"
                                                class="text-xs px-2 py-1 rounded font-medium text-white bg-red-500">Cancel</button>
                                        <span v-if="b.status === 'completed' || b.status === 'cancelled'"
                                              class="text-xs" :style="{ color: themeColors.textTertiary }">—</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>

        <!-- ══ MODAL: NEW CONCIERGE REQUEST ══ -->
        <div v-if="showNewRequest" @click.self="showNewRequest = false"
             class="fixed inset-0 flex items-center justify-center z-50 bg-black/50">
            <div class="rounded-lg p-6 max-w-md w-full mx-4 shadow-xl"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, border: '1px solid' }">
                <h2 class="text-xl font-bold mb-5" :style="{ color: themeColors.textPrimary }">New Concierge Request</h2>
                <form @submit.prevent="submitRequest" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Guest Name *</label>
                        <input v-model="newRequest.guest_name" type="text" required
                               class="w-full rounded-md px-3 py-2 border text-sm"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Room Number (optional)</label>
                        <input v-model="newRequest.room_number" type="text"
                               class="w-full rounded-md px-3 py-2 border text-sm"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Service Type *</label>
                        <select v-model="newRequest.service_type" required
                                class="w-full rounded-md px-3 py-2 border text-sm"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                            <option value="">Select Type</option>
                            <option value="Transportation">Transportation</option>
                            <option value="Restaurant Booking">Restaurant Booking</option>
                            <option value="Tour Booking">Tour Booking</option>
                            <option value="Ticket Booking">Ticket Booking</option>
                            <option value="Luggage Assistance">Luggage Assistance</option>
                            <option value="Wake-up Call">Wake-up Call</option>
                            <option value="Laundry">Laundry</option>
                            <option value="Room Service">Room Service</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Details</label>
                        <textarea v-model="newRequest.details" rows="3"
                                  class="w-full rounded-md px-3 py-2 border text-sm"
                                  :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"></textarea>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="flex-1 py-2 rounded-md font-medium text-white text-sm"
                                :style="{ backgroundColor: themeColors.primary }">Submit Request</button>
                        <button type="button" @click="showNewRequest = false"
                                class="flex-1 py-2 rounded-md font-medium text-sm border"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ══ MODAL: NEW HALL BOOKING ══ -->
        <div v-if="showNewHallBooking" @click.self="showNewHallBooking = false"
             class="fixed inset-0 flex items-center justify-center z-50 bg-black/50">
            <div class="rounded-lg p-6 max-w-lg w-full mx-4 shadow-xl max-h-[90vh] overflow-y-auto"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, border: '1px solid' }">
                <h2 class="text-xl font-bold mb-5" :style="{ color: themeColors.textPrimary }">Book a Hall</h2>
                <form @submit.prevent="submitHallBooking" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Hall *</label>
                        <select v-model="newHallBooking.hall_id" required @change="onHallChange"
                                class="w-full rounded-md px-3 py-2 border text-sm"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                            <option value="">Select a Hall</option>
                            <option v-for="hall in halls" :key="hall.id" :value="hall.id">
                                {{ hall.name }} (Cap: {{ hall.capacity }} · SAR {{ formatNumber(hall.base_price) }}/hr)
                            </option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Contact Name *</label>
                            <input v-model="newHallBooking.contact_name" type="text" required
                                   class="w-full rounded-md px-3 py-2 border text-sm"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Contact Phone</label>
                            <input v-model="newHallBooking.contact_phone" type="text"
                                   class="w-full rounded-md px-3 py-2 border text-sm"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Contact Email</label>
                        <input v-model="newHallBooking.contact_email" type="email"
                               class="w-full rounded-md px-3 py-2 border text-sm"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Event Date *</label>
                            <input v-model="newHallBooking.event_date" type="date" required :min="today"
                                   class="w-full rounded-md px-3 py-2 border text-sm"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Start Time *</label>
                            <input v-model="newHallBooking.start_time" type="time" required
                                   class="w-full rounded-md px-3 py-2 border text-sm"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">End Time *</label>
                            <input v-model="newHallBooking.end_time" type="time" required
                                   class="w-full rounded-md px-3 py-2 border text-sm"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Attendees *</label>
                        <input v-model.number="newHallBooking.attendees" type="number" min="1" required
                               class="w-full rounded-md px-3 py-2 border text-sm"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                    </div>

                    <!-- Cost Preview -->
                    <div v-if="selectedHall && estimatedHours > 0"
                         class="rounded-lg p-4 border"
                         :style="{ backgroundColor: 'rgba(139,92,246,0.08)', borderColor: '#8b5cf6' }">
                        <p class="text-sm font-semibold mb-1" style="color:#8b5cf6">Cost Estimate</p>
                        <p class="text-xs" :style="{ color: themeColors.textSecondary }">
                            {{ formatNumber(selectedHall.base_price) }} SAR/hr × {{ estimatedHours }} hrs
                        </p>
                        <p class="text-lg font-bold mt-1" style="color:#8b5cf6">SAR {{ formatNumber(estimatedCost) }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Notes</label>
                        <textarea v-model="newHallBooking.notes" rows="2"
                                  class="w-full rounded-md px-3 py-2 border text-sm"
                                  :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"></textarea>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="flex-1 py-2 rounded-md font-medium text-white text-sm"
                                style="background-color:#8b5cf6">Book Hall</button>
                        <button type="button" @click="showNewHallBooking = false"
                                class="flex-1 py-2 rounded-md font-medium text-sm border"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { PlusIcon, DocumentArrowDownIcon } from '@heroicons/vue/24/outline'

const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background:    'var(--kotel-background)',
    card:          'var(--kotel-card)',
    border:        'var(--kotel-border)',
    textPrimary:   'var(--kotel-text-primary)',
    textSecondary: 'var(--kotel-text-secondary)',
    textTertiary:  'var(--kotel-text-tertiary)',
    primary:       'var(--kotel-primary)',
    success:       'var(--kotel-success)',
    warning:       'var(--kotel-warning)',
    danger:        'var(--kotel-danger)',
    hover:         'rgba(255,255,255,0.1)',
}))
loadTheme()

const props = defineProps({
    user:             Object,
    navigation:       Array,
    requests:         { type: Object, default: () => ({ data: [], links: [] }) },
    stats:            { type: Object, default: () => ({ pending: 0, in_progress: 0, completed: 0, total: 0 }) },
    hallBookings:     { type: Array,  default: () => [] },
    hallBookingStats: { type: Object, default: () => ({ total: 0, pending: 0, confirmed: 0, completed: 0 }) },
    halls:            { type: Array,  default: () => [] },
    currentGuests:    { type: Array,  default: () => [] },
})

// ── TABS ──
const activeTab = ref('requests')
const tabs = computed(() => [
    { id: 'requests', label: 'Concierge Requests', count: props.stats.total },
    { id: 'halls',    label: 'Hall Bookings',       count: props.hallBookingStats.total },
])

// ── CONCIERGE REQUESTS ──
const requestSearch       = ref('')
const requestStatusFilter = ref('')
const showNewRequest      = ref(false)
const newRequest          = ref({ guest_name: '', room_number: '', service_type: '', details: '' })

const filteredRequests = computed(() =>
    (props.requests.data || []).filter(r => {
        const s = requestSearch.value.toLowerCase()
        if (s && !`${r.guest_name} ${r.service_type} ${r.request_number}`.toLowerCase().includes(s)) return false
        if (requestStatusFilter.value && r.status !== requestStatusFilter.value) return false
        return true
    })
)

const conciergeStats = computed(() => [
    { label: 'Pending',     icon: '⏳', color: '#f59e0b', value: props.stats.pending },
    { label: 'In Progress', icon: '🔄', color: '#3b82f6', value: props.stats.in_progress },
    { label: 'Completed',   icon: '✅', color: '#10b981', value: props.stats.completed },
    { label: 'Today Total', icon: '📋', color: '#6b7280', value: props.stats.total },
])

const submitRequest = () => {
    router.post(route('front-desk.services.concierge.store'), newRequest.value, {
        preserveScroll: true,
        onSuccess: () => {
            showNewRequest.value = false
            newRequest.value = { guest_name: '', room_number: '', service_type: '', details: '' }
        },
    })
}

const advanceStatus = (request) => {
    const next = request.status === 'pending' ? 'in_progress' : 'completed'
    router.post(route('front-desk.services.concierge.update-status', request.id), { status: next }, { preserveScroll: true })
}

// ── HALL BOOKINGS ──
const hallSearch         = ref('')
const hallStatusFilter   = ref('')
const showNewHallBooking = ref(false)
const newHallBooking     = ref({ hall_id: '', contact_name: '', contact_email: '', contact_phone: '', event_date: '', start_time: '', end_time: '', attendees: 1, notes: '' })
const selectedHall       = ref(null)
const today              = new Date().toISOString().split('T')[0]

const filteredHallBookings = computed(() =>
    props.hallBookings.filter(b => {
        const s = hallSearch.value.toLowerCase()
        if (s && !`${b.hall_name} ${b.contact_name} ${b.booking_number}`.toLowerCase().includes(s)) return false
        if (hallStatusFilter.value && b.status !== hallStatusFilter.value) return false
        return true
    })
)

const hallStats = computed(() => [
    { label: 'Total',     icon: '🏛️', color: '#6b7280', value: props.hallBookingStats.total },
    { label: 'Pending',   icon: '⏳', color: '#f59e0b', value: props.hallBookingStats.pending },
    { label: 'Confirmed', icon: '✅', color: '#10b981', value: props.hallBookingStats.confirmed },
    { label: 'Completed', icon: '🎉', color: '#3b82f6', value: props.hallBookingStats.completed },
])

const estimatedHours = computed(() => {
    if (!newHallBooking.value.start_time || !newHallBooking.value.end_time) return 0
    const [sh, sm] = newHallBooking.value.start_time.split(':').map(Number)
    const [eh, em] = newHallBooking.value.end_time.split(':').map(Number)
    const mins = (eh * 60 + em) - (sh * 60 + sm)
    return mins > 0 ? Math.round(mins / 60 * 10) / 10 : 0
})

const estimatedCost = computed(() => {
    if (!selectedHall.value || estimatedHours.value <= 0) return 0
    return Math.round(selectedHall.value.base_price * estimatedHours.value * 100) / 100
})

const onHallChange = () => {
    selectedHall.value = props.halls.find(h => h.id === newHallBooking.value.hall_id) || null
}

const prefillHall = (hall) => {
    newHallBooking.value.hall_id = hall.id
    selectedHall.value = hall
    showNewHallBooking.value = true
}

const submitHallBooking = () => {
    router.post(route('front-desk.services.hall-bookings.store'), newHallBooking.value, {
        preserveScroll: true,
        onSuccess: () => {
            showNewHallBooking.value = false
            newHallBooking.value = { hall_id: '', contact_name: '', contact_email: '', contact_phone: '', event_date: '', start_time: '', end_time: '', attendees: 1, notes: '' }
            selectedHall.value = null
        },
    })
}

const updateHallStatus = (booking, status) =>
    router.post(route('front-desk.services.hall-bookings.update-status', booking.id), { status }, { preserveScroll: true })

// ── FORMATTERS ──
const formatStatus    = (s) => s ? s.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A'
const formatDateTime  = (d) => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A'
const formatNumber    = (n) => Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })

const getStatusClass      = (s) => ({ pending: 'bg-yellow-100 text-yellow-800', in_progress: 'bg-blue-100 text-blue-800', completed: 'bg-green-100 text-green-800' }[s] || 'bg-gray-100 text-gray-800')
const getHallStatusClass  = (s) => ({ pending: 'bg-yellow-100 text-yellow-800', confirmed: 'bg-blue-100 text-blue-800', completed: 'bg-green-100 text-green-800', cancelled: 'bg-red-100 text-red-800' }[s] || 'bg-gray-100 text-gray-800')
const getServiceTypeClass = (t) => {
    const map = { 'Transportation': 'bg-purple-100 text-purple-800', 'Restaurant Booking': 'bg-orange-100 text-orange-800', 'Tour Booking': 'bg-teal-100 text-teal-800', 'Ticket Booking': 'bg-indigo-100 text-indigo-800', 'Laundry': 'bg-pink-100 text-pink-800', 'Room Service': 'bg-cyan-100 text-cyan-800' }
    return map[t] || 'bg-gray-100 text-gray-800'
}

const exportData = () => {
    const data = { concierge_requests: props.requests.data, hall_bookings: props.hallBookings, exported_at: new Date().toISOString() }
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
    const url  = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `concierge_export_${today}.json`
    link.click()
    URL.revokeObjectURL(url)
}
</script>
