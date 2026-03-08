<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <!-- Page Header -->
                <div class="px-4 py-6 sm:px-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Refunds</h1>
                            <p class="mt-1 text-sm text-gray-600">Manage guest payment refunds</p>
                        </div>
                        <div class="flex space-x-3">
                            <Link
                                :href="route('front-desk.payments.process')"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"></path>
                                </svg>
                                Process Payment
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="p-6">
                    <!-- Refunds List -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Refunds</h2>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div v-if="refunds.length === 0" class="text-center py-4 text-gray-500">
                                No refunds found.
                            </div>
                            <div v-else class="space-y-3">
                                <div
                                    v-for="refund in refunds"
                                    :key="refund.id"
                                    class="flex justify-between items-center bg-white p-3 rounded border"
                                >
                                    <div>
                                        <div class="font-medium text-gray-900">{{ refund.guestName }}</div>
                                        <div class="text-sm text-gray-500">Original: {{ formatCurrency(refund.originalAmount) }}</div>
                                        <div class="text-xs text-gray-400">Refunded: {{ refund.refundedAt }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-medium text-red-600">-{{ formatCurrency(refund.refundedAmount) }}</div>
                                        <div class="text-sm text-gray-500">{{ refund.method }}</div>
                                        <div class="text-xs text-gray-400">{{ refund.reason }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { formatCurrency as formatCurrencyUtil } from '@/Utils/currency.js'

export default defineComponent({
    components: {
        Head,
        Link
    },

    props: {
        user: Object,
        refunds: Array
    },

    methods: {
        formatCurrency(amount) {
            return formatCurrencyUtil(amount || 0)
        }
    }
})
</script>
