<template>
    <DashboardLayout title="Edit Group Booking">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <!-- Header Section -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Edit Group Booking #{{ groupBooking.group_number }}</h1>
                    <div class="flex items-center gap-4">
                        <p class="text-sm"
                           :style="{ color: themeColors.textSecondary }">Status:
                            <span class="px-2 py-1 text-xs rounded-full ml-1" :style="getStatusStyle(groupBooking.status)">
                                {{ formatStatus(groupBooking.status) }}
                            </span>
                        </p>
                        <p class="text-sm"
                           :style="{ color: themeColors.textSecondary }">
                            Created: {{ formatDate(groupBooking.created_at) }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.group-bookings.index')"
                          class="px-4 py-2 rounded-md transition-colors font-medium"
                          :style="{
                              backgroundColor: themeColors.primary,
                              color: 'white'
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        Back
                    </Link>
                </div>
            </div>

            <!-- Group Information Section -->
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Group Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Group Name *</label>
                            <input v-model="form.group_name" type="text" required
                                   class="w-full rounded-md transition-colors"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px'
                                   }"
                                   :class="{ 'border-red-500': form.errors.group_name }">
                            <div v-if="form.errors.group_name" class="text-sm mt-1"
                                 :style="{ color: themeColors.danger }">{{ form.errors.group_name }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Primary Guest *</label>
                            <select v-model="form.primary_guest_id" required
                                    class="w-full rounded-md transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px'
                                    }"
                                    :class="{ 'border-red-500': form.errors.primary_guest_id }">
                                <option value="">Select Primary Guest</option>
                                <option v-for="guest in guests" :key="guest.id" :value="guest.id">
                                    {{ guest.first_name }} {{ guest.last_name }} ({{ guest.email }})
                                </option>
                            </select>
                            <div v-if="form.errors.primary_guest_id" class="text-sm mt-1"
                                 :style="{ color: themeColors.danger }">{{ form.errors.primary_guest_id }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Contact Person</label>
                            <select v-model="form.contact_person_id"
                                    class="w-full rounded-md transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px'
                                    }">
                                <option value="">Select Contact Person</option>
                                <option v-for="guest in guests" :key="guest.id" :value="guest.id">
                                    {{ guest.first_name }} {{ guest.last_name }} ({{ guest.email }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Status *</label>
                            <select v-model="form.status" required
                                    class="w-full rounded-md transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px'
                                    }"
                                    :class="{ 'border-red-500': form.errors.status }">
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="checked_in">Checked In</option>
                                <option value="checked_out">Checked Out</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <div v-if="form.errors.status" class="text-sm mt-1"
                                 :style="{ color: themeColors.danger }">{{ form.errors.status }}</div>
                        </div>
                    </div>
                </div>

                <!-- Dates and Capacity Section -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Dates & Capacity</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Check-in Date *</label>
                            <DatePicker v-model="form.check_in_date" required
                                        :min="new Date().toISOString().split('T')[0]"
                                        :style="{ width: '100%' }"
                                        :class="{ 'border-red-500': form.errors.check_in_date }" />
                            <div v-if="form.errors.check_in_date" class="text-sm mt-1"
                                 :style="{ color: themeColors.danger }">{{ form.errors.check_in_date }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Check-out Date *</label>
                            <DatePicker v-model="form.check_out_date" required
                                        :min="form.check_in_date || new Date().toISOString().split('T')[0]"
                                        :style="{ width: '100%' }"
                                        :class="{ 'border-red-500': form.errors.check_out_date }" />
                            <div v-if="form.errors.check_out_date" class="text-sm mt-1"
                                 :style="{ color: themeColors.danger }">{{ form.errors.check_out_date }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Total Rooms *</label>
                            <input v-model.number="form.total_rooms" type="number" min="1" required
                                   class="w-full rounded-md transition-colors"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px'
                                   }"
                                   :class="{ 'border-red-500': form.errors.total_rooms }">
                            <div v-if="form.errors.total_rooms" class="text-sm mt-1"
                                 :style="{ color: themeColors.danger }">{{ form.errors.total_rooms }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Total Adults *</label>
                            <input v-model.number="form.total_adults" type="number" min="1" required
                                   class="w-full rounded-md transition-colors"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px'
                                   }"
                                   :class="{ 'border-red-500': form.errors.total_adults }">
                            <div v-if="form.errors.total_adults" class="text-sm mt-1"
                                 :style="{ color: themeColors.danger }">{{ form.errors.total_adults }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Total Children</label>
                            <input v-model.number="form.total_children" type="number" min="0"
                                   class="w-full rounded-md transition-colors"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px'
                                   }"
                                   :class="{ 'border-red-500': form.errors.total_children }">
                            <div v-if="form.errors.total_children" class="text-sm mt-1"
                                 :style="{ color: themeColors.danger }">{{ form.errors.total_children }}</div>
                        </div>
                    </div>
                </div>

                <!-- Billing Information Section -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Billing Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Billing Type *</label>
                            <select v-model="form.billing_type" required
                                    class="w-full rounded-md transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px'
                                    }"
                                    :class="{ 'border-red-500': form.errors.billing_type }">
                                <option value="consolidated">Consolidated</option>
                                <option value="individual">Individual</option>
                                <option value="split">Split</option>
                            </select>
                            <div v-if="form.errors.billing_type" class="text-sm mt-1"
                                 :style="{ color: themeColors.danger }">{{ form.errors.billing_type }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Group Discount (%)</label>
                            <input v-model.number="form.group_discount_percentage" type="number" min="0" max="100"
                                   class="w-full rounded-md transition-colors"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px'
                                   }"
                                   :class="{ 'border-red-500': form.errors.group_discount_percentage }">
                            <div v-if="form.errors.group_discount_percentage" class="text-sm mt-1"
                                 :style="{ color: themeColors.danger }">{{ form.errors.group_discount_percentage }}</div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Billing Instructions</label>
                            <textarea v-model="form.billing_instructions"
                                      rows="3"
                                      class="w-full rounded-md transition-colors"
                                      :style="{
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px'
                                      }"
                                      :class="{ 'border-red-500': form.errors.billing_instructions }">
                            </textarea>
                            <div v-if="form.errors.billing_instructions" class="text-sm mt-1"
                                 :style="{ color: themeColors.danger }">{{ form.errors.billing_instructions }}</div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information Section -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Additional Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Special Requests</label>
                            <textarea v-model="form.special_requests"
                                      rows="3"
                                      class="w-full rounded-md transition-colors"
                                      :style="{
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px'
                                      }"
                                      :class="{ 'border-red-500': form.errors.special_requests }">
                            </textarea>
                            <div v-if="form.errors.special_requests" class="text-sm mt-1"
                                 :style="{ color: themeColors.danger }">{{ form.errors.special_requests }}</div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Group Notes</label>
                            <textarea v-model="form.group_notes"
                                      rows="3"
                                      class="w-full rounded-md transition-colors"
                                      :style="{
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px'
                                      }"
                                      :class="{ 'border-red-500': form.errors.group_notes }">
                            </textarea>
                            <div v-if="form.errors.group_notes" class="text-sm mt-1"
                                 :style="{ color: themeColors.danger }">{{ form.errors.group_notes }}</div>
                        </div>
                    </div>
                </div>

                <!-- Payment Section for Confirmed Bookings -->
                <div v-if="form.status === 'confirmed'" class="shadow rounded-lg p-6"
                     :style="{
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border
                     }">
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Add Payment</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Payment Amount *</label>
                            <input v-model.number="paymentForm.amount" type="number" min="0" step="0.01" required
                                   class="w-full rounded-md transition-colors"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px'
                                   }"
                                   :class="{ 'border-red-500': paymentForm.errors.amount }">
                            <div v-if="paymentForm.errors.amount" class="text-sm mt-1"
                                 :style="{ color: themeColors.danger }">{{ paymentForm.errors.amount }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Payment Type *</label>
                            <select v-model="paymentForm.payment_type" required
                                    class="w-full rounded-md transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px'
                                    }">
                                <option value="full">Full Payment</option>
                                <option value="partial">Partial Payment</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Payment Method *</label>
                            <select v-model="paymentForm.payment_method" required
                                    class="w-full rounded-md transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px'
                                    }">
                                <option value="cash">Cash</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="debit_card">Debit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="mobile_money">Mobile Money</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Payment Notes</label>
                            <textarea v-model="paymentForm.notes"
                                      rows="2"
                                      class="w-full rounded-md transition-colors"
                                      :style="{
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px'
                                      }">
                            </textarea>
                        </div>
                    </div>
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t"
                         :style="{
                             borderTopColor: themeColors.border,
                             borderTopWidth: '1px'
                         }">
                        <button @click="addPayment" :disabled="paymentForm.processing"
                                class="px-6 py-2 rounded-md transition-colors font-medium text-white"
                                :style="{
                                    backgroundColor: themeColors.success,
                                }"
                                :class="{ 'opacity-50 cursor-not-allowed': paymentForm.processing }"
                                @mouseenter="$event.target.style.backgroundColor = '#166534'"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                            <span v-if="paymentForm.processing">Processing...</span>
                            <span v-else>Add Payment</span>
                        </button>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t"
                     :style="{
                         borderTopColor: themeColors.border,
                         borderTopWidth: '1px'
                     }">
                    <Link :href="route('admin.group-bookings.index')"
                          class="px-4 py-2 rounded-md transition-colors font-medium"
                          :style="{
                              backgroundColor: themeColors.secondary,
                              color: themeColors.textPrimary
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                            class="px-6 py-2 rounded-md transition-colors font-medium text-white"
                            :style="{
                                backgroundColor: themeColors.warning,
                            }"
                            :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                            @mouseenter="$event.target.style.backgroundColor = '#d97706'"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.warning">
                        <span v-if="form.processing">Updating...</span>
                        <span v-else>Update Group Booking</span>
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DatePicker from '@/Components/DatePicker.vue'
import { useTheme } from '@/Composables/useTheme.js'

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
    groupBooking: Object,
    guests: Array,
})

