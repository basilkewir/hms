<template>
    <DashboardLayout title="Guest Requests" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-6" :style="{ backgroundColor: themeColors.card }">
            <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Guest Requests</h1>
            <p class="mt-1 text-sm" :style="{ color: themeColors.textSecondary }">Housekeeping requests submitted by guests.</p>
        </div>
        <div class="shadow rounded-lg overflow-hidden" :style="{ backgroundColor: themeColors.card }">
            <table class="min-w-full divide-y" :style="{ borderColor: themeColors.border }">
                <thead :style="{ backgroundColor: themeColors.background }">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Room</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Request</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Requested At</th>
                    </tr>
                </thead>
                <tbody class="divide-y" :style="{ borderColor: themeColors.border }">
                    <tr v-if="!requests || requests.length === 0">
                        <td colspan="5" class="px-6 py-10 text-center text-sm" :style="{ color: themeColors.textSecondary }">No guest requests at this time.</td>
                    </tr>
                    <tr v-for="req in requests" :key="req.id">
                        <td class="px-6 py-4 text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ req.room_number ?? req.room_id }}</td>
                        <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textPrimary }">{{ req.request_type ?? req.description }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs" :class="req.priority === 'urgent' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'">{{ req.priority ?? 'Normal' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs" :class="req.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">{{ req.status ?? 'Pending' }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textSecondary }">{{ req.created_at }}</td>
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
defineProps({ user: Object, navigation: Array, requests: { type: Array, default: () => [] } });
</script>
