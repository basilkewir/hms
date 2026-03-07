<template>
    <DashboardLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-white">Edit Housekeeping Schedule</h2>
                <Link
                    :href="route('admin.housekeeping.schedules.show', schedule.id)"
                    class="text-kotel-yellow hover:text-kotel-yellow/80"
                >
                    ← Back to Schedule
                </Link>
            </div>
        </template>

        <div class="max-w-4xl mx-auto">
            <form @submit.prevent="submit" class="bg-kotel-dark/60 backdrop-blur-xl rounded-lg border border-kotel-yellow/20 p-6 space-y-6">
                <!-- Housekeeper Selection -->
                <div>
                    <label class="block text-sm font-medium text-kotel-yellow mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Assign to Housekeeper
                    </label>
                    <select
                        v-model="form.assigned_to"
                        class="w-full bg-kotel-dark/80 border border-kotel-yellow/30 rounded-lg px-4 py-2 text-white focus:border-kotel-yellow focus:ring-2 focus:ring-kotel-yellow/20"
                    >
                        <option value="">Unassigned</option>
                        <option
                            v-for="housekeeper in housekeepers"
                            :key="housekeeper.id"
                            :value="housekeeper.id"
                        >
                            {{ housekeeper.name }} ({{ housekeeper.employee_id }})
                        </option>
                    </select>
                    <div v-if="form.errors.assigned_to" class="text-red-400 text-sm mt-1">
                        {{ form.errors.assigned_to }}
                    </div>
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-kotel-yellow mb-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Start Date *
                        </label>
                        <div class="relative">
                            <input
                                type="date"
                                v-model="form.start_date"
                                class="w-full bg-kotel-dark/80 border border-kotel-yellow/30 rounded-lg pl-10 pr-4 py-2 text-white focus:border-kotel-yellow focus:ring-2 focus:ring-kotel-yellow/20"
                                required
                                @keydown.prevent
                                @click="$event.target.showPicker && $event.target.showPicker()"
                            />
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-kotel-yellow/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div v-if="form.errors.start_date" class="text-red-400 text-sm mt-1">
                            {{ form.errors.start_date }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-kotel-yellow mb-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            End Date *
                        </label>
                        <div class="relative">
                            <input
                                type="date"
                                v-model="form.end_date"
                                :min="form.start_date"
                                class="w-full bg-kotel-dark/80 border border-kotel-yellow/30 rounded-lg pl-10 pr-4 py-2 text-white focus:border-kotel-yellow focus:ring-2 focus:ring-kotel-yellow/20"
                                required
                                @keydown.prevent
                                @click="$event.target.showPicker && $event.target.showPicker()"
                            />
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-kotel-yellow/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div v-if="form.errors.end_date" class="text-red-400 text-sm mt-1">
                            {{ form.errors.end_date }}
                        </div>
                    </div>
                </div>

                <!-- Time Settings -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-kotel-yellow mb-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Preferred Start Time
                        </label>
                        <div class="relative">
                            <input
                                type="time"
                                v-model="form.preferred_start_time"
                                class="w-full bg-kotel-dark/80 border border-kotel-yellow/30 rounded-lg pl-10 pr-4 py-2 text-white focus:border-kotel-yellow focus:ring-2 focus:ring-kotel-yellow/20"
                                @keydown.prevent
                                @click="$event.target.showPicker && $event.target.showPicker()"
                            />
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-kotel-yellow/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-kotel-yellow mb-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Preferred End Time
                        </label>
                        <div class="relative">
                            <input
                                type="time"
                                v-model="form.preferred_end_time"
                                class="w-full bg-kotel-dark/80 border border-kotel-yellow/30 rounded-lg pl-10 pr-4 py-2 text-white focus:border-kotel-yellow focus:ring-2 focus:ring-kotel-yellow/20"
                                @keydown.prevent
                                @click="$event.target.showPicker && $event.target.showPicker()"
                            />
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-kotel-yellow/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-kotel-yellow mb-2">
                            Status
                        </label>
                        <select
                            v-model="form.status"
                            class="w-full bg-kotel-dark/80 border border-kotel-yellow/30 rounded-lg px-4 py-2 text-white focus:border-kotel-yellow focus:ring-2 focus:ring-kotel-yellow/20"
                        >
                            <option value="pending">Pending</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-kotel-yellow mb-2">
                            Active
                        </label>
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.is_active"
                                class="rounded border-kotel-yellow/30 text-kotel-yellow focus:ring-kotel-yellow"
                            />
                            <span class="text-white">Schedule is active</span>
                        </label>
                    </div>
                </div>

                <!-- Rooms List -->
                <div>
                    <label class="block text-sm font-medium text-kotel-yellow mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Room Assignments *
                    </label>
                    <div class="bg-kotel-dark/80 border border-kotel-yellow/30 rounded-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-kotel-dark/60">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-kotel-yellow text-sm font-semibold">Room</th>
                                        <th class="px-4 py-3 text-left text-kotel-yellow text-sm font-semibold">Task Type</th>
                                        <th class="px-4 py-3 text-left text-kotel-yellow text-sm font-semibold">Priority</th>
                                        <th class="px-4 py-3 text-left text-kotel-yellow text-sm font-semibold">Status</th>
                                        <th class="px-4 py-3 text-left text-kotel-yellow text-sm font-semibold">Notes</th>
                                        <th class="px-4 py-3 text-left text-kotel-yellow text-sm font-semibold">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-kotel-yellow/10">
                                    <tr v-for="(room, index) in form.rooms" :key="room.id" class="hover:bg-kotel-dark/40">
                                        <td class="px-4 py-3">
                                            <span class="text-white font-medium">{{ getRoomNumber(room.id) }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <select
                                                v-model="room.task_type"
                                                class="bg-kotel-dark border border-kotel-yellow/30 rounded px-2 py-1 text-white text-sm focus:border-kotel-yellow"
                                            >
                                                <option value="checkout">Checkout</option>
                                                <option value="cleaning">Cleaning</option>
                                                <option value="check_cleaning">Check & Clean</option>
                                                <option value="stayover">Stayover</option>
                                                <option value="deep_clean">Deep Clean</option>
                                                <option value="inspection">Inspection</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            <select
                                                v-model="room.priority"
                                                class="bg-kotel-dark border border-kotel-yellow/30 rounded px-2 py-1 text-white text-sm focus:border-kotel-yellow"
                                            >
                                                <option value="low">Low</option>
                                                <option value="medium">Medium</option>
                                                <option value="high">High</option>
                                                <option value="urgent">Urgent</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            <select
                                                v-model="room.status"
                                                class="bg-kotel-dark border border-kotel-yellow/30 rounded px-2 py-1 text-white text-sm focus:border-kotel-yellow"
                                            >
                                                <option value="pending">Pending</option>
                                                <option value="in_progress">In Progress</option>
                                                <option value="completed">Completed</option>
                                                <option value="skipped">Skipped</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input
                                                type="text"
                                                v-model="room.notes"
                                                class="bg-kotel-dark border border-kotel-yellow/30 rounded px-2 py-1 text-white text-sm focus:border-kotel-yellow w-full"
                                                placeholder="Notes..."
                                            />
                                        </td>
                                        <td class="px-4 py-3">
                                            <button
                                                type="button"
                                                @click="removeRoom(index)"
                                                class="text-red-400 hover:text-red-300"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-if="form.rooms.length === 0" class="p-4 text-center text-kotel-yellow/60">
                            No rooms assigned. Add rooms below.
                        </div>
                    </div>
                    <div v-if="form.errors.rooms" class="text-red-400 text-sm mt-1">
                        {{ form.errors.rooms }}
                    </div>
                </div>

                <!-- Add Room -->
                <div class="bg-kotel-dark/40 rounded-lg p-4 border border-kotel-yellow/10">
                    <label class="block text-sm font-medium text-kotel-yellow mb-2">Add Room</label>
                    <div class="flex gap-2">
                        <select
                            v-model="selectedRoomId"
                            class="flex-1 bg-kotel-dark/80 border border-kotel-yellow/30 rounded-lg px-4 py-2 text-white focus:border-kotel-yellow"
                        >
                            <option value="">Select a room to add</option>
                            <option
                                v-for="room in availableRooms"
                                :key="room.id"
                                :value="room.id"
                            >
                                {{ room.room_number }} - {{ room.status }}
                            </option>
                        </select>
                        <button
                            type="button"
                            @click="addRoom"
                            :disabled="!selectedRoomId"
                            class="px-4 py-2 bg-kotel-yellow/20 text-kotel-yellow border border-kotel-yellow/30 rounded-lg hover:bg-kotel-yellow/30 transition disabled:opacity-50"
                        >
                            Add
                        </button>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-kotel-yellow mb-2">
                        Notes
                    </label>
                    <textarea
                        v-model="form.notes"
                        rows="3"
                        class="w-full bg-kotel-dark/80 border border-kotel-yellow/30 rounded-lg px-4 py-2 text-white focus:border-kotel-yellow focus:ring-2 focus:ring-kotel-yellow/20"
                        placeholder="Any special notes..."
                    ></textarea>
                </div>

                <!-- Instructions -->
                <div>
                    <label class="block text-sm font-medium text-kotel-yellow mb-2">
                        Instructions
                    </label>
                    <textarea
                        v-model="form.instructions"
                        rows="3"
                        class="w-full bg-kotel-dark/80 border border-kotel-yellow/30 rounded-lg px-4 py-2 text-white focus:border-kotel-yellow focus:ring-2 focus:ring-kotel-yellow/20"
                        placeholder="Instructions for the housekeeper..."
                    ></textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4">
                    <Link
                        :href="route('admin.housekeeping.schedules.show', schedule.id)"
                        class="px-6 py-2 border border-kotel-yellow/30 text-kotel-yellow rounded-lg hover:bg-kotel-yellow/10 transition"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 bg-kotel-yellow text-kotel-dark rounded-lg font-semibold hover:bg-kotel-yellow/90 transition disabled:opacity-50"
                    >
                        {{ form.processing ? 'Updating...' : 'Update Schedule' }}
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    schedule: Object,
    rooms: Array,
    housekeepers: Array,
});

