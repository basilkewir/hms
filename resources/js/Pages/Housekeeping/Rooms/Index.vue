<template>
    <DashboardLayout title="Room Status" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Room Status Overview</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Monitor and update room cleaning status.</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="refreshStatus" 
                            class="text-white px-4 py-2 rounded-md transition-colors"
                            :style="{ backgroundColor: themeColors.primary }">
                        <ArrowPathIcon class="h-4 w-4 mr-2 inline" />
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Room Status Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg shadow p-6" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <ClockIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.danger }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Dirty Rooms</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ roomStats?.dirty || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <WrenchScrewdriverIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.warning }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">In Progress</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ roomStats?.inProgress || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <CheckCircleIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.success }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Clean</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ roomStats?.clean || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <ExclamationTriangleIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.warning }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Maintenance</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ roomStats?.maintenance || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <Link :href="route('housekeeping.rooms.to-clean')" 
                  class="rounded-lg p-4 transition-colors"
                  :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                <div class="flex items-center">
                    <ClockIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.danger }" />
                    <div>
                        <h3 class="font-medium" :style="{ color: themeColors.textPrimary }">Rooms to Clean</h3>
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">{{ roomStats.dirty }} rooms need cleaning</p>
                    </div>
                </div>
            </Link>

            <Link :href="route('housekeeping.rooms.in-progress')" 
                  class="rounded-lg p-4 transition-colors"
                  :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                <div class="flex items-center">
                    <WrenchScrewdriverIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.warning }" />
                    <div>
                        <h3 class="font-medium" :style="{ color: themeColors.textPrimary }">In Progress</h3>
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">{{ roomStats.inProgress }} rooms being cleaned</p>
                    </div>
                </div>
            </Link>

            <Link :href="route('housekeeping.rooms.completed')" 
                  class="rounded-lg p-4 transition-colors"
                  :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                <div class="flex items-center">
                    <CheckCircleIcon class="h-8 w-8 mr-3" :style="{ color: themeColors.success }" />
                    <div>
                        <h3 class="font-medium" :style="{ color: themeColors.textPrimary }">Completed Today</h3>
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">{{ roomStats.completedToday }} rooms cleaned</p>
                    </div>
                </div>
            </Link>
        </div>

        <!-- Room Grid -->
        <div class="shadow rounded-lg p-6" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">All Rooms</h3>
                <div class="flex space-x-2">
                    <select v-model="selectedFloor" 
                            class="border rounded-md px-3 py-1 text-sm focus:outline-none focus:ring-2"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        <option value="">All Floors</option>
                        <option v-for="floor in uniqueFloors" :key="floor" :value="floor">
                            {{ floor }}
                        </option>
                    </select>
                    <select v-model="selectedStatus" 
                            class="border rounded-md px-3 py-1 text-sm focus:outline-none focus:ring-2"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        <option value="">All Status</option>
                        <option value="dirty">Dirty</option>
                        <option value="in_progress">In Progress</option>
                        <option value="clean">Clean</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <div v-for="room in filteredRooms" :key="room.id" 
                     class="border rounded-lg p-4 cursor-pointer transition-all hover:shadow-md"
                     :class="getRoomCardClass(room.status)"
                     @click="selectRoom(room)">
                    <div class="text-center">
                        <div class="text-lg font-bold mb-2" :style="{ color: themeColors.textPrimary }">{{ room.number }}</div>
                        <div class="text-sm mb-2" :style="{ color: themeColors.textSecondary }">{{ room.type }}</div>
                        <div class="flex items-center justify-center mb-2">
                            <component :is="getStatusIcon(room.status)" 
                                      class="h-6 w-6"
                                      :class="getStatusIconColor(room.status)" />
                        </div>
                        <div class="text-xs font-medium"
                             :class="getStatusTextColor(room.status)">
                            {{ formatStatus(room.status) }}
                        </div>
                        <div v-if="room.assigned_to" class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">
                            {{ room.assigned_to }}
                        </div>
                        <div v-if="room.checkout_time" class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">
                            Checkout: {{ room.checkout_time }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Room Details Modal (unified dark theme, keyboard-safe) -->
        <div v-if="selectedRoom" class="fixed inset-0 bg-black/60 z-50 p-4 flex items-center justify-center overflow-y-auto min-h-full min-h-[100dvh]"
             @click="closeModal">
            <div class="bg-kotel-dark border border-kotel-yellow/30 rounded-xl shadow-2xl max-w-md w-full mx-4 my-auto flex flex-col max-h-[min(90vh,90dvh)]" @click.stop>
                <!-- Header (fixed) -->
                <div class="bg-kotel-black/50 border-b border-kotel-yellow/30 p-5 rounded-t-xl flex-shrink-0">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold text-kotel-yellow">Room {{ selectedRoom.number }}</h2>
                            <p v-if="selectedRoom.type || selectedRoom.floor" class="text-kotel-sky-blue mt-1">{{ selectedRoom.type || '' }}{{ selectedRoom.type && selectedRoom.floor ? ' · ' : '' }}{{ selectedRoom.floor ? `Floor ${selectedRoom.floor}` : '' }}</p>
                        </div>
                        <button @click="closeModal" class="text-white/80 hover:text-white p-1 rounded">
                            <XMarkIcon class="h-6 w-6" />
                        </button>
                    </div>
                    <div class="mt-3">
                        <span class="px-3 py-1 rounded-full text-sm font-medium border" :class="getModalStatusBadgeClass(selectedRoom.status)">
                            {{ formatStatus(selectedRoom.status) }}
                        </span>
                    </div>
                </div>

                <!-- Scrollable body so keyboard doesn't cover input/buttons -->
                <div class="overflow-y-auto flex-1 min-h-0 p-5 space-y-4">
                    <div>
                        <label class="block text-kotel-sky-blue font-medium mb-2 text-sm">Status</label>
                        <select v-model="selectedRoom.status"
                                class="w-full bg-kotel-black/50 border border-kotel-yellow/30 text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-kotel-yellow focus:border-kotel-yellow">
                            <option value="dirty" class="bg-kotel-dark text-white">Dirty</option>
                            <option value="in_progress" class="bg-kotel-dark text-white">In Progress</option>
                            <option value="clean" class="bg-kotel-dark text-white">Clean</option>
                            <option value="maintenance" class="bg-kotel-dark text-white">Maintenance</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-kotel-sky-blue font-medium mb-2 text-sm">Notes</label>
                        <textarea v-model="selectedRoom.notes" rows="3"
                                  class="w-full bg-kotel-black/50 border border-kotel-yellow/30 text-white placeholder-gray-500 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-kotel-yellow focus:border-kotel-yellow"
                                  placeholder="Add any notes about this room..."></textarea>
                    </div>
                </div>

                <!-- Footer (always visible when scrolled; stays at bottom of modal) -->
                <div class="p-5 pt-4 border-t border-kotel-yellow/30 bg-kotel-black/30 rounded-b-xl flex gap-3 flex-shrink-0">
                    <button @click="updateRoomStatus"
                            class="flex-1 bg-kotel-yellow text-kotel-black py-3 px-4 rounded-lg hover:bg-yellow-400 font-medium transition">
                        Update Status
                    </button>
                    <button @click="closeModal"
                            class="flex-1 bg-kotel-gray border border-kotel-yellow/30 text-kotel-yellow py-3 px-4 rounded-lg hover:bg-kotel-yellow/20 font-medium transition">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme.js'
import {
    ArrowPathIcon,
    ClockIcon,
    WrenchScrewdriverIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    XMarkIcon
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
}))

