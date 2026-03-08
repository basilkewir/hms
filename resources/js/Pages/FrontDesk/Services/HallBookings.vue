<template>
    <DashboardLayout title="Hall Bookings" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-6" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Hall Bookings</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Manage event hall reservations and bookings.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="showNewBooking = true"
                            class="px-4 py-2 rounded-md font-medium text-white flex items-center gap-2"
                            style="background-color:#8b5cf6">
                        <PlusIcon class="h-4 w-4" /> Book Hall
                    </button>
                    <button @click="exportBookings"
                            class="px-4 py-2 rounded-md font-medium text-white flex items-center gap-2"
                            style="background-color:#6b7280">
                        <DocumentArrowDownIcon class="h-4 w-4" /> Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div v-for="stat in statsCards" :key="stat.label"
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
                        <span class="font-semibold" :style="{ color: '#8b5cf6' }">{{ formatCurrency(hall.base_price) }}/hr</span>
                    </div>
                    <button @click="prefillHall(hall)"
                            class="w-full py-2 text-sm rounded-md font-medium text-white"
                            style="background-color:#8b5cf6">
                        Book This Hall
                    </button>
                </div>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="flex gap-3 mb-4">
            <input v-model="search" type="text" placeholder="Search by booking #, contact, hall..."
                   class="flex-1 rounded-md px-3 py-2 text-sm border"
                   :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }" />
            <select v-model="statusFilter"
                    class="rounded-md px-3 py-2 text-sm border"
                    :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <!-- Bookings Table -->
        <div class="shadow rounded-lg overflow-hidden" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: 'rgba(0,0,0,0.04)' }">
                            <th v-for="h in ['Booking #','Hall','Contact','Event Date','Time','Attendees','Amount','Status','Actions']" :key="h"
                                class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">{{ h }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="filteredBookings.length === 0">
                            <td colspan="9" class="px-5 py-10 text-center text-sm" :style="{ color: themeColors.textTertiary }">No hall bookings found</td>
                        </tr>
                        <tr v-for="b in filteredBookings" :key="b.id"
                            class="border-t hover:bg-black/5 transition-colors"
                            :style="{ borderColor: themeColors.border }">
                            <td class="px-5 py-3 text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ b.booking_number }}</td>
                            <td class="px-5 py-3 text-sm" :style="{ color: themeColors.textPrimary }">{{ b.hall_name || '—' }}</td>
                            <td class="px-5 py-3 text-sm">
                                <div class="font-medium" :style="{ color: themeColors.textPrimary }">{{ b.contact_name }}</div>
                                <div class="text-xs" :style="{ color: themeColors.textSecondary }">{{ b.contact_phone }}</div>
                            </td>
                            <td class="px-5 py-3 text-sm" :style="{ color: themeColors.textSecondary }">
                                {{ b.event_date ? new Date(b.event_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—' }}
                            </td>
                            <td class="px-5 py-3 text-xs" :style="{ color: themeColors.textSecondary }">
                                {{ b.start_time || '—' }} – {{ b.end_time || '—' }}
                            </td>
                            <td class="px-5 py-3 text-sm text-center" :style="{ color: themeColors.textSecondary }">{{ b.attendees }}</td>
                            <td class="px-5 py-3 text-sm font-semibold" style="color:#8b5cf6">{{ formatCurrency(b.total_amount) }}</td>
                            <td class="px-5 py-3 text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="statusClass(b.status)">{{ formatStatus(b.status) }}</span>
                            </td>
                            <td class="px-5 py-3 text-sm">
                                <div class="flex gap-1 flex-wrap">
                                    <button v-if="b.status === 'pending'" @click="updateStatus(b, 'confirmed')"
                                            class="text-xs px-2 py-1 rounded font-medium text-white bg-blue-500">Confirm</button>
                                    <button v-if="b.status === 'confirmed'" @click="updateStatus(b, 'completed')"
                                            class="text-xs px-2 py-1 rounded font-medium text-white bg-green-500">Complete</button>
                                    <button v-if="b.status !== 'completed' && b.status !== 'cancelled'" @click="updateStatus(b, 'cancelled')"
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

        <!-- ══ MODAL: NEW HALL BOOKING ══ -->
        <div v-if="showNewBooking" @click.self="showNewBooking = false"
             class="fixed inset-0 flex items-center justify-center z-50 bg-black/50">
            <div class="rounded-lg p-6 max-w-lg w-full mx-4 shadow-xl max-h-[90vh] overflow-y-auto"
                 :style="{ backgroundColor: themeColors.card, border: '1px solid', borderColor: themeColors.border }">
                <h2 class="text-xl font-bold mb-5" :style="{ color: themeColors.textPrimary }">Book a Hall</h2>
                <form @submit.prevent="submitBooking" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Hall *</label>
                        <select v-model="newBooking.hall_id" required @change="onHallChange"
                                class="w-full rounded-md px-3 py-2 border text-sm"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                            <option value="">Select a Hall</option>
                            <option v-for="hall in halls" :key="hall.id" :value="hall.id">
                                {{ hall.name }} (Cap: {{ hall.capacity }} · {{ formatCurrency(hall.base_price) }}/hr)
                            </option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Contact Name *</label>
                            <input v-model="newBooking.contact_name" type="text" required
                                   class="w-full rounded-md px-3 py-2 border text-sm"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Contact Phone</label>
                            <input v-model="newBooking.contact_phone" type="text"
                                   class="w-full rounded-md px-3 py-2 border text-sm"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Contact Email</label>
                        <input v-model="newBooking.contact_email" type="email"
                               class="w-full rounded-md px-3 py-2 border text-sm"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Event Date *</label>
                            <input v-model="newBooking.event_date" type="date" required :min="today"
                                   class="w-full rounded-md px-3 py-2 border text-sm"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Start Time *</label>
                            <input v-model="newBooking.start_time" type="time" required
                                   class="w-full rounded-md px-3 py-2 border text-sm"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">End Time *</label>
                            <input v-model="newBooking.end_time" type="time" required
                                   class="w-full rounded-md px-3 py-2 border text-sm"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Attendees *</label>
                        <input v-model.number="newBooking.attendees" type="number" min="1" required
                               class="w-full rounded-md px-3 py-2 border text-sm"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                    </div>

                    <!-- Cost Preview -->
                    <div v-if="selectedHall && estimatedHours > 0"
                         class="rounded-lg p-4 border"
                         style="background-color:rgba(139,92,246,0.08);border-color:#8b5cf6">
                        <p class="text-sm font-semibold mb-1" style="color:#8b5cf6">Cost Estimate</p>
                        <p class="text-xs" :style="{ color: themeColors.textSecondary }">
                            {{ formatCurrency(selectedHall.base_price) }}/hr × {{ estimatedHours }} hrs
                        </p>
                        <p class="text-lg font-bold mt-1" style="color:#8b5cf6">{{ formatCurrency(estimatedCost) }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Notes</label>
                        <textarea v-model="newBooking.notes" rows="2"
                                  class="w-full rounded-md px-3 py-2 border text-sm"
                                  :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"></textarea>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="flex-1 py-2 rounded-md font-medium text-white text-sm"
                                style="background-color:#8b5cf6">Book Hall</button>
                        <button type="button" @click="showNewBooking = false"
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
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'
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
}))
loadTheme()

const props = defineProps({
    user:         Object,
    navigation:   Array,
    bookings:     { type: [Array, Object], default: () => [] },
    stats:        { type: Object, default: () => ({ total: 0, pending: 0, confirmed: 0, completed: 0, cancelled: 0 }) },
    halls:        { type: Array,  default: () => [] },
    guests:       { type: Array,  default: () => [] },
})

// Support both paginated (Object with .data) and plain array
const bookingsList = computed(() => Array.isArray(props.bookings) ? props.bookings : (props.bookings?.data ?? []))

// ── STATS CARDS ──
const statsCards = computed(() => [
    { label: 'Total',     icon: '🏛️', color: '#6b7280', value: props.stats.total },
    { label: 'Pending',   icon: '⏳', color: '#f59e0b', value: props.stats.pending },
    { label: 'Confirmed', icon: '✅', color: '#10b981', value: props.stats.confirmed },
    { label: 'Completed', icon: '🎉', color: '#3b82f6', value: props.stats.completed },
])

// ── FILTER / SEARCH ──
const search       = ref('')
const statusFilter = ref('')

const filteredBookings = computed(() =>
    bookingsList.value.filter(b => {
        const s = search.value.toLowerCase()
        if (s && !`${b.booking_number} ${b.contact_name} ${b.hall_name}`.toLowerCase().includes(s)) return false
        if (statusFilter.value && b.status !== statusFilter.value) return false
        return true
    })
)

// ── BOOKING FORM ──
const showNewBooking = ref(false)
const newBooking     = ref({ hall_id: '', contact_name: '', contact_email: '', contact_phone: '', event_date: '', start_time: '', end_time: '', attendees: 1, notes: '' })
const selectedHall   = ref(null)
const today          = new Date().toISOString().split('T')[0]

const estimatedHours = computed(() => {
    if (!newBooking.value.start_time || !newBooking.value.end_time) return 0
    const [sh, sm] = newBooking.value.start_time.split(':').map(Number)
    const [eh, em] = newBooking.value.end_time.split(':').map(Number)
    const mins = (eh * 60 + em) - (sh * 60 + sm)
    return mins > 0 ? Math.round(mins / 60 * 10) / 10 : 0
})

const estimatedCost = computed(() => {
    if (!selectedHall.value || estimatedHours.value <= 0) return 0
    return Math.round(selectedHall.value.base_price * estimatedHours.value * 100) / 100
})

const onHallChange = () => {
    selectedHall.value = props.halls.find(h => h.id === newBooking.value.hall_id) || null
}

const prefillHall = (hall) => {
    newBooking.value.hall_id = hall.id
    selectedHall.value = hall
    showNewBooking.value = true
}

const submitBooking = () => {
    router.post(route('front-desk.services.hall-bookings.store'), newBooking.value, {
        preserveScroll: true,
        onSuccess: () => {
            showNewBooking.value = false
            newBooking.value = { hall_id: '', contact_name: '', contact_email: '', contact_phone: '', event_date: '', start_time: '', end_time: '', attendees: 1, notes: '' }
            selectedHall.value = null
        },
    })
}

const updateStatus = (booking, status) =>
    router.post(route('front-desk.services.hall-bookings.update-status', booking.id), { status }, { preserveScroll: true })

// ── FORMATTERS ──
const formatStatus = (s) => s ? s.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A'
const statusClass  = (s) => ({
    pending:   'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
}[s] || 'bg-gray-100 text-gray-800')

const exportBookings = () => {
    const blob = new Blob([JSON.stringify(bookingsList.value, null, 2)], { type: 'application/json' })
    const url  = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `hall_bookings_${today}.json`
    link.click()
    URL.revokeObjectURL(url)
}
</script>
