<template>
    <DashboardLayout title="Availability Check">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <!-- Header -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Availability Check</h2>
                                <p class="mt-1 text-sm text-gray-600">Check room availability for waitlist conversions</p>
                            </div>
                            <div class="flex space-x-4">
                                <Link :href="route(`${routePrefix}.waitlist.index`)"
                                      class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                    Back to Waitlist
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Availability Grid -->
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th v-for="roomType in roomTypes" :key="roomType.id" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ roomType.name }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(dateData, date) in availability" :key="date">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ formatDate(date) }}
                                        </td>
                                        <td v-for="roomType in roomTypes" :key="roomType.id" class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div v-if="dateData[roomType.id]" class="space-y-1">
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-sm font-medium text-gray-900">{{ dateData[roomType.id].available_rooms }}</span>
                                                    <span class="text-xs text-gray-500">available</span>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-sm text-gray-600">{{ dateData[roomType.id].booked_rooms }} booked</span>
                                                    <span class="text-sm text-gray-600">• {{ formatCurrency(dateData[roomType.id].base_price) }}</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="bg-blue-600 h-2 rounded-full"
                                                         :style="{ width: calculateOccupancyPercentage(dateData[roomType.id]) + '%' }">
                                                    </div>
                                                </div>
                                            </div>
                                            <div v-else class="text-sm text-gray-500">No data</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    availability: Object,
    routePrefix: { type: String, default: 'admin' },
});

const roomTypes = computed(() => {
    // Extract unique room types from the availability data
    const roomTypes = new Set();
    Object.values(props.availability).forEach(dateData => {
        Object.keys(dateData).forEach(roomTypeId => {
            roomTypes.add(dateData[roomTypeId].room_type_name);
        });
    });
    return Array.from(roomTypes);
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

const calculateOccupancyPercentage = (roomData) => {
    if (!roomData || roomData.total_rooms === 0) return 0;
    return Math.round((roomData.booked_rooms / roomData.total_rooms) * 100);
};
</script>
