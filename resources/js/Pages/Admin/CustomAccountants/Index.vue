<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import {
    UserGroupIcon,
    ChevronDownIcon,
    ChevronUpIcon,
    PlusIcon,
    TrashIcon,
    CheckBadgeIcon,
    ExclamationCircleIcon,
} from '@heroicons/vue/24/outline'

const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    primary: `var(--kotel-primary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
    hover: `rgba(255, 255, 255, 0.1)`
}))
loadTheme()

const props = defineProps({
    user: Object,
    navigation: Array,
    accountants: Array,
})

// ── Local state ──────────────────────────────────────────────────────────────
const expandedUser = ref(null)
const overridesByUser = ref({}) // { [userId]: [...] }
const loadingToggle = ref(null)
const loadingOverrides = ref(null)
const savingOverrides = ref(null)

const metricSuggestions = {
    profit_loss: [
        'profitLossData.total_revenue',
        'profitLossData.net_profit',
        'profitLossData.total_cogs',
        'profitLossData.total_operating_expenses',
        'profitLossData.total_other_income_expenses',
    ],
    balance_sheet: [
        'balanceSheetData.totalAssets',
        'balanceSheetData.totals.total_liabilities',
        'balanceSheetData.totals.total_equity',
    ],
    cash_flow: [
        'cashFlowData.beginning_cash',
        'cashFlowData.ending_cash',
        'cashFlowData.net_income',
        'cashFlowData.operating_cash_flow',
        'cashFlowData.investing_cash_flow',
        'cashFlowData.financing_cash_flow',
    ],
    revenue: [
        'revenueData.total_revenue',
        'revenueData.room_revenue',
        'revenueData.pos_sales_revenue',
        'revenueData.growth_rate',
        'revenueData.average_daily_rate',
    ],
}

const reportTypes = ['profit_loss', 'balance_sheet', 'cash_flow', 'revenue']

// New override form state per user
const newOverride = ref({})

// ── Helpers ──────────────────────────────────────────────────────────────────
const accountantsList = ref(props.accountants ?? [])

function formatReportType(t) {
    return { profit_loss: 'Profit & Loss', balance_sheet: 'Balance Sheet', cash_flow: 'Cash Flow', revenue: 'Revenue' }[t] ?? t
}

// ── Toggle is_custom_accountant ──────────────────────────────────────────────
async function toggleCustom(accountant) {
    loadingToggle.value = accountant.id
    try {
        const res = await fetch(`/admin/custom-accountants/${accountant.id}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        })
        const data = await res.json()
        const idx = accountantsList.value.findIndex(a => a.id === accountant.id)
        if (idx !== -1) {
            accountantsList.value[idx].is_custom_accountant = data.is_custom_accountant
        }
    } catch (e) {
        console.error('Toggle failed', e)
    } finally {
        loadingToggle.value = null
    }
}

// ── Expand / load overrides ──────────────────────────────────────────────────
async function toggleExpand(accountant) {
    if (expandedUser.value === accountant.id) {
        expandedUser.value = null
        return
    }
    expandedUser.value = accountant.id
    if (!overridesByUser.value[accountant.id]) {
        await loadOverrides(accountant.id)
    }
    // Init new-override form for this user
    if (!newOverride.value[accountant.id]) {
        newOverride.value[accountant.id] = { report_type: 'profit_loss', metric_key: '', custom_value: '' }
    }
}

async function loadOverrides(userId) {
    loadingOverrides.value = userId
    try {
        const res = await fetch(`/admin/custom-accountants/${userId}/overrides`, {
            headers: { 'Accept': 'application/json' },
        })
        overridesByUser.value[userId] = await res.json()
    } catch (e) {
        console.error('Load overrides failed', e)
        overridesByUser.value[userId] = []
    } finally {
        loadingOverrides.value = null
    }
}

// ── Add override ─────────────────────────────────────────────────────────────
async function addOverride(userId) {
    const form = newOverride.value[userId]
    if (!form?.metric_key || form?.custom_value === '') return

    savingOverrides.value = userId
    try {
        const res = await fetch(`/admin/custom-accountants/${userId}/overrides`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                overrides: [{
                    report_type: form.report_type,
                    metric_key: form.metric_key,
                    custom_value: Number(form.custom_value),
                }]
            }),
        })
        if (res.ok) {
            await loadOverrides(userId)
            newOverride.value[userId] = { report_type: form.report_type, metric_key: '', custom_value: '' }
            // Update override_count in list
            const idx = accountantsList.value.findIndex(a => a.id === userId)
            if (idx !== -1) {
                accountantsList.value[idx].override_count = (overridesByUser.value[userId] ?? []).length
            }
        }
    } catch (e) {
        console.error('Add override failed', e)
    } finally {
        savingOverrides.value = null
    }
}

