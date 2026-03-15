<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { getNavigationForRole } from '@/Utils/navigation.js'
import {
    PrinterIcon,
    ArrowDownTrayIcon,
    ArrowLeftIcon,
    ShieldCheckIcon,
} from '@heroicons/vue/24/outline'

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
}))
loadTheme()

const props = defineProps({
    user: Object,
    navigation: Array,
    checkedInGuests: Array,
    reportDate: String,
    hotelName: String,
    hotelAddress: String,
    hotelPhone: String,
})

const navigation = computed(() => props.navigation || getNavigationForRole(props.user?.roles?.[0]?.name || 'admin'))

// Print the police report
const printReport = () => {
    window.print()
}

// Export as CSV
const exportCSV = () => {
    const guests = props.checkedInGuests || []
    const headers = [
        'Room No', 'Room Type', 'Reservation No', 'Full Name', 'Gender',
        'Date of Birth', 'Nationality', 'ID Type', 'ID Number', 'ID Expiry',
        'Passport No', 'Passport Expiry', 'Visa No', 'Visa Type', 'Visa Expiry',
        'Phone', 'Email', 'Address', 'City', 'Country',
        'Arrival From', 'Departure To', 'Purpose of Visit',
        'Check-in Date', 'Check-out Date', 'Actual Check-in', 'Nights',
        'Adults', 'Children', 'Police Verification Status',
        'Emergency Contact', 'Emergency Phone',
    ]
    const rows = guests.map(r => {
        const g = r.guest || {}
        return [
            r.room_number,
            r.room_type,
            r.reservation_number,
            g.full_name || '',
            g.gender || '',
            g.date_of_birth || '',
            g.nationality || '',
            g.id_type || '',
            g.id_number || '',
            g.id_expiry_date || '',
            g.passport_number || '',
            g.passport_expiry_date || '',
            g.visa_number || '',
            g.visa_type || '',
            g.visa_expiry_date || '',
            g.phone || '',
            g.email || '',
            g.address || '',
            g.city || '',
            g.country || '',
            g.arrival_from || '',
            g.departure_to || '',
            g.purpose_of_visit || '',
            r.check_in_date || '',
            r.check_out_date || '',
            r.actual_check_in || '',
            r.nights || '',
            r.number_of_adults || '',
            r.number_of_children || '',
            g.police_verification_status || '',
            g.emergency_contact_name || '',
            g.emergency_contact_phone || '',
        ].map(v => `"${String(v).replace(/"/g, '""')}"`)
    })

    const csv = [headers.join(','), ...rows.map(r => r.join(','))].join('\n')
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `police-report-${new Date().toISOString().slice(0, 10)}.csv`
    a.click()
    URL.revokeObjectURL(url)
}

const policeStatusClass = (status) => {
    if (status === 'verified') return 'bg-green-100 text-green-800'
    if (status === 'pending') return 'bg-yellow-100 text-yellow-800'
    if (status === 'flagged') return 'bg-red-100 text-red-800'
    return 'bg-gray-100 text-gray-600'
}

const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-GB') : '—'
</script>

<template>
    <Head title="Police Report – Checked-In Guests" />
    <DashboardLayout title="Police Report" :user="user" :navigation="navigation">
        <!-- Top action bar (hidden on print) -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6 no-print">
            <div class="flex items-center gap-3">
                <Link href="/admin/checkin"
                      class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium transition-colors"
                      :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary, border: '1px solid ' + themeColors.border }">
                    <ArrowLeftIcon class="h-4 w-4" />
                    Back to Check-in
                </Link>
                <h1 class="text-xl font-semibold flex items-center gap-2" :style="{ color: themeColors.textPrimary }">
                    <ShieldCheckIcon class="h-6 w-6 text-blue-500" />
                    Police Report — Currently Checked-In Guests
                </h1>
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
                    Print Report
                </button>
            </div>
        </div>

        <!-- Printable report -->
        <div id="police-report-print" class="rounded-lg shadow-xl p-8 print:p-4 print:shadow-none" style="background:#fff; color:#000;">

            <!-- Report header -->
            <div class="text-center border-b pb-4 mb-6" style="border-color:#d1d5db;">
                <h1 class="text-2xl font-bold mb-1">{{ hotelName }}</h1>
                <p v-if="hotelAddress" class="text-sm text-gray-500">{{ hotelAddress }}</p>
                <p v-if="hotelPhone" class="text-sm text-gray-500">{{ hotelPhone }}</p>
                <div class="mt-4">
                    <h2 class="text-lg font-bold uppercase tracking-widest">Guest Register for Police / Immigration</h2>
                    <p class="text-sm text-gray-500 mt-1">Report generated: {{ reportDate }}</p>
                    <p class="text-sm font-semibold mt-1 text-blue-700">Total Guests Currently Checked In: {{ checkedInGuests?.length ?? 0 }}</p>
                </div>
            </div>

            <!-- No guests -->
            <div v-if="!checkedInGuests || checkedInGuests.length === 0" class="text-center py-16 text-gray-400">
                <ShieldCheckIcon class="h-16 w-16 mx-auto mb-4 opacity-40" />
                <p class="text-lg font-medium">No guests currently checked in.</p>
            </div>

            <!-- Guest table -->
            <div v-else class="overflow-x-auto">
                <table class="w-full border-collapse text-xs print:text-[10px]">
                    <thead>
                        <tr class="border-b-2" style="border-color:#374151; background:#f9fafb;">
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">#</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">Room</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">Guest Name</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">DOB / Gender</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">Nationality</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">ID / Passport</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">Visa</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">Arrival From</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">Purpose</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">Check-in</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">Check-out</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">Contact</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-700">Police Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(r, i) in checkedInGuests" :key="i"
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
                                <div v-if="!r.guest?.id_number && !r.guest?.passport_number" class="text-gray-400">—</div>
                            </td>
                            <td class="py-2 px-2">
                                <div v-if="r.guest?.visa_number">
                                    {{ r.guest?.visa_type || 'Visa' }}: {{ r.guest?.visa_number }}
                                    <span v-if="r.guest?.visa_expiry_date" class="text-[9px] text-gray-400 ml-1">(exp {{ formatDate(r.guest?.visa_expiry_date) }})</span>
                                </div>
                                <span v-else class="text-gray-400">—</span>
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
                                    {{ r.guest?.police_verification_status || 'pending' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Signature area -->
            <div class="mt-12 grid grid-cols-3 gap-8 text-xs text-gray-600 print:grid-cols-3">
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
    .no-print { display: none !important; }
    body { background: white !important; }
    #police-report-print { box-shadow: none !important; }
}
</style>
