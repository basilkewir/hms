<template>
    <DashboardLayout title="Vendor Contacts" :user="user">
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Vendor Contacts</h1>
            <p class="text-gray-600 mt-2">Suppliers available for maintenance parts.</p>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="vendors.length === 0">
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No vendors available.</td>
                        </tr>
                        <tr v-for="vendor in vendors" :key="vendor.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ vendor.name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ vendor.contact_person || 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ vendor.email || 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ vendor.phone || 'N/A' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full text-xs font-medium"
                                      :class="vendor.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                                    {{ vendor.is_active ? 'Active' : 'Inactive' }}
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

const props = defineProps({
    user: Object,
    vendors: Array,
})

const vendors = props.vendors || []
</script>
