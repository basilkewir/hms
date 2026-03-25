<template>
    <DashboardLayout title="Service Charges" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Service Charges</h1>
                    <p class="text-gray-600 mt-2">
                        Reservation: {{ reservation.reservation_number }} | 
                        Guest: {{ reservation.guest }} | 
                        Room: {{ reservation.room }}
                    </p>
                </div>
                <Link :href="route('admin.reservations.index')" 
                      class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                    Back to Reservations
                </Link>
            </div>
        </div>

        <!-- Folio Summary -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Folio Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="text-sm text-gray-600">Folio Number</div>
                    <div class="text-lg font-bold text-gray-900">{{ folio.folio_number }}</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-sm text-gray-600">Total Amount</div>
                    <div class="text-lg font-bold text-green-700">{{ formatCurrency(folio.total_amount) }}</div>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <div class="text-sm text-gray-600">Paid Amount</div>
                    <div class="text-lg font-bold text-yellow-700">{{ formatCurrency(folio.paid_amount) }}</div>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                    <div class="text-sm text-gray-600">Balance</div>
                    <div class="text-lg font-bold text-red-700">{{ formatCurrency(folio.balance_amount) }}</div>
                </div>
            </div>
        </div>

        <!-- Add Service Charge -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Add Service Charge</h2>
            <form @submit.prevent="addCharge" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Service *</label>
                    <select v-model="chargeForm.hotel_service_id" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Service</option>
                        <option v-for="service in services" :key="service.id" :value="service.id">
                            {{ service.name }} 
                            <span v-if="service.is_free">(Free)</span>
                            <span v-else>({{ formatCurrency(service.price) }})</span>
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
                    <input v-model="chargeForm.quantity" type="number" step="0.01" min="0.01" required
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                    <div class="relative">
                        <input ref="chargeDateInput" v-model="chargeForm.charge_date" type="date" required
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        <div class="absolute inset-0 cursor-pointer" @click="chargeDateInput?.showPicker ? chargeDateInput.showPicker() : chargeDateInput?.focus()"></div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Time</label>
                    <input v-model="chargeForm.charge_time" type="time"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" :disabled="chargeForm.processing"
                            class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50">
                        <span v-if="chargeForm.processing">Adding...</span>
                        <span v-else>Add Charge</span>
                    </button>
                </div>
            </form>
            <div v-if="chargeForm.notes !== undefined" class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <input v-model="chargeForm.notes" type="text"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Optional notes">
            </div>
        </div>

        <!-- Charges List -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Service Charges</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posted By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="charge in charges" :key="charge.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ charge.charge_date }}<br>
                                <span class="text-xs text-gray-500">{{ charge.charge_time || 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ charge.description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ charge.quantity }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ formatCurrency(charge.unit_price) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ formatCurrency(charge.net_amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ charge.posted_by }}<br>
                                <span class="text-xs text-gray-500">{{ charge.posted_at }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click="voidCharge(charge)" 
                                        class="text-red-600 hover:text-red-900">
                                    Void
                                </button>
                            </td>
                        </tr>
                        <tr v-if="charges.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                No service charges yet. Add one above.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Void Charge Modal -->
        <DialogModal :show="showVoidModal" @close="showVoidModal = false">
            <template #title>Void Charge</template>
            <template #content>
                <form @submit.prevent="confirmVoid" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reason *</label>
                        <textarea v-model="voidForm.void_reason" rows="3" required
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Enter reason for voiding this charge"></textarea>
                    </div>
                    <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200">
                        <button type="button" @click="showVoidModal = false"
                                class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" :disabled="voidForm.processing"
                                class="bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700 disabled:opacity-50">
                            Void Charge
                        </button>
                    </div>
                </form>
            </template>
        </DialogModal>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, useForm, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DialogModal from '@/Components/DialogModal.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useNotification } from '@/Composables/useNotification'
import { formatCurrency } from '@/Utils/currency.js'

const props = defineProps({
    user: Object,
    reservation: Object,
    folio: Object,
    charges: Array,
    services: Array,
    voidRouteName: { type: String, default: 'admin.folio-charges.void' },
    storeRouteName: { type: String, default: 'admin.reservations.service-charges.store' },
})

const navigation = computed(() => getNavigationForRole(props.user?.roles?.[0]?.name || 'admin'))
const { notify } = useNotification()

const showVoidModal = ref(false)
const selectedCharge = ref(null)

const chargeForm = useForm({
    hotel_service_id: '',
    quantity: 1,
    charge_date: new Date().toISOString().split('T')[0],
    charge_time: new Date().toTimeString().slice(0, 5),
    notes: '',
})

const voidForm = useForm({
    void_reason: '',
})

const addCharge = () => {
    chargeForm.post(route(props.storeRouteName, props.reservation.id), {
        onSuccess: () => {
            notify('success', 'Service charge added successfully')
            chargeForm.reset()
            chargeForm.charge_date = new Date().toISOString().split('T')[0]
            chargeForm.charge_time = new Date().toTimeString().slice(0, 5)
            chargeForm.quantity = 1
        }
    })
}

const voidCharge = (charge) => {
    selectedCharge.value = charge
    showVoidModal.value = true
    voidForm.reset()
}

const confirmVoid = () => {
    voidForm.post(route(props.voidRouteName, selectedCharge.value.id), {
        onSuccess: () => {
            notify('success', 'Charge voided successfully')
            showVoidModal.value = false
            selectedCharge.value = null
        }
    })
}
</script>
