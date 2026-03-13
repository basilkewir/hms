<template>
    <DashboardLayout title="Room Management" :user="user" :navigation="navigation">
        <!-- Rooms Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Room Management</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Manage hotel rooms and inventory.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('manager.rooms.create')" 
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Add New Room
                    </Link>
                    <button @click="exportRooms"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{ 
                                backgroundColor: themeColors.success,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = '#059669'"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                        Export Rooms
                    </button>
                </div>
            </div>
        </div>

        <!-- Room Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
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
                        <HomeIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Rooms</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ roomStats.total }}</p>
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
                           :style="{ color: themeColors.textSecondary }">Available</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ roomStats.available }}</p>
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
                        <UserIcon class="h-6 w-6" :style="{ color: '#8b5cf6' }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Occupied</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ roomStats.occupied }}</p>
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
                        <WrenchScrewdriverIcon class="h-6 w-6" :style="{ color: themeColors.danger }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Maintenance</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ roomStats.maintenance }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="rounded-lg p-6 mb-8 border shadow-sm"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Search</label>
                    <input type="text" v-model="searchQuery" placeholder="Search rooms..."
                           class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                           :style="{
                               backgroundColor: themeColors.background,
                               borderColor: themeColors.border,
                               color: themeColors.textPrimary,
                               borderWidth: '1px',
                               borderStyle: 'solid'
                           }">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Room Type</label>
                    <select v-model="selectedType"
                            class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                            :style="{
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary,
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }">
                        <option value="">All Types</option>
                        <option value="standard_single">Standard Single</option>
                        <option value="standard_double">Standard Double</option>
                        <option value="deluxe_suite">Deluxe Suite</option>
                        <option value="presidential_suite">Presidential Suite</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Floor</label>
                    <select v-model="selectedFloor"
                            class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                            :style="{
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary,
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }">
                        <option value="">All Floors</option>
                        <option v-for="floor in floors" :key="floor.id" :value="floor.floor_number">
                            {{ floor.name || `Floor ${floor.floor_number}` }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Status</label>
                    <select v-model="selectedStatus"
                            class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                            :style="{
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary,
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }">
                        <option value="">All Status</option>
                        <option value="available">Available</option>
                        <option value="occupied">Occupied</option>
                        <option value="cleaning">Cleaning</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="out_of_order">Out of Order</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Rooms Table -->
        <div class="rounded-lg overflow-hidden shadow border"
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
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">All Rooms</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Room Number
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Floor
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Rate
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                IPTV
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="room in filteredRooms" :key="room.id" 
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
                                {{ room.number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getTypeBadgeClass(room.type)">
                                    {{ formatType(room.type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ room.floor }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusBadgeClass(room.status)">
                                    {{ formatStatus(room.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(room.rate) }}/night
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="room.has_iptv ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                                    {{ room.has_iptv ? 'Enabled' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-2">
                                    <Link :href="route('manager.rooms.edit', room.id)"
                                          class="transition-colors"
                                          :style="{ color: themeColors.primary }"
                                          @mouseenter="$event.target.style.color = themeColors.hover"
                                          @mouseleave="$event.target.style.color = themeColors.primary">Edit</Link>
                                    <button @click="deleteRoom(room.id, room.room_number)"
                                            class="transition-colors"
                                            :style="{ color: '#dc2626' }"
                                            @mouseenter="$event.target.style.color = '#991b1b'"
                                            @mouseleave="$event.target.style.color = '#dc2626'">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { formatCurrency, initializeCurrencySettings } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme.js'
import {
    HomeIcon,
    PlusIcon,
    CheckCircleIcon,
    UserIcon,
    WrenchScrewdriverIcon,
    DocumentArrowDownIcon
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
    rooms: Array,
    roomStats: Object,
    floors: {
        type: Array,
        default: () => []
    },
})

const navigation = computed(() => getNavigationForRole('manager'))

const searchQuery = ref('')
const selectedType = ref('')
const selectedFloor = ref('')
const selectedStatus = ref('')

const roomStats = computed(() => props.roomStats || {
    total: 0,
    available: 0,
    occupied: 0,
    maintenance: 0,
})

const filteredRooms = computed(() => {
    return (props.rooms?.data || []).filter(room => {
        const matchesSearch = !searchQuery.value || 
            room.number?.toLowerCase().includes(searchQuery.value.toLowerCase())
        
        const matchesType = !selectedType.value || room.type?.toLowerCase().includes(selectedType.value.toLowerCase())
        const matchesFloor = !selectedFloor.value || room.floor_number == selectedFloor.value
        const matchesStatus = !selectedStatus.value || room.status === selectedStatus.value
        
        return matchesSearch && matchesType && matchesFloor && matchesStatus
    })
})

const getTypeBadgeClass = (type) => {
    const classes = {
        standard_single: 'bg-blue-500 text-white',
        standard_double: 'bg-green-500 text-white',
        deluxe_suite: 'bg-purple-500 text-white',
        presidential_suite: 'bg-red-500 text-white',
    }
    return classes[type] || 'bg-gray-500 text-white'
}

const getStatusBadgeClass = (status) => {
    const classes = {
        available: 'bg-green-100 text-green-800',
        occupied: 'bg-blue-100 text-blue-800',
        cleaning: 'bg-yellow-100 text-yellow-800',
        maintenance: 'bg-red-100 text-red-800',
        out_of_order: 'bg-gray-100 text-gray-800',
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatType = (type) => {
    return type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatStatus = (status) => {
    return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const deleteRoom = (id, roomNumber) => {
    if (!confirm(`Are you sure you want to delete Room ${roomNumber}? This cannot be undone.`)) return
    router.delete(route('manager.rooms.destroy', id))
}

const exportRooms = () => {
    try {
        // Get current date for filename
        const today = new Date().toISOString().split('T')[0]
        const filename = `manager_rooms_${today}.csv`
        
        // Prepare CSV data
        const csvData = generateRoomsCSV()
        
        // Create blob and download
        const blob = new Blob([csvData], { type: 'text/csv;charset=utf-8;' })
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
        showNotification('Rooms exported successfully!', 'success')
    } catch (error) {
        console.error('Export error:', error)
        showNotification('Failed to export rooms', 'error')
    }
}

const generateRoomsCSV = () => {
    const headers = [
        'Room Number',
        'Room Type',
        'Floor',
        'Status',
        'Rate/Night',
        'IPTV Enabled',
        'Current Guest',
        'Check-in Date',
        'Check-out Date',
        'Created At'
    ]
    
    const rows = filteredRooms.value.map(room => {
        return [
            room.number || '',
            room.type || '',
            room.floor || '',
            room.status || '',
            room.rate || 0,
            room.has_iptv ? 'Yes' : 'No',
            room.current_guest?.full_name || '',
            room.actual_check_in || '',
            room.check_out_date || '',
            room.created_at || ''
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

// Initialize currency settings from database on mount
onMounted(() => {
    initializeCurrencySettings()
})
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

.grid-cols-4 {
    grid-template-columns: repeat(4, minmax(0, 1fr));
}

.gap-2 {
    gap: 0.5rem;
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
.p-3 {
    padding: 0.75rem;
}

.p-4 {
    padding: 1rem;
}

.p-6 {
    padding: 1.5rem;
}

.px-3 {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
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

.mb-6 {
    margin-bottom: 1.5rem;
}

.mb-8 {
    margin-bottom: 2rem;
}

.mr-2 {
    margin-right: 0.5rem;
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

/* Ensure badge text is visible and not overridden by theme */
.inline-flex.items-center.px-2\.5\.py-0\.5.rounded-full.text-xs.font-medium {
    color: inherit !important;
}

/* Room type badge colors - distinct backgrounds with white text */
.bg-blue-500.text-white {
    background-color: rgb(59 130 246) !important;
    color: rgb(255 255 255) !important;
}

.bg-green-500.text-white {
    background-color: rgb(34 197 94) !important;
    color: rgb(255 255 255) !important;
}

.bg-yellow-100.text-yellow-800 {
    background-color: rgb(254 249 195) !important;
    color: rgb(133 77 14) !important;
}

.bg-red-500.text-white {
    background-color: rgb(239 68 68) !important;
    color: rgb(255 255 255) !important;
}

.bg-purple-500.text-white {
    background-color: rgb(139 92 246) !important;
    color: rgb(255 255 255) !important;
}

.bg-gray-500.text-white {
    background-color: rgb(107 114 128) !important;
    color: rgb(255 255 255) !important;
}

/* Status badge colors - keep light backgrounds with dark text */
.bg-green-100.text-green-800 {
    background-color: rgb(220 252 231) !important;
    color: rgb(22 101 52) !important;
}

.bg-blue-100.text-blue-800 {
    background-color: rgb(219 234 254) !important;
    color: rgb(30 64 175) !important;
}

.bg-yellow-100.text-yellow-800 {
    background-color: rgb(254 249 195) !important;
    color: rgb(133 77 14) !important;
}

.bg-red-100.text-red-800 {
    background-color: rgb(254 226 226) !important;
    color: rgb(153 27 27) !important;
}

.bg-gray-100.text-gray-800 {
    background-color: rgb(243 244 246) !important;
    color: rgb(31 41 55) !important;
}

/* Responsive utilities */
@media (min-width: 768px) {
    .md\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    
    .md\:grid-cols-4 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .lg\:grid-cols-4 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}
</style>
