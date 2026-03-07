<template>
    <DashboardLayout title="Housekeeping Tasks" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-6" :style="{ backgroundColor: themeColors.card }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Housekeeping Tasks</h1>
                    <p class="mt-1 text-sm" :style="{ color: themeColors.textSecondary }">Manage and track all housekeeping tasks.</p>
                </div>
                <Link href="/housekeeping/tasks/create" class="inline-flex items-center px-4 py-2 rounded-md text-white text-sm font-medium" :style="{ backgroundColor: themeColors.primary }">
                    <PlusIcon class="h-4 w-4 mr-2" /> New Task
                </Link>
            </div>
        </div>

        <div class="shadow rounded-lg overflow-hidden" :style="{ backgroundColor: themeColors.card }">
            <table class="min-w-full divide-y" :style="{ borderColor: themeColors.border }">
                <thead :style="{ backgroundColor: themeColors.background }">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Room</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Task</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Assigned To</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Due</th>
                    </tr>
                </thead>
                <tbody class="divide-y" :style="{ borderColor: themeColors.border }">
                    <tr v-if="!tasks || tasks.length === 0">
                        <td colspan="5" class="px-6 py-10 text-center text-sm" :style="{ color: themeColors.textSecondary }">No tasks found.</td>
                    </tr>
                    <tr v-for="task in tasks" :key="task.id" class="hover:opacity-90">
                        <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textPrimary }">{{ task.room_number ?? task.room_id }}</td>
                        <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textPrimary }">{{ task.task_type ?? task.title }}</td>
                        <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textSecondary }">{{ task.assigned_to ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-medium" :class="statusClass(task.status)">{{ task.status }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textSecondary }">{{ task.due_date ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useTheme } from '@/Composables/useTheme';
import { PlusIcon } from '@heroicons/vue/24/outline';

const { themeColors } = useTheme();

defineProps({ user: Object, navigation: Array, tasks: { type: Array, default: () => [] } });

const statusClass = (status) => {
    const map = { pending: 'bg-yellow-100 text-yellow-800', in_progress: 'bg-blue-100 text-blue-800', completed: 'bg-green-100 text-green-800' };
    return map[status] ?? 'bg-gray-100 text-gray-800';
};
</script>
