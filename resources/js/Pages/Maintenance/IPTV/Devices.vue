<template>
    <DashboardLayout title="IPTV Devices" :user="user">
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">IPTV Devices</h1>
            <p class="text-gray-600 mt-2">Monitor device connectivity and status.</p>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Room</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Seen</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="devices.data.length === 0">
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No devices found.</td>
                        </tr>
                        <tr v-for="device in devices.data" :key="device.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ device.room_number || 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ device.device_type || device.device_name || 'Unknown' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ device.ip_address || 'N/A' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full text-xs font-medium"
                                      :class="device.online ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    {{ device.online ? 'Online' : 'Offline' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ device.last_seen }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="devices.links" class="px-6 py-4 border-t border-gray-200">
                <Pagination :links="devices.links" />
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    user: Object,
    devices: Object,
})

const devices = computed(() => props.devices || { data: [], links: [] })
</script>
