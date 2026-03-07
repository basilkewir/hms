<template>
    <DashboardLayout title="License Management" :user="user" :navigation="navigation">
        <!-- License Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">IPTV License Management</h1>
                    <p class="text-gray-600 mt-2">Manage IPTV system licenses and activations.</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="showCreateModal = true" 
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        <KeyIcon class="h-4 w-4 mr-2 inline" />
                        Generate License
                    </button>
                    <button @click="validateAllLicenses" 
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                        <ShieldCheckIcon class="h-4 w-4 mr-2 inline" />
                        Validate All
                    </button>
                </div>
            </div>
        </div>

        <!-- License Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <KeyIcon class="h-8 w-8 text-blue-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Licenses</p>
                        <p class="text-2xl font-bold text-gray-900">{{ licenseStats.total }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <CheckCircleIcon class="h-8 w-8 text-green-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Active</p>
                        <p class="text-2xl font-bold text-gray-900">{{ licenseStats.active }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <ClockIcon class="h-8 w-8 text-yellow-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Expiring Soon</p>
                        <p class="text-2xl font-bold text-gray-900">{{ licenseStats.expiring }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <XCircleIcon class="h-8 w-8 text-red-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Expired</p>
                        <p class="text-2xl font-bold text-gray-900">{{ licenseStats.expired }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" v-model="searchQuery" placeholder="Search licenses..."
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">License Type</label>
                    <select v-model="selectedType"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Types</option>
                        <option value="iptv">IPTV</option>
                        <option value="hotel_management">Hotel Management</option>
                        <option value="premium">Premium</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select v-model="selectedStatus"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="expired">Expired</option>
                        <option value="suspended">Suspended</option>
                        <option value="revoked">Revoked</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Expiry</label>
                    <select v-model="selectedExpiry"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All</option>
                        <option value="expiring">Expiring Soon</option>
                        <option value="expired">Expired</option>
                        <option value="never">Never Expires</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Licenses Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">All Licenses</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                License Key
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Customer
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Expires
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Devices
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="license in filteredLicenses" :key="license.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-mono text-gray-900">{{ license.license_key }}</div>
                                <div class="text-xs text-gray-500">{{ license.product_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ license.customer_name }}</div>
                                <div class="text-sm text-gray-500">{{ license.customer_email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getTypeColor(license.license_type)">
                                    {{ formatType(license.license_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(license.status)">
                                    {{ formatStatus(license.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div v-if="license.expires_at">
                                    {{ formatDate(license.expires_at) }}
                                    <div class="text-xs text-gray-500">
                                        {{ getRemainingDays(license.expires_at) }}
                                    </div>
                                </div>
                                <div v-else class="text-gray-500">Never</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ license.activation_count }}/{{ license.max_activations }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button @click="viewLicense(license)" class="text-blue-600 hover:text-blue-900">View</button>
                                    <button @click="editLicense(license)" class="text-green-600 hover:text-green-900">Edit</button>
                                    <button @click="validateLicense(license)" class="text-purple-600 hover:text-purple-900">Validate</button>
                                    <button @click="deleteLicense(license)" class="text-red-600 hover:text-red-900">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create License Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Generate New License</h3>
                    <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600">
                        <XMarkIcon class="h-6 w-6" />
                    </button>
                </div>
                
                <form @submit.prevent="createLicense" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name</label>
                            <input v-model="newLicense.customer_name" type="text" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Customer Email</label>
                            <input v-model="newLicense.customer_email" type="email" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">License Type</label>
                            <select v-model="newLicense.license_type" required
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="iptv">IPTV</option>
                                <option value="hotel_management">Hotel Management</option>
                                <option value="premium">Premium</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                            <input v-model="newLicense.product_name" type="text" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Devices</label>
                            <input v-model.number="newLicense.max_devices" type="number" min="1" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Rooms</label>
                            <input v-model.number="newLicense.max_rooms" type="number" min="1" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Channels</label>
                            <input v-model.number="newLicense.max_channels" type="number" min="1" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expires At</label>
                            <div class="relative">
                                <input ref="licenseExpiryInput" v-model="newLicense.expires_at" type="date"
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                                <div class="absolute inset-0 cursor-pointer" @click="licenseExpiryInput?.showPicker ? licenseExpiryInput.showPicker() : licenseExpiryInput?.focus()"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center">
                            <input v-model="newLicense.vod_enabled" type="checkbox" class="mr-2">
                            <span class="text-sm text-gray-700">VOD Enabled</span>
                        </label>
                        <label class="flex items-center">
                            <input v-model="newLicense.premium_features" type="checkbox" class="mr-2">
                            <span class="text-sm text-gray-700">Premium Features</span>
                        </label>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea v-model="newLicense.notes" rows="3"
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" @click="showCreateModal = false"
                                class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" :disabled="isCreating"
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50">
                            {{ isCreating ? 'Creating...' : 'Generate License' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import {
    KeyIcon,
    ShieldCheckIcon,
    CheckCircleIcon,
    ClockIcon,
    XCircleIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    licenses: Object,
})

const navigation = computed(() => getNavigationForRole('admin'))

const searchQuery = ref('')
const selectedType = ref('')
const selectedStatus = ref('')
const selectedExpiry = ref('')
const showCreateModal = ref(false)
const isCreating = ref(false)

const newLicense = ref({
    customer_name: '',
    customer_email: '',
    license_type: 'iptv',
    product_name: 'IPTV Hotel Management System',
    max_devices: 1,
    max_rooms: 10,
    max_channels: 100,
    vod_enabled: true,
    premium_features: false,
    expires_at: '',
    notes: ''
})

// Sample data - replace with real data from props
const licenses = [
    {
        id: 1,
        license_key: 'IPTV-2024-ABCD-1234',
        customer_name: 'Grand Hotel',
        customer_email: 'admin@grandhotel.com',
        license_type: 'iptv',
        product_name: 'IPTV Hotel Management',
        status: 'active',
        expires_at: '2025-12-31',
        activation_count: 1,
        max_activations: 1,
        max_devices: 50,
        max_rooms: 100
    },
    {
        id: 2,
        license_key: 'HOTEL-2024-EFGH-5678',
        customer_name: 'Luxury Resort',
        customer_email: 'it@luxuryresort.com',
        license_type: 'premium',
        product_name: 'Premium Hotel Suite',
        status: 'active',
        expires_at: '2024-08-15',
        activation_count: 1,
        max_activations: 2,
        max_devices: 100,
        max_rooms: 200
    }
]

const licenseStats = computed(() => ({
    total: licenses.length,
    active: licenses.filter(l => l.status === 'active').length,
    expiring: licenses.filter(l => l.status === 'active' && isExpiringSoon(l.expires_at)).length,
    expired: licenses.filter(l => l.status === 'expired').length
}))

const filteredLicenses = computed(() => {
    return licenses.filter(license => {
        const matchesSearch = !searchQuery.value || 
            license.license_key.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            license.customer_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            license.customer_email.toLowerCase().includes(searchQuery.value.toLowerCase())
        
        const matchesType = !selectedType.value || license.license_type === selectedType.value
        const matchesStatus = !selectedStatus.value || license.status === selectedStatus.value
        
        return matchesSearch && matchesType && matchesStatus
    })
})

const getTypeColor = (type) => {
    const colors = {
        iptv: 'bg-blue-100 text-blue-800',
        hotel_management: 'bg-green-100 text-green-800',
        premium: 'bg-purple-100 text-purple-800'
    }
    return colors[type] || 'bg-gray-100 text-gray-800'
}

const getStatusColor = (status) => {
    const colors = {
        active: 'bg-green-100 text-green-800',
        expired: 'bg-red-100 text-red-800',
        suspended: 'bg-yellow-100 text-yellow-800',
        revoked: 'bg-red-100 text-red-800'
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const formatType = (type) => {
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const getRemainingDays = (expiryDate) => {
    const days = Math.ceil((new Date(expiryDate) - new Date()) / (1000 * 60 * 60 * 24))
    if (days < 0) return 'Expired'
    if (days === 0) return 'Expires today'
    if (days === 1) return '1 day left'
    return `${days} days left`
}

const isExpiringSoon = (expiryDate) => {
    const days = Math.ceil((new Date(expiryDate) - new Date()) / (1000 * 60 * 60 * 24))
    return days <= 30 && days > 0
}

const createLicense = async () => {
    isCreating.value = true
    
    try {
        const response = await fetch('/admin/iptv/licenses', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(newLicense.value)
        })
        
        if (response.ok) {
            alert('License created successfully!')
            showCreateModal.value = false
            // Reset form
            newLicense.value = {
                customer_name: '',
                customer_email: '',
                license_type: 'iptv',
                product_name: 'IPTV Hotel Management System',
                max_devices: 1,
                max_rooms: 10,
                max_channels: 100,
                vod_enabled: true,
                premium_features: false,
                expires_at: '',
                notes: ''
            }
            // Refresh page or update data
            window.location.reload()
        } else {
            alert('Error creating license')
        }
    } catch (error) {
        console.error('Error:', error)
        alert('Error creating license')
    }
    
    isCreating.value = false
}

const viewLicense = (license) => {
    // Navigate to license details
    window.location.href = `/admin/iptv/licenses/${license.id}`
}

const editLicense = (license) => {
    // Navigate to edit page
    window.location.href = `/admin/iptv/licenses/${license.id}/edit`
}

const validateLicense = async (license) => {
    try {
        const response = await fetch('/api/licenses/validate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                license_key: license.license_key
            })
        })
        
        const result = await response.json()
        if (result.valid) {
            alert('License is valid!')
        } else {
            alert(`License validation failed: ${result.message}`)
        }
    } catch (error) {
        alert('Error validating license')
    }
}

const validateAllLicenses = () => {
    alert('Validating all licenses...')
}

const deleteLicense = (license) => {
    if (confirm(`Are you sure you want to delete license ${license.license_key}?`)) {
        // Delete license
        alert('License deleted!')
    }
}
</script>
