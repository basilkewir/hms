<template>
    <DashboardLayout title="Guest Types" :user="user" :navigation="navigation">
        <!-- Header -->
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
             class="shadow rounded-lg p-6 mb-8 border">
            <div class="flex items-center justify-between">
                <div>
                    <h1 :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">Guest Types Management</h1>
                    <p :style="{ color: themeColors.textSecondary }" class="mt-2">Manage guest types and their configurations</p>
                </div>
                <Link :href="route('admin.guest-types.create')" 
                      :style="{ backgroundColor: themeColors.primary, color: '#000' }"
                      class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity flex items-center">
                    <PlusIcon class="h-4 w-4 mr-2" />
                    Add Guest Type
                </Link>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <TagIcon :style="{ color: themeColors.primary }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Total Types</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ stats.total }}</p>
                    </div>
                </div>
            </div>
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <CheckCircleIcon :style="{ color: themeColors.success }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Active</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ stats.active }}</p>
                    </div>
                </div>
            </div>
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <XCircleIcon :style="{ color: themeColors.danger }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Inactive</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ stats.inactive }}</p>
                    </div>
                </div>
            </div>
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <UsersIcon :style="{ color: themeColors.primary }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Total Guests</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ stats.totalGuests }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Guest Types Table -->
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
             class="shadow rounded-lg overflow-hidden border">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Name</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Code</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Color</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Discount</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Description</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Guests</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="guestType in guestTypes" :key="guestType.id"
                            :style="hoveredRow === guestType.id ? { backgroundColor: themeColors.hover } : {}"
                            @mouseenter="hoveredRow = guestType.id"
                            @mouseleave="hoveredRow = null"
                            class="transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div :style="{ color: themeColors.textPrimary }" class="text-sm font-medium">{{ guestType.name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div :style="{ color: themeColors.textSecondary }" class="text-sm">{{ guestType.code || '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span v-if="guestType.color" 
                                      class="inline-block w-6 h-6 rounded-full border-2"
                                      :style="{ backgroundColor: guestType.color, borderColor: themeColors.border }"
                                      :title="guestType.color"></span>
                                <span v-else :style="{ color: themeColors.textTertiary }" class="text-sm">-</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span v-if="guestType.discount_percentage > 0" :style="{ color: themeColors.success }" class="font-semibold">
                                    {{ guestType.discount_percentage }}%
                                </span>
                                <span v-else :style="{ color: themeColors.textTertiary }">0%</span>
                            </td>
                            <td class="px-6 py-4">
                                <div :style="{ color: themeColors.textSecondary }" class="text-sm">{{ guestType.description || '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div :style="{ color: themeColors.textPrimary }" class="text-sm">{{ guestType.guest_count }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                      :class="guestType.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    {{ guestType.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <Link :href="route('admin.guest-types.edit', guestType.id)" 
                                          :style="{ color: themeColors.primary }" 
                                          class="hover:opacity-80">Edit</Link>
                                    <button @click="deleteGuestType(guestType)" 
                                            :style="{ color: themeColors.danger }" 
                                            class="hover:opacity-80">Delete</button>
                                </div>
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
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'
import { PlusIcon, TagIcon, CheckCircleIcon, XCircleIcon, UsersIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    guestTypes: Array,
    stats: Object,
})

const { currentTheme } = useTheme()
const navigation = computed(() => {
    const userRole = props.user?.roles?.[0]?.name || 'admin'
    return getNavigationForRole(userRole)
})
const hoveredRow = ref(null)

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

const deleteGuestType = (guestType) => {
    if (confirm(`Are you sure you want to delete ${guestType.name}?`)) {
        router.delete(route('admin.guest-types.destroy', guestType.id))
    }
}
</script>
