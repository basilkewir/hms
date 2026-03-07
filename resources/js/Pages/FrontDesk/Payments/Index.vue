<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <!-- Page Header -->
                <div class="px-4 py-6 sm:px-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Payments</h1>
                            <p class="mt-1 text-sm text-gray-600">Manage guest payments and transactions</p>
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
                    <!-- Recent Payments Section -->
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Payments</h2>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div v-if="recentPayments.length === 0" class="text-center py-4 text-gray-500">
                                No recent payments found.
                            </div>
                            <div v-else class="space-y-3">
                                <div 
                                    v-for="payment in recentPayments" 
                                    :key="payment.id"
                                    class="flex justify-between items-center bg-white p-3 rounded border"
                                >
                                    <div>
                                        <div class="font-medium text-gray-900">{{ payment.guestName }}</div>
                                        <div class="text-sm text-gray-500">{{ payment.time }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-medium text-gray-900">{{ formatCurrency(payment.amount) }}</div>
                                        <div class="text-sm text-gray-500">{{ payment.method }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Current Guests Section -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Current Guests</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div 
                                v-for="guest in currentGuests" 
                                :key="guest.id"
                                class="bg-white rounded-lg border p-4 hover:shadow-md transition-shadow"
                            >
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="font-medium text-gray-900">{{ guest.full_name }}</h3>
                                        <p class="text-sm text-gray-500">Room: {{ guest.reservations[0]?.room?.room_number || 'N/A' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ formatCurrency(guest.folios[0]?.balance_amount || 0) }}
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Balance
                                        </span>
                                    </div>
                                </div>
                                <div class="flex space-x-2 mt-3">
                                    <Link 
                                        :href="route('front-desk.payments.process')"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700"
                                    >
                                        Process Payment
                                    </Link>
                                    <button 
                                        @click="viewBill(guest)"
                                        class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        View Bill
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bill Modal -->
        <Modal :show="showBillModal" @close="showBillModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Guest Bill</h3>
                
                <div v-if="selectedGuest" class="space-y-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Guest:</span>
                                <div class="font-medium">{{ selectedGuest.name }}</div>
                            </div>
                            <div>
                                <span class="text-gray-500">Room:</span>
                                <div class="font-medium">{{ selectedGuest.room }}</div>
                            </div>
                            <div>
                                <span class="text-gray-500">Check-out:</span>
                                <div class="font-medium">{{ selectedGuest.checkOut }}</div>
                            </div>
                            <div>
                                <span class="text-gray-500">Total Amount:</span>
                                <div class="font-medium text-lg text-green-600">{{ formatCurrency(selectedGuest.totalAmount) }}</div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Bill Items</h4>
                        <div v-if="selectedGuest.billItems.length === 0" class="text-center py-4 text-gray-500">
                            No charges found.
                        </div>
                        <div v-else class="space-y-2">
                            <div 
                                v-for="item in selectedGuest.billItems" 
                                :key="item.id"
                                class="flex justify-between items-center bg-white border rounded p-3"
                            >
                                <div>
                                    <div class="font-medium text-gray-900">{{ item.description }}</div>
                                    <div class="text-xs text-gray-500">{{ item.date }} at {{ item.time }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-medium">{{ formatCurrency(item.amount) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button 
                            @click="showBillModal = false"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                        >
                            Close
                        </button>
                        <Link 
                            :href="route('front-desk.payments.process')"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                        >
                            Process Payment
                        </Link>
                    </div>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script>
import { defineComponent } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'

export default defineComponent({
    components: {
        Head,
        Link,
        Modal
    },

    props: {
        user: Object,
        currentGuests: Array,
        recentPayments: Array
    },

    data() {
        return {
            showBillModal: false,
            selectedGuest: null
        }
    },

    methods: {
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(amount)
        },

        async viewBill(guest) {
            try {
                const response = await fetch(route('front-desk.payments.process') + '/bill', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        guest_id: guest.id
                    })
                })

                const data = await response.json()

                if (data.success) {
                    this.selectedGuest = {
                        id: guest.id,
                        name: guest.full_name,
                        room: guest.reservations[0]?.room?.room_number || 'N/A',
                        checkOut: guest.reservations[0]?.check_out_date || 'N/A',
                        billItems: data.billItems || [],
                        totalAmount: data.totalAmount || 0
                    }
                    this.showBillModal = true
                } else {
                    console.error('Error fetching bill:', data.message)
                }
            } catch (error) {
                console.error('Error fetching bill:', error)
            }
        }
    }
})
</script>
