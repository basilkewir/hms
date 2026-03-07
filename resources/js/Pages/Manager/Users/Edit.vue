<template>
    <DashboardLayout title="Edit User" :user="authUser" :navigation="navigation">
        <div style="box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 2rem;"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                <div>
                    <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem;"
                        :style="{ color: themeColors.textPrimary }">Edit User</h1>
                    <p style="margin-top: 0.5rem;"
                       :style="{ color: themeColors.textSecondary }">Update staff member information and account settings.</p>
                </div>
                <div style="display: flex; gap: 0.75rem;">
                    <Link :href="`/admin/users/${user.id}`"
                          style="padding: 0.5rem 1rem; border-radius: 0.375rem; transition: all 0.2s ease; font-size: 0.875rem; font-weight: 500; text-decoration: none;"
                          :style="{ 
                              backgroundColor: themeColors.secondary,
                              color: themeColors.textPrimary 
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        <ArrowLeftIcon style="height: 1rem; width: 1rem; margin-right: 0.5rem; display: inline;" />
                        View User
                    </Link>
                    <Link href="/admin/users"
                          style="padding: 0.5rem 1rem; border-radius: 0.375rem; transition: all 0.2s ease; font-size: 0.875rem; font-weight: 500; text-decoration: none;"
                          :style="{ 
                              backgroundColor: themeColors.secondary,
                              color: themeColors.textPrimary 
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        <ArrowLeftIcon style="height: 1rem; width: 1rem; margin-right: 0.5rem; display: inline;" />
                        Back to Users
                    </Link>
                </div>
            </div>

            <form @submit.prevent="updateUser" style="display: flex; flex-direction: column; gap: 1.5rem;">
                <!-- Personal Information -->
                <div>
                    <h3 style="font-size: 1.125rem; font-weight: 500; margin-bottom: 1rem;"
                        :style="{ color: themeColors.textPrimary }">Personal Information</h3>
                    <div style="display: grid; grid-template-columns: repeat(1, 1fr); gap: 1.5rem;"
                         :style="{ '@media (min-width: 768px)': { gridTemplateColumns: 'repeat(2, 1fr)' } }">
                        <div>
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;"
                                   :style="{ color: themeColors.textSecondary }">First Name *</label>
                            <input type="text" v-model="form.first_name" required
                                   style="width: 100%; border-radius: 0.375rem; padding: 0.5rem 0.75rem; outline: none; transition: all 0.2s ease; border: 1px solid;"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;"
                                   :style="{ color: themeColors.textSecondary }">Last Name *</label>
                            <input type="text" v-model="form.last_name" required
                                   style="width: 100%; border-radius: 0.375rem; padding: 0.5rem 0.75rem; outline: none; transition: all 0.2s ease; border: 1px solid;"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;"
                                   :style="{ color: themeColors.textSecondary }">Email Address *</label>
                            <input type="email" v-model="form.email" required
                                   style="width: 100%; border-radius: 0.375rem; padding: 0.5rem 0.75rem; outline: none; transition: all 0.2s ease; border: 1px solid;"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;"
                                   :style="{ color: themeColors.textSecondary }">Phone Number</label>
                            <input type="tel" v-model="form.phone"
                                   style="width: 100%; border-radius: 0.375rem; padding: 0.5rem 0.75rem; outline: none; transition: all 0.2s ease; border: 1px solid;"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;"
                                   :style="{ color: themeColors.textSecondary }">Date of Birth</label>
                            <input type="date" v-model="form.date_of_birth" placeholder="Select date of birth" inputmode="none"
                                   @keydown.prevent @keypress.prevent @paste.prevent @focus="tryShowPicker($event)" @click="tryShowPicker($event)"
                                   style="width: 100%; border-radius: 0.375rem; padding: 0.5rem 0.75rem; outline: none; transition: all 0.2s ease; border: 1px solid;"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;"
                                   :style="{ color: themeColors.textSecondary }">Gender</label>
                            <select v-model="form.gender"
                                    style="width: 100%; border-radius: 0.375rem; padding: 0.5rem 0.75rem; outline: none; transition: all 0.2s ease; border: 1px solid;"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary
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
                <div>
                    <h3 style="font-size: 1.125rem; font-weight: 500; margin-bottom: 1rem;"
                        :style="{ color: themeColors.textPrimary }">Employment Information</h3>
                    <div style="display: grid; grid-template-columns: repeat(1, 1fr); gap: 1.5rem;"
                         :style="{ '@media (min-width: 768px)': { gridTemplateColumns: 'repeat(2, 1fr)' } }">
                        <div>
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;"
                                   :style="{ color: themeColors.textSecondary }">Employee ID</label>
                            <input type="text" v-model="form.employee_id"
                                   style="width: 100%; border-radius: 0.375rem; padding: 0.5rem 0.75rem; outline: none; transition: all 0.2s ease; border: 1px solid;"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;"
                                   :style="{ color: themeColors.textSecondary }">Department *</label>
                            <select v-model="form.department" required
                                    style="width: 100%; border-radius: 0.375rem; padding: 0.5rem 0.75rem; outline: none; transition: all 0.2s ease; border: 1px solid;"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary
                                    }">
                                <option value="">Select Department</option>
                                <option value="front_desk">Front Desk</option>
                                <option value="housekeeping">Housekeeping</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="management">Management</option>
                                <option value="accounting">Accounting</option>
                                <option value="security">Security</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;"
                                   :style="{ color: themeColors.textSecondary }">Position *</label>
                            <input type="text" v-model="form.position" required
                                   style="width: 100%; border-radius: 0.375rem; padding: 0.5rem 0.75rem; outline: none; transition: all 0.2s ease; border: 1px solid;"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;"
                                   :style="{ color: themeColors.textSecondary }">Hire Date *</label>
                            <input type="date" v-model="form.hire_date" placeholder="Select hire date" required inputmode="none"
                                   @keydown.prevent @keypress.prevent @paste.prevent @focus="tryShowPicker($event)" @click="tryShowPicker($event)"
                                   style="width: 100%; border-radius: 0.375rem; padding: 0.5rem 0.75rem; outline: none; transition: all 0.2s ease; border: 1px solid;"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;"
                                   :style="{ color: themeColors.textSecondary }">Pay Type</label>
                            <select v-model="form.pay_type"
                                    style="width: 100%; border-radius: 0.375rem; padding: 0.5rem 0.75rem; outline: none; transition: all 0.2s ease; border: 1px solid;"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary
                                    }">
                                <option value="hourly">Hourly</option>
                                <option value="monthly">Monthly</option>
                                <option value="salary">Annual Salary</option>
                            </select>
                        </div>
                        <div v-if="form.pay_type === 'hourly'">
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;"
                                   :style="{ color: themeColors.textSecondary }">Hourly Rate ({{ currencySymbol }})</label>
                            <input type="number" step="0.01" v-model="form.hourly_rate" :placeholder="'e.g. ' + currencySymbol + '15.00'"
                                   style="width: 100%; border-radius: 0.375rem; padding: 0.5rem 0.75rem; outline: none; transition: all 0.2s ease; border: 1px solid;"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }">
                        </div>
                        <div v-if="form.pay_type === 'monthly'">
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;"
                                   :style="{ color: themeColors.textSecondary }">Monthly Rate ({{ currencySymbol }})</label>
                            <input type="number" step="0.01" v-model="form.monthly_rate" :placeholder="'e.g. ' + currencySymbol + '3000.00'"
                                   style="width: 100%; border-radius: 0.375rem; padding: 0.5rem 0.75rem; outline: none; transition: all 0.2s ease; border: 1px solid;"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }">
                        </div>
                        <div v-if="form.pay_type === 'salary'">
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;"
                                   :style="{ color: themeColors.textSecondary }">Annual Salary ({{ currencySymbol }})</label>
                            <input type="number" step="0.01" v-model="form.salary" :placeholder="'e.g. ' + currencySymbol + '45000.00'"
                                   style="width: 100%; border-radius: 0.375rem; padding: 0.5rem 0.75rem; outline: none; transition: all 0.2s ease; border: 1px solid;"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }">
                        </div>
                    </div>
                </div>

                <!-- Role Assignment -->
                <div>
                    <h3 style="font-size: 1.125rem; font-weight: 500; margin-bottom: 1rem;"
                        :style="{ color: themeColors.textPrimary }">Role Assignment</h3>
                    <div style="display: grid; grid-template-columns: repeat(1, 1fr); gap: 1.5rem;"
                         :style="{ '@media (min-width: 768px)': { gridTemplateColumns: 'repeat(2, 1fr)' } }">
                        <div>
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;"
                                   :style="{ color: themeColors.textSecondary }">Primary Role *</label>
                            <select v-model="form.role" required
                                    style="width: 100%; border-radius: 0.375rem; padding: 0.5rem 0.75rem; outline: none; transition: all 0.2s ease; border: 1px solid;"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary
                                    }">
                                <option value="">Select Role</option>
                                <option v-for="role in roles" :key="role.id" :value="role.name">
                                    {{ formatRole(role.name) }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;"
                                   :style="{ color: themeColors.textSecondary }">Employment Status</label>
                            <select v-model="form.employment_status"
                                    style="width: 100%; border-radius: 0.375rem; padding: 0.5rem 0.75rem; outline: none; transition: all 0.2s ease; border: 1px solid;"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary
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
                <div>
                    <h3 style="font-size: 1.125rem; font-weight: 500; margin-bottom: 1rem;"
                        :style="{ color: themeColors.textPrimary }">Security Settings</h3>
                    <div style="display: grid; grid-template-columns: repeat(1, 1fr); gap: 1.5rem;"
                         :style="{ '@media (min-width: 768px)': { gridTemplateColumns: 'repeat(2, 1fr)' } }">
                        <div>
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;"
                                   :style="{ color: themeColors.textSecondary }">New Password (leave blank to keep current)</label>
                            <input type="password" v-model="form.password"
                                   style="width: 100%; border-radius: 0.375rem; padding: 0.5rem 0.75rem; outline: none; transition: all 0.2s ease; border: 1px solid;"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;"
                                   :style="{ color: themeColors.textSecondary }">Confirm New Password</label>
                            <input type="password" v-model="form.password_confirmation"
                                   style="width: 100%; border-radius: 0.375rem; padding: 0.5rem 0.75rem; outline: none; transition: all 0.2s ease; border: 1px solid;"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary
                                   }">
                        </div>
                        <div style="display: flex; align-items: center;">
                            <input type="checkbox" v-model="form.force_password_change"
                                   style="height: 1rem; width: 1rem; border-radius: 0.25rem; outline: none; transition: all 0.2s ease; margin-right: 0.5rem;"
                                   :style="{ 
                                       accentColor: themeColors.primary,
                                       borderColor: themeColors.border
                                   }">
                            <label style="display: block; font-size: 0.875rem;"
                                   :style="{ color: themeColors.textSecondary }">Force password change on next login</label>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <input type="checkbox" v-model="form.is_active"
                                   style="height: 1rem; width: 1rem; border-radius: 0.25rem; outline: none; transition: all 0.2s ease; margin-right: 0.5rem;"
                                   :style="{ 
                                       accentColor: themeColors.primary,
                                       borderColor: themeColors.border
                                   }">
                            <label style="display: block; font-size: 0.875rem;"
                                   :style="{ color: themeColors.textSecondary }">Account is active</label>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 1rem; padding-top: 1.5rem; border-top: 1px solid;"
                     :style="{ borderColor: themeColors.border }">
                    <Link href="/admin/users"
                          style="padding: 0.75rem 1.5rem; border-radius: 0.375rem; transition: all 0.2s ease; text-decoration: none;"
                          :style="{ 
                              backgroundColor: themeColors.secondary,
                              color: themeColors.textPrimary 
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="isSubmitting"
                            style="padding: 0.75rem 1.5rem; border-radius: 0.375rem; transition: all 0.2s ease; border: none; cursor: pointer;"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                                color: 'white',
                                opacity: isSubmitting ? '0.5' : '1'
                            }"
                            @mouseenter="!isSubmitting && ($event.target.style.backgroundColor = themeColors.hover)"
                            @mouseleave="!isSubmitting && ($event.target.style.backgroundColor = themeColors.primary)">
                        <span v-if="isSubmitting">Updating...</span>
                        <span v-else>Update User</span>
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import { getCurrencySymbol } from '@/Utils/currency.js'
import { getNavigationForRole } from '@/Utils/navigation.js'

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
    authUser: Object,
    currentUser: Object,
    settings: {
        type: Object,
        default: () => ({
            currency: 'XAF',
            currency_symbol: 'FCFA'
        })
    },
    roles: {
        type: Array,
        default: () => []
    },
    errors: Object
})

