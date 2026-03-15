<template>
    <DashboardLayout title="Halls" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Halls</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Manage banquet, conference and meeting halls.</p>
                </div>
                <Link
                    :href="route(`${routePrefix}.halls.create`)"
                    class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                    :style="{ backgroundColor: themeColors.primary }"
                    @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                    @mouseleave="$event.target.style.backgroundColor = themeColors.primary"
                >
                    New Hall
                </Link>
            </div>
        </div>

        <div class="rounded-lg overflow-hidden shadow border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">All Halls</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Capacity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Base Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="hall in halls?.data || []"
                            :key="hall.id"
                            class="transition-colors"
                            :style="hoveredRow === hall.id ? { backgroundColor: themeColors.hover } : {}"
                            @mouseenter="hoveredRow = hall.id"
                            @mouseleave="hoveredRow = null"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium" :style="{ color: themeColors.textPrimary }">{{ hall.name }}</div>
                                <div class="text-sm" :style="{ color: themeColors.textTertiary }">{{ hall.description || '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textSecondary }">{{ hall.code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm capitalize" :style="{ color: themeColors.textSecondary }">{{ hall.type }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textSecondary }">{{ hall.capacity }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(hall.base_price || 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full"
                                    :style="{ backgroundColor: (hall.is_active ? themeColors.success : themeColors.danger) + '20', color: hall.is_active ? themeColors.success : themeColors.danger }"
                                >
                                    {{ hall.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                                <div class="flex items-center justify-end gap-3">
                                    <Link :href="route(`${routePrefix}.halls.show`, hall.id)" class="hover:opacity-80" :style="{ color: themeColors.primary }">View</Link>
                                    <Link :href="route(`${routePrefix}.halls.edit`, hall.id)" class="hover:opacity-80" :style="{ color: themeColors.warning }">Edit</Link>
                                    <button type="button" @click="deleteHall(hall)" class="hover:opacity-80" :style="{ color: themeColors.danger }">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination v-if="halls?.links" :links="halls.links" :meta="halls.meta" />
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'
import { formatCurrency } from '@/Utils/currency.js'

const props = defineProps({
    user: Object,
    halls: Object,
    routePrefix: { type: String, default: 'admin' },
})

const { currentTheme } = useTheme()
const navigation = computed(() => getNavigationForRole('admin'))

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
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)'
}))

const hoveredRow = ref(null)

const deleteHall = (hall) => {
    if (confirm(`Are you sure you want to delete "${hall?.name || 'this hall'}"?`)) {
        router.delete(route(`${props.routePrefix}.halls.destroy`, hall.id))
    }
}
</script>
