<template>
    <DashboardLayout :user="user" :navigation="navigation" title="Bill Adjustment Requests">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Bill Modification Requests</h1>
                    <p class="text-sm mt-1" :style="{ color: themeColors.textSecondary }">Review pending requests and track approved billing impacts across reservations.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div v-for="card in statCards" :key="card.label"
                     class="rounded-lg border p-4 shadow-sm"
                     :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                    <p class="text-xs font-medium uppercase tracking-wide" :style="{ color: themeColors.textSecondary }">{{ card.label }}</p>
                    <p class="text-2xl font-bold mt-2" :style="{ color: card.color }">{{ card.value }}</p>
                </div>
            </div>

            <div class="rounded-lg border p-4 shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Search</label>
                        <input v-model="form.search" type="text" placeholder="Reservation, guest, reason"
                               class="w-full rounded-md px-3 py-2 border text-sm focus:outline-none"
                               :style="inputStyle" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Status</label>
                        <select v-model="form.status" class="w-full rounded-md px-3 py-2 border text-sm focus:outline-none" :style="inputStyle">
                            <option value="">All</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Type</label>
                        <select v-model="form.adjustment_type" class="w-full rounded-md px-3 py-2 border text-sm focus:outline-none" :style="inputStyle">
                            <option value="">All</option>
                            <option value="increase">Increase</option>
                            <option value="decrease">Decrease</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Start Date</label>
                        <input v-model="form.start_date" type="date" class="w-full rounded-md px-3 py-2 border text-sm focus:outline-none" :style="inputStyle" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">End Date</label>
                        <input v-model="form.end_date" type="date" class="w-full rounded-md px-3 py-2 border text-sm focus:outline-none" :style="inputStyle" />
                    </div>
                </div>
                <div class="flex gap-2 mt-4">
                    <button @click="applyFilters"
                            class="px-4 py-2 rounded-md text-sm font-medium text-white"
                            :style="{ backgroundColor: themeColors.primary }">
                        Apply Filters
                    </button>
                    <button @click="clearFilters"
                            class="px-4 py-2 rounded-md text-sm font-medium border"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        Clear
                    </button>
                </div>
            </div>

            <div class="rounded-lg border shadow-sm overflow-hidden"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr :style="{ backgroundColor: themeColors.background }">
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Reservation</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Impact</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Requested By</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Requested</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="requestItem in requestRows" :key="requestItem.id" class="align-top border-t" :style="{ borderColor: themeColors.border }">
                                <td class="px-4 py-4 text-sm">
                                    <div class="font-semibold" :style="{ color: themeColors.textPrimary }">{{ requestItem.reservation_number || 'Reservation' }}</div>
                                    <div :style="{ color: themeColors.textSecondary }">{{ requestItem.guest_name }}</div>
                                    <div class="mt-1 text-xs" :style="{ color: themeColors.textSecondary }">{{ requestItem.reason }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ requestItem.adjustment_type === 'decrease' ? 'Reduction' : 'Increase' }}
                                </td>
                                <td class="px-4 py-4 text-sm font-semibold" :style="{ color: requestItem.signed_amount >= 0 ? themeColors.success : themeColors.danger }">
                                    {{ formatCurrency(requestItem.folio_charge_amount ?? requestItem.signed_amount) }}
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <div :style="{ color: themeColors.textPrimary }">{{ requestItem.requested_by_name }}</div>
                                    <div v-if="requestItem.reviewed_by_name" class="text-xs mt-1" :style="{ color: themeColors.textSecondary }">Reviewed by {{ requestItem.reviewed_by_name }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium" :style="statusStyle(requestItem.status)">
                                        {{ requestItem.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-sm" :style="{ color: themeColors.textPrimary }">
                                    <div>{{ formatDateTime(requestItem.requested_at) }}</div>
                                    <div v-if="requestItem.reviewed_at" class="text-xs mt-1" :style="{ color: themeColors.textSecondary }">{{ formatDateTime(requestItem.reviewed_at) }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <div class="space-y-2 min-w-[220px]">
                                        <Link :href="route(`${routePrefix}.reservations.show`, requestItem.reservation_id)"
                                              class="inline-flex px-3 py-1.5 rounded-md text-xs font-medium text-white"
                                              :style="{ backgroundColor: themeColors.primary }">
                                            Open Reservation
                                        </Link>
                                        <div v-if="requestItem.status === 'pending'" class="space-y-2">
                                            <textarea v-model="reviewNotes[requestItem.id]"
                                                      rows="2"
                                                      class="w-full rounded-md px-3 py-2 border text-xs focus:outline-none"
                                                      :style="inputStyle"
                                                      placeholder="Optional validation notes"></textarea>
                                            <div class="flex gap-2">
                                                <button @click="approveRequest(requestItem)"
                                                        class="px-3 py-1.5 rounded-md text-xs font-medium text-white"
                                                        :style="{ backgroundColor: themeColors.success }">
                                                    Approve
                                                </button>
                                                <button @click="rejectRequest(requestItem)"
                                                        class="px-3 py-1.5 rounded-md text-xs font-medium text-white"
                                                        :style="{ backgroundColor: themeColors.danger }">
                                                    Reject
                                                </button>
                                            </div>
                                        </div>
                                        <div v-else-if="requestItem.review_notes" class="text-xs" :style="{ color: themeColors.textSecondary }">
                                            {{ requestItem.review_notes }}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="requestRows.length === 0">
                                <td colspan="7" class="px-4 py-12 text-center text-sm" :style="{ color: themeColors.textSecondary }">
                                    No bill modification requests match the current filters.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'

const props = defineProps({
    user: Object,
    navigation: Array,
    routePrefix: String,
    requests: Object,
    stats: Object,
    filters: Object,
})

const page = usePage()
const themeColors = computed(() => page.props.themeColors || {
    primary: '#2563eb',
    success: '#16a34a',
    danger: '#dc2626',
    warning: '#d97706',
    background: '#f8fafc',
    card: '#ffffff',
    border: '#e5e7eb',
    textPrimary: '#111827',
    textSecondary: '#6b7280',
})

const form = reactive({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    adjustment_type: props.filters?.adjustment_type || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
})

const reviewNotes = ref({})

const requestRows = computed(() => props.requests?.data || [])

const statCards = computed(() => [
    { label: 'Total', value: props.stats?.total || 0, color: themeColors.value.textPrimary },
    { label: 'Pending', value: props.stats?.pending || 0, color: themeColors.value.warning },
    { label: 'Approved', value: props.stats?.approved || 0, color: themeColors.value.success },
    { label: 'Rejected', value: props.stats?.rejected || 0, color: themeColors.value.danger },
    { label: 'Net Impact', value: formatCurrency(props.stats?.approved_net_impact || 0), color: (props.stats?.approved_net_impact || 0) >= 0 ? themeColors.value.success : themeColors.value.danger },
])

const inputStyle = computed(() => ({
    backgroundColor: themeColors.value.background,
    borderColor: themeColors.value.border,
    color: themeColors.value.textPrimary,
    borderWidth: '1px',
    borderStyle: 'solid',
}))

const applyFilters = () => {
    router.get(route(`${props.routePrefix}.bill-adjustment-requests.index`), { ...form }, { preserveState: true, replace: true })
}

const clearFilters = () => {
    form.search = ''
    form.status = ''
    form.adjustment_type = ''
    form.start_date = ''
    form.end_date = ''
    applyFilters()
}

const approveRequest = (requestItem) => {
    router.post(route(`${props.routePrefix}.reservations.bill-adjustment-requests.approve`, {
        reservation: requestItem.reservation_id,
        billAdjustmentRequest: requestItem.id,
    }), {
        review_notes: reviewNotes.value[requestItem.id] || '',
    }, {
        preserveScroll: true,
    })
}

const rejectRequest = (requestItem) => {
    router.post(route(`${props.routePrefix}.reservations.bill-adjustment-requests.reject`, {
        reservation: requestItem.reservation_id,
        billAdjustmentRequest: requestItem.id,
    }), {
        review_notes: reviewNotes.value[requestItem.id] || '',
    }, {
        preserveScroll: true,
    })
}

const formatDateTime = (value) => value ? new Date(value.replace(' ', 'T')).toLocaleString() : '—'

const statusStyle = (status) => {
    if (status === 'approved') return { backgroundColor: `${themeColors.value.success}20`, color: themeColors.value.success }
    if (status === 'rejected') return { backgroundColor: `${themeColors.value.danger}20`, color: themeColors.value.danger }
    return { backgroundColor: `${themeColors.value.warning}20`, color: themeColors.value.warning }
}
</script>