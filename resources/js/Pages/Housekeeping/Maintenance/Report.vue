<template>
    <DashboardLayout title="Maintenance Report" :user="user">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Maintenance Report</h1>
                    <p class="text-gray-600 mt-2">Report maintenance issues and track repairs.</p>
                </div>
            </div>
        </div>

        <!-- Report Form -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Report New Issue</h3>
            <form @submit.prevent="submitReport" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Room Number</label>
                        <input type="text" v-model="form.roomNumber" required placeholder="e.g., 201"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Issue Category</label>
                        <select v-model="form.category" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Category</option>
                            <option value="plumbing">Plumbing</option>
                            <option value="electrical">Electrical</option>
                            <option value="hvac">HVAC</option>
                            <option value="furniture">Furniture</option>
                            <option value="appliances">Appliances</option>
                            <option value="iptv">IPTV/Electronics</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priority Level</label>
                        <select v-model="form.priority" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="low">Low - Can wait</option>
                            <option value="medium">Medium - Schedule soon</option>
                            <option value="high">High - Needs attention</option>
                            <option value="urgent">Urgent - Immediate action</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Affects Guest Stay?</label>
                        <select v-model="form.affectsGuest" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="no">No</option>
                            <option value="minor">Minor impact</option>
                            <option value="major">Major impact</option>
                            <option value="room_unusable">Room unusable</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Issue Description</label>
                    <textarea v-model="form.description" required rows="4" 
                              placeholder="Describe the issue in detail..."
                              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Location Details</label>
                    <input type="text" v-model="form.location" placeholder="e.g., Bathroom sink, Living room TV"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end">
                    <button type="submit" :disabled="isSubmitting"
                            class="bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700 disabled:opacity-50">
                        <span v-if="isSubmitting">Submitting...</span>
                        <span v-else>Submit Report</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- My Reports -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">My Recent Reports</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Room
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Issue
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Priority
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="report in myReports" :key="report.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ report.roomNumber }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ report.description.substring(0, 50) }}...
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getCategoryColor(report.category)">
                                    {{ formatCategory(report.category) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getPriorityColor(report.priority)">
                                    {{ report.priority }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(report.status)">
                                    {{ report.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDate(report.date) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

const props = defineProps({
    user: Object,
    myReports: Array,
})

const isSubmitting = ref(false)

const form = ref({
    roomNumber: '',
    category: '',
    priority: 'medium',
    affectsGuest: 'no',
    description: '',
    location: '',
})

const myReports = computed(() => props.myReports || [])

const submitReport = () => {
    isSubmitting.value = true
    router.post(route('housekeeping.maintenance.report.store'), form.value, {
        preserveScroll: true,
        onSuccess: () => {
            isSubmitting.value = false
            form.value = {
                roomNumber: '',
                category: '',
                priority: 'medium',
                affectsGuest: 'no',
                description: '',
                location: '',
            }
        },
        onError: () => {
            isSubmitting.value = false
        }
    })
}

const getCategoryColor = (category) => {
    const colors = {
        plumbing: 'bg-blue-100 text-blue-800',
        electrical: 'bg-yellow-100 text-yellow-800',
        hvac: 'bg-green-100 text-green-800',
        furniture: 'bg-purple-100 text-purple-800',
        appliances: 'bg-indigo-100 text-indigo-800',
        iptv: 'bg-red-100 text-red-800',
        other: 'bg-gray-100 text-gray-800',
    }
    return colors[(category || '').toLowerCase()] || 'bg-gray-100 text-gray-800'
}

const getPriorityColor = (priority) => {
    const colors = {
        low: 'bg-gray-100 text-gray-800',
        medium: 'bg-blue-100 text-blue-800',
        high: 'bg-yellow-100 text-yellow-800',
        urgent: 'bg-red-100 text-red-800',
    }
    return colors[(priority || '').toLowerCase()] || 'bg-gray-100 text-gray-800'
}

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800',
        assigned: 'bg-blue-100 text-blue-800',
        in_progress: 'bg-purple-100 text-purple-800',
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
    }
    return colors[(status || '').toLowerCase()] || 'bg-gray-100 text-gray-800'
}

const formatCategory = (category) => {
    return category.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString()
}
</script>
