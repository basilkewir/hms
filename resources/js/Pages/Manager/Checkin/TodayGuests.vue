<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { getNavigationForRole } from '@/Utils/navigation.js'
import {
    PrinterIcon,
    ArrowDownTrayIcon,
    ArrowLeftIcon,
    ShieldCheckIcon,
    UserGroupIcon,
    CheckCircleIcon,
    ClockIcon,
    MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline'

const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    primary: `var(--kotel-primary)`,
    secondary: `var(--kotel-secondary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
}))
loadTheme()

const props = defineProps({
    user: Object,
    todayGuests: { type: Array, default: () => [] },
    reportDate: String,
    today: String,
    hotelName: String,
    hotelAddress: String,
    hotelPhone: String,
})

const navigation = computed(() => getNavigationForRole('manager'))

const search = ref('')
const statusFilter = ref('all')

const filtered = computed(() => {
    let list = props.todayGuests || []
    if (statusFilter.value !== 'all') {
        list = list.filter(r => r.status === statusFilter.value)
    }
    if (search.value.trim()) {
        const q = search.value.trim().toLowerCase()
        list = list.filter(r =>
            r.guest?.full_name?.toLowerCase().includes(q) ||
            r.room_number?.toLowerCase().includes(q) ||
            r.guest?.nationality?.toLowerCase().includes(q) ||
            r.reservation_number?.toLowerCase().includes(q) ||
            r.guest?.id_number?.toLowerCase().includes(q)
        )
    }
    return list
})

const checkedInCount   = computed(() => (props.todayGuests || []).filter(r => r.status === 'checked_in').length)
const pendingCount     = computed(() => (props.todayGuests || []).filter(r => r.status !== 'checked_in').length)
const totalGuests      = computed(() => (props.todayGuests || []).reduce((sum, r) => sum + (r.number_of_adults || 1) + (r.number_of_children || 0), 0))

const statusBadge = (status) => {
    if (status === 'checked_in') return { label: 'Checked In', bg: '#dcfce7', color: '#166534' }
    if (status === 'confirmed')  return { label: 'Confirmed',  bg: '#dbeafe', color: '#1e40af' }
    return { label: 'Pending', bg: '#fef9c3', color: '#854d0e' }
}

const policeStatusClass = (status) => {
    if (status === 'verified') return 'bg-green-100 text-green-800'
    if (status === 'pending')  return 'bg-yellow-100 text-yellow-800'
    if (status === 'flagged')  return 'bg-red-100 text-red-800'
    return 'bg-gray-100 text-gray-600'
}

const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-GB') : '—'

const printReport = () => window.print()

const exportCSV = () => {
    const guests = props.todayGuests || []
    const headers = [
        'Room No', 'Room Type', 'Reservation No', 'Status',
        'Full Name', 'Gender', 'Date of Birth', 'Nationality',
        'ID Type', 'ID Number', 'ID Expiry',
        'Phone', 'Email', 'Address', 'City', 'Country',
        'Purpose of Visit',
        'Check-in Date', 'Check-out Date', 'Actual Check-in', 'Nights',
        'Adults', 'Children',
        'Emergency Contact', 'Emergency Phone',
    ]
    const rows = guests.map(r => {
        const g = r.guest || {}
        return [
            r.room_number, r.room_type, r.reservation_number, r.status,
            g.full_name || '', g.gender || '', g.date_of_birth || '', g.nationality || '',
            g.id_type || '', g.id_number || '', g.id_expiry_date || '',
            g.phone || '', g.email || '', g.address || '', g.city || '', g.country || '',
            g.purpose_of_visit || '',
            r.check_in_date || '', r.check_out_date || '', r.actual_check_in || '', r.nights || '',
            r.number_of_adults || '', r.number_of_children || '',
            g.emergency_contact_name || '', g.emergency_contact_phone || '',
        ].map(v => `"${String(v).replace(/"/g, '""')}"`)
    })
    const csv = [headers.join(','), ...rows.map(r => r.join(','))].join('\n')
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
    const url  = URL.createObjectURL(blob)
    const a    = document.createElement('a')
    a.href = url
    a.download = `today-guests-${props.today || new Date().toISOString().slice(0, 10)}.csv`
    a.click()
    URL.revokeObjectURL(url)
}
</script>

