<template>
    <DashboardLayout title="Create Hall Booking">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Create Hall Booking</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Book a hall independently from room reservations.</p>
                </div>
                <Link :href="route(`${routePrefix}.hall-bookings.index`)"
                      class="px-4 py-2 rounded-md transition-colors text-sm font-medium"
                      :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                    Back
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div v-if="Object.keys(form.errors).length" class="p-4 rounded-lg border"
                     :style="{ 
                         backgroundColor: 'rgba(239, 68, 68, 0.1)',
                         borderColor: themeColors.danger,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <p class="text-sm font-medium" :style="{ color: themeColors.danger }">Please fix the errors below and try again.</p>
                </div>

                <div class="shadow rounded-lg p-6"
                     :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                    <h2 class="text-xl font-bold mb-4 pb-2 border-b"
                        :style="{ color: themeColors.textPrimary, borderColor: themeColors.border }">Booking Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Hall *</label>
                            <select v-model="form.hall_id" required
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="inputStyle">
                                <option value="">Select Hall</option>
                                <option v-for="h in halls" :key="h.id" :value="h.id">
                                    {{ h.name }} ({{ h.code }}) - cap {{ h.capacity }}
                                </option>
                            </select>
                            <p v-if="form.errors.hall_id" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.hall_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Linked Guest (optional)</label>
                            <select v-model="form.guest_id"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="inputStyle">
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
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="inputStyle" />
                            <p v-if="form.errors.contact_name" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.contact_name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Contact Email</label>
                            <input v-model="form.contact_email" type="email"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="inputStyle" />
                            <p v-if="form.errors.contact_email" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.contact_email }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Contact Phone</label>
                            <input v-model="form.contact_phone" type="text"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="inputStyle" />
                            <p v-if="form.errors.contact_phone" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.contact_phone }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Event Date *</label>
                            <DatePicker v-model="form.event_date" required
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="inputStyle"
                                      placeholder="Select event date" />
                            <p v-if="form.errors.event_date" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.event_date }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Start Time *</label>
                            <TimePicker v-model="form.start_time" required
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="inputStyle" />
                            <p v-if="form.errors.start_time" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.start_time }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">End Time *</label>
                            <TimePicker v-model="form.end_time" required
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="inputStyle" />
                            <p v-if="form.errors.end_time" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.end_time }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Attendees *</label>
                            <input v-model.number="form.attendees" type="number" min="1" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="inputStyle" />
                            <p v-if="form.errors.attendees" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.attendees }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Status</label>
                            <select v-model="form.status"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="inputStyle">
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
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="inputStyle" />
                            <p v-if="form.errors.paid_amount" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.paid_amount }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Notes</label>
                            <textarea v-model="form.notes" rows="3"
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="inputStyle"></textarea>
                            <p v-if="form.errors.notes" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.notes }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3">
                    <Link :href="route(`${routePrefix}.hall-bookings.index`)"
                          class="px-6 py-2 rounded-md transition-colors"
                          :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                            class="px-6 py-2 rounded-md transition-colors disabled:opacity-50"
                            :style="{ backgroundColor: themeColors.primary, color: 'white' }"
                            @mouseenter="!form.processing && ($event.target.style.backgroundColor = themeColors.hover)"
                            @mouseleave="!form.processing && ($event.target.style.backgroundColor = themeColors.primary)">
                        <span v-if="form.processing">Creating...</span>
                        <span v-else>Create Hall Booking</span>
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
import DatePicker from '@/Components/DatePicker.vue'
import TimePicker from '@/Components/TimePicker.vue'
import { useTheme } from '@/Composables/useTheme.js'

const props = defineProps({
    halls:       Array,
    guests:      Array,
    routePrefix: { type: String, default: 'admin' },
})

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

loadTheme()

const inputStyle = computed(() => ({
    backgroundColor: themeColors.value.background,
    borderColor: themeColors.value.border,
    color: themeColors.value.textPrimary,
    borderWidth: '1px',
    borderStyle: 'solid'
}))

const form = useForm({
    hall_id: '',
    guest_id: null,
    contact_name: '',
    contact_email: '',
    contact_phone: '',
    event_date: '',
    start_time: '',
    end_time: '',
    attendees: 1,
    paid_amount: 0,
    status: 'pending',
    notes: '',
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
    form.post(route(`${props.routePrefix}.hall-bookings.store`))
}
</script>
