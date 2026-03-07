<template>
    <DashboardLayout title="My Profile" :user="user" :navigation="navigation">
        <!-- Profile Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
                    <p class="text-gray-600 mt-2">Manage your personal information and account settings.</p>
                </div>
            </div>
        </div>

        <!-- Profile Information -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
            <form @submit.prevent="updateProfile" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                        <input type="text" v-model="profile.firstName" required
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                        <input type="text" v-model="profile.lastName" required
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" v-model="profile.email" required
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                        <input type="tel" v-model="profile.phone"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                        <div class="relative">
                            <input ref="dobInput" type="date" v-model="profile.dateOfBirth"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                            <div class="absolute inset-0 cursor-pointer" @click="dobInput?.showPicker ? dobInput.showPicker() : dobInput?.focus()"></div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Emergency Contact</label>
                        <input type="text" v-model="profile.emergencyContact"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                    <textarea v-model="profile.address" rows="3"
                              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" :disabled="isUpdating"
                            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50">
                        <span v-if="isUpdating">Updating...</span>
                        <span v-else>Update Profile</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Employment Information -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Employment Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Employee ID</label>
                    <input type="text" :value="employment.employeeId" readonly
                           class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                    <input type="text" :value="employment.department" readonly
                           class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                    <input type="text" :value="employment.position" readonly
                           class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hire Date</label>
                    <input type="text" :value="employment.hireDate" readonly
                           class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Manager</label>
                    <input type="text" :value="employment.manager" readonly
                           class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Employment Status</label>
                    <input type="text" :value="employment.status" readonly
                           class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50">
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>
            <form @submit.prevent="changePassword" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                        <input type="password" v-model="passwordForm.currentPassword" required
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <input type="password" v-model="passwordForm.newPassword" required
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                        <input type="password" v-model="passwordForm.confirmPassword" required
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" :disabled="isChangingPassword"
                            class="bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700 disabled:opacity-50">
                        <span v-if="isChangingPassword">Changing...</span>
                        <span v-else>Change Password</span>
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'

const props = defineProps({
    user: Object,
})

const navigation = computed(() => getNavigationForRole('staff'))

const isUpdating = ref(false)
const isChangingPassword = ref(false)

const profile = ref({
    firstName: 'John',
    lastName: 'Doe',
    email: 'john.doe@hotel.com',
    phone: '+1 (555) 123-4567',
    dateOfBirth: '1990-05-15',
    emergencyContact: 'Jane Doe - +1 (555) 987-6543',
    address: '123 Main Street\nAnytown, ST 12345',
})

const employment = {
    employeeId: 'EMP-001',
    department: 'Housekeeping',
    position: 'Housekeeper',
    hireDate: 'January 15, 2023',
    manager: 'Sarah Wilson',
    status: 'Active',
}

const passwordForm = ref({
    currentPassword: '',
    newPassword: '',
    confirmPassword: '',
})

const updateProfile = () => {
    isUpdating.value = true
    // Simulate API call
    setTimeout(() => {
        alert('Profile updated successfully!')
        isUpdating.value = false
    }, 1000)
}

const changePassword = () => {
    if (passwordForm.value.newPassword !== passwordForm.value.confirmPassword) {
        alert('New passwords do not match!')
        return
    }
    
    isChangingPassword.value = true
    // Simulate API call
    setTimeout(() => {
        alert('Password changed successfully!')
        isChangingPassword.value = false
        // Reset form
        passwordForm.value = {
            currentPassword: '',
            newPassword: '',
            confirmPassword: '',
        }
    }, 1000)
}
</script>
