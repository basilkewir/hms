<template>
    <DashboardLayout title="Add New Guest" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Add New Guest</h1>
                    <p class="text-gray-600 mt-2">Create a new guest profile in the system.</p>
                </div>
                <div class="flex space-x-3">
                    <Link href="/manager/guests" 
                          class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                        <ArrowLeftIcon class="h-4 w-4 mr-2 inline" />
                        Back to Guests
                    </Link>
                </div>
            </div>
        </div>

        <!-- Guest Form -->
        <div class="bg-white shadow rounded-lg p-6">
            <form @submit.prevent="createGuest" class="space-y-6">
                <!-- Personal Information -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Guest Type</label>
                            <select v-model="form.guest_type_id"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option :value="null">Select type</option>
                                <option v-for="guestType in guestTypes" :key="guestType.id" :value="guestType.id">
                                    {{ guestType.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                            <input v-model="form.first_name" type="text" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                            <input v-model="form.last_name" type="text" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                            <DatePicker 
                                v-model="form.date_of_birth" 
                                placeholder="Select date of birth"
                                required
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                            <select v-model="form.gender"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                                <option value="prefer_not_to_say">Prefer not to say</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nationality *</label>
                            <input v-model="form.nationality" type="text" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                            <input v-model="form.email" type="email" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                            <input v-model="form.phone" type="tel" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alternative Phone</label>
                            <input v-model="form.alternate_phone" type="tel"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Emergency Contact Name *</label>
                            <input v-model="form.emergency_contact_name" type="text" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Emergency Contact Phone *</label>
                            <input v-model="form.emergency_contact_phone" type="tel" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Address Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Street Address *</label>
                            <input v-model="form.address" type="text" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                            <input v-model="form.city" type="text" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">State/Province *</label>
                            <input v-model="form.state" type="text" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ZIP/Postal Code</label>
                            <input v-model="form.postal_code" type="text"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Country *</label>
                            <input v-model="form.country" type="text" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Identification -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Identification</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ID Type</label>
                            <select v-model="form.id_type"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select ID type</option>
                                <option value="passport">Passport</option>
                                <option value="drivers_license">Driver's License</option>
                                <option value="national_id">National ID</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ID Number *</label>
                            <input v-model="form.id_number" type="text" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ID Issue Date *</label>
                            <DatePicker 
                                v-model="form.id_issue_date" 
                                placeholder="Select issue date"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ID Expiry Date *</label>
                            <DatePicker 
                                v-model="form.id_expiry_date" 
                                placeholder="Select expiry date"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Issuing Authority *</label>
                            <input v-model="form.id_issuing_authority" type="text" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Preferences -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Preferences & Special Requests</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Room Preference</label>
                            <select v-model="form.room_preference"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">No preference</option>
                                <option value="smoking">Smoking</option>
                                <option value="non_smoking">Non-smoking</option>
                                <option value="high_floor">High floor</option>
                                <option value="low_floor">Low floor</option>
                                <option value="quiet_room">Quiet room</option>
                                <option value="city_view">City view</option>
                                <option value="ocean_view">Ocean view</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bed Preference</label>
                            <select v-model="form.bed_preference"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">No preference</option>
                                <option value="single">Single bed</option>
                                <option value="double">Double bed</option>
                                <option value="twin">Twin beds</option>
                                <option value="king">King bed</option>
                                <option value="queen">Queen bed</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dietary Restrictions</label>
                            <textarea v-model="form.dietary_restrictions" rows="3"
                                      placeholder="Enter dietary restrictions (e.g., Vegetarian, Gluten-free, etc.)"
                                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Special Requests</label>
                            <textarea v-model="form.special_requests" rows="3" 
                                      placeholder="Any special requests or notes about the guest..."
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

                <!-- Error Messages -->
                <div v-if="Object.keys(form.errors).length > 0" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                    <h4 class="text-sm font-medium text-red-800 mb-2">Please fix the following errors:</h4>
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        <li v-for="(error, field) in form.errors" :key="field">
                            <strong>{{ field }}:</strong> {{ Array.isArray(error) ? error[0] : error }}
                        </li>
                    </ul>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-6">
                    <Link href="/manager/guests"
                          class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50">
                        {{ form.processing ? 'Creating...' : 'Create Guest Profile' }}
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DatePicker from '@/Components/DatePicker.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    guestTypes: {
        type: Array,
        default: () => []
    },
    errors: {
        type: Object,
        default: () => ({})
    }
})

