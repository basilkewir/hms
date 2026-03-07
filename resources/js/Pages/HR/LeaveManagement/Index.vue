<template>
    <DashboardLayout title="Leave Management" :user="user" :navigation="props.navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Leave Management</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        Track employee leave requests, approvals, and balances
                    </p>
                </div>
                <div class="flex gap-3">
                    <button
                        @click="openLeaveModal"
                        class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                        :style="{ backgroundColor: themeColors.primary }">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        New Leave Request
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" style="background-color: rgba(59, 130, 246, 0.1);">
                        <CalendarDaysIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Pending Requests</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ pendingCount }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" style="background-color: rgba(34, 197, 94, 0.1);">
                        <CheckCircleIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Approved This Month</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ approvedThisMonth }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" style="background-color: rgba(239, 68, 68, 0.1);">
                        <XCircleIcon class="h-6 w-6" :style="{ color: themeColors.danger }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Rejected This Month</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ rejectedThisMonth }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Placeholder table -->
        <div class="rounded-lg border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Leave Requests</h3>
            </div>
            <div v-if="leaveRequests && leaveRequests.length" class="divide-y" :style="{ borderColor: themeColors.border }">
                <div
                    v-for="lr in leaveRequests"
                    :key="lr.id"
                    class="px-6 py-4 grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                    <div class="md:col-span-4">
                        <p class="font-medium" :style="{ color: themeColors.textPrimary }">
                            {{ lr.user?.first_name ? (lr.user.first_name + ' ' + lr.user.last_name) : 'Employee #' + lr.user_id }}
                        </p>
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                            {{ lr.leave_type }}
                        </p>
                    </div>
                    <div class="md:col-span-4">
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                            {{ formatDate(lr.start_date) }} - {{ formatDate(lr.end_date) }}
                            <span class="ml-2" :style="{ color: themeColors.textTertiary }">({{ Number(lr.days_requested).toFixed(1) }} days)</span>
                        </p>
                    </div>
                    <div class="md:col-span-2">
                        <span class="px-2 py-1 rounded-md capitalize" :style="{ backgroundColor: themeColors.border, color: themeColors.textPrimary }">
                            {{ lr.status ?? 'pending' }}
                        </span>
                    </div>
                    <div class="md:col-span-2 flex gap-2 justify-start md:justify-end">
                        <button
                            v-if="lr.status !== 'approved'"
                            @click="updateStatus(lr, 'approved')"
                            class="px-3 py-1 rounded-md text-sm font-medium text-white"
                            :style="{ backgroundColor: themeColors.success }">
                            Approve
                        </button>
                        <button
                            v-if="lr.status !== 'rejected'"
                            @click="updateStatus(lr, 'rejected')"
                            class="px-3 py-1 rounded-md text-sm font-medium text-white"
                            :style="{ backgroundColor: themeColors.danger }">
                            Reject
                        </button>
                        <button
                            v-if="lr.status !== 'pending'"
                            @click="updateStatus(lr, 'pending')"
                            class="px-3 py-1 rounded-md text-sm font-medium"
                            :style="{ backgroundColor: themeColors.border, color: themeColors.textPrimary }">
                            Mark Pending
                        </button>
                    </div>
                </div>
            </div>
            <div v-else class="p-8 text-center">
                <p class="mb-2" :style="{ color: themeColors.textSecondary }">
                    No leave requests found yet.
                </p>
                <p class="text-sm" :style="{ color: themeColors.textTertiary }">
                    Use the New Leave Request button above to create one.
                </p>
            </div>
        </div>

        <!-- New Leave Request Modal -->
        <DialogModal :show="showLeaveModal" @close="closeLeaveModal">
            <template #title>
                New Leave Request
            </template>

            <template #content>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Start Date</label>
                            <DatePicker v-model="form.start_date" class="w-full" />
                            <p v-if="form.errors.start_date" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ form.errors.start_date }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">End Date</label>
                            <DatePicker v-model="form.end_date" class="w-full" />
                            <p v-if="form.errors.end_date" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ form.errors.end_date }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Leave Type</label>
                        <select
                            v-model="form.leave_type"
                            class="w-full rounded-md px-3 py-2 focus:outline-none"
                            :style="{
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary,
                                borderWidth: '1px',
                                borderStyle: 'solid',
                            }"
                        >
                            <option value="Annual">Annual Leave</option>
                            <option value="Sick">Sick Leave</option>
                            <option value="Unpaid">Unpaid Leave</option>
                            <option value="Other">Other</option>
                        </select>
                        <p v-if="form.errors.leave_type" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ form.errors.leave_type }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Reason</label>
                        <textarea
                            v-model="form.reason"
                            rows="3"
                            class="w-full rounded-md px-3 py-2 focus:outline-none"
                            :style="{
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary,
                                borderWidth: '1px',
                                borderStyle: 'solid',
                            }"
                        />
                        <p v-if="form.errors.reason" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ form.errors.reason }}</p>
                    </div>
                </div>
            </template>

            <template #footer>
                <div class="flex items-center justify-end gap-3">
                    <button
                        type="button"
                        @click="closeLeaveModal"
                        class="px-4 py-2 rounded-md text-sm font-medium"
                        :style="{ backgroundColor: themeColors.border, color: themeColors.textPrimary }"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="submitLeaveRequest"
                        class="px-4 py-2 rounded-md text-sm font-medium text-white"
                        :style="{ backgroundColor: themeColors.primary }"
                    >
                        <span v-if="!form.processing">Submit Request</span>
                        <span v-else>Submitting...</span>
                    </button>
                </div>
            </template>
        </DialogModal>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DialogModal from '@/Components/DialogModal.vue'
