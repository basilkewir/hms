<template>
    <DashboardLayout title="Customer Groups" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Customer Groups</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Manage customer groups and their discount settings.</p>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="rounded-lg p-4 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="text-sm" :style="{ color: themeColors.textSecondary }">Total Groups</div>
                <div class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ stats.total }}</div>
            </div>
            <div class="rounded-lg p-4 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="text-sm" :style="{ color: themeColors.textSecondary }">Active</div>
                <div class="text-2xl font-bold mt-1" :style="{ color: themeColors.success }">{{ stats.active }}</div>
            </div>
            <div class="rounded-lg p-4 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="text-sm" :style="{ color: themeColors.textSecondary }">Inactive</div>
                <div class="text-2xl font-bold mt-1" :style="{ color: themeColors.warning }">{{ stats.inactive }}</div>
            </div>
            <div class="rounded-lg p-4 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="text-sm" :style="{ color: themeColors.textSecondary }">Total Customers</div>
                <div class="text-2xl font-bold mt-1" :style="{ color: themeColors.primary }">{{ stats.totalCustomers }}</div>
            </div>
        </div>

        <!-- Table -->
        <div class="shadow rounded-lg overflow-hidden" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-6 py-4 border-b flex items-center justify-between" :style="{ borderColor: themeColors.border }">
                <h2 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">All Groups</h2>
                <div class="text-sm" :style="{ color: themeColors.textSecondary }">{{ customerGroups.data?.length || 0 }} groups</div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Group Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Discount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Customers</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="group in customerGroups.data" :key="group.id"
                            class="transition-colors"
                            :style="{ borderBottomStyle: 'solid', borderBottomWidth: '1px', borderColor: themeColors.border }">
                            <td class="px-6 py-4 text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                {{ group.name }}
                            </td>
                            <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textSecondary }">
                                {{ group.description || '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium" :style="{ color: themeColors.success }">
                                {{ group.discount_percentage ? group.discount_percentage + '%' : '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ group.customers_count ?? 0 }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2 py-1 rounded-full text-xs font-medium"
                                      :style="group.is_active ? { backgroundColor: themeColors.success, color: 'white' } : { backgroundColor: themeColors.warning, color: 'white' }">
                                    {{ group.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="!customerGroups.data?.length">
                            <td colspan="5" class="px-6 py-12 text-center text-sm" :style="{ color: themeColors.textSecondary }">
                                No customer groups found.
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
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'

const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background:    `var(--kotel-background)`,
    card:          `var(--kotel-card)`,
    border:        `var(--kotel-border)`,
    textPrimary:   `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary:  `var(--kotel-text-tertiary)`,
    primary:       `var(--kotel-primary)`,
    secondary:     `var(--kotel-secondary)`,
    success:       `var(--kotel-success)`,
    warning:       `var(--kotel-warning)`,
    danger:        `var(--kotel-danger)`,
}))
loadTheme()

defineProps({
    user:           Object,
    navigation:     Array,
    customerGroups: {
        type: Object,
        default: () => ({ data: [], total: 0 })
    },
    stats: {
        type: Object,
        default: () => ({ total: 0, active: 0, inactive: 0, totalCustomers: 0 })
    },
})
</script>
