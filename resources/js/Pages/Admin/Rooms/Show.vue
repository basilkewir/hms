<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {
    HomeIcon,
    UserIcon,
    CalendarDaysIcon,
    CurrencyDollarIcon,
    WrenchScrewdriverIcon,
    CheckCircleIcon,
    ClockIcon,
    ArrowLeftIcon,
    PencilIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline'

// Theme system
const themeColors = ref({
    primary: '#3b82f6',
    hover: '#2563eb',
    background: '#f8fafc',
    card: '#ffffff',
    border: '#e2e8f0',
    textPrimary: '#1e293b',
    textSecondary: '#64748b',
    textTertiary: '#94a3b8',
    success: '#10b981',
    warning: '#f59e0b',
    error: '#ef4444',
})

const loadTheme = () => {
    const savedTheme = localStorage.getItem('themeColors')
    if (savedTheme) {
        themeColors.value = JSON.parse(savedTheme)
    }
}

loadTheme()

// Props
const props = defineProps({
    user: Object,
    room: Object,
})

// Computed properties
const statusColor = computed(() => {
    const colors = {
        'available': 'bg-green-100 text-green-800',
        'occupied': 'bg-blue-100 text-blue-800',
        'cleaning': 'bg-yellow-100 text-yellow-800',
        'maintenance': 'bg-red-100 text-red-800',
        'out_of_order': 'bg-gray-100 text-gray-800',
    }
    return colors[props.room.status] || 'bg-gray-100 text-gray-800'
})

const housekeepingColor = computed(() => {
    const colors = {
        'clean': 'bg-green-100 text-green-800',
        'dirty': 'bg-red-100 text-red-800',
        'inspected': 'bg-blue-100 text-blue-800',
        'maintenance_required': 'bg-yellow-100 text-yellow-800',
        'waiting_for_check': 'bg-purple-100 text-purple-800',
    }
    return colors[props.room.housekeeping_status] || 'bg-gray-100 text-gray-800'
})

// Methods
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount || 0)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>

<template>
    <Head :title="`Room ${room.number} - Details`" />
    
    <DashboardLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <Link :href="route('admin.rooms.index')" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                                <ArrowLeftIcon class="h-4 w-4 mr-1" />
                                Back to Rooms
                            </Link>
                            <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">
                                Room {{ room.number }}
                            </h1>
                        </div>
                        <div class="flex space-x-3">
                            <Link :href="route('admin.rooms.edit', room.id)" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <PencilIcon class="h-4 w-4 mr-2" />
                                Edit Room
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Room Details Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Information -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Information -->
                        <div class="bg-white rounded-lg shadow border" :style="{ borderColor: themeColors.border }">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">
                                    Room Information
                                </h3>
                            </div>
                            <div class="px-6 py-4">
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Room Number</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ room.number }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Room Type</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ room.type }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Floor</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ room.floor }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Capacity</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ room.capacity }} guests</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                                        <dd class="mt-1">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="statusColor">
                                                {{ room.status.replace('_', ' ').toUpperCase() }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Housekeeping Status</dt>
                                        <dd class="mt-1">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="housekeepingColor">
                                                {{ room.housekeeping_status.replace('_', ' ').toUpperCase() }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Nightly Rate</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ formatCurrency(room.rate) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Last Cleaned</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ room.last_cleaned || 'Never' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Guest Information (if occupied) -->
                        <div v-if="room.guest" class="bg-white rounded-lg shadow border" :style="{ borderColor: themeColors.border }">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">
                                    Current Guest
                                </h3>
                            </div>
                            <div class="px-6 py-4">
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Guest Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ room.guest }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Check-in</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(room.check_in) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Check-out</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(room.check_out) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Reservation ID</dt>
                                        <dd class="mt-1 text-sm text-gray-900">#{{ room.reservation_id }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Amenities -->
                        <div v-if="room.amenities && room.amenities.length > 0" class="bg-white rounded-lg shadow border" :style="{ borderColor: themeColors.border }">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">
                                    Room Amenities
                                </h3>
                            </div>
                            <div class="px-6 py-4">
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                    <div v-for="amenity in room.amenities" :key="amenity" class="flex items-center">
                                        <CheckCircleIcon class="h-4 w-4 text-green-500 mr-2" />
                                        <span class="text-sm text-gray-900">{{ amenity }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div v-if="room.notes" class="bg-white rounded-lg shadow border" :style="{ borderColor: themeColors.border }">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">
                                    Notes
                                </h3>
                            </div>
                            <div class="px-6 py-4">
                                <p class="text-sm text-gray-900">{{ room.notes }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Quick Actions -->
                        <div class="bg-white rounded-lg shadow border" :style="{ borderColor: themeColors.border }">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">
                                    Quick Actions
                                </h3>
                            </div>
                            <div class="px-6 py-4 space-y-3">
                                <Link :href="route('admin.rooms.edit', room.id)" class="block w-full text-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <PencilIcon class="h-4 w-4 inline mr-2" />
                                    Edit Room
                                </Link>
                                <Link :href="route('admin.rooms.status')" class="block w-full text-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <HomeIcon class="h-4 w-4 inline mr-2" />
                                    Room Status
                                </Link>
                            </div>
                        </div>

                        <!-- Room Status Summary -->
                        <div class="bg-white rounded-lg shadow border" :style="{ borderColor: themeColors.border }">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">
                                    Status Summary
                                </h3>
                            </div>
                            <div class="px-6 py-4 space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Current Status</span>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="statusColor">
                                        {{ room.status.replace('_', ' ').toUpperCase() }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Housekeeping</span>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="housekeepingColor">
                                        {{ room.housekeeping_status.replace('_', ' ').toUpperCase() }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Occupancy</span>
                                    <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                        {{ room.guest ? 'Occupied' : 'Available' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Special Features -->
                        <div v-if="room.special_features" class="bg-white rounded-lg shadow border" :style="{ borderColor: themeColors.border }">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">
                                    Special Features
                                </h3>
                            </div>
                            <div class="px-6 py-4">
                                <p class="text-sm text-gray-900">{{ room.special_features }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
