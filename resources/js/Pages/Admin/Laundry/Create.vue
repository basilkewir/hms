<script setup>
import { ref, computed } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DatePicker from '@/Components/DatePicker.vue'
import TimePicker from '@/Components/TimePicker.vue'
import { formatCurrency } from '@/Utils/currency.js'

const page = usePage()
const user = computed(() => page.props.auth.user)
const navigation = computed(() => page.props.navigation)

const props = defineProps({
    guests: Array,
    rooms:  Array,
})

const themeColors = computed(() => ({
    background:    'var(--kotel-background)',
    card:          'var(--kotel-card)',
    border:        'var(--kotel-border)',
    textPrimary:   'var(--kotel-text-primary)',
    textSecondary: 'var(--kotel-text-secondary)',
    textTertiary:  'var(--kotel-text-tertiary)',
    primary:       'var(--kotel-primary)',
    success:       'var(--kotel-success)',
    warning:       'var(--kotel-warning)',
    danger:        'var(--kotel-danger)',
}))

const serviceTypes = [
    { value: 'wash',           label: 'Wash' },
    { value: 'dry_clean',      label: 'Dry Clean' },
    { value: 'iron',           label: 'Iron Only' },
    { value: 'wash_iron',      label: 'Wash & Iron' },
    { value: 'dry_clean_iron', label: 'Dry Clean & Iron' },
]

const onGuestChange = () => {
    const guest = props.guests.find(g => g.id === form.guest_id)
    if (guest?.room_id) form.room_id = guest.room_id
}

const form = useForm({
    guest_id:             null,
    room_id:              null,
    priority:             'normal',
    pickup_date:          '',
    delivery_date:        '',
    pickup_time:          '',
    delivery_time:        '',
    payment_status:       'unpaid',
    special_instructions: '',
    notes:                '',
    items: [
        { item_name: '', service_type: 'wash', quantity: 1, unit_price: 0, notes: '' }
    ],
})

const addItem = () => {
    form.items.push({ item_name: '', service_type: 'wash', quantity: 1, unit_price: 0, notes: '' })
}

const removeItem = (index) => {
    if (form.items.length > 1) form.items.splice(index, 1)
}

const itemTotal = (item) => (item.quantity || 0) * (item.unit_price || 0)

const subtotal = computed(() => form.items.reduce((sum, i) => sum + itemTotal(i), 0))

const expressFee = computed(() => {
    if (form.priority === 'express')   return subtotal.value * 0.5
    if (form.priority === 'overnight') return subtotal.value * 0.25
    return 0
})

const total = computed(() => subtotal.value + expressFee.value)

const submit = () => {
    form.post(route('admin.laundry.store'))
}
</script>

