<template>
    <DashboardLayout title="Staff Schedules" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-6" :style="{ backgroundColor: themeColors.card }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Staff Schedules</h1>
                    <p class="mt-1 text-sm" :style="{ color: themeColors.textSecondary }">View and manage staff work schedules.</p>
                </div>
            </div>
        </div>
        <div class="shadow rounded-lg overflow-hidden" :style="{ backgroundColor: themeColors.card }">
            <table class="min-w-full divide-y" :style="{ borderColor: themeColors.border }">
                <thead :style="{ backgroundColor: themeColors.background }">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Staff Member</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Shift</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Days</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y" :style="{ borderColor: themeColors.border }">
                    <tr v-if="!schedules || schedules.length === 0">
                        <td colspan="5" class="px-6 py-10 text-center text-sm" :style="{ color: themeColors.textSecondary }">No schedules found.</td>
                    </tr>
                    <tr v-for="schedule in schedules" :key="schedule.id">
                        <td class="px-6 py-4 text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ schedule.user?.name ?? schedule.staff_name }}</td>
                        <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textSecondary }">{{ schedule.department ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textPrimary }">{{ schedule.shift_name ?? schedule.shift }}</td>
                        <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textSecondary }">{{ schedule.days ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs" :class="schedule.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">{{ schedule.is_active ? 'Active' : 'Inactive' }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </DashboardLayout>
</template>
<script setup>
import { computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useTheme } from '@/Composables/useTheme';
const { currentTheme } = useTheme();
const themeColors = computed(() => ({
    background: 'var(--kotel-background)',
    card: 'var(--kotel-card)',
    border: 'var(--kotel-border)',
    textPrimary: 'var(--kotel-text-primary)',
    textSecondary: 'var(--kotel-text-secondary)',
    primary: 'var(--kotel-primary)',
    hover: currentTheme.value?.theme_mode === 'dark' ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.02)',
}));
defineProps({ user: Object, navigation: Array, schedules: { type: Array, default: () => [] } });
</script>
