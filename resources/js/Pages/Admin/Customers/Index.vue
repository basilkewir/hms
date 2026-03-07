<template>
    <DashboardLayout title="Customers">
        <!-- Header -->
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
                       :style="{ color: themeColors.textSecondary }">Manage customers and customer groups for POS transactions.</p>
                </div>
                <div class="flex space-x-3">
                    <Link href="/admin/customers/create" 
                          class="px-4 py-2 rounded-md transition-colors text-sm font-medium"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                              color: 'white'
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <UserPlusIcon class="h-4 w-4 mr-2 inline" />
                        Add New Customer
                    </Link>
                    <Link href="/admin/customer-groups" 
                          class="px-4 py-2 rounded-md transition-colors text-sm font-medium"
                          :style="{ 
                              backgroundColor: themeColors.success,
                              color: 'white'
                          }"
                          @mouseenter="$event.target.style.backgroundColor = 'rgba(255, 255, 255, 0.1)'"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                        <UserGroupIcon class="h-4 w-4 mr-2 inline" />
                        Manage Groups
                    </Link>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Search</label>
                    <input type="text" v-model="filters.search" placeholder="Search customers..."
                           @input="applyFilters"
                           class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                           :style="{ 
                               backgroundColor: themeColors.background,
                               borderColor: themeColors.border,
                               color: themeColors.textPrimary,
                               borderWidth: '1px',
                               borderStyle: 'solid'
                           }">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Customer Group</label>
                    <select v-model="filters.group_id" @change="applyFilters"
                            class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary,
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }">
                        <option value="">All Groups</option>
                        <option v-for="group in customerGroups" :key="group.id" :value="group.id">
                            {{ group.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Status</label>
                    <select v-model="filters.status" @change="applyFilters"
                            class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary,
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button @click="clearFilters" 
                            class="w-full px-4 py-2 rounded-md transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.secondary,
                                color: themeColors.textPrimary 
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <div v-if="$page.props.flash?.success" 
             class="mb-4 px-4 py-3 rounded-md text-sm"
             :style="{ 
                 backgroundColor: themeColors.success + '20',
                 color: themeColors.success,
                 border: `1px solid ${themeColors.success}`
             }">
            {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash?.error" 
             class="mb-4 px-4 py-3 rounded-md text-sm"
             :style="{ 
                 backgroundColor: themeColors.danger + '20',
                 color: themeColors.danger,
                 border: `1px solid ${themeColors.danger}`
             }">
            {{ $page.props.flash.error }}
        </div>
        <div v-if="actionError" 
             class="mb-4 px-4 py-3 rounded-md text-sm"
             :style="{ 
                 backgroundColor: themeColors.danger + '20',
                 color: themeColors.danger,
                 border: `1px solid ${themeColors.danger}`
             }">
            {{ actionError }}
        </div>

        <!-- Customers Table -->
        <div class="shadow rounded-lg overflow-hidden"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <table class="min-w-full"
                   :style="{ 
                       borderColor: themeColors.border 
                   }">
                <thead class="border-b"
                       :style="{ 
                           backgroundColor: themeColors.background,
                           borderColor: themeColors.border 
                       }">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                            :style="{ color: themeColors.textSecondary }">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                            :style="{ color: themeColors.textSecondary }">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                            :style="{ color: themeColors.textSecondary }">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                            :style="{ color: themeColors.textSecondary }">Group</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                            :style="{ color: themeColors.textSecondary }">Discount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                            :style="{ color: themeColors.textSecondary }">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider"
                            :style="{ color: themeColors.textSecondary }">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-b"
                       :style="{ 
                           backgroundColor: themeColors.card,
                           borderColor: themeColors.border 
                       }">
                    <tr v-for="customer in customers.data" :key="customer.id" 
                        class="transition-colors"
                        :style="{ 
                            '&:hover': {
                                backgroundColor: themeColors.hover
                            }
                        }">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium"
                                 :style="{ color: themeColors.textPrimary }">{{ customer.customer_code }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium"
                                 :style="{ color: themeColors.textPrimary }">{{ customer.first_name }} {{ customer.last_name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm"
                                 :style="{ color: themeColors.textPrimary }">{{ customer.email || 'N/A' }}</div>
                            <div class="text-sm"
                                 :style="{ color: themeColors.textSecondary }">{{ customer.phone || 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm"
                                 :style="{ color: themeColors.textPrimary }">
                                <span v-if="customer.customer_group" 
                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="{ 
                                          backgroundColor: themeColors.primary + '20',
                                          color: themeColors.primary
                                      }">
                                    {{ customer.customer_group.name }}
                                </span>
                                <span v-else 
                                      :style="{ color: themeColors.textTertiary }">No Group</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm"
                                 :style="{ color: themeColors.textPrimary }">
                                <span v-if="customer.customer_group && customer.customer_group.discount_percentage">
                                    {{ customer.customer_group.discount_percentage }}%
                                </span>
                                <span v-else 
                                      :style="{ color: themeColors.textTertiary }">0%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                  :style="{ 
                                      backgroundColor: customer.is_active ? themeColors.success + '20' : themeColors.danger + '20',
                                      color: customer.is_active ? themeColors.success : themeColors.danger
                                  }">
                                {{ customer.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <Link :href="`/admin/customers/${customer.id}`" 
                                  class="mr-4 transition-colors"
                                  :style="{ color: themeColors.primary }"
                                  @mouseenter="$event.target.style.color = themeColors.hover"
                                  @mouseleave="$event.target.style.color = themeColors.primary">View</Link>
                            <Link :href="`/admin/customers/${customer.id}/edit`" 
                                  class="mr-4 transition-colors"
                                  :style="{ color: themeColors.warning }"
                                  @mouseenter="$event.target.style.color = 'rgba(255, 255, 255, 0.1)'"
                                  @mouseleave="$event.target.style.color = themeColors.warning">Edit</Link>
                            <button @click="deleteCustomer(customer)" 
                                    class="transition-colors"
                                    :style="{ color: themeColors.danger }"
                                    @mouseenter="$event.target.style.color = 'rgba(255, 255, 255, 0.1)'"
                                    @mouseleave="$event.target.style.color = themeColors.danger">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="customers.data.length === 0">
                        <td colspan="7" class="px-6 py-4 text-center text-sm"
                            :style="{ color: themeColors.textTertiary }">
                            No customers found. Create your first customer to get started.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="customers.links && customers.links.length > 3" 
                 class="px-4 py-3 border-t"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <Link v-if="customers.prev_page_url" :href="customers.prev_page_url" 
                              class="relative inline-flex items-center px-4 py-2 border rounded-md text-sm font-medium transition-colors"
                              :style="{ 
                                  borderColor: themeColors.border,
                                  color: themeColors.textPrimary,
                                  backgroundColor: themeColors.background
                              }"
                              @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                              @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                            Previous
                        </Link>
                        <Link v-if="customers.next_page_url" :href="customers.next_page_url" 
                              class="ml-3 relative inline-flex items-center px-4 py-2 border rounded-md text-sm font-medium transition-colors"
                              :style="{ 
                                  borderColor: themeColors.border,
                                  color: themeColors.textPrimary,
                                  backgroundColor: themeColors.background
                              }"
                              @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                              @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                            Next
                        </Link>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm"
                               :style="{ color: themeColors.textSecondary }">
                                Showing <span class="font-medium">{{ customers.from }}</span> to <span class="font-medium">{{ customers.to }}</span> of <span class="font-medium">{{ customers.total }}</span> results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <Link v-for="link in customers.links" :key="link.label" 
                                      :href="link.url || '#'" 
                                      v-html="link.label"
                                      class="relative inline-flex items-center px-4 py-2 border text-sm font-medium transition-colors"
                                      :style="{ 
                                          borderColor: themeColors.border,
                                          color: link.active ? 'white' : themeColors.textSecondary,
                                          backgroundColor: link.active ? themeColors.primary : themeColors.card,
                                          opacity: link.url ? '1' : '0.5',
                                          cursor: link.url ? 'pointer' : 'not-allowed'
                                      }"
                                      @mouseenter="link.url && ($event.target.style.backgroundColor = link.active ? themeColors.hover : themeColors.background)"
                                      @mouseleave="link.url && ($event.target.style.backgroundColor = link.active ? themeColors.primary : themeColors.card)">
                                </Link>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { UserPlusIcon, UserGroupIcon } from '@heroicons/vue/24/outline'

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
    customers: Object,
    customerGroups: Array,
    filters: Object,
})

const actionError = ref('')

const filters = ref({
    search: props.filters?.search || '',
    group_id: props.filters?.group_id || '',
    status: props.filters?.status || ''
})

const applyFilters = () => {
    router.get(route('admin.customers.index'), filters.value, {
        preserveState: true,
        preserveScroll: true
    })
}

const clearFilters = () => {
    filters.value = {
        search: '',
        group_id: '',
        status: ''
    }
    applyFilters()
}

const deleteCustomer = (customer) => {
    if (confirm(`Are you sure you want to delete "${customer.first_name} ${customer.last_name}"?`)) {
        router.delete(route('admin.customers.destroy', customer.id), {
            onSuccess: () => {
                // Success handled by flash message
            },
            onError: () => {
                actionError.value = 'Failed to delete customer. It may be in use.'
            }
        })
    }
}
</script>
