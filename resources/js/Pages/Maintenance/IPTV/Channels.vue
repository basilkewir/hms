<template>
    <DashboardLayout title="IPTV Packages" :user="user">
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">IPTV Packages</h1>
            <p class="text-gray-600 mt-2">Active IPTV packages and configuration.</p>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="packages.length === 0">
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No packages configured.</td>
                        </tr>
                        <tr v-for="pkg in packages" :key="pkg.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ pkg.name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ pkg.code || 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ formatCurrency(pkg.price || 0) }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full text-xs font-medium"
                                      :class="pkg.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                                    {{ pkg.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'

const props = defineProps({
    user: Object,
    packages: Array,
})

const packages = props.packages || []
</script>
