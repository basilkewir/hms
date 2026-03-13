<template>
    <DashboardLayout title="Edit Guest" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Guest</h1>
                    <p class="text-gray-600 mt-2">Update guest information for {{ guest.first_name }} {{ guest.last_name }}</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('front-desk.guests.show', guest.id)"
                          class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                        View Guest
                    </Link>
                    <Link :href="route('front-desk.guests.index')"
                          class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                        Back to Guests
                    </Link>
                </div>
            </div>
        </div>

        <!-- Guest Form -->
        <div class="bg-white shadow rounded-lg p-6">
            <form @submit.prevent="updateGuest" class="space-y-6">
                <!-- Personal Information -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Guest ID</label>
                            <input type="text" :value="guest.guest_id" disabled
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Guest Type</label>
                            <select v-model="form.guest_type_id"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option :value="null">Select type</option>
                                <option v-for="type in guestTypes" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                            <select v-model="form.title"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select title</option>
                                <option value="Mr">Mr.</option>
                                <option value="Mrs">Mrs.</option>
                                <option value="Ms">Ms.</option>
                                <option value="Dr">Dr.</option>
                                <option value="Prof">Prof.</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                            <input v-model="form.first_name" type="text" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.first_name" class="mt-1 text-sm text-red-600">{{ form.errors.first_name }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                            <input v-model="form.last_name" type="text" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.last_name" class="mt-1 text-sm text-red-600">{{ form.errors.last_name }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                            <DatePicker
                                v-model="form.date_of_birth"
                                placeholder="Select date of birth"
                            />
                            <div v-if="form.errors.date_of_birth" class="mt-1 text-sm text-red-600">{{ form.errors.date_of_birth }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gender *</label>
                            <select v-model="form.gender" required
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <div v-if="form.errors.gender" class="mt-1 text-sm text-red-600">{{ form.errors.gender }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nationality *</label>
                            <input v-model="form.nationality" type="text" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.nationality" class="mt-1 text-sm text-red-600">{{ form.errors.nationality }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Occupation</label>
                            <input v-model="form.occupation" type="text"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input v-model="form.email" type="email"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                            <input v-model="form.phone" type="tel" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alternate Phone</label>
                            <input v-model="form.alternate_phone" type="tel"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Emergency Contact Name *</label>
                            <input v-model="form.emergency_contact_name" type="text" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.emergency_contact_name" class="mt-1 text-sm text-red-600">{{ form.errors.emergency_contact_name }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Emergency Contact Phone *</label>
                            <input v-model="form.emergency_contact_phone" type="tel" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.emergency_contact_phone" class="mt-1 text-sm text-red-600">{{ form.errors.emergency_contact_phone }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Emergency Contact Relationship *</label>
                            <input v-model="form.emergency_contact_relationship" type="text" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.emergency_contact_relationship" class="mt-1 text-sm text-red-600">{{ form.errors.emergency_contact_relationship }}</div>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Address Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Street Address</label>
                            <input v-model="form.address" type="text"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                            <input v-model="form.city" type="text" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.city" class="mt-1 text-sm text-red-600">{{ form.errors.city }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">State/Province</label>
                            <input v-model="form.state" type="text"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.state" class="mt-1 text-sm text-red-600">{{ form.errors.state }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Country *</label>
                            <input v-model="form.country" type="text" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.country" class="mt-1 text-sm text-red-600">{{ form.errors.country }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ZIP/Postal Code</label>
                            <input v-model="form.postal_code" type="text"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Identification -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Identification</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ID Type *</label>
                            <select v-model="form.id_type" required
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select ID type</option>
                                <option value="passport">Passport</option>
                                <option value="drivers_license">Driver's License</option>
                                <option value="national_id">National ID</option>
                                <option value="other">Other</option>
                            </select>
                            <div v-if="form.errors.id_type" class="mt-1 text-sm text-red-600">{{ form.errors.id_type }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ID Number *</label>
                            <input v-model="form.id_number" type="text" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.id_number" class="mt-1 text-sm text-red-600">{{ form.errors.id_number }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ID Issue Date</label>
                            <DatePicker
                                v-model="form.id_issue_date"
                                placeholder="Select issue date"
                            />
                            <div v-if="form.errors.id_issue_date" class="mt-1 text-sm text-red-600">{{ form.errors.id_issue_date }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ID Expiry Date</label>
                            <DatePicker
                                v-model="form.id_expiry_date"
                                placeholder="Select expiry date"
                            />
                            <div v-if="form.errors.id_expiry_date" class="mt-1 text-sm text-red-600">{{ form.errors.id_expiry_date }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Issuing Authority</label>
                            <input v-model="form.id_issuing_authority" type="text"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.id_issuing_authority" class="mt-1 text-sm text-red-600">{{ form.errors.id_issuing_authority }}</div>
                        </div>
                    </div>
                </div>

                <!-- Travel & Preferences -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Travel & Preferences</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Purpose of Visit *</label>
                            <input v-model="form.purpose_of_visit" type="text" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div v-if="form.errors.purpose_of_visit" class="mt-1 text-sm text-red-600">{{ form.errors.purpose_of_visit }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Expected Duration (Days)</label>
                            <input v-model="form.expected_duration_days" type="number" min="1"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Special Requests</label>
                            <textarea v-model="form.special_requests" rows="3"
                                      placeholder="Any special requests or notes about the guest..."
                                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dietary Restrictions</label>
                            <textarea v-model="form.dietary_restrictions" rows="3"
                                      placeholder="Enter dietary restrictions (e.g., Vegetarian, Gluten-free, etc.)"
                                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="pb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Notes</h3>
                    <textarea v-model="form.notes" rows="3"
                              placeholder="Any additional notes about the guest..."
                              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-6">
                    <Link :href="route('front-desk.guests.show', guest.id)"
                          class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50">
                        {{ form.processing ? 'Updating...' : 'Update Guest Profile' }}
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DatePicker from '@/Components/DatePicker.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'

const props = defineProps({
    user: Object,
    guest: Object,
    navigation: Array,
    guestTypes: {
        type: Array,
        default: () => []
    }
})

const form = useForm({
    guest_type_id: props.guest.guest_type_id || null,
    title: props.guest.title || '',
    first_name: props.guest.first_name || '',
    last_name: props.guest.last_name || '',
    date_of_birth: props.guest.date_of_birth ? (typeof props.guest.date_of_birth === 'string' ? props.guest.date_of_birth : props.guest.date_of_birth.split('T')[0]) : '',
    gender: props.guest.gender || 'other',
    nationality: props.guest.nationality || '',
    occupation: props.guest.occupation || '',
    email: props.guest.email || '',
    phone: props.guest.phone || '',
    alternate_phone: props.guest.alternate_phone || '',
    emergency_contact_name: props.guest.emergency_contact_name || '',
    emergency_contact_phone: props.guest.emergency_contact_phone || '',
    emergency_contact_relationship: props.guest.emergency_contact_relationship || 'Not specified',
    address: props.guest.address || '',
    city: props.guest.city || '',
    state: props.guest.state || '',
    country: props.guest.country || '',
    postal_code: props.guest.postal_code || '',
    id_type: props.guest.id_type || 'other',
    id_number: props.guest.id_number || '',
    id_issuing_authority: props.guest.id_issuing_authority || '',
    id_issue_date: props.guest.id_issue_date ? (typeof props.guest.id_issue_date === 'string' ? props.guest.id_issue_date : props.guest.id_issue_date.split('T')[0]) : '',
    id_expiry_date: props.guest.id_expiry_date ? (typeof props.guest.id_expiry_date === 'string' ? props.guest.id_expiry_date : props.guest.id_expiry_date.split('T')[0]) : '',
    purpose_of_visit: props.guest.purpose_of_visit || 'Hotel stay',
    expected_duration_days: props.guest.expected_duration_days || null,
    special_requests: props.guest.special_requests || '',
    dietary_restrictions: props.guest.dietary_restrictions || '',
    notes: props.guest.notes || '',
})

const updateGuest = () => {
    form.put(route('front-desk.guests.update', props.guest.id), {
        preserveScroll: true,
    })
}
</script>
