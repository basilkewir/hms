<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import {
    ArrowLeftIcon,
    PencilIcon,
    TrashIcon,
    CurrencyDollarIcon,
    CalendarIcon,
    BuildingOfficeIcon,
    DocumentTextIcon,
    CreditCardIcon,
    UserIcon,
    TagIcon,
    CheckCircleIcon,
    ClockIcon,
    XCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    expense:        { type: Object, required: true },
    receipt_url:    { type: String, default: null },
    submitter_name: { type: String, default: null },
    approver_name:  { type: String, default: null },
    routePrefix:    { type: String, default: 'admin' },
})

const isImage = computed(() => {
    if (!props.receipt_url) return false
    return /\.(jpg|jpeg|png|webp|gif)$/i.test(props.receipt_url)
})

const showImagePreview = ref(false)

const page = usePage()
const navigation = computed(() => {
    return page.props.navigation || []
})

// Theme system
const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    primary: `var(--kotel-primary)`,
    primaryHover: `var(--kotel-primary-hover)`,
    secondary: `var(--kotel-secondary)`,
    success: `var(--kotel-success)`,
    successHover: `var(--kotel-success-hover)`,
    danger: `var(--kotel-danger)`,
    dangerHover: `var(--kotel-danger-hover)`,
    warning: `var(--kotel-warning)`,
    warningHover: `var(--kotel-warning-hover)`,
    hover: `rgba(255, 255, 255, 0.1)`,
    textOnPrimary: `var(--kotel-text-on-primary)`
}))

// Load theme on mount
loadTheme()

// Status helper functions
const getStatusColor = (status) => {
    switch (status?.toLowerCase()) {
        case 'approved':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
        case 'rejected':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
    }
}

const getStatusIcon = (status) => {
    switch (status?.toLowerCase()) {
        case 'approved':
            return CheckCircleIcon
        case 'pending':
            return ClockIcon
        case 'rejected':
            return XCircleIcon
        default:
            return ClockIcon
    }
}

const formatStatus = (status) => {
    if (!status) return 'Unknown'
    return status.charAt(0).toUpperCase() + status.slice(1).toLowerCase()
}

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const formatCurrency = (amount, currency = 'XAF') => {
    if (!amount) return 'N/A'
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency || 'XAF',
        minimumFractionDigits: 2
    }).format(amount)
}

const formatPaymentMethod = (method) => {
    if (!method) return 'N/A'
    return method.charAt(0).toUpperCase() + method.slice(1).toLowerCase()
}

const processing = ref(false)
const showRejectModal = ref(false)
const rejectNotes = ref('')

const approve = () => {
    if (processing.value) return
    processing.value = true
    router.post(route(`${props.routePrefix}.expenses.approve`, props.expense.id), {}, {
        onFinish: () => { processing.value = false }
    })
}

const openRejectModal = () => {
    rejectNotes.value = ''
    showRejectModal.value = true
}

const submitReject = () => {
    if (processing.value) return
    processing.value = true
    router.post(route(`${props.routePrefix}.expenses.reject`, props.expense.id), { approval_notes: rejectNotes.value }, {
        onSuccess: () => { showRejectModal.value = false },
        onFinish: () => { processing.value = false }
    })
}
</script>

