<template>
    <DashboardLayout title="Edit Housekeeping Task">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Edit Housekeeping Task</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Update the housekeeping task details and assignments.</p>
                </div>
                <Link href="/manager/housekeeping-tasks"
                      class="px-4 py-2 rounded-md transition-colors"
                      :style="{ 
                          backgroundColor: themeColors.secondary,
                          color: themeColors.textPrimary 
                      }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                    <ArrowLeftIcon class="h-4 w-4 mr-2 inline" />
                    Back
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Task Information -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Task Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Room *</label>
                            <select v-model="form.room_id" required
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="">Select Room</option>
                                <option v-for="room in rooms" :key="room.id" :value="room.id">
                                    Room {{ room.room_number }}
                                    — {{ room.room_type?.name || 'N/A' }}
                                    [{{ room.status }}]
                                    · HK: {{ room.housekeeping_status || 'unknown' }}
                                </option>
                            </select>
                            <div v-if="form.errors.room_id" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                {{ form.errors.room_id }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Assigned To</label>
                            <select v-model="form.assigned_to"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option :value="null">Unassigned</option>
                                <option v-for="housekeeper in housekeepers" :key="housekeeper.id" :value="housekeeper.id">
                                    {{ housekeeper.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Task Type *</label>
                            <select v-model="form.task_type" required
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="checkout">Checkout Cleaning</option>
                                <option value="cleaning">Cleaning</option>
                                <option value="check_cleaning">Check Cleaning</option>
                                <option value="stayover">Stayover Service</option>
                                <option value="deep_clean">Deep Clean</option>
                                <option value="inspection">Inspection</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                            <div v-if="form.errors.task_type" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                {{ form.errors.task_type }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Priority</label>
                            <select v-model="form.priority"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="low">Low</option>
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Scheduled Date *</label>
                            <DatePicker v-model="form.scheduled_date" required />
                            <div v-if="form.errors.scheduled_date" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                {{ form.errors.scheduled_date }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Scheduled Time</label>
                            <TimePicker v-model="form.scheduled_time" />
                        </div>
                    </div>
                </div>

                <!-- Duration & Instructions -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Duration & Instructions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Estimated Duration (minutes)</label>
                            <input type="number" v-model="form.estimated_minutes" min="1"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="e.g., 30">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Status</label>
                            <select v-model="form.status"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="skipped">Skipped</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6">
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Special Instructions</label>
                        <textarea v-model="form.instructions" rows="4"
                                  class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                  :style="{ 
                                      backgroundColor: themeColors.background,
                                      borderColor: themeColors.border,
                                      color: themeColors.textPrimary,
                                      borderWidth: '1px',
                                      borderStyle: 'solid'
                                  }"
                                  placeholder="Enter any special instructions or notes for this task..."></textarea>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t"
                     :style="{ 
                         borderColor: themeColors.border,
                         borderTopWidth: '1px',
                         borderTopStyle: 'solid'
                     }">
                    <Link href="/manager/housekeeping-tasks"
                          class="px-4 py-2 rounded-md transition-colors font-medium"
                          :style="{ 
                              backgroundColor: themeColors.secondary,
                              color: themeColors.textPrimary 
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        Cancel
                    </Link>
                    <button type="submit" 
                            :disabled="form.processing"
                            class="px-4 py-2 rounded-md transition-colors text-white font-medium"
                            :style="{ 
                                backgroundColor: form.processing ? themeColors.secondary : themeColors.primary,
                            }"
                            @mouseenter="!form.processing && ($event.target.style.backgroundColor = themeColors.hover)"
                            @mouseleave="!form.processing && ($event.target.style.backgroundColor = themeColors.primary)">
                        <span v-if="form.processing">Saving...</span>
                        <span v-else>Update Task</span>
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import DatePicker from '@/Components/DatePicker.vue'
import TimePicker from '@/Components/TimePicker.vue'
import { useTheme } from '@/Composables/useTheme.js'

// Initialize theme
const { loadTheme } = useTheme()
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
    hover: `rgba(255, 255, 255, 0.1)`
}))

// Load theme on mount
loadTheme()

const props = defineProps({
    task: Object,
    rooms: Array,
    housekeepers: Array,
})

// Debug: Log what we're receiving
console.log('Task data received:', props.task)
console.log('Housekeepers:', props.housekeepers)
console.log('Task assigned_to value:', props.task.assigned_to)

const form = useForm({
    room_id: props.task.room_id,
    assigned_to: props.task.assigned_to || null,
    task_type: props.task.task_type,
    priority: props.task.priority,
    scheduled_date: props.task.scheduled_date ? String(props.task.scheduled_date).slice(0, 10) : '',
    scheduled_time: props.task.scheduled_time ? String(props.task.scheduled_time).slice(0, 5) : '',
    estimated_minutes: props.task.estimated_minutes,
    status: props.task.status,
    instructions: props.task.instructions || '',
})

const submit = () => {
    form.put(`/manager/housekeeping-tasks/${props.task.id}`)
}
</script>

<style scoped>
input[type="date"],
input[type="time"] {
    appearance: auto !important;
    -webkit-appearance: auto !important;
    -moz-appearance: auto !important;
    -ms-appearance: auto !important;
}

input[type="date"]::-webkit-calendar-picker-indicator,
input[type="time"]::-webkit-calendar-picker-indicator {
    display: block !important;
    cursor: pointer !important;
}

input[type="date"]::-webkit-inner-spin-button,
input[type="time"]::-webkit-inner-spin-button {
    display: none !important;
}

input[type="date"]::-webkit-clear-button,
input[type="time"]::-webkit-clear-button {
    display: none !important;
}
</style>
