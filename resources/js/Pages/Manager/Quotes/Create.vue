<template>
    <DashboardLayout title="Create Quote" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Create Quote</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Create quote for customers with full CRUD functionality.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('manager.quotes.index')" 
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ 
                              backgroundColor: '#6b7280',
                          }"
                          @mouseenter="$event.target.style.backgroundColor = '#4b5563'"
                          @mouseleave="$event.target.style.backgroundColor = '#6b7280'">
                        <ArrowLeftIcon class="h-4 w-4 mr-2" />
                        Back to Quotes
                    </Link>
                </div>
            </div>
        </div>

            <!-- Form -->
            <div class="rounded-lg border p-6 shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <form @submit.prevent="submitQuote" class="space-y-6">
                    <!-- Quote Type Selection -->
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                            Quote Type *
                        </label>
                        <div class="flex gap-4">
                            <label class="flex items-center">
                                <input type="radio" v-model="form.quote_type" value="guest" 
                                       class="mr-2" @change="switchQuoteType">
                                <span :style="{ color: themeColors.textPrimary }">Checked-in Guest</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" v-model="form.quote_type" value="outsider" 
                                       class="mr-2" @change="switchQuoteType">
                                <span :style="{ color: themeColors.textPrimary }">Outsider</span>
                            </label>
                        </div>
                    </div>

                    <!-- Guest Selection (for checked-in guests) -->
                    <div v-if="form.quote_type === 'guest'">
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                            Select Checked-in Guest *
                        </label>
                        <select v-model="form.reservation_id" required 
                                class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                            <option value="">Select checked-in guest</option>
                            <option v-for="reservation in reservations" :key="reservation.id" :value="reservation.id">
                                {{ reservation.guest_name }} • Room {{ reservation.room_number }} • Check-in: {{ reservation.check_in_date }}
                            </option>
                        </select>
                        <div v-if="errors.reservation_id" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                            {{ errors.reservation_id }}
                        </div>
                    </div>

                    <!-- Outsider Information -->
                    <div v-if="form.quote_type === 'outsider'" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                                    Customer Name *
                                </label>
                                <input v-model="form.customer_name" type="text" required
                                       class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                       :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                       placeholder="Enter customer name">
                                <div v-if="errors.customer_name" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                    {{ errors.customer_name }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                                    Email *
                                </label>
                                <input v-model="form.customer_email" type="email" required
                                       class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                       :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                       placeholder="customer@example.com">
                                <div v-if="errors.customer_email" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                    {{ errors.customer_email }}
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                                Phone
                            </label>
                            <input v-model="form.customer_phone" type="tel"
                                   class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                   placeholder="+1234567890">
                            <div v-if="errors.customer_phone" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                {{ errors.customer_phone }}
                            </div>
                        </div>
                    </div>

                    <!-- Quote Details -->
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                                    Valid Until *
                                </label>
                                <div class="relative">
                                    <input v-model="form.valid_until" 
                                           type="date" 
                                           required
                                           :min="today"
                                           class="w-full px-3 py-2 pr-10 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer"
                                           :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <svg class="h-4 w-4" :style="{ color: themeColors.textSecondary }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <div v-if="errors.valid_until" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                    {{ errors.valid_until }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                                    Total Amount *
                                </label>
                                <input v-model="form.total_amount" type="number" step="0.01" min="0" required
                                       class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                       :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                       placeholder="0.00">
                                <div v-if="errors.total_amount" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                    {{ errors.total_amount }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                                    Status
                                </label>
                                <select v-model="form.status"
                                        class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                        :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                                    <option value="draft">Draft</option>
                                    <option value="sent">Sent</option>
                                </select>
                            </div>
                        </div>

                        <!-- Quote Items -->
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                                Quote Items
                            </label>
                            <div v-for="(item, index) in form.items" :key="index" class="space-y-2 mb-4 p-4 border rounded-md"
                                 :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
                                    <input v-model="item.description" type="text" placeholder="Item description"
                                           class="px-3 py-2 border rounded-md text-sm focus:outline-none"
                                           :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                                    <input v-model="item.quantity" type="number" min="1" placeholder="Qty"
                                           class="px-3 py-2 border rounded-md text-sm focus:outline-none"
                                           :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                                    <input v-model="item.unit_price" type="number" step="0.01" min="0" placeholder="Unit price"
                                           class="px-3 py-2 border rounded-md text-sm focus:outline-none"
                                           :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                                    <button type="button" @click="removeItem(index)"
                                            class="px-3 py-2 rounded-md text-sm font-medium text-white"
                                            :style="{ backgroundColor: themeColors.danger }">
                                        Remove
                                    </button>
                                </div>
                            </div>
                            <button type="button" @click="addItem"
                                    class="px-4 py-2 rounded-md text-sm font-medium text-white"
                                    :style="{ backgroundColor: themeColors.primary }">
                                + Add Item
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                            Notes
                        </label>
                        <textarea v-model="form.notes" rows="4" 
                                  class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                  :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                  placeholder="Additional notes for this quote..."></textarea>
                        <div v-if="errors.notes" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                            {{ errors.notes }}
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" :disabled="processing"
                                class="px-4 py-2 rounded-md font-medium text-white transition-colors disabled:opacity-50"
                                :style="{ backgroundColor: themeColors.primary }">
                            <span v-if="processing">Creating...</span>
                            <span v-else>Create Quote</span>
                        </button>
                        <button type="button" @click="$inertia.visit(route('manager.quotes.index'))"
                                class="px-4 py-2 rounded-md font-medium transition-colors"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                            Cancel
                        </button>
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
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

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
    hover: 'rgba(255, 255, 255, 0.1)'
}))

