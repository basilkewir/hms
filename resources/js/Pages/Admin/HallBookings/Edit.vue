<template>
    <DashboardLayout title="Edit Hall Booking" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-8 border"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Edit Hall Booking</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">{{ booking.booking_number }}</p>
                </div>
                <Link :href="route(`${routePrefix}.hall-bookings.show`, booking.id)"
                      class="px-4 py-2 rounded-md transition-colors text-sm font-medium"
                      :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }">
                    Cancel
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div v-if="Object.keys(form.errors).length"
                     class="p-4 rounded-lg border"
                     :style="{ backgroundColor: 'rgba(239,68,68,0.1)', borderColor: themeColors.danger, borderWidth: '1px', borderStyle: 'solid' }">
                    <p class="text-sm font-medium" :style="{ color: themeColors.danger }">Please fix the errors below and try again.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Hall *</label>
                        <select v-model="form.hall_id" required class="w-full rounded-md px-3 py-2 focus:outline-none" :style="inputStyle">
                            <option value="">Select Hall</option>
                            <option v-for="h in halls" :key="h.id" :value="h.id">
                                {{ h.name }} ({{ h.code }}) – cap {{ h.capacity }}
                            </option>
                        </select>
                        <p v-if="form.errors.hall_id" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.hall_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Linked Guest (optional)</label>
                        <select v-model="form.guest_id" class="w-full rounded-md px-3 py-2 focus:outline-none" :style="inputStyle">
                            <option :value="null">No guest</option>
                            <option v-for="g in guests" :key="g.id" :value="g.id">
                                {{ g.first_name }} {{ g.last_name }} ({{ g.email }})
                            </option>
                        </select>
                        <p v-if="form.errors.guest_id" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.guest_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Contact Name *</label>
                        <input v-model="form.contact_name" type="text" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none" :style="inputStyle" />
                        <p v-if="form.errors.contact_name" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.contact_name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Contact Email</label>
                        <input v-model="form.contact_email" type="email"
                               class="w-full rounded-md px-3 py-2 focus:outline-none" :style="inputStyle" />
                        <p v-if="form.errors.contact_email" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.contact_email }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Contact Phone</label>
                        <input v-model="form.contact_phone" type="text"
                               class="w-full rounded-md px-3 py-2 focus:outline-none" :style="inputStyle" />
                        <p v-if="form.errors.contact_phone" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.contact_phone }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Event Date *</label>
                        <input v-model="form.event_date" type="date" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none" :style="inputStyle" />
                        <p v-if="form.errors.event_date" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.event_date }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Start Time *</label>
                        <input v-model="form.start_time" type="time" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none" :style="inputStyle" />
                        <p v-if="form.errors.start_time" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.start_time }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">End Time *</label>
                        <input v-model="form.end_time" type="time" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none" :style="inputStyle" />
                        <p v-if="form.errors.end_time" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.end_time }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Attendees *</label>
                        <input v-model.number="form.attendees" type="number" min="1" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none" :style="inputStyle" />
                        <p v-if="form.errors.attendees" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.attendees }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Status</label>
                        <select v-model="form.status" class="w-full rounded-md px-3 py-2 focus:outline-none" :style="inputStyle">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="completed">Completed</option>
                        </select>
                        <p v-if="form.errors.status" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.status }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Estimated Total</label>
                        <div class="w-full rounded-md px-3 py-2 border text-sm"
                             :style="{ ...inputStyle, opacity: priceReady ? 1 : 0.5 }">
                            <template v-if="priceReady">
                                <span :style="{ color: themeColors.textSecondary }">{{ formatCurrency(selectedHall.base_price) }}/hr</span>
                                <span :style="{ color: themeColors.textTertiary }"> &times; {{ durationHours }} hrs = </span>
                                <strong :style="{ color: themeColors.primary }">{{ formatCurrency(autoTotal) }}</strong>
                            </template>
                            <span v-else :style="{ color: themeColors.textTertiary }">Select a hall and times to calculate</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Paid Amount</label>
                        <input v-model.number="form.paid_amount" type="number" min="0" step="0.01"
                               class="w-full rounded-md px-3 py-2 focus:outline-none" :style="inputStyle" />
                        <p v-if="form.errors.paid_amount" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.paid_amount }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Notes</label>
                        <textarea v-model="form.notes" rows="3"
                                  class="w-full rounded-md px-3 py-2 focus:outline-none" :style="inputStyle"></textarea>
                        <p v-if="form.errors.notes" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.notes }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <Link :href="route(`${routePrefix}.hall-bookings.show`, booking.id)"
                          class="px-6 py-2 rounded-md transition-colors text-sm"
                          :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                            class="px-6 py-2 rounded-md transition-colors text-sm font-medium disabled:opacity-50"
                            :style="{ backgroundColor: themeColors.primary, color: 'white' }">
                        <span v-if="form.processing">Saving…</span>
                        <span v-else>Save Changes</span>
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
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'

const props = defineProps({
    user:        Object,
    booking:     Object,
    halls:       Array,
    guests:      Array,
    routePrefix: { type: String, default: 'admin' },
})

const { currentTheme } = useTheme()
const navigation = computed(() => getNavigationForRole('admin'))

const themeColors = computed(() => ({
    background:    `var(--kotel-background)`,
    card:          `var(--kotel-card)`,
    border:        `var(--kotel-border)`,
    textPrimary:   `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    primary:       `var(--kotel-primary)`,
    secondary:     `var(--kotel-secondary)`,
    success:       `var(--kotel-success)`,
    warning:       `var(--kotel-warning)`,
    danger:        `var(--kotel-danger)`,
}))

const inputStyle = computed(() => ({
    backgroundColor: themeColors.value.background,
    borderColor:     themeColors.value.border,
    color:           themeColors.value.textPrimary,
    borderWidth:     '1px',
    borderStyle:     'solid',
}))

const form = useForm({
    hall_id:       props.booking.hall_id,
    guest_id:      props.booking.guest_id ?? null,
    contact_name:  props.booking.contact_name,
    contact_email: props.booking.contact_email ?? '',
    contact_phone: props.booking.contact_phone ?? '',
    event_date:    props.booking.event_date ? props.booking.event_date.substring(0, 10) : '',
    start_time:    props.booking.start_time,
    end_time:      props.booking.end_time,
    attendees:     props.booking.attendees,
    paid_amount:   props.booking.paid_amount,
    status:        props.booking.status,
    notes:         props.booking.notes ?? '',
})

const selectedHall = computed(() =>
    props.halls.find(h => h.id == form.hall_id) ?? null
)

const durationHours = computed(() => {
    if (!form.start_time || !form.end_time) return 0
    const [sh, sm] = form.start_time.split(':').map(Number)
    const [eh, em] = form.end_time.split(':').map(Number)
    let mins = (eh * 60 + em) - (sh * 60 + sm)
    if (mins <= 0) mins += 24 * 60
    return Math.round(mins / 60 * 100) / 100
})

const autoTotal = computed(() => {
    if (!selectedHall.value || !durationHours.value) return 0
    return Math.round(selectedHall.value.base_price * durationHours.value * 100) / 100
})

const priceReady = computed(() => selectedHall.value && durationHours.value > 0)

const formatCurrency = (val) =>
    new Intl.NumberFormat(undefined, { style: 'currency', currency: 'MAD', minimumFractionDigits: 2 }).format(val)

const submit = () => {
    form.put(route(`${props.routePrefix}.hall-bookings.update`, props.booking.id))
}
</script>