const selectedRoomId = ref('');

// Build form data from schedule
const form = useForm({
    assigned_to: props.schedule.assigned_to || '',
    start_date: props.schedule.start_date || '',
    end_date: props.schedule.end_date || '',
    preferred_start_time: props.schedule.preferred_start_time || '',
    preferred_end_time: props.schedule.preferred_end_time || '',
    status: props.schedule.status || 'pending',
    is_active: props.schedule.is_active ?? true,
    notes: props.schedule.notes || '',
    instructions: props.schedule.instructions || '',
    rooms: props.schedule.rooms ? props.schedule.rooms.map(room => ({
        id: room.id,
        task_type: room.pivot?.task_type || 'cleaning',
        priority: room.pivot?.priority || 'medium',
        status: room.pivot?.status || 'pending',
        notes: room.pivot?.notes || '',
    })) : [],
});

// Get room numbers from rooms list
const getRoomNumber = (roomId) => {
    const room = props.rooms.find(r => r.id === roomId);
    return room ? room.room_number : roomId;
};

// Available rooms for adding (exclude already added)
const availableRooms = computed(() => {
    const addedIds = form.rooms.map(r => r.id);
    return props.rooms.filter(room => !addedIds.includes(room.id));
});

// Add room to form
const addRoom = () => {
    if (selectedRoomId.value && !form.rooms.find(r => r.id === selectedRoomId.value)) {
        form.rooms.push({
            id: parseInt(selectedRoomId.value),
            task_type: 'cleaning',
            priority: 'medium',
            status: 'pending',
            notes: '',
        });
        selectedRoomId.value = '';
    }
};

// Remove room from form
const removeRoom = (index) => {
    form.rooms.splice(index, 1);
};

// Submit form
const submit = () => {
    form.put(route('admin.housekeeping.schedules.update', props.schedule.id), {
        onSuccess: () => {
            // Success notification will be handled by the layout
        },
        onError: (errors) => {
            console.error('Form errors:', errors);
        },
    });
};
</script>
