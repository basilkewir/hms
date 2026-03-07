<script setup>
import { ref, computed, watch } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'

const page = usePage()
const user = computed(() => page.props.auth.user)
const navigation = computed(() => page.props.navigation)

const props = defineProps({
    orders:  Object,
    stats:   Object,
    filters: Object,
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

const search   = ref(props.filters?.search   ?? '')
const status   = ref(props.filters?.status   ?? '')
const priority = ref(props.filters?.priority ?? '')

const applyFilters = debounce(() => {
    router.get(route('admin.laundry.index'), {
        search:   search.value   || undefined,
        status:   status.value   || undefined,
        priority: priority.value || undefined,
    }, { preserveState: true, replace: true })
}, 300)

watch([search, status, priority], applyFilters)

const statusConfig = {
    pending:     { label: 'Pending',     color: 'warning' },
    picked_up:   { label: 'Picked Up',   color: 'primary' },
    in_progress: { label: 'In Progress', color: 'primary' },
    ready:       { label: 'Ready',       color: 'success' },
    delivered:   { label: 'Delivered',   color: 'success' },
    cancelled:   { label: 'Cancelled',   color: 'danger'  },
}

const priorityConfig = {
    normal:    { label: 'Normal',    color: 'textSecondary' },
    express:   { label: 'Express',   color: 'warning'       },
    overnight: { label: 'Overnight', color: 'primary'       },
}

const getStatusStyle = (s) => {
    const c = statusConfig[s]?.color ?? 'textSecondary'
    return { backgroundColor: themeColors.value[c] + '20', color: themeColors.value[c] }
}
const getPriorityStyle = (p) => {
    const c = priorityConfig[p]?.color ?? 'textSecondary'
    return { backgroundColor: themeColors.value[c] + '20', color: themeColors.value[c] }
}

const updateStatus = (orderId, newStatus) => {
    router.patch(route('admin.laundry.update-status', orderId), { status: newStatus })
}

const deleteOrder = (orderId) => {
    if (confirm('Delete this laundry order?')) {
        router.delete(route('admin.laundry.destroy', orderId))
    }
}

const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '—'
</script>

<template>
    <DashboardLayout title="Laundry Management" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Laundry Management</h1>
                    <p :style="{ color: themeColors.textSecondary }">Track and manage all laundry orders</p>
                </div>
                <Link :href="route('admin.laundry.create')"
                      class="px-4 py-2 rounded-lg font-medium text-white transition-colors"
                      :style="{ backgroundColor: themeColors.primary }">
                    + New Order
                </Link>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            <div v-for="(value, key) in {
                'Total Orders': stats.total,
                'Pending': stats.pending,
                'In Progress': stats.in_progress,
                'Ready': stats.ready,
                'Delivered': stats.delivered,
                'Revenue': formatCurrency(stats.revenue),
            }" :key="key"
                class="rounded-lg p-4 border"
                :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">{{ key }}</p>
                <p class="text-xl font-bold" :style="{ color: themeColors.textPrimary }">{{ value }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="rounded-lg border p-4 mb-6 flex flex-wrap gap-3"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <input v-model="search"
                   type="text"
                   placeholder="Search order # or guest..."
                   class="px-3 py-2 rounded-lg border text-sm flex-1 min-w-48"
                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
            <select v-model="status" class="px-3 py-2 rounded-lg border text-sm"
                    :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                <option value="">All Statuses</option>
                <option v-for="(cfg, val) in statusConfig" :key="val" :value="val">{{ cfg.label }}</option>
            </select>
            <select v-model="priority" class="px-3 py-2 rounded-lg border text-sm"
                    :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                <option value="">All Priorities</option>
                <option v-for="(cfg, val) in priorityConfig" :key="val" :value="val">{{ cfg.label }}</option>
            </select>
        </div>

        <!-- Table -->
        <div class="rounded-xl border overflow-hidden"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <table class="w-full">
                <thead>
                    <tr :style="{ backgroundColor: themeColors.background }">
                        <th class="text-left p-4 text-sm font-medium" :style="{ color: themeColors.textSecondary }">Order #</th>
                        <th class="text-left p-4 text-sm font-medium" :style="{ color: themeColors.textSecondary }">Guest / Room</th>
                        <th class="text-left p-4 text-sm font-medium" :style="{ color: themeColors.textSecondary }">Items</th>
                        <th class="text-left p-4 text-sm font-medium" :style="{ color: themeColors.textSecondary }">Priority</th>
                        <th class="text-left p-4 text-sm font-medium" :style="{ color: themeColors.textSecondary }">Pickup / Delivery</th>
                        <th class="text-left p-4 text-sm font-medium" :style="{ color: themeColors.textSecondary }">Total</th>
                        <th class="text-left p-4 text-sm font-medium" :style="{ color: themeColors.textSecondary }">Status</th>
                        <th class="text-left p-4 text-sm font-medium" :style="{ color: themeColors.textSecondary }">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="order in orders.data" :key="order.id"
                        class="border-t hover:opacity-90 transition-opacity"
                        :style="{ borderColor: themeColors.border }">
                        <td class="p-4">
                            <Link :href="route('admin.laundry.show', order.id)"
                                  class="font-medium hover:underline"
                                  :style="{ color: themeColors.primary }">
                                {{ order.order_number }}
                            </Link>
                        </td>
                        <td class="p-4">
                            <p class="font-medium text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ order.guest ? order.guest.first_name + ' ' + order.guest.last_name : 'Walk-in' }}
                            </p>
                            <p class="text-xs" :style="{ color: themeColors.textSecondary }">
                                {{ order.room ? 'Room ' + order.room.room_number : '—' }}
                            </p>
                        </td>
                        <td class="p-4">
                            <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                {{ order.items?.length ?? 0 }} item(s)
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-1 rounded text-xs font-medium" :style="getPriorityStyle(order.priority)">
                                {{ priorityConfig[order.priority]?.label ?? order.priority }}
                            </span>
                        </td>
                        <td class="p-4 text-sm" :style="{ color: themeColors.textSecondary }">
                            <p>{{ formatDate(order.pickup_date) }}</p>
                            <p>{{ formatDate(order.delivery_date) }}</p>
                        </td>
                        <td class="p-4 font-medium" :style="{ color: themeColors.textPrimary }">
                            {{ formatCurrency(order.total_amount) }}
                        </td>
                        <td class="p-4">
                            <select :value="order.status"
                                    @change="updateStatus(order.id, $event.target.value)"
                                    class="px-2 py-1 rounded text-xs font-medium border-0 cursor-pointer"
                                    :style="getStatusStyle(order.status)">
                                <option v-for="(cfg, val) in statusConfig" :key="val" :value="val">{{ cfg.label }}</option>
                            </select>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center space-x-2">
                                <Link :href="route('admin.laundry.show', order.id)"
                                      class="p-1 rounded hover:opacity-70"
                                      :style="{ color: themeColors.primary }" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </Link>
                                <button @click="deleteOrder(order.id)"
                                        class="p-1 rounded hover:opacity-70"
                                        :style="{ color: themeColors.danger }" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="orders.data.length === 0">
                        <td colspan="8" class="p-12 text-center">
                            <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" :style="{ color: themeColors.textTertiary }">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="font-medium" :style="{ color: themeColors.textPrimary }">No laundry orders found</p>
                            <p class="text-sm mt-1" :style="{ color: themeColors.textSecondary }">Create a new order to get started.</p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="orders.last_page > 1" class="flex items-center justify-between p-4 border-t"
                 :style="{ borderColor: themeColors.border }">
                <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                    Showing {{ orders.from }}–{{ orders.to }} of {{ orders.total }} orders
                </p>
                <div class="flex space-x-1">
                    <Link v-for="link in orders.links" :key="link.label"
                          :href="link.url ?? '#'"
                          class="px-3 py-1 rounded text-sm"
                          :class="link.url ? 'hover:opacity-80' : 'opacity-40 cursor-default'"
                          :style="link.active
                              ? { backgroundColor: themeColors.primary, color: 'white' }
                              : { backgroundColor: themeColors.background, color: themeColors.textSecondary }"
                          v-html="link.label" />
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