<template>
    <DashboardLayout :navigation="navigation">
        <div class="min-h-screen p-6"
             :style="{ backgroundColor: themeColors.background }">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <Link :href="route(`${routePrefix}.expenses.index`)"
                              class="p-2 rounded-lg transition-colors"
                              :style="{ 
                                  backgroundColor: themeColors.secondary,
                                  color: themeColors.textPrimary 
                              }"
                              @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                              @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                            <ArrowLeftIcon class="h-5 w-5" />
                        </Link>
                        <div>
                            <h1 class="text-2xl font-bold"
                                :style="{ color: themeColors.textPrimary }">Expense Details</h1>
                            <p class="mt-1 text-sm"
                               :style="{ color: themeColors.textSecondary }">View expense information and details</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <!-- Approve / Reject (only when pending) -->
                        <template v-if="expense.status === 'pending'">
                            <button @click="approve" :disabled="processing"
                                class="px-4 py-2 rounded-lg font-medium text-sm flex items-center gap-2 text-white transition-colors disabled:opacity-60"
                                :style="{ backgroundColor: themeColors.success }">
                                <CheckCircleIcon class="h-4 w-4" />
                                Approve
                            </button>
                            <button @click="openRejectModal" :disabled="processing"
                                class="px-4 py-2 rounded-lg font-medium text-sm flex items-center gap-2 text-white transition-colors disabled:opacity-60"
                                :style="{ backgroundColor: themeColors.danger }">
                                <XCircleIcon class="h-4 w-4" />
                                Reject
                            </button>
                        </template>
                        <Link :href="route(`${routePrefix}.expenses.edit`, expense)"
                              class="px-4 py-2 rounded-md transition-colors flex items-center gap-2 text-sm font-medium"
                              :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                            <PencilIcon class="h-4 w-4" />
                            <span>Edit</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Expense Information -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <div class="rounded-lg p-6"
                         :style="{ 
                             backgroundColor: themeColors.card,
                             borderColor: themeColors.border,
                             borderWidth: '1px',
                             borderStyle: 'solid'
                         }">
                        <h2 class="text-lg font-semibold mb-4 flex items-center"
                            :style="{ color: themeColors.textPrimary }">
                            <DocumentTextIcon class="h-5 w-5 mr-2" />
                            Expense Information
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium"
                                       :style="{ color: themeColors.textSecondary }">Expense Number</label>
                                <p class="mt-1 text-sm font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ expense.expense_number || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium"
                                       :style="{ color: themeColors.textSecondary }">Status</label>
                                <div class="mt-1 flex items-center space-x-2">
                                    <component :is="getStatusIcon(expense.status)" 
                                               class="h-4 w-4"
                                               :style="{ color: themeColors.textSecondary }" />
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :class="getStatusColor(expense.status)">
                                        {{ formatStatus(expense.status) }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label class="text-sm font-medium"
                                       :style="{ color: themeColors.textSecondary }">Date</label>
                                <p class="mt-1 text-sm font-medium flex items-center"
                                   :style="{ color: themeColors.textPrimary }">
                                    <CalendarIcon class="h-4 w-4 mr-2" />
                                    {{ formatDate(expense.expense_date) }}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium"
                                       :style="{ color: themeColors.textSecondary }">Payment Method</label>
                                <p class="mt-1 text-sm font-medium flex items-center"
                                   :style="{ color: themeColors.textPrimary }">
                                    <CreditCardIcon class="h-4 w-4 mr-2" />
                                    {{ formatPaymentMethod(expense.payment_method) }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="text-sm font-medium"
                                   :style="{ color: themeColors.textSecondary }">Description</label>
                            <p class="mt-1 text-sm"
                               :style="{ color: themeColors.textPrimary }">{{ expense.description || 'No description provided' }}</p>
                        </div>
                        <div v-if="expense.notes" class="mt-4">
                            <label class="text-sm font-medium"
                                   :style="{ color: themeColors.textSecondary }">Notes</label>
                            <p class="mt-1 text-sm"
                               :style="{ color: themeColors.textPrimary }">{{ expense.notes }}</p>
                        </div>
                    </div>

                    <!-- Amount Information -->
                    <div class="rounded-lg p-6"
                         :style="{ 
                             backgroundColor: themeColors.card,
                             borderColor: themeColors.border,
                             borderWidth: '1px',
                             borderStyle: 'solid'
                         }">
                        <h2 class="text-lg font-semibold mb-4 flex items-center"
                            :style="{ color: themeColors.textPrimary }">
                            <CurrencyDollarIcon class="h-5 w-5 mr-2" />
                            Amount Information
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium"
                                       :style="{ color: themeColors.textSecondary }">Amount</label>
                                <p class="mt-1 text-2xl font-bold"
                                   :style="{ color: themeColors.primary }">
                                    {{ formatCurrency(expense.amount, expense.currency) }}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium"
                                       :style="{ color: themeColors.textSecondary }">Currency</label>
                                <p class="mt-1 text-sm font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ expense.currency || 'XAF' }}</p>
                            </div>
                        </div>
                        <div v-if="expense.receipt_number" class="mt-4">
                            <label class="text-sm font-medium"
                                   :style="{ color: themeColors.textSecondary }">Receipt Number</label>
                            <p class="mt-1 text-sm font-medium"
                               :style="{ color: themeColors.textPrimary }">{{ expense.receipt_number }}</p>
                        </div>
                    </div>

                    <!-- Receipt -->
                    <div v-if="receipt_url" class="rounded-lg p-6"
                         :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                        <h2 class="text-lg font-semibold mb-4 flex items-center"
                            :style="{ color: themeColors.textPrimary }">
                            <DocumentTextIcon class="h-5 w-5 mr-2" />
                            Receipt
                        </h2>

                        <!-- Image preview -->
                        <div v-if="isImage" class="space-y-3">
                            <img :src="receipt_url"
                                 @click="showImagePreview = true"
                                 class="w-full max-h-64 object-contain rounded-lg border cursor-zoom-in"
                                 :style="{ borderColor: themeColors.border }"
                                 alt="Receipt" />
                            <div class="flex gap-3">
                                <button @click="showImagePreview = true"
                                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                                    :style="{ backgroundColor: themeColors.background, color: themeColors.primary, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                                    Full Screen
                                </button>
                                <a :href="receipt_url" target="_blank" rel="noopener"
                                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                                    :style="{ backgroundColor: themeColors.primary, color: '#ffffff' }">
                                    Open in New Tab
                                </a>
                            </div>
                        </div>

                        <!-- PDF / other file -->
                        <div v-else class="space-y-3">
                            <div class="flex items-center gap-3 p-3 rounded-lg border"
                                 :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background }">
                                <DocumentTextIcon class="h-8 w-8 flex-shrink-0" :style="{ color: themeColors.textSecondary }" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate" :style="{ color: themeColors.textPrimary }">
                                        {{ expense.receipt_file_path?.split('/').pop() }}
                                    </p>
                                    <p class="text-xs" :style="{ color: themeColors.textSecondary }">Click below to open</p>
                                </div>
                            </div>
                            <a :href="receipt_url" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white transition-colors"
                                :style="{ backgroundColor: themeColors.primary }">
                                <DocumentTextIcon class="h-4 w-4" />
                                View / Download Receipt
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Information -->
                <div class="space-y-6">
                    <!-- Category Information -->
                    <div class="rounded-lg p-6"
                         :style="{ 
                             backgroundColor: themeColors.card,
                             borderColor: themeColors.border,
                             borderWidth: '1px',
                             borderStyle: 'solid'
                         }">
                        <h2 class="text-lg font-semibold mb-4 flex items-center"
                            :style="{ color: themeColors.textPrimary }">
                            <TagIcon class="h-5 w-5 mr-2" />
                            Category Information
                        </h2>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <div v-if="expense.category?.color"
                                     class="w-4 h-4 rounded-full border"
                                     :style="`background-color: ${expense.category.color}; border-color: ${expense.category.color}40`"></div>
                                <div>
                                    <p class="text-sm font-medium"
                                       :style="{ color: themeColors.textPrimary }">{{ expense.category?.name || 'Uncategorized' }}</p>
                                    <p class="text-xs"
                                       :style="{ color: themeColors.textSecondary }">Category</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vendor Information -->
                    <div class="rounded-lg p-6"
                         :style="{ 
                             backgroundColor: themeColors.card,
                             borderColor: themeColors.border,
                             borderWidth: '1px',
                             borderStyle: 'solid'
                         }">
                        <h2 class="text-lg font-semibold mb-4 flex items-center"
                            :style="{ color: themeColors.textPrimary }">
                            <BuildingOfficeIcon class="h-5 w-5 mr-2" />
                            Vendor Information
                        </h2>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ expense.vendor_name || 'N/A' }}</p>
                                <p class="text-xs"
                                   :style="{ color: themeColors.textSecondary }">Vendor Name</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submitted By -->
                    <div class="rounded-lg p-6"
                         :style="{ 
                             backgroundColor: themeColors.card,
                             borderColor: themeColors.border,
                             borderWidth: '1px',
                             borderStyle: 'solid'
                         }">
                        <h2 class="text-lg font-semibold mb-4 flex items-center"
                            :style="{ color: themeColors.textPrimary }">
                            <UserIcon class="h-5 w-5 mr-2" />
                            Submitted By
                        </h2>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ submitter_name || 'N/A' }}</p>
                                <p class="text-xs"
                                   :style="{ color: themeColors.textSecondary }">User</p>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Info -->
                    <div v-if="expense.status !== 'pending'" class="rounded-lg p-6"
                         :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                        <h2 class="text-lg font-semibold mb-4 flex items-center"
                            :style="{ color: themeColors.textPrimary }">
                            <CheckCircleIcon class="h-5 w-5 mr-2" />
                            Approval
                        </h2>
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs" :style="{ color: themeColors.textSecondary }">Decision</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1"
                                      :class="expense.status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    {{ formatStatus(expense.status) }}
                                </span>
                            </div>
                            <div v-if="approver_name">
                                <p class="text-xs" :style="{ color: themeColors.textSecondary }">By</p>
                                <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ approver_name }}</p>
                            </div>
                            <div v-if="expense.approved_at">
                                <p class="text-xs" :style="{ color: themeColors.textSecondary }">On</p>
                                <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ formatDate(expense.approved_at) }}</p>
                            </div>
                            <div v-if="expense.approval_notes">
                                <p class="text-xs" :style="{ color: themeColors.textSecondary }">Notes</p>
                                <p class="text-sm" :style="{ color: themeColors.textPrimary }">{{ expense.approval_notes }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Timestamps -->
                    <div class="rounded-lg p-6"
                         :style="{ 
                             backgroundColor: themeColors.card,
                             borderColor: themeColors.border,
                             borderWidth: '1px',
                             borderStyle: 'solid'
                         }">
                        <h2 class="text-lg font-semibold mb-4 flex items-center"
                            :style="{ color: themeColors.textPrimary }">
                            <ClockIcon class="h-5 w-5 mr-2" />
                            Timestamps
                        </h2>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ formatDate(expense.created_at) }}</p>
                                <p class="text-xs"
                                   :style="{ color: themeColors.textSecondary }">Created At</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ formatDate(expense.updated_at) }}</p>
                                <p class="text-xs"
                                   :style="{ color: themeColors.textSecondary }">Last Updated</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Image Lightbox -->
        <div v-if="showImagePreview && isImage"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/80"
             @click.self="showImagePreview = false">
            <div class="relative max-w-5xl w-full mx-4">
                <button @click="showImagePreview = false"
                    class="absolute -top-10 right-0 text-white text-sm px-3 py-1 rounded-lg bg-white/10 hover:bg-white/20 transition-colors">
                    ✕ Close
                </button>
                <img :src="receipt_url" class="w-full max-h-screen object-contain rounded-lg" alt="Receipt" />
                <div class="mt-3 flex justify-center">
                    <a :href="receipt_url" target="_blank" rel="noopener"
                       class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/20 hover:bg-white/30 transition-colors">
                        Open in New Tab
                    </a>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div v-if="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50" @click="showRejectModal = false"></div>
            <div class="relative rounded-xl shadow-2xl w-full max-w-md mx-4 p-6"
                 :style="{ backgroundColor: themeColors.card }">
                <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Reject Expense</h3>
                <p class="text-sm mb-4" :style="{ color: themeColors.textSecondary }">
                    Optionally provide a reason for rejection.
                </p>
                <textarea v-model="rejectNotes" rows="3"
                    class="w-full px-3 py-2 rounded-lg border focus:outline-none transition-colors"
                    :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }"
                    placeholder="Reason for rejection (optional)"></textarea>
                <div class="flex justify-end gap-3 mt-5">
                    <button @click="showRejectModal = false" type="button"
                        class="px-4 py-2 rounded-lg border text-sm font-medium"
                        :style="{ borderColor: themeColors.border, color: themeColors.textSecondary }">
                        Cancel
                    </button>
                    <button @click="submitReject" :disabled="processing" type="button"
                        class="px-4 py-2 rounded-lg text-sm font-medium text-white disabled:opacity-60"
                        :style="{ backgroundColor: themeColors.danger }">
                        {{ processing ? 'Rejecting...' : 'Confirm Reject' }}
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
