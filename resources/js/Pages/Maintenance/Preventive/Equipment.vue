<template>
    <DashboardLayout title="Equipment Log" :user="user">
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Equipment Log</h1>
            <p class="text-gray-600 mt-2">Maintenance activity by category.</p>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Equipment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Scheduled Tasks</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Due</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="equipment.length === 0">
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">No scheduled maintenance records.</td>
                        </tr>
                        <tr v-for="row in equipment" :key="row.equipment" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ row.equipment }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ row.total }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ row.last_due || 'N/A' }}</td>
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
    equipment: Array,
})

const equipment = props.equipment || []
</script>
