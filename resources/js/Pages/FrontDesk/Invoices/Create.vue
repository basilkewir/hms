<template>
    <DashboardLayout title="Create Invoice" :user="user" :navigation="navigation">

        <!-- Header Card -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Create Invoice</h1>
                    <p class="mt-1" :style="{ color: themeColors.textSecondary }">Create an invoice for a checked-in guest or an outsider customer.</p>
                </div>
                <Link :href="route('front-desk.invoices.index')"
                      class="flex items-center px-4 py-2 rounded-md transition-colors"
                      :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                    <ArrowLeftIcon class="h-4 w-4 mr-2 inline" />
                    Back
                </Link>
            </div>
        </div>

        <!-- Form Card -->
        <div class="shadow rounded-lg p-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <form @submit.prevent="submitInvoice" class="space-y-8">

                <!-- Invoice Type -->
                <div>
                    <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Invoice Type</h3>
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" v-model="form.invoice_type" value="guest"
                                   class="w-4 h-4" @change="switchInvoiceType">
                            <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">Checked-in Guest</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" v-model="form.invoice_type" value="outsider"
                                   class="w-4 h-4" @change="switchInvoiceType">
                            <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">Outsider Customer</span>
                        </label>
                    </div>
                </div>

                <!-- Guest Selection -->
                <div v-if="form.invoice_type === 'guest'">
                    <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Guest Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                                Select Checked-in Guest *
                            </label>
                            <select v-model="form.reservation_id" required
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="">Select a checked-in guest</option>
                                <option v-for="reservation in reservations" :key="reservation.id" :value="reservation.id">
                                    {{ reservation.guest_name }} • Room {{ reservation.room_number }} • Check-in: {{ reservation.check_in_date }}
                                </option>
                            </select>
                            <div v-if="errors.reservation_id" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                {{ errors.reservation_id }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Outsider Customer Information -->
                <div v-if="form.invoice_type === 'outsider'">
                    <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Customer Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                                Customer Name *
                            </label>
                            <input v-model="form.customer_name" type="text" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="Enter customer name">
                            <div v-if="errors.customer_name" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                {{ errors.customer_name }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                                Email Address
                            </label>
                            <input v-model="form.customer_email" type="email"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="customer@example.com">
                            <div v-if="errors.customer_email" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                {{ errors.customer_email }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                                Phone Number
                            </label>
                            <input v-model="form.customer_phone" type="tel"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="+1 234 567 8900">
                            <div v-if="errors.customer_phone" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                {{ errors.customer_phone }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Items (outsider only) -->
                <div v-if="form.invoice_type === 'outsider'">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Invoice Items</h3>
                        <button type="button" @click="addItem"
                                class="flex items-center gap-1 px-3 py-1.5 rounded-md text-sm font-medium text-white transition-colors"
                                :style="{ backgroundColor: themeColors.primary }">
                            + Add Item
                        </button>
                    </div>

                    <div v-if="form.items.length === 0"
                         class="text-center py-8 border-2 border-dashed rounded-lg"
                         :style="{ borderColor: themeColors.border }">
                        <p class="text-sm" :style="{ color: themeColors.textTertiary }">No items added yet. Click "Add Item" to add invoice line items.</p>
                    </div>

                    <div v-for="(item, index) in form.items" :key="index"
                         class="mb-4 p-4 rounded-lg border"
                         :style="{
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderWidth: '1px',
                             borderStyle: 'solid'
                         }">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">Item {{ index + 1 }}</span>
                            <button type="button" @click="removeItem(index)"
                                    class="text-sm font-medium transition-colors"
                                    :style="{ color: themeColors.danger }">
                                Remove
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                                    Description *
                                </label>
                                <input v-model="item.description" type="text" required
                                       class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                       :style="{
                                           backgroundColor: themeColors.card,
                                           borderColor: themeColors.border,
                                           color: themeColors.textPrimary,
                                           borderWidth: '1px',
                                           borderStyle: 'solid'
                                       }"
                                       placeholder="Item description">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                                    Amount *
                                </label>
                                <input v-model="item.amount" type="number" step="0.01" min="0" required
                                       class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                       :style="{
                                           backgroundColor: themeColors.card,
                                           borderColor: themeColors.border,
                                           color: themeColors.textPrimary,
                                           borderWidth: '1px',
                                           borderStyle: 'solid'
                                       }"
                                       placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <div v-if="errors.items" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                        {{ errors.items }}
                    </div>

                    <!-- Items Total -->
                    <div v-if="form.items.length > 0" class="mt-4 p-3 rounded-md flex justify-end"
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                        <span class="text-sm font-semibold" :style="{ color: themeColors.textPrimary }">
                            Total: {{ formatCurrency(itemsTotal) }}
                        </span>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Additional Notes</h3>
                    <textarea v-model="form.notes" rows="4"
                              class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                              :style="{
                                  backgroundColor: themeColors.background,
                                  borderColor: themeColors.border,
                                  color: themeColors.textPrimary,
                                  borderWidth: '1px',
                                  borderStyle: 'solid'
                              }"
                              placeholder="Additional notes or instructions for this invoice..."></textarea>
                    <div v-if="errors.notes" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                        {{ errors.notes }}
                    </div>
                </div>

                <!-- Submit Actions -->
                <div class="flex items-center gap-3 pt-2 border-t"
                     :style="{ borderColor: themeColors.border }">
                    <button type="submit" :disabled="processing"
                            class="px-6 py-2 rounded-md font-medium text-white transition-colors disabled:opacity-50"
                            :style="{ backgroundColor: themeColors.primary }">
                        <span v-if="processing">Creating...</span>
                        <span v-else>Create Invoice</span>
                    </button>
                    <Link :href="route('front-desk.invoices.index')"
                          class="px-6 py-2 rounded-md font-medium transition-colors"
                          :style="{
                              backgroundColor: themeColors.secondary,
                              color: themeColors.textPrimary
                          }">
                        Cancel
                    </Link>
                </div>

            </form>
        </div>

    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background: 'var(--kotel-background)',
    card: 'var(--kotel-card)',
    border: 'var(--kotel-border)',
    textPrimary: 'var(--kotel-text-primary)',
    textSecondary: 'var(--kotel-text-secondary)',
    textTertiary: 'var(--kotel-text-tertiary)',
    primary: 'var(--kotel-primary)',
    secondary: 'var(--kotel-secondary)',
    success: 'var(--kotel-success)',
    warning: 'var(--kotel-warning)',
    danger: 'var(--kotel-danger)',
    hover: 'rgba(255, 255, 255, 0.1)'
}))
loadTheme()