import DatePicker from '@/Components/DatePicker.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { CalendarDaysIcon, CheckCircleIcon, XCircleIcon, PlusIcon } from '@heroicons/vue/24/outline'

const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    primary: `var(--kotel-primary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
}))

loadTheme()

const props = defineProps({
    user: Object,
    leaveRequests: { type: Array, default: () => [] },
    navigation: [Array, Object],
})

const pendingCount = computed(() => (props.leaveRequests ?? []).filter(lr => (lr.status ?? 'pending') === 'pending').length)
const approvedThisMonth = computed(() => {
    const now = new Date()
    return (props.leaveRequests ?? []).filter(lr => {
        if (lr.status !== 'approved') return false
        const d = new Date(lr.updated_at ?? lr.created_at)
        return d.getFullYear() === now.getFullYear() && d.getMonth() === now.getMonth()
    }).length
})
const rejectedThisMonth = computed(() => {
    const now = new Date()
    return (props.leaveRequests ?? []).filter(lr => {
        if (lr.status !== 'rejected') return false
        const d = new Date(lr.updated_at ?? lr.created_at)
        return d.getFullYear() === now.getFullYear() && d.getMonth() === now.getMonth()
    }).length
})

const showLeaveModal = ref(false)

const form = useForm({
    start_date: '',
    end_date: '',
    leave_type: 'Annual',
    reason: '',
})

const resetLeaveForm = () => {
    form.reset()
    form.clearErrors()
}

const openLeaveModal = () => {
    resetLeaveForm()
    showLeaveModal.value = true
}

const closeLeaveModal = () => {
    showLeaveModal.value = false
}

const submitLeaveRequest = () => {
    form.post(route('hr.leave-requests.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showLeaveModal.value = false
            resetLeaveForm()
        },
    })
}

// Helpers & actions
function formatDate(value) {
    if (!value) return ''
    try {
        // If it's already a string like '2026-03-10', just slice
        if (typeof value === 'string') {
            if (value.length >= 10) return value.slice(0, 10)
        }
        const d = new Date(value)
        if (Number.isNaN(d.getTime())) return String(value)
        return d.toISOString().slice(0, 10)
    } catch (e) {
        return String(value)
    }
}

const updateStatus = (lr, status) => {
    router.put(route('hr.leave-requests.update', lr.id), { status }, {
        preserveScroll: true,
        onSuccess: () => {
            // No-op: server returns to same page; Inertia props will refresh list
        },
    })
}
</script>
