<template>
    <DashboardLayout title="Customer Group Details" :user="user">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">{{ customerGroup.name }}</h1>
                    <p class="text-sm mt-2"
                       :style="{ color: themeColors.textSecondary }">Customer group details and management.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('accountant.customer-groups.index')" 
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ 
                              backgroundColor: themeColors.secondary,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        <ArrowPathIcon class="h-4 w-4 mr-2" />
                        Back to Groups
                    </Link>
                    <Link :href="route('accountant.customer-groups.edit', customerGroup.id)" 
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PencilIcon class="h-4 w-4 mr-2" />
                        Edit Group
                    </Link>
                </div>
            </div>
        </div>

        <!-- Group Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Main Information -->
            <div class="lg:col-span-2">
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
                        <h2 class="text-lg font-semibold"
                            :style="{ color: themeColors.textPrimary }">Group Information</h2>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2"
                                       :style="{ color: themeColors.textSecondary }">Group Name</label>
                                <div class="text-lg font-medium"
                                     :style="{ color: themeColors.textPrimary }">{{ customerGroup.name }}</div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium mb-2"
                                       :style="{ color: themeColors.textSecondary }">Status</label>
                                <span class="px-3 py-1 text-sm rounded-full font-medium"
                                      :class="customerGroup.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    {{ customerGroup.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Description</label>
                            <div class="text-sm"
                                 :style="{ color: themeColors.textPrimary }">
                                {{ customerGroup.description || 'No description provided' }}
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Discount Percentage</label>
                            <div class="flex items-center">
                                <span class="text-2xl font-bold"
                                      :style="{ color: themeColors.primary }">{{ customerGroup.discount_percentage }}%</span>
                                <span class="ml-2 text-sm"
                                      :style="{ color: themeColors.textSecondary }">discount for all customers in this group</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div>
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
                        <h2 class="text-lg font-semibold"
                            :style="{ color: themeColors.textPrimary }">Statistics</h2>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm"
                                  :style="{ color: themeColors.textSecondary }">Total Customers</span>
                            <span class="text-lg font-bold"
                                  :style="{ color: themeColors.textPrimary }">{{ customerGroup.customers?.length || 0 }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm"
                                  :style="{ color: themeColors.textSecondary }">Discount Rate</span>
                            <span class="text-lg font-bold"
                                  :style="{ color: themeColors.success }">{{ customerGroup.discount_percentage }}%</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm"
                                  :style="{ color: themeColors.textSecondary }">Status</span>
                            <span class="text-sm font-medium"
                                  :class="customerGroup.is_active ? 'text-green-600' : 'text-red-600'">
                                {{ customerGroup.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customers in this Group -->
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
                        :style="{ color: themeColors.textPrimary }">Customers in this Group</h2>
                    <div class="text-sm"
                         :style="{ color: themeColors.textSecondary }">
                        {{ customerGroup.customers?.length || 0 }} customers
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
                                :style="{ color: themeColors.textSecondary }">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y"
                           :style="{ borderColor: themeColors.border }">
                        <tr v-for="customer in (customerGroup.customers || [])" :key="customer.id"
                            class="hover:bg-opacity-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium"
                                     :style="{ color: themeColors.textPrimary }">{{ customer.first_name }} {{ customer.last_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textPrimary }">{{ customer.email || 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textPrimary }">{{ customer.phone || 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full"
                                      :class="customer.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    {{ customer.status }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="!customerGroup.customers || customerGroup.customers.length === 0">
                            <td colspan="4" class="px-6 py-8 text-center">
                                <div class="text-sm"
                                     :style="{ color: themeColors.textSecondary }">No customers assigned to this group yet.</div>
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
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { ArrowPathIcon, PencilIcon } from '@heroicons/vue/24/outline'

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
    customerGroup: Object,
})
</script>
