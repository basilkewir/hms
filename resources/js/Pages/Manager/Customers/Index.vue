<template>
    <DashboardLayout title="Customers" :user="user">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Customer Management</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Manage customers and customer groups for POS transactions.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('manager.customers.create')"
                          class="px-4 py-2 rounded-md font-medium text-white flex items-center transition-colors"
                          :style="{ backgroundColor: themeColors.primary }">
                        <UserPlusIcon class="h-4 w-4 mr-2" />
                        Add Customer
                    </Link>
                    <Link :href="route('manager.customer-groups.index')"
                          class="px-4 py-2 rounded-md font-medium text-white flex items-center transition-colors"
                          :style="{ backgroundColor: '#8b5cf6' }">
                        <UserGroupIcon class="h-4 w-4 mr-2" />
                        Manage Groups
                    </Link>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         style="background-color: rgba(59,130,246,0.1)">
                        <UsersIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Total Customers</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ customers.total || 0 }}</p>
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
                         style="background-color: rgba(239,68,68,0.1)">
                        <XCircleIcon class="h-6 w-6" :style="{ color: themeColors.danger }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Inactive</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ (customers.total || 0) - activeCount }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         style="background-color: rgba(139,92,246,0.1)">
                        <UserGroupIcon class="h-6 w-6" style="color: #8b5cf6" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Groups</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ customerGroups.length }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="rounded-lg p-6 mb-8 border shadow-sm"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Search</label>
                    <input type="text" v-model="filters.search" placeholder="Search customers..."
                           @input="applyFilters"
                           class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                           :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Customer Group</label>
                    <select v-model="filters.group_id" @change="applyFilters"
                            class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        <option value="">All Groups</option>
                        <option v-for="group in customerGroups" :key="group.id" :value="group.id">{{ group.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Status</label>
                    <select v-model="filters.status" @change="applyFilters"
                            class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button @click="resetFilters"
                            class="w-full px-4 py-2 rounded-md text-sm font-medium transition-colors border"
                            :style="{ borderColor: themeColors.border, color: themeColors.textSecondary, backgroundColor: themeColors.background }">
                        Reset Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-lg overflow-hidden shadow"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Customers</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Group</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="customer in customers.data" :key="customer.id"
                            class="transition-colors"
                            :style="{ borderBottomWidth: '1px', borderBottomStyle: 'solid', borderColor: themeColors.border }"
                            @mouseenter="e => e.currentTarget.style.backgroundColor = themeColors.hover"
                            @mouseleave="e => e.currentTarget.style.backgroundColor = 'transparent'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                    {{ customer.first_name }} {{ customer.last_name }}
                                </div>
                                <div class="text-sm" :style="{ color: themeColors.textSecondary }">{{ customer.customer_code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm" :style="{ color: themeColors.textPrimary }">{{ customer.email || '—' }}</div>
                                <div class="text-sm" :style="{ color: themeColors.textSecondary }">{{ customer.phone || '—' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span v-if="customer.customer_group"
                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ customer.customer_group.name }}
                                </span>
                                <span v-else class="text-sm" :style="{ color: themeColors.textSecondary }">No Group</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="customer.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    {{ customer.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link :href="route('manager.customers.show', customer.id)"
                                      class="mr-3 transition-colors" :style="{ color: themeColors.primary }">View</Link>
                                <Link :href="route('manager.customers.edit', customer.id)"
                                      class="transition-colors" :style="{ color: themeColors.success }">Edit</Link>
                            </td>
                        </tr>
                        <tr v-if="!customers.data?.length">
                            <td colspan="5" class="px-6 py-12 text-center">
                                <UsersIcon class="mx-auto h-12 w-12 mb-3" :style="{ color: themeColors.textTertiary }" />
                                <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">No customers found</p>
                                <p class="text-sm mt-1" :style="{ color: themeColors.textSecondary }">Get started by creating a new customer.</p>
                                <Link :href="route('manager.customers.create')"
                                      class="inline-flex items-center mt-4 px-4 py-2 rounded-md text-sm font-medium text-white"
                                      :style="{ backgroundColor: themeColors.primary }">
                                    <UserPlusIcon class="h-4 w-4 mr-2" /> Add Customer
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div v-if="customers.links" class="px-6 py-4 border-t" :style="{ borderColor: themeColors.border }">
                <Pagination :links="customers.links" />
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { UserPlusIcon, UserGroupIcon, UsersIcon, CheckCircleIcon, XCircleIcon } from '@heroicons/vue/24/outline'

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
    user:           Object,
    customers:      Object,
    customerGroups: { type: Array, default: () => [] },
    filters:        Object,
})

const activeCount = computed(() => props.customers?.data?.filter(c => c.is_active).length ?? 0)

const filters = ref({
    search:   props.filters?.search   || '',
    group_id: props.filters?.group_id || '',
    status:   props.filters?.status   || '',
})

const applyFilters = () => {
    router.get(route('manager.customers.index'), filters.value, { preserveState: true, preserveScroll: true })
}

const resetFilters = () => {
    filters.value = { search: '', group_id: '', status: '' }
    applyFilters()
}
</script>
