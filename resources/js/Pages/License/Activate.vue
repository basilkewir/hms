<template>
    <div class="min-h-screen bg-kotel-dark flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <h1 class="text-4xl font-bold text-kotel-yellow mb-2">🏨 Hotel Management System</h1>
                <h2 class="text-2xl font-semibold text-white mb-4">License Activation</h2>
                <p class="text-kotel-sky-blue">Enter your license key to activate the system</p>
            </div>

            <!-- License Form -->
            <div class="bg-white rounded-lg shadow-xl p-8">
                <form @submit.prevent="activateLicense" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">License Key *</label>
                        <input type="text" v-model="form.license_key" required
                               placeholder="XXXX-XXXX-XXXX-XXXX"
                               class="w-full border border-gray-300 rounded-md px-3 py-3 text-center font-mono text-lg focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hotel Name</label>
                        <input type="text" v-model="form.hotel_name"
                               placeholder="Your hotel name"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                    </div>

                    <!-- Error Message -->
                    <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {{ errorMessage }}
                    </div>

                    <!-- Success Message -->
                    <div v-if="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ successMessage }}
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" :disabled="isActivating"
                            class="w-full bg-kotel-yellow text-kotel-black font-semibold py-3 px-4 rounded-md hover:bg-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span v-if="isActivating">Activating License...</span>
                        <span v-else>Activate License</span>
                    </button>
                </form>

                <!-- License Info -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">License Information</h3>
                    <div class="space-y-2 text-sm text-gray-600">
                        <p><strong>Hardware ID:</strong> <code class="bg-gray-100 px-2 py-1 rounded">{{ hardwareId }}</code></p>
                        <p><strong>Server:</strong> {{ serverInfo.domain }}</p>
                        <p><strong>Version:</strong> {{ serverInfo.version }}</p>
                    </div>
                </div>

                <!-- Support -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-500">
                        Need help? Contact support at 
                        <a href="mailto:support@kewirdev.com" class="text-kotel-yellow hover:underline">support@kewirdev.com</a>
                    </p>
                    <p class="text-xs text-gray-400 mt-2">
                        Purchase licenses at <a href="https://kewirdev.com" target="_blank" class="text-kotel-yellow hover:underline">kewirdev.com</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

const form = ref({
    license_key: '',
    hotel_name: ''
})

const isActivating = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const hardwareId = ref('')
const serverInfo = ref({
    domain: window.location.hostname,
    version: '1.0.0'
})

onMounted(() => {
    // Generate hardware fingerprint for display
    const info = {
        domain: window.location.hostname,
        userAgent: navigator.userAgent.substring(0, 50),
        screen: `${screen.width}x${screen.height}`,
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
    }
    hardwareId.value = btoa(JSON.stringify(info)).substring(0, 16)
})

const activateLicense = async () => {
    isActivating.value = true
    errorMessage.value = ''
    successMessage.value = ''

    try {
        const response = await fetch('/api/license/activate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                license_key: form.value.license_key,
                hotel_name: form.value.hotel_name,
                device_info: {
                    device_type: 'management_backend',
                    device_name: 'Hotel Management System',
                    device_model: 'Server',
                    device_os: navigator.platform,
                    device_os_version: navigator.userAgent.substring(0, 100),
                    app_version: serverInfo.value.version,
                    mac_address: hardwareId.value,
                    ip_address: window.location.hostname,
                    metadata: {
                        hotel_name: form.value.hotel_name,
                        browser_info: navigator.userAgent.substring(0, 200),
                        screen_resolution: `${screen.width}x${screen.height}`
                    }
                }
            })
        })

        const data = await response.json()

        if (data.success) {
            successMessage.value = 'License activated successfully! Redirecting...'
            setTimeout(() => {
                router.visit('/dashboard')
            }, 2000)
        } else {
            // Handle specific error messages from the API
            switch (data.message) {
                case 'License not found':
                    errorMessage.value = 'The license key was not found in our system'
                    break
                case 'Invalid license key':
                    errorMessage.value = 'The license key format is invalid'
                    break
                case 'Too many validation attempts. Please try again later.':
                    errorMessage.value = 'Too many attempts. Please wait and try again later.'
                    break
                default:
                    errorMessage.value = data.message || 'License activation failed'
            }
        }
    } catch (error) {
        errorMessage.value = 'Network error: Unable to connect to license server'
        console.error('License activation error:', error)
    }

    isActivating.value = false
}
</script>
