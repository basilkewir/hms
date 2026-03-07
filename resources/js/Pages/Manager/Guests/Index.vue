<template>
    <DashboardLayout title="Guest Management" :user="user">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Guest Management</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Manage all hotel guests and their information.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="exportGuests"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{ backgroundColor: '#8b5cf6' }"
                            @mouseenter="$event.target.style.backgroundColor = '#7c3aed'"
                            @mouseleave="$event.target.style.backgroundColor = '#8b5cf6'">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                        Export
                    </button>
                    <Link :href="route('manager.guests.create')"
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ backgroundColor: themeColors.primary }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Add Guest
                    </Link>
                </div>
            </div>
        </div>

        <!-- Guest Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         style="background-color: rgba(59,130,246,0.1)">
                        <UserGroupIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Total Guests</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ guestStats.total }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         style="background-color: rgba(34,197,94,0.1)">
                        <UserIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Current Guests</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ guestStats.current }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         style="background-color: rgba(250,204,21,0.1)">
                        <StarIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">VIP Guests</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ guestStats.vip }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         style="background-color: rgba(139,92,246,0.1)">
                        <ArrowPathIcon class="h-6 w-6" style="color: #8b5cf6" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Returning Guests</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ guestStats.returning }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Search</label>
                    <input type="text" v-model="searchQuery" placeholder="Search guests..."
                           class="w-full rounded-md px-3 py-2 focus:outline-none text-sm"
                           :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Status</label>
                    <select v-model="selectedStatus"
                            class="w-full rounded-md px-3 py-2 focus:outline-none text-sm"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                        <option value="">All Status</option>
                        <option value="checked_in">Checked In</option>
                        <option value="checked_out">Checked Out</option>
                        <option value="reserved">Reserved</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Guest Type</label>
                    <select v-model="selectedType"
                            class="w-full rounded-md px-3 py-2 focus:outline-none text-sm"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                        <option value="">All Types</option>
                        <option value="regular">Regular</option>
                        <option value="vip">VIP</option>
                        <option value="corporate">Corporate</option>
                        <option value="group">Group</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Date Range</label>
                    <select v-model="selectedDateRange"
                            class="w-full rounded-md px-3 py-2 focus:outline-none text-sm"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                        <option value="">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="year">This Year</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button @click="clearFilters"
                            class="w-full px-4 py-2 rounded-md transition-colors font-medium text-white text-sm"
                            :style="{ backgroundColor: themeColors.secondary }"
                            @mouseenter="$event.target.style.opacity = '0.85'"
                            @mouseleave="$event.target.style.opacity = '1'">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Guests Table -->
        <div class="rounded-lg overflow-hidden shadow"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
            <div class="px-6 py-4 border-b"
                 :style="{ borderColor: themeColors.border, borderBottomWidth: '1px' }">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">All Guests</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Guest</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Current Stay</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Total Stays</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="guest in filteredGuests" :key="guest.id"
                            class="transition-colors"
                            :style="{ borderBottomStyle: 'solid', borderBottomWidth: '1px', borderColor: themeColors.border }"
                            @mouseenter="$event.currentTarget.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.currentTarget.style.backgroundColor = 'transparent'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center mr-4 font-semibold text-sm"
                                         :style="{ backgroundColor: themeColors.primary, color: themeColors.background }">
                                        {{ getInitials(guest.name) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ guest.name }}</div>
                                        <div class="text-sm" :style="{ color: themeColors.textSecondary }">ID: {{ guest.guest_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm" :style="{ color: themeColors.textPrimary }">{{ guest.email }}</div>
                                <div class="text-sm" :style="{ color: themeColors.textSecondary }">{{ guest.phone }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div v-if="guest.current_room" class="text-sm" :style="{ color: themeColors.textPrimary }">
                                    Room {{ guest.current_room }}
                                </div>
                                <div v-if="guest.checkout_date" class="text-sm" :style="{ color: themeColors.textSecondary }">
                                    Until {{ formatDate(guest.checkout_date) }}
                                </div>
                                <div v-if="!guest.current_room" class="text-sm" :style="{ color: themeColors.textTertiary }">
                                    No current stay
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span v-if="guest.guest_type && guest.guest_type.color"
                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="getGuestTypeBadgeStyle(guest.guest_type.color)">
                                    {{ guest.guest_type.name }}
                                </span>
                                <span v-else
                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="getTypeColor(guest.type)">
                                    {{ formatType(guest.type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ guest.total_stays }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="getStatusColor(guest.status)">
                                    {{ formatStatus(guest.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click="viewGuest(guest)"
                                        class="mr-3 transition-colors"
                                        :style="{ color: themeColors.primary }"
                                        @mouseenter="$event.target.style.opacity = '0.7'"
                                        @mouseleave="$event.target.style.opacity = '1'">View</button>
                                <button @click="editGuest(guest)"
                                        class="mr-3 transition-colors"
                                        :style="{ color: themeColors.success }"
                                        @mouseenter="$event.target.style.opacity = '0.7'"
                                        @mouseleave="$event.target.style.opacity = '1'">Edit</button>
                                <button @click="guestHistory(guest)"
                                        class="mr-3 transition-colors"
                                        style="color: #8b5cf6"
                                        @mouseenter="$event.target.style.opacity = '0.7'"
                                        @mouseleave="$event.target.style.opacity = '1'">History</button>
                                <button v-if="guest.status === 'checked_in'"
                                        @click="checkOutGuest(guest)"
                                        class="transition-colors"
                                        :style="{ color: themeColors.warning }"
                                        @mouseenter="$event.target.style.opacity = '0.7'"
                                        @mouseleave="$event.target.style.opacity = '1'">Check Out</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="!filteredGuests || filteredGuests.length === 0" class="text-center py-12">
                <UserGroupIcon class="mx-auto h-12 w-12 mb-4" :style="{ color: themeColors.textTertiary }" />
                <h3 class="text-sm font-medium mb-1" :style="{ color: themeColors.textPrimary }">No guests found</h3>
                <p class="text-sm mb-6" :style="{ color: themeColors.textSecondary }">Get started by creating a new guest.</p>
                <Link :href="route('manager.guests.create')"
                      class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-white transition-colors"
                      :style="{ backgroundColor: themeColors.primary }">
                    <PlusIcon class="h-4 w-4 mr-2" />
                    Add New Guest
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="guests?.links" class="px-6 py-4 border-t"
                 :style="{ borderColor: themeColors.border, borderTopWidth: '1px' }">
                <Pagination :links="guests.links" :meta="guests" />
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { useTheme } from '@/Composables/useTheme.js'
import {
    PlusIcon,
    DocumentArrowDownIcon,
    UserGroupIcon,
    UserIcon,
    StarIcon,
    ArrowPathIcon
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
    hover: `rgba(255, 255, 255, 0.1)`
}))
loadTheme()

const props = defineProps({
    user: Object,
    guests: Object,
    guestStats: Object,
})

const searchQuery = ref('')
const selectedStatus = ref('')
const selectedType = ref('')
const selectedDateRange = ref('')

const guestStats = computed(() => props.guestStats || {
    total: 0,
    current: 0,
    vip: 0,
    returning: 0
})

const filteredGuests = computed(() => {
    if (!props.guests?.data) return []
    
    return props.guests.data.filter(guest => {
        const matchesSearch = !searchQuery.value || 
            guest.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            guest.email?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            guest.guest_id?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            guest.phone?.toLowerCase().includes(searchQuery.value.toLowerCase())
        
        const matchesStatus = !selectedStatus.value || guest.status === selectedStatus.value
        const matchesType = !selectedType.value || guest.type === selectedType.value
        
        return matchesSearch && matchesStatus && matchesType
    })
})

const getInitials = (name) => {
    if (!name) return '?'
    return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const getTypeColor = (type) => {
    // Return inline style object instead of Tailwind classes to avoid theme overrides
    const colorMap = {
        regular: { backgroundColor: '#f3f4f6', color: '#1f2937' },
        vip: { backgroundColor: '#fef3c7', color: '#92400e' },
        corporate: { backgroundColor: '#dbeafe', color: '#1e40af' },
        group: { backgroundColor: '#e9d5ff', color: '#6b21a8' }
    }
    return colorMap[type] || { backgroundColor: '#f3f4f6', color: '#1f2937' }
}

const getGuestTypeBadgeStyle = (color) => {
    if (!color) {
        return {
            backgroundColor: '#6b7280',
            color: '#ffffff'
        }
    }
    
    // Convert hex to RGB to determine if background is light or dark
    const hex = color.replace('#', '')
    const r = parseInt(hex.substr(0, 2), 16)
    const g = parseInt(hex.substr(2, 2), 16)
    const b = parseInt(hex.substr(4, 2), 16)
    
    // Calculate luminance
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255
    
    // Use white text for dark backgrounds, black for light backgrounds
    const textColor = luminance > 0.5 ? '#000000' : '#ffffff'
    
    return {
        backgroundColor: color,
        color: textColor
    }
}

const getStatusColor = (status) => {
    // Return inline style object instead of Tailwind classes to avoid theme overrides
    const colorMap = {
        checked_in: { backgroundColor: '#d1fae5', color: '#065f46' },
        checked_out: { backgroundColor: '#f3f4f6', color: '#1f2937' },
        reserved: { backgroundColor: '#dbeafe', color: '#1e40af' },
        cancelled: { backgroundColor: '#fee2e2', color: '#991b1b' },
        confirmed: { backgroundColor: '#dbeafe', color: '#1e40af' },
        pending: { backgroundColor: '#fef3c7', color: '#92400e' },
        no_show: { backgroundColor: '#fed7aa', color: '#9a3412' }
    }
    return colorMap[status] || { backgroundColor: '#f3f4f6', color: '#1f2937' }
}

const formatType = (type) => {
    if (!type) return ''
    return type.charAt(0).toUpperCase() + type.slice(1)
}

const formatStatus = (status) => {
    if (!status) return ''
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const clearFilters = () => {
    searchQuery.value = ''
    selectedStatus.value = ''
    selectedType.value = ''
    selectedDateRange.value = ''
}

const exportGuests = () => {
    alert('Exporting guest list...')
}

const viewGuest = (guest) => {
    router.visit(route('manager.guests.show', guest.id))
}

const editGuest = (guest) => {
    router.visit(route('manager.guests.edit', guest.id))
}

const guestHistory = (guest) => {
    router.visit(route('manager.guests.history'))
}

const checkOutGuest = (guest) => {
    const reservationId = guest.current_reservation_id
    if (!reservationId) {
        alert('This guest has no current checked-in reservation.')
        return
    }
    // Open checkout page with this reservation so user sees accumulated bill and can complete checkout
    router.get(route('manager.checkout'), { reservation_id: reservationId })
}
</script>
