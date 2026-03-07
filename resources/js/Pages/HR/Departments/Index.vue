<script setup>
import { ref, computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

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

const departments = computed(() => page.props.departments)

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const deleteDepartment = (departmentId) => {
    if (confirm('Are you sure you want to delete this department? This action cannot be undone.')) {
        router.delete(route('hr.departments.destroy', departmentId))
    }
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}
</script>

<template>
    <DashboardLayout title="Departments" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Departments</h1>
                    <p :style="{ color: themeColors.textSecondary }">Manage hotel departments and their staff</p>
                </div>
                <Link :href="route('hr.departments.create')"
                      class="px-4 py-2 rounded-lg font-medium text-white transition-colors"
                      :style="{ backgroundColor: themeColors.primary }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                    Add Department
                </Link>
            </div>
        </div>

        <!-- Departments Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="department in departments.data" :key="department.id"
                 class="rounded-lg border shadow-sm overflow-hidden transition-all hover:shadow-md"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <!-- Department Header -->
                <div class="p-6 border-b" :style="{ borderColor: themeColors.border }">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center"
                             :style="{ backgroundColor: themeColors.primary + '20', color: themeColors.primary }">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Link :href="route('hr.departments.edit', department.id)"
                                  class="p-1 rounded hover:opacity-80 transition-opacity"
                                  :style="{ color: themeColors.warning }">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </Link>
                            <button @click="deleteDepartment(department.id)"
                                    class="p-1 rounded hover:opacity-80 transition-opacity"
                                    :style="{ color: themeColors.danger }">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <h3 class="text-xl font-semibold mb-2" :style="{ color: themeColors.textPrimary }">
                        {{ department.name }}
                    </h3>
                    <p v-if="department.description" class="text-sm mb-4" :style="{ color: themeColors.textSecondary }">
                        {{ department.description }}
                    </p>
                </div>

                <!-- Department Stats -->
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="text-center">
                            <p class="text-2xl font-bold" :style="{ color: themeColors.primary }">
                                {{ department.users_count || 0 }}
                            </p>
                            <p class="text-sm" :style="{ color: themeColors.textSecondary }">Employees</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold" :style="{ color: themeColors.success }">
                                {{ department.active_employees_count || 0 }}
                            </p>
                            <p class="text-sm" :style="{ color: themeColors.textSecondary }">Active</p>
                        </div>
                    </div>

                    <!-- Department Manager -->
                    <div v-if="department.manager" class="mb-4">
                        <p class="text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Department Manager</p>
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-semibold text-xs"
                                 :style="{ backgroundColor: themeColors.success, color: 'white' }">
                                {{ getInitials(department.manager.name) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                    {{ department.manager.name }}
                                </p>
                                <p class="text-xs" :style="{ color: themeColors.textSecondary }">
                                    {{ department.manager.email }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Employees -->
                    <div v-if="department.recent_employees && department.recent_employees.length > 0">
                        <p class="text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Recent Employees</p>
                        <div class="space-y-2">
                            <div v-for="employee in department.recent_employees.slice(0, 3)" :key="employee.id"
                                 class="flex items-center space-x-2">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center font-semibold text-xs"
                                     :style="{ backgroundColor: themeColors.background, color: themeColors.textTertiary }">
                                    {{ getInitials(employee.name) }}
                                </div>
                                <span class="text-xs" :style="{ color: themeColors.textPrimary }">
                                    {{ employee.name }}
                                </span>
                                <span class="text-xs" :style="{ color: themeColors.textTertiary }">
                                    Joined {{ formatDate(employee.created_at) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- View Details Button -->
                    <div class="mt-4 pt-4 border-t" :style="{ borderColor: themeColors.border }">
                        <Link :href="route('hr.departments.show', department.id)"
                              class="block w-full text-center px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                              :style="{
                                  backgroundColor: themeColors.background,
                                  color: themeColors.primary,
                                  borderColor: themeColors.border,
                                  borderStyle: 'solid',
                                  borderWidth: '1px'
                              }">
                            View Department Details
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="departments.links" class="mt-6">
            <div class="flex items-center justify-between">
                <div class="text-sm" :style="{ color: themeColors.textSecondary }">
                    Showing {{ departments.from || 0 }} to {{ departments.to || 0 }} of {{ departments.total }} departments
                </div>
                <div class="flex items-center space-x-2">
                    <Link v-if="departments.prev_page_url" :href="departments.prev_page_url"
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
                    <Link v-if="departments.next_page_url" :href="departments.next_page_url"
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

        <!-- Empty State -->
        <div v-if="departments.data.length === 0" class="text-center py-12">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
                 :style="{ backgroundColor: themeColors.background, color: themeColors.textTertiary }">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium mb-2" :style="{ color: themeColors.textPrimary }">No departments found</h3>
            <p :style="{ color: themeColors.textSecondary }">Get started by creating your first department.</p>
            <Link :href="route('hr.departments.create')"
                  class="mt-4 inline-flex px-4 py-2 rounded-lg font-medium text-white transition-colors"
                  :style="{ backgroundColor: themeColors.primary }">
                Create Department
            </Link>
        </div>
    </DashboardLayout>
</template>
