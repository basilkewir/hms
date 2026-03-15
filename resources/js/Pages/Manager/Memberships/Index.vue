<template>
    <DashboardLayout title="Memberships" :user="user" :navigation="navigation">
        <!-- Header -->
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
             class="shadow rounded-lg p-6 mb-8 border">
            <div class="flex items-center justify-between">
                <div>
                    <h1 :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">Memberships</h1>
                    <p :style="{ color: themeColors.textSecondary }" class="mt-2">Manage guest memberships and loyalty tiers.</p>
                </div>
                <Link :href="route('admin.memberships.create')" 
                      :style="{ backgroundColor: themeColors.primary, color: '#000' }"
                      class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity flex items-center">
                    <PlusIcon class="h-4 w-4 mr-2" />
                    Add Membership
                </Link>
            </div>
        </div>

        <!-- Flash Messages -->
        <div v-if="flash.success || flash.error" class="mb-4">
            <div v-if="flash.success"
                 class="rounded-md p-4 border"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.success }">
                <p :style="{ color: themeColors.textPrimary }" class="text-sm">{{ flash.success }}</p>
            </div>
            <div v-if="flash.error"
                 class="rounded-md p-4 border mt-3"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.danger }">
                <p :style="{ color: themeColors.textPrimary }" class="text-sm">{{ flash.error }}</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <StarIcon :style="{ color: themeColors.primary }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Total Tiers</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ stats.total }}</p>
                    </div>
                </div>
            </div>
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <UsersIcon :style="{ color: themeColors.success }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Active Members</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ stats.active_members }}</p>
                    </div>
                </div>
            </div>
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <GiftIcon :style="{ color: themeColors.warning }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Rewards Redeemed</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ stats.rewards_redeemed }}</p>
                    </div>
                </div>
            </div>
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <ChartBarIcon :style="{ color: themeColors.primary }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Points Issued</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ stats.points_issued }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Memberships Table / Empty State -->
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
             class="shadow rounded-lg overflow-hidden border">
            <div v-if="(memberships?.length || 0) > 0" class="overflow-x-auto">
                <table class="min-w-full">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Tier</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Min Points</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Discount</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Members</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="tier in memberships" :key="tier.id"
                            :style="hoveredRow === tier.id ? { backgroundColor: themeColors.hover } : {}"
                            @mouseenter="hoveredRow = tier.id"
                            @mouseleave="hoveredRow = null"
                            class="transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div :style="{ color: themeColors.textPrimary }" class="text-sm font-medium">{{ tier.name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div :style="{ color: themeColors.textSecondary }" class="text-sm">{{ tier.min_points }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span :style="{ color: themeColors.success }" class="font-semibold">{{ tier.discount_percentage || 0 }}%</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div :style="{ color: themeColors.textPrimary }" class="text-sm">{{ tier.members_count || 0 }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <Link :href="route('admin.memberships.edit', tier.id)" 
                                          :style="{ color: themeColors.primary }" 
                                          class="hover:opacity-80">Edit</Link>
                                    <button @click="deleteTier(tier)" 
                                            :style="{ color: themeColors.danger }" 
                                            class="hover:opacity-80">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="p-8 text-center">
                <p :style="{ color: themeColors.textSecondary }" class="text-sm">No memberships yet. Click "Add Membership" to create your first tier.</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'
import { PlusIcon, StarIcon, UsersIcon, GiftIcon, ChartBarIcon } from '@heroicons/vue/24/outline'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
    user: Object,
    memberships: Array,
    stats: { type: Object, default: () => ({ total: 0, active_members: 0, rewards_redeemed: 0, points_issued: 0 }) }
})

const { currentTheme } = useTheme()
const navigation = computed(() => {
    const userRole = props.user?.roles?.[0]?.name || 'admin'
    return getNavigationForRole(userRole)
})
const hoveredRow = ref(null)

const page = usePage()
const flash = computed(() => page.props.flash || {})

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

const deleteTier = (tier) => {
    if (confirm(`Are you sure you want to delete ${tier.name}?`)) {
        router.delete(route('admin.memberships.destroy', tier.id))
    }
}
</script>
