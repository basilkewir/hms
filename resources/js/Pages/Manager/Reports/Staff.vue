<template>
    <DashboardLayout title="Staff Reports" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Staff Report</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Headcount overview, roles distribution, and recent hires.</p>
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
                        :href="route('admin.reports.staff.export', { format: 'csv', start_date: startDate, end_date: endDate })"
                        class="px-4 py-2 rounded-md border text-sm hover:opacity-80"
                        :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }"
                    >
                        Export CSV
                    </a>
                    <a
                        :href="route('admin.reports.staff.export', { format: 'json', start_date: startDate, end_date: endDate })"
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
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Total Staff</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ stats.total_staff }}</p>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Active Staff</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.success }">{{ stats.active_staff }}</p>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">New Today</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.primary }">{{ stats.new_today }}</p>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">New This Month</p>
                <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.primary }">{{ stats.new_month }}</p>
            </div>
        </div>

        <!-- Body -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- By Role -->
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Staff by Role</h2>
                <div v-if="byRole && byRole.length" class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Role</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in byRole" :key="r.id" class="border-t" :style="{ borderColor: themeColors.border }">
                                <td class="px-4 py-2 text-sm" :style="{ color: themeColors.textPrimary }">{{ r.name }}</td>
                                <td class="px-4 py-2 text-sm text-right" :style="{ color: themeColors.textPrimary }">{{ r.total }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No role data</p>
            </div>

            <!-- Recent Hires -->
            <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Recent Hires</h2>
                <div v-if="recentHires && recentHires.length" class="space-y-3">
                    <div v-for="u in recentHires" :key="u.id" class="flex items-center justify-between pb-3 border-b" :style="{ borderColor: themeColors.border }">
                        <div>
                            <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ u.name }}</p>
                            <p class="text-xs" :style="{ color: themeColors.textSecondary }">{{ u.email }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs" :style="{ color: themeColors.textSecondary }">{{ (u.roles || []).join(', ') }}</p>
                            <p class="text-xs" :style="{ color: themeColors.textSecondary }">{{ formatDate(u.created_at) }}</p>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No recent hires</p>
            </div>
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
    stats: { type: Object, default: () => ({ total_staff: 0, active_staff: 0, new_today: 0, new_month: 0 }) },
    byRole: { type: Array, default: () => [] },
    recentHires: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ start_date: null, end_date: null }) },
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
    router.get(route('admin.reports.staff'), {
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
