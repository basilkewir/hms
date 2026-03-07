<template>
    <DashboardLayout title="Edit Reservation" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Edit Reservation</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Reservation #{{ reservation.reservation_number }}</p>
                </div>
                <Link :href="route('front-desk.reservations.show', reservation.id)"
                      class="px-4 py-2 rounded-md transition-colors font-medium"
                      :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }">
                    <ArrowLeftIcon class="h-4 w-4 mr-2 inline" />
                    Back
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Guest & Booking -->
                <div>
                    <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Guest & Booking</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Guest *</label>
                            <select v-model="form.guest_id" required
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="inputStyle">
                                <option value="">Select Guest</option>
                                <option v-for="guest in guests" :key="guest.id" :value="guest.id">
                                    {{ guest.first_name }} {{ guest.last_name }}{{ guest.email ? ' (' + guest.email + ')' : '' }}
                                </option>
                            </select>
                            <div v-if="form.errors.guest_id" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.guest_id }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Booking Source</label>
                            <select v-model="form.booking_source"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="inputStyle">
                                <option value="">Select Source</option>
                                <option v-for="(label, value) in bookingSources" :key="value" :value="value">{{ label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Room Type</label>
                            <select v-model="form.room_type_id"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="inputStyle">
                                <option value="">Select Room Type</option>
                                <option v-for="rt in roomTypes" :key="rt.id" :value="rt.id">{{ rt.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Status *</label>
                            <select v-model="form.status" required
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="inputStyle">
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="checked_in">Checked In</option>
                                <option value="checked_out">Checked Out</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="no_show">No Show</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Dates & Occupancy -->
                <div>
                    <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Dates & Occupancy</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Check-in Date *</label>
                            <input type="date" v-model="form.check_in_date" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="inputStyle">
                            <div v-if="form.errors.check_in_date" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.check_in_date }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Check-out Date *</label>
                            <input type="date" v-model="form.check_out_date" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="inputStyle">
                            <div v-if="form.errors.check_out_date" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.check_out_date }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Adults *</label>
                            <input type="number" v-model="form.number_of_adults" min="1" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="inputStyle">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Children</label>
                            <input type="number" v-model="form.number_of_children" min="0"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="inputStyle">
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                <div>
                    <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Pricing</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Room Rate</label>
                            <input type="number" v-model="form.room_rate" min="0" step="0.01"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="inputStyle">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Booking Reference</label>
                            <input type="text" v-model="form.booking_reference"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="inputStyle">
                        </div>
                    </div>
                </div>

                <!-- Special Requests -->
                <div>
                    <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Special Requests</label>
                    <textarea v-model="form.special_requests" rows="3"
                              class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                              :style="inputStyle"></textarea>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t" :style="{ borderColor: themeColors.border }">
                    <Link :href="route('front-desk.reservations.show', reservation.id)"
                          class="px-6 py-2 rounded-md font-medium"
                          :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                            class="px-6 py-2 rounded-md font-medium text-white disabled:opacity-50"
                            :style="{ backgroundColor: themeColors.primary }">
                        <span v-if="form.processing">Updating...</span>
                        <span v-else>Update Reservation</span>
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
import { useTheme } from '@/Composables/useTheme.js'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background:    `var(--kotel-background)`,
    card:          `var(--kotel-card)`,
    border:        `var(--kotel-border)`,
    textPrimary:   `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    primary:       `var(--kotel-primary)`,
    secondary:     `var(--kotel-secondary)`,
    danger:        `var(--kotel-danger)`,
    hover:         `rgba(255, 255, 255, 0.1)`,
}))
loadTheme()

const inputStyle = computed(() => ({
    backgroundColor: themeColors.value.background,
    borderColor:     themeColors.value.border,
    color:           themeColors.value.textPrimary,
    borderWidth:     '1px',
    borderStyle:     'solid',
}))

const props = defineProps({
    user:           Object,
    navigation:     Array,
    reservation:    Object,
    guests:         { type: Array, default: () => [] },
    roomTypes:      { type: Array, default: () => [] },
    bookingSources: { type: Object, default: () => ({}) },
})

const form = useForm({
    guest_id:           props.reservation.guest_id,
    room_type_id:       props.reservation.room_type_id,
    check_in_date:      props.reservation.check_in_date,
    check_out_date:     props.reservation.check_out_date,
    number_of_adults:   props.reservation.number_of_adults,
    number_of_children: props.reservation.number_of_children || 0,
    booking_source:     props.reservation.booking_source || '',
    booking_reference:  props.reservation.booking_reference || '',
    room_rate:          props.reservation.room_rate,
    special_requests:   props.reservation.special_requests || '',
    status:             props.reservation.status,
})

const submit = () => {
    form.put(route('front-desk.reservations.update', props.reservation.id))
}
</script>
