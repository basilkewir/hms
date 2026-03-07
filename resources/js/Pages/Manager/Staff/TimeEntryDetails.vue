<template>
    <DashboardLayout title="Time Entry Details" :user="user">
        <div class="shadow rounded-lg p-6 mb-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Time Entry Details</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Review and update the selected time entry.</p>
                </div>
                <Link
                    :href="route('manager.staff.time-tracking')"
                    class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity border"
                    :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"
                >
                    Back to Time Tracking
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Entry Information</h3>
                <div class="space-y-3 text-sm" :style="{ color: themeColors.textSecondary }">
                    <div><span class="font-semibold">Employee:</span> {{ timeEntry.employee_name }}</div>
                    <div><span class="font-semibold">Employee ID:</span> {{ timeEntry.employee_id }}</div>
                    <div><span class="font-semibold">Department:</span> {{ timeEntry.department }}</div>
                    <div><span class="font-semibold">Work Date:</span> {{ timeEntry.work_date }}</div>
                    <div><span class="font-semibold">Shift:</span> {{ timeEntry.shift_name }}</div>
                    <div><span class="font-semibold">Clock In:</span> {{ timeEntry.clock_in || '-' }}</div>
                    <div><span class="font-semibold">Clock Out:</span> {{ timeEntry.clock_out || '-' }}</div>
                    <div><span class="font-semibold">Break:</span> {{ timeEntry.break_start || '-' }} - {{ timeEntry.break_end || '-' }}</div>
                    <div><span class="font-semibold">Regular Hours:</span> {{ timeEntry.regular_hours }}</div>
                    <div><span class="font-semibold">Overtime Hours:</span> {{ timeEntry.overtime_hours }}</div>
                    <div><span class="font-semibold">Total Hours:</span> {{ timeEntry.total_hours }}</div>
                    <div><span class="font-semibold">Late:</span> {{ timeEntry.is_late ? `Yes (${timeEntry.late_minutes} min)` : 'No' }}</div>
                    <div><span class="font-semibold">Early Out:</span> {{ timeEntry.is_early_out ? `Yes (${timeEntry.early_out_minutes} min)` : 'No' }}</div>
                </div>
            </div>

            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Edit Entry</h3>
                <form @submit.prevent="submit">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Clock In</label>
                            <input v-model="form.clock_in_time" type="time" class="w-full rounded-md border px-3 py-2 focus:outline-none" :style="inputStyle" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Clock Out</label>
                            <input v-model="form.clock_out_time" type="time" class="w-full rounded-md border px-3 py-2 focus:outline-none" :style="inputStyle" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Break Start</label>
                            <input v-model="form.break_start_time" type="time" class="w-full rounded-md border px-3 py-2 focus:outline-none" :style="inputStyle" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Break End</label>
                            <input v-model="form.break_end_time" type="time" class="w-full rounded-md border px-3 py-2 focus:outline-none" :style="inputStyle" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Manager Notes</label>
                            <textarea v-model="form.admin_notes" rows="3" class="w-full rounded-md border px-3 py-2 focus:outline-none" :style="inputStyle"></textarea>
                        </div>
                        <button
                            type="submit"
                            class="w-full px-4 py-2 rounded-md hover:opacity-90 transition-opacity"
                            :style="{ backgroundColor: themeColors.primary, color: '#000' }"
                        >
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme'

const props = defineProps({
    user: Object,
    timeEntry: Object,
})

const form = useForm({
    clock_in_time: '',
    clock_out_time: '',
    break_start_time: '',
    break_end_time: '',
    admin_notes: props.timeEntry?.admin_notes || '',
})

const { currentTheme } = useTheme()

const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    primary: `var(--kotel-primary)`,
    secondary: `var(--kotel-secondary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)'
}))

const inputStyle = computed(() => ({
    backgroundColor: themeColors.value.background,
    borderColor: themeColors.value.border,
    color: themeColors.value.textPrimary,
}))

const submit = () => {
    form.put(route('manager.staff.time-tracking.update', { timeEntry: props.timeEntry.id }))
}
</script>
