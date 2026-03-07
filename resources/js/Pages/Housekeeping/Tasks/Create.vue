<template>
    <DashboardLayout title="Create Task" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-6" :style="{ backgroundColor: themeColors.card }">
            <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Create Housekeeping Task</h1>
        </div>
        <div class="shadow rounded-lg p-6" :style="{ backgroundColor: themeColors.card }">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Room</label>
                    <input v-model="form.room_id" type="text" class="w-full rounded-md border px-3 py-2 text-sm" :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }" placeholder="Room number or ID" />
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
                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Due Date</label>
                    <input v-model="form.due_date" type="date" class="w-full rounded-md border px-3 py-2 text-sm" :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Notes</label>
                    <textarea v-model="form.notes" rows="3" class="w-full rounded-md border px-3 py-2 text-sm" :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }"></textarea>
                </div>
                <div class="flex space-x-3">
                    <button type="submit" class="px-4 py-2 rounded-md text-white text-sm font-medium" :style="{ backgroundColor: themeColors.primary }">Create Task</button>
                    <Link href="/housekeeping/tasks" class="px-4 py-2 rounded-md border text-sm font-medium" :style="{ color: themeColors.textSecondary, borderColor: themeColors.border }">Cancel</Link>
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
defineProps({ user: Object, navigation: Array });
const form = useForm({ room_id: '', task_type: 'cleaning', due_date: '', notes: '' });
const submit = () => form.post('/housekeeping/tasks');
</script>
