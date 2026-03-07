<template>
    <DashboardLayout title="Guest Requests" :user="user" :navigation="navigation">
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Guest Requests</h1>
            <p class="text-gray-600 mt-2">Manage guest service requests</p>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="space-y-4">
                <div v-for="request in requests" :key="request.id" class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-medium text-gray-900">{{ request.description }}</h3>
                            <p class="text-sm text-gray-500 mt-1">Room {{ request.room_number }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-medium" :class="{
                            'bg-yellow-100 text-yellow-800': request.status === 'pending',
                            'bg-blue-100 text-blue-800': request.status === 'in_progress',
                            'bg-green-100 text-green-800': request.status === 'completed'
                        }">
                            {{ request.status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'

const props = defineProps({
    user: Object,
    requests: Array,
})

const navigation = computed(() => getNavigationForRole('front_desk'))
</script>
