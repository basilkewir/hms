<template>
    <DashboardLayout title="Edit Reservation" :user="user" :navigation="navigation">
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold" style="color: #ffffff;">Edit Reservation</h1>
                    <p class="mt-2" style="color: #ffffff;">Reservation #{{ reservation.reservation_number }}</p>
                </div>
                <Link :href="route('manager.reservations.show', reservation.id)" 
                      class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                    <ArrowLeftIcon class="h-4 w-4 mr-2 inline" />
                    Back
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Guest & Booking -->
                <div>
                    <h3 class="text-lg font-medium mb-4" style="color: #ffffff;">Guest & Booking</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #ffffff;">Guest *</label>
                            <select v-model="form.guest_id" required
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Guest</option>
                                <option v-for="guest in guests" :key="guest.id" :value="guest.id">
                                    {{ guest.first_name }} {{ guest.last_name }} ({{ guest.email }})
                                </option>
                            </select>
                            <div v-if="form.errors.guest_id" class="mt-1 text-sm text-red-600">{{ form.errors.guest_id }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #374151;">Booking Source *</label>
                            <select v-model="form.booking_source" required
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option v-for="(label, value) in bookingSources" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                            <div v-if="form.errors.booking_source" class="mt-1 text-sm text-red-600">{{ form.errors.booking_source }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #ffffff;">Status *</label>
                            <select v-model="form.status" required
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="checked_in">Checked In</option>
                                <option value="checked_out">Checked Out</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="no_show">No Show</option>
                                <option value="modified">Modified</option>
                            </select>
                            <div v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #ffffff;">Booking Reference</label>
                            <input v-model="form.booking_reference" type="text"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.booking_reference" class="mt-1 text-sm text-red-600">{{ form.errors.booking_reference }}</div>
                        </div>
                    </div>
                </div>

                <!-- Dates & Guests -->
                <div>
                    <h3 class="text-lg font-medium mb-4" style="color: #ffffff;">Dates & Guests</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #ffffff;">Check-in Date *</label>
                            <DatePicker 
                                v-model="form.check_in_date" 
                                placeholder="Select check-in date"
                                :required="true"
                            />
                            <div v-if="form.errors.check_in_date" class="mt-1 text-sm text-red-600">{{ form.errors.check_in_date }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #ffffff;">Check-out Date *</label>
                            <DatePicker 
                                v-model="form.check_out_date" 
                                placeholder="Select check-out date"
                                :required="true"
                            />
                            <div v-if="form.errors.check_out_date" class="mt-1 text-sm text-red-600">{{ form.errors.check_out_date }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #ffffff;">Number of Adults *</label>
                            <input v-model.number="form.number_of_adults" type="number" min="1" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.number_of_adults" class="mt-1 text-sm text-red-600">{{ form.errors.number_of_adults }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #374151;">Number of Children</label>
                            <input v-model.number="form.number_of_children" type="number" min="0"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.number_of_children" class="mt-1 text-sm text-red-600">{{ form.errors.number_of_children }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #ffffff;">Infants</label>
                            <input v-model.number="form.infants" type="number" min="0"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.infants" class="mt-1 text-sm text-red-600">{{ form.errors.infants }}</div>
                        </div>
                    </div>
                </div>

                <!-- Room Selection -->
                <div>
                    <h3 class="text-lg font-medium mb-4" style="color: #ffffff;">Room Selection</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #ffffff;">Room Type *</label>
                            <select v-model="form.room_type_id" required @change="filterRoomsByType"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Room Type</option>
                                <option v-for="type in roomTypes" :key="type.id" :value="type.id">
                                    {{ type.name }} - {{ formatCurrency(type.base_price) }}/night
                                </option>
                            </select>
                            <div v-if="form.errors.room_type_id" class="mt-1 text-sm text-red-600">{{ form.errors.room_type_id }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #ffffff;">Room (Optional)</label>
                            <select v-model="form.room_id"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Room (Auto-assign if empty)</option>
                                <option v-for="room in filteredRooms" :key="room.id" :value="room.id">
                                    {{ room.room_number }} ({{ room.room_type?.name || 'N/A' }})
                                </option>
                            </select>
                            <div v-if="form.errors.room_id" class="mt-1 text-sm text-red-600">{{ form.errors.room_id }}</div>
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                <div>
                    <h3 class="text-lg font-medium mb-4" style="color: #ffffff;">Pricing</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #ffffff;">Room Rate per Night *</label>
                            <input v-model.number="form.room_rate" type="number" step="0.01" min="0" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.room_rate" class="mt-1 text-sm text-red-600">{{ form.errors.room_rate }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #ffffff;">Discount Amount</label>
                            <input v-model.number="form.discount_amount" type="number" step="0.01" min="0"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.discount_amount" class="mt-1 text-sm text-red-600">{{ form.errors.discount_amount }}</div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-2" style="color: #ffffff;">Discount Reason</label>
                            <input v-model="form.discount_reason" type="text"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.discount_reason" class="mt-1 text-sm text-red-600">{{ form.errors.discount_reason }}</div>
                        </div>
                    </div>
                </div>

                <!-- Special Requests -->
                <div>
                    <h3 class="text-lg font-medium mb-4" style="color: #ffffff;">Special Requests</h3>
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: #ffffff;">Special Requests</label>
                        <textarea v-model="form.special_requests" rows="4"
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Any special requests or preferences..."></textarea>
                        <div v-if="form.errors.special_requests" class="mt-1 text-sm text-red-600">{{ form.errors.special_requests }}</div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t" style="border-color: #e5e7eb;">
                    <Link :href="route('manager.reservations.show', reservation.id)" 
                          class="bg-gray-300 px-6 py-2 rounded-md hover:bg-gray-400" style="color: #374151;">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50">
                        <span v-if="form.processing">Updating...</span>
                        <span v-else>Update Reservation</span>
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DatePicker from '@/Components/DatePicker.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { formatCurrency } from '@/Utils/currency.js'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    reservation: Object,
    guests: Array,
    roomTypes: Array,
    rooms: Array,
    groupBookings: Array,
    bookingSources: Object,
})

const navigation = computed(() => {
    // Determine role from user's roles
    const userRoles = props.user?.roles || []
    
    // Check if user has manager role (but not admin, as admin should use admin nav)
    const hasManagerRole = userRoles.some(role => role.name === 'manager')
    const hasAdminRole = userRoles.some(role => role.name === 'admin')
    
    // If user is manager (and not admin), use manager navigation
    if (hasManagerRole && !hasAdminRole) {
        return getNavigationForRole('manager')
    }
    
    // For manager routes, always use manager navigation
    // This ensures manager routes show manager sidebar even if user has admin role
    return getNavigationForRole('manager')
})

const form = useForm({
    guest_id: props.reservation.guest_id,
    room_type_id: props.reservation.room_type_id,
    room_id: props.reservation.room_id,
    check_in_date: props.reservation.check_in_date,
    check_out_date: props.reservation.check_out_date,
    number_of_adults: props.reservation.number_of_adults,
    number_of_children: props.reservation.number_of_children,
    infants: props.reservation.infants || 0,
    booking_source: props.reservation.booking_source,
    booking_reference: props.reservation.booking_reference || '',
    room_rate: props.reservation.room_rate,
    discount_amount: props.reservation.discount_amount || 0,
    discount_reason: props.reservation.discount_reason || '',
    special_requests: props.reservation.special_requests || '',
    room_preferences: props.reservation.room_preferences || [],
    status: props.reservation.status,
    group_booking_id: props.reservation.group_booking_id || null,
    is_group_booking: props.reservation.is_group_booking || false,
})

const filteredRooms = computed(() => {
    if (!form.room_type_id) {
        return props.rooms || []
    }
    return (props.rooms || []).filter(room => room.room_type_id == form.room_type_id)
})

const filterRoomsByType = () => {
    // Reset room selection when room type changes
    if (form.room_id) {
        const selectedRoom = props.rooms.find(r => r.id == form.room_id)
        if (selectedRoom && selectedRoom.room_type_id != form.room_type_id) {
            form.room_id = null
        }
    }
}

const submit = () => {
    form.put(route('manager.reservations.update', props.reservation.id), {
        preserveScroll: true,
    })
}
</script>
