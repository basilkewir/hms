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

const navigation = computed(() => props.navigation || getNavigationForRole(props.user?.roles?.[0]?.name || 'front_desk'))

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

const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-GB') : '—'

// Escape HTML to prevent XSS when writing data into the print window
const esc = (s) => String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')

const printReport = () => {
    const guests = props.checkedInGuests || []
    const fmtD = (d) => d ? new Date(d).toLocaleDateString('en-GB') : '—'

    const rows = guests.map((r, i) => {
        const g = r.guest || {}
        const bg = i % 2 === 0 ? '#ffffff' : '#f5f5f5'

        const idPassport = [
            g.id_number  ? `${esc(g.id_type || 'ID')}: ${esc(g.id_number)}` : '',
            g.passport_number ? `Passport: ${esc(g.passport_number)}${g.passport_expiry_date ? ` <span style="color:#666">(exp ${fmtD(g.passport_expiry_date)})</span>` : ''}` : '',
        ].filter(Boolean).join('<br>') || '—'

        const visa = g.visa_number
            ? `${esc(g.visa_type || 'Visa')}: ${esc(g.visa_number)}${g.visa_expiry_date ? ` <span style="color:#666">(exp ${fmtD(g.visa_expiry_date)})</span>` : ''}`
            : '—'

        const arrivalFrom = g.arrival_from
            ? esc(g.arrival_from) + (g.departure_to ? `<br><span style="color:#666">→ ${esc(g.departure_to)}</span>` : '')
            : '—'

        const contact = esc(g.phone || '—') +
            (g.emergency_contact_name ? `<br><span style="color:#555;font-size:6.5pt">Emergency: ${esc(g.emergency_contact_name)}${g.emergency_contact_phone ? ` (${esc(g.emergency_contact_phone)})` : ''}</span>` : '')

        return `<tr style="background:${bg}">
          <td>${i + 1}</td>
          <td><strong>${esc(r.room_number)}</strong></td>
          <td><strong>${esc(g.full_name || '—')}</strong><br><span style="color:#666;font-size:6.5pt">${esc(r.reservation_number || '')}</span></td>
          <td>${fmtD(g.date_of_birth)}<br><span style="color:#555;text-transform:capitalize">${esc(g.gender || '—')}</span></td>
          <td>${esc(g.nationality || '—')}</td>
          <td>${idPassport}</td>
          <td>${visa}</td>
          <td>${arrivalFrom}</td>
          <td>${esc(g.purpose_of_visit || '—')}</td>
          <td>${fmtD(r.check_in_date)}${r.actual_check_in ? `<br><span style="color:#666;font-size:6.5pt">${esc(r.actual_check_in)}</span>` : ''}</td>
          <td>${fmtD(r.check_out_date)}<br><span style="color:#666;font-size:6.5pt">${esc(String(r.nights ?? ''))} night${r.nights !== 1 ? 's' : ''}</span></td>
          <td>${contact}</td>
        </tr>`
    }).join('')

    const tableHtml = guests.length === 0
        ? '<p style="text-align:center;padding:30px 0;color:#666">No guests currently checked in.</p>'
        : `<table>
            <thead><tr>
              <th>#</th><th>Room</th><th>Guest Name</th><th>DOB / Gender</th>
              <th>Nationality</th><th>ID / Passport</th><th>Visa</th>
              <th>Arrival From</th><th>Purpose</th><th>Check-in</th><th>Check-out</th><th>Contact</th>
            </tr></thead>
            <tbody>${rows}</tbody>
          </table>`

    const html = `<!DOCTYPE html><html><head><meta charset="utf-8">
<title>Police Report &mdash; ${esc(props.hotelName || 'Hotel')}</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body{font-family:Arial,Helvetica,sans-serif;font-size:9pt;color:#000;background:#fff;padding:8mm}
  h1{font-size:15pt;font-weight:bold;text-align:center;margin-bottom:3px}
  h2{font-size:9.5pt;font-weight:bold;text-align:center;text-transform:uppercase;letter-spacing:1.5px;margin-top:8px}
  .meta{text-align:center;font-size:8pt;color:#444;margin:2px 0}
  .header{border-bottom:2px solid #333;padding-bottom:10px;margin-bottom:12px}
  table{width:100%;border-collapse:collapse;font-size:7pt;margin-top:4px}
  th{background:#e8e8e8;text-align:left;padding:4px 5px;border-bottom:2px solid #222;font-weight:bold;white-space:nowrap}
  td{padding:3px 5px;border-bottom:1px solid #ddd;vertical-align:top}
  .sigs{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-top:36px}
  .sig{border-top:1px solid #888;padding-top:5px;font-size:8pt}
  .sig-line{border-top:1px solid #888;margin-top:22px;padding-top:3px;font-size:7.5pt;color:#555}
  @page{size:A4 landscape;margin:10mm}
</style></head><body>
<div class="header">
  <h1>${esc(props.hotelName || 'Hotel')}</h1>
  ${props.hotelAddress ? `<p class="meta">${esc(props.hotelAddress)}</p>` : ''}
  ${props.hotelPhone   ? `<p class="meta">${esc(props.hotelPhone)}</p>` : ''}
  <h2>Guest Register for Police / Immigration</h2>
  <p class="meta">Report generated: ${esc(props.reportDate || '')}</p>
  <p class="meta"><strong>Total Guests Currently Checked In: ${guests.length}</strong></p>
</div>
${tableHtml}
<div class="sigs">
  <div class="sig"><strong>Prepared By</strong><div class="sig-line">Name / Signature / Date</div></div>
  <div class="sig"><strong>Hotel Manager / Front Desk</strong><div class="sig-line">Name / Signature / Date</div></div>
  <div class="sig"><strong>Receiving Officer (Police)</strong><div class="sig-line">Name / Badge No / Date</div></div>
</div>
</body></html>`

    const win = window.open('', '_blank', 'width=1200,height=900')
    if (!win) { window.print(); return }  // fallback if popup blocked
    win.document.write(html)
    win.document.close()
    win.focus()
    setTimeout(() => { win.print(); win.close() }, 400)
}
</script>

