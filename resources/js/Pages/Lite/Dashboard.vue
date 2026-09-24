<template>
    <DashboardLayout :user="user" :navigation="navigation">

        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success"
             class="mb-5 px-4 py-3 rounded-xl bg-emerald-900/40 border border-emerald-700/60 text-emerald-200 flex items-center gap-2.5 shadow-sm">
            <CheckCircleIcon class="h-5 w-5 flex-shrink-0 text-emerald-400" />
            <span class="text-sm font-medium">{{ $page.props.flash.success }}</span>
        </div>

        <!-- Hero header -->
        <div class="relative overflow-hidden rounded-2xl border mb-6 shadow-lg"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="absolute inset-0 opacity-[0.07] pointer-events-none"
                 style="background-image: radial-gradient(circle at 12% 20%, #f59e0b 0, transparent 40%), radial-gradient(circle at 88% 70%, #22c55e 0, transparent 42%);"></div>
            <div class="relative px-6 py-7 md:px-8 md:py-8 flex flex-col md:flex-row md:items-center md:justify-between gap-5">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-[11px] font-semibold uppercase tracking-wider mb-3"
                         :style="{ backgroundColor: 'rgba(245, 158, 11, 0.12)', color: '#f59e0b' }">
                        <TvIcon class="h-3.5 w-3.5" />
                        In-room TV welcome
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight flex items-center gap-3"
                        :style="{ color: themeColors.textPrimary }">
                        Guest Display
                    </h1>
                    <p class="mt-1.5 text-sm max-w-xl" :style="{ color: themeColors.textSecondary }">
                        Enter a guest name and send it straight to the room’s TV. The welcome screen updates within seconds.
                    </p>
                </div>

                <div class="flex flex-col gap-2 md:items-end shrink-0">
                    <div class="flex items-center gap-2 px-4 py-3 rounded-xl border"
                         :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                        <div class="text-right">
                            <p class="text-[10px] uppercase tracking-wider font-semibold" :style="{ color: themeColors.textTertiary }">Occupancy</p>
                            <p class="text-xl font-bold leading-tight" :style="{ color: themeColors.textPrimary }">
                                {{ occupancyPct }}%
                            </p>
                        </div>
                        <div class="w-20 h-2 rounded-full overflow-hidden" :style="{ backgroundColor: themeColors.border }">
                            <div class="h-full rounded-full transition-all duration-500"
                                 :style="{ width: occupancyPct + '%', backgroundColor: 'var(--kotel-success)' }"></div>
                        </div>
                    </div>
                    <button @click="showAddRoom = true"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-amber-500 hover:bg-amber-400 text-black shadow-sm transition">
                        <PlusIcon class="h-4 w-4" /> Add Room
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div v-for="stat in statCards" :key="stat.label"
                 class="rounded-2xl border p-4 flex items-center gap-3 shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"
                     :style="{ backgroundColor: stat.bg }">
                    <component :is="stat.icon" class="h-5 w-5" :style="{ color: stat.color }" />
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-medium uppercase tracking-wide truncate" :style="{ color: themeColors.textSecondary }">{{ stat.label }}</p>
                    <p class="text-2xl font-bold leading-tight" :style="{ color: themeColors.textPrimary }">{{ stat.value }}</p>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <div class="rounded-2xl border mb-6 shadow-sm"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-5 py-4 border-b flex flex-col lg:flex-row lg:items-center gap-3 justify-between"
                 :style="{ borderColor: themeColors.border }">
                <div class="flex items-center gap-2">
                    <BuildingOfficeIcon class="h-5 w-5 text-amber-500" />
                    <h2 class="font-semibold text-base" :style="{ color: themeColors.textPrimary }">Rooms</h2>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                          :style="{ backgroundColor: themeColors.background, color: themeColors.textSecondary }">
                        {{ filteredRooms.length }} shown
                    </span>
                </div>

                <div class="flex flex-col sm:flex-row gap-2.5 sm:items-center w-full lg:w-auto">
                    <div class="relative flex-1 sm:min-w-[220px]">
                        <MagnifyingGlassIcon class="h-4 w-4 absolute left-3 top-1/2 -translate-y-1/2"
                                             :style="{ color: themeColors.textTertiary }" />
                        <input v-model="search"
                               type="search"
                               placeholder="Search room, guest, or TV…"
                               class="w-full pl-9 pr-3 py-2.5 rounded-xl border text-sm outline-none focus:ring-2 focus:ring-amber-500/40"
                               :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }" />
                    </div>
                    <div class="flex gap-1.5 p-1 rounded-xl border"
                         :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                        <button v-for="f in filters" :key="f.key"
                                @click="statusFilter = f.key"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition"
                                :class="statusFilter === f.key
                                    ? 'bg-amber-500 text-black shadow-sm'
                                    : ''
                                "
                                :style="statusFilter === f.key ? {} : { color: themeColors.textSecondary }">
                            {{ f.label }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Rooms grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 p-5">
                <div v-for="room in filteredRooms" :key="room.id"
                     class="rounded-2xl border p-4 flex flex-col gap-3 transition hover:shadow-md"
                     :style="{
                         backgroundColor: themeColors.background,
                         borderColor: room.guest_name ? 'rgba(34,197,94,0.45)' : themeColors.border,
                         boxShadow: room.guest_name ? '0 0 0 1px rgba(34,197,94,0.08)' : 'none',
                     }">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="font-bold text-lg leading-tight tracking-tight" :style="{ color: themeColors.textPrimary }">
                                Room {{ room.room_number }}
                            </p>
                            <p class="text-xs mt-0.5" :style="{ color: themeColors.textSecondary }">{{ room.room_type }}</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide shrink-0"
                              :class="room.guest_name
                                  ? 'bg-emerald-900/50 text-emerald-300'
                                  : 'bg-gray-800/50 text-gray-300'">
                            {{ room.guest_name ? 'Occupied' : 'Available' }}
                        </span>
                    </div>

                    <!-- Current guest -->
                    <div v-if="room.guest_name"
                         class="rounded-xl px-3 py-2.5 flex items-center justify-between gap-2"
                         style="background: linear-gradient(135deg, rgba(34,197,94,0.14), rgba(34,197,94,0.05)); border: 1px solid rgba(34,197,94,0.25);">
                        <span class="flex items-center gap-2 text-sm font-semibold truncate" :style="{ color: themeColors.success }">
                            <UserCircleIcon class="h-5 w-5 shrink-0" />
                            <span class="truncate">{{ room.guest_name }}</span>
                        </span>
                        <button @click="confirmCheckout(room)"
                                class="shrink-0 text-xs px-2.5 py-1 rounded-lg bg-red-800/50 hover:bg-red-700 text-white flex items-center gap-1 font-medium transition"
                                title="Remove name from TV">
                            <XCircleIcon class="h-3.5 w-3.5" /> Clear
                        </button>
                    </div>
                    <div v-else
                         class="rounded-xl px-3 py-2.5 flex items-center gap-2 text-xs font-medium border border-dashed"
                         :style="{ borderColor: themeColors.border, color: themeColors.textTertiary, backgroundColor: themeColors.card }">
                        <SparklesIcon class="h-4 w-4 text-amber-500/80" />
                        Ready for a guest name — it will appear on the TV welcome screen.
                    </div>

                    <!-- Device -->
                    <div class="flex items-center gap-2 text-xs" :style="{ color: themeColors.textSecondary }">
                        <TvIcon class="h-4 w-4 shrink-0" :class="room.device_name ? 'text-amber-500' : 'opacity-40'" />
                        <span v-if="room.device_name" class="truncate">{{ room.device_name }}</span>
                        <span v-else>No TV device assigned</span>
                        <span v-if="room.iptv_device_count > 1"
                              class="ml-auto shrink-0 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-900/40 text-amber-300">
                            +{{ room.iptv_device_count - 1 }} more
                        </span>
                    </div>

                    <!-- Guest name input -->
                    <form @submit.prevent="setGuest(room)" class="flex gap-2 mt-auto pt-1">
                        <label class="sr-only" :for="'guest-name-' + room.id">Guest name for room {{ room.room_number }}</label>
                        <input :id="'guest-name-' + room.id"
                               v-model="guestNames[room.id]"
                               type="text"
                               autocomplete="off"
                               :placeholder="room.guest_name ? 'New guest name…' : 'Full guest name…'"
                               class="flex-1 min-w-0 px-3.5 py-2.5 rounded-xl border text-sm font-medium outline-none focus:ring-2 focus:ring-amber-500/40"
                               :style="{ backgroundColor: themeColors.card, color: themeColors.textPrimary, borderColor: themeColors.border }" />
                        <button type="submit"
                                :disabled="processingRoom === room.id || !(guestNames[room.id] || '').trim()"
                                class="shrink-0 px-3.5 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white disabled:opacity-40 flex items-center gap-1.5 transition shadow-sm">
                            <CheckCircleIcon class="h-4 w-4" />
                            <span class="hidden sm:inline">{{ processingRoom === room.id ? 'Sending…' : 'Show' }}</span>
                            <span class="sm:hidden">Show</span>
                        </button>
                    </form>
                </div>

                <div v-if="!filteredRooms.length" class="col-span-full text-center text-sm py-12 px-4">
                    <BuildingOfficeIcon class="h-10 w-10 mx-auto mb-3 opacity-40" :style="{ color: themeColors.textTertiary }" />
                    <p :style="{ color: themeColors.textSecondary }" class="font-medium">
                        {{ rooms.length ? 'No rooms match your search.' : 'No rooms yet.' }}
                    </p>
                    <p v-if="!rooms.length" :style="{ color: themeColors.textTertiary }" class="text-xs mt-1">
                        Click “Add Room” to create your first room.
                    </p>
                </div>
            </div>
        </div>

        <!-- Devices -->
        <div class="rounded-2xl border overflow-hidden mb-6 shadow-sm"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-5 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h2 class="font-semibold flex items-center gap-2" :style="{ color: themeColors.textPrimary }">
                    <TvIcon class="h-5 w-5 text-amber-500" /> TV Devices
                </h2>
                <p class="text-xs mt-1" :style="{ color: themeColors.textSecondary }">
                    Assign each Android TV / IPTV box to a room so the guest name appears on the right screen.
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th :style="{ color: themeColors.textSecondary }" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide">Device</th>
                            <th :style="{ color: themeColors.textSecondary }" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide">Status</th>
                            <th :style="{ color: themeColors.textSecondary }" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide">Room</th>
                            <th :style="{ color: themeColors.textSecondary }" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="device in devices" :key="device.id"
                            :style="{ borderColor: themeColors.border }" class="border-t">
                            <td class="px-5 py-3.5 font-medium" :style="{ color: themeColors.textPrimary }">
                                {{ device.device_name }}
                                <span class="text-xs font-mono block" :style="{ color: themeColors.textTertiary }">{{ device.device_id }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span :class="statusBadgeClass(device.status)">{{ device.status }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <select v-model="deviceRooms[device.id]"
                                        class="px-3 py-1.5 rounded-xl border text-sm outline-none focus:ring-2 focus:ring-amber-500/40"
                                        :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }">
                                    <option :value="null">Unassigned</option>
                                    <option v-for="r in rooms" :key="r.id" :value="r.id">Room {{ r.room_number }}</option>
                                </select>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <button @click="saveDeviceRoom(device)"
                                        :disabled="processingDevice === device.id"
                                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-sky-600 hover:bg-sky-500 text-white disabled:opacity-50 transition">
                                    {{ processingDevice === device.id ? 'Saving…' : 'Save' }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!devices.length">
                            <td class="px-5 py-8 text-center text-sm" :style="{ color: themeColors.textSecondary }" colspan="4">
                                No devices registered yet. They appear here when an Android TV box connects.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Room modal -->
        <div v-if="showAddRoom" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
             @click.self="showAddRoom = false">
            <div class="rounded-2xl border p-6 w-full max-w-md shadow-2xl"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/15 flex items-center justify-center">
                        <PlusIcon class="h-5 w-5 text-amber-500" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold" :style="{ color: themeColors.textPrimary }">Add Room</h3>
                        <p class="text-xs" :style="{ color: themeColors.textSecondary }">Create a room so it can show a guest name.</p>
                    </div>
                </div>
                <form @submit.prevent="createRoom">
                    <div class="mb-4">
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Room Number</label>
                        <input v-model="addRoom.room_number" type="text" required placeholder="e.g. 101"
                               class="w-full px-3.5 py-2.5 rounded-xl border text-sm outline-none focus:ring-2 focus:ring-amber-500/40"
                               :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }" />
                        <p v-if="addRoom.errors.room_number" class="text-xs mt-1.5" :style="{ color: themeColors.danger }">
                            {{ addRoom.errors.room_number }}
                        </p>
                    </div>
                    <div class="mb-5">
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">Room Type (optional)</label>
                        <select v-model="addRoom.room_type_id"
                                class="w-full px-3.5 py-2.5 rounded-xl border text-sm"
                                :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }">
                            <option value="">Default</option>
                            <option v-for="type in roomTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showAddRoom = false"
                                class="px-4 py-2.5 rounded-xl text-sm border font-medium"
                                :style="{ borderColor: themeColors.border, color: themeColors.textSecondary }">
                            Cancel
                        </button>
                        <button type="submit" :disabled="addRoom.processing"
                                class="px-4 py-2.5 rounded-xl text-sm font-bold bg-amber-500 hover:bg-amber-400 text-black disabled:opacity-50 transition">
                            {{ addRoom.processing ? 'Creating…' : 'Create Room' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Checkout confirm modal -->
        <div v-if="checkoutRoom" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
             @click.self="checkoutRoom = null">
            <div class="rounded-2xl border p-6 w-full max-w-sm shadow-2xl"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="w-11 h-11 rounded-xl bg-red-900/40 flex items-center justify-center mb-4">
                    <XCircleIcon class="h-6 w-6 text-red-400" />
                </div>
                <h3 class="text-lg font-bold mb-1" :style="{ color: themeColors.textPrimary }">Clear guest name?</h3>
                <p class="text-sm mb-5" :style="{ color: themeColors.textSecondary }">
                    Remove <strong :style="{ color: themeColors.textPrimary }">{{ checkoutRoom.guest_name }}</strong>
                    from Room {{ checkoutRoom.room_number }}’s TV welcome screen?
                </p>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="checkoutRoom = null"
                            class="px-4 py-2.5 rounded-xl text-sm border font-medium"
                            :style="{ borderColor: themeColors.border, color: themeColors.textSecondary }">
                        Keep
                    </button>
                    <button type="button" @click="doCheckout"
                            class="px-4 py-2.5 rounded-xl text-sm font-bold bg-red-600 hover:bg-red-500 text-white transition">
                        Clear on TV
                    </button>
                </div>
            </div>
        </div>

    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {
    TvIcon,
    CheckCircleIcon,
    XCircleIcon,
    PlusIcon,
    UserCircleIcon,
    BuildingOfficeIcon,
    MagnifyingGlassIcon,
    SparklesIcon,
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

const occupiedCount = computed(() => props.rooms.filter(r => r.guest_name).length)
const availableCount = computed(() => props.rooms.filter(r => !r.guest_name).length)
const linkedDeviceCount = computed(() => props.devices.filter(d => d.room_id).length)
const occupancyPct = computed(() =>
    props.rooms.length ? Math.round((occupiedCount.value / props.rooms.length) * 100) : 0
)

const statCards = computed(() => [
    {
        label: 'Total Rooms',
        value: props.rooms.length,
        icon: BuildingOfficeIcon,
        color: '#f59e0b',
        bg: 'rgba(245, 158, 11, 0.12)',
    },
    {
        label: 'With Guest',
        value: occupiedCount.value,
        icon: UserCircleIcon,
        color: '#22c55e',
        bg: 'rgba(34, 197, 94, 0.12)',
    },
    {
        label: 'Available',
        value: availableCount.value,
        icon: SparklesIcon,
        color: '#38bdf8',
        bg: 'rgba(56, 189, 248, 0.12)',
    },
    {
        label: 'TV Linked',
        value: linkedDeviceCount.value,
        icon: TvIcon,
        color: '#f59e0b',
        bg: 'rgba(245, 158, 11, 0.12)',
    },
])

const search = ref('')
const statusFilter = ref('all')
const filters = [
    { key: 'all', label: 'All' },
    { key: 'occupied', label: 'Occupied' },
    { key: 'available', label: 'Available' },
]

const filteredRooms = computed(() => {
    const q = search.value.trim().toLowerCase()
    return props.rooms.filter((room) => {
        if (statusFilter.value === 'occupied' && !room.guest_name) return false
        if (statusFilter.value === 'available' && room.guest_name) return false
        if (!q) return true
        const haystack = [
            room.room_number,
            room.room_type,
            room.guest_name || '',
            room.device_name || '',
        ].join(' ').toLowerCase()
        return haystack.includes(q)
    })
})

const guestNames = ref({})
const deviceRooms = ref({})
props.devices.forEach(d => { deviceRooms.value[d.id] = d.room_id || null })

const processingRoom = ref(null)
const processingDevice = ref(null)
const showAddRoom = ref(false)
const checkoutRoom = ref(null)

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

const confirmCheckout = (room) => {
    checkoutRoom.value = room
}

const doCheckout = () => {
    const room = checkoutRoom.value
    if (!room) return
    checkoutRoom.value = null
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
    online: 'px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-900/50 text-emerald-300',
    idle: 'px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-900/50 text-amber-300',
    offline: 'px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-900/50 text-red-300',
}[status] || 'px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-800/50 text-gray-300')
</script>