loadTheme()

const props = defineProps({
    user: Object,
    rooms: {
        type: Array,
        default: () => []
    },
    roomStats: {
        type: Object,
        default: () => ({
            dirty: 0,
            inProgress: 0,
            clean: 0,
            maintenance: 0,
            completedToday: 0
        })
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

const navigation = computed(() => getNavigationForRole('housekeeping'))

const selectedFloor = ref(props.filters?.floor || '')
const selectedStatus = ref(props.filters?.status || '')
const selectedRoom = ref(null)

const uniqueFloors = computed(() => {
    const floors = [...new Set(props.rooms.map(r => r.floor).filter(Boolean))]
    return floors.sort()
})

const filteredRooms = computed(() => {
    return props.rooms.filter(room => {
        const matchesFloor = !selectedFloor.value || room.floor?.toString() === selectedFloor.value
        const matchesStatus = !selectedStatus.value || room.status === selectedStatus.value
        return matchesFloor && matchesStatus
    })
})

const getRoomCardClass = (status) => {
    const classes = {
        dirty: 'border-red-300 bg-red-50',
        in_progress: 'border-yellow-300 bg-yellow-50',
        clean: 'border-green-300 bg-green-50',
        maintenance: 'border-orange-300 bg-orange-50'
    }
    return classes[status] || 'border-gray-300 bg-gray-50'
}

const getStatusIcon = (status) => {
    const icons = {
        dirty: ClockIcon,
        in_progress: WrenchScrewdriverIcon,
        clean: CheckCircleIcon,
        maintenance: ExclamationTriangleIcon
    }
    return icons[status] || ClockIcon
}

const getStatusIconColor = (status) => {
    const colors = {
        dirty: 'text-red-500',
        in_progress: 'text-yellow-500',
        clean: 'text-green-500',
        maintenance: 'text-orange-500'
    }
    return colors[status] || 'text-gray-500'
}

const getStatusTextColor = (status) => {
    const colors = {
        dirty: 'text-red-700',
        in_progress: 'text-yellow-700',
        clean: 'text-green-700',
        maintenance: 'text-orange-700'
    }
    return colors[status] || 'text-gray-700'
}

const formatStatus = (status) => {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getModalStatusBadgeClass = (status) => {
    const classes = {
        dirty: 'bg-red-500/30 text-red-200 border-red-400/40',
        in_progress: 'bg-amber-500/30 text-amber-200 border-amber-400/40',
        clean: 'bg-emerald-500/30 text-emerald-200 border-emerald-400/40',
        maintenance: 'bg-orange-500/30 text-orange-200 border-orange-400/40'
    }
    return classes[status] || 'bg-gray-500/30 text-gray-200 border-gray-400/40'
}

const selectRoom = (room) => {
    selectedRoom.value = { ...room }
}

const closeModal = () => {
    selectedRoom.value = null
}

const updateRoomStatus = () => {
    if (!selectedRoom.value) return

    router.post(route('housekeeping.rooms.update-status', selectedRoom.value.id), {
        status: selectedRoom.value.status,
        notes: selectedRoom.value.notes
    }, {
        onSuccess: () => {
            closeModal()
            router.reload({ only: ['rooms', 'roomStats'] })
        },
        onError: (errors) => {
            alert('Failed to update room status. Please try again.')
            console.error(errors)
        }
    })
}

const refreshStatus = () => {
    router.reload({ only: ['rooms', 'roomStats'] })
}
</script>
