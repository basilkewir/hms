<template>
    <DashboardLayout title="Overdue Maintenance" :user="user">
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Overdue Maintenance</h1>
            <p class="text-gray-600 mt-2">Tasks past their scheduled date.</p>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Task</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Equipment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="tasks.length === 0">
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No overdue tasks.</td>
                        </tr>
                        <tr v-for="task in tasks" :key="task.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ task.description }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ task.equipment }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ task.due_date || 'N/A' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Overdue
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
    tasks: Array,
})

const tasks = props.tasks || []
</script>
