<template>
    <DashboardLayout title="Waitlist Details" :user="user" :navigation="navigation">
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Waitlist Entry Details</h1>
                </div>
                <Link :href="route(`${routePrefix}.waitlist.index`)"
                      class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                    Back
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-900 mb-3">Guest Information</h3>
                    <div class="space-y-2 text-sm" v-if="waitlist.guest">
                        <div><span class="font-medium">Name:</span> {{ waitlist.guest.full_name }}</div>
                        <div><span class="font-medium">Email:</span> {{ waitlist.guest.email }}</div>
                        <div><span class="font-medium">Phone:</span> {{ waitlist.guest.phone }}</div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-900 mb-3">Request Details</h3>
                    <div class="space-y-2 text-sm">
                        <div><span class="font-medium">Room Type:</span> {{ waitlist.room_type?.name || 'N/A' }}</div>
                        <div><span class="font-medium">Check-in:</span> {{ formatDate(waitlist.requested_check_in) }}</div>
                        <div><span class="font-medium">Check-out:</span> {{ formatDate(waitlist.requested_check_out) }}</div>
                        <div><span class="font-medium">Nights:</span> {{ waitlist.requested_nights }}</div>
                        <div><span class="font-medium">Guests:</span> {{ waitlist.number_of_adults }} adults, {{ waitlist.number_of_children }} children</div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'

const props = defineProps({
    user: Object,
    waitlist: Object,
    routePrefix: { type: String, default: 'admin' },
})

const navigation = computed(() => getNavigationForRole('admin'))

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>
