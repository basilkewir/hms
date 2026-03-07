<template>
    <DashboardLayout title="Create New User">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Create New User</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Add a new staff member to the hotel management system.</p>
                </div>
                <Link href="/manager/users"
                      class="px-4 py-2 rounded-md transition-colors text-sm font-medium"
                      :style="{ 
                          backgroundColor: themeColors.secondary,
                          color: themeColors.textPrimary 
                      }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                    <ArrowLeftIcon class="h-4 w-4 mr-2 inline" />
                    Back to Users
                </Link>
            </div>
        </div>

        <!-- User Creation Form -->
        <form @submit.prevent="createUser" class="space-y-6">
            <!-- Personal Information -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h2 class="text-xl font-bold mb-4 pb-2 border-b"
                    :style="{ 
                        color: themeColors.textPrimary,
                        borderColor: themeColors.border 
                    }">
                    <UserIcon class="h-5 w-5 mr-2 inline" />
                    Personal Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">First Name *</label>
                        <input type="text" v-model="form.first_name" required
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
                               :style="{ color: themeColors.textSecondary }">Last Name *</label>
                        <input type="text" v-model="form.last_name" required
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
                               :style="{ color: themeColors.textSecondary }">Email Address *</label>
                        <input type="email" v-model="form.email" required
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
                               :style="{ color: themeColors.textSecondary }">Phone Number</label>
                        <input type="tel" v-model="form.phone"
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
                               :style="{ color: themeColors.textSecondary }">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Date of Birth
                        </label>
                        <div class="relative">
                            <input type="date" v-model="form.date_of_birth" placeholder="Select date of birth"
                                   class="w-full rounded-md pl-10 pr-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   @keydown.prevent
                                   @click="$event.target.showPicker && $event.target.showPicker()">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                     :style="{ color: themeColors.textTertiary }">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Gender</label>
                        <select v-model="form.gender"
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Employment Information -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h2 class="text-xl font-bold mb-4 pb-2 border-b"
                    :style="{ 
                        color: themeColors.textPrimary,
                        borderColor: themeColors.border 
                    }">
                    <BriefcaseIcon class="h-5 w-5 mr-2 inline" />
                    Employment Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Employee ID</label>
                        <input type="text" v-model="form.employee_id"
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
                               :style="{ color: themeColors.textSecondary }">Department *</label>
                        <select v-model="form.department_id" @change="onDepartmentChange" required
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }"
                                :disabled="departments.length === 0">
                            <option value="">Select Department</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                {{ dept.name }}
                            </option>
                        </select>
                        <p v-if="departments.length === 0" 
                           class="text-sm mt-1"
                           :style="{ color: themeColors.textTertiary }">No departments available. Please create departments first.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Position *</label>
                        <select v-model="form.position_id" @change="onPositionChange" required
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }"
                                :disabled="!form.department_id || isLoadingPositions || positions.length === 0">
                            <option value="">Select Position</option>
                            <option v-for="pos in positions" :key="pos.id" :value="pos.id">
                                {{ pos.name }}
                            </option>
                        </select>
                        <p v-if="isLoadingPositions" 
                           class="text-sm mt-1"
                           :style="{ color: themeColors.textTertiary }">Loading positions...</p>
                        <p v-else-if="form.department_id && positions.length === 0" 
                           class="text-sm mt-1"
                           :style="{ color: themeColors.textTertiary }">No positions available for this department.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Hire Date *
                        </label>
                        <div class="relative">
                            <input type="date" v-model="form.hire_date" placeholder="Select hire date" required
                                   class="w-full rounded-md pl-10 pr-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   @keydown.prevent
                                   @click="$event.target.showPicker && $event.target.showPicker()">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                     :style="{ color: themeColors.textTertiary }">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Pay Type *</label>
                        <select v-model="form.pay_type" required
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="">Select Pay Type</option>
                            <option value="hourly">Hourly</option>
                            <option value="monthly">Monthly</option>
                            <option value="salary">Annual Salary</option>
                        </select>
                    </div>
                    <div v-if="form.pay_type === 'hourly'">
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Hourly Rate ({{ currencySymbol }})</label>
                        <input type="number" step="0.01" v-model="form.hourly_rate" :placeholder="'e.g. ' + currencySymbol + '15.00'"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                    </div>
                    <div v-if="form.pay_type === 'monthly'">
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Monthly Rate ({{ currencySymbol }})</label>
                        <input type="number" step="0.01" v-model="form.monthly_rate" :placeholder="'e.g. ' + currencySymbol + '3000.00'"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                    </div>
                    <div v-if="form.pay_type === 'salary'">
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Annual Salary ({{ currencySymbol }})</label>
                        <input type="number" step="0.01" v-model="form.salary" :placeholder="'e.g. ' + currencySymbol + '45000.00'"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                    </div>
                </div>
            </div>

            <!-- Role Assignment -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h2 class="text-xl font-bold mb-4 pb-2 border-b"
                    :style="{ 
                        color: themeColors.textPrimary,
                        borderColor: themeColors.border 
                    }">
                    <ShieldCheckIcon class="h-5 w-5 mr-2 inline" />
                    Role Assignment
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Primary Role *</label>
                        <select v-model="form.role_id" required
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }"
                                :disabled="isLoadingRoles">
                            <option value="">Select Role</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">
                                {{ role.display_name || role.name }}
                            </option>
                        </select>
                        <p v-if="isLoadingRoles" 
                           class="text-sm mt-1"
                           :style="{ color: themeColors.textTertiary }">Loading roles...</p>
                        <p v-else-if="roles.length === 0" 
                           class="text-sm mt-1"
                           :style="{ color: themeColors.textTertiary }">No roles available.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Employment Status</label>
                        <select v-model="form.employment_status"
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="probation">Probation</option>
                            <option value="terminated">Terminated</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h2 class="text-xl font-bold mb-4 pb-2 border-b"
                    :style="{ 
                        color: themeColors.textPrimary,
                        borderColor: themeColors.border 
                    }">
                    <LockClosedIcon class="h-5 w-5 mr-2 inline" />
                    Security Settings
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Temporary Password *</label>
                        <input type="password" v-model="form.password" required
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
                               :style="{ color: themeColors.textSecondary }">Confirm Password *</label>
                        <input type="password" v-model="form.password_confirmation" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" v-model="form.force_password_change"
                               class="h-4 w-4 rounded focus:outline-none transition-colors"
                               :style="{ 
                                   accentColor: themeColors.primary,
                                   borderColor: themeColors.border
                               }">
                        <label class="ml-2 block text-sm"
                               :style="{ color: themeColors.textSecondary }">Force password change on first login</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" v-model="form.is_active"
                               class="h-4 w-4 rounded focus:outline-none transition-colors"
                               :style="{ 
                                   accentColor: themeColors.primary,
                                   borderColor: themeColors.border
                               }">
                        <label class="ml-2 block text-sm"
                               :style="{ color: themeColors.textSecondary }">Account is active</label>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <div class="flex items-center justify-end space-x-3">
                    <button type="button" @click="resetForm"
                            class="px-6 py-2 rounded-md transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.secondary,
                                color: themeColors.textPrimary 
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        Reset Form
                    </button>
                    <button type="submit" :disabled="isSubmitting"
                            class="px-6 py-2 rounded-md transition-colors disabled:opacity-50"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                                color: 'white' 
                            }"
                            @mouseenter="!isSubmitting && ($event.target.style.backgroundColor = themeColors.hover)"
                            @mouseleave="!isSubmitting && ($event.target.style.backgroundColor = themeColors.primary)">
                        <span v-if="isSubmitting">Creating...</span>
                        <span v-else>Create User</span>
                    </button>
                </div>
            </div>
        </form>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { ArrowLeftIcon, UserIcon, BriefcaseIcon, ShieldCheckIcon, LockClosedIcon } from '@heroicons/vue/24/outline'
