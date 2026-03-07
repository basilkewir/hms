<template>
    <DashboardLayout title="Concierge Services" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Concierge Services</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Manage guest concierge requests and services.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="showNewRequest = true"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{
                                backgroundColor: themeColors.primary,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        New Request
                    </button>
                    <button @click="exportRequests"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{
                                backgroundColor: '#8b5cf6',
                            }"
                            @mouseenter="$event.target.style.backgroundColor = '#7c3aed'"
                            @mouseleave="$event.target.style.backgroundColor = '#8b5cf6'">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                        Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Request Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(250, 204, 21, 0.1)' }">
                        <ClockIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Pending</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.pending }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                        <ArrowPathIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">In Progress</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.in_progress }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                        <CheckCircleIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Completed</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.completed }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(107, 114, 128, 0.1)' }">
                        <ClipboardDocumentListIcon class="h-6 w-6" :style="{ color: themeColors.textSecondary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.total }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requests Table -->
        <div class="shadow rounded-lg overflow-hidden"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="px-6 py-4 border-b"
                 :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Recent Requests</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: 'rgba(249, 250, 251, 0.5)' }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Request #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Guest</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Room</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Service Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Requested</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody :style="{ backgroundColor: themeColors.card }">
                        <tr v-if="requests.data.length === 0">
                            <td colspan="7" class="px-6 py-4 text-center"
                                :style="{ color: themeColors.textTertiary }">No requests found</td>
                        </tr>
                        <tr v-for="request in requests.data" :key="request.id"
                            class="hover:bg-opacity-50 transition-colors"
                            :style="{
                                '&:hover': { backgroundColor: themeColors.hover }
                            }">
                            <td class="px-6 py-4 text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">{{ request.request_number }}</td>
                            <td class="px-6 py-4 text-sm"
                                :style="{ color: themeColors.textPrimary }">{{ request.guest_name }}</td>
                            <td class="px-6 py-4 text-sm"
                                :style="{ color: themeColors.textSecondary }">{{ request.room_number || 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm"
                                :style="{ color: themeColors.textPrimary }">{{ request.service_type }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusClass(request.status)">
                                    {{ formatStatus(request.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm"
                                :style="{ color: themeColors.textSecondary }">{{ formatDateTime(request.requested_at) }}</td>
                            <td class="px-6 py-4 text-sm">
                                <button @click="advanceStatus(request)"
                                        class="font-medium transition-colors"
                                        :style="{ color: themeColors.primary }"
                                        @mouseenter="$event.target.style.color = themeColors.hover"
                                        @mouseleave="$event.target.style.color = themeColors.primary">
                                    Advance
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="requests.links" class="px-6 py-4 border-t"
                 :style="{ borderColor: themeColors.border }">
                <Pagination :links="requests.links" />
            </div>
        </div>

        <!-- New Request Modal -->
        <div v-if="showNewRequest" @click="showNewRequest = false"
             class="fixed inset-0 flex items-center justify-center z-50"
             :style="{ backgroundColor: 'rgba(0, 0, 0, 0.5)' }">
            <div @click.stop
                 class="rounded-lg p-6 max-w-md w-full mx-4"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <h2 class="text-xl font-bold mb-4"
                    :style="{ color: themeColors.textPrimary }">New Concierge Request</h2>
                <form @submit.prevent="submitRequest">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Guest Name</label>
                            <input v-model="newRequest.guest_name" type="text" required
                                   class="w-full rounded-md px-3 py-2 transition-colors"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       borderStyle: 'solid',
                                       borderWidth: '1px',
                                       color: themeColors.textPrimary
                                   }"
                                   @focus="$event.target.style.borderColor = themeColors.primary"
                                   @blur="$event.target.style.borderColor = themeColors.border">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Room Number (optional)</label>
                            <input v-model="newRequest.room_number" type="text"
                                   class="w-full rounded-md px-3 py-2 transition-colors"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       borderStyle: 'solid',
                                       borderWidth: '1px',
                                       color: themeColors.textPrimary
                                   }">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Service Type</label>
                            <select v-model="newRequest.service_type" required
                                    class="w-full rounded-md px-3 py-2 transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        borderStyle: 'solid',
                                        borderWidth: '1px',
                                        color: themeColors.textPrimary
                                    }">
                                <option value="">Select Type</option>
                                <option value="Transportation">Transportation</option>
                                <option value="Restaurant Booking">Restaurant Booking</option>
                                <option value="Tour Booking">Tour Booking</option>
                                <option value="Ticket Booking">Ticket Booking</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Details</label>
                            <textarea v-model="newRequest.details" rows="3"
                                      class="w-full rounded-md px-3 py-2 transition-colors"
                                      :style="{
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          borderStyle: 'solid',
                                          borderWidth: '1px',
                                          color: themeColors.textPrimary
                                      }"></textarea>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-6">
                        <button type="submit"
                                class="flex-1 py-2 rounded-md transition-colors font-medium text-white"
                                :style="{
                                    backgroundColor: themeColors.primary,
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                            Submit
                        </button>
                        <button type="button" @click="showNewRequest = false"
                                class="flex-1 py-2 rounded-md transition-colors font-medium"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    borderStyle: 'solid',
                                    borderWidth: '1px',
                                    color: themeColors.textPrimary
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme.js'
import {
    ArrowPathIcon,
    CheckCircleIcon,
    ClockIcon,
    ClipboardDocumentListIcon,
    PlusIcon,
    DocumentArrowDownIcon
} from '@heroicons/vue/24/outline'

// Initialize theme
const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    primary: `var(--kotel-primary)`,
    secondary: `var(--kotel-secondary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
    hover: `rgba(255, 255, 255, 0.1)`
}))

// Load theme on mount
loadTheme()

const props = defineProps({
    user: Object,
    requests: Object,
    stats: Object,
})

const navigation = computed(() => getNavigationForRole('admin'))

const showNewRequest = ref(false)
const newRequest = ref({
    guest_name: '',
    room_number: '',
    service_type: '',
    details: ''
})

const requests = computed(() => props.requests || { data: [], links: [] })
const stats = computed(() => props.stats || {
    pending: 0,
    in_progress: 0,
    completed: 0,
    total: 0
})

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-black',
        in_progress: 'bg-blue-100 text-black',
        completed: 'bg-green-100 text-black',
    }
    return classes[status] || 'bg-gray-100 text-black'
}

const formatStatus = (status) => {
    return status ? status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A'
}

const formatDateTime = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const submitRequest = () => {
    router.post(route('admin.services.concierge.store'), newRequest.value, {
        preserveScroll: true,
        onSuccess: () => {
            showNewRequest.value = false
            newRequest.value = { guest_name: '', room_number: '', service_type: '', details: '' }
        }
    })
}

const advanceStatus = (request) => {
    const nextStatus = request.status === 'pending'
        ? 'in_progress'
        : request.status === 'in_progress'
            ? 'completed'
            : 'completed'

    router.post(route('admin.services.concierge.update-status', request.id), {
        status: nextStatus
    }, {
        preserveScroll: true
    })
}

const exportRequests = () => {
    const data = {
        requests: props.requests.data || [],
        stats: props.stats || {},
        export_date: new Date().toISOString(),
        exported_by: props.user.name
    }

    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `concierge_requests_${new Date().toISOString().split('T')[0]}.json`
    link.click()
    URL.revokeObjectURL(url)
}
</script>
