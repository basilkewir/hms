<template>
    <DashboardLayout title="Packages" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Packages</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Manage event and stay packages.</p>
                </div>
                <Link
                    :href="route('admin.packages.create')"
                    class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                    :style="{ backgroundColor: themeColors.primary }"
                    @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                    @mouseleave="$event.target.style.backgroundColor = themeColors.primary"
                >
                    New Package
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Total Packages</p>
                <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ stats?.total || 0 }}</p>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Active</p>
                <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ stats?.active || 0 }}</p>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Available</p>
                <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ stats?.available || 0 }}</p>
            </div>
        </div>

        <div class="rounded-lg overflow-hidden shadow border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">All Packages</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Halls</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="pkg in packages?.data || []"
                            :key="pkg.id"
                            class="transition-colors"
                            :style="hoveredRow === pkg.id ? { backgroundColor: themeColors.hover } : {}"
                            @mouseenter="hoveredRow = pkg.id"
                            @mouseleave="hoveredRow = null"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium" :style="{ color: themeColors.textPrimary }">{{ pkg.name }}</div>
                                <div class="text-sm" :style="{ color: themeColors.textTertiary }">{{ pkg.description || '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textSecondary }">{{ pkg.code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(pkg.price || 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textSecondary }">{{ (pkg.halls || []).length }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full"
                                    :style="{ backgroundColor: (pkg.is_active ? themeColors.success : themeColors.danger) + '20', color: pkg.is_active ? themeColors.success : themeColors.danger }"
                                >
                                    {{ pkg.is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <span
                                    class="ml-2 px-2 py-1 text-xs font-semibold rounded-full"
                                    :style="{ backgroundColor: (pkg.is_available ? themeColors.primary : themeColors.warning) + '20', color: pkg.is_available ? themeColors.primary : themeColors.warning }"
                                >
                                    {{ pkg.is_available ? 'Available' : 'Unavailable' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                                <div class="flex items-center justify-end gap-3">
                                    <Link :href="route('admin.packages.show', pkg.id)" class="hover:opacity-80" :style="{ color: themeColors.primary }">View</Link>
                                    <Link :href="route('admin.packages.edit', pkg.id)" class="hover:opacity-80" :style="{ color: themeColors.warning }">Edit</Link>
                                    <button type="button" @click="deletePackage(pkg)" class="hover:opacity-80" :style="{ color: themeColors.danger }">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination v-if="packages?.links" :links="packages.links" :meta="packages.meta" />
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
    packages: Object,
    stats: Object,
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

const deletePackage = (pkg) => {
    if (confirm(`Are you sure you want to delete "${pkg?.name || 'this package'}"?`)) {
        router.delete(route('admin.packages.destroy', pkg.id))
    }
}
</script>
