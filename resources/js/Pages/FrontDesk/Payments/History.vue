<template>
    <DashboardLayout title="Payment History" :user="user" :navigation="navigation">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <!-- Page Header -->
            <div class="px-4 py-6 sm:px-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Payment History</h1>
                        <p class="mt-1 text-sm text-gray-600">View all payments you have processed</p>
                    </div>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Guest</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-if="payments.data.length === 0">
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    No payments found
                                </td>
                            </tr>
                            <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ payment.payment_number || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ payment.reservation?.guest?.full_name || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ payment.reservation?.room?.room_number || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatCurrency(payment.amount || 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatMethod(payment.payment_method || '') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full"
                                          :class="getStatusClass(payment.status || 'pending')">
                                        {{ payment.status || 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(payment.processed_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="payments.data.length > 0" class="mt-4 flex justify-between items-center">
                    <div class="text-sm text-gray-700">
                        Showing {{ payments.from }} to {{ payments.to }} of {{ payments.total }} payments
                    </div>
                    <div class="flex gap-2">
                        <Link v-for="link in payments.links" :key="link.label"
                              :href="link.url"
                              :class="[
                                  'px-3 py-1 text-sm rounded',
                                  link.active ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
                                  !link.url ? 'opacity-50 cursor-not-allowed' : ''
                              ]"
                              v-html="link.label">
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { formatCurrency } from '@/Utils/currency.js'

const props = defineProps({
    user: Object,
    payments: {
        type: Object,
        default: () => ({ data: [], links: [], from: 0, to: 0, total: 0 })
    },
})

const navigation = computed(() => getNavigationForRole('front_desk'))

const formatMethod = (method) => {
    if (!method) return 'N/A'
    return method.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleString()
}

const getStatusClass = (status) => {
    const classes = {
        completed: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        failed: 'bg-red-100 text-red-800',
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>
