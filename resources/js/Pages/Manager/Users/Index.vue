<template>
    <DashboardLayout title="User Management">
        <!-- Users Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">User Management</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Manage hotel staff and user accounts.</p>
                </div>
                <div class="flex space-x-3">
                    <Link href="/manager/users/create"
                          class="px-4 py-2 rounded-md transition-colors text-sm font-medium"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                              color: 'white'
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <UserPlusIcon class="h-4 w-4 mr-2 inline" />
                        Add New User
                    </Link>
                    <button @click="exportUsers" 
                            class="px-4 py-2 rounded-md transition-colors text-sm font-medium"
                            :style="{ 
                                backgroundColor: themeColors.success,
                                color: 'white'
                            }"
                            @mouseenter="$event.target.style.backgroundColor = 'rgba(255, 255, 255, 0.1)'"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2 inline" />
                        Export All Users
                    </button>
                </div>
            </div>
        </div>

        <!-- User Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="p-4 rounded-lg border"
                 :style="{ 
                     backgroundColor: themeColors.background,
                     borderColor: themeColors.border,
                     borderWidth: '1px',
                     borderStyle: 'solid'
                 }">
                <div class="flex items-center">
                    <div class="p-2 rounded-md mr-3"
                         :style="{ backgroundColor: themeColors.primary + '20' }">
                        <UserGroupIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <h3 class="text-sm font-medium"
                            :style="{ color: themeColors.textSecondary }">Total Users</h3>
                        <p class="text-2xl font-bold mt-1"
                           :style="{ color: themeColors.textPrimary }">{{ userStats?.total || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="p-4 rounded-lg border"
                 :style="{ 
                     backgroundColor: themeColors.background,
                     borderColor: themeColors.border,
                     borderWidth: '1px',
                     borderStyle: 'solid'
                 }">
                <div class="flex items-center">
                    <div class="p-2 rounded-md mr-3"
                         :style="{ backgroundColor: themeColors.success + '20' }">
                        <CheckCircleIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <h3 class="text-sm font-medium"
                            :style="{ color: themeColors.textSecondary }">Active Users</h3>
                        <p class="text-2xl font-bold mt-1"
                           :style="{ color: themeColors.textPrimary }">{{ userStats?.active || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="p-4 rounded-lg border"
                 :style="{ 
                     backgroundColor: themeColors.background,
                     borderColor: themeColors.border,
                     borderWidth: '1px',
                     borderStyle: 'solid'
                 }">
                <div class="flex items-center">
                    <div class="p-2 rounded-md mr-3"
                         :style="{ backgroundColor: themeColors.warning + '20' }">
                        <ClockIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <h3 class="text-sm font-medium"
                            :style="{ color: themeColors.textSecondary }">Pending</h3>
                        <p class="text-2xl font-bold mt-1"
                           :style="{ color: themeColors.textPrimary }">{{ userStats?.pending || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="p-4 rounded-lg border"
                 :style="{ 
                     backgroundColor: themeColors.background,
                     borderColor: themeColors.border,
                     borderWidth: '1px',
                     borderStyle: 'solid'
                 }">
                <div class="flex items-center">
                    <div class="p-2 rounded-md mr-3"
                         :style="{ backgroundColor: themeColors.danger + '20' }">
                        <XCircleIcon class="h-6 w-6" :style="{ color: themeColors.danger }" />
                    </div>
                    <div>
                        <h3 class="text-sm font-medium"
                            :style="{ color: themeColors.textSecondary }">Inactive</h3>
                        <p class="text-2xl font-bold mt-1"
                           :style="{ color: themeColors.textPrimary }">{{ userStats?.inactive || 0 }}</p>
                    </div>
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
                    <input type="text" v-model="searchQuery" placeholder="Search users..."
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
                           :style="{ color: themeColors.textSecondary }">Department</label>
                    <select v-model="selectedDepartment"
                            class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary,
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }">
                        <option value="">All Departments</option>
                        <option value="front_desk">Front Desk</option>
                        <option value="housekeeping">Housekeeping</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="management">Management</option>
                        <option value="accounting">Accounting</option>
                        <option value="food_beverage">Food & Beverage</option>
                        <option value="administration">Administration</option>
                        <option value="finance">Finance</option>
                        <option value="front_office">Front Office</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Role</label>
                    <select v-model="selectedRole"
                            class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary,
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }">
                        <option value="">All Roles</option>
                        <option value="admin">Administrator</option>
                        <option value="manager">Manager</option>
                        <option value="accountant">Accountant</option>
                        <option value="front_desk">Front Desk</option>
                        <option value="housekeeping">Housekeeping</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="bartender">Bartender</option>
                        <option value="chef">Chef</option>
                        <option value="server">Server</option>
                        <option value="bar_staff">Bar Staff</option>
                        <option value="restaurant_staff">Restaurant Staff</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Status</label>
                    <select v-model="selectedStatus"
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
                        <option value="pending">Pending</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="shadow rounded-lg overflow-hidden"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="px-6 py-4 border-b"
                 :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">All Users</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full"
                       :style="{ borderColor: themeColors.border }">
                    <thead class="border-b"
                           :style="{ 
                               backgroundColor: themeColors.background,
                               borderColor: themeColors.border 
                           }">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                User
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Role
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Department
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Last Login
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="border-b"
                           :style="{ 
                               backgroundColor: themeColors.card,
                               borderColor: themeColors.border 
                           }">
                        <tr v-for="user in filteredUsers" :key="user.id" 
                            class="transition-colors"
                            :style="{ 
                                '&:hover': {
                                    backgroundColor: themeColors.hover
                                }
                            }">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3"
                                         :style="{ backgroundColor: themeColors.border }">
                                        <span class="text-sm font-medium"
                                              :style="{ color: themeColors.textPrimary }">
                                            {{ (user.first_name ? user.first_name.charAt(0) : '') + (user.last_name ? user.last_name.charAt(0) : '') }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium"
                                             :style="{ color: themeColors.textPrimary }">{{ user.first_name }} {{ user.last_name }}</div>
                                        <div class="text-sm"
                                             :style="{ color: themeColors.textSecondary }">{{ user.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                      :style="{ 
                                          backgroundColor: themeColors.warning + '20',
                                          color: themeColors.warning,
                                          border: `1px solid ${themeColors.warning}40`
                                      }">
                                    {{ formatRole(user.roles?.[0]?.name) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatDepartment(user.department) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="{ 
                                          backgroundColor: getStatusColor(user.status) + '20',
                                          color: getStatusColor(user.status)
                                      }">
                                    {{ user.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                {{ formatDate(user.last_login) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button @click="viewUser(user.id)" 
                                            class="transition-colors"
                                            :style="{ color: themeColors.primary }"
                                            @mouseenter="$event.target.style.color = themeColors.hover"
                                            @mouseleave="$event.target.style.color = themeColors.primary">View</button>
                                    <button @click="editUser(user.id)" 
                                            class="transition-colors"
                                            :style="{ color: themeColors.warning }"
                                            @mouseenter="$event.target.style.color = 'rgba(255, 255, 255, 0.1)'"
                                            @mouseleave="$event.target.style.color = themeColors.warning">Edit</button>
                                    <button @click="deleteUser(user.id)" 
                                            class="transition-colors"
                                            :style="{ color: themeColors.danger }"
                                            @mouseenter="$event.target.style.color = 'rgba(255, 255, 255, 0.1)'"
                                            @mouseleave="$event.target.style.color = themeColors.danger">Delete</button>
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
import { ref, computed, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import {
    UserGroupIcon,
    UserPlusIcon,
    CheckCircleIcon,
    ClockIcon,
    XCircleIcon,
    DocumentArrowDownIcon
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
    users: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
            meta: {}
        })
    },
    userStats: {
        type: Object,
        default: () => ({
            total: 0,
            active: 0,
            pending: 0,
            inactive: 0
        })
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            department: '',
            role: '',
            status: ''
        })
    }
})

const navigation = computed(() => getNavigationForRole('admin'))

const searchQuery = ref(props.filters.search || '')
const selectedDepartment = ref(props.filters.department || '')
const selectedRole = ref(props.filters.role || '')
const selectedStatus = ref(props.filters.status || '')

// Watch for filter changes and update URL
watch([searchQuery, selectedDepartment, selectedRole, selectedStatus], () => {
    router.get(route('admin.users.index'), {
        search: searchQuery.value,
        department: selectedDepartment.value,
        role: selectedRole.value,
        status: selectedStatus.value
    }, {
        preserveState: true,
        preserveScroll: true
    })
}, { debounce: 500 })

const filteredUsers = computed(() => {
    return props.users.data.filter(user => {
        const fullName = (user.first_name + ' ' + user.last_name).toLowerCase()
        const matchesSearch = !searchQuery.value ||
            fullName.includes(searchQuery.value.toLowerCase()) ||
            user.email.toLowerCase().includes(searchQuery.value.toLowerCase())

        const matchesDepartment = !selectedDepartment.value || user.department === selectedDepartment.value
        const matchesRole = !selectedRole.value || user.roles?.[0]?.name === selectedRole.value
        const matchesStatus = !selectedStatus.value || user.status === selectedStatus.value

        return matchesSearch && matchesDepartment && matchesRole && matchesStatus
    })
})

const getRoleColor = (role) => {
    const colors = {
        admin: 'bg-red-100 text-red-800',
        manager: 'bg-purple-100 text-purple-800',
        accountant: 'bg-indigo-100 text-indigo-800',
        front_desk: 'bg-blue-100 text-blue-800',
        housekeeping: 'bg-green-100 text-green-800',
        maintenance: 'bg-yellow-100 text-yellow-800',
        bartender: 'bg-orange-100 text-orange-800',
        chef: 'bg-rose-100 text-rose-800',
        server: 'bg-teal-100 text-teal-800',
        bar_staff: 'bg-amber-100 text-amber-800',
        restaurant_staff: 'bg-lime-100 text-lime-800',
        staff: 'bg-gray-100 text-gray-800',
    }
    return colors[role] || 'bg-gray-100 text-gray-800'
}

const getStatusColor = (status) => {
    const colors = {
        active: themeColors.value.success,
        inactive: themeColors.value.danger,
        pending: themeColors.value.warning,
    }
    return colors[status] || colors['pending']
}

const formatRole = (role) => {
    if (!role) return 'No Role'
    return role.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDepartment = (department) => {
    if (!department) return 'No Department'
    return department.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (date) => {
    if (!date) return 'Never'
    return new Date(date).toLocaleDateString()
}

const viewUser = (userId) => {
    router.get(`/manager/users/${userId}`)
}

const editUser = (userId) => {
    router.get(`/manager/users/${userId}/edit`)
}

const deleteUser = (userId) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(`/manager/users/${userId}`, {
            onSuccess: () => {
                // Refresh the page after deletion
                router.reload()
            }
        })
    }
}

const exportUsers = () => {
    showExportDialog()
}

const showExportDialog = () => {
    // Create modal dialog
    const modal = document.createElement('div')
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50'
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6" style="background-color: var(--kotel-card); color: var(--kotel-text-primary);">
            <h3 class="text-lg font-semibold mb-4">Choose Export Format</h3>
            <div class="space-y-3">
                <button onclick="exportData('csv')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div>
                            <div class="font-medium">CSV</div>
                            <div class="text-sm text-gray-500">Excel-compatible spreadsheet format</div>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                <button onclick="exportData('excel')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v6a2 2 0 002 2h2m4-4h.01M17 16h.01"></path>
                        </svg>
                        <div>
                            <div class="font-medium">Excel</div>
                            <div class="text-sm text-gray-500">CSV file that opens in Excel</div>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                <button onclick="exportData('pdf')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <div class="font-medium">PDF</div>
                            <div class="text-sm text-gray-500">Text file for printing</div>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                <button onclick="exportData('word')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div>
                            <div class="font-medium">Word</div>
                            <div class="text-sm text-gray-500">Tab-separated text file</div>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
            <div class="flex gap-3 mt-6">
                <button onclick="closeExportDialog()" class="flex-1 px-4 py-2 border rounded-lg hover:bg-gray-50 transition-colors" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    Cancel
                </button>
            </div>
        </div>
    `
    
    // Add to page
    document.body.appendChild(modal)
    
    // Make functions globally available
    window.exportData = (format) => {
        closeExportDialog()
        performExport(format)
    }
    
    window.closeExportDialog = () => {
        document.body.removeChild(modal)
        delete window.exportData
        delete window.closeExportDialog
    }
    
    // Close on backdrop click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeExportDialog()
        }
    })
}

const performExport = (format) => {
    try {
        // Show loading notification
        showNotification(`Preparing ${format.toUpperCase()} export...`, 'info')
        
        // Use backend export route
        const exportUrl = route('admin.users.export', { format: format })
        
        // Create a temporary link to download the file
        const link = document.createElement('a')
        link.href = exportUrl
        link.style.display = 'none'
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        
        // Show success message
        showNotification(`Users exported as ${format.toUpperCase()} successfully!`, 'success')
    } catch (error) {
        console.error('Export error:', error)
        showNotification(`Failed to export as ${format.toUpperCase()}`, 'error')
    }
}

const generateUsersCSV = () => {
    const headers = ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Employee ID', 'Department', 'Position', 'Status', 'Role', 'Hire Date', 'Created At']
    const csvContent = [
        headers.join(','),
        ...filteredUsers.value.map(user => [
            user.id,
            `"${user.first_name || ''}"`,
            `"${user.last_name || ''}"`,
            `"${user.email}"`,
            `"${user.phone || ''}"`,
            `"${user.employee_id || ''}"`,
            `"${user.department ? user.department.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : ''}"`,
            `"${user.position || ''}"`,
            `"${user.status || ''}"`,
            `"${formatRole(user.roles?.[0]?.name || '')}"`,
            `"${user.hire_date || ''}"`,
            `"${user.created_at ? new Date(user.created_at).toLocaleString() : ''}"`
        ].join(','))
    ].join('\n')
    
    return csvContent
}

const generateExcelContent = () => {
    const headers = ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Employee ID', 'Department', 'Position', 'Status', 'Role', 'Hire Date', 'Created At']
    
    let html = `
    <html>
    <head>
        <meta charset="utf-8">
        <style>
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
        </style>
    </head>
    <body>
        <h2>Users Export - ${new Date().toLocaleDateString()}</h2>
        <table>
            <thead>
                <tr>
                    ${headers.map(header => `<th>${header}</th>`).join('')}
                </tr>
            </thead>
            <tbody>
                ${filteredUsers.value.map(user => `
                    <tr>
                        <td>${user.id}</td>
                        <td>${user.first_name || ''}</td>
                        <td>${user.last_name || ''}</td>
                        <td>${user.email}</td>
                        <td>${user.phone || ''}</td>
                        <td>${user.employee_id || ''}</td>
                        <td>${user.department ? user.department.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : ''}</td>
                        <td>${user.position || ''}</td>
                        <td>${user.status || ''}</td>
                        <td>${formatRole(user.roles?.[0]?.name || '')}</td>
                        <td>${user.hire_date || ''}</td>
                        <td>${user.created_at ? new Date(user.created_at).toLocaleString() : ''}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    </body>
    </html>
    `
    
    return html
}

const generatePDFContent = () => {
    // Simple PDF-like HTML content
    return generateExcelContent().replace('<h2>Users Export', '<h1 style="text-align: center;">Users Export Report')
}

const generateWordContent = () => {
    // Word-compatible HTML content
    return generateExcelContent().replace('<h2>Users Export', '<h1 style="font-family: Arial;">Users Export Report')
}

const showNotification = (message, type = 'info') => {
    // Create notification element
    const notification = document.createElement('div')
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300`
    
    const colors = {
        success: 'bg-green-500 text-white',
        error: 'bg-red-500 text-white',
        info: 'bg-blue-500 text-white'
    }
    
    notification.className += ` ${colors[type] || colors.info}`
    notification.textContent = message
    
    document.body.appendChild(notification)
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0'
        setTimeout(() => {
            document.body.removeChild(notification)
        }, 300)
    }, 3000)
}
</script>