const isSubmitting = ref(false)

// Show native date picker if supported
const tryShowPicker = (e) => {
    const el = e?.target
    if (el && typeof el.showPicker === 'function') {
        try { el.showPicker() } catch (_) { /* no-op */ }
    }
}

// Get currency symbol from settings
const currencySymbol = computed(() => {
    const currency = props.settings?.currency || 'USD'
    return getCurrencySymbol(currency)
})

// Navigation for DashboardLayout
const navigation = computed(() => {
    const role = props.authUser?.roles?.[0]?.name || 'admin'
    return getNavigationForRole(role)
})

// Format role name for display
const formatRole = (role) => {
    return role.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

// Initialize form with user data
const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    date_of_birth: '',
    gender: '',
    employee_id: '',
    department: '',
    position: '',
    hire_date: '',
    pay_type: 'hourly',
    hourly_rate: '',
    monthly_rate: '',
    salary: '',
    role: '',
    employment_status: 'active',
    password: '',
    password_confirmation: '',
    force_password_change: true,
    is_active: true,
})

// Helper: format any date-like value to YYYY-MM-DD for input[type=date]
const formatToInputDate = (value) => {
    if (!value) return ''
    // Already in YYYY-MM-DD
    if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(value)) return value
    const d = new Date(value)
    if (isNaN(d.getTime())) return ''
    const yyyy = d.getFullYear()
    const mm = String(d.getMonth() + 1).padStart(2, '0')
    const dd = String(d.getDate()).padStart(2, '0')
    return `${yyyy}-${mm}-${dd}`
}