import { getCurrencySymbol } from '@/Utils/currency.js'

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
    settings: {
        type: Object,
        default: () => ({
            currency: 'USD'
        })
    },
    departments: {
        type: Array,
        default: () => []
    }
})

const isSubmitting = ref(false)
const isLoadingPositions = ref(false)
const isLoadingRoles = ref(false)
const positions = ref([])
const roles = ref([])

// Get currency symbol from settings
const currencySymbol = computed(() => {
    const currency = props.settings?.currency || 'USD'
    return getCurrencySymbol(currency)
})

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    date_of_birth: '',
    gender: '',
    employee_id: '',
    department_id: null,
    position_id: null,
    hire_date: '',
    pay_type: '',
    hourly_rate: '',
    monthly_rate: '',
    salary: '',
    role_id: null,
    employment_status: 'active',
    password: '',
    password_confirmation: '',
    force_password_change: true,
    is_active: true,
})

const onDepartmentChange = async () => {
    if (!form.department_id) {
        positions.value = []
        return
    }
    
    isLoadingPositions.value = true
    try {
        // Mock positions loading - replace with actual API call
        await new Promise(resolve => setTimeout(resolve, 500))
        positions.value = [
            { id: 1, name: 'Manager' },
            { id: 2, name: 'Staff' },
            { id: 3, name: 'Supervisor' }
        ]
    } catch (error) {
        console.error('Failed to load positions:', error)
    } finally {
        isLoadingPositions.value = false
    }
}

const onPositionChange = async () => {
    // Position change no longer affects roles loading
    // Roles are loaded independently on component mount
}

// Load roles on component mount
const loadRoles = async () => {
    isLoadingRoles.value = true
    try {
        // Mock roles loading - replace with actual API call
        await new Promise(resolve => setTimeout(resolve, 500))
        roles.value = [
            { id: 1, name: 'admin', display_name: 'Administrator' },
            { id: 2, name: 'staff', display_name: 'Staff' },
            { id: 3, name: 'manager', display_name: 'Manager' },
            { id: 4, name: 'front_desk', display_name: 'Front Desk' },
            { id: 5, name: 'housekeeping', display_name: 'Housekeeping' },
            { id: 6, name: 'maintenance', display_name: 'Maintenance' },
            { id: 7, name: 'accountant', display_name: 'Accountant' }
        ]
    } catch (error) {
        console.error('Failed to load roles:', error)
    } finally {
        isLoadingRoles.value = false
    }
}

const resetForm = () => {
    form.reset()
}

const createUser = () => {
    isSubmitting.value = true
    form.post('/manager/users', {
        onSuccess: () => {
            isSubmitting.value = false
        },
        onError: () => {
            isSubmitting.value = false
        }
    })
}

// Load initial data on component mount
onMounted(() => {
    loadRoles()
})
</script>
