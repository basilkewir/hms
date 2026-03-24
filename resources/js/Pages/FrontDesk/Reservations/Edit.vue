<template>
    <DashboardLayout title="Edit Reservation" :user="user" :navigation="resolvedNavigation">
        <div class="shadow rounded-lg p-6 mb-8" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Edit Reservation</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Reservation #{{ reservation.reservation_number }}</p>
                </div>
                <Link :href="route('front-desk.reservations.show', reservation.id)" class="px-4 py-2 rounded-md transition-colors font-medium"
                      :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }">
                    <ArrowLeftIcon class="h-4 w-4 mr-2 inline" />
                    Back
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Guest & Booking</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Guest *</label>
                            <select v-model="form.guest_id" required class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle">
                                <option value="">Select Guest</option>
                                <option v-for="guest in guests" :key="guest.id" :value="guest.id">{{ guest.first_name }} {{ guest.last_name }}{{ guest.email ? ' (' + guest.email + ')' : '' }}</option>
                            </select>
                            <div v-if="form.errors.guest_id" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.guest_id }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Booking Source *</label>
                            <select v-model="form.booking_source" required class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle">
                                <option value="">Select Source</option>
                                <option v-for="(label, value) in bookingSources" :key="value" :value="value">{{ label }}</option>
                            </select>
                            <div v-if="form.errors.booking_source" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.booking_source }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Booking Reference</label>
                            <input v-model="form.booking_reference" type="text" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Status *</label>
                            <select v-model="form.status" required class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle">
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="checked_in">Checked In</option>
                                <option value="checked_out">Checked Out</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="no_show">No Show</option>
                                <option value="modified">Modified</option>
                            </select>
                            <div v-if="form.errors.status" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.status }}</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Dates & Occupancy</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Check-in Date *</label>
                            <input type="date" v-model="form.check_in_date" required class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" />
                            <div v-if="form.errors.check_in_date" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.check_in_date }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Check-out Date *</label>
                            <input type="date" v-model="form.check_out_date" required class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" />
                            <div v-if="form.errors.check_out_date" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.check_out_date }}</div>
                        </div>
                        <div v-if="form.status === 'checked_in'">
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Extend Stay (Days)</label>
                            <input type="number" v-model.number="form.extend_days" min="0" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" placeholder="0" />
                            <p class="mt-1 text-xs" :style="{ color: themeColors.textTertiary }">Add extra days to current check-out date for checked-in guest.</p>
                            <div v-if="form.errors.extend_days" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.extend_days }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Adults *</label>
                            <input type="number" v-model.number="form.number_of_adults" min="1" required class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" />
                            <div v-if="form.errors.number_of_adults" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.number_of_adults }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Children</label>
                            <input type="number" v-model.number="form.number_of_children" min="0" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Infants</label>
                            <input type="number" v-model.number="form.infants" min="0" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" />
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Room</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Room Type *</label>
                            <select v-model="form.room_type_id" required class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" @change="onRoomTypeChange">
                                <option value="">Select Room Type</option>
                                <option v-for="roomType in roomTypes" :key="roomType.id" :value="roomType.id">{{ roomType.name }} - {{ formatCurrency(roomType.base_price || 0) }}/night</option>
                            </select>
                            <div v-if="form.errors.room_type_id" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.room_type_id }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Room (Optional)</label>
                            <select v-model="form.room_id" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle">
                                <option value="">Auto-assign room</option>
                                <option v-for="room in filteredRooms" :key="room.id" :value="room.id">{{ room.room_number }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Pricing</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Room Rate *</label>
                            <input type="number" v-model.number="form.room_rate" min="0" step="0.01" required class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" />
                            <div v-if="form.errors.room_rate" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.room_rate }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Discount Amount</label>
                            <input type="number" v-model.number="form.discount_amount" min="0" step="0.01" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Discount Reason</label>
                            <input type="text" v-model="form.discount_reason" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" />
                        </div>
                    </div>
                    <div class="mt-6 rounded-lg p-4" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-semibold" :style="{ color: themeColors.textPrimary }">Updated Reservation Price</h4>
                            <span class="text-xs" :style="{ color: themeColors.textSecondary }">{{ pricingSummary.nights }} night(s)</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                            <div class="flex justify-between">
                                <span :style="{ color: themeColors.textSecondary }">Room Charges</span>
                                <span :style="{ color: themeColors.textPrimary }">{{ formatCurrency(pricingSummary.roomCharges) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span :style="{ color: themeColors.textSecondary }">Discount</span>
                                <span :style="{ color: pricingSummary.totalDiscount > 0 ? themeColors.danger : themeColors.textPrimary }">-{{ formatCurrency(pricingSummary.totalDiscount) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span :style="{ color: themeColors.textSecondary }">Taxes</span>
                                <span :style="{ color: themeColors.textPrimary }">{{ formatCurrency(pricingSummary.taxes) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span :style="{ color: themeColors.textSecondary }">Service Charges</span>
                                <span :style="{ color: themeColors.textPrimary }">{{ formatCurrency(pricingSummary.serviceCharges) }}</span>
                            </div>
                            <div class="flex justify-between font-semibold md:col-span-2 pt-2" :style="{ borderTop: `1px solid ${themeColors.border}` }">
                                <span :style="{ color: themeColors.textPrimary }">Total Price</span>
                                <span :style="{ color: themeColors.primary }">{{ formatCurrency(pricingSummary.totalAmount) }}</span>
                            </div>
                            <div class="flex justify-between md:col-span-2">
                                <span :style="{ color: themeColors.textSecondary }">Balance After Paid Amount</span>
                                <span :style="{ color: pricingSummary.balanceAmount > 0 ? themeColors.danger : themeColors.primary }">{{ formatCurrency(pricingSummary.balanceAmount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Group Booking</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <label class="inline-flex items-center gap-2 mt-8">
                            <input type="checkbox" v-model="form.is_group_booking" class="rounded" />
                            <span class="text-sm" :style="{ color: themeColors.textSecondary }">Is group booking</span>
                        </label>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Group Booking</label>
                            <select v-model="form.group_booking_id" :disabled="!form.is_group_booking" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors disabled:opacity-50" :style="inputStyle">
                                <option value="">Select Group Booking</option>
                                <option v-for="group in groupBookings" :key="group.id" :value="group.id">{{ group.group_number }} - {{ group.group_name }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Special Requests</label>
                    <textarea v-model="form.special_requests" rows="4" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" placeholder="Any special requests or preferences..."></textarea>
                </div>

                <div class="flex items-center justify-end gap-4 pt-6 border-t" :style="{ borderColor: themeColors.border }">
                    <Link :href="route('front-desk.reservations.show', reservation.id)" class="px-6 py-2 rounded-md font-medium"
                          :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing" class="px-6 py-2 rounded-md font-medium text-white disabled:opacity-50"
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
import { getNavigationForRole } from '@/Utils/navigation.js'
import { formatCurrency } from '@/Utils/currency.js'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    primary: `var(--kotel-primary)`,
    secondary: `var(--kotel-secondary)`,
    danger: `var(--kotel-danger)`,
}))
loadTheme()

const inputStyle = computed(() => ({
    backgroundColor: themeColors.value.background,
    borderColor: themeColors.value.border,
    color: themeColors.value.textPrimary,
    borderWidth: '1px',
    borderStyle: 'solid',
}))

const props = defineProps({
    user: Object,
    navigation: { type: Array, default: () => [] },
    reservation: Object,
    guests: { type: Array, default: () => [] },
    roomTypes: { type: Array, default: () => [] },
    rooms: { type: Array, default: () => [] },
    groupBookings: { type: Array, default: () => [] },
    bookingSources: { type: Object, default: () => ({}) },
    pricingSettings: { type: Object, default: () => ({}) },
})

const resolvedNavigation = computed(() => props.navigation?.length ? props.navigation : getNavigationForRole('front_desk'))

const form = useForm({
    guest_id: props.reservation.guest_id,
    room_type_id: props.reservation.room_type_id,
    room_id: props.reservation.room_id || '',
    check_in_date: props.reservation.check_in_date,
    check_out_date: props.reservation.check_out_date,
    extend_days: 0,
    number_of_adults: props.reservation.number_of_adults,
    number_of_children: props.reservation.number_of_children || 0,
    infants: props.reservation.infants || 0,
    booking_source: props.reservation.booking_source || '',
    booking_reference: props.reservation.booking_reference || '',
    room_rate: props.reservation.room_rate,
    discount_amount: props.reservation.discount_amount || 0,
    discount_reason: props.reservation.discount_reason || '',
    special_requests: props.reservation.special_requests || '',
    room_preferences: props.reservation.room_preferences || [],
    status: props.reservation.status,
    group_booking_id: props.reservation.group_booking_id || '',
    is_group_booking: !!props.reservation.is_group_booking,
})

const filteredRooms = computed(() => {
    if (!form.room_type_id) return props.rooms || []
    return (props.rooms || []).filter(room => String(room.room_type_id) === String(form.room_type_id))
})

const selectedGuest = computed(() => {
    return (props.guests || []).find(guest => String(guest.id) === String(form.guest_id)) || null
})

const pricingSummary = computed(() => {
    const checkInDate = form.check_in_date ? new Date(`${form.check_in_date}T00:00:00`) : null
    const checkOutDate = form.check_out_date ? new Date(`${form.check_out_date}T00:00:00`) : null

    let nights = 0
    if (checkInDate && checkOutDate && checkOutDate > checkInDate) {
        nights = Math.round((checkOutDate - checkInDate) / 86400000)
    }

    const roomRate = Number(form.room_rate) || 0
    const roomCharges = roomRate * nights

    let guestTypeDiscount = 0
    if (props.pricingSettings?.auto_apply_guest_type_discount && selectedGuest.value?.guest_type?.discount_percentage) {
        guestTypeDiscount = roomCharges * (Number(selectedGuest.value.guest_type.discount_percentage) || 0) / 100
    }

    let vipDiscount = 0
    if (props.pricingSettings?.auto_apply_vip_discount && selectedGuest.value?.is_vip) {
        vipDiscount = roomCharges * (Number(props.pricingSettings?.vip_discount_percentage) || 0) / 100
    }

    const manualDiscount = Number(form.discount_amount) || 0
    let totalDiscount = guestTypeDiscount + vipDiscount + manualDiscount
    if (props.pricingSettings?.discount_combination_mode === 'override' && manualDiscount > 0) {
        totalDiscount = manualDiscount
    }

    totalDiscount = Math.max(0, Math.min(totalDiscount, roomCharges))

    const taxableAmount = Math.max(0, roomCharges - totalDiscount)
    const taxes = taxableAmount * ((Number(props.pricingSettings?.tax_rate) || 0) / 100)
    const serviceCharges = taxableAmount * ((Number(props.pricingSettings?.service_charge_rate) || 0) / 100)
    const totalAmount = taxableAmount + taxes + serviceCharges
    const paidAmount = Number(props.reservation?.paid_amount) || 0
    const balanceAmount = Math.max(0, totalAmount - paidAmount)

    return {
        nights,
        roomCharges,
        totalDiscount,
        taxes,
        serviceCharges,
        totalAmount,
        balanceAmount,
    }
})

const onRoomTypeChange = () => {
    if (!form.room_id) return
    const selectedRoom = (props.rooms || []).find(room => String(room.id) === String(form.room_id))
    if (!selectedRoom || String(selectedRoom.room_type_id) !== String(form.room_type_id)) {
        form.room_id = ''
    }
}

const submit = () => {
    form.put(route('front-desk.reservations.update', props.reservation.id), { preserveScroll: true })
}
</script>
