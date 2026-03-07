<script setup>
import { ref, computed, watch } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'

const page = usePage()
const user = computed(() => page.props.auth.user)
const navigation = computed(() => page.props.navigation)

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
    hover: `var(--kotel-primary-hover)`
}))

const employees = computed(() => page.props.employees)
const filters = computed(() => page.props.filters)
const departments = computed(() => page.props.departments)

const searchParams = ref({
    search: filters.value.search || '',
    department: filters.value.department || '',
    status: filters.value.status || ''
})

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const getStatusBadge = (isActive) => {
    return {
        text: isActive ? 'Active' : 'Inactive',
        style: {
            backgroundColor: isActive ? themeColors.value.success + '20' : themeColors.value.danger + '20',
            color: isActive ? themeColors.value.success : themeColors.value.danger
        }
    }
}

// Watch for changes and update URL
watch(searchParams, debounce((newParams) => {
    router.get(route('hr.employees.index'), newParams, {
        preserveState: true,
        replace: true
    })
}, 300), { deep: true })

const resetFilters = () => {
    searchParams.value = {
        search: '',
        department: '',
        status: ''
    }
}

const deleteEmployee = (employeeId) => {
    if (confirm('Are you sure you want to delete this employee?')) {
        router.delete(route('hr.employees.destroy', employeeId))
    }
}
</script>

