<template>
    <DashboardLayout title="Roles & Permissions" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="bg-kotel-dark/90 backdrop-blur-xl rounded-xl shadow-xl p-6 mb-8 border border-kotel-yellow/30">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-kotel-yellow">Roles & Permissions</h1>
                    <p class="text-kotel-sky-blue mt-2">Manage user roles and their permissions.</p>
                </div>
                <button @click="addRole" 
                        class="inline-flex items-center px-4 py-2 border border-kotel-yellow/30 text-sm font-medium rounded-md text-kotel-black bg-kotel-yellow hover:bg-kotel-yellow/80 transition-colors">
                    <PlusIcon class="h-4 w-4 mr-2" />
                    Add Role
                </button>
            </div>
        </div>

        <div v-if="actionError" class="bg-red-500/20 border border-red-500/30 rounded-xl p-4 mb-6">
            <p class="text-red-400">{{ actionError }}</p>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-kotel-yellow mx-auto"></div>
            <p class="text-kotel-sky-blue mt-4">Loading roles...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-red-500/20 border border-red-500/30 rounded-xl p-6 mb-8">
            <p class="text-red-400">{{ error }}</p>
        </div>

        <!-- Roles Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="role in roles" :key="role.id" 
                 class="bg-kotel-dark/90 backdrop-blur-xl rounded-xl shadow-xl p-6 border border-kotel-yellow/30">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center">
                        <component :is="getRoleIcon(role.name)" 
                                  :class="getRoleColor(role.name)"
                                  class="h-8 w-8 mr-3" />
                        <div>
                            <h3 class="text-lg font-semibold text-white">{{ formatRoleName(role.name) }}</h3>
                            <p class="text-sm text-kotel-sky-blue">{{ role.users_count || 0 }} users</p>
                        </div>
                    </div>
                    <span :class="getStatusColor(role.is_active ? 'active' : 'inactive')"
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                        {{ formatStatus(role.is_active ? 'active' : 'inactive') }}
                    </span>
                </div>

                <p class="text-kotel-sky-blue/70 text-sm mb-4">{{ role.description }}</p>

                <div class="mb-4">
                    <h4 class="text-sm font-medium text-white mb-2">Key Permissions:</h4>
                    <div class="flex flex-wrap gap-1">
                        <span v-for="permission in (role.key_permissions || []).slice(0, 3)" :key="permission"
                              class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-kotel-yellow/20 text-kotel-yellow">
                            {{ permission }}
                        </span>
                        <span v-if="(role.key_permissions || []).length > 3"
                              class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-kotel-sky-blue/20 text-kotel-sky-blue">
                            +{{ (role.key_permissions || []).length - 3 }} more
                        </span>
                    </div>
                </div>

                <div class="flex space-x-2">
                    <button @click="editRole(role)"
                            class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-kotel-sky-blue/30 text-sm font-medium rounded-md text-kotel-sky-blue bg-kotel-sky-blue/10 hover:bg-kotel-sky-blue/20 transition-colors">
                        <PencilIcon class="h-4 w-4 mr-1" />
                        Edit
                    </button>
                    <button @click="managePermissions(role)"
                            class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-kotel-yellow/30 text-sm font-medium rounded-md text-kotel-yellow bg-kotel-yellow/10 hover:bg-kotel-yellow/20 transition-colors">
                        <CogIcon class="h-4 w-4 mr-1" />
                        Permissions
                    </button>
                    <button @click="deleteRole(role)"
                            class="inline-flex items-center justify-center px-3 py-2 border border-red-500/30 text-sm font-medium rounded-md text-red-400 bg-red-500/10 hover:bg-red-500/20 transition-colors">
                        <TrashIcon class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Add/Edit Role Modal -->
        <div v-if="showAddRoleModal || showEditRoleModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity"
                     :style="{ backgroundColor: 'rgba(0, 0, 0, 0.75)' }"></div>
                <div class="inline-block align-bottom rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                     :style="{ 
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="px-6 py-4"
                         :style="{ borderBottom: `1px solid ${themeColors.border}` }">
                        <h3 class="text-lg font-medium"
                            :style="{ color: themeColors.textPrimary }">
                            {{ showEditRoleModal ? 'Edit Role' : 'Add New Role' }}
                        </h3>
                    </div>
                    <div class="px-6 py-4">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Role Name</label>
                                <input v-model="form.name" type="text" 
                                       class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                       :style="{ 
                                           backgroundColor: themeColors.background,
                                           borderColor: themeColors.border,
                                           color: themeColors.textPrimary,
                                           borderWidth: '1px',
                                           borderStyle: 'solid'
                                       }"
                                       placeholder="e.g., custom_role">
                                <p v-if="formErrors.name" class="text-sm mt-1"
                                   :style="{ color: themeColors.danger }">{{ formErrors.name[0] }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Display Name</label>
                                <input v-model="form.display_name" type="text" 
                                       class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                       :style="{ 
                                           backgroundColor: themeColors.background,
                                           borderColor: themeColors.border,
                                           color: themeColors.textPrimary,
                                           borderWidth: '1px',
                                           borderStyle: 'solid'
                                       }"
                                       placeholder="e.g., Custom Role">
                                <p v-if="formErrors.display_name" class="text-sm mt-1"
                                   :style="{ color: themeColors.danger }">{{ formErrors.display_name[0] }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Description</label>
                                <textarea v-model="form.description" rows="3" 
                                          class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors resize-none"
                                          :style="{ 
                                              backgroundColor: themeColors.background,
                                              borderColor: themeColors.border,
                                              color: themeColors.textPrimary,
                                              borderWidth: '1px',
                                              borderStyle: 'solid'
                                          }"
                                          placeholder="Role description..."></textarea>
                                <p v-if="formErrors.description" class="text-sm mt-1"
                                   :style="{ color: themeColors.danger }">{{ formErrors.description[0] }}</p>
                            </div>
                            <div class="flex items-center">
                                <input v-model="form.is_active" type="checkbox" 
                                       class="h-4 w-4 rounded focus:outline-none transition-colors"
                                       :style="{ 
                                           accentColor: themeColors.primary,
                                           borderColor: themeColors.border
                                       }">
                                <label class="ml-2 block text-sm"
                                       :style="{ color: themeColors.textSecondary }">Role is active</label>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 flex justify-end space-x-3"
                     :style="{ borderTop: `1px solid ${themeColors.border}` }">
                        <button @click="cancelOperation" type="button"
                                class="px-4 py-2 rounded-md text-sm font-medium transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.secondary,
                                    color: themeColors.textPrimary 
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                            Cancel
                        </button>
                        <button @click="saveRole" type="button"
                                class="px-4 py-2 rounded-md text-sm font-medium transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.primary,
                                    color: 'white' 
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                            {{ showEditRoleModal ? 'Update Role' : 'Create Role' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Permissions Modal -->
        <div v-if="showPermissionsModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity"
                     :style="{ backgroundColor: 'rgba(0, 0, 0, 0.75)' }"></div>
                <div class="inline-block align-bottom rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
                     :style="{ 
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="px-6 py-4"
                     :style="{ borderBottom: `1px solid ${themeColors.border}` }">
                        <h3 class="text-lg font-medium"
                            :style="{ color: themeColors.textPrimary }">
                            Manage Permissions - {{ formatRoleName(currentRole?.name) }}
                        </h3>
                    </div>
                    <div class="px-6 py-4 max-h-96 overflow-y-auto">
                        <div v-for="(categoryPerms, category) in permissions" :key="category" class="mb-6">
                            <h4 class="text-sm font-medium mb-3 capitalize"
                                :style="{ color: themeColors.warning }">{{ category.replace('_', ' ') }}</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <label v-for="(perm, permName) in categoryPerms" :key="permName" 
                                       class="flex items-center p-3 rounded-lg border transition-colors cursor-pointer"
                                       :style="{ 
                                           backgroundColor: themeColors.background,
                                           borderColor: themeColors.border
                                       }"
                                       @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                       @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                                    <input v-model="perm.has_permission" type="checkbox" 
                                           class="h-4 w-4 rounded focus:outline-none transition-colors"
                                           :style="{ 
                                               accentColor: themeColors.primary,
                                               borderColor: themeColors.border
                                           }">
                                    <div class="ml-3">
                                        <div class="text-sm font-medium"
                                             :style="{ color: themeColors.textPrimary }">{{ perm.display_name }}</div>
                                        <div class="text-xs"
                                             :style="{ color: themeColors.textSecondary }">{{ perm.description }}</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 flex justify-end space-x-3"
                     :style="{ borderTop: `1px solid ${themeColors.border}` }">
                        <button @click="cancelOperation" type="button"
                                class="px-4 py-2 rounded-md text-sm font-medium transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.secondary,
                                    color: themeColors.textPrimary 
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                            Cancel
                        </button>
                        <button @click="updateRolePermissions" type="button"
                                class="px-4 py-2 rounded-md text-sm font-medium transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.primary,
                                    color: 'white' 
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                            Update Permissions
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteConfirm" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity"
                     :style="{ backgroundColor: 'rgba(0, 0, 0, 0.75)' }"></div>
                <div class="inline-block align-bottom rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                     :style="{ 
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.danger,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full"
                                 :style="{ backgroundColor: themeColors.danger + '20' }">
                                <TrashIcon class="h-6 w-6" :style="{ color: themeColors.danger }" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg font-medium"
                                    :style="{ color: themeColors.textPrimary }">Delete Role</h3>
                                <div class="mt-2">
                                    <p class="text-sm"
                                       :style="{ color: themeColors.textSecondary }">
                                        Are you sure you want to delete the role <strong>{{ formatRoleName(currentRole?.name) }}</strong>?
                                        This action cannot be undone.
                                    </p>
                                    <div v-if="error" class="mt-4 bg-red-500/20 border border-red-500/30 rounded p-3">
                                        <p class="text-sm text-red-400">{{ error }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 flex justify-end space-x-3"
                     :style="{ borderTop: `1px solid ${themeColors.border}` }">
                        <button @click="cancelOperation" type="button"
                                class="px-4 py-2 rounded-md text-sm font-medium transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.secondary,
                                    color: themeColors.textPrimary 
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                            Cancel
                        </button>
                        <button @click="confirmDelete" type="button"
                                class="px-4 py-2 rounded-md text-sm font-medium transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.danger,
                                    color: 'white' 
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.danger">
                            Delete Role
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme.js'
import { usePage } from '@inertiajs/vue3'
import { 
    PlusIcon, 
    ShieldCheckIcon, 
    UserIcon, 
    HomeIcon, 
    SparklesIcon, 
    WrenchScrewdriverIcon, 
    CurrencyDollarIcon,
    PencilIcon,
    TrashIcon,
    XMarkIcon,
    CheckIcon
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
})

const page = usePage()
const navigation = computed(() => getNavigationForRole(page.props.auth.permissions || []))

// State
const roles = ref([])
const isLoading = ref(true)
const error = ref(null)
const actionError = ref('')

// Modals state
const showAddRoleModal = ref(false)
const showEditRoleModal = ref(false)
const showPermissionsModal = ref(false)
const showDeleteConfirm = ref(false)

const currentRole = ref(null)
const permissions = ref({})
const allPermissions = ref({})

// Form state
const form = ref({
    name: '',
    display_name: '',
    description: '',
    permissions: [],
    is_active: true
})

const formErrors = ref({})

// Fetch roles on component mount
onMounted(() => {
    fetchRoles()
})

const fetchRoles = async () => {
    try {
        isLoading.value = true
        error.value = null

        const response = await axios.get('/admin/roles/api')

        if (response.data && response.data.length > 0) {
            roles.value = response.data.map(role => {
                const rolePermissions = Array.isArray(role.permissions) ? role.permissions : []
                const keyPermissions = rolePermissions.slice(0, 3).map(permission => {
                    return permission.display_name || permission.name || permission
                })

                return {
                    id: role.id,
                    name: role.name,
                    display_name: role.display_name || formatRoleName(role.name),
                    description: role.description || 'No description provided',
                    users_count: role.user_count || 0,
                    is_active: role.is_active ?? true,
                    key_permissions: keyPermissions,
                    permissions: rolePermissions
                }
            })
        } else {
            roles.value = []
        }
    } catch (err) {
        console.error('Failed to fetch roles:', err)
        error.value = 'Failed to load roles. Please try again.'
        roles.value = []
    } finally {
        isLoading.value = false
    }
}

const fetchPermissions = async () => {
    try {
        const response = await axios.get('/admin/roles/permissions/all')
        if (response.data && response.data.permissions) {
            allPermissions.value = response.data.permissions
        } else {
            allPermissions.value = {}
        }
    } catch (err) {
        console.error('Failed to fetch permissions:', err)
        allPermissions.value = {}
    }
}

const getRoleIcon = (roleName) => {
    const icons = {
        admin: ShieldCheckIcon,
        manager: UserIcon,
        front_desk: HomeIcon,
        housekeeping: SparklesIcon,
        maintenance: WrenchScrewdriverIcon,
        accountant: CurrencyDollarIcon,
        staff: UserIcon
    }
    return icons[roleName] || UserIcon
}

const getRoleColor = (roleName) => {
    const colors = {
        admin: 'text-kotel-yellow',
        manager: 'text-kotel-sky-blue',
        front_desk: 'text-kotel-yellow',
        housekeeping: 'text-kotel-sky-blue',
        maintenance: 'text-kotel-yellow',
        accountant: 'text-kotel-sky-blue',
        staff: 'text-white'
    }
    return colors[roleName] || 'text-kotel-sky-blue'
}

const getStatusColor = (status) => {
    const colors = {
        active: 'bg-green-100 text-green-800',
        inactive: 'bg-red-100 text-red-800'
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const formatRoleName = (name) => {
    return name.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1)
}

const addRole = () => {
    form.value = {
        name: '',
        display_name: '',
        description: '',
        permissions: [],
        is_active: true
    }
    formErrors.value = {}
    showAddRoleModal.value = true
    fetchPermissions()
}

const editRole = async (role) => {
    currentRole.value = role
    form.value = {
        name: role.name,
        display_name: role.display_name,
        description: role.description,
        permissions: Array.isArray(role.permissions)
            ? role.permissions.map(permission => permission.name || permission)
            : [],
        is_active: role.is_active
    }
    formErrors.value = {}
    showEditRoleModal.value = true
    fetchPermissions()
}

const managePermissions = async (role) => {
    currentRole.value = role
    await fetchPermissions()

    try {
        const response = await axios.get(`/admin/roles/${role.id}/permissions`)
        if (response.data && response.data.permissions) {
            // Ensure the permissions data is reactive by creating a new object
            permissions.value = JSON.parse(JSON.stringify(response.data.permissions))
            console.log('Loaded permissions for role:', role.name, permissions.value)
        } else {
            permissions.value = JSON.parse(JSON.stringify(allPermissions.value)) || {}
        }
    } catch (err) {
        console.error('Failed to fetch role permissions:', err)
        permissions.value = JSON.parse(JSON.stringify(allPermissions.value)) || {}
    }

    showPermissionsModal.value = true
}

const saveRole = async () => {
    formErrors.value = {}
    try {
        if (showEditRoleModal.value && currentRole.value) {
            await axios.put(`/admin/roles/${currentRole.value.id}`, form.value)
        } else {
            await axios.post('/admin/roles', form.value)
        }
        await fetchRoles()
        showAddRoleModal.value = false
        showEditRoleModal.value = false
        actionError.value = ''
    } catch (err) {
        if (err.response?.data?.errors) {
            formErrors.value = err.response.data.errors
        } else {
            actionError.value = 'Failed to save role. Please try again.'
        }
    }
}

const deleteRole = async (role) => {
    currentRole.value = role
    showDeleteConfirm.value = true
}

const confirmDelete = async () => {
    if (!currentRole.value) return
    try {
        await axios.delete(`/admin/roles/${currentRole.value.id}`)
        await fetchRoles()
        showDeleteConfirm.value = false
        actionError.value = ''
    } catch (err) {
        console.error('Failed to delete role:', err)
        actionError.value = err.response?.data?.message || 'Failed to delete role.'
    }
}

const updateRolePermissions = async () => {
    // Collect selected permissions
    const selectedPermissions = []
    for (const category in permissions.value) {
        for (const permName in permissions.value[category]) {
            if (permissions.value[category][permName].has_permission) {
                selectedPermissions.push(permName)
            }
        }
    }
    
    if (!currentRole.value) return

    try {
        await axios.put(`/admin/roles/${currentRole.value.id}/permissions`, {
            permissions: selectedPermissions
        })
        await fetchRoles()
        showPermissionsModal.value = false
        actionError.value = ''
    } catch (err) {
        console.error('Failed to update permissions:', err)
        actionError.value = 'Failed to update permissions. Please try again.'
    }
}

const cancelOperation = () => {
    showAddRoleModal.value = false
    showEditRoleModal.value = false
    showPermissionsModal.value = false
    showDeleteConfirm.value = false
    formErrors.value = {}
}
</script>
