<template>
    <DashboardLayout title="Room Status" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-6" :style="{ backgroundColor: themeColors.card }">
            <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Room Status</h1>
            <p class="mt-1 text-sm" :style="{ color: themeColors.textSecondary }">Current cleaning and occupancy status for all rooms.</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div v-for="stat in statusStats" :key="stat.label" class="rounded-lg shadow p-4 text-center" :style="{ backgroundColor: themeColors.card }">
                <p class="text-3xl font-bold" :style="{ color: stat.color }">{{ stat.count }}</p>
                <p class="text-sm mt-1" :style="{ color: themeColors.textSecondary }">{{ stat.label }}</p>
            </div>
        </div>
        <div class="shadow rounded-lg overflow-hidden" :style="{ backgroundColor: themeColors.card }">
            <table class="min-w-full divide-y" :style="{ borderColor: themeColors.border }">
                <thead :style="{ backgroundColor: themeColors.background }">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Room</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Occupancy</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Cleaning Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y" :style="{ borderColor: themeColors.border }">
                    <tr v-if="!rooms || rooms.length === 0">
                        <td colspan="4" class="px-6 py-10 text-center text-sm" :style="{ color: themeColors.textSecondary }">No room data available.</td>
                    </tr>
                    <tr v-for="room in rooms" :key="room.id">
                        <td class="px-6 py-4 text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ room.room_number ?? room.number }}</td>
                        <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textSecondary }">{{ room.type }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs" :class="room.is_occupied ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'">{{ room.is_occupied ? 'Occupied' : 'Vacant' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs" :class="cleaningClass(room.cleaning_status)">{{ room.cleaning_status ?? 'Clean' }}</span>
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
import { computed } from 'vue';
const { themeColors } = useTheme();
const props = defineProps({ user: Object, navigation: Array, rooms: { type: Array, default: () => [] } });
const statusStats = computed(() => [
    { label: 'Clean', count: props.rooms.filter(r => r.cleaning_status === 'clean').length, color: '#10b981' },
    { label: 'Dirty', count: props.rooms.filter(r => r.cleaning_status === 'dirty').length, color: '#ef4444' },
    { label: 'In Progress', count: props.rooms.filter(r => r.cleaning_status === 'in_progress').length, color: '#f59e0b' },
    { label: 'Inspecting', count: props.rooms.filter(r => r.cleaning_status === 'inspecting').length, color: '#6366f1' },
]);
const cleaningClass = (status) => ({
    clean: 'bg-green-100 text-green-800', dirty: 'bg-red-100 text-red-800',
    in_progress: 'bg-yellow-100 text-yellow-800', inspecting: 'bg-purple-100 text-purple-800',
})[status] ?? 'bg-gray-100 text-gray-800';
</script>