// ── Delete override ──────────────────────────────────────────────────────────
async function deleteOverride(userId, overrideId) {
    if (!confirm('Delete this override?')) return
    try {
        const res = await fetch(`/admin/custom-accountants/overrides/${overrideId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        })
        if (res.ok) {
            overridesByUser.value[userId] = (overridesByUser.value[userId] ?? []).filter(o => o.id !== overrideId)
            const idx = accountantsList.value.findIndex(a => a.id === userId)
            if (idx !== -1) {
                accountantsList.value[idx].override_count = (overridesByUser.value[userId] ?? []).length
            }
        }
    } catch (e) {
        console.error('Delete override failed', e)
    }
}
</script>

<template>
    <Head title="Custom Accountants" />
    <DashboardLayout :user="user" :navigation="navigation">

        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-6 border"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center gap-3">
                <UserGroupIcon class="h-8 w-8" :style="{ color: themeColors.primary }" />
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Custom Accountants</h1>
                    <p class="text-sm mt-1" :style="{ color: themeColors.textSecondary }">
                        Enable custom (fake) report data per accountant, and manage per-field overrides.
                        Fake data is only revealed to admin and manager — the accountant sees their customized figures as real.
                    </p>
                </div>
            </div>
        </div>

        <!-- Info box -->
        <div class="mb-6 bg-yellow-900 border border-yellow-600 rounded-lg p-4">
            <div class="flex items-start gap-2 text-yellow-300">
                <ExclamationCircleIcon class="h-5 w-5 mt-0.5 flex-shrink-0" />
                <div class="text-sm">
                    <strong>How it works:</strong> When you enable <em>Custom Mode</em> for an accountant and add overrides,
                    that accountant will see the overridden values in their reports instead of the real figures.
                    Admin and Manager will see an orange warning banner in all accountant report pages listing which accounts have customized data.
                    The accountant themselves will never see a warning.
                </div>
            </div>
        </div>

        <!-- Accountants list -->
        <div v-if="!accountantsList || accountantsList.length === 0"
             class="text-center py-12 rounded-lg border"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textSecondary }">
            No accountant users found.
        </div>

        <div v-else class="space-y-4">
            <div v-for="acct in accountantsList" :key="acct.id"
                 class="rounded-lg border overflow-hidden"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">

                <!-- Accountant row -->
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 rounded-full flex items-center justify-center text-white font-bold text-sm"
                             :style="{ backgroundColor: acct.is_custom_accountant ? '#b45309' : themeColors.primary }">
                            {{ (acct.name || '?').charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <div class="font-semibold" :style="{ color: themeColors.textPrimary }">
                                {{ acct.name || acct.email }}
                            </div>
                            <div class="text-xs" :style="{ color: themeColors.textSecondary }">
                                {{ acct.email }}
                                <span v-if="acct.override_count > 0" class="ml-2 text-amber-400">
                                    {{ acct.override_count }} override(s)
                                </span>
                            </div>
                        </div>

                        <!-- Status badge -->
                        <span v-if="acct.is_custom_accountant"
                              class="px-2 py-1 text-xs font-bold rounded-full bg-amber-700 text-amber-200">
                            Custom Mode ON
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Toggle button -->
                        <button
                            @click="toggleCustom(acct)"
                            :disabled="loadingToggle === acct.id"
                            class="px-3 py-1.5 text-sm rounded-md font-medium transition-colors disabled:opacity-50"
                            :class="acct.is_custom_accountant
                                ? 'bg-red-700 hover:bg-red-600 text-white'
                                : 'bg-green-700 hover:bg-green-600 text-white'">
                            <template v-if="loadingToggle === acct.id">...</template>
                            <template v-else>
                                {{ acct.is_custom_accountant ? 'Disable Custom Mode' : 'Enable Custom Mode' }}
                            </template>
                        </button>

                        <!-- Expand overrides -->
                        <button
                            @click="toggleExpand(acct)"
                            class="px-3 py-1.5 text-sm rounded-md font-medium transition-colors text-white"
                            :style="{ backgroundColor: themeColors.primary }">
                            <ChevronUpIcon v-if="expandedUser === acct.id" class="h-4 w-4" />
                            <ChevronDownIcon v-else class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <!-- Overrides panel -->
                <div v-if="expandedUser === acct.id"
                     class="border-t p-4"
                     :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background }">

                    <h3 class="font-semibold mb-3 text-sm" :style="{ color: themeColors.textPrimary }">
                        Report Overrides for {{ acct.name || acct.email }}
                    </h3>

                    <!-- Loading -->
                    <p v-if="loadingOverrides === acct.id" class="text-sm py-2" :style="{ color: themeColors.textSecondary }">
                        Loading overrides...
                    </p>

                    <!-- Existing overrides table -->
                    <div v-else-if="overridesByUser[acct.id] && overridesByUser[acct.id].length > 0" class="mb-4 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b" :style="{ borderColor: themeColors.border }">
                                    <th class="text-left py-1.5 pr-4" :style="{ color: themeColors.textSecondary }">Report Type</th>
                                    <th class="text-left py-1.5 pr-4" :style="{ color: themeColors.textSecondary }">Metric Key (dot-notation)</th>
                                    <th class="text-left py-1.5 pr-4" :style="{ color: themeColors.textSecondary }">Custom Value</th>
                                    <th class="text-right py-1.5" :style="{ color: themeColors.textSecondary }">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="ov in overridesByUser[acct.id]" :key="ov.id"
                                    class="border-b" :style="{ borderColor: themeColors.border }">
                                    <td class="py-1.5 pr-4" :style="{ color: themeColors.textPrimary }">
                                        {{ formatReportType(ov.report_type) }}
                                    </td>
                                    <td class="py-1.5 pr-4 font-mono text-xs" :style="{ color: themeColors.textPrimary }">
                                        {{ ov.metric_key }}
                                    </td>
                                    <td class="py-1.5 pr-4 text-amber-400 font-semibold">
                                        {{ ov.custom_value }}
                                    </td>
                                    <td class="py-1.5 text-right">
                                        <button @click="deleteOverride(acct.id, ov.id)"
                                                class="text-red-400 hover:text-red-300 transition-colors">
                                            <TrashIcon class="h-4 w-4 inline" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="text-sm mb-4" :style="{ color: themeColors.textSecondary }">
                        No overrides yet. Add one below.
                    </p>

                    <!-- Add new override form -->
                    <div v-if="acct.is_custom_accountant" class="mt-2 p-3 rounded-md border"
                         :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                        <p class="text-xs font-semibold mb-2" :style="{ color: themeColors.textSecondary }">
                            + Add Override
                        </p>
                        <div class="flex flex-wrap gap-3 items-end">
                            <!-- Report type -->
                            <div>
                                <label class="block text-xs mb-1" :style="{ color: themeColors.textSecondary }">Report</label>
                                <select v-model="newOverride[acct.id].report_type"
                                        class="rounded px-2 py-1.5 text-sm border focus:outline-none"
                                        :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }">
                                    <option v-for="rt in reportTypes" :key="rt" :value="rt">{{ formatReportType(rt) }}</option>
                                </select>
                            </div>

                            <!-- Metric key with datalist suggestions -->
                            <div class="flex-1 min-w-48">
                                <label class="block text-xs mb-1" :style="{ color: themeColors.textSecondary }">Metric Key</label>
                                <input
                                    :list="`suggestions-${acct.id}`"
                                    v-model="newOverride[acct.id].metric_key"
                                    placeholder="e.g. revenueData.total_revenue"
                                    class="rounded px-2 py-1.5 text-sm border focus:outline-none w-full font-mono"
                                    :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }" />
                                <datalist :id="`suggestions-${acct.id}`">
                                    <option v-for="s in metricSuggestions[newOverride[acct.id]?.report_type] ?? []" :key="s" :value="s" />
                                </datalist>
                            </div>

                            <!-- Custom value -->
                            <div>
                                <label class="block text-xs mb-1" :style="{ color: themeColors.textSecondary }">Custom Value</label>
                                <input
                                    type="number"
                                    v-model="newOverride[acct.id].custom_value"
                                    placeholder="0.00"
                                    step="0.01"
                                    class="rounded px-2 py-1.5 text-sm border focus:outline-none w-32"
                                    :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }" />
                            </div>

                            <!-- Save button -->
                            <button
                                @click="addOverride(acct.id)"
                                :disabled="savingOverrides === acct.id"
                                class="px-3 py-1.5 text-sm rounded-md font-medium text-white flex items-center gap-1 disabled:opacity-50"
                                :style="{ backgroundColor: themeColors.success }">
                                <PlusIcon class="h-4 w-4" />
                                {{ savingOverrides === acct.id ? 'Saving…' : 'Add' }}
                            </button>
                        </div>
                    </div>

                    <!-- Hint when custom mode is off -->
                    <div v-else class="mt-2 text-xs p-2 rounded border border-dashed"
                         :style="{ color: themeColors.textSecondary, borderColor: themeColors.border }">
                        Enable Custom Mode first to add overrides for this accountant.
                    </div>
                </div>

            </div>
        </div>

    </DashboardLayout>
</template>
