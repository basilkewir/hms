<template>
    <DashboardLayout :title="liveDevice.device_name || liveDevice.device_id" :user="user" :navigation="navigation">

        <!-- Flash -->
        <div v-if="$page.props.flash?.success"
             class="mb-4 px-4 py-3 rounded-lg bg-green-900/50 border border-green-700 text-green-300 flex items-center gap-2">
            <CheckCircleIcon class="h-5 w-5 flex-shrink-0" />
            <span>{{ $page.props.flash.success }}</span>
        </div>

        <!-- New token after regeneration -->
        <div v-if="$page.props.flash?.newToken"
             class="mb-4 px-4 py-3 rounded-lg bg-blue-900/50 border border-blue-700 text-blue-300">
            <p class="font-semibold">New Registration Token:</p>
            <div class="flex items-center gap-3 mt-2">
                <code class="bg-black/40 px-3 py-2 rounded font-mono text-yellow-300 tracking-widest select-all">
                    {{ $page.props.flash.newToken }}
                </code>
                <button @click="copy($page.props.flash.newToken)"
                        class="text-xs px-2 py-1 bg-blue-700 hover:bg-blue-600 rounded text-white">Copy</button>
            </div>
        </div>

        <!-- Back + breadcrumb -->
        <div class="flex items-center gap-2 mb-6 text-sm">
            <Link :href="route('admin.iptv.devices.index')"
                  class="text-yellow-400 hover:text-yellow-300 flex items-center gap-1">
                <ChevronLeftIcon class="h-4 w-4" />
                All Devices
            </Link>
            <span class="text-gray-500">/</span>
            <span :style="{ color: themeColors.textPrimary }">{{ liveDevice.device_name || liveDevice.device_id }}</span>
            <!-- Live indicator -->
            <span class="ml-auto flex items-center gap-1.5 text-xs text-green-400 opacity-70">
                <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                Live · updates every 8s
            </span>
        </div>

        <!-- Top section: info + status + token -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

            <!-- Device Info Card -->
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                 class="lg:col-span-2 rounded-xl border p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-xl bg-yellow-500/20 flex items-center justify-center">
                            <TvIcon class="h-8 w-8 text-yellow-400" />
                        </div>
                        <div>
                            <h1 :style="{ color: themeColors.textPrimary }" class="text-xl font-bold">
                                {{ liveDevice.device_name || liveDevice.device_id }}
                            </h1>
                            <p :style="{ color: themeColors.textSecondary }" class="text-sm font-mono">{{ liveDevice.device_id }}</p>
                        </div>
                    </div>
                    <!-- Status badge -->
                    <span :class="statusBadgeClass(liveDevice.computed_status)"
                          class="px-3 py-1 rounded-full text-sm font-semibold capitalize flex items-center gap-1.5">
                        <span :class="statusDotClass(liveDevice.computed_status)" class="w-2 h-2 rounded-full"></span>
                        {{ liveDevice.computed_status || 'offline' }}
                    </span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-xs mb-0.5">Room</p>
                        <p :style="{ color: themeColors.textPrimary }" class="font-medium">
                            {{ liveDevice.room_number ? 'Room ' + liveDevice.room_number : '—' }}
                        </p>
                    </div>
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-xs mb-0.5">IP Address</p>
                        <p :style="{ color: themeColors.textPrimary }" class="font-mono">{{ liveDevice.ip_address || '—' }}</p>
                    </div>
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-xs mb-0.5">App Version</p>
                        <p :style="{ color: themeColors.textPrimary }">{{ liveDevice.app_version || '—' }}</p>
                    </div>
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-xs mb-0.5">Android</p>
                        <p :style="{ color: themeColors.textPrimary }">{{ liveDevice.android_version || '—' }}</p>
                    </div>
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-xs mb-0.5">Last Heartbeat</p>
                        <p :style="{ color: themeColors.textPrimary }">{{ ago(liveDevice.last_heartbeat) }}</p>
                    </div>
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-xs mb-0.5">Registered</p>
                        <p :style="{ color: themeColors.textPrimary }">{{ ago(liveDevice.registered_at) }}</p>
                    </div>
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-xs mb-0.5">Settings Version</p>
                        <p :style="{ color: themeColors.textPrimary }">v{{ liveDevice.settings_version || 0 }}</p>
                    </div>
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-xs mb-0.5">Device Type</p>
                        <p :style="{ color: themeColors.textPrimary }" class="capitalize">{{ (liveDevice.device_type || 'unknown').replace('_', ' ') }}</p>
                    </div>
                </div>

                <!-- Notes / edit -->
                <div class="mt-4 pt-4 border-t" :style="{ borderColor: themeColors.border }">
                    <label :style="{ color: themeColors.textSecondary }" class="block text-xs mb-1">Notes</label>
                    <textarea v-model="editForm.notes" rows="2"
                              :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"
                              class="w-full px-3 py-2 rounded-lg border text-sm focus:outline-none resize-none" />
                    <div class="flex justify-end mt-2">
                        <button @click="saveNotes" class="text-xs px-4 py-1.5 rounded-lg bg-yellow-500/20 text-yellow-400 hover:bg-yellow-500/30">
                            Save Notes
                        </button>
                    </div>
                </div>
            </div>

            <!-- Registration Token + Actions -->
            <div class="space-y-4">
                <!-- Token card -->
                <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                     class="rounded-xl border p-5">
                    <h3 :style="{ color: themeColors.textPrimary }" class="font-semibold text-sm mb-3 flex items-center gap-2">
                        <KeyIcon class="h-4 w-4 text-yellow-400" />
                        Registration Token
                    </h3>
                    <code v-if="liveDevice.registration_token"
                          :style="{ backgroundColor: themeColors.background }"
                          class="block px-3 py-2 rounded-lg text-xs font-mono text-yellow-300 break-all mb-3">
                        {{ liveDevice.registration_token }}
                    </code>
                    <p v-else :style="{ color: themeColors.textSecondary }" class="text-xs mb-3">
                        No token yet. Click Register to generate one.
                    </p>
                    <div class="flex gap-2">
                        <button v-if="liveDevice.registration_token" @click="copy(liveDevice.registration_token)"
                                class="flex-1 text-xs py-1.5 rounded-lg bg-blue-500/20 text-blue-400 hover:bg-blue-500/30">
                            Copy Token
                        </button>
                        <button @click="regenerateToken"
                                class="flex-1 text-xs py-1.5 rounded-lg bg-orange-500/20 text-orange-400 hover:bg-orange-500/30">
                            New Token
                        </button>
                    </div>
                    <p :style="{ color: themeColors.textSecondary }" class="text-xs mt-3">
                        Enter server URL <strong class="text-white">{{ serverUrl }}</strong> + this token in the Android app.
                    </p>
                </div>

                <!-- Quick danger actions -->
                <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                     class="rounded-xl border p-5">
                    <h3 :style="{ color: themeColors.textPrimary }" class="font-semibold text-sm mb-3 flex items-center gap-2">
                        <ExclamationTriangleIcon class="h-4 w-4 text-red-400" />
                        Device Actions
                    </h3>
                    <div class="space-y-2">
                        <button @click="sendCmd('reboot')"
                                class="w-full text-xs py-2 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30">
                            🔄 Reboot Device
                        </button>
                        <button @click="sendCmd('reload_app')"
                                class="w-full text-xs py-2 rounded-lg bg-cyan-500/20 text-cyan-400 hover:bg-cyan-500/30">
                            ↺ Reload App
                        </button>
                        <button @click="sendCmd('refresh_channels')"
                                class="w-full text-xs py-2 rounded-lg bg-blue-500/20 text-blue-400 hover:bg-blue-500/30">
                            📡 Refresh Channels
                        </button>
                        <button @click="toggleActive"
                                :class="liveDevice.is_active ? 'bg-red-500/20 text-red-400 hover:bg-red-500/30' : 'bg-green-500/20 text-green-400 hover:bg-green-500/30'"
                                class="w-full text-xs py-2 rounded-lg">
                            {{ liveDevice.is_active ? '🔒 Deactivate Device' : '🔓 Activate Device' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Remote Control + Settings Push -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

            <!-- Remote Control Panel -->
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                 class="rounded-xl border p-6">
                <h2 :style="{ color: themeColors.textPrimary }" class="font-bold mb-4 flex items-center gap-2">
                    <CommandLineIcon class="h-5 w-5 text-yellow-400" />
                    Remote Control
                </h2>

                <!-- Lock/Unlock -->
                <div class="flex gap-3 mb-5">
                    <button @click="sendCmd('lock')"
                            class="flex-1 py-3 rounded-xl text-sm font-semibold bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 flex items-center justify-center gap-2">
                        <LockClosedIcon class="h-4 w-4" />
                        Lock Screen
                    </button>
                    <button @click="sendCmd('unlock')"
                            class="flex-1 py-3 rounded-xl text-sm font-semibold bg-green-500/20 text-green-400 hover:bg-green-500/30 flex items-center justify-center gap-2">
                        <LockOpenIcon class="h-4 w-4" />
                        Unlock
                    </button>
                </div>

                <!-- Send Message -->
                <div class="mb-5">
                    <label :style="{ color: themeColors.textSecondary }" class="block text-xs font-semibold uppercase tracking-wider mb-2">
                        💬 Send Message to TV Screen
                    </label>
                    <!-- Sent flash -->
                    <transition name="fade">
                        <div v-if="msgSent"
                             class="flex items-center gap-2 text-xs text-green-400 bg-green-500/10 border border-green-500/20 rounded-lg px-3 py-2 mb-2">
                            <span>✓</span> Message delivered to TV
                        </div>
                    </transition>
                    <textarea v-model="msgText"
                              placeholder="Type the message guests will see on their TV…"
                              maxlength="200"
                              rows="3"
                              :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: msgText.length > 180 ? '#f59e0b' : themeColors.border }"
                              class="w-full px-3 py-2 rounded-lg border text-sm focus:outline-none resize-none transition-colors"
                              @keydown.ctrl.enter="sendMessage" />
                    <div class="flex items-center justify-between mt-1.5">
                        <span :style="{ color: themeColors.textSecondary }" class="text-xs">
                            Ctrl+Enter to send · displays for 8 s on TV
                        </span>
                        <div class="flex items-center gap-2">
                            <span :class="msgText.length > 180 ? 'text-yellow-400' : ''"
                                  :style="msgText.length <= 180 ? { color: themeColors.textSecondary } : {}"
                                  class="text-xs">{{ msgText.length }}/200</span>
                            <button @click="sendMessage" :disabled="!msgText.trim() || sendingMsg"
                                    class="px-4 py-1.5 rounded-lg bg-yellow-500 text-black text-xs font-bold disabled:opacity-40 hover:bg-yellow-400 transition-colors flex items-center gap-1.5">
                                <span v-if="sendingMsg">Sending…</span>
                                <span v-else>Send 📤</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Set Channel -->
                <div>
                    <label :style="{ color: themeColors.textSecondary }" class="block text-xs mb-1">Jump to Channel Number</label>
                    <div class="flex gap-2">
                        <input v-model.number="channelNum" type="number" min="1" placeholder="e.g. 42"
                               :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"
                               class="w-32 px-3 py-2 rounded-lg border text-sm focus:outline-none" />
                        <button @click="setChannel" :disabled="!channelNum"
                                class="px-4 py-2 rounded-lg bg-blue-500/20 text-blue-400 hover:bg-blue-500/30 text-sm font-semibold disabled:opacity-40">
                            📺 Set Channel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Per-device Settings Push -->
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                 class="rounded-xl border p-6">
                <h2 :style="{ color: themeColors.textPrimary }" class="font-bold mb-4 flex items-center gap-2">
                    <Cog6ToothIcon class="h-5 w-5 text-purple-400" />
                    Push Settings to This Device
                </h2>
                <p :style="{ color: themeColors.textSecondary }" class="text-xs mb-4">
                    These override global settings only for this device.
                </p>
                <div class="space-y-3">
                    <div>
                        <label :style="{ color: themeColors.textSecondary }" class="block text-xs mb-1">Xtream Server URL</label>
                        <input v-model="settingsForm.xtream_url" placeholder="Leave blank to use global"
                               :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"
                               class="w-full px-3 py-2 rounded-lg border text-sm focus:outline-none" />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label :style="{ color: themeColors.textSecondary }" class="block text-xs mb-1">Username</label>
                            <input v-model="settingsForm.xtream_username"
                                   :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"
                                   class="w-full px-3 py-2 rounded-lg border text-sm focus:outline-none" />
                        </div>
                        <div>
                            <label :style="{ color: themeColors.textSecondary }" class="block text-xs mb-1">Password</label>
                            <input v-model="settingsForm.xtream_password" type="password"
                                   :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"
                                   class="w-full px-3 py-2 rounded-lg border text-sm focus:outline-none" />
                        </div>
                    </div>
                    <div>
                        <label :style="{ color: themeColors.textSecondary }" class="block text-xs mb-1">Admin PIN Override</label>
                        <input v-model="settingsForm.admin_pin" maxlength="6" placeholder="Leave blank to use global"
                               :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"
                               class="w-full px-3 py-2 rounded-lg border text-sm focus:outline-none" />
                    </div>
                    <button @click="pushSettings" :disabled="pushingSettings"
                            class="w-full py-2.5 rounded-xl text-sm font-bold bg-purple-600 hover:bg-purple-500 text-white disabled:opacity-50">
                        {{ pushingSettings ? 'Pushing...' : '⬆ Push Settings' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Command History + Heartbeat Timeline -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Command History -->
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                 class="rounded-xl border overflow-hidden">
                <div :style="{ borderColor: themeColors.border }" class="px-5 py-3 border-b flex items-center justify-between">
                    <h2 :style="{ color: themeColors.textPrimary }" class="font-bold text-sm">Command History</h2>
                    <span :style="{ color: themeColors.textSecondary }" class="text-xs">{{ liveCommands.length }} total</span>
                </div>
                <div class="divide-y" :style="{ borderColor: themeColors.border }">
                    <div v-if="!liveCommands || liveCommands.length === 0"
                         :style="{ color: themeColors.textSecondary }" class="px-5 py-8 text-center text-sm">
                        No commands sent yet.
                    </div>
                    <div v-for="cmd in liveCommands" :key="cmd.id"
                         class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p :style="{ color: themeColors.textPrimary }" class="text-sm font-medium capitalize">
                                {{ cmd.type.replace(/_/g, ' ') }}
                            </p>
                            <p :style="{ color: themeColors.textSecondary }" class="text-xs">{{ ago(cmd.dispatched_at) }}</p>
                        </div>
                        <span :class="cmdBadgeClass(cmd.status)"
                              class="text-xs px-2 py-0.5 rounded-full font-semibold capitalize">
                            {{ cmd.status }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Heartbeat Timeline -->
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }"
                 class="rounded-xl border overflow-hidden">
                <div :style="{ borderColor: themeColors.border }" class="px-5 py-3 border-b flex items-center justify-between">
                    <h2 :style="{ color: themeColors.textPrimary }" class="font-bold text-sm">Heartbeat Log</h2>
                    <span :style="{ color: themeColors.textSecondary }" class="text-xs">Last 48 pings</span>
                </div>
                <div class="divide-y" :style="{ borderColor: themeColors.border }">
                    <div v-if="!liveHeartbeats || liveHeartbeats.length === 0"
                         :style="{ color: themeColors.textSecondary }" class="px-5 py-8 text-center text-sm">
                        No heartbeats received yet. Device has not connected.
                    </div>
                    <div v-for="hb in liveHeartbeats" :key="hb.id"
                         class="px-5 py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span :class="hb.status === 'online' ? 'bg-green-400' : 'bg-gray-400'"
                                  class="w-2 h-2 rounded-full flex-shrink-0"></span>
                            <div>
                                <p :style="{ color: themeColors.textPrimary }" class="text-xs font-medium">
                                    {{ hb.status }} — {{ hb.ip_address || '—' }}
                                </p>
                                <p :style="{ color: themeColors.textSecondary }" class="text-xs">
                                    {{ hb.current_channel ? 'Ch ' + hb.current_channel : '' }}
                                    {{ hb.app_version ? '· v' + hb.app_version : '' }}
                                </p>
                            </div>
                        </div>
                        <span :style="{ color: themeColors.textSecondary }" class="text-xs">{{ ago(hb.recorded_at) }}</span>
                    </div>
                </div>
            </div>
        </div>

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
    ChevronLeftIcon,
    ExclamationTriangleIcon,
    CommandLineIcon,
    Cog6ToothIcon,
    KeyIcon,
    LockClosedIcon,
    LockOpenIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    device: { type: Object, default: () => ({}) },
    commands: { type: Array, default: () => [] },
    heartbeats: { type: Array, default: () => [] },
    serverUrl: { type: String, default: '' },
})

// ── Live reactive copies (updated by the poll loop) ───────────────────────
const liveDevice     = ref({ ...props.device })
const liveCommands   = ref([...props.commands])
const liveHeartbeats = ref([...props.heartbeats])
const lastPollAt     = ref(null)

// ── Poll every 8 seconds ─────────────────────────────────────────────────
let pollTimer = null

const startPolling = () => {
    pollTimer = setInterval(async () => {
        try {
            const res = await fetch(route('admin.iptv.devices.poll', props.device.id), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            if (!res.ok) return
            const data = await res.json()
            liveDevice.value     = data.device
            liveCommands.value   = data.commands
            liveHeartbeats.value = data.heartbeats
            lastPollAt.value     = new Date()
        } catch (e) {
            // silently ignore network errors
        }
    }, 8000)
}

onMounted(startPolling)
onUnmounted(() => clearInterval(pollTimer))

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

const serverUrl = computed(() => props.serverUrl || window.location.origin)

// Helpers
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

const statusBadgeClass = (s) => {
    if (s === 'online') return 'bg-green-500/20 text-green-400'
    if (s === 'idle') return 'bg-yellow-500/20 text-yellow-400'
    return 'bg-red-500/20 text-red-400'
}
const statusDotClass = (s) => {
    if (s === 'online') return 'bg-green-400 animate-pulse'
    if (s === 'idle') return 'bg-yellow-400'
    return 'bg-red-400'
}
const cmdBadgeClass = (s) => {
    if (s === 'executed') return 'bg-green-500/20 text-green-400'
    if (s === 'pending' || s === 'delivered') return 'bg-yellow-500/20 text-yellow-400'
    return 'bg-red-500/20 text-red-400'
}

// Edit notes
const editForm = useForm({ notes: props.device.notes || '' })
const saveNotes = () => {
    router.put(route('admin.iptv.devices.update', props.device.id), { notes: editForm.notes }, { preserveScroll: true })
}

// Send command
const sendCmd = (type) => {
    if (type === 'reboot' && !confirm('Reboot this device?')) return
    router.post(route('admin.iptv.devices.command', props.device.id), { type }, { preserveScroll: true })
}

// Message
const msgText    = ref('')
const sendingMsg = ref(false)
const msgSent    = ref(false)
let msgSentTimer = null

const sendMessage = () => {
    if (!msgText.value.trim() || sendingMsg.value) return
    sendingMsg.value = true
    router.post(route('admin.iptv.devices.command', props.device.id), {
        type: 'message',
        payload: { text: msgText.value.trim() }
    }, {
        preserveScroll: true,
        onSuccess: () => {
            msgText.value    = ''
            sendingMsg.value = false
            msgSent.value    = true
            if (msgSentTimer) clearTimeout(msgSentTimer)
            msgSentTimer = setTimeout(() => { msgSent.value = false }, 3000)
        },
        onError: () => { sendingMsg.value = false }
    })
}

// Set channel
const channelNum = ref(null)
const setChannel = () => {
    if (!channelNum.value) return
    router.post(route('admin.iptv.devices.command', props.device.id), {
        type: 'set_channel',
        payload: { channel: channelNum.value }
    }, { preserveScroll: true, onSuccess: () => { channelNum.value = null } })
}

// Toggle active
const toggleActive = () => {
    router.put(route('admin.iptv.devices.update', props.device.id), {
        is_active: !liveDevice.value.is_active
    }, { preserveScroll: true })
}

// Regenerate token
const regenerateToken = () => {
    if (!confirm('Generate a new registration token? The old one will stop working immediately.')) return
    router.post(route('admin.iptv.devices.regenerate-token', props.device.id), {}, { preserveScroll: true })
}

// Push settings
const pushingSettings = ref(false)
const settingsForm = ref({ xtream_url: '', xtream_username: '', xtream_password: '', admin_pin: '' })
const pushSettings = () => {
    pushingSettings.value = true
    router.post(route('admin.iptv.devices.push-settings', props.device.id), settingsForm.value, {
        preserveScroll: true,
        onFinish: () => { pushingSettings.value = false }
    })
}

// Copy
const copy = (text) => navigator.clipboard.writeText(text).then(() => alert('Copied!'))
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.4s ease; }
.fade-enter-from, .fade-leave-to       { opacity: 0; }
</style>