const form = useForm({
    group_name: props.groupBooking.group_name,
    primary_guest_id: props.groupBooking.primary_guest_id,
    contact_person_id: props.groupBooking.contact_person_id,
    check_in_date: props.groupBooking.check_in_date,
    check_out_date: props.groupBooking.check_out_date,
    total_rooms: props.groupBooking.total_rooms,
    total_adults: props.groupBooking.total_adults,
    total_children: props.groupBooking.total_children,
    group_discount_percentage: props.groupBooking.group_discount_percentage,
    billing_type: props.groupBooking.billing_type,
    billing_instructions: props.groupBooking.billing_instructions,
    special_requests: props.groupBooking.special_requests,
    group_notes: props.groupBooking.group_notes,
    status: props.groupBooking.status,
})

const paymentForm = useForm({
    amount: '',
    payment_method: 'cash',
    payment_type: 'full',
    notes: ''
})

const submit = () => {
    form.put(route('admin.group-bookings.update', props.groupBooking.id))
}

const addPayment = () => {
    paymentForm.post(route('admin.group-bookings.add-payment', props.groupBooking.id), {
        onSuccess: () => {
            paymentForm.reset()
            // Optionally show a success message or refresh the page
        }
    })
}

// Utility functions
const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const formatStatus = (status) => {
    return status ? status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A'
}

const getStatusStyle = (status) => {
    const styles = {
        pending: {
            backgroundColor: `var(--kotel-warning)`,
            color: 'white'
        },
        confirmed: {
            backgroundColor: `var(--kotel-primary)`,
            color: 'white'
        },
        checked_in: {
            backgroundColor: `var(--kotel-success)`,
            color: 'white'
        },
        checked_out: {
            backgroundColor: `var(--kotel-secondary)`,
            color: 'white'
        },
        cancelled: {
            backgroundColor: `var(--kotel-danger)`,
            color: 'white'
        },
        no_show: {
            backgroundColor: `var(--kotel-danger)`,
            color: 'white'
        },
        modified: {
            backgroundColor: `var(--kotel-warning)`,
            color: 'white'
        },
    }
    return styles[status] || styles['pending']
}
</script>
