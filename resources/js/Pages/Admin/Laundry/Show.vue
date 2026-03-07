<script setup>
import { computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'

const page = usePage()
const user = computed(() => page.props.auth.user)
const navigation = computed(() => page.props.navigation)

const props = defineProps({ order: Object })

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

const statusConfig = {
    pending:     { label: 'Pending',     color: 'warning' },
    picked_up:   { label: 'Picked Up',   color: 'primary' },
    in_progress: { label: 'In Progress', color: 'primary' },
    ready:       { label: 'Ready',       color: 'success' },
    delivered:   { label: 'Delivered',   color: 'success' },
    cancelled:   { label: 'Cancelled',   color: 'danger'  },
}

const priorityConfig = {
    normal:    { label: 'Normal'    },
    express:   { label: 'Express'   },
    overnight: { label: 'Overnight' },
}

const serviceTypeLabel = {
    wash:           'Wash',
    dry_clean:      'Dry Clean',
    iron:           'Iron Only',
    wash_iron:      'Wash & Iron',
    dry_clean_iron: 'Dry Clean & Iron',
}

const getStatusStyle = (s) => {
    const c = statusConfig[s]?.color ?? 'textSecondary'
    return { backgroundColor: themeColors.value[c] + '20', color: themeColors.value[c] }
}

const updateStatus = (newStatus) => {
    router.patch(route('admin.laundry.update-status', props.order.id), { status: newStatus })
}

const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '—'
</script>

<template>
    <DashboardLayout :title="'Order ' + order.order_number" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between flex-wrap gap-4">
            <div>
                <p class="text-sm mb-1" :style="{ color: themeColors.textSecondary }">Laundry Order</p>
                <h1 class="text-3xl font-bold" :style="{ color: themeColors.textPrimary }">{{ order.order_number }}</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 rounded-full text-sm font-medium" :style="getStatusStyle(order.status)">
                    {{ statusConfig[order.status]?.label ?? order.status }}
                </span>
                <select @change="updateStatus($event.target.value)"
                        class="px-3 py-1.5 rounded-lg border text-sm"
                        :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }">
                    <option value="">Update Status…</option>
                    <option v-for="(cfg, val) in statusConfig" :key="val" :value="val">{{ cfg.label }}</option>
                </select>
                <Link :href="route('admin.laundry.index')"
                      class="px-4 py-2 rounded-lg border text-sm font-medium"
                      :style="{ borderColor: themeColors.border, color: themeColors.textSecondary }">
                    Back
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left col -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Items table -->
                <div class="rounded-xl border overflow-hidden"
                     :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                    <div class="p-5 border-b" :style="{ borderColor: themeColors.border }">
                        <h2 class="font-semibold text-lg" :style="{ color: themeColors.textPrimary }">Items</h2>
                    </div>
                    <table class="w-full">
                        <thead>
                            <tr :style="{ backgroundColor: themeColors.background }">
                                <th class="text-left p-4 text-sm font-medium" :style="{ color: themeColors.textSecondary }">Item</th>
                                <th class="text-left p-4 text-sm font-medium" :style="{ color: themeColors.textSecondary }">Service</th>
                                <th class="text-center p-4 text-sm font-medium" :style="{ color: themeColors.textSecondary }">Qty</th>
                                <th class="text-right p-4 text-sm font-medium" :style="{ color: themeColors.textSecondary }">Unit Price</th>
                                <th class="text-right p-4 text-sm font-medium" :style="{ color: themeColors.textSecondary }">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in order.items" :key="item.id"
                                class="border-t" :style="{ borderColor: themeColors.border }">
                                <td class="p-4">
                                    <p class="font-medium text-sm" :style="{ color: themeColors.textPrimary }">{{ item.item_name }}</p>
                                    <p v-if="item.notes" class="text-xs mt-0.5" :style="{ color: themeColors.textTertiary }">{{ item.notes }}</p>
                                </td>
                                <td class="p-4 text-sm" :style="{ color: themeColors.textSecondary }">{{ serviceTypeLabel[item.service_type] ?? item.service_type }}</td>
                                <td class="p-4 text-center text-sm" :style="{ color: themeColors.textPrimary }">{{ item.quantity }}</td>
                                <td class="p-4 text-right text-sm" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(item.unit_price) }}</td>
                                <td class="p-4 text-right text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(item.total_price) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="border-t" :style="{ borderColor: themeColors.border }">
                            <tr>
                                <td colspan="4" class="p-4 text-right text-sm" :style="{ color: themeColors.textSecondary }">Subtotal</td>
                                <td class="p-4 text-right font-medium" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(order.subtotal) }}</td>
                            </tr>
                            <tr v-if="order.express_fee > 0">
                                <td colspan="4" class="p-4 text-right text-sm" :style="{ color: themeColors.textSecondary }">
                                    {{ order.priority === 'express' ? 'Express Fee (50%)' : 'Overnight Fee (25%)' }}
                                </td>
                                <td class="p-4 text-right font-medium" :style="{ color: themeColors.warning }">{{ formatCurrency(order.express_fee) }}</td>
                            </tr>
                            <tr class="border-t font-semibold" :style="{ borderColor: themeColors.border }">
                                <td colspan="4" class="p-4 text-right" :style="{ color: themeColors.textPrimary }">Total</td>
                                <td class="p-4 text-right text-lg" :style="{ color: themeColors.primary }">{{ formatCurrency(order.total_amount) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Notes -->
                <div v-if="order.special_instructions || order.notes"
                     class="rounded-xl border p-5"
                     :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                    <h2 class="font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Notes</h2>
                    <p v-if="order.special_instructions" class="text-sm mb-2" :style="{ color: themeColors.textSecondary }">
                        <span class="font-medium" :style="{ color: themeColors.textPrimary }">Special Instructions: </span>
                        {{ order.special_instructions }}
                    </p>
                    <p v-if="order.notes" class="text-sm" :style="{ color: themeColors.textSecondary }">
                        <span class="font-medium" :style="{ color: themeColors.textPrimary }">Internal Notes: </span>
                        {{ order.notes }}
                    </p>
                </div>
            </div>

            <!-- Right col -->
            <div class="space-y-6">
                <!-- Guest & Room -->
                <div class="rounded-xl border p-5"
                     :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                    <h2 class="font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Guest</h2>
                    <div class="space-y-2 text-sm">
                        <div>
                            <span class="font-medium" :style="{ color: themeColors.textSecondary }">Name: </span>
                            <span :style="{ color: themeColors.textPrimary }">
                                {{ order.guest ? order.guest.first_name + ' ' + order.guest.last_name : 'Walk-in' }}
                            </span>
                        </div>
                        <div v-if="order.room">
                            <span class="font-medium" :style="{ color: themeColors.textSecondary }">Room: </span>
                            <span :style="{ color: themeColors.textPrimary }">{{ order.room.room_number }}</span>
                        </div>
                        <div v-if="order.staff">
                            <span class="font-medium" :style="{ color: themeColors.textSecondary }">Created by: </span>
                            <span :style="{ color: themeColors.textPrimary }">
                                {{ order.staff.first_name }} {{ order.staff.last_name }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Schedule -->
                <div class="rounded-xl border p-5"
                     :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                    <h2 class="font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Schedule</h2>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="font-medium mb-0.5" :style="{ color: themeColors.textSecondary }">Pickup</p>
                            <p :style="{ color: themeColors.textPrimary }">{{ formatDate(order.pickup_date) }}{{ order.pickup_time ? ' at ' + order.pickup_time : '' }}</p>
                        </div>
                        <div>
                            <p class="font-medium mb-0.5" :style="{ color: themeColors.textSecondary }">Delivery</p>
                            <p :style="{ color: themeColors.textPrimary }">{{ formatDate(order.delivery_date) }}{{ order.delivery_time ? ' at ' + order.delivery_time : '' }}</p>
                        </div>
                        <div>
                            <p class="font-medium mb-0.5" :style="{ color: themeColors.textSecondary }">Priority</p>
                            <p :style="{ color: themeColors.textPrimary }">{{ priorityConfig[order.priority]?.label ?? order.priority }}</p>
                        </div>
                        <div>
                            <p class="font-medium mb-0.5" :style="{ color: themeColors.textSecondary }">Payment</p>
                            <p class="capitalize" :style="{ color: themeColors.textPrimary }">{{ order.payment_status?.replace('_', ' ') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
