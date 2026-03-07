<template>
    <DashboardLayout title="Task Details" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-6" :style="{ backgroundColor: themeColors.card }">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Task Details</h1>
                <div class="flex space-x-3">
                    <Link :href="`/housekeeping/tasks/${task?.id}/edit`" class="px-4 py-2 rounded-md text-white text-sm font-medium" :style="{ backgroundColor: themeColors.primary }">Edit</Link>
                    <Link href="/housekeeping/tasks" class="px-4 py-2 rounded-md border text-sm font-medium" :style="{ color: themeColors.textSecondary, borderColor: themeColors.border }">Back</Link>
                </div>
            </div>
        </div>
        <div class="shadow rounded-lg p-6" :style="{ backgroundColor: themeColors.card }">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="(value, label) in details" :key="label">
                    <dt class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">{{ label }}</dt>
                    <dd class="mt-1 text-sm font-semibold" :style="{ color: themeColors.textPrimary }">{{ value ?? '—' }}</dd>
                </div>
            </dl>
        </div>
    </DashboardLayout>
</template>
<script setup>
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useTheme } from '@/Composables/useTheme';
import { computed } from 'vue';
const { themeColors } = useTheme();
const props = defineProps({ user: Object, navigation: Array, task: Object });
const details = computed(() => ({
    'Room': props.task?.room_number ?? props.task?.room_id,
    'Task Type': props.task?.task_type,
    'Status': props.task?.status,
    'Assigned To': props.task?.assigned_to,
    'Due Date': props.task?.due_date,
    'Notes': props.task?.notes,
    'Created': props.task?.created_at,
}));
</script>