const navigation = computed(() => getNavigationForRole('manager'))

const form = useForm({
    title: '',
    guest_type_id: null,
    first_name: '',
    last_name: '',
    date_of_birth: '',
    gender: 'other',
    nationality: '',
    occupation: '',
    email: '',
    phone: '',
    alternate_phone: '',
    emergency_contact_name: '',
    emergency_contact_phone: '',
    emergency_contact_relationship: 'Not specified',
    address: '',
    city: '',
    state: '',
    country: '',
    postal_code: '',
    id_type: 'other',
    id_number: '',
    id_issuing_authority: '',
    id_issue_date: '',
    id_expiry_date: '',
    purpose_of_visit: 'Hotel stay',
    room_preference: '',
    bed_preference: '',
    preferences: {},
    special_requests: '',
    dietary_restrictions: '',
    notes: '',
})

const createGuest = () => {
    // Prepare preferences object
    const preferences = {}
    if (form.room_preference) preferences.room_preference = form.room_preference
    if (form.bed_preference) preferences.bed_preference = form.bed_preference
    
    // Update form with preferences
    form.preferences = preferences
    
    // Set default values for required fields if not provided
    if (!form.date_of_birth) {
        // Set a default date of birth if not provided (18 years ago)
        const defaultDate = new Date()
        defaultDate.setFullYear(defaultDate.getFullYear() - 18)
        form.date_of_birth = defaultDate.toISOString().split('T')[0]
    }
    
    if (!form.id_issue_date) {
        form.id_issue_date = form.date_of_birth || new Date().toISOString().split('T')[0]
    }
    
    if (!form.id_expiry_date) {
        // Set expiry to 10 years from issue date or today
        const issueDate = form.id_issue_date || form.date_of_birth || new Date().toISOString().split('T')[0]
        const expiryDate = new Date(issueDate)
        expiryDate.setFullYear(expiryDate.getFullYear() + 10)
        form.id_expiry_date = expiryDate.toISOString().split('T')[0]
    }
    
    // Ensure required address fields have defaults
    if (!form.address) form.address = 'Not provided'
    if (!form.city) form.city = 'Not provided'
    if (!form.state) form.state = 'Not provided'
    if (!form.country) form.country = form.nationality || 'Not provided'
    
    // Ensure gender is valid
    if (form.gender === 'prefer_not_to_say') {
        form.gender = 'other'
    }
    
    // Ensure nationality is set
    if (!form.nationality) {
        form.nationality = form.country || 'Not provided'
    }
    
    // Ensure emergency contact fields
    if (!form.emergency_contact_name) {
        form.emergency_contact_name = 'Not provided'
    }
    if (!form.emergency_contact_phone) {
        form.emergency_contact_phone = form.phone || 'Not provided'
    }
    if (!form.emergency_contact_relationship) {
        form.emergency_contact_relationship = 'Not specified'
    }
    
    // Ensure purpose of visit
    if (!form.purpose_of_visit) {
        form.purpose_of_visit = 'Hotel stay'
    }
    
    form.post(route('manager.guests.store'), {
        preserveScroll: true,
        onSuccess: (page) => {
            // Success - redirect handled by controller
            console.log('Guest created successfully')
        },
        onError: (errors) => {
            console.error('Guest creation errors:', errors)
            // Errors are automatically handled by Inertia form
        },
        onFinish: () => {
            // Form submission finished
        }
    })
}
</script>
