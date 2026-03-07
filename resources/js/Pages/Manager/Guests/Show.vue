<template>
    <DashboardLayout title="Guest Details" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Guest Details</h1>
                    <p class="text-gray-600 mt-2">{{ guest.full_name }}</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('manager.guests.edit', guest.id)" 
                          class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        Edit Guest
                    </Link>
                    <Link :href="route('manager.guests.index')" 
                          class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                        Back to Guests
                    </Link>
                </div>
            </div>
        </div>

        <!-- Guest Information -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2 bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Guest ID</label>
                        <p class="mt-1 text-sm text-gray-900">{{ guest.guest_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Status</label>
                        <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :style="getStatusStyle(guest.current_status || 'no_reservations')">
                            {{ formatStatus(guest.current_status || 'no_reservations') }}
                        </span>
                    </div>
                    <div v-if="guest.police_verification_status">
                        <label class="block text-sm font-medium text-gray-500">Police Verification</label>
                        <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :style="getStatusStyle(guest.police_verification_status)">
                            {{ formatStatus(guest.police_verification_status) }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Title</label>
                        <p class="mt-1 text-sm text-gray-900">{{ guest.title || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Full Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ guest.full_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Date of Birth</label>
                        <p class="mt-1 text-sm text-gray-900">{{ formatDate(guest.date_of_birth) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Age</label>
                        <p class="mt-1 text-sm text-gray-900">{{ guest.age || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Gender</label>
                        <p class="mt-1 text-sm text-gray-900 capitalize">{{ guest.gender || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Nationality</label>
                        <p class="mt-1 text-sm text-gray-900">{{ guest.nationality || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Occupation</label>
                        <p class="mt-1 text-sm text-gray-900">{{ guest.occupation || 'N/A' }}</p>
                    </div>
                    <div v-if="guest.guest_type">
                        <label class="block text-sm font-medium text-gray-500">Guest Type</label>
                        <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :style="getGuestTypeBadgeStyle(guest.guest_type?.color)">
                            {{ guest.guest_type.name }}
                        </span>
                    </div>
                </div>

                <h2 class="text-lg font-semibold text-gray-900 mb-4 mt-6">Contact Information</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Email</label>
                        <p class="mt-1 text-sm text-gray-900">{{ guest.email || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Phone</label>
                        <p class="mt-1 text-sm text-gray-900">{{ guest.phone || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Alternate Phone</label>
                        <p class="mt-1 text-sm text-gray-900">{{ guest.alternate_phone || 'N/A' }}</p>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-500">Address</label>
                        <p class="mt-1 text-sm text-gray-900">{{ formatAddress(guest) }}</p>
                    </div>
                </div>

                <h2 class="text-lg font-semibold text-gray-900 mb-4 mt-6">Emergency Contact</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ guest.emergency_contact_name || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Phone</label>
                        <p class="mt-1 text-sm text-gray-900">{{ guest.emergency_contact_phone || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Relationship</label>
                        <p class="mt-1 text-sm text-gray-900">{{ guest.emergency_contact_relationship || 'N/A' }}</p>
                    </div>
                </div>

                <h2 class="text-lg font-semibold text-gray-900 mb-4 mt-6">Identification</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">ID Type</label>
                        <p class="mt-1 text-sm text-gray-900 capitalize">{{ guest.id_type?.replace('_', ' ') || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">ID Number</label>
                        <p class="mt-1 text-sm text-gray-900">{{ guest.id_number || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Issuing Authority</label>
                        <p class="mt-1 text-sm text-gray-900">{{ guest.id_issuing_authority || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Issue Date</label>
                        <p class="mt-1 text-sm text-gray-900">{{ formatDate(guest.id_issue_date) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Expiry Date</label>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ formatDate(guest.id_expiry_date) }}
                            <span v-if="guest.is_id_expired" class="ml-2 text-red-600 text-xs">(Expired)</span>
                        </p>
                    </div>
                </div>

                <h2 class="text-lg font-semibold text-gray-900 mb-4 mt-6">Additional Information</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Purpose of Visit</label>
                        <p class="mt-1 text-sm text-gray-900">{{ guest.purpose_of_visit || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Expected Duration</label>
                        <p class="mt-1 text-sm text-gray-900">{{ guest.expected_duration_days ? guest.expected_duration_days + ' days' : 'N/A' }}</p>
                    </div>
                    <div v-if="guest.special_requests" class="col-span-2">
                        <label class="block text-sm font-medium text-gray-500">Special Requests</label>
                        <p class="mt-1 text-sm text-gray-900">{{ guest.special_requests }}</p>
                    </div>
                    <div v-if="guest.dietary_restrictions" class="col-span-2">
                        <label class="block text-sm font-medium text-gray-500">Dietary Restrictions</label>
                        <p class="mt-1 text-sm text-gray-900">{{ guest.dietary_restrictions }}</p>
                    </div>
                    <div v-if="guest.notes" class="col-span-2">
                        <label class="block text-sm font-medium text-gray-500">Notes</label>
                        <p class="mt-1 text-sm text-gray-900">{{ guest.notes }}</p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Reservation History -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Reservation History</h2>
                    <div v-if="guest.reservations && guest.reservations.length > 0" class="space-y-3">
                        <div v-for="reservation in guest.reservations.slice(0, 5)" :key="reservation.id" 
                             class="border-b border-gray-200 pb-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ reservation.reservation_number }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ formatDate(reservation.check_in_date) }} - {{ formatDate(reservation.check_out_date) }}
                                    </p>
                                    <p class="text-xs text-gray-500" v-if="reservation.room">
                                        Room {{ reservation.room.room_number }}
                                    </p>
                                </div>
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full"
                                      :style="getStatusStyle(reservation.status)">
                                    {{ formatStatus(reservation.status) }}
                                </span>
                            </div>
                        </div>
                        <Link :href="route('manager.guests.history')" 
                              class="text-sm text-blue-600 hover:text-blue-900">
                            View all history →
                        </Link>
                    </div>
                    <p v-else class="text-sm text-gray-500">No reservation history</p>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                    <div class="space-y-2">
                        <Link :href="route('manager.reservations.create', { guest_id: guest.id })"
                              class="block w-full text-center bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Create Reservation
                        </Link>
                        <Link :href="route('manager.guests.edit', guest.id)"
                              class="block w-full text-center bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                            Edit Guest
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'

const props = defineProps({
    user: Object,
    guest: Object,
})

const navigation = computed(() => getNavigationForRole('manager'))

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const formatAddress = (guest) => {
    const parts = []
    if (guest.address) parts.push(guest.address)
    if (guest.city) parts.push(guest.city)
    if (guest.state) parts.push(guest.state)
    if (guest.country) parts.push(guest.country)
    if (guest.postal_code) parts.push(guest.postal_code)
    return parts.length > 0 ? parts.join(', ') : 'N/A'
}

const formatStatus = (status) => {
    if (!status) return 'N/A'
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getStatusStyle = (status) => {
    const statusMap = {
        verified: { backgroundColor: '#d1fae5', color: '#065f46' },
        pending: { backgroundColor: '#fef3c7', color: '#92400e' },
        flagged: { backgroundColor: '#fee2e2', color: '#991b1b' },
        checked_in: { backgroundColor: '#d1fae5', color: '#065f46' },
        checked_out: { backgroundColor: '#f3f4f6', color: '#1f2937' },
        confirmed: { backgroundColor: '#dbeafe', color: '#1e40af' },
        cancelled: { backgroundColor: '#fee2e2', color: '#991b1b' },
        no_reservations: { backgroundColor: '#f3f4f6', color: '#6b7280' },
        no_show: { backgroundColor: '#fef3c7', color: '#92400e' },
    }
    return statusMap[status] || { backgroundColor: '#f3f4f6', color: '#1f2937' }
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
</script>
