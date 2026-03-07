<template>
    <DashboardLayout title="Customers" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Customer Management</h1>
                    <p class="text-gray-600 mt-2">Manage customers and customer groups for POS transactions.</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('manager.customers.create')" 
                          class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        <UserPlusIcon class="h-4 w-4 mr-2 inline" />
                        Add New Customer
                    </Link>
                    <Link :href="route('manager.customer-groups.index')" 
                          class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                        <UserGroupIcon class="h-4 w-4 mr-2 inline" />
                        Manage Groups
                    </Link>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" v-model="filters.search" placeholder="Search customers..."
                           @input="applyFilters"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Customer Group</label>
                    <select v-model="filters.group_id" @change="applyFilters"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Groups</option>
                        <option v-for="group in customerGroups" :key="group.id" :value="group.id">
                            {{ group.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select v-model="filters.status" @change="applyFilters"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button @click="resetFilters" 
                            class="w-full bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300">
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Customer
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Contact
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Group
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ customer.first_name }} {{ customer.last_name }}
                                </div>
                                <div class="text-sm text-gray-500">{{ customer.customer_code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ customer.email || '-' }}</div>
                                <div class="text-sm text-gray-500">{{ customer.phone || '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span v-if="customer.customer_group" 
                                      class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                    {{ customer.customer_group.name }}
                                </span>
                                <span v-else class="text-sm text-gray-500">No Group</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="customer.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                      class="px-2 py-1 text-xs font-medium rounded-full">
                                    {{ customer.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link :href="route('manager.customers.show', customer.id)" 
                                      class="text-blue-600 hover:text-blue-900 mr-3">View</Link>
                                <Link :href="route('manager.customers.edit', customer.id)" 
                                      class="text-green-600 hover:text-green-900">Edit</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="!customers.data || customers.data.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No customers</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new customer.</p>
                <div class="mt-6">
                    <Link :href="route('manager.customers.create')"
                          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <UserPlusIcon class="h-4 w-4 mr-2" />
                        Add New Customer
                    </Link>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="customers.links" class="px-6 py-4 border-t border-gray-200">
                <Pagination :links="customers.links" :meta="customers" />
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { UserPlusIcon, UserGroupIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    customers: Object,
    customerGroups: Array,
    filters: Object,
})

const navigation = computed(() => getNavigationForRole('manager'))

const filters = ref({
    search: props.filters?.search || '',
    group_id: props.filters?.group_id || '',
    status: props.filters?.status || '',
})

const applyFilters = () => {
    router.get(route('manager.customers.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    })
}

const resetFilters = () => {
    filters.value = {
        search: '',
        group_id: '',
        status: '',
    }
    applyFilters()
}
</script>