// Populate form with user data when component mounts
onMounted(() => {
    if (props.user) {
        // Prefer explicit first_name/last_name from DB; fallback to splitting name
        if (props.user.first_name || props.user.last_name) {
            form.first_name = props.user.first_name || ''
            form.last_name = props.user.last_name || ''
        } else {
            const parts = (props.user.name || '').trim().split(/\s+/)
            form.first_name = parts.shift() || ''
            form.last_name = parts.join(' ')
        }
        form.email = props.user.email || ''
        form.phone = props.user.phone || ''
        const dobRaw = props.user.date_of_birth || props.user.dob || props.user.birth_date || props.user.birthday
        form.date_of_birth = formatToInputDate(dobRaw)
        form.gender = props.user.gender || ''
        form.employee_id = props.user.employee_id || ''
        form.department = props.user.department || ''
        form.position = props.user.position || ''
        form.hire_date = formatToInputDate(props.user.hire_date)
        form.pay_type = props.user.pay_type || 'hourly'
        form.hourly_rate = props.user.hourly_rate || ''
        form.monthly_rate = props.user.monthly_rate || ''
        form.salary = props.user.salary || ''
        form.role = props.user.roles?.[0]?.name || ''
        form.employment_status = props.user.status || 'active'
        form.force_password_change = !!props.user.force_password_change
        form.is_active = props.user.is_active ?? true
    }
})

const updateUser = () => {
    isSubmitting.value = true
    form.transform((data) => ({
        // Backend expects 'name', 'email', 'is_active'; extra fields are ignored by update() validation
        name: `${(data.first_name || '').trim()} ${(data.last_name || '').trim()}`.trim(),
        email: data.email,
        is_active: !!data.is_active,
    })).put(`/admin/users/${props.user.id}`, {
        onFinish: () => {
            isSubmitting.value = false
        }
    })
}
</script>
