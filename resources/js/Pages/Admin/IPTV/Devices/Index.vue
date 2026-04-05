<template>
    <DashboardLayout title="Android TV Device Management" :user="user" :navigation="navigation">

        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success" class="mb-4 px-4 py-3 rounded-lg bg-green-900/50 border border-green-700 text-green-300 flex items-center gap-2">
            <CheckCircleIcon class="h-5 w-5 flex-shrink-0" />
            <span>{{ $page.props.flash.success }}</span>
        </div>
        <div v-if="$page.props.flash?.newToken" class="mb-4 px-4 py-3 rounded-lg bg-blue-900/50 border border-blue-700 text-blue-300">
            <p class="font-semibold mb-1">🎉 Device Registered! Share this registration token with the device:</p>
            <div class="flex items-center gap-3 mt-2">
                <code class="bg-black/40 px-3 py-2 rounded font-mono text-lg text-yellow-300 tracking-widest select-all">{{ $page.props.flash.newToken }}</code>
                <button @click="copyToken($page.props.flash.newToken)" class="text-xs px-2 py-1 bg-blue-700 hover:bg-blue-600 rounded text-white">Copy</button>
            </div>
            <p class="text-xs mt-2 text-blue-400">Server URL: <strong class="text-white">{{ serverUrl }}</strong></p>
        </div>

        <!-- Page Header -->
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
             class="shadow rounded-xl p-6 mb-6 border">
            <div class="flex items-center justify-between">
                <div>
                    <h1 :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold flex items-center gap-3">
                        <TvIcon class="h-7 w-7 text-yellow-400" />
                        Android TV Device Management
                    </h1>
                    <p :style="{ color: themeColors.textSecondary }" class="mt-1 text-sm">
                        Real-time monitoring &amp; remote control for all Android TV / IPTV boxes
                        <span class="ml-2 text-xs text-green-400">● Auto-refreshes every 15s</span>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="pushSettingsAll"
                            :disabled="pushingAll"
                            class="px-4 py-2 rounded-lg text-sm font-medium bg-purple-600 hover:bg-purple-500 text-white disabled:opacity-50 flex items-center gap-2">
                        <ArrowUpCircleIcon class="h-4 w-4" />
                        {{ pushingAll ? 'Pushing...' : 'Push Settings to All' }}
                    </button>
                    <button @click="showAddModal = true"
                            class="px-4 py-2 rounded-lg text-sm font-medium bg-yellow-500 hover:bg-yellow-400 text-black flex items-center gap-2">
                        <PlusIcon class="h-4 w-4" />
                        Add Device
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
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

        <!-- Main Content: Table + Side Panel -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            <!-- Devices Table -->
            <div class="xl:col-span-2">
                <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                     class="rounded-xl border overflow-hidden">
                    <!-- Table toolbar -->
                    <div :style="{ borderColor: themeColors.border }" class="px-5 py-3 border-b flex items-center gap-3">
                        <div class="relative flex-1">
                            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <input v-model="search" placeholder="Search devices, rooms, IP..."
                                   :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"
                                   class="w-full pl-9 pr-3 py-2 rounded-lg border text-sm focus:outline-none focus:ring-1 focus:ring-yellow-500" />
                        </div>
                        <select v-model="filterStatus"
                                :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"
                                class="border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-yellow-500">
                            <option value="">All Status</option>
                            <option value="online">Online</option>
                            <option value="idle">Idle</option>
                            <option value="offline">Offline</option>
                        </select>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead :style="{ backgroundColor: themeColors.background }">
                                <tr>
                                    <th v-for="col in ['Device', 'Room', 'Status', 'Last Seen', 'Version', 'Actions']" :key="col"
                                        :style="{ color: themeColors.textSecondary }"
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">{{ col }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="filteredDevices.length === 0">
                                    <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                                        No devices found. Add your first Android TV device to get started.
                                    </td>
                                </tr>
                                <tr v-for="device in filteredDevices" :key="device.id"
                                    :style="selectedDevice?.id === device.id ? { backgroundColor: 'rgba(234,179,8,0.08)' } : {}"
                                    @click="selectDevice(device)"
                                    class="cursor-pointer transition-colors hover:bg-white/5 border-b border-white/5">
                                    <!-- Device -->
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div :class="statusDotClass(device.computed_status)"
                                                 class="w-2.5 h-2.5 rounded-full flex-shrink-0"></div>
                                            <div>
                                                <p :style="{ color: themeColors.textPrimary }" class="font-medium">
                                                    {{ device.device_name || device.device_id }}
                                                </p>
                                                <p :style="{ color: themeColors.textTertiary }" class="text-xs font-mono">
                                                    {{ device.device_id }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- Room -->
                                    <td class="px-4 py-3">
                                        <span :style="{ color: themeColors.textPrimary }">
                                            {{ device.room_number ? 'Room ' + device.room_number : '—' }}
                                        </span>
                                    </td>
                                    <!-- Status -->
                                    <td class="px-4 py-3">
                                        <span :class="statusBadgeClass(device.computed_status)"
                                              class="px-2 py-0.5 rounded-full text-xs font-semibold capitalize">
                                            {{ device.computed_status || 'offline' }}
                                        </span>
                                    </td>
                                    <!-- Last seen -->
                                    <td class="px-4 py-3">
                                        <span :style="{ color: themeColors.textSecondary }">
                                            {{ ago(device.last_heartbeat) }}
                                        </span>
                                    </td>
                                    <!-- Version -->
                                    <td class="px-4 py-3">
                                        <span :style="{ color: themeColors.textSecondary }" class="text-xs">
                                            {{ device.app_version || '—' }}
                                        </span>
                                    </td>
                                    <!-- Actions -->
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <Link :href="route('admin.iptv.devices.show', device.id)"
                                                  class="text-xs px-2 py-1 rounded bg-yellow-500/20 text-yellow-400 hover:bg-yellow-500/30">
                                                Manage
                                            </Link>
                                            <button @click.stop="quickCommand(device, 'reboot')"
                                                    class="text-xs px-2 py-1 rounded bg-blue-500/20 text-blue-400 hover:bg-blue-500/30">
                                                Reboot
                                            </button>
                                            <button @click.stop="confirmDelete(device)"
                                                    class="text-xs px-2 py-1 rounded bg-red-500/20 text-red-400 hover:bg-red-500/30">
                                                ✕
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Side Panel: Quick Actions on selected device -->
            <div class="space-y-4">
                <!-- Quick Command Panel -->
                <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                     class="rounded-xl border p-5">
                    <h3 :style="{ color: themeColors.textPrimary }" class="font-semibold mb-3 flex items-center gap-2">
                        <CommandLineIcon class="h-4 w-4 text-yellow-400" />
                        Quick Command
                    </h3>
                    <div v-if="!selectedDevice" :style="{ color: themeColors.textSecondary }" class="text-sm text-center py-4">
                        Click a device in the table to select it
                    </div>
                    <div v-else>
                        <p :style="{ color: themeColors.textSecondary }" class="text-xs mb-3">
                            Target: <strong :style="{ color: themeColors.textPrimary }">{{ selectedDevice.device_name || selectedDevice.device_id }}</strong>
                        </p>
                        <div class="grid grid-cols-2 gap-2">
                            <button v-for="cmd in quickCommands" :key="cmd.type"
                                    @click="quickCommand(selectedDevice, cmd.type)"
                                    :class="cmd.cls"
                                    class="text-xs py-2 px-3 rounded-lg font-medium flex items-center gap-1.5 justify-center">
                                <component :is="cmd.icon" class="h-3.5 w-3.5" />
                                {{ cmd.label }}
                            </button>
                        </div>
                        <!-- Message command -->
                        <div class="mt-3 space-y-2">
                            <input v-model="messageText" placeholder="Send a message to TV..."
                                   :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"
                                   class="w-full px-3 py-2 rounded-lg border text-sm focus:outline-none focus:ring-1 focus:ring-yellow-500" />
                            <button @click="sendMessage" :disabled="!messageText.trim()"
                                    class="w-full py-2 rounded-lg text-sm font-medium bg-yellow-500/20 text-yellow-400 hover:bg-yellow-500/30 disabled:opacity-40">
                                Send Message
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Push Settings to All Panel -->
                <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                     class="rounded-xl border p-5">
                    <h3 :style="{ color: themeColors.textPrimary }" class="font-semibold mb-3 flex items-center gap-2">
                        <Cog6ToothIcon class="h-4 w-4 text-purple-400" />
                        Global Settings Push
                    </h3>
                    <p :style="{ color: themeColors.textSecondary }" class="text-xs mb-4">
                        Override IPTV settings for all active devices simultaneously.
                    </p>
                    <div class="space-y-3">
                        <div>
                            <label :style="{ color: themeColors.textSecondary }" class="block text-xs mb-1">Xtream Server URL</label>
                            <input v-model="globalSettings.xtream_url"
                                   :placeholder="globalSettingsProp.xtream_url || 'http://server:port'"
                                   :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"
                                   class="w-full px-3 py-2 rounded-lg border text-sm focus:outline-none" />
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label :style="{ color: themeColors.textSecondary }" class="block text-xs mb-1">Username</label>
                                <input v-model="globalSettings.xtream_username"
                                       :placeholder="globalSettingsProp.xtream_username || ''"
                                       :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"
                                       class="w-full px-3 py-2 rounded-lg border text-sm focus:outline-none" />
                            </div>
                            <div>
                                <label :style="{ color: themeColors.textSecondary }" class="block text-xs mb-1">Password</label>
                                <input v-model="globalSettings.xtream_password" type="password"
                                       :placeholder="globalSettingsProp.xtream_password ? '••••••' : ''"
                                       :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"
                                       class="w-full px-3 py-2 rounded-lg border text-sm focus:outline-none" />
                            </div>
                        </div>
                        <div>
                            <label :style="{ color: themeColors.textSecondary }" class="block text-xs mb-1">Admin PIN</label>
                            <input v-model="globalSettings.admin_pin" maxlength="6"
                                   :placeholder="globalSettingsProp.admin_pin || '1234'"
                                   :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"
                                   class="w-full px-3 py-2 rounded-lg border text-sm focus:outline-none" />
                        </div>
                        <button @click="pushSettingsAll" :disabled="pushingAll"
                                class="w-full py-2.5 rounded-lg text-sm font-semibold bg-purple-600 hover:bg-purple-500 text-white disabled:opacity-50">
                            {{ pushingAll ? 'Pushing to all devices...' : '⬆ Push to All Devices' }}
                        </button>
                    </div>
                </div>

                <!-- Server URL Info Box -->
                <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                     class="rounded-xl border p-5">
                    <h3 :style="{ color: themeColors.textPrimary }" class="font-semibold mb-3 flex items-center gap-2">
                        <ServerIcon class="h-4 w-4 text-green-400" />
                        Connection Info
                    </h3>
                    <p :style="{ color: themeColors.textSecondary }" class="text-xs mb-2">
                        Enter this URL into the Android TV app to register it with this server:
                    </p>
                    <div class="flex items-center gap-2">
                        <code :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary }"
                              class="flex-1 px-3 py-2 rounded-lg text-xs font-mono break-all">{{ serverUrl }}</code>
                        <button @click="copyToken(serverUrl)" class="text-xs px-2 py-1.5 bg-green-500/20 text-green-400 hover:bg-green-500/30 rounded-lg flex-shrink-0">
                            Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Device Modal -->
        <Teleport to="body">
            <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
                <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                     class="w-full max-w-lg mx-4 rounded-xl border shadow-2xl">
                    <div :style="{ borderColor: themeColors.border }" class="flex items-center justify-between px-6 py-4 border-b">
                        <h2 :style="{ color: themeColors.textPrimary }" class="text-lg font-bold">Add Android TV Device</h2>
                        <button @click="showAddModal = false" class="text-gray-400 hover:text-white">✕</button>
                    </div>
                    <form @submit.prevent="submitAdd" class="p-6 space-y-4">
                        <div class="bg-blue-900/30 border border-blue-700 rounded-lg p-3 text-xs text-blue-300">
                            <strong>How to add a device:</strong> Enter the <strong>Server URL</strong> shown in the Connection Info box into the IPTV app on your Android TV. The app will register automatically and appear here. You can also pre-register manually below.
                        </div>
                        <div>
                            <label :style="{ color: themeColors.textSecondary }" class="block text-sm mb-1">Device Name</label>
                            <input v-model="addForm.device_name" placeholder="e.g., Room 101 TV"
                                   :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"
                                   class="w-full px-3 py-2 rounded-lg border text-sm focus:outline-none focus:ring-1 focus:ring-yellow-500" />
                        </div>
                        <div>
                            <label :style="{ color: themeColors.textSecondary }" class="block text-sm mb-1">Device ID (Android device ID)</label>
                            <input v-model="addForm.device_id" required placeholder="e.g., abc123def456"
                                   :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"
                                   class="w-full px-3 py-2 rounded-lg border text-sm focus:outline-none focus:ring-1 focus:ring-yellow-500" />
                            <p :style="{ color: themeColors.textSecondary }" class="text-xs mt-1">Found in the app's "Server Setup" screen or Android Settings → About</p>
                        </div>
                        <div>
                            <label :style="{ color: themeColors.textSecondary }" class="block text-sm mb-1">Assign to Room</label>
                            <select v-model="addForm.room_id"
                                    :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"
                                    class="w-full px-3 py-2 rounded-lg border text-sm focus:outline-none focus:ring-1 focus:ring-yellow-500">
                                <option value="">— No room (assign later) —</option>
                                <option v-for="room in availableRooms" :key="room.id" :value="room.id">
                                    Room {{ room.room_number }}{{ room.room_type ? ' — ' + room.room_type : '' }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label :style="{ color: themeColors.textSecondary }" class="block text-sm mb-1">Device Type</label>
                            <select v-model="addForm.device_type"
                                    :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"
                                    class="w-full px-3 py-2 rounded-lg border text-sm focus:outline-none focus:ring-1 focus:ring-yellow-500">
                                <option value="android_tv">Android TV</option>
                                <option value="android_box">Android Box</option>
                                <option value="fire_stick">Fire Stick</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button type="button" @click="showAddModal = false"
                                    class="px-5 py-2 rounded-lg text-sm bg-gray-700 hover:bg-gray-600 text-white">
                                Cancel
                            </button>
                            <button type="submit" :disabled="addForm.processing"
                                    class="px-5 py-2 rounded-lg text-sm font-semibold bg-yellow-500 hover:bg-yellow-400 text-black disabled:opacity-50">
                                {{ addForm.processing ? 'Adding...' : 'Add Device' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router, useForm, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'
import {
    TvIcon,
    CheckCircleIcon,
    XCircleIcon,
    ExclamationTriangleIcon,
    ClockIcon,
    MagnifyingGlassIcon,
    PlusIcon,
    CommandLineIcon,
    Cog6ToothIcon,
    ServerIcon,
    ArrowUpCircleIcon,
    ArrowPathIcon,
    ArrowDownTrayIcon,
    LockClosedIcon,
    LockOpenIcon,
    ChatBubbleBottomCenterTextIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    devices: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
    availableRooms: { type: Array, default: () => [] },
    globalSettings: { type: Object, default: () => ({}) },
    serverUrl: { type: String, default: '' },
})

const { currentTheme } = useTheme()
const navigation = computed(() => getNavigationForRole('admin'))

const themeColors = computed(() => ({
    background: 'var(--kotel-background)',
    card: 'var(--kotel-card)',
    border: 'var(--kotel-border)',
    textPrimary: 'var(--kotel-text-primary)',
    textSecondary: 'var(--kotel-text-secondary)',
    textTertiary: 'var(--kotel-text-tertiary)',
    primary: 'var(--kotel-primary)',
    success: 'var(--kotel-success)',
    warning: 'var(--kotel-warning)',
    danger: 'var(--kotel-danger)',
}))

// Live device list (refreshed via polling)
const liveDevices = ref([...(props.devices || [])])
const globalSettingsProp = computed(() => props.globalSettings || {})
const serverUrl = computed(() => props.serverUrl || window.location.origin)

// Stats
const statCards = computed(() => [
    { label: 'Total', value: liveDevices.value.length, icon: TvIcon, color: 'var(--kotel-primary)' },
    { label: 'Online', value: liveDevices.value.filter(d => d.computed_status === 'online').length, icon: CheckCircleIcon, color: '#22c55e' },
    { label: 'Idle', value: liveDevices.value.filter(d => d.computed_status === 'idle').length, icon: ClockIcon, color: '#f59e0b' },
    { label: 'Offline', value: liveDevices.value.filter(d => d.computed_status === 'offline').length, icon: XCircleIcon, color: '#ef4444' },
    { label: 'Issues', value: props.stats?.issues ?? 0, icon: ExclamationTriangleIcon, color: '#f97316' },
])

// Filtering
const search = ref('')
const filterStatus = ref('')

const filteredDevices = computed(() =>
    liveDevices.value.filter(d => {
        const q = search.value.toLowerCase()
        const matchSearch = !q ||
            (d.device_id || '').toLowerCase().includes(q) ||
            (d.device_name || '').toLowerCase().includes(q) ||
            (d.room_number || '').toString().includes(q) ||
            (d.ip_address || '').includes(q)
        const matchStatus = !filterStatus.value || d.computed_status === filterStatus.value
        return matchSearch && matchStatus
    })
)

// Selected device for quick command panel
const selectedDevice = ref(null)
const selectDevice = (device) => {
    selectedDevice.value = selectedDevice.value?.id === device.id ? null : device
}

// Status helpers
const statusDotClass = (status) => ({
    'bg-green-400 animate-pulse': status === 'online',
    'bg-yellow-400': status === 'idle',
    'bg-red-400': status === 'offline' || !status,
}[Object.keys({ 'bg-green-400 animate-pulse': status === 'online', 'bg-yellow-400': status === 'idle', 'bg-red-400': status === 'offline' || !status }).find(k => ({ 'bg-green-400 animate-pulse': status === 'online', 'bg-yellow-400': status === 'idle', 'bg-red-400': status === 'offline' || !status })[k])] || 'bg-gray-400')

const statusBadgeClass = (status) => {
    if (status === 'online') return 'bg-green-500/20 text-green-400'
    if (status === 'idle') return 'bg-yellow-500/20 text-yellow-400'
    return 'bg-red-500/20 text-red-400'
}

const ago = (dt) => {
    if (!dt) return 'Never'
    const diff = Date.now() - new Date(dt).getTime()
    const mins = Math.floor(diff / 60000)
    if (mins < 1) return 'Just now'
    if (mins < 60) return `${mins}m ago`
    const hrs = Math.floor(mins / 60)
    if (hrs < 24) return `${hrs}h ago`
    return `${Math.floor(hrs / 24)}d ago`
}

// Quick commands
const quickCommands = [
    { type: 'refresh_channels', label: 'Refresh Channels', icon: ArrowPathIcon, cls: 'bg-blue-500/20 text-blue-400 hover:bg-blue-500/30' },
    { type: 'reload_app', label: 'Reload App', icon: ArrowDownTrayIcon, cls: 'bg-cyan-500/20 text-cyan-400 hover:bg-cyan-500/30' },
    { type: 'lock', label: 'Lock Screen', icon: LockClosedIcon, cls: 'bg-orange-500/20 text-orange-400 hover:bg-orange-500/30' },
    { type: 'unlock', label: 'Unlock', icon: LockOpenIcon, cls: 'bg-green-500/20 text-green-400 hover:bg-green-500/30' },
    { type: 'reboot', label: 'Reboot', icon: ArrowPathIcon, cls: 'bg-red-500/20 text-red-400 hover:bg-red-500/30' },
    { type: 'push_settings', label: 'Push Settings', icon: ArrowUpCircleIcon, cls: 'bg-purple-500/20 text-purple-400 hover:bg-purple-500/30' },
]

const messageText = ref('')

const quickCommand = (device, type) => {
    if (!device) return
    if (type === 'reboot' && !confirm(`Reboot ${device.device_name || device.device_id}?`)) return
    router.post(route('admin.iptv.devices.command', device.id), { type }, {
        preserveScroll: true,
        onSuccess: () => {
            if (type === 'reboot') alert('Reboot command queued. Device will restart on next heartbeat.')
        }
    })
}

const sendMessage = () => {
    if (!selectedDevice.value || !messageText.value.trim()) return
    router.post(route('admin.iptv.devices.command', selectedDevice.value.id), {
        type: 'message',
        payload: { text: messageText.value.trim() }
    }, {
        preserveScroll: true,
        onSuccess: () => { messageText.value = '' }
    })
}

// Add device
const showAddModal = ref(false)
const addForm = useForm({
    device_name: '',
    device_id: '',
    room_id: '',
    device_type: 'android_tv',
})

const submitAdd = () => {
    addForm.post(route('admin.iptv.devices.store'), {
        onSuccess: () => { showAddModal.value = false; addForm.reset() }
    })
}

// Delete
const confirmDelete = (device) => {
    if (!confirm(`Remove device "${device.device_name || device.device_id}"? This cannot be undone.`)) return
    router.delete(route('admin.iptv.devices.destroy', device.id), { preserveScroll: true })
}

// Push settings to all
const pushingAll = ref(false)
const globalSettings = ref({
    xtream_url: '',
    xtream_username: '',
    xtream_password: '',
    admin_pin: '',
})

const pushSettingsAll = () => {
    if (!confirm('Push these settings to ALL active devices?')) return
    pushingAll.value = true
    router.post(route('admin.iptv.devices.push-all'), globalSettings.value, {
        preserveScroll: true,
        onFinish: () => { pushingAll.value = false }
    })
}

// Copy to clipboard
const copyToken = (text) => {
    navigator.clipboard.writeText(text).then(() => alert('Copied to clipboard!'))
}

// Auto-refresh every 15 seconds
let pollTimer = null
const startPolling = () => {
    pollTimer = setInterval(async () => {
        try {
            const res = await fetch(route('admin.iptv.devices.status'), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            if (res.ok) {
                const data = await res.json()
                if (data.devices) {
                    // Merge live status fields into full device objects (preserves name, room, etc.)
                    const statusMap = {}
                    data.devices.forEach(d => { statusMap[d.id] = d })
                    liveDevices.value = liveDevices.value.map(dev => {
                        const live = statusMap[dev.id]
                        return live ? { ...dev, ...live } : dev
                    })
                }
            }
        } catch (_) { /* silent */ }
    }, 15000)
}

onMounted(startPolling)
onUnmounted(() => clearInterval(pollTimer))
</script>
