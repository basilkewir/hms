<template>
    <DashboardLayout title="Package Details" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Package Details</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">{{ package?.name || 'Package' }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('admin.packages.edit', package.id)"
                        class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                        :style="{ backgroundColor: themeColors.warning }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.warning"
                    >
                        Edit
                    </Link>
                    <Link
                        :href="route('admin.packages.index')"
                        class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                        :style="{ backgroundColor: themeColors.primary }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.primary"
                    >
                        Back
                    </Link>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 rounded-lg overflow-hidden shadow border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                    <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Overview</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <div class="text-sm" :style="{ color: themeColors.textTertiary }">Code</div>
                        <div class="font-medium" :style="{ color: themeColors.textPrimary }">{{ package?.code || '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm" :style="{ color: themeColors.textTertiary }">Description</div>
                        <div :style="{ color: themeColors.textSecondary }">{{ package?.description || '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm" :style="{ color: themeColors.textTertiary }">Price</div>
                        <div class="font-medium" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(package?.price || 0) }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg overflow-hidden shadow border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                    <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Status</h3>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <span :style="{ color: themeColors.textSecondary }">Active</span>
                        <span class="font-medium" :style="{ color: package?.is_active ? themeColors.success : themeColors.danger }">{{ package?.is_active ? 'Yes' : 'No' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span :style="{ color: themeColors.textSecondary }">Available</span>
                        <span class="font-medium" :style="{ color: package?.is_available ? themeColors.primary : themeColors.warning }">{{ package?.is_available ? 'Yes' : 'No' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span :style="{ color: themeColors.textSecondary }">Max Bookings</span>
                        <span class="font-medium" :style="{ color: themeColors.textPrimary }">{{ package?.max_bookings ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3 rounded-lg overflow-hidden shadow border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                    <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Halls</h3>
                </div>
                <div class="p-6">
                    <div v-if="(package?.halls || []).length" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div v-for="hall in package.halls" :key="hall.id" class="p-4 rounded-lg border" :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background }">
                            <div class="font-medium" :style="{ color: themeColors.textPrimary }">{{ hall.name }}</div>
                            <div class="text-sm" :style="{ color: themeColors.textSecondary }">{{ hall.code }}</div>
                        </div>
                    </div>
                    <div v-else :style="{ color: themeColors.textSecondary }">No halls linked to this package.</div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'
import { formatCurrency } from '@/Utils/currency.js'

const props = defineProps({
    user: Object,
    package: Object,
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
</script>
