<template>
    <DashboardLayout :user="user" :navigation="navigation">

        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success"
             class="mb-4 px-4 py-3 rounded-lg bg-green-900/50 border border-green-700 text-green-300 flex items-center gap-2">
            <CheckCircleIcon class="h-5 w-5 flex-shrink-0" />
            <span>{{ $page.props.flash.success }}</span>
        </div>

        <!-- Page Header -->
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
             class="shadow rounded-xl p-6 mb-6 border">
            <div class="flex items-center justify-between">
                <div>
                    <h1 :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold flex items-center gap-3">
                        <TvIcon class="h-7 w-7 text-yellow-400" />
                        Guest Display
                    </h1>
                    <p :style="{ color: themeColors.textSecondary }" class="mt-1 text-sm">
                        Type a guest's name below to show it on the room's TV.
                    </p>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div v-for="stat in statCards" :key="stat.label"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                 class="rounded-xl border p-4 flex items-center gap-3">
                <component :is="stat.icon" :style="{ color: stat.color }" class="h-8 w-8 flex-shrink-0" />
                <div>
                    <p :style="{ color: themeColors.textSecondary }" class="text-xs">{{ stat.label }}</p>
                    <p :style="{ color: themeColors.textPrimary }" class="text-xl font-bold">{{ stat.value }}</p>
                </div>
            </div>
        </div>

        <!-- Rooms grid -->
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
             class="rounded-xl border overflow-hidden mb-6">
            <div :style="{ borderColor: themeColors.border }" class="px-5 py-3 border-b flex items-center justify-between">
                <h2 :style="{ color: themeColors.textPrimary }" class="font-semibold flex items-center gap-2">
                    <BuildingOfficeIcon class="h-5 w-5 text-yellow-400" /> Rooms
                </h2>
                <button @click="showAddRoom = true"
                        class="px-3 py-1.5 rounded-lg text-xs font-medium bg-yellow-500 hover:bg-yellow-400 text-black flex items-center gap-1.5">
                    <PlusIcon class="h-4 w-4" /> Add Room
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 p-5">
                <div v-for="room in rooms" :key="room.id"
                     :style="{ backgroundColor: themeColors.background, borderColor: room.guest_name ? themeColors.success : themeColors.border }"
                     class="rounded-lg border p-4 flex flex-col gap-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <p :style="{ color: themeColors.textPrimary }" class="font-bold text-lg leading-tight">
                                Room {{ room.room_number }}
                            </p>
                            <p :style="{ color: themeColors.textSecondary }" class="text-xs">{{ room.room_type }}</p>
                        </div>
                        <span :class="room.guest_name ? 'bg-green-900/60 text-green-300' : 'bg-gray-900/60 text-gray-300'"
                              class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase">
                            {{ room.guest_name ? 'Occupied' : 'Available' }}
                        </span>
                    </div>

                    <!-- Current guest -->
                    <div v-if="room.guest_name"
                         class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg"
                         :style="{ backgroundColor: 'rgba(34,197,94,0.1)', color: themeColors.success }">
                        <span class="flex items-center gap-2 text-sm font-medium">
                            <UserIcon class="h-4 w-4" /> {{ room.guest_name }}
                        </span>
                        <button @click="checkout(room)"
                                class="text-xs px-2 py-1 rounded bg-red-700/60 hover:bg-red-700 text-white flex items-center gap-1"
                                title="Remove name from TV">
                            <XCircleIcon class="h-3.5 w-3.5" /> Clear
                        </button>
                    </div>

                    <!-- Device -->
                    <div :style="{ color: themeColors.textSecondary }" class="text-xs flex items-center gap-2">
                        <TvIcon class="h-4 w-4" />
                        <span v-if="room.device_name">{{ room.device_name }}</span>
                        <span v-else>No TV device assigned</span>
                        <span v-if="room.iptv_device_count > 1" class="text-yellow-400">+{{ room.iptv_device_count - 1 }} more</span>
                    </div>

                    <!-- Guest name input -->
                    <form @submit.prevent="setGuest(room)" class="flex gap-2 mt-auto">
                        <input v-model="guestNames[room.id]"
                               type="text"
                               :placeholder="room.guest_name ? 'Change guest name' : 'Guest name…'"
                               :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"
                               class="flex-1 px-3 py-1.5 rounded-lg border text-sm focus:outline-none" />
                        <button type="submit"
                                :disabled="processingRoom === room.id || !(guestNames[room.id] || '').trim()"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium bg-green-600 hover:bg-green-500 text-white disabled:opacity-50 flex items-center gap-1.5">
                            <CheckCircleIcon class="h-4 w-4" /> Show on TV
                        </button>
                    </form>
                </div>

                <div v-if="!rooms.length" :style="{ color: themeColors.textSecondary }"
                     class="col-span-full text-center text-sm py-8">
                    No rooms yet. Click “Add Room” to create one.
                </div>
            </div>
        </div>

        <!-- Devices attribution -->
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
             class="rounded-xl border overflow-hidden mb-6">
            <div :style="{ borderColor: themeColors.border }" class="px-5 py-3 border-b">
                <h2 :style="{ color: themeColors.textPrimary }" class="font-semibold flex items-center gap-2">
                    <TvIcon class="h-5 w-5 text-yellow-400" /> TV Devices
                </h2>
                <p :style="{ color: themeColors.textSecondary }" class="text-xs mt-0.5">
                    Assign each Android TV / IPTV device to a room so the room's guest shows on it.
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th :style="{ color: themeColors.textSecondary }" class="px-5 py-2.5 text-left text-xs font-medium">Device</th>
                            <th :style="{ color: themeColors.textSecondary }" class="px-5 py-2.5 text-left text-xs font-medium">Status</th>
                            <th :style="{ color: themeColors.textSecondary }" class="px-5 py-2.5 text-left text-xs font-medium">Room</th>
                            <th :style="{ color: themeColors.textSecondary }" class="px-5 py-2.5 text-right text-xs font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="device in devices" :key="device.id"
                            :style="{ borderColor: themeColors.border }" class="border-t">
                            <td :style="{ color: themeColors.textPrimary }" class="px-5 py-3 font-medium">
                                {{ device.device_name }}
                                <span :style="{ color: themeColors.textTertiary }" class="text-xs font-mono block">{{ device.device_id }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <span :class="statusBadgeClass(device.status)">{{ device.status }}</span>
                            </td>
                            <td :style="{ color: themeColors.textPrimary }" class="px-5 py-3">
                                <select v-model="deviceRooms[device.id]"
                                        :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"
                                        class="px-2 py-1 rounded-lg border text-sm">
                                    <option :value="null">Unassigned</option>
                                    <option v-for="r in rooms" :key="r.id" :value="r.id">Room {{ r.room_number }}</option>
                                </select>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <button @click="saveDeviceRoom(device)"
                                        :disabled="processingDevice === device.id"
                                        class="px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-600 hover:bg-blue-500 text-white disabled:opacity-50">
                                    {{ processingDevice === device.id ? 'Saving…' : 'Save' }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!devices.length">
                            <td :style="{ color: themeColors.textSecondary }" colspan="4" class="px-5 py-6 text-center text-sm">
                                No devices registered yet. They appear on the Devices page when an Android TV box connects.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Room modal -->
        <div v-if="showAddRoom" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
             @click.self="showAddRoom = false">
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                 class="rounded-xl border p-6 w-full max-w-md">
                <h3 :style="{ color: themeColors.textPrimary }" class="text-lg font-bold mb-4">Add Room</h3>
                <form @submit.prevent="createRoom">
                    <div class="mb-4">
                        <label :style="{ color: themeColors.textSecondary }" class="block text-xs font-medium mb-1">Room Number</label>
                        <input v-model="addRoom.room_number" type="text" required placeholder="e.g. 101"
                               :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"
                               class="w-full px-3 py-2 rounded-lg border text-sm focus:outline-none" />
                        <p v-if="addRoom.errors.room_number" :style="{ color: themeColors.danger }" class="text-xs mt-1">
                            {{ addRoom.errors.room_number }}
                        </p>
                    </div>
                    <div class="mb-5">
                        <label :style="{ color: themeColors.textSecondary }" class="block text-xs font-medium mb-1">Room Type (optional)</label>
                        <select v-model="addRoom.room_type_id"
                                :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"
                                class="w-full px-3 py-2 rounded-lg border text-sm">
                            <option value="">Default</option>
                            <option v-for="type in roomTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showAddRoom = false"
                                :style="{ borderColor: themeColors.border, color: themeColors.textSecondary }"
                                class="px-4 py-2 rounded-lg text-sm border">Cancel</button>
                        <button type="submit" :disabled="addRoom.processing"
                                class="px-4 py-2 rounded-lg text-sm font-medium bg-yellow-500 hover:bg-yellow-400 text-black disabled:opacity-50">
                            {{ addRoom.processing ? 'Creating…' : 'Create Room' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme'
import {
    TvIcon,
    CheckCircleIcon,
    XCircleIcon,
    PlusIcon,
    UserIcon,
    BuildingOfficeIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    navigation: Array,
    rooms: { type: Array, default: () => [] },
    devices: { type: Array, default: () => [] },
    roomTypes: { type: Array, default: () => [] },
    unassignedRooms: { type: Array, default: () => [] },
})

const themeColors = computed(() => ({
    background: 'var(--kotel-background)',
    card: 'var(--kotel-card)',
    border: 'var(--kotel-border)',
    textPrimary: 'var(--kotel-text-primary)',
    textSecondary: 'var(--kotel-text-secondary)',
    textTertiary: 'var(--kotel-text-tertiary)',
    success: 'var(--kotel-success)',
    danger: 'var(--kotel-danger)',
}))

const statCards = computed(() => [
    { label: 'Total Rooms', value: props.rooms.length, icon: BuildingOfficeIcon, color: 'var(--kotel-primary)' },
    { label: 'With Guest', value: props.rooms.filter(r => r.guest_name).length, icon: UserIcon, color: 'var(--kotel-success)' },
    { label: 'Available', value: props.rooms.filter(r => !r.guest_name).length, icon: CheckCircleIcon, color: '#22c55e' },
    { label: 'TV Devices', value: props.devices.length, icon: TvIcon, color: '#f59e0b' },
])

const guestNames = ref({})
const deviceRooms = ref({})
props.devices.forEach(d => { deviceRooms.value[d.id] = d.room_id || null })

const processingRoom = ref(null)
const processingDevice = ref(null)
const showAddRoom = ref(false)

const addRoom = useForm({ room_number: '', room_type_id: '' })

const setGuest = (room) => {
    const name = (guestNames.value[room.id] || '').trim()
    if (!name) return
    processingRoom.value = room.id
    router.post(route('lite.guests.store'), {
        room_id: room.id,
        first_name: name,
    }, {
        preserveScroll: true,
        onFinish: () => { processingRoom.value = null; guestNames.value[room.id] = '' },
    })
}

const checkout = (room) => {
    if (!confirm(`Remove ${room.guest_name} from Room ${room.room_number}'s TV?`)) return
    router.post(route('lite.guests.checkout'), {
        reservation_id: room.reservation_id,
    }, { preserveScroll: true })
}

const saveDeviceRoom = (device) => {
    processingDevice.value = device.id
    router.post(route('lite.devices.room', device.id), {
        room_id: deviceRooms.value[device.id] || null,
    }, { preserveScroll: true, onFinish: () => { processingDevice.value = null } })
}

const createRoom = () => {
    addRoom.post(route('lite.rooms.store'), {
        preserveScroll: true,
        onSuccess: () => {
            addRoom.reset()
            showAddRoom.value = false
        },
    })
}

const statusBadgeClass = (status) => ({
    online: 'px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-900/60 text-green-300',
    idle: 'px-2 py-0.5 rounded-full text-[10px] font-semibold bg-yellow-900/60 text-yellow-300',
    offline: 'px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-900/60 text-red-300',
}[status] || 'px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-900/60 text-gray-300')
</script>