<template>
    <DashboardLayout title="Guest Types" :user="user">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Guest Types Management</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Manage guest types and their discount configurations.</p>
                </div>
                <Link :href="route('manager.guest-types.create')"
                      class="px-4 py-2 rounded-md font-medium text-white flex items-center transition-colors"
                      :style="{ backgroundColor: themeColors.primary }">
                    <PlusIcon class="h-4 w-4 mr-2" />
                    Add Guest Type
                </Link>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         style="background-color: rgba(59,130,246,0.1)">
                        <TagIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Total Types</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ guestTypes.length }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         style="background-color: rgba(34,197,94,0.1)">
                        <CheckCircleIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Active</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ activeCount }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         style="background-color: rgba(250,204,21,0.1)">
                        <PercentBadgeIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">With Discount</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ discountCount }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         style="background-color: rgba(139,92,246,0.1)">
                        <UsersIcon class="h-6 w-6" style="color: #8b5cf6" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Total Guests</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ totalGuests }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-lg overflow-hidden shadow"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Guest Types</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Color</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Discount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Guests</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="guestType in guestTypes" :key="guestType.id"
                            class="transition-colors"
                            :style="{ borderBottomWidth: '1px', borderBottomStyle: 'solid', borderColor: themeColors.border }"
                            @mouseenter="e => e.currentTarget.style.backgroundColor = themeColors.hover"
                            @mouseleave="e => e.currentTarget.style.backgroundColor = 'transparent'">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">{{ guestType.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">{{ guestType.code || '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span v-if="guestType.color"
                                      class="inline-block w-6 h-6 rounded-full border-2"
                                      :style="{ backgroundColor: guestType.color, borderColor: themeColors.border }"
                                      :title="guestType.color"></span>
                                <span v-else class="text-sm" :style="{ color: themeColors.textSecondary }">—</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span v-if="guestType.discount_percentage > 0"
                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ guestType.discount_percentage }}%
                                </span>
                                <span v-else class="text-sm" :style="{ color: themeColors.textSecondary }">0%</span>
                            </td>
                            <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textSecondary }">
                                {{ guestType.description || '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">{{ guestType.guest_count ?? 0 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="guestType.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    {{ guestType.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link :href="route('manager.guest-types.edit', guestType.id)"
                                      class="mr-3 transition-colors" :style="{ color: themeColors.success }">Edit</Link>
                                <button @click="deleteGuestType(guestType)"
                                        class="transition-colors" :style="{ color: themeColors.danger }">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!guestTypes.length">
                            <td colspan="8" class="px-6 py-12 text-center">
                                <TagIcon class="mx-auto h-12 w-12 mb-3" :style="{ color: themeColors.textTertiary }" />
                                <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">No guest types found</p>
                                <p class="text-sm mt-1" :style="{ color: themeColors.textSecondary }">Create your first guest type to get started.</p>
                                <Link :href="route('manager.guest-types.create')"
                                      class="inline-flex items-center mt-4 px-4 py-2 rounded-md text-sm font-medium text-white"
                                      :style="{ backgroundColor: themeColors.primary }">
                                    <PlusIcon class="h-4 w-4 mr-2" /> Add Guest Type
                                </Link>
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
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { PlusIcon, TagIcon, UsersIcon, CheckCircleIcon, PercentBadgeIcon } from '@heroicons/vue/24/outline'

const { loadTheme } = useTheme()
loadTheme()

const themeColors = computed(() => ({
    background:    'var(--kotel-background)',
    card:          'var(--kotel-card)',
    border:        'var(--kotel-border)',
    textPrimary:   'var(--kotel-text-primary)',
    textSecondary: 'var(--kotel-text-secondary)',
    textTertiary:  'var(--kotel-text-tertiary)',
    primary:       'var(--kotel-primary)',
    success:       'var(--kotel-success)',
    warning:       'var(--kotel-warning)',
    danger:        'var(--kotel-danger)',
    hover:         'rgba(255,255,255,0.05)',
}))

const props = defineProps({
    user:       Object,
    guestTypes: { type: Array, default: () => [] },
})

const activeCount   = computed(() => props.guestTypes.filter(g => g.is_active).length)
const discountCount = computed(() => props.guestTypes.filter(g => g.discount_percentage > 0).length)
const totalGuests   = computed(() => props.guestTypes.reduce((sum, g) => sum + (g.guest_count ?? 0), 0))

const deleteGuestType = (guestType) => {
    if (confirm(`Are you sure you want to delete "${guestType.name}"?`)) {
        router.delete(route('manager.guest-types.destroy', guestType.id))
    }
}
</script>