<template>
    <Head title="Today's Checked-In Guests" />
    <DashboardLayout title="Today's Guest Register" :user="user" :navigation="navigation">

        <!-- ─── Action bar (hidden when printing) ─── -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6 no-print">
            <div class="flex items-center gap-3 flex-wrap">
                <Link href="/manager/checkin"
                      class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium transition-colors"
                      :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary, border: '1px solid ' + themeColors.border }">
                    <ArrowLeftIcon class="h-4 w-4" />
                    Back to Check-in
                </Link>
                <div>
                    <h1 class="text-xl font-semibold flex items-center gap-2" :style="{ color: themeColors.textPrimary }">
                        <ShieldCheckIcon class="h-6 w-6 text-blue-500" />
                        Today's Guest Register
                    </h1>
                    <p class="text-xs mt-0.5" :style="{ color: themeColors.textSecondary }">{{ today }} &mdash; {{ hotelName }}</p>
                </div>
            </div>
            <div class="flex gap-3">
                <button @click="exportCSV"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium transition-colors"
                        :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary, border: '1px solid ' + themeColors.border }">
                    <ArrowDownTrayIcon class="h-4 w-4" />
                    Export CSV
                </button>
                <button @click="printReport"
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-md text-sm font-medium text-black transition-colors"
                        :style="{ backgroundColor: themeColors.primary }">
                    <PrinterIcon class="h-4 w-4" />
                    Print Police Report
                </button>
            </div>
        </div>

        <!-- ─── Stats cards (hidden when printing) ─── -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 no-print">
            <div class="rounded-lg p-4 shadow border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center gap-3">
                    <UserGroupIcon class="h-8 w-8" :style="{ color: themeColors.primary }" />
                    <div>
                        <p class="text-xs" :style="{ color: themeColors.textSecondary }">Total Arrivals</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ todayGuests.length }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-4 shadow border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center gap-3">
                    <CheckCircleIcon class="h-8 w-8" :style="{ color: themeColors.success }" />
                    <div>
                        <p class="text-xs" :style="{ color: themeColors.textSecondary }">Checked In</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ checkedInCount }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-4 shadow border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center gap-3">
                    <ClockIcon class="h-8 w-8" :style="{ color: themeColors.warning }" />
                    <div>
                        <p class="text-xs" :style="{ color: themeColors.textSecondary }">Pending Arrival</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ pendingCount }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-4 shadow border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center gap-3">
                    <UserGroupIcon class="h-8 w-8" :style="{ color: themeColors.textSecondary }" />
                    <div>
                        <p class="text-xs" :style="{ color: themeColors.textSecondary }">Total Guests</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ totalGuests }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── Filter + Search (hidden when printing) ─── -->
        <div class="flex flex-wrap items-center gap-4 mb-4 no-print">
            <!-- Status tabs -->
            <div class="flex rounded-md overflow-hidden border" :style="{ borderColor: themeColors.border }">
                <button v-for="tab in [{ value: 'all', label: 'All' }, { value: 'checked_in', label: 'Checked In' }, { value: 'confirmed', label: 'Confirmed' }, { value: 'pending', label: 'Pending' }]"
                        :key="tab.value"
                        @click="statusFilter = tab.value"
                        class="px-4 py-2 text-sm font-medium transition-colors"
                        :style="statusFilter === tab.value
                            ? { backgroundColor: themeColors.primary, color: '#000' }
                            : { backgroundColor: themeColors.card, color: themeColors.textSecondary }">
                    {{ tab.label }}
                </button>
            </div>
            <!-- Search -->
            <div class="relative flex-1 min-w-48">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4" :style="{ color: themeColors.textSecondary }" />
                <input v-model="search" type="text" placeholder="Search by name, room, nationality, ID…"
                       class="w-full pl-9 pr-4 py-2 rounded-md text-sm focus:outline-none"
                       :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary, border: '1px solid' }" />
            </div>
        </div>

        <!-- ─── Screen table (hidden when printing) ─── -->
        <div class="shadow rounded-lg overflow-hidden border mb-6 no-print" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <!-- Empty state -->
            <div v-if="filtered.length === 0" class="py-16 text-center">
                <ShieldCheckIcon class="h-12 w-12 mx-auto mb-3 opacity-30" :style="{ color: themeColors.textSecondary }" />
                <p class="font-medium" :style="{ color: themeColors.textPrimary }">No guests found for today.</p>
            </div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">#</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Room</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Guest</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Nationality</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">ID</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Check-in</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Check-out</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(r, i) in filtered" :key="r.reservation_number"
                            class="border-b transition-colors"
                            :style="{ borderColor: themeColors.border }">
                            <td class="px-4 py-3 text-xs" :style="{ color: themeColors.textSecondary }">{{ i + 1 }}</td>
                            <td class="px-4 py-3 font-mono font-semibold" :style="{ color: themeColors.textPrimary }">
                                {{ r.room_number }}
                                <div class="text-xs font-normal" :style="{ color: themeColors.textSecondary }">{{ r.room_type }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium" :style="{ color: themeColors.textPrimary }">{{ r.guest?.full_name || '—' }}</div>
                                <div class="text-xs" :style="{ color: themeColors.textSecondary }">{{ r.reservation_number }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">{{ r.guest?.nationality || '—' }}</td>
                            <td class="px-4 py-3 text-xs" :style="{ color: themeColors.textSecondary }">
                                <span v-if="r.guest?.id_number">{{ r.guest?.id_type }}: {{ r.guest?.id_number }}</span>
                                <span v-else>—</span>
                            </td>
                            <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ formatDate(r.check_in_date) }}
                                <div v-if="r.actual_check_in" class="text-xs" :style="{ color: themeColors.success }">✓ {{ r.actual_check_in }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ formatDate(r.check_out_date) }}
                                <div class="text-xs" :style="{ color: themeColors.textSecondary }">{{ r.nights }} night{{ r.nights !== 1 ? 's' : '' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                      :style="{ backgroundColor: statusBadge(r.status).bg, color: statusBadge(r.status).color }">
                                    {{ statusBadge(r.status).label }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ─── Printable Police Report (visible only when printing) ─── -->
        <div id="police-report-print" class="print-only rounded-lg p-8" style="background:#fff; color:#000;">

            <!-- Report header -->
            <div class="text-center border-b pb-4 mb-6" style="border-color:#d1d5db;">
                <h1 class="text-2xl font-bold mb-1">{{ hotelName }}</h1>
                <p v-if="hotelAddress" class="text-sm text-gray-500">{{ hotelAddress }}</p>
                <p v-if="hotelPhone"   class="text-sm text-gray-500">{{ hotelPhone }}</p>
                <div class="mt-4">
                    <h2 class="text-lg font-bold uppercase tracking-widest">Guest Register for Police / Immigration</h2>
                    <p class="text-sm text-gray-500 mt-1">Date: {{ today }} &nbsp;&mdash;&nbsp; Report generated: {{ reportDate }}</p>
                    <p class="text-sm font-semibold mt-1 text-blue-700">
                        Today's Arrivals: {{ todayGuests.length }} reservation(s) &nbsp;|&nbsp;
                        Checked In: {{ checkedInCount }} &nbsp;|&nbsp;
                        Total Guests: {{ totalGuests }}
                    </p>
                </div>
            </div>

            <!-- No guests -->
            <div v-if="todayGuests.length === 0" class="text-center py-16 text-gray-400">
                <p class="text-lg font-medium">No arrivals today.</p>
            </div>

            <!-- Guest table -->
            <div v-else class="overflow-x-auto">
                <table class="w-full border-collapse text-xs">
                    <thead>
                        <tr class="border-b-2" style="border-color:#374151; background:#f9fafb;">
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">#</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">Room</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">Guest Name</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">DOB / Gender</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">Nationality</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">ID / Passport</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">Arrival From</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">Purpose</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">Check-in</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">Check-out</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">Contact</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(r, i) in todayGuests" :key="i"
                            class="border-b"
                            :style="{ borderColor: '#e5e7eb', backgroundColor: i % 2 === 0 ? '#ffffff' : '#f9fafb' }">
                            <td class="py-2 px-2 text-gray-500">{{ i + 1 }}</td>
                            <td class="py-2 px-2 font-mono font-semibold">{{ r.room_number }}</td>
                            <td class="py-2 px-2">
                                <div class="font-semibold">{{ r.guest?.full_name || '—' }}</div>
                                <div class="text-gray-500 text-[10px]">{{ r.reservation_number }}</div>
                            </td>
                            <td class="py-2 px-2">
                                <div>{{ formatDate(r.guest?.date_of_birth) }}</div>
                                <div class="text-gray-500 capitalize">{{ r.guest?.gender || '—' }}</div>
                            </td>
                            <td class="py-2 px-2">{{ r.guest?.nationality || '—' }}</td>
                            <td class="py-2 px-2">
                                <div v-if="r.guest?.id_number">
                                    <span class="text-gray-500">{{ r.guest?.id_type }}:</span> {{ r.guest?.id_number }}
                                </div>
                                <div v-if="r.guest?.passport_number" class="mt-0.5">
                                    <span class="text-gray-500">Passport:</span> {{ r.guest?.passport_number }}
                                    <span v-if="r.guest?.passport_expiry_date" class="text-[9px] text-gray-400 ml-1">(exp {{ formatDate(r.guest?.passport_expiry_date) }})</span>
                                </div>
                                <span v-if="!r.guest?.id_number && !r.guest?.passport_number" class="text-gray-400">—</span>
                            </td>
                            <td class="py-2 px-2">
                                <div>{{ r.guest?.arrival_from || '—' }}</div>
                                <div v-if="r.guest?.departure_to" class="text-gray-500 text-[10px]">→ {{ r.guest?.departure_to }}</div>
                            </td>
                            <td class="py-2 px-2">{{ r.guest?.purpose_of_visit || '—' }}</td>
                            <td class="py-2 px-2">
                                <div>{{ formatDate(r.check_in_date) }}</div>
                                <div v-if="r.actual_check_in" class="text-[10px] text-gray-500">{{ r.actual_check_in }}</div>
                            </td>
                            <td class="py-2 px-2">
                                {{ formatDate(r.check_out_date) }}
                                <div class="text-[10px] text-gray-500">{{ r.nights }} night{{ r.nights !== 1 ? 's' : '' }}</div>
                            </td>
                            <td class="py-2 px-2">
                                <div>{{ r.guest?.phone || '—' }}</div>
                                <div v-if="r.guest?.emergency_contact_name" class="text-[10px] text-gray-500 mt-0.5">
                                    Emergency: {{ r.guest?.emergency_contact_name }}
                                    <span v-if="r.guest?.emergency_contact_phone"> ({{ r.guest?.emergency_contact_phone }})</span>
                                </div>
                            </td>
                            <td class="py-2 px-2">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold"
                                      :class="policeStatusClass(r.guest?.police_verification_status)">
                                    {{ r.status === 'checked_in' ? 'Checked In' : (r.status === 'confirmed' ? 'Confirmed' : 'Pending') }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Signature area -->
            <div class="mt-12 grid grid-cols-3 gap-8 text-xs text-gray-600">
                <div class="border-t pt-2" style="border-color:#9ca3af;">
                    <p class="font-semibold">Prepared By</p>
                    <p class="mt-6">&nbsp;</p>
                    <p class="border-t" style="border-color:#9ca3af;">Name / Signature / Date</p>
                </div>
                <div class="border-t pt-2" style="border-color:#9ca3af;">
                    <p class="font-semibold">Hotel Manager / Front Desk</p>
                    <p class="mt-6">&nbsp;</p>
                    <p class="border-t" style="border-color:#9ca3af;">Name / Signature / Date</p>
                </div>
                <div class="border-t pt-2" style="border-color:#9ca3af;">
                    <p class="font-semibold">Receiving Officer (Police)</p>
                    <p class="mt-6">&nbsp;</p>
                    <p class="border-t" style="border-color:#9ca3af;">Name / Badge No / Date</p>
                </div>
            </div>
        </div>

    </DashboardLayout>
</template>

<style>
@media print {
    .no-print  { display: none !important; }
    .print-only { display: block !important; }
    body { background: white !important; }
}
.print-only { display: none; }
</style>
