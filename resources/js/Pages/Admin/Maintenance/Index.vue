<template>
    <DashboardLayout title="Maintenance Dashboard">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Maintenance Dashboard</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Overview of maintenance operations, requests and categories.</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('admin.maintenance-requests.create')"
                          class="px-4 py-2 rounded-md transition-colors text-sm font-medium text-white"
                          :style="{ backgroundColor: themeColors.primary }">
                        + New Request
                    </Link>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
            <div v-for="stat in statCards" :key="stat.label"
                 class="rounded-lg p-4 border"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <div class="flex items-center">
                    <div class="p-2 rounded-md mr-3" :style="{ backgroundColor: stat.color + '20' }">
                        <component :is="stat.icon" class="h-5 w-5" :style="{ color: stat.color }" />
                    </div>
                    <div>
                        <p class="text-xs font-medium" :style="{ color: themeColors.textTertiary }">{{ stat.label }}</p>
                        <p class="text-xl font-bold" :style="{ color: themeColors.textPrimary }">{{ stat.value }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rooms Currently in Maintenance Report -->
        <div class="rounded-lg border mb-8" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
            <div class="flex items-center px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <WrenchScrewdriverIcon class="h-5 w-5 mr-2" :style="{ color: themeColors.warning }" />
                <h2 class="text-base font-semibold" :style="{ color: themeColors.textPrimary }">Rooms Currently in Maintenance</h2>
                <span class="ml-3 px-2 py-0.5 text-xs font-semibold rounded-full"
                      :style="{ backgroundColor: 'rgba(250,204,21,0.15)', color: themeColors.warning }">
                    {{ (maintenanceRooms || []).length }} room(s) active
                </span>
            </div>
            <div v-if="!maintenanceRooms || maintenanceRooms.length === 0" class="px-6 py-8 text-center">
                <CheckCircleIcon class="h-10 w-10 mx-auto mb-2" :style="{ color: themeColors.success }" />
                <p class="text-sm" :style="{ color: themeColors.textTertiary }">No rooms are currently in active maintenance.</p>
            </div>
            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y" :style="{ borderColor: themeColors.border }">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Room</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Issue / Description</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Priority</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Assigned To</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Reported By</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Reported</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Days Open</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" :style="{ borderColor: themeColors.border }">
                        <tr v-for="item in maintenanceRooms" :key="item.id"
                            :style="{ backgroundColor: item.priority === 'urgent' ? 'rgba(239,68,68,0.07)' : themeColors.card }">
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-bold" :style="{ color: themeColors.textPrimary }">Room {{ item.room_number }}</td>
                            <td class="px-4 py-3 text-sm" style="max-width:280px;">
                                <div class="font-medium" :style="{ color: themeColors.textPrimary }">{{ item.title }}</div>
                                <div class="text-xs mt-0.5 truncate" :style="{ color: themeColors.textTertiary }" :title="item.description">{{ item.description }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm capitalize" :style="{ color: themeColors.textSecondary }">{{ item.category }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full" :style="getPriorityStyle(item.priority)">{{ capitalize(item.priority) }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full" :style="getStatusStyle(item.status)">{{ formatStatus(item.status) }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm" :style="{ color: themeColors.textSecondary }">{{ item.assigned_to || 'Unassigned' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm" :style="{ color: themeColors.textSecondary }">{{ item.reported_by || '—' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm" :style="{ color: themeColors.textTertiary }">{{ item.reported_at }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full"
                                      :style="item.days_open >= 7 ? { backgroundColor: 'var(--kotel-danger)', color: 'white' } : item.days_open >= 3 ? { backgroundColor: 'var(--kotel-warning)', color: 'white' } : { backgroundColor: 'var(--kotel-success)', color: 'white' }">
                                    {{ item.days_open }}d
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recurring Issue Alerts -->
        <div v-if="recurringAlerts && recurringAlerts.length > 0"
             class="rounded-lg border mb-8 overflow-hidden"
             :style="{ borderColor: 'var(--kotel-danger)', borderWidth: '2px', borderStyle: 'solid' }">
            <div class="px-6 py-3 flex items-center gap-2" :style="{ backgroundColor: 'var(--kotel-danger)', color: 'white' }">
                <ExclamationTriangleIcon class="h-5 w-5 flex-shrink-0" />
                <span class="text-sm font-semibold">Recurring Issue Alerts — Frequent maintenance detected in the last 30 days</span>
            </div>
            <div class="divide-y" :style="{ borderColor: themeColors.border }">
                <div v-for="alert in recurringAlerts" :key="alert.label + alert.type"
                     class="px-6 py-3 flex items-center justify-between"
                     :style="{ backgroundColor: themeColors.card }">
                    <div class="flex items-center gap-3">
                        <ExclamationCircleIcon class="h-5 w-5 flex-shrink-0"
                            :style="{ color: alert.count >= 5 ? 'var(--kotel-danger)' : 'var(--kotel-warning)' }" />
                        <div>
                            <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ alert.message }}</span>
                            <span class="ml-2 text-xs px-2 py-0.5 rounded-full uppercase font-medium"
                                  :style="alert.type === 'room'
                                    ? { backgroundColor: 'rgba(59,130,246,0.15)', color: 'var(--kotel-primary)' }
                                    : { backgroundColor: 'rgba(139,92,246,0.15)', color: '#8b5cf6' }">
                                {{ alert.type }}
                            </span>
                        </div>
                    </div>
                    <span class="text-base font-bold flex-shrink-0 ml-4"
                          :style="{ color: alert.count >= 5 ? 'var(--kotel-danger)' : 'var(--kotel-warning)' }">
                        {{ alert.count }}&times;
                    </span>
                </div>
            </div>
        </div>

        <!-- Two-column layout: Quick Links + Category Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Quick Links -->
            <div class="rounded-lg border p-6"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Quick Links</h2>
                <div class="space-y-3">
                    <Link v-for="link in quickLinks" :key="link.label" :href="link.href"
                          class="flex items-center justify-between p-3 rounded-md border transition-colors"
                          :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                        <div class="flex items-center">
                            <span class="mr-3 text-lg">{{ link.icon }}</span>
                            <div>
                                <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ link.label }}</p>
                                <p class="text-xs" :style="{ color: themeColors.textTertiary }">{{ link.desc }}</p>
                            </div>
                        </div>
                        <ChevronRightIcon class="h-4 w-4" :style="{ color: themeColors.textTertiary }" />
                    </Link>
                </div>
            </div>

            <!-- Active Requests by Category -->
            <div class="lg:col-span-2 rounded-lg border p-6"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Active Requests by Category</h2>
                <div v-if="Object.keys(categoryBreakdown || {}).length > 0" class="space-y-3">
                    <div v-for="(count, cat) in categoryBreakdown" :key="cat"
                         class="flex items-center justify-between p-3 rounded-md"
                         :style="{ backgroundColor: themeColors.background }">
                        <span class="text-sm font-medium capitalize" :style="{ color: themeColors.textPrimary }">{{ cat }}</span>
                        <div class="flex items-center gap-3">
                            <div class="w-32 h-2 rounded-full overflow-hidden" :style="{ backgroundColor: themeColors.border }">
                                <div class="h-full rounded-full" :style="{ width: barWidth(count) + '%', backgroundColor: themeColors.primary }"></div>
                            </div>
                            <span class="text-sm font-bold min-w-[2rem] text-right" :style="{ color: themeColors.textPrimary }">{{ count }}</span>
                        </div>
                    </div>
                </div>
                <div v-else class="py-8 text-center">
                    <p class="text-sm" :style="{ color: themeColors.textTertiary }">No active requests at this time.</p>
                </div>

                <!-- Categories List -->
                <div class="mt-6 pt-4 border-t" :style="{ borderColor: themeColors.border }">
                    <h3 class="text-sm font-semibold mb-3" :style="{ color: themeColors.textSecondary }">Maintenance Categories</h3>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="cat in (categories || [])" :key="cat.id"
                              class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                              :style="{ backgroundColor: (cat.color || themeColors.primary) + '20', color: cat.color || themeColors.primary, border: '1px solid ' + (cat.color || themeColors.primary) + '40' }">
                            {{ cat.name }}
                            <span v-if="!cat.is_active" class="ml-1 opacity-60">(inactive)</span>
                        </span>
                        <Link :href="route('admin.maintenance-categories.index')"
                              class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                              :style="{ backgroundColor: themeColors.background, color: themeColors.textSecondary, border: '1px solid ' + themeColors.border }">
                            Manage Categories &rarr;
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Requests Table -->
        <div class="rounded-lg border"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
            <div class="flex items-center justify-between p-6 pb-4">
                <h2 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Recent Requests</h2>
                <Link :href="route('admin.maintenance-requests.index')"
                      class="text-sm font-medium transition-colors"
                      :style="{ color: themeColors.primary }">
                    View All &rarr;
                </Link>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y" :style="{ borderColor: themeColors.border }">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Request #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Assigned</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Reported</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" :style="{ borderColor: themeColors.border }">
                        <tr v-for="req in (recentRequests || [])" :key="req.id"
                            class="transition-colors"
                            :style="hoveredRow === req.id ? { backgroundColor: themeColors.hover } : { backgroundColor: themeColors.card }"
                            @mouseenter="hoveredRow = req.id"
                            @mouseleave="hoveredRow = null">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ req.request_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textPrimary }">{{ req.title }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm capitalize" :style="{ color: themeColors.textSecondary }">{{ req.category }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full" :style="getPriorityStyle(req.priority)">{{ capitalize(req.priority) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full" :style="getStatusStyle(req.status)">{{ formatStatus(req.status) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textSecondary }">{{ req.assigned_to || 'Unassigned' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textTertiary }">{{ req.reported_at }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <Link :href="route('admin.maintenance-requests.show', req.id)"
                                      class="transition-colors font-medium"
                                      :style="{ color: themeColors.primary }">
                                    View
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!recentRequests || recentRequests.length === 0">
                            <td colspan="8" class="px-6 py-12 text-center" :style="{ color: themeColors.textTertiary }">
                                No maintenance requests yet. <Link :href="route('admin.maintenance-requests.create')" :style="{ color: themeColors.primary }">Create one</Link>.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import {
    WrenchScrewdriverIcon,
    ExclamationCircleIcon,
    ArrowPathIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    ClockIcon,
    ChevronRightIcon,
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
    hover: `rgba(255, 255, 255, 0.05)`
}))
loadTheme()

const props = defineProps({
    user: Object,
    navigation: [Array, Object],
    stats: Object,
    recentRequests: Array,
    categories: Array,
    categoryBreakdown: Object,
    maintenanceRooms: { type: Array, default: () => [] },
    recurringAlerts:  { type: Array, default: () => [] },
})

const hoveredRow = ref(null)

const statCards = computed(() => {
    const s = props.stats || {}
    return [
        { label: 'Total Requests', value: s.total ?? 0, color: 'var(--kotel-primary)', icon: WrenchScrewdriverIcon },
        { label: 'Open', value: s.open ?? 0, color: 'var(--kotel-warning)', icon: ExclamationCircleIcon },
        { label: 'In Progress', value: s.in_progress ?? 0, color: 'var(--kotel-primary)', icon: ArrowPathIcon },
        { label: 'Resolved', value: s.resolved ?? 0, color: 'var(--kotel-success)', icon: CheckCircleIcon },
        { label: 'Urgent', value: s.urgent ?? 0, color: 'var(--kotel-danger)', icon: ExclamationTriangleIcon },
    ]
})

const quickLinks = computed(() => [
    { label: 'Maintenance Requests', desc: `${(props.stats?.open ?? 0) + (props.stats?.in_progress ?? 0)} active`, icon: '📝', href: route('admin.maintenance-requests.index') },
    { label: 'Maintenance Categories', desc: `${(props.categories || []).length} categories`, icon: '📋', href: route('admin.maintenance-categories.index') },
    { label: 'IPTV Devices', desc: 'Manage devices', icon: '📺', href: route('admin.devices.index') },
    { label: 'Preventive Maintenance', desc: 'Scheduled tasks', icon: '📅', href: route('admin.maintenance.preventive.scheduled') },
    { label: 'Housekeeping Tasks', desc: 'Room cleaning tasks', icon: '🧹', href: route('admin.housekeeping-tasks.index') },
])

const maxCategoryCount = computed(() => {
    const vals = Object.values(props.categoryBreakdown || {})
    return vals.length > 0 ? Math.max(...vals) : 1
})

const barWidth = (count) => Math.max(5, (count / maxCategoryCount.value) * 100)

const capitalize = (str) => str ? str.charAt(0).toUpperCase() + str.slice(1) : ''
const formatStatus = (status) => status ? status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : ''

const getStatusStyle = (status) => {
    const map = {
        open: { backgroundColor: 'var(--kotel-warning)', color: 'white' },
        assigned: { backgroundColor: 'var(--kotel-primary)', color: 'white' },
        in_progress: { backgroundColor: 'var(--kotel-primary)', color: 'white' },
        on_hold: { backgroundColor: 'var(--kotel-secondary)', color: 'white' },
        resolved: { backgroundColor: 'var(--kotel-success)', color: 'white' },
        closed: { backgroundColor: 'var(--kotel-secondary)', color: 'white' },
        cancelled: { backgroundColor: 'var(--kotel-danger)', color: 'white' },
    }
    return map[status] || map['open']
}

const getPriorityStyle = (priority) => {
    const map = {
        low: { backgroundColor: 'var(--kotel-secondary)', color: 'white' },
        normal: { backgroundColor: 'var(--kotel-primary)', color: 'white' },
        high: { backgroundColor: 'var(--kotel-warning)', color: 'white' },
        urgent: { backgroundColor: 'var(--kotel-danger)', color: 'white' },
    }
    return map[priority] || map['normal']
}
</script>