<template>
    <DashboardLayout title="Employees" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Employees</h1>
                    <p :style="{ color: themeColors.textSecondary }">Manage your hotel staff and team members</p>
                </div>
                <Link :href="route('hr.employees.create')"
                      class="px-4 py-2 rounded-lg font-medium text-white transition-colors"
                      :style="{ backgroundColor: themeColors.primary }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                    Add Employee
                </Link>
            </div>
        </div>

        <!-- Filters -->
        <div class="rounded-lg p-6 mb-6 border shadow-sm"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Search</label>
                    <input 
                        v-model="searchParams.search"
                        type="text" 
                        placeholder="Search employees..."
                        class="w-full px-3 py-2 rounded-lg border text-sm"
                        :style="{
                            backgroundColor: themeColors.background,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary,
                            borderStyle: 'solid',
                            borderWidth: '1px'
                        }"
                    />
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Department</label>
                    <select 
                        v-model="searchParams.department"
                        class="w-full px-3 py-2 rounded-lg border text-sm"
                        :style="{
                            backgroundColor: themeColors.background,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary,
                            borderStyle: 'solid',
                            borderWidth: '1px'
                        }">
                        <option value="">All Departments</option>
                        <option v-for="dept in departments" :key="dept.id" :value="dept.name">
                            {{ dept.name }}
                        </option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Status</label>
                    <select 
                        v-model="searchParams.status"
                        class="w-full px-3 py-2 rounded-lg border text-sm"
                        :style="{
                            backgroundColor: themeColors.background,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary,
                            borderStyle: 'solid',
                            borderWidth: '1px'
                        }">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button 
                        @click="resetFilters"
                        class="w-full px-3 py-2 rounded-lg border text-sm font-medium transition-colors"
                        :style="{
                            backgroundColor: themeColors.background,
                            color: themeColors.textSecondary,
                            borderColor: themeColors.border,
                            borderStyle: 'solid',
                            borderWidth: '1px'
                        }">
                        Reset Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Employees Table -->
        <div class="rounded-lg border shadow-sm overflow-hidden"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="text-left p-4 font-medium text-sm" :style="{ color: themeColors.textSecondary }">Employee</th>
                            <th class="text-left p-4 font-medium text-sm" :style="{ color: themeColors.textSecondary }">Role</th>
                            <th class="text-left p-4 font-medium text-sm" :style="{ color: themeColors.textSecondary }">Department</th>
                            <th class="text-left p-4 font-medium text-sm" :style="{ color: themeColors.textSecondary }">Contact</th>
                            <th class="text-left p-4 font-medium text-sm" :style="{ color: themeColors.textSecondary }">Hire Date</th>
                            <th class="text-left p-4 font-medium text-sm" :style="{ color: themeColors.textSecondary }">Status</th>
                            <th class="text-left p-4 font-medium text-sm" :style="{ color: themeColors.textSecondary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="employee in employees.data" :key="employee.id"
                            class="border-t transition-colors hover:opacity-80"
                            :style="{ 
                                borderColor: themeColors.border,
                                borderStyle: 'solid',
                                borderWidth: '1px'
                            }">
                            <td class="p-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm"
                                         :style="{ backgroundColor: themeColors.primary, color: 'white' }">
                                        {{ getInitials(employee.name) }}
                                    </div>
                                    <div>
                                        <p class="font-medium" :style="{ color: themeColors.textPrimary }">{{ employee.name }}</p>
                                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                                            ID: {{ employee.employee_id || 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-1 rounded text-xs font-medium"
                                      :style="{
                                          backgroundColor: themeColors.primary + '20',
                                          color: themeColors.primary
                                      }">
                                    {{ employee.roles?.[0]?.name || 'No role' }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span v-if="employee.departments?.length > 0" 
                                      class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">
                                    {{ employee.departments[0].name }}
                                </span>
                                <span v-else 
                                      class="text-sm"
                                      :style="{ color: themeColors.textTertiary }">
                                    No department
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="text-sm">
                                    <p :style="{ color: themeColors.textPrimary }">{{ employee.email }}</p>
                                    <p :style="{ color: themeColors.textSecondary }">{{ employee.phone || 'No phone' }}</p>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="text-sm" :style="{ color: themeColors.textPrimary }">
                                    {{ formatDate(employee.hire_date) }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-1 rounded-full text-xs font-medium"
                                      :style="getStatusBadge(employee.is_active).style">
                                    {{ getStatusBadge(employee.is_active).text }}
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center space-x-2">
                                    <Link :href="route('hr.employees.show', employee.id)"
                                          class="p-1 rounded hover:opacity-80 transition-opacity"
                                          :style="{ color: themeColors.primary }">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </Link>
                                    <Link :href="route('hr.employees.edit', employee.id)"
                                          class="p-1 rounded hover:opacity-80 transition-opacity"
                                          :style="{ color: themeColors.warning }">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </Link>
                                    <button @click="deleteEmployee(employee.id)"
                                            class="p-1 rounded hover:opacity-80 transition-opacity"
                                            :style="{ color: themeColors.danger }">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="employees.links" class="p-4 border-t" :style="{ borderColor: themeColors.border }">
                <div class="flex items-center justify-between">
                    <div class="text-sm" :style="{ color: themeColors.textSecondary }">
                        Showing {{ employees.from || 0 }} to {{ employees.to || 0 }} of {{ employees.total }} results
                    </div>
                    <div class="flex items-center space-x-2">
                        <Link v-if="employees.prev_page_url" :href="employees.prev_page_url"
                              class="px-3 py-1 rounded border text-sm transition-colors"
                              :style="{
                                  backgroundColor: themeColors.background,
                                  borderColor: themeColors.border,
                                  color: themeColors.textPrimary,
                                  borderStyle: 'solid',
                                  borderWidth: '1px'
                              }">
                            Previous
                        </Link>
                        <Link v-if="employees.next_page_url" :href="employees.next_page_url"
                              class="px-3 py-1 rounded border text-sm transition-colors"
                              :style="{
                                  backgroundColor: themeColors.background,
                                  borderColor: themeColors.border,
                                  color: themeColors.textPrimary,
                                  borderStyle: 'solid',
                                  borderWidth: '1px'
                              }">
                            Next
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="employees.data.length === 0" class="text-center py-12">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
                 :style="{ backgroundColor: themeColors.background, color: themeColors.textTertiary }">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium mb-2" :style="{ color: themeColors.textPrimary }">No employees found</h3>
            <p :style="{ color: themeColors.textSecondary }">Get started by adding your first employee.</p>
            <Link :href="route('hr.employees.create')"
                  class="mt-4 inline-flex px-4 py-2 rounded-lg font-medium text-white transition-colors"
                  :style="{ backgroundColor: themeColors.primary }">
                Add Employee
            </Link>
        </div>
    </DashboardLayout>
</template>
