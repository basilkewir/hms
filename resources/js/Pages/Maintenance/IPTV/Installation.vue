<template>
    <DashboardLayout title="IPTV Installation" :user="user">
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">IPTV Installation</h1>
            <p class="text-gray-600 mt-2">Devices pending room assignment.</p>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Seen</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="devices.length === 0">
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No unassigned devices.</td>
                        </tr>
                        <tr v-for="device in devices" :key="device.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ device.device_name || 'Unknown' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ device.device_type || 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ device.ip_address || 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ device.last_seen }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

const props = defineProps({
    user: Object,
    devices: Array,
})

const devices = props.devices || []
</script>
