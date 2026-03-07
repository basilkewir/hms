<template>
    <DashboardLayout title="Email Settings" :user="user">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Email Settings</h1>
                    <p class="text-gray-600 mt-2">Configure email server settings and templates.</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="testConnection" 
                            class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700">
                        <WifiIcon class="h-4 w-4 mr-2 inline" />
                        Test Connection
                    </button>
                    <button @click="saveSettings" 
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        <CheckIcon class="h-4 w-4 mr-2 inline" />
                        Save Settings
                    </button>
                </div>
            </div>
        </div>

        <div v-if="statusMessage" class="rounded-lg p-4 mb-6 border"
             :class="statusType === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700'">
            {{ statusMessage }}
        </div>

        <!-- SMTP Configuration -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">SMTP Configuration</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Host</label>
                    <input type="text" v-model="emailSettings.smtp_host" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="smtp.gmail.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Port</label>
                    <input type="number" v-model="emailSettings.smtp_port" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="587">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                    <input type="text" v-model="emailSettings.smtp_username" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="your-email@gmail.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password" v-model="emailSettings.smtp_password" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="••••••••">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Encryption</label>
                    <select v-model="emailSettings.smtp_encryption"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="tls">TLS</option>
                        <option value="ssl">SSL</option>
                        <option value="">None</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Email</label>
                    <input type="email" v-model="emailSettings.from_email" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="noreply@hotel.com">
                </div>
            </div>
        </div>

        <!-- Email Templates -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Email Templates</h3>
            <div class="space-y-6">
                <div v-for="template in emailTemplates" :key="template.id"
                     class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="font-medium text-gray-900">{{ template.name }}</h4>
                            <p class="text-sm text-gray-500">{{ template.description }}</p>
                        </div>
                        <div class="flex space-x-2">
                            <button @click="editTemplate(template)" 
                                    class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                Edit
                            </button>
                            <button @click="previewTemplate(template)" 
                                    class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700">
                                Preview
                            </button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                            <input type="text" v-model="template.subject" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select v-model="template.status"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Queue Status -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Email Queue Status</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Recipient
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Subject
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Queued At
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Sent At
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="email in emailQueue" :key="email.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ email.recipient }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ email.subject }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(email.status)">
                                    {{ formatStatus(email.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDateTime(email.queued_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ email.sent_at ? formatDateTime(email.sent_at) : '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {
    WifiIcon,
    CheckIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
})

const statusMessage = ref('')
const statusType = ref('success')

const defaultTemplates = [
    {
        id: 1,
        name: 'Booking Confirmation',
        description: 'Sent when a reservation is confirmed',
        subject: 'Booking Confirmation - {{booking_number}}',
        status: 'active'
    },
    {
        id: 2,
        name: 'Check-in Reminder',
        description: 'Sent 24 hours before check-in',
        subject: 'Check-in Reminder - {{hotel_name}}',
        status: 'active'
    },
    {
        id: 3,
        name: 'Welcome Email',
        description: 'Sent upon guest arrival',
        subject: 'Welcome to {{hotel_name}}',
        status: 'active'
    }
]

const emailSettings = ref({
    smtp_host: 'smtp.gmail.com',
    smtp_port: 587,
    smtp_username: '',
    smtp_password: '',
    smtp_encryption: 'tls',
    from_email: 'noreply@hotel.com'
})

const emailTemplates = ref([...defaultTemplates])

const emailQueue = ref([
    {
        id: 1,
        recipient: 'john@example.com',
        subject: 'Booking Confirmation - HTL001234',
        status: 'sent',
        queued_at: new Date(Date.now() - 3600000),
        sent_at: new Date(Date.now() - 3000000)
    },
    {
        id: 2,
        recipient: 'mary@example.com',
        subject: 'Check-in Reminder - Grand Hotel',
        status: 'pending',
        queued_at: new Date(Date.now() - 1800000),
        sent_at: null
    }
])

const getStatusColor = (status) => {
    const colors = {
        sent: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        failed: 'bg-red-100 text-red-800'
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1)
}

const formatDateTime = (date) => {
    return new Date(date).toLocaleString()
}

const loadSettings = async () => {
    try {
        const response = await fetch('/api/settings/email')
        const settings = await response.json()
        const map = {}
        settings.forEach((item) => {
            map[item.key] = item.value
        })

        emailSettings.value.smtp_host = map['email.smtp_host'] || emailSettings.value.smtp_host
        emailSettings.value.smtp_port = map['email.smtp_port'] || emailSettings.value.smtp_port
        emailSettings.value.smtp_username = map['email.smtp_username'] || ''
        emailSettings.value.smtp_password = map['email.smtp_password'] || ''
        emailSettings.value.smtp_encryption = map['email.smtp_encryption'] || 'tls'
        emailSettings.value.from_email = map['email.from_email'] || emailSettings.value.from_email

        if (map['email.templates']) {
            emailTemplates.value = map['email.templates']
        }
    } catch (error) {
        statusType.value = 'error'
        statusMessage.value = 'Failed to load email settings.'
    }
}

const testConnection = () => {
    statusType.value = 'success'
    statusMessage.value = 'Settings saved. Please send a test email from a reservation confirmation.'
    saveSettings()
}

const saveSettings = () => {
    const settingsPayload = {
        'email.smtp_host': { value: emailSettings.value.smtp_host, group: 'email' },
        'email.smtp_port': { value: emailSettings.value.smtp_port, group: 'email', type: 'integer' },
        'email.smtp_username': { value: emailSettings.value.smtp_username, group: 'email' },
        'email.smtp_password': { value: emailSettings.value.smtp_password, group: 'email' },
        'email.smtp_encryption': { value: emailSettings.value.smtp_encryption, group: 'email' },
        'email.from_email': { value: emailSettings.value.from_email, group: 'email' },
        'email.templates': { value: emailTemplates.value, group: 'email', type: 'json' },
    }

    router.post(route('admin.settings.update'), { settings: settingsPayload }, {
        onSuccess: () => {
            statusType.value = 'success'
            statusMessage.value = 'Email settings saved successfully.'
        },
        onError: () => {
            statusType.value = 'error'
            statusMessage.value = 'Failed to save email settings.'
        }
    })
}

const editTemplate = () => {
    statusType.value = 'success'
    statusMessage.value = 'Edit the template fields directly and click Save Settings.'
}

const previewTemplate = () => {
    statusType.value = 'success'
    statusMessage.value = 'Preview is not available yet; template fields are saved for future use.'
}

onMounted(() => {
    loadSettings()
})
</script>
