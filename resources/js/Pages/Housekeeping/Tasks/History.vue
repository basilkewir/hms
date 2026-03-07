<template>
    <DashboardLayout title="Task History" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-6" :style="{ backgroundColor: themeColors.card }">
            <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Task History</h1>
            <p class="mt-1 text-sm" :style="{ color: themeColors.textSecondary }">View all completed housekeeping tasks.</p>
        </div>
        <div class="shadow rounded-lg overflow-hidden" :style="{ backgroundColor: themeColors.card }">
            <table class="min-w-full divide-y" :style="{ borderColor: themeColors.border }">
                <thead :style="{ backgroundColor: themeColors.background }">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Room</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Task</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Completed By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Completed At</th>
                    </tr>
                </thead>
                <tbody class="divide-y" :style="{ borderColor: themeColors.border }">
                    <tr v-if="!history || history.length === 0">
                        <td colspan="4" class="px-6 py-10 text-center text-sm" :style="{ color: themeColors.textSecondary }">No task history found.</td>
                    </tr>
                    <tr v-for="item in history" :key="item.id">
                        <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textPrimary }">{{ item.room_number ?? item.room_id }}</td>
                        <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textPrimary }">{{ item.task_type }}</td>
                        <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textSecondary }">{{ item.assigned_to ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textSecondary }">{{ item.updated_at ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </DashboardLayout>
</template>
<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useTheme } from '@/Composables/useTheme';
const { themeColors } = useTheme();
defineProps({ user: Object, navigation: Array, history: { type: Array, default: () => [] } });
</script>