// Load theme on mount
loadTheme()

// Get today's date for date picker validation
const today = computed(() => {
    return new Date().toISOString().split('T')[0]
})

const props = defineProps({
    user: Object,
    navigation: Array,
    reservations: Array,
    errors: Object
})

const form = useForm({
    quote_type: 'guest',
    reservation_id: '',
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    valid_until: '',
    total_amount: '',
    status: 'draft',
    notes: '',
    items: [
        { description: '', quantity: 1, unit_price: 0 }
    ]
})

const processing = ref(false)

const switchQuoteType = () => {
    // Clear form fields when switching type
    form.reservation_id = ''
    form.customer_name = ''
    form.customer_email = ''
    form.customer_phone = ''
}

const addItem = () => {
    form.items.push({ description: '', quantity: 1, unit_price: 0 })
}

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1)
    }
}

const submitQuote = () => {
    processing.value = true
    
    // Validate based on quote type
    if (form.quote_type === 'guest' && !form.reservation_id) {
        processing.value = false
        return
    }
    if (form.quote_type === 'outsider' && (!form.customer_name || !form.customer_email)) {
        processing.value = false
        return
    }
    
    form.post(route('manager.quotes.store'), {
        onSuccess: () => {
            processing.value = false
        },
        onError: () => {
            processing.value = false
        }
    })
}
</script>

<style scoped>
/* Enhanced date input styling */
input[type="date"] {
    position: relative;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

input[type="date"]::-webkit-calendar-picker-indicator {
    background: transparent;
    bottom: 0;
    color: transparent;
    cursor: pointer;
    height: auto;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
    width: auto;
}

input[type="date"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
}

input[type="date"]::-webkit-clear-button {
    -webkit-appearance: none;
}

input[type="date"]::-ms-clear {
    display: none;
}

input[type="date"]::-ms-reveal {
    display: none;
}

/* Focus states */
input[type="date"]:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Hover state */
input[type="date"]:hover {
    border-color: #6b7280;
}

/* Firefox specific styling */
@-moz-document url-prefix() {
    input[type="date"] {
        padding-right: 2.5rem;
    }
}

/* Dark theme adjustments */
@media (prefers-color-scheme: dark) {
    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
    }
}

/* Mobile responsive */
@media (max-width: 768px) {
    input[type="date"] {
        font-size: 16px; /* Prevent zoom on iOS */
    }
}
</style>