const props = defineProps({
    user: Object,
    navigation: Array,
    reservations: Array,
    errors: Object
})

const form = useForm({
    invoice_type: 'guest',
    reservation_id: '',
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    items: [],
    notes: ''
})

const processing = ref(false)

const itemsTotal = computed(() => {
    return form.items.reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0)
})

const switchInvoiceType = () => {
    form.reservation_id = ''
    form.customer_name = ''
    form.customer_email = ''
    form.customer_phone = ''
    form.items = []
}

const addItem = () => {
    form.items.push({ description: '', amount: 0 })
}

const removeItem = (index) => {
    form.items.splice(index, 1)
}

const submitInvoice = () => {
    processing.value = true

    if (form.invoice_type === 'guest' && !form.reservation_id) {
        processing.value = false
        return
    }
    if (form.invoice_type === 'outsider') {
        if (!form.customer_name) {
            processing.value = false
            return
        }
        if (form.items.length === 0) {
            processing.value = false
            return
        }
        for (const item of form.items) {
            if (!item.description || item.amount <= 0) {
                processing.value = false
                return
            }
        }
    }

    form.post(route('front-desk.invoices.store'), {
        onSuccess: () => { processing.value = false },
        onError: () => { processing.value = false }
    })
}
</script>

<style scoped>
input::placeholder,
textarea::placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}
input::-webkit-input-placeholder,
textarea::-webkit-input-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}
input::-moz-placeholder,
textarea::-moz-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}
select option[value=""] {
    color: var(--kotel-text-tertiary) !important;
}
</style>
