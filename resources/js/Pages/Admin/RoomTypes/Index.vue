<template>
    <DashboardLayout title="Room Types Management">
        <!-- Header -->
        <div class="bg-kotel-dark border border-kotel-yellow/30 rounded-lg p-6 mb-8"
             style="background-color: #1a1a1a; border: 1px solid rgba(255, 215, 0, 0.3);">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-kotel-yellow"
                        style="color: #FFD700;">Room Types Management</h1>
                    <p class="text-kotel-sky-blue mt-2"
                       style="color: #87CEEB;">Manage different room types and their configurations.</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="addRoomType"
                            class="bg-kotel-yellow text-kotel-black px-4 py-2 rounded-md hover:bg-kotel-yellow/90 transition-colors"
                            style="background-color: #FFD700; color: #000000;">
                        <PlusIcon class="h-4 w-4 mr-2 inline" />
                        Add Room Type
                    </button>
                </div>
            </div>
        </div>

        <!-- Room Types Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="roomType in roomTypes" :key="roomType.id"
                 class="bg-kotel-dark border border-kotel-yellow/30 rounded-lg shadow p-6 hover:shadow-lg transition-shadow"
                 style="background-color: #1a1a1a; border: 1px solid rgba(255, 215, 0, 0.3);">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-white"
                        style="color: #ffffff;">{{ roomType.name }}</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="getStatusColor(roomType.status)"
                          :style="getStatusStyle(roomType.status)">
                        {{ formatStatus(roomType.status) }}
                    </span>
                </div>

                <div class="space-y-2 mb-4">
                    <p class="text-sm text-kotel-sky-blue/80"
                       style="color: rgba(135, 206, 235, 0.8);">{{ roomType.description }}</p>
                    <div class="flex items-center text-sm text-kotel-sky-blue/60"
                         style="color: rgba(135, 206, 235, 0.6);">
                        <UserGroupIcon class="h-4 w-4 mr-1" />
                        <span>Max {{ roomType.max_occupancy }} guests</span>
                    </div>
                    <div class="flex items-center text-sm text-kotel-sky-blue/60"
                         style="color: rgba(135, 206, 235, 0.6);">
                        <HomeIcon class="h-4 w-4 mr-1" />
                        <span>{{ roomType.room_count }} rooms</span>
                    </div>
                    <div class="flex items-center text-sm text-kotel-sky-blue/60"
                         style="color: rgba(135, 206, 235, 0.6);">
                        <CurrencyDollarIcon class="h-4 w-4 mr-1" />
                        <span>{{ formatCurrency(roomType.base_rate) }}/night</span>
                    </div>
                </div>

                <div class="flex space-x-2">
                    <button @click="editRoomType(roomType)"
                            class="flex-1 bg-kotel-yellow text-kotel-black px-3 py-2 rounded text-sm hover:bg-kotel-yellow/90 transition-colors"
                            style="background-color: #FFD700; color: #000000;">
                        Edit
                    </button>
                    <button @click="deleteRoomType(roomType)"
                            class="flex-1 bg-red-600/90 text-white px-3 py-2 rounded text-sm hover:bg-red-600 transition-colors border border-red-400/50"
                            style="background-color: rgba(220, 38, 38, 0.9); color: #ffffff; border: 1px solid rgba(248, 113, 113, 0.5);">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import {
    PlusIcon,
    UserGroupIcon,
    HomeIcon,
    CurrencyDollarIcon
} from '@heroicons/vue/24/outline'
import { formatCurrency } from '@/Utils/currency.js'

const props = defineProps({
    user: Object,
    roomTypes: Array,
    flash: Object
})

const navigation = computed(() => getNavigationForRole('admin'))

const getStatusColor = (status) => {
    const colors = {
        active: 'bg-emerald-800/50 text-emerald-300 border border-emerald-400/50',
        inactive: 'bg-red-800/50 text-red-300 border border-red-400/50'
    }
    return colors[status] || 'bg-kotel-gray text-kotel-sky-blue border border-kotel-gray'
}

const getStatusStyle = (status) => {
    const styles = {
        active: {
            backgroundColor: 'rgba(16, 185, 129, 0.5)',
            color: '#86efac',
            border: '1px solid rgba(52, 211, 153, 0.5)'
        },
        inactive: {
            backgroundColor: 'rgba(153, 27, 27, 0.5)',
            color: '#fca5a5',
            border: '1px solid rgba(248, 113, 113, 0.5)'
        }
    }
    return styles[status] || {
        backgroundColor: '#374151',
        color: '#87CEEB',
        border: '1px solid #374151'
    }
}

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1)
}

const addRoomType = () => {
    router.get('/admin/room-types/create')
}

const editRoomType = (roomType) => {
    router.get(`/admin/room-types/${roomType.id}/edit`)
}

const deleteRoomType = (roomType) => {
    if (confirm(`Delete room type: ${roomType.name}? This action cannot be undone.`)) {
        router.delete(`/admin/room-types/${roomType.id}`, {
            onSuccess: () => {
                // Success message will be handled by flash
            },
            onError: () => {
                alert('Failed to delete room type. It may be in use by existing rooms.')
            }
        })
    }
}
</script>