<template>
    <Head title="Police Report – Checked-In Guests" />
    <DashboardLayout title="Police Report" :user="user" :navigation="navigation">
        <!-- Top action bar (hidden on print) -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6 no-print">
            <div class="flex items-center gap-3">
                <Link href="/front-desk/checkin"
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
        <div id="police-report-print" class="rounded-lg shadow-xl p-8 print:p-4 print:shadow-none"
             :style="{ backgroundColor: themeColors.card, color: themeColors.textPrimary, border: '1px solid ' + themeColors.border }">

            <!-- Report header -->
            <div class="text-center border-b pb-4 mb-6" :style="{ borderColor: themeColors.border }">
                <h1 class="text-2xl font-bold mb-1">{{ hotelName }}</h1>
                <p v-if="hotelAddress" class="text-sm text-gray-500">{{ hotelAddress }}</p>
                <p v-if="hotelPhone" class="text-sm text-gray-500">{{ hotelPhone }}</p>
                <div class="mt-4">
                    <h2 class="text-lg font-bold uppercase tracking-widest">Guest Register for Police / Immigration</h2>
                    <p class="text-sm text-gray-500 mt-1">Report generated: {{ reportDate }}</p>
                    <p class="text-sm font-semibold mt-1" :style="{ color: themeColors.primary }">Total Guests Currently Checked In: {{ checkedInGuests?.length ?? 0 }}</p>
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
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Signature area -->
            <div class="mt-12 grid grid-cols-3 gap-8 text-xs print:grid-cols-3" :style="{ color: themeColors.textSecondary }">
                <div class="border-t pt-2" :style="{ borderColor: themeColors.border }">
                    <p class="font-semibold">Prepared By</p>
                    <p class="mt-6">&nbsp;</p>
                    <p class="border-t" :style="{ borderColor: themeColors.border }">Name / Signature / Date</p>
                </div>
                <div class="border-t pt-2" :style="{ borderColor: themeColors.border }">
                    <p class="font-semibold">Hotel Manager / Front Desk</p>
                    <p class="mt-6">&nbsp;</p>
                    <p class="border-t" :style="{ borderColor: themeColors.border }">Name / Signature / Date</p>
                </div>
                <div class="border-t pt-2" :style="{ borderColor: themeColors.border }">
                    <p class="font-semibold">Receiving Officer (Police)</p>
                    <p class="mt-6">&nbsp;</p>
                    <p class="border-t" :style="{ borderColor: themeColors.border }">Name / Badge No / Date</p>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<style>
@media print {
    aside { display: none !important; }
    header { display: none !important; }
    .no-print { display: none !important; }

    .flex-1 { margin-left: 0 !important; }
    main { padding: 0 !important; }

    #police-report-print {
        background: #fff !important;
        background-color: #fff !important;
        color: #000 !important;
        box-shadow: none !important;
        border: 1px solid #ccc !important;
        padding: 10mm !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    #police-report-print,
    #police-report-print * {
        color: #000 !important;
    }

    #police-report-print .overflow-x-auto {
        overflow: visible !important;
    }

    #police-report-print table {
        font-size: 8pt !important;
        width: 100% !important;
        table-layout: auto !important;
    }
    #police-report-print th,
    #police-report-print td {
        padding: 2px 4px !important;
    }

    #police-report-print thead tr {
        background-color: #f0f0f0 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    #police-report-print tbody tr:nth-child(even) {
        background-color: #f8f8f8 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    @page { margin: 10mm; size: A4 landscape; }
}
</style>
