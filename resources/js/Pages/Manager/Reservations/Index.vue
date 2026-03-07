<template>
    <DashboardLayout title="Reservations Overview" :user="user">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Reservations Overview</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">System-wide reservation analytics and management.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.reservations.create')" 
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        New Reservation
                    </Link>
                    <button @click="exportReservations" 
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{ 
                                backgroundColor: '#8b5cf6',
                            }"
                            @mouseenter="$event.target.style.backgroundColor = '#7c3aed'"
                            @mouseleave="$event.target.style.backgroundColor = '#8b5cf6'">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                        Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Reservation Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                        <CalendarDaysIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Reservations</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ reservationStats?.total || 0 }}</p>
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
                           :style="{ color: themeColors.textSecondary }">Confirmed</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ reservationStats?.confirmed || 0 }}</p>
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
                           :style="{ color: themeColors.textPrimary }">{{ reservationStats?.pending || 0 }}</p>
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
                         :style="{ backgroundColor: 'rgba(139, 92, 246, 0.1)' }">
                        <UserGroupIcon class="h-6 w-6" :style="{ color: '#8b5cf6' }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Checked In</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ reservationStats?.checkedIn || 0 }}</p>
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
                         :style="{ backgroundColor: 'rgba(251, 146, 60, 0.1)' }">
                        <CalendarDaysIcon class="h-6 w-6" :style="{ color: '#fb923c' }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Today's Arrivals</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ reservationStats?.todayArrivals || 0 }}</p>
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
                         :style="{ backgroundColor: 'rgba(239, 68, 68, 0.1)' }">
                        <ClockIcon class="h-6 w-6" :style="{ color: themeColors.danger }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Today's Departures</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ reservationStats?.todayDepartures || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Reservations Table -->
        <div class="rounded-lg overflow-hidden shadow"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderBottomWidth: '1px'
                 }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Recent Reservations</h3>
            </div>
            
            <!-- Table Content -->
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Confirmation
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Guest
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
                                Booking Source
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
                        <tr v-for="reservation in recentReservations" :key="reservation.id" 
                            class="transition-colors"
                            :style="{ 
                                borderBottomStyle: 'solid',
                                borderBottomWidth: '1px',
                                borderColor: themeColors.border
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ reservation.reservation_number || reservation.confirmation_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium"
                                     :style="{ color: themeColors.textPrimary }">{{ reservation.guest_name }}</div>
                                <div class="text-sm"
                                     :style="{ color: themeColors.textSecondary }">{{ reservation.guest_email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textPrimary }">{{ formatDate(reservation.check_in_date) }}</div>
                                <div class="text-sm"
                                     :style="{ color: themeColors.textSecondary }">to {{ formatDate(reservation.check_out_date) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: reservation.room_number ? themeColors.textPrimary : themeColors.warning }">
                                {{ reservation.room_number || 'TBA' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(reservation.total_amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                {{ formatBookingSource(reservation.booking_source) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusBadgeClass(reservation.status)">
                                    {{ formatStatus(reservation.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link :href="route('admin.reservations.show', reservation.id)"
                                      class="mr-3 transition-colors"
                                      :style="{ color: themeColors.primary }"
                                      @mouseenter="$event.target.style.color = themeColors.hover"
                                      @mouseleave="$event.target.style.color = themeColors.primary">View</Link>
                                <Link :href="route('admin.reservations.edit', reservation.id)"
                                      class="mr-3 transition-colors"
                                      :style="{ color: themeColors.success }"
                                      @mouseenter="$event.target.style.color = themeColors.hover"
                                      @mouseleave="$event.target.style.color = themeColors.success">Edit</Link>
                                <Link :href="route('admin.reservations.service-charges', reservation.id)"
                                      class="transition-colors"
                                      :style="{ color: '#8b5cf6' }"
                                      @mouseenter="$event.target.style.color = '#7c3aed'"
                                      @mouseleave="$event.target.style.color = '#8b5cf6'">Charges</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div v-if="reservations.links" class="px-6 py-4 border-t"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderTopWidth: '1px'
                 }">
                <Pagination :links="reservations.links" />
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme.js'
import {
    DocumentArrowDownIcon,
    CalendarDaysIcon,
    CheckCircleIcon,
    ClockIcon,
    UserGroupIcon,
    PlusIcon
} from '@heroicons/vue/24/outline'
import Pagination from '@/Components/Pagination.vue'

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
    reservations: Object,
    reservationStats: Object,
    bookingSources: Object,
})

const reservationStats = computed(() => props.reservationStats || {
    total: 0,
    confirmed: 0,
    pending: 0,
    checkedIn: 0
})

const recentReservations = computed(() => {
    if (!props.reservations?.data) return []
    return props.reservations.data
})

const getStatusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-blue-100 text-blue-800',
        checked_in: 'bg-green-100 text-green-800',
        checked_out: 'bg-gray-100 text-gray-800',
        cancelled: 'bg-red-100 text-red-800',
        no_show: 'bg-red-100 text-red-800',
        modified: 'bg-orange-100 text-orange-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatStatus = (status) => {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatBookingSource = (source) => {
    const sources = {
        'walk_in': 'Walk-in',
        'phone': 'Phone',
        'email': 'Email',
        'website': 'Website',
        'booking_com': 'Booking.com',
        'expedia': 'Expedia',
        'agoda': 'Agoda',
        'travel_agent': 'Travel Agent',
        'corporate': 'Corporate',
    }
    return sources[source] || source
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const exportReservations = () => {
    showExportDialog()
}

const showExportDialog = () => {
    // Create modal dialog
    const modal = document.createElement('div')
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50'
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6" style="background-color: var(--kotel-card); color: var(--kotel-text-primary);">
            <h3 class="text-lg font-semibold mb-4">Choose Export Format</h3>
            <div class="space-y-3">
                <button onclick="exportData('csv')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div>
                            <div class="font-medium">CSV</div>
                            <div class="text-sm text-gray-500">Excel-compatible spreadsheet format</div>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                <button onclick="exportData('excel')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v6a2 2 0 002 2h2m4-4h.01M17 16h.01"></path>
                        </svg>
                        <div>
                            <div class="font-medium">Excel</div>
                            <div class="text-sm text-gray-500">HTML table for Excel import</div>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                <button onclick="exportData('pdf')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <div class="font-medium">PDF</div>
                            <div class="text-sm text-gray-500">Portable Document Format</div>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                <button onclick="exportData('word')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div>
                            <div class="font-medium">Word</div>
                            <div class="text-sm text-gray-500">HTML format for Word import</div>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
            <div class="flex gap-3 mt-6">
                <button onclick="closeExportDialog()" class="flex-1 px-4 py-2 border rounded-lg hover:bg-gray-50 transition-colors" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    Cancel
                </button>
            </div>
        </div>
    `
    
    // Add to page
    document.body.appendChild(modal)
    
    // Make functions globally available
    window.exportData = (format) => {
        closeExportDialog()
        performExport(format)
    }
    
    window.closeExportDialog = () => {
        document.body.removeChild(modal)
        delete window.exportData
        delete window.closeExportDialog
    }
    
    // Close on backdrop click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeExportDialog()
        }
    })
}

const performExport = (format) => {
    try {
        const today = new Date().toISOString().split('T')[0]
        let filename, content, mimeType
        
        switch (format) {
            case 'csv':
                filename = `reservations_${today}.csv`
                content = generateReservationsCSV()
                mimeType = 'text/csv;charset=utf-8;'
                break
            case 'excel':
                filename = `reservations_${today}.html`
                content = generateExcelContent()
                mimeType = 'text/html;charset=utf-8;'
                break
            case 'pdf':
                filename = `reservations_${today}.pdf`
                content = generatePDFContent()
                mimeType = 'application/pdf'
                break
            case 'word':
                filename = `reservations_${today}.html`
                content = generateWordContent()
                mimeType = 'text/html;charset=utf-8;'
                break
        }
        
        // Create blob and download
        const blob = new Blob([content], { type: mimeType })
        const link = document.createElement('a')
        const url = URL.createObjectURL(blob)
        
        link.setAttribute('href', url)
        link.setAttribute('download', filename)
        link.style.visibility = 'hidden'
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        
        // Clean up
        URL.revokeObjectURL(url)
        
        // Show success message
        showNotification(`Reservations exported as ${format.toUpperCase()} successfully!`, 'success')
    } catch (error) {
        console.error('Export error:', error)
        showNotification(`Failed to export as ${format.toUpperCase()}`, 'error')
    }
}

const generateExcelContent = () => {
    // Generate HTML table that can be opened in Excel
    const csvData = generateReservationsCSV()
    const headers = csvData.split('\n')[0]
    const rows = csvData.split('\n').slice(1)
    
    // Create HTML table
    let htmlTable = '<table>\n'
    
    // Add headers
    htmlTable += '<thead><tr>'
    headers.split(',').forEach(header => {
        htmlTable += `<th>${header.replace(/"/g, '')}</th>`
    })
    htmlTable += '</tr></thead>\n'
    
    // Add data rows using actual reservation data
    htmlTable += '<tbody>'
    recentReservations.value.forEach(reservation => {
        if (reservation) {
            const data = [
                reservation.reservation_number || reservation.confirmation_number || '',
                reservation.guest_name || '',
                reservation.room_number || '',
                reservation.room_type || '',
                reservation.check_in_date || '',
                reservation.check_out_date || '',
                reservation.nights || 0,
                reservation.total_amount || 0,
                reservation.paid_amount || 0,
                reservation.balance_amount || 0,
                reservation.status || '',
                formatBookingSource(reservation.booking_source) || '',
                reservation.created_at || ''
            ]
            
            htmlTable += '<tr>'
            data.forEach(cell => {
                htmlTable += `<td>${String(cell).replace(/"/g, '')}</td>`
            })
            htmlTable += '</tr>\n'
        }
    })
    htmlTable += '</tbody></table>'
    
    // Add HTML structure
    const htmlContent = `
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reservations Export</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Reservations Export - ${new Date().toLocaleDateString()}</h2>
    ${htmlTable}
</body>
</html>`
    
    return htmlContent
}

const generatePDFContent = () => {
    // Generate a proper PDF using a simple but effective approach
    const reservations = recentReservations.value
    const totalRecords = reservations.length
    
    // Create PDF content as a base64-encoded string
    const pdfHeader = '%PDF-1.4\n'
    const catalog = '1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n'
    const pages = '2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n'
    
    // Create page content
    let pageContent = '3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n/Contents 4 0 R\n/Resources <<\n/Font <<\n/F1 5 0 R\n>>\n>>\n>>\nendobj\n'
    
    // Create content stream
    let content = 'BT\n/F1 12 Tf\n72 720 Td\n(Reservations Report) Tj\n'
    content += '0 -20 Td\n/F1 10 Tf\n'
    content += '(Generated on ' + new Date().toLocaleDateString() + ' at ' + new Date().toLocaleTimeString() + ') Tj\n'
    content += '0 -15 Td\n'
    content += '(Total Records: ' + totalRecords + ') Tj\n'
    content += '0 -30 Td\n/F1 8 Tf\n'
    
    // Add table headers
    const headers = ['Reservation Number', 'Guest Name', 'Room Number', 'Room Type', 'Check-in Date', 'Check-out Date', 'Nights', 'Total Amount', 'Paid Amount', 'Balance Amount', 'Status', 'Booking Source', 'Created At']
    headers.forEach((header, index) => {
        content += '(' + header + ') Tj\n'
        if (index < headers.length - 1) {
            content += '100 0 Td\n'
        } else {
            content += '0 -15 Td\n'
        }
    })
    
    // Add data rows
    reservations.forEach((reservation, rowIndex) => {
        if (rowIndex < 20) { // Limit to 20 rows for space
            const yPos = 600 - (rowIndex * 12)
            content += '72 ' + yPos + ' Td\n'
            
            const data = [
                String(reservation.reservation_number || reservation.confirmation_number || ''),
                String(reservation.guest_name || ''),
                String(reservation.room_number || ''),
                String(reservation.room_type || ''),
                String(reservation.check_in_date || ''),
                String(reservation.check_out_date || ''),
                String(reservation.nights || 0),
                String(reservation.total_amount || 0),
                String(reservation.paid_amount || 0),
                String(reservation.balance_amount || 0),
                String(reservation.status || ''),
                String(formatBookingSource(reservation.booking_source) || ''),
                String(reservation.created_at || '')
            ]
            
            data.forEach((cell, cellIndex) => {
                // Escape PDF special characters
                const escapedCell = cell.replace(/[\(\)]/g, '\\$&')
                content += '(' + escapedCell + ') Tj\n'
                if (cellIndex < data.length - 1) {
                    content += '100 0 Td\n'
                }
            })
            
            if (rowIndex < reservations.length - 1 && rowIndex < 19) {
                content += '0 -12 Td\n'
            }
        }
    })
    
    content += 'ET\n'
    
    const contentLength = content.length
    const contentObj = '4 0 obj\n<<\n/Length ' + contentLength + '\n>>\nstream\n' + content + '\nendstream\nendobj\n'
    
    const fontObj = '5 0 obj\n<<\n/Type /Font\n/Subtype /Type1\n/BaseFont /Helvetica\n>>\nendobj\n'
    
    // Calculate offsets
    const offset1 = pdfHeader.length
    const offset2 = offset1 + catalog.length
    const offset3 = offset2 + pages.length
    const offset4 = offset3 + pageContent.length
    const offset5 = offset4 + contentObj.length
    const offset6 = offset5 + fontObj.length
    
    // Create cross-reference table
    const xref = 'xref\n0 6\n0000000000 65535 f \n' +
        '0000000009 00000 n \n' +
        String(offset1).padStart(10, '0') + ' 00000 n \n' +
        String(offset2).padStart(10, '0') + ' 00000 n \n' +
        String(offset3).padStart(10, '0') + ' 00000 n \n' +
        String(offset4).padStart(10, '0') + ' 00000 n \n' +
        String(offset5).padStart(10, '0') + ' 00000 n \n'
    
    // Calculate startxref position
    const startxrefPos = offset6 + fontObj.length + xref.length
    const trailerStr = 'trailer\n<<\n/Size 6\n/Root 1 0 R\n>>\nstartxref\n' + String(startxrefPos) + '\n%%EOF'
    
    // Combine all parts
    const pdfContent = pdfHeader + catalog + pages + pageContent + contentObj + fontObj + xref + trailerStr
    
    return pdfContent
}

const generateWordContent = () => {
    // Generate HTML content that can be opened in Word
    const csvData = generateReservationsCSV()
    const headers = csvData.split('\n')[0]
    const rows = csvData.split('\n').slice(1)
    
    // Create Word-compatible HTML
    let htmlContent = `
<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <title>Reservations Report</title>
    <style>
        @page Section1 {size:8.5in 11.0in; margin:1.0in; }
        body { font-family: 'Calibri', sans-serif; font-size: 11pt; line-height: 1.15; }
        .header { text-align: center; margin-bottom: 20pt; }
        .header h1 { font-size: 16pt; color: #2F5496; }
        .header p { font-size: 10pt; color: #595959; }
        table { width: 100%; border-collapse: collapse; margin-top: 20pt; }
        th, td { border: 1pt solid #D3D3D3; padding: 8pt; text-align: left; }
        th { background-color: #F2F2F2; font-weight: bold; }
        .footer { margin-top: 30pt; text-align: center; font-size: 9pt; color: #7F7F7F; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reservations Report</h1>
        <p>Generated on ${new Date().toLocaleDateString()} at ${new Date().toLocaleTimeString()}</p>
        <p>Total Records: ${rows.length}</p>
    </div>
    
    <table>
        <thead>
            <tr>`
    
    // Add headers
    headers.split(',').forEach(header => {
        htmlContent += `<th>${header.replace(/"/g, '')}</th>`
    })
    
    htmlContent += `
            </tr>
        </thead>
        <tbody>`
    
    // Add data rows using actual reservation data
    recentReservations.value.forEach(reservation => {
        if (reservation) {
            const data = [
                reservation.reservation_number || reservation.confirmation_number || '',
                reservation.guest_name || '',
                reservation.room_number || '',
                reservation.room_type || '',
                reservation.check_in_date || '',
                reservation.check_out_date || '',
                reservation.nights || 0,
                reservation.total_amount || 0,
                reservation.paid_amount || 0,
                reservation.balance_amount || 0,
                reservation.status || '',
                formatBookingSource(reservation.booking_source) || '',
                reservation.created_at || ''
            ]
            
            htmlContent += '<tr>'
            data.forEach(cell => {
                htmlContent += `<td>${String(cell).replace(/"/g, '')}</td>`
            })
            htmlContent += '</tr>'
        }
    })
    
    htmlContent += `
        </tbody>
    </table>
    
    <div class="footer">
        <p>Hotel Management System - Reservations Report</p>
        <p>Confidential - For Internal Use Only</p>
    </div>
</body>
</html>`
    
    return htmlContent
}

const generateReservationsCSV = () => {
    const headers = [
        'Reservation Number',
        'Guest Name',
        'Room Number',
        'Room Type',
        'Check-in Date',
        'Check-out Date',
        'Nights',
        'Total Amount',
        'Paid Amount',
        'Balance Amount',
        'Status',
        'Booking Source',
        'Created At'
    ]
    
    const rows = recentReservations.value.map(reservation => {
        return [
            reservation.reservation_number || reservation.confirmation_number || '',
            reservation.guest_name || '',
            reservation.room_number || '',
            reservation.room_type || '',
            reservation.check_in_date || '',
            reservation.check_out_date || '',
            reservation.nights || 0,
            reservation.total_amount || 0,
            reservation.paid_amount || 0,
            reservation.balance_amount || 0,
            reservation.status || '',
            formatBookingSource(reservation.booking_source) || '',
            reservation.created_at || ''
        ].map(field => `"${String(field).replace(/"/g, '""')}"`)
    })
    
    // Combine headers and rows
    const csvContent = [headers.join(','), ...rows.map(row => row.join(','))].join('\n')
    
    return csvContent
}

const showNotification = (message, type = 'info') => {
    // Create notification element
    const notification = document.createElement('div')
    notification.textContent = message
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 text-white font-medium transition-all duration-300`
    
    // Set background color based on type
    if (type === 'success') {
        notification.style.backgroundColor = '#10b981'
    } else if (type === 'error') {
        notification.style.backgroundColor = '#ef4444'
    } else {
        notification.style.backgroundColor = '#3b82f6'
    }
    
    // Add to page
    document.body.appendChild(notification)
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0'
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification)
            }
        }, 300)
    }, 3000)
}
</script>

<style scoped>
/* Fix placeholder colors for inputs */
input::placeholder,
textarea::placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input::-webkit-input-placeholder,
textarea::-webkit-input-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input::-moz-placeholder,
textarea::-moz-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input:-ms-input-placeholder,
textarea:-ms-input-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

/* Fix placeholder colors for select options */
select option:disabled,
select option[disabled] {
    color: var(--kotel-text-tertiary) !important;
}

select option[value=""] {
    color: var(--kotel-text-tertiary) !important;
}

/* Custom animations and transitions */
.transition-colors {
    transition-property: background-color, border-color, color;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Hover effects for interactive elements */
button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

button:active {
    transform: translateY(0);
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

/* Status badge improvements */
.rounded-full {
    border-radius: 9999px;
}

.inline-flex {
    display: inline-flex;
}

/* Card shadow improvements */
.shadow {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

.shadow-sm {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.rounded-lg {
    border-radius: 0.5rem;
}

.rounded-md {
    border-radius: 0.375rem;
}

/* Grid utilities */
.grid {
    display: grid;
}

.grid-cols-1 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
}

.grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.grid-cols-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.grid-cols-6 {
    grid-template-columns: repeat(6, minmax(0, 1fr));
}

.gap-3 {
    gap: 0.75rem;
}

.gap-4 {
    gap: 1rem;
}

.gap-6 {
    gap: 1.5rem;
}

/* Flex utilities */
.flex {
    display: flex;
}

.items-center {
    align-items: center;
}

.justify-between {
    justify-content: space-between;
}

/* Spacing utilities */
.p-4 {
    padding: 1rem;
}

.p-6 {
    padding: 1.5rem;
}

.px-4 {
    padding-left: 1rem;
    padding-right: 1rem;
}

.px-6 {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
}

.py-2 {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
}

.py-3 {
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
}

.py-4 {
    padding-top: 1rem;
    padding-bottom: 1rem;
}

.mb-1 {
    margin-bottom: 0.25rem;
}

.mb-2 {
    margin-bottom: 0.5rem;
}

.mb-8 {
    margin-bottom: 2rem;
}

.mr-2 {
    margin-right: 0.5rem;
}

.mr-3 {
    margin-right: 0.75rem;
}

.mr-4 {
    margin-right: 1rem;
}

/* Text utilities */
.text-sm {
    font-size: 0.875rem;
    line-height: 1.25rem;
}

.text-xs {
    font-size: 0.75rem;
    line-height: 1rem;
}

.text-lg {
    font-size: 1.125rem;
    line-height: 1.75rem;
}

.text-2xl {
    font-size: 1.5rem;
    line-height: 2rem;
}

.font-medium {
    font-weight: 500;
}

.font-bold {
    font-weight: 700;
}

.uppercase {
    text-transform: uppercase;
}

.tracking-wider {
    letter-spacing: 0.05em;
}

/* Width utilities */
.w-12 {
    width: 3rem;
}

.w-6 {
    width: 1.5rem;
}

.w-4 {
    width: 1rem;
}

.h-12 {
    height: 3rem;
}

.h-6 {
    height: 1.5rem;
}

.h-4 {
    height: 1rem;
}

/* Display utilities */
.block {
    display: block;
}

.overflow-hidden {
    overflow: hidden;
}

.overflow-x-auto {
    overflow-x: auto;
}

/* Table utilities */
.min-w-full {
    min-width: 100%;
}

.whitespace-nowrap {
    white-space: nowrap;
}

.text-left {
    text-align: left;
}

/* Border utilities */
.border {
    border-width: 1px;
}

.border-b {
    border-bottom-width: 1px;
}

.border-t {
    border-top-width: 1px;
}

/* Position utilities */
.relative {
    position: relative;
}

/* Status badge colors - Ensure they override theme */
.bg-yellow-100 {
    background-color: rgb(254 249 195) !important;
}

.text-yellow-800 {
    color: rgb(133 77 14) !important;
}

.bg-blue-100 {
    background-color: rgb(219 234 254) !important;
}

.text-blue-800 {
    color: rgb(30 64 175) !important;
}

.bg-green-100 {
    background-color: rgb(220 252 231) !important;
}

.text-green-800 {
    color: rgb(22 101 52) !important;
}

.bg-gray-100 {
    background-color: rgb(243 244 246) !important;
}

.text-gray-800 {
    color: rgb(31 41 55) !important;
}

.bg-red-100 {
    background-color: rgb(254 226 226) !important;
}

.text-red-800 {
    color: rgb(153 27 27) !important;
}

.bg-purple-100 {
    background-color: rgb(243 232 255) !important;
}

.text-purple-800 {
    color: rgb(107 33 168) !important;
}

.bg-orange-100 {
    background-color: rgb(254 236 179) !important;
}

.text-orange-800 {
    color: rgb(154 52 18) !important;
}

/* Specific badge overrides to ensure visibility */
.bg-gray-100.text-gray-800 {
    background-color: rgb(243 244 246) !important;
    color: rgb(31 41 55) !important;
}

/* Responsive utilities */
@media (min-width: 768px) {
    .md\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    
    .md\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .lg\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (min-width: 1280px) {
    .xl\:grid-cols-6 {
        grid-template-columns: repeat(6, minmax(0, 1fr));
    }
}
</style>
