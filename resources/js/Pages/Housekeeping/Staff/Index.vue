<template>
    <DashboardLayout title="Housekeeping Staff" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-6" :style="{ backgroundColor: themeColors.card }">
            <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Housekeeping Staff</h1>
            <p class="mt-1 text-sm" :style="{ color: themeColors.textSecondary }">View staff members and their current assignments.</p>
        </div>
        <div class="shadow rounded-lg overflow-hidden" :style="{ backgroundColor: themeColors.card }">
            <table class="min-w-full divide-y" :style="{ borderColor: themeColors.border }">
                <thead :style="{ backgroundColor: themeColors.background }">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Current Assignment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Tasks Today</th>
                    </tr>
                </thead>
                <tbody class="divide-y" :style="{ borderColor: themeColors.border }">
                    <tr v-if="!staff || staff.length === 0">
                        <td colspan="4" class="px-6 py-10 text-center text-sm" :style="{ color: themeColors.textSecondary }">No staff data found.</td>
                    </tr>
                    <tr v-for="member in staff" :key="member.id">
                        <td class="px-6 py-4 text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ member.name }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs" :class="member.on_duty ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">{{ member.on_duty ? 'On Duty' : 'Off Duty' }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textSecondary }">{{ member.current_assignment ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textPrimary }">{{ member.tasks_today ?? 0 }}</td>
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
defineProps({ user: Object, navigation: Array, staff: { type: Array, default: () => [] } });
</script>
