<template>
    <DashboardLayout title="Edit Task" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-6" :style="{ backgroundColor: themeColors.card }">
            <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Edit Task</h1>
        </div>
        <div class="shadow rounded-lg p-6" :style="{ backgroundColor: themeColors.card }">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Room</label>
                    <input v-model="form.room_id" type="text" class="w-full rounded-md border px-3 py-2 text-sm" :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Task Type</label>
                    <select v-model="form.task_type" class="w-full rounded-md border px-3 py-2 text-sm" :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }">
                        <option value="cleaning">Cleaning</option>
                        <option value="linen_change">Linen Change</option>
                        <option value="deep_cleaning">Deep Cleaning</option>
                        <option value="inspection">Inspection</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Status</label>
                    <select v-model="form.status" class="w-full rounded-md border px-3 py-2 text-sm" :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Due Date</label>
                    <input v-model="form.due_date" type="date" class="w-full rounded-md border px-3 py-2 text-sm" :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Notes</label>
                    <textarea v-model="form.notes" rows="3" class="w-full rounded-md border px-3 py-2 text-sm" :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"></textarea>
                </div>
                <div class="flex space-x-3">
                    <button type="submit" class="px-4 py-2 rounded-md text-white text-sm font-medium" :style="{ backgroundColor: themeColors.primary }">Update Task</button>
                    <Link :href="`/housekeeping/tasks/${task?.id}`" class="px-4 py-2 rounded-md border text-sm font-medium" :style="{ color: themeColors.textSecondary, borderColor: themeColors.border }">Cancel</Link>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>
<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useTheme } from '@/Composables/useTheme';
const { themeColors } = useTheme();
const props = defineProps({ user: Object, navigation: Array, task: Object });
const form = useForm({ room_id: props.task?.room_id ?? '', task_type: props.task?.task_type ?? 'cleaning', status: props.task?.status ?? 'pending', due_date: props.task?.due_date ?? '', notes: props.task?.notes ?? '' });
const submit = () => form.put(`/housekeeping/tasks/${props.task?.id}`);
</script>
