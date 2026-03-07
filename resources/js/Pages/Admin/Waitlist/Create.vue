<template>
    <DashboardLayout title="Add to Waitlist" :user="user" :navigation="navigation">
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Add to Waitlist</h1>
                    <p class="text-gray-600 mt-2">Add a guest to the waitlist for unavailable dates.</p>
                </div>
                <Link :href="route(`${routePrefix}.waitlist.index`)"
                      class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                    <ArrowLeftIcon class="h-4 w-4 mr-2 inline" />
                    Back
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Guest Selection -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Guest Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Guest *</label>
                            <select v-model="form.guest_id" required
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Guest</option>
                                <option v-for="guest in guests" :key="guest.id" :value="guest.id">
                                    {{ guest.first_name }} {{ guest.last_name }} ({{ guest.email }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Room Type *</label>
                            <select v-model="form.room_type_id" required
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Room Type</option>
                                <option v-for="roomType in roomTypes" :key="roomType.id" :value="roomType.id">
                                    {{ roomType.name }} {{ roomType.code ? '(' + roomType.code + ')' : '' }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Requested Dates -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Requested Dates</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Check-in Date *</label>
                            <div class="relative">
                                <input ref="checkInInput" v-model="form.requested_check_in" type="date" required
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                                <div class="absolute inset-0 cursor-pointer" @click="checkInInput?.showPicker ? checkInInput.showPicker() : checkInInput?.focus()"></div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Check-out Date *</label>
                            <div class="relative">
                                <input ref="checkOutInput" v-model="form.requested_check_out" type="date" required
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                                <div class="absolute inset-0 cursor-pointer" @click="checkOutInput?.showPicker ? checkOutInput.showPicker() : checkOutInput?.focus()"></div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Number of Adults *</label>
                            <input v-model.number="form.number_of_adults" type="number" required min="1"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Number of Children</label>
                            <input v-model.number="form.number_of_children" type="number" min="0"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Priority & Contact -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Priority & Contact</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Priority (0-100)</label>
                            <input v-model.number="form.priority" type="number" min="0" max="100"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Higher number = higher priority</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Expiry Date</label>
                            <div class="relative">
                                <input ref="expiresAtInput" v-model="form.expires_at" type="date"
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                                <div class="absolute inset-0 cursor-pointer" @click="expiresAtInput?.showPicker ? expiresAtInput.showPicker() : expiresAtInput?.focus()"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Leave empty for default (30 days)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email *</label>
                            <input v-model="form.contact_email" type="email" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone *</label>
                            <input v-model="form.contact_phone" type="text" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Special Requests</label>
                            <textarea v-model="form.special_requests" rows="3"
                                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <Link :href="route(`${routePrefix}.waitlist.index`)"
                          class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50">
                        <span v-if="form.processing">Adding...</span>
                        <span v-else>Add to Waitlist</span>
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    guests: Array,
    roomTypes: Array,
    routePrefix: { type: String, default: 'admin' },
})

const navigation = computed(() => getNavigationForRole('admin'))

const form = useForm({
    guest_id: '',
    room_type_id: '',
    requested_check_in: '',
    requested_check_out: '',
    number_of_adults: 1,
    number_of_children: 0,
    priority: 0,
    contact_email: '',
    contact_phone: '',
    special_requests: '',
    expires_at: '',
})

const submit = () => {
    form.post(route(`${props.routePrefix}.waitlist.store`))
}
</script>
