<template>
    <DashboardLayout title="IPTV Device Management" :user="user" :navigation="navigation">
        <!-- Device Header -->
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
             class="shadow rounded-lg p-6 mb-8 border">
            <div class="flex items-center justify-between">
                <div>
                    <h1 :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">IPTV Device Management</h1>
                    <p :style="{ color: themeColors.textSecondary }" class="mt-2">Monitor and manage all IPTV devices across the hotel</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="exportDevices" 
                            :style="{ backgroundColor: themeColors.success, color: '#000' }"
                            class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity flex items-center">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                        Export
                    </button>
                    <button @click="registerDevice" 
                            :style="{ backgroundColor: themeColors.primary, color: '#000' }"
                            class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity flex items-center">
                        <DeviceTabletIcon class="h-4 w-4 mr-2" />
                        Register Device
                    </button>
                </div>
            </div>
        </div>

        <!-- Device Stats -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <DeviceTabletIcon :style="{ color: themeColors.primary }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Total Devices</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ deviceStats.total }}</p>
                    </div>
                </div>
            </div>
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <CheckCircleIcon :style="{ color: themeColors.success }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Online</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ deviceStats.online }}</p>
                    </div>
                </div>
            </div>
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <XCircleIcon :style="{ color: themeColors.danger }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Offline</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ deviceStats.offline }}</p>
                    </div>
                </div>
            </div>
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <ExclamationTriangleIcon :style="{ color: themeColors.warning }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Issues</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ deviceStats.issues }}</p>
                    </div>
                </div>
            </div>
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <ClockIcon :style="{ color: themeColors.primary }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Avg Uptime</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ deviceStats.uptime }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Devices Table -->
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
             class="shadow rounded-lg overflow-hidden border">
            <div :style="{ borderColor: themeColors.border }" class="px-6 py-4 border-b">
                <h3 :style="{ color: themeColors.textPrimary }" class="text-lg font-medium">All IPTV Devices</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Device / Room</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Package</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">IP Address</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Last Seen</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Version</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="device in filteredDevices" :key="device.id"
                            :style="hoveredRow === device.id ? { backgroundColor: themeColors.hover } : {}"
                            @mouseenter="hoveredRow = device.id"
                            @mouseleave="hoveredRow = null"
                            class="transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div :style="{ backgroundColor: themeColors.primary, color: '#000' }" 
                                         class="w-10 h-10 rounded-lg flex items-center justify-center mr-4">
                                        <DeviceTabletIcon class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <div :style="{ color: themeColors.textPrimary }" class="text-sm font-medium">{{ device.device_id }}</div>
                                        <div :style="{ color: themeColors.textSecondary }" class="text-sm">Room {{ device.room_number }}</div>
                                        <div :style="{ color: themeColors.textTertiary }" class="text-xs">{{ device.mac_address }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(device.status)">
                                    <span class="w-2 h-2 rounded-full mr-1.5" :class="getStatusDot(device.status)"></span>
                                    {{ formatStatus(device.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getPackageColor(device.package)">
                                    {{ formatPackage(device.package) }}
                                </span>
                            </td>
                            <td :style="{ color: themeColors.textPrimary }" class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ device.ip_address }}
                            </td>
                            <td :style="{ color: themeColors.textSecondary }" class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ formatLastSeen(device.last_heartbeat) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div :style="{ color: themeColors.textPrimary }">{{ device.app_version }}</div>
                                <div :style="{ color: themeColors.textTertiary }" class="text-xs">Android {{ device.android_version }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button @click="viewDevice(device)" :style="{ color: themeColors.primary }" class="hover:opacity-80">View</button>
                                    <button @click="restartDevice(device)" :style="{ color: themeColors.success }" class="hover:opacity-80">Restart</button>
                                    <button @click="removeDevice(device)" :style="{ color: themeColors.danger }" class="hover:opacity-80">Remove</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Register Device Modal -->
        <DialogModal :show="showRegisterModal" @close="closeRegisterModal">
            <template #title>
                Register New IPTV Device
            </template>

            <template #content>
                <form @submit.prevent="submitRegisterDevice" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Device ID *</label>
                        <input v-model="deviceForm.device_id" type="text" required
                               placeholder="e.g., ANDROID_TV_001"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p v-if="deviceForm.errors.device_id" class="text-red-500 text-xs mt-1">{{ deviceForm.errors.device_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">MAC Address *</label>
                        <input v-model="deviceForm.mac_address" type="text" required
                               placeholder="e.g., AA:BB:CC:DD:EE:FF"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p v-if="deviceForm.errors.mac_address" class="text-red-500 text-xs mt-1">{{ deviceForm.errors.mac_address }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Room *</label>
                        <select v-model="deviceForm.room_id" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select a room</option>
                            <option v-for="room in availableRooms" :key="room.id" :value="room.id">
                                Room {{ room.room_number }}
                            </option>
                        </select>
                        <p v-if="!availableRooms || availableRooms.length === 0" class="text-yellow-600 text-xs mt-1">
                            No rooms available. Please create rooms first.
                        </p>
                        <p v-if="deviceForm.errors.room_id" class="text-red-500 text-xs mt-1">{{ deviceForm.errors.room_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">IPTV Package *</label>
                        <select v-model="deviceForm.package" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="basic">Basic (50 channels)</option>
                            <option value="premium">Premium (150 channels)</option>
                            <option value="vip">VIP (Unlimited channels)</option>
                        </select>
                        <p v-if="deviceForm.errors.package" class="text-red-500 text-xs mt-1">{{ deviceForm.errors.package }}</p>
                    </div>

                    <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200">
                        <button type="button" @click="closeRegisterModal"
                                class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" :disabled="deviceForm.processing"
                                class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50">
                            <span v-if="deviceForm.processing">Registering...</span>
                            <span v-else>Register Device</span>
                        </button>
                    </div>
                </form>
            </template>
        </DialogModal>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DialogModal from '@/Components/DialogModal.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'
import {
    DeviceTabletIcon,
    CheckCircleIcon,
    XCircleIcon,
    ExclamationTriangleIcon,
    ClockIcon,
    DocumentArrowDownIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    devices: Array,
    deviceStats: Object,
    availableRooms: Array,
})

const { currentTheme } = useTheme()
const navigation = computed(() => getNavigationForRole('admin'))
const hoveredRow = ref(null)
const showRegisterModal = ref(false)

const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    primary: `var(--kotel-primary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)'
}))

const searchQuery = ref('')
const selectedStatus = ref('')
const selectedPackage = ref('')

const deviceForm = useForm({
    device_id: '',
    mac_address: '',
    room_id: '',
    package: 'basic',
})

const filteredDevices = computed(() => {
    return props.devices.filter(device => {
        const matchesSearch = !searchQuery.value || 
            device.device_id.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            device.room_number.includes(searchQuery.value) ||
            (device.ip_address && device.ip_address.includes(searchQuery.value))
        
        const matchesStatus = !selectedStatus.value || device.status === selectedStatus.value
        const matchesPackage = !selectedPackage.value || device.package === selectedPackage.value
        
        return matchesSearch && matchesStatus && matchesPackage
    })
})

const getStatusColor = (status) => {
    const colors = {
        online: 'bg-green-100 text-green-800',
        offline: 'bg-red-100 text-red-800',
        error: 'bg-yellow-100 text-yellow-800',
        maintenance: 'bg-blue-100 text-blue-800'
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const getStatusDot = (status) => {
    const colors = {
        online: 'bg-green-400',
        offline: 'bg-red-400',
        error: 'bg-yellow-400',
        maintenance: 'bg-blue-400'
    }
    return colors[status] || 'bg-gray-400'
}

const getPackageColor = (packageType) => {
    const colors = {
        basic: 'bg-gray-100 text-gray-800',
        premium: 'bg-blue-100 text-blue-800',
        vip: 'bg-purple-100 text-purple-800'
    }
    return colors[packageType] || 'bg-gray-100 text-gray-800'
}

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1)
}

const formatPackage = (packageType) => {
    return packageType.toUpperCase()
}

const formatLastSeen = (date) => {
    if (!date) return 'Never'
    const lastSeen = new Date(date)
    const now = new Date()
    const diff = now - lastSeen
    const minutes = Math.floor(diff / 60000)
    
    if (minutes < 1) return 'Just now'
    if (minutes < 60) return `${minutes}m ago`
    
    const hours = Math.floor(minutes / 60)
    if (hours < 24) return `${hours}h ago`
    
    const days = Math.floor(hours / 24)
    return `${days}d ago`
}

const registerDevice = () => {
    showRegisterModal.value = true
}

const closeRegisterModal = () => {
    showRegisterModal.value = false
    deviceForm.reset()
}

const submitRegisterDevice = () => {
    deviceForm.post(route('admin.iptv.devices.store'), {
        onSuccess: () => {
            closeRegisterModal()
        }
    })
}

const exportDevices = () => {
    alert('Exporting device list...')
}

const viewDevice = (device) => {
    alert(`Viewing device ${device.device_id}`)
}

const restartDevice = (device) => {
    if (confirm(`Restart device ${device.device_id}?`)) {
        alert(`Restarting device ${device.device_id}...`)
    }
}

const updateDevice = (device) => {
    alert(`Updating device ${device.device_id}...`)
}

const removeDevice = (device) => {
    if (confirm(`Remove device ${device.device_id}?`)) {
        router.delete(route('admin.iptv.devices.destroy', device.id))
    }
}
</script>
