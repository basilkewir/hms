<template>
    <DashboardLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-white">Create Housekeeping Schedule</h2>
                <Link
                    :href="route('admin.housekeeping-schedules.index')"
                    class="text-kotel-yellow hover:text-kotel-yellow/80"
                >
                    ← Back to Schedules
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
                        Assign to Housekeeper *
                    </label>
                    <select
                        v-model="form.assigned_to"
                        class="w-full bg-kotel-dark/80 border border-kotel-yellow/30 rounded-lg px-4 py-2 text-white focus:border-kotel-yellow focus:ring-2 focus:ring-kotel-yellow/20"
                        required
                    >
                        <option value="">Select a housekeeper</option>
                        <option
                            v-for="housekeeper in housekeepers"
                            :key="housekeeper.id"
                            :value="housekeeper.id"
                        >
                            {{ housekeeper.first_name }} {{ housekeeper.last_name }} ({{ housekeeper.employee_id }})
                        </option>
                    </select>
                    <div v-if="form.errors.assigned_to" class="text-red-400 text-sm mt-1">
                        {{ form.errors.assigned_to }}
                    </div>
                </div>

                <!-- Room Selection -->
                <div>
                    <label class="block text-sm font-medium text-kotel-yellow mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Select Rooms *
                    </label>
                    <div class="bg-kotel-dark/80 border border-kotel-yellow/30 rounded-lg p-4 max-h-64 overflow-y-auto">
                        <div class="grid grid-cols-3 md:grid-cols-4 gap-3">
                            <label
                                v-for="room in rooms"
                                :key="room.id"
                                class="flex items-center space-x-2 cursor-pointer hover:bg-kotel-dark/40 p-2 rounded"
                            >
                                <input
                                    type="checkbox"
                                    :value="room.room_number"
                                    v-model="form.room_numbers"
                                    class="rounded border-kotel-yellow/30 text-kotel-yellow focus:ring-kotel-yellow"
                                />
                                <span class="text-white text-sm">{{ room.room_number }}</span>
                            </label>
                        </div>
                    </div>
                    <div v-if="form.errors.room_numbers" class="text-red-400 text-sm mt-1">
                        {{ form.errors.room_numbers }}
                    </div>
                    <div class="text-kotel-yellow/60 text-sm mt-2">
                        Selected: {{ form.room_numbers.length }} room(s)
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

                <!-- Preferred Start Time -->
                <div>
                    <label class="block text-sm font-medium text-kotel-yellow mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Preferred Start Time (Optional)
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
                    <div v-if="form.errors.preferred_start_time" class="text-red-400 text-sm mt-1">
                        {{ form.errors.preferred_start_time }}
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-kotel-yellow mb-2">
                        Notes (Optional)
                    </label>
                    <textarea
                        v-model="form.notes"
                        rows="4"
                        class="w-full bg-kotel-dark/80 border border-kotel-yellow/30 rounded-lg px-4 py-2 text-white focus:border-kotel-yellow focus:ring-2 focus:ring-kotel-yellow/20"
                        placeholder="Any special instructions or notes..."
                    ></textarea>
                    <div v-if="form.errors.notes" class="text-red-400 text-sm mt-1">
                        {{ form.errors.notes }}
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4">
                    <Link
                        :href="route('admin.housekeeping-schedules.index')"
                        class="px-6 py-2 border border-kotel-yellow/30 text-kotel-yellow rounded-lg hover:bg-kotel-yellow/10 transition"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 bg-kotel-yellow text-kotel-dark rounded-lg font-semibold hover:bg-kotel-yellow/90 transition disabled:opacity-50"
                    >
                        {{ form.processing ? 'Creating...' : 'Create Schedule' }}
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    housekeepers: Array,
    rooms: Array,
});

const form = useForm({
    assigned_to: '',
    room_numbers: [],
    start_date: '',
    end_date: '',
    preferred_start_time: '',
    notes: '',
});

const submit = () => {
    form.post(route('admin.housekeeping-schedules.store'), {
        onSuccess: (page) => {
            // Use page.props.flash.success if available, otherwise use alert
            if (page.props.flash?.success) {
                // Success notification will be handled by the layout
                window.location.href = route('admin.housekeeping-schedules.index');
            } else {
                alert('Schedule created successfully!');
                window.location.href = route('admin.housekeeping-schedules.index');
            }
        },
        onError: (errors) => {
            console.error('Form errors:', errors);
            alert('Failed to create schedule. Please check the form.');
        },
    });
};
</script>