<template>
    <DashboardLayout title="New Laundry Order" :user="user" :navigation="navigation">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">New Laundry Order</h1>
                <p :style="{ color: themeColors.textSecondary }">Create a laundry order for a guest</p>
            </div>
            <Link :href="route('admin.laundry.index')"
                  class="px-4 py-2 rounded-lg border text-sm font-medium"
                  :style="{ borderColor: themeColors.border, color: themeColors.textSecondary }">
                Back
            </Link>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Guest & Room -->
            <div class="rounded-xl border p-6"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Guest Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Guest</label>
                        <select v-model="form.guest_id" @change="onGuestChange" class="w-full px-3 py-2 rounded-lg border text-sm"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                            <option :value="null">— Walk-in / No guest —</option>
                            <option v-for="g in guests" :key="g.id" :value="g.id">
                                {{ g.first_name }} {{ g.last_name }} ({{ g.guest_id }}) — Room {{ g.room_number }}
                            </option>
                        </select>
                        <p v-if="form.errors.guest_id" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.guest_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Room</label>
                        <select v-model="form.room_id" class="w-full px-3 py-2 rounded-lg border text-sm"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                            <option :value="null">— No room —</option>
                            <option v-for="r in rooms" :key="r.id" :value="r.id">Room {{ r.room_number }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="rounded-xl border p-6"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Order Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Priority</label>
                        <select v-model="form.priority" class="w-full px-3 py-2 rounded-lg border text-sm"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                            <option value="normal">Normal</option>
                            <option value="express">Express (+50%)</option>
                            <option value="overnight">Overnight (+25%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Payment Status</label>
                        <select v-model="form.payment_status" class="w-full px-3 py-2 rounded-lg border text-sm"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                            <option value="unpaid">Unpaid</option>
                            <option value="paid">Paid</option>
                            <option value="billed_to_room">Billed to Room</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Pickup Date *</label>
                        <DatePicker v-model="form.pickup_date" />
                        <p v-if="form.errors.pickup_date" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.pickup_date }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Pickup Time</label>
                        <TimePicker v-model="form.pickup_time" placeholder="Select pickup time" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Delivery Date *</label>
                        <DatePicker v-model="form.delivery_date" :min="form.pickup_date || undefined" />
                        <p v-if="form.errors.delivery_date" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.delivery_date }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Delivery Time</label>
                        <TimePicker v-model="form.delivery_time" placeholder="Select delivery time" />
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Special Instructions</label>
                    <textarea v-model="form.special_instructions" rows="2" class="w-full px-3 py-2 rounded-lg border text-sm"
                              :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"></textarea>
                </div>
            </div>

            <!-- Items -->
            <div class="rounded-xl border p-6"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Laundry Items</h2>
                    <button type="button" @click="addItem"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium text-white"
                            :style="{ backgroundColor: themeColors.primary }">
                        + Add Item
                    </button>
                </div>
                <p v-if="form.errors.items" class="text-xs mb-3" :style="{ color: themeColors.danger }">{{ form.errors.items }}</p>

                <div class="space-y-3">
                    <div v-for="(item, index) in form.items" :key="index"
                         class="grid grid-cols-12 gap-3 items-start p-3 rounded-lg border"
                         :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background }">
                        <div class="col-span-3">
                            <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Item Name</label>
                            <input v-model="item.item_name" type="text" placeholder="e.g. Shirt" class="w-full px-2 py-1.5 rounded border text-sm"
                                   :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                        </div>
                        <div class="col-span-3">
                            <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Service</label>
                            <select v-model="item.service_type" class="w-full px-2 py-1.5 rounded border text-sm"
                                    :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }">
                                <option v-for="st in serviceTypes" :key="st.value" :value="st.value">{{ st.label }}</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Qty</label>
                            <input v-model.number="item.quantity" type="number" min="1" class="w-full px-2 py-1.5 rounded border text-sm"
                                   :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Unit Price</label>
                            <input v-model.number="item.unit_price" type="number" min="0" step="0.01" class="w-full px-2 py-1.5 rounded border text-sm"
                                   :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                        </div>
                        <div class="col-span-1 pt-5 text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                            {{ formatCurrency(itemTotal(item)) }}
                        </div>
                        <div class="col-span-1 pt-5 flex justify-end">
                            <button type="button" @click="removeItem(index)"
                                    class="p-1 rounded hover:opacity-70"
                                    :style="{ color: themeColors.danger }"
                                    :disabled="form.items.length === 1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Totals -->
                <div class="mt-6 ml-auto max-w-xs space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span :style="{ color: themeColors.textSecondary }">Subtotal</span>
                        <span :style="{ color: themeColors.textPrimary }">{{ formatCurrency(subtotal) }}</span>
                    </div>
                    <div v-if="expressFee > 0" class="flex justify-between">
                        <span :style="{ color: themeColors.textSecondary }">
                            {{ form.priority === 'express' ? 'Express Fee (50%)' : 'Overnight Fee (25%)' }}
                        </span>
                        <span :style="{ color: themeColors.warning }">{{ formatCurrency(expressFee) }}</span>
                    </div>
                    <div class="flex justify-between font-semibold border-t pt-2" :style="{ borderColor: themeColors.border }">
                        <span :style="{ color: themeColors.textPrimary }">Total</span>
                        <span :style="{ color: themeColors.primary }">{{ formatCurrency(total) }}</span>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end space-x-3">
                <Link :href="route('admin.laundry.index')"
                      class="px-5 py-2 rounded-lg border text-sm font-medium"
                      :style="{ borderColor: themeColors.border, color: themeColors.textSecondary }">
                    Cancel
                </Link>
                <button type="submit"
                        :disabled="form.processing"
                        class="px-5 py-2 rounded-lg text-sm font-medium text-white disabled:opacity-50"
                        :style="{ backgroundColor: themeColors.primary }">
                    {{ form.processing ? 'Creating...' : 'Create Order' }}
                </button>
            </div>
        </form>
    </DashboardLayout>
</template>
