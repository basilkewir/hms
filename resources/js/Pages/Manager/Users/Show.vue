<template>
    <DashboardLayout title="User Details">
        <!-- Header -->
        <div style="box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 2rem;"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem;"
                        :style="{ color: themeColors.textPrimary }">User Details</h1>
                    <p style="margin-top: 0.5rem;"
                       :style="{ color: themeColors.textSecondary }">View detailed information about this staff member.</p>
                </div>
                <div style="display: flex; gap: 0.75rem;">
                    <Link :href="`/admin/users/${user.id}/edit`"
                          style="padding: 0.5rem 1rem; border-radius: 0.375rem; transition: all 0.2s ease; font-size: 0.875rem; font-weight: 500; text-decoration: none;"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                              color: 'white' 
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        Edit User
                    </Link>
                    <Link href="/admin/users"
                          style="padding: 0.5rem 1rem; border-radius: 0.375rem; transition: all 0.2s ease; font-size: 0.875rem; font-weight: 500; text-decoration: none;"
                          :style="{ 
                              backgroundColor: themeColors.secondary,
                              color: themeColors.textPrimary 
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        Back to Users
                    </Link>
                </div>
            </div>
        </div>

        <!-- User Profile -->
        <div style="box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 2rem;"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div style="display: grid; grid-template-columns: repeat(1, 1fr); gap: 1.5rem;"
                 :style="{ '@media (min-width: 768px)': { gridTemplateColumns: 'repeat(3, 1fr)' } }">
                <!-- Profile Card -->
                <div :style="{ '@media (min-width: 768px)': { gridColumn: 'span 1' } }">
                    <div style="display: flex; flex-direction: column; align-items: center;">
                        <div style="width: 6rem; height: 6rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;"
                             :style="{ backgroundColor: themeColors.border }">
                            <span style="font-size: 1.5rem; font-weight: 500;"
                                  :style="{ color: themeColors.textPrimary }">
                                {{ user.first_name?.charAt(0) || '' }}{{ user.last_name?.charAt(0) || '' }}
                            </span>
                        </div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;"
                            :style="{ color: themeColors.textPrimary }">{{ user.first_name }} {{ user.last_name }}</h2>
                        <p style="margin-bottom: 0.5rem;"
                           :style="{ color: themeColors.textSecondary }">{{ user.email }}</p>
                        <p style="font-size: 0.875rem; margin-top: 0.5rem;"
                           :style="{ color: themeColors.textTertiary }">{{ user.employee_id ? 'ID: ' + user.employee_id : '' }}</p>

                        <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                            <span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500;"
                                  :style="{ 
                                      backgroundColor: getStatusColor(user.status) + '20',
                                      color: getStatusColor(user.status),
                                      border: `1px solid ${getStatusColor(user.status)}40`
                                  }">
                                {{ user.status }}
                            </span>
                            <span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500;"
                                  :style="{ 
                                      backgroundColor: getRoleColor(user.roles?.[0]?.name) + '20',
                                      color: getRoleColor(user.roles?.[0]?.name),
                                      border: `1px solid ${getRoleColor(user.roles?.[0]?.name)}40`
                                  }">
                                {{ formatRole(user.roles?.[0]?.name) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div :style="{ '@media (min-width: 768px)': { gridColumn: 'span 2' } }">
                    <h3 style="font-size: 1.125rem; font-weight: 500; margin-bottom: 1rem;"
                        :style="{ color: themeColors.textPrimary }">Personal Information</h3>
                    <div style="display: grid; grid-template-columns: repeat(1, 1fr); gap: 1rem;"
                         :style="{ '@media (min-width: 768px)': { gridTemplateColumns: 'repeat(2, 1fr)' } }">
                        <div style="margin-bottom: 1rem;">
                            <p style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;"
                               :style="{ color: themeColors.textSecondary }">Full Name</p>
                            <p :style="{ color: themeColors.textPrimary }">{{ user.first_name }} {{ user.last_name }}</p>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <p style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;"
                               :style="{ color: themeColors.textSecondary }">Email</p>
                            <p :style="{ color: themeColors.textPrimary }">{{ user.email }}</p>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <p style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;"
                               :style="{ color: themeColors.textSecondary }">Phone</p>
                            <p :style="{ color: themeColors.textPrimary }">{{ user.phone || 'Not provided' }}</p>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <p style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;"
                               :style="{ color: themeColors.textSecondary }">Date of Birth</p>
                            <p :style="{ color: themeColors.textPrimary }">{{ user.date_of_birth ? formatDate(user.date_of_birth) : 'Not provided' }}</p>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <p style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;"
                               :style="{ color: themeColors.textSecondary }">Gender</p>
                            <p :style="{ color: themeColors.textPrimary }">{{ user.gender || 'Not specified' }}</p>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <p style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;"
                               :style="{ color: themeColors.textSecondary }">Employee ID</p>
                            <p :style="{ color: themeColors.textPrimary }">{{ user.employee_id || 'Not assigned' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employment Information -->
        <div style="box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 2rem;"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <h3 style="font-size: 1.125rem; font-weight: 500; margin-bottom: 1rem;"
                :style="{ color: themeColors.textPrimary }">Employment Information</h3>
            <div style="display: grid; grid-template-columns: repeat(1, 1fr); gap: 1rem;"
                 :style="{ '@media (min-width: 768px)': { gridTemplateColumns: 'repeat(2, 1fr)' } }">
                <div style="margin-bottom: 1rem;">
                    <p style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;"
                       :style="{ color: themeColors.textSecondary }">Department</p>
                    <p :style="{ color: themeColors.textPrimary }">{{ formatDepartment(user.department) }}</p>
                </div>
                <div style="margin-bottom: 1rem;">
                    <p style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;"
                       :style="{ color: themeColors.textSecondary }">Position</p>
                    <p :style="{ color: themeColors.textPrimary }">{{ user.position || 'Not specified' }}</p>
                </div>
                <div style="margin-bottom: 1rem;">
                    <p style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;"
                       :style="{ color: themeColors.textSecondary }">Hire Date</p>
                    <p :style="{ color: themeColors.textPrimary }">{{ user.hire_date ? formatDate(user.hire_date) : 'Not specified' }}</p>
                </div>
                <div style="margin-bottom: 1rem;">
                    <p style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;"
                       :style="{ color: themeColors.textSecondary }">Employment Status</p>
                    <p :style="{ color: themeColors.textPrimary }">{{ user.status || 'Not specified' }}</p>
                </div>
                <div style="margin-bottom: 1rem;">
                    <p style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;"
                       :style="{ color: themeColors.textSecondary }">Pay Type</p>
                    <p :style="{ color: themeColors.textPrimary }">{{ user.pay_type || 'Not specified' }}</p>
                </div>
                <div style="margin-bottom: 1rem;" v-if="user.pay_type === 'hourly' && user.hourly_rate">
                    <p style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;"
                       :style="{ color: themeColors.textSecondary }">Hourly Rate</p>
                    <p :style="{ color: themeColors.textPrimary }">{{ formatCurrency(user.hourly_rate) }}</p>
                </div>
                <div style="margin-bottom: 1rem;" v-if="user.pay_type === 'monthly' && user.monthly_rate">
                    <p style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;"
                       :style="{ color: themeColors.textSecondary }">Monthly Rate</p>
                    <p :style="{ color: themeColors.textPrimary }">{{ formatCurrency(user.monthly_rate) }}</p>
                </div>
                <div style="margin-bottom: 1rem;" v-if="user.pay_type === 'salary' && user.salary">
                    <p style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;"
                       :style="{ color: themeColors.textSecondary }">Annual Salary</p>
                    <p :style="{ color: themeColors.textPrimary }">{{ formatCurrency(user.salary) }}</p>
                </div>
            </div>
        </div>

        <!-- Account Information -->
        <div style="box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 2rem;"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <h3 style="font-size: 1.125rem; font-weight: 500; margin-bottom: 1rem;"
                :style="{ color: themeColors.textPrimary }">Account Information</h3>
            <div style="display: grid; grid-template-columns: repeat(1, 1fr); gap: 1rem;"
                 :style="{ '@media (min-width: 768px)': { gridTemplateColumns: 'repeat(2, 1fr)' } }">
                <div style="margin-bottom: 1rem;">
                    <p style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;"
                       :style="{ color: themeColors.textSecondary }">Account Status</p>
                    <p :style="{ color: themeColors.textPrimary }">{{ user.is_active ? 'Active' : 'Inactive' }}</p>
                </div>
                <div style="margin-bottom: 1rem;">
                    <p style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;"
                       :style="{ color: themeColors.textSecondary }">Force Password Change</p>
                    <p :style="{ color: themeColors.textPrimary }">{{ user.force_password_change ? 'Yes' : 'No' }}</p>
                </div>
                <div style="margin-bottom: 1rem;">
                    <p style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;"
                       :style="{ color: themeColors.textSecondary }">Last Login</p>
                    <p :style="{ color: themeColors.textPrimary }">{{ user.last_login_at ? formatDateTime(user.last_login_at) : 'Never' }}</p>
                </div>
                <div style="margin-bottom: 1rem;">
                    <p style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;"
                       :style="{ color: themeColors.textSecondary }">Created At</p>
                    <p :style="{ color: themeColors.textPrimary }">{{ user.created_at ? formatDateTime(user.created_at) : 'Unknown' }}</p>
                </div>
            </div>
        </div>

        <!-- Roles and Permissions -->
        <div style="box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); border-radius: 0.5rem; padding: 1.5rem;"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <h3 style="font-size: 1.125rem; font-weight: 500; margin-bottom: 1rem;"
                :style="{ color: themeColors.textPrimary }">Roles and Permissions</h3>
            <div style="margin-bottom: 1rem;">
                <p style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;"
                   :style="{ color: themeColors.textSecondary }">Assigned Roles</p>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    <span v-for="role in user.roles" :key="role.id"
                          style="display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500;"
                          :style="{ 
                              backgroundColor: getRoleColor(role.name) + '20',
                              color: getRoleColor(role.name),
                              border: `1px solid ${getRoleColor(role.name)}40`
                          }">
                        {{ formatRole(role.name) }}
                    </span>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency, getCurrencySymbol } from '@/Utils/currency.js'

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
    currentUser: Object,
    settings: {
        type: Object,
        default: () => ({
            currency: 'XAF',
            currency_symbol: 'FCFA'
        })
    }
})

const getRoleColor = (role) => {
    const colors = {
        admin: themeColors.value.danger,
        manager: themeColors.value.warning,
        accountant: themeColors.value.primary,
        front_desk: themeColors.value.success,
        housekeeping: themeColors.value.secondary,
        maintenance: themeColors.value.warning,
        staff: themeColors.value.textSecondary,
    }
    return colors[role] || themeColors.value.textSecondary
}

const getStatusColor = (status) => {
    const colors = {
        active: themeColors.value.success,
        inactive: themeColors.value.danger,
        pending: themeColors.value.warning,
        probation: themeColors.value.primary,
        terminated: themeColors.value.textSecondary,
    }
    return colors[status] || themeColors.value.textSecondary
}

const formatRole = (role) => {
    return role.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDepartment = (department) => {
    return department.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (date) => {
    if (!date) return 'Not specified'
    return new Date(date).toLocaleDateString()
}

const formatDateTime = (date) => {
    if (!date) return 'Not specified'
    return new Date(date).toLocaleString()
}

// Get currency symbol from settings
const currencySymbol = computed(() => {
    const currency = props.settings?.currency || 'USD'
    return getCurrencySymbol(currency)
})
</script>
