<template>
    <DashboardLayout title="View Quote" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Quote #{{ quote.quote_number }}</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">View quote details</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="editQuote"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{
                                backgroundColor: themeColors.primary,
                            }"
                            @mouseenter="$event.target.style.opacity = '0.8'"
                            @mouseleave="$event.target.style.opacity = '1'">
                        <PencilSquareIcon class="h-4 w-4 mr-2" />
                        Edit
                    </button>
                    <Link :href="route('front-desk.quotes.index')"
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

        <!-- Quote Details -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Main Details -->
            <div class="md:col-span-2 rounded-lg border p-6 shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Quote Details</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Customer Name</p>
                        <p class="font-medium" :style="{ color: themeColors.textPrimary }">{{ quote.customer_name || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Email</p>
                        <p class="font-medium" :style="{ color: themeColors.textPrimary }">{{ quote.customer_email || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Phone</p>
                        <p class="font-medium" :style="{ color: themeColors.textPrimary }">{{ quote.customer_phone || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Quote Type</p>
                        <p class="font-medium" :style="{ color: themeColors.textPrimary }">{{ quote.quote_type === 'guest' ? 'Checked-in Guest' : 'Outsider' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Issue Date</p>
                        <p class="font-medium" :style="{ color: themeColors.textPrimary }">{{ formatDate(quote.issue_date) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Valid Until</p>
                        <p class="font-medium" :style="{ color: themeColors.textPrimary }">{{ formatDate(quote.valid_until) }}</p>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="quote.notes" class="mt-4 pt-4 border-t" :style="{ borderColor: themeColors.border }">
                    <p class="text-xs font-medium mb-2" :style="{ color: themeColors.textSecondary }">Notes</p>
                    <p :style="{ color: themeColors.textPrimary }">{{ quote.notes }}</p>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="rounded-lg border p-6 shadow-sm h-fit"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Summary</h2>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span :style="{ color: themeColors.textSecondary }">Status:</span>
                        <span class="px-2 py-1 rounded-full text-xs font-medium"
                              :style="getStatusStyle(quote.status)">
                            {{ quote.status }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span :style="{ color: themeColors.textSecondary }">Total Amount:</span>
                        <span class="font-semibold text-lg" :style="{ color: themeColors.primary }">
                            {{ formatCurrency(quote.total_amount) }}
                        </span>
                    </div>
                    <div v-if="!isExpired" class="flex justify-between text-sm">
                        <span :style="{ color: themeColors.textSecondary }">Days Until Expiry:</span>
                        <span :style="{ color: getDaysColor() }">{{ daysUntilExpiry }} days</span>
                    </div>
                    <div v-else class="flex justify-between text-sm">
                        <span :style="{ color: themeColors.textSecondary }">Expired:</span>
                        <span class="text-red-600 font-medium">Yes</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 space-y-2">
                    <button v-if="quote.status === 'draft'" @click="sendQuote"
                            class="w-full px-4 py-2 rounded-md font-medium text-white transition-colors"
                            :style="{ backgroundColor: '#a855f7' }"
                            @mouseenter="$event.target.style.opacity = '0.8'"
                            @mouseleave="$event.target.style.opacity = '1'">
                        📧 Send Quote
                    </button>
                    <button v-if="quote.status === 'sent'" @click="convertToInvoice"
                            class="w-full px-4 py-2 rounded-md font-medium text-white transition-colors"
                            :style="{ backgroundColor: '#f97316' }"
                            @mouseenter="$event.target.style.opacity = '0.8'"
                            @mouseleave="$event.target.style.opacity = '1'">
                        📄 Convert to Invoice
                    </button>
                    <button @click="printQuote"
                            class="w-full px-4 py-2 rounded-md font-medium text-white transition-colors"
                            :style="{ backgroundColor: '#0ea5e9' }"
                            @mouseenter="$event.target.style.opacity = '0.8'"
                            @mouseleave="$event.target.style.opacity = '1'">
                        🖨️ Print
                    </button>
                </div>
            </div>
        </div>

        <!-- Quote Items -->
        <div class="rounded-lg border shadow-sm overflow-hidden"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Description</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Quantity</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Unit Price</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" :style="{ borderColor: themeColors.border }">
                        <tr v-for="item in quote.items" :key="item.id">
                            <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ item.description }}
                            </td>
                            <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ item.quantity }}
                            </td>
                            <td class="px-4 py-3 text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(item.unit_price) }}
                            </td>
                            <td class="px-4 py-3 text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(item.quantity * item.unit_price) }}
                            </td>
                        </tr>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <td colspan="3" class="px-4 py-3 text-right font-medium" :style="{ color: themeColors.textPrimary }">
                                Total Amount:
                            </td>
                            <td class="px-4 py-3 text-sm font-bold" :style="{ color: themeColors.primary }">
                                {{ formatCurrency(quote.total_amount) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme.js'
import {
    ArrowLeftIcon,
    PencilSquareIcon,
} from '@heroicons/vue/24/outline'

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
}))

loadTheme()

const props = defineProps({
    user: Object,
    navigation: Array,
    quote: {
        type: Object,
        required: true
    }
})

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString()
}

const getStatusStyle = (status) => {
    const styles = {
        draft: { backgroundColor: '#6b7280', color: 'white' },
        sent: { backgroundColor: '#f59e0b', color: 'white' },
        accepted: { backgroundColor: '#10b981', color: 'white' },
        rejected: { backgroundColor: '#ef4444', color: 'white' },
        expired: { backgroundColor: '#dc2626', color: 'white' }
    }
    return styles[status] || { backgroundColor: '#6b7280', color: 'white' }
}

const isExpired = computed(() => {
    const validUntil = new Date(props.quote.valid_until)
    const today = new Date()
    return validUntil < today
})

const daysUntilExpiry = computed(() => {
    const validUntil = new Date(props.quote.valid_until)
    const today = new Date()
    const diffTime = validUntil - today
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    return Math.max(0, diffDays)
})

const getDaysColor = () => {
    if (daysUntilExpiry.value <= 0) return '#dc2626'
    if (daysUntilExpiry.value <= 3) return '#f59e0b'
    return '#10b981'
}

const editQuote = () => {
    router.get(route('front-desk.quotes.edit', props.quote.id))
}

const sendQuote = () => {
    if (confirm(`Send quote #${props.quote.quote_number} to ${props.quote.customer_email}?`)) {
        router.patch(route('front-desk.quotes.update', props.quote.id), { status: 'sent' })
    }
}

const convertToInvoice = () => {
    if (confirm(`Convert quote #${props.quote.quote_number} to invoice?`)) {
        router.post(route('front-desk.invoices.store'), {
            quote_id: props.quote.id,
            customer_name: props.quote.customer_name,
            customer_email: props.quote.customer_email,
            total_amount: props.quote.total_amount
        }, {
            onSuccess: () => {
                alert('Invoice created successfully!')
            }
        })
    }
}

const printQuote = () => {
    window.print()
}
</script>
