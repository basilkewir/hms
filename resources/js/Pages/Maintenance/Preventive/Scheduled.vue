<template>
    <DashboardLayout title="Scheduled Maintenance" :user="user">
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" class="shadow rounded-lg p-6 mb-6 border">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">Scheduled Maintenance</h1>
                    <p :style="{ color: themeColors.textSecondary }" class="mt-2">Upcoming maintenance tasks.</p>
                </div>
                <button
                    type="button"
                    @click="openCreateModal"
                    :style="{ backgroundColor: themeColors.primary, color: '#000' }"
                    class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity"
                >
                    Add Preventive Maintenance
                </button>
            </div>
        </div>

        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" class="shadow rounded-lg overflow-hidden border">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th :style="{ color: themeColors.textSecondary }" class="px-6 py-3 text-left text-xs font-medium uppercase">Task</th>
                            <th :style="{ color: themeColors.textSecondary }" class="px-6 py-3 text-left text-xs font-medium uppercase">Equipment</th>
                            <th :style="{ color: themeColors.textSecondary }" class="px-6 py-3 text-left text-xs font-medium uppercase">Due Date</th>
                            <th :style="{ color: themeColors.textSecondary }" class="px-6 py-3 text-left text-xs font-medium uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="tasks.length === 0">
                            <td colspan="4" :style="{ color: themeColors.textSecondary }" class="px-6 py-6 text-center">No scheduled tasks.</td>
                        </tr>
                        <tr
                            v-for="task in tasks"
                            :key="task.id"
                            :style="hoveredRow === task.id ? { backgroundColor: themeColors.hover } : {}"
                            @mouseenter="hoveredRow = task.id"
                            @mouseleave="hoveredRow = null"
                            class="transition-colors"
                        >
                            <td :style="{ color: themeColors.textPrimary }" class="px-6 py-4 text-sm">{{ task.description }}</td>
                            <td :style="{ color: themeColors.textSecondary }" class="px-6 py-4 text-sm">{{ task.equipment }}</td>
                            <td :style="{ color: themeColors.textSecondary }" class="px-6 py-4 text-sm">{{ task.due_date || 'N/A' }}</td>
                            <td class="px-6 py-4">
                                <span
                                    :style="getStatusPillStyle(task.status)"
                                    class="px-2 py-1 rounded-full text-xs font-medium"
                                >
                                    {{ formatStatus(task.status) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <DialogModal :show="showCreateModal" @close="closeCreateModal">
            <template #title>
                Schedule Preventive Maintenance
            </template>

            <template #content>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label :style="{ color: themeColors.textSecondary }" class="block text-sm font-medium mb-2">Title *</label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"
                            class="w-full border rounded-md px-3 py-2 focus:outline-none"
                        />
                        <p v-if="form.errors.title" :style="{ color: themeColors.danger }" class="text-xs mt-1">{{ form.errors.title }}</p>
                    </div>

                    <div>
                        <label :style="{ color: themeColors.textSecondary }" class="block text-sm font-medium mb-2">Equipment / Category</label>
                        <input
                            v-model="form.category"
                            type="text"
                            placeholder="e.g. AC, Generator, Elevator"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"
                            class="w-full border rounded-md px-3 py-2 focus:outline-none"
                        />
                        <p v-if="form.errors.category" :style="{ color: themeColors.danger }" class="text-xs mt-1">{{ form.errors.category }}</p>
                    </div>

                    <div>
                        <label :style="{ color: themeColors.textSecondary }" class="block text-sm font-medium mb-2">Room</label>
                        <select
                            v-model="form.room_id"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"
                            class="w-full border rounded-md px-3 py-2 focus:outline-none"
                        >
                            <option value="">No room</option>
                            <option v-for="room in rooms" :key="room.id" :value="room.id">Room {{ room.room_number }}</option>
                        </select>
                        <p v-if="form.errors.room_id" :style="{ color: themeColors.danger }" class="text-xs mt-1">{{ form.errors.room_id }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label :style="{ color: themeColors.textSecondary }" class="block text-sm font-medium mb-2">Scheduled Date *</label>
                            <DatePicker v-model="form.scheduled_date" required />
                            <p v-if="form.errors.scheduled_date" :style="{ color: themeColors.danger }" class="text-xs mt-1">{{ form.errors.scheduled_date }}</p>
                        </div>
                        <div>
                            <label :style="{ color: themeColors.textSecondary }" class="block text-sm font-medium mb-2">Scheduled Time</label>
                            <TimePicker v-model="form.scheduled_time" />
                            <p v-if="form.errors.scheduled_time" :style="{ color: themeColors.danger }" class="text-xs mt-1">{{ form.errors.scheduled_time }}</p>
                        </div>
                    </div>

                    <div>
                        <label :style="{ color: themeColors.textSecondary }" class="block text-sm font-medium mb-2">Description</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"
                            class="w-full border rounded-md px-3 py-2 focus:outline-none"
                        />
                        <p v-if="form.errors.description" :style="{ color: themeColors.danger }" class="text-xs mt-1">{{ form.errors.description }}</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4" :style="{ borderColor: themeColors.border }">
                        <button
                            type="button"
                            @click="closeCreateModal"
                            :style="{ backgroundColor: themeColors.border, color: themeColors.textPrimary }"
                            class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            :style="{ backgroundColor: themeColors.primary, color: '#000' }"
                            class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity disabled:opacity-60"
                        >
                            {{ form.processing ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </form>
            </template>
        </DialogModal>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DialogModal from '@/Components/DialogModal.vue'
import DatePicker from '@/Components/DatePicker.vue'
import TimePicker from '@/Components/TimePicker.vue'
import { useTheme } from '@/Composables/useTheme'

const props = defineProps({
    user: Object,
    tasks: Array,
    rooms: Array,
})

const tasks = computed(() => props.tasks || [])
const rooms = computed(() => props.rooms || [])

const hoveredRow = ref(null)
const showCreateModal = ref(false)

const { currentTheme } = useTheme()

const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    primary: `var(--kotel-primary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)'
}))

const storeUrl = computed(() => {
    const roleNames = (props.user?.roles || []).map(r => r?.name).filter(Boolean)
    if (roleNames.includes('admin')) return '/admin/maintenance/preventive'
    if (roleNames.includes('manager')) return '/manager/maintenance/preventive'
    return '/maintenance/preventive'
})

const form = useForm({
    title: '',
    description: '',
    category: '',
    room_id: '',
    scheduled_date: '',
    scheduled_time: '',
})

const openCreateModal = () => { showCreateModal.value = true }
const closeCreateModal = () => {
    showCreateModal.value = false
    form.reset()
    form.clearErrors()
}

const submit = () => {
    form.post(storeUrl.value, {
        preserveScroll: true,
        onSuccess: () => closeCreateModal(),
    })
}

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800',
        overdue: 'bg-red-100 text-red-800',
        completed: 'bg-green-100 text-green-800',
    }
    return colors[(status || '').toLowerCase()] || 'bg-gray-100 text-gray-800'
}

const getStatusPillStyle = (status) => {
    const key = (status || '').toLowerCase()

    if (key === 'completed') return { backgroundColor: themeColors.value.success, color: '#000' }
    if (key === 'overdue') return { backgroundColor: themeColors.value.danger, color: '#000' }
    if (key === 'pending') return { backgroundColor: themeColors.value.warning, color: '#000' }

    return { backgroundColor: themeColors.value.border, color: themeColors.value.textPrimary }
}

const formatStatus = (status) => {
    return status ? status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A'
}
</script>
