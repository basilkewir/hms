<template>
    <DashboardLayout title="Customer Management" :user="user">
        <!-- Page Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Customer Management</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Manage customer accounts and view customer details.</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('accountant.customers.create')"
                          class="px-4 py-2 rounded-md transition-colors flex items-center"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                              color: '#ffffff'
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <UserPlusIcon class="h-4 w-4 mr-2" />
                        Add Customer
                    </Link>
                </div>
            </div>
        </div>

        <!-- Filters and Actions -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Search</label>
                    <input
                        type="text"
                        v-model="filters.search"
                        placeholder="Search customers..."
                        class="w-full px-3 py-2 rounded-md focus:outline-none transition-colors"
                        :style="{ 
                            backgroundColor: themeColors.background,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary
                        }"
                        @input="debouncedSearch"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Customer Group</label>
                    <select
                        v-model="filters.group_id"
                        class="w-full px-3 py-2 rounded-md focus:outline-none transition-colors"
                        :style="{ 
                            backgroundColor: themeColors.background,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary
                        }"
                        @change="searchCustomers"
                    >
                        <option value="">All Groups</option>
                        <option v-for="group in customerGroups" :key="group.id" :value="group.id">
                            {{ group.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Status</label>
                    <select
                        v-model="filters.status"
                        class="w-full px-3 py-2 rounded-md focus:outline-none transition-colors"
                        :style="{ 
                            backgroundColor: themeColors.background,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary
                        }"
                        @change="searchCustomers"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <button
                        @click="searchCustomers"
                        class="flex-1 px-4 py-2 rounded-md font-medium transition-colors flex items-center"
                        :style="{ 
                            backgroundColor: themeColors.primary,
                            color: '#ffffff'
                        }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <MagnifyingGlassIcon class="h-4 w-4 mr-2" />
                        Search
                    </button>
                    <button
                        @click="resetFilters"
                        class="flex-1 px-4 py-2 rounded-md font-medium transition-colors flex items-center"
                        :style="{ 
                            backgroundColor: themeColors.secondary,
                            color: themeColors.textPrimary
                        }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        <ArrowPathIcon class="h-4 w-4 mr-2" />
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Customer List -->
        <div class="shadow rounded-lg"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="px-6 py-4 border-b"
                 :style="{ borderColor: themeColors.border }">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Customers</h2>
                    <div class="text-sm"
                         :style="{ color: themeColors.textSecondary }">
                        {{ customers?.total || 0 }} total customers
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Phone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Group</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y"
                           :style="{ borderColor: themeColors.border }">
                        <tr v-for="customer in (customers?.data || [])" :key="customer.id"
                            class="hover:bg-opacity-50 transition-colors cursor-pointer"
                            :style="{ hover: { backgroundColor: themeColors.hover } }"
                            @click="viewCustomer(customer)">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium"
                                         :style="{ color: themeColors.textPrimary }">{{ customer.first_name }} {{ customer.last_name }}</div>
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textSecondary }">{{ customer.customer_id }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textPrimary }">{{ customer.email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textPrimary }">{{ customer.phone || 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full"
                                      :style="{ 
                                          backgroundColor: themeColors.background,
                                          color: themeColors.textPrimary
                                      }">
                                    {{ customer.customer_group?.name || 'No Group' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full"
                                      :class="customer.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    {{ customer.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <Link :href="route('accountant.customers.show', customer.id)"
                                          class="text-blue-600 hover:text-blue-900"
                                          @click.stop>
                                        View
                                    </Link>
                                    <Link :href="route('accountant.customers.edit', customer.id)"
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
                 :style="{ borderColor: themeColors.border }">
                <div class="flex items-center justify-between">
                    <div class="text-sm"
                         :style="{ color: themeColors.textSecondary }">
                        Showing {{ customers?.from || 0 }} to {{ customers?.to || 0 }} of {{ customers?.total || 0 }} results
                    </div>
                    <div class="flex space-x-2">
                        <Link v-for="link in (customers?.links || [])" 
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
import { ref, onMounted, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useTheme } from '@/Composables/useTheme.js';
import { UserPlusIcon, MagnifyingGlassIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    user: Object,
    customers: Object,
    customerGroups: Array,
    filters: Object
});

const { loadTheme } = useTheme();
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
}));

loadTheme();

const filters = ref({
    search: props.filters?.search || '',
    group_id: props.filters?.group_id || '',
    status: props.filters?.status || ''
});

const currentDateTime = computed(() => {
    return new Date().toLocaleString();
});

const debouncedSearch = (() => {
    let timeoutId = null;
    return () => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            searchCustomers();
        }, 500);
    };
})();

const searchCustomers = () => {
    router.get(route('accountant.customers.index'), filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const resetFilters = () => {
    filters.value = {
        search: '',
        group_id: '',
        status: ''
    };
    searchCustomers();
};

const viewCustomer = (customer) => {
    router.visit(route('accountant.customers.show', customer.id));
};
</script>
