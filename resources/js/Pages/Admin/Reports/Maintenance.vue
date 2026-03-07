<template>
    <DashboardLayout title="Maintenance Report" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Maintenance Report</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Overview of maintenance workload, open requests, and recently completed work.</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-2">
                        <label class="text-sm" :style="{ color: themeColors.textSecondary }">From</label>
                        <input
                            type="date"
                            v-model="startDate"
                            @focus="tryShowPicker($event.target)"
                            @click="tryShowPicker($event.target)"
                            @keydown.prevent
                            @paste.prevent
                            class="rounded-md px-3 py-2 text-sm focus:outline-none"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }"
                        />
                        <label class="text-sm" :style="{ color: themeColors.textSecondary }">To</label>
                        <input
                            type="date"
                            v-model="endDate"
                            @focus="tryShowPicker($event.target)"
                            @click="tryShowPicker($event.target)"
                            @keydown.prevent
                            @paste.prevent
                            class="rounded-md px-3 py-2 text-sm focus:outline-none"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }"
                        />
                        <button
                            type="button"
                            @click="applyFilters"
                            class="px-3 py-2 rounded-md text-sm font-medium hover:opacity-90"
                            :style="{ backgroundColor: themeColors.primary, color: '#000' }"
                        >
                            Apply
                        </button>
                    </div>
                    <a
                        :href="route('admin.reports.maintenance.export', { format: 'csv', start_date: startDate, end_date: endDate })"
                        class="px-4 py-2 rounded-md border text-sm hover:opacity-80"
                        :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }"
                    >
                        Export CSV
                    </a>
                    <a
                        :href="route('admin.reports.maintenance.export', { format: 'json', start_date: startDate, end_date: endDate })"
                        class="px-4 py-2 rounded-md text-sm hover:opacity-90"
                        :style="{ backgroundColor: themeColors.secondary, color: '#000' }"
                    >
                        Export JSON
                    </a>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Total Requests</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ stats.total_requests }}</p>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Open Requests</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.warning }">{{ stats.open_requests }}</p>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">In Progress</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.primary }">{{ stats.in_progress }}</p>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Completed Today</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.success }">{{ stats.completed_today }}</p>
            </div>
        </div>

        <!-- Body -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Open / In-progress Requests -->
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">
                    Open &amp; In-progress Requests
                    <span class="text-sm font-normal ml-2" :style="{ color: themeColors.textSecondary }">({{ recentOpen.length }})</span>
                </h2>
                <div v-if="recentOpen && recentOpen.length" class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Title</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Location</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in recentOpen" :key="r.id" class="border-t" :style="{ borderColor: themeColors.border }">
                                <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ r.title }}</td>
                                <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ r.location }}</td>
                                <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ r.status }}</td>
                                <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ formatDate(r.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No open or in-progress requests</p>
            </div>

            <!-- Recently Completed -->
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">
                    Recently Completed
                    <span class="text-sm font-normal ml-2" :style="{ color: themeColors.textSecondary }">({{ recentCompleted.length }})</span>
                </h2>
                <div v-if="recentCompleted && recentCompleted.length" class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Title</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Location</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Completed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in recentCompleted" :key="r.id" class="border-t" :style="{ borderColor: themeColors.border }">
                                <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ r.title }}</td>
                                <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ r.location }}</td>
                                <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ r.status }}</td>
                                <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ formatDate(r.updated_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No recently completed requests</p>
            </div>
        </div>

        <!-- All Requests in Range -->
        <div class="mt-6 shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">
                All Requests in Range
                <span class="text-sm font-normal ml-2" :style="{ color: themeColors.textSecondary }">({{ allRequests.length }})</span>
            </h2>
            <div v-if="allRequests && allRequests.length" class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">ID</th>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Title</th>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Location</th>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Created</th>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="r in allRequests" :key="r.id" class="border-t" :style="{ borderColor: themeColors.border }">
                            <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">#{{ r.id }}</td>
                            <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ r.title }}</td>
                            <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ r.location }}</td>
                            <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ r.status }}</td>
                            <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ formatDate(r.created_at) }}</td>
                            <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ formatDate(r.updated_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No maintenance requests in this date range</p>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'

const props = defineProps({
    user: Object,
    stats: { type: Object, default: () => ({ total_requests: 0, open_requests: 0, in_progress: 0, completed_today: 0 }) },
    recentOpen: { type: Array, default: () => [] },
    recentCompleted: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ start_date: null, end_date: null }) },
    allRequests: { type: Array, default: () => [] },
})

const navigation = computed(() => {
    const role = props.user?.roles?.[0]?.name || 'admin'
    return getNavigationForRole(role)
})

const { currentTheme } = useTheme()
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
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.02)'
}))

const startDate = ref(props.filters?.start_date || '')
const endDate = ref(props.filters?.end_date || '')

const applyFilters = () => {
    router.get(route('admin.reports.maintenance'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    })
}

const tryShowPicker = (el) => {
    if (el && typeof el.showPicker === 'function') {
        el.showPicker()
    }
}

const formatDate = (d) => {
    if (!d) return ''
    const dt = new Date(d)
    return isNaN(dt.getTime()) ? '' : dt.toLocaleDateString()
}
</script>
