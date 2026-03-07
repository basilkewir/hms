<template>
    <DashboardLayout title="Housekeeping Schedules" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-6" :style="{ backgroundColor: themeColors.card }">
            <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Housekeeping Schedules</h1>
            <p class="mt-1 text-sm" :style="{ color: themeColors.textSecondary }">View your assigned cleaning schedules.</p>
        </div>
        <div class="shadow rounded-lg overflow-hidden" :style="{ backgroundColor: themeColors.card }">
            <table class="min-w-full divide-y" :style="{ borderColor: themeColors.border }">
                <thead :style="{ backgroundColor: themeColors.background }">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Shift</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Rooms Assigned</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y" :style="{ borderColor: themeColors.border }">
                    <tr v-if="!schedules || schedules.length === 0">
                        <td colspan="4" class="px-6 py-10 text-center text-sm" :style="{ color: themeColors.textSecondary }">No schedules found.</td>
                    </tr>
                    <tr v-for="schedule in schedules" :key="schedule.id">
                        <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textPrimary }">{{ schedule.date }}</td>
                        <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textSecondary }">{{ schedule.shift ?? 'Morning' }}</td>
                        <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textPrimary }">{{ schedule.rooms_count ?? 0 }} rooms</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs" :class="schedule.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">{{ schedule.status ?? 'Pending' }}</span>
                        </td>
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
defineProps({ user: Object, navigation: Array, schedules: { type: Array, default: () => [] } });
</script>
