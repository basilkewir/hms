<template>
    <DashboardLayout title="Customer Groups" :user="user">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Customer Groups</h1>
                    <p class="text-sm mt-2"
                       :style="{ color: themeColors.textSecondary }">Manage customer groups and their discount settings.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.customer-groups.create')"
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{
                              backgroundColor: themeColors.primary,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <UserPlusIcon class="h-4 w-4 mr-2" />
                        Add New Group
                    </Link>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="flex-shrink-0"
                         :style="{ color: themeColors.primary }">
                        <UserGroupIcon class="h-8 w-8" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Total Groups</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.total }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="flex-shrink-0"
                         :style="{ color: themeColors.success }">
                        <CheckCircleIcon class="h-8 w-8" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Active Groups</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.active }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="flex-shrink-0"
                         :style="{ color: themeColors.warning }">
                        <ClockIcon class="h-8 w-8" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Inactive Groups</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.inactive }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="flex-shrink-0"
                         :style="{ color: themeColors.danger }">
                        <UserIcon class="h-8 w-8" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">Total Customers</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.totalCustomers }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Groups List -->
        <div class="shadow rounded-lg overflow-hidden"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <div class="px-6 py-4 border-b"
                 :style="{
                     borderColor: themeColors.border,
                     borderBottomWidth: '1px'
                 }">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Customer Groups</h2>
                    <div class="text-sm"
                         :style="{ color: themeColors.textSecondary }">
                        {{ customerGroups.total }} total groups
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Group Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Discount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Customers</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y"
                           :style="{ borderColor: themeColors.border }">
                        <tr v-for="group in customerGroups.data" :key="group.id"
                            class="hover:bg-opacity-50 transition-colors cursor-pointer"
                            :style="{ hover: { backgroundColor: themeColors.hover } }"
                            @click="viewGroup(group)">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium"
                                         :style="{ color: themeColors.textPrimary }">{{ group.name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textPrimary }">{{ group.description || 'No description' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full font-medium"
                                      :style="{
                                          backgroundColor: themeColors.success + '20',
                                          color: themeColors.success
                                      }">
                                    {{ group.discount_percentage }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textPrimary }">{{ group.customers_count || 0 }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full font-medium"
                                      :class="group.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    {{ group.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <Link :href="route('admin.customer-groups.show', group.id)"
                                          class="text-blue-600 hover:text-blue-900"
                                          @click.stop>
                                        View
                                    </Link>
                                    <Link :href="route('admin.customer-groups.edit', group.id)"
                                          class="text-indigo-600 hover:text-indigo-900"
                                          @click.stop>
                                        Edit
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t"
                 :style="{ borderColor: themeColors.border, borderTopWidth: '1px' }">
                <div class="flex items-center justify-between">
                    <div class="text-sm"
                         :style="{ color: themeColors.textSecondary }">
                        Showing {{ customerGroups.from || 0 }} to {{ customerGroups.to || 0 }} of {{ customerGroups.total || 0 }} results
                    </div>
                    <div class="flex space-x-2">
                        <Link v-for="link in (customerGroups?.links || [])"
                              :key="link.label"
                              :href="link.url"
                              class="px-3 py-1 rounded-md text-sm transition-colors"
                              :class="link.active ?
                                  'text-white' :
                                  'hover:bg-opacity-50'"
                              :style="link.active ?
                                  { backgroundColor: themeColors.primary } :
                                  {
                                      backgroundColor: themeColors.background,
                                      color: themeColors.textPrimary
                                  }"
                              v-html="link.label"
                              v-if="link && link.url">
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import {
    UserPlusIcon,
    UserGroupIcon,
    CheckCircleIcon,
    ClockIcon,
    UserIcon
} from '@heroicons/vue/24/outline'

// Initialize theme
const { loadTheme } = useTheme()
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
    hover: `rgba(255, 255, 255, 0.1)`
}))

// Load theme on mount
loadTheme()

const props = defineProps({
    user: Object,
    customerGroups: {
        type: Object,
        default: () => ({
            data: [],
            total: 0,
            from: 0,
            to: 0,
            links: []
        })
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            active: 0,
            inactive: 0,
            totalCustomers: 0
        })
    },
})

const viewGroup = (group) => {
    router.visit(route('admin.customer-groups.show', group.id));
}
</script>
