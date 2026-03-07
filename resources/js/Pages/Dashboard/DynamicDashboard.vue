<template>
    <DashboardLayout :title="`${role.charAt(0).toUpperCase() + role.slice(1)} Dashboard`" :user="user">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">{{ role.charAt(0).toUpperCase() + role.slice(1) }} Dashboard</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">{{ getWelcomeMessage() }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-sm"
                           :style="{ color: themeColors.textTertiary }">{{ currentDateTime }}</p>
                        <p class="text-lg font-semibold"
                           :style="{ color: themeColors.textPrimary }">{{ user.roles[0]?.display_name || role.charAt(0).toUpperCase() + role.slice(1) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Critical Alerts (if applicable for role) -->
        <div v-if="hasAlerts && dashboardConfig.alerts" class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <h2 class="text-lg font-semibold mb-4"
                :style="{ color: themeColors.textPrimary }">Critical Alerts</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div v-for="(alert, key) in alerts" :key="key"
                     v-if="alert > 0"
                     class="rounded-lg p-4 cursor-pointer transition-colors"
                     :style="{
                         backgroundColor: 'rgba(239, 68, 68, 0.1)',
                         borderColor: themeColors.danger,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }">
                    <div class="flex items-center">
                        <ExclamationTriangleIcon class="h-6 w-6 mr-3"
                             :style="{ color: themeColors.danger }" />
                        <div>
                            <h3 class="text-sm font-medium"
                                :style="{ color: themeColors.danger }">{{ getAlertTitle(key) }}</h3>
                            <p class="text-lg font-bold"
                               :style="{ color: themeColors.danger }">{{ alert }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role-specific widgets -->
        <component :is="getRoleComponent()" 
                   :user="user" 
                   :data="dashboardData" 
                   :theme-colors="themeColors"
                   @navigate="navigateTo" />
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { useTheme } from '@/Composables/useTheme.js'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

// Import role-specific dashboard components
import AdminDashboardWidgets from '@/Components/Dashboard/AdminDashboardWidgets.vue'
import ManagerDashboardWidgets from '@/Components/Dashboard/ManagerDashboardWidgets.vue'
import AccountantDashboardWidgets from '@/Components/Dashboard/AccountantDashboardWidgets.vue'
import FrontDeskDashboardWidgets from '@/Components/Dashboard/FrontDeskDashboardWidgets.vue'
import HousekeepingDashboardWidgets from '@/Components/Dashboard/HousekeepingDashboardWidgets.vue'
import MaintenanceDashboardWidgets from '@/Components/Dashboard/MaintenanceDashboardWidgets.vue'
import StaffDashboardWidgets from '@/Components/Dashboard/StaffDashboardWidgets.vue'

const props = defineProps({
    user: Object,
    role: String,
    dashboardData: Object,
    alerts: Object
})

const { currentTheme, themeColors } = useTheme()
const currentDateTime = ref('')

// Dashboard configuration per role
const dashboardConfig = computed(() => {
    const configs = {
        admin: {
            alerts: true,
            widgets: ['stats', 'charts', 'activities', 'metrics'],
            permissions: ['full_access']
        },
        manager: {
            alerts: true,
            widgets: ['stats', 'charts', 'tasks'],
            permissions: ['operations', 'staff_management']
        },
        accountant: {
            alerts: false,
            widgets: ['financial_summary', 'charts', 'transactions'],
            permissions: ['financial_reports', 'expenses']
        },
        front_desk: {
            alerts: true,
            widgets: ['arrivals_departures', 'room_status', 'tasks'],
            permissions: ['checkin_checkout', 'reservations']
        },
        housekeeping: {
            alerts: true,
            widgets: ['tasks', 'room_status'],
            permissions: ['housekeeping_tasks']
        },
        maintenance: {
            alerts: true,
            widgets: ['requests', 'tasks'],
            permissions: ['maintenance_requests']
        },
        staff: {
            alerts: false,
            widgets: ['personal_info', 'schedule'],
            permissions: ['personal_view']
        }
    }
    return configs[props.role] || configs.staff
})

// Computed properties
const hasAlerts = computed(() => {
    return props.alerts && Object.values(props.alerts).some(count => count > 0)
})

// Methods
const updateDateTime = () => {
    const now = new Date()
    currentDateTime.value = now.toLocaleString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getWelcomeMessage = () => {
    const messages = {
        admin: `Welcome back, ${props.user.first_name}! Monitor and manage all hotel operations.`,
        manager: `Welcome back, ${props.user.first_name}! Oversee daily operations and staff performance.`,
        accountant: `Welcome back, ${props.user.first_name}! Manage financial reports and transactions.`,
        front_desk: `Welcome back, ${props.user.first_name}! Handle guest check-ins, check-outs, and reservations.`,
        housekeeping: `Welcome back, ${props.user.first_name}! Manage room cleaning and maintenance tasks.`,
        maintenance: `Welcome back, ${props.user.first_name}! Handle maintenance requests and facility upkeep.`,
        staff: `Welcome back, ${props.user.first_name}! View your schedule and personal information.`
    }
    return messages[props.role] || messages.staff
}

const getAlertTitle = (key) => {
    const titles = {
        maintenance_required: 'Maintenance Required',
        system_errors: 'System Errors',
        pending_approvals: 'Pending Approvals',
        offline_devices: 'Offline Devices',
        pending_tasks: 'Pending Tasks',
        urgent_requests: 'Urgent Requests'
    }
    return titles[key] || key
}

const getRoleComponent = () => {
    const components = {
        admin: AdminDashboardWidgets,
        manager: ManagerDashboardWidgets,
        accountant: AccountantDashboardWidgets,
        front_desk: FrontDeskDashboardWidgets,
        housekeeping: HousekeepingDashboardWidgets,
        maintenance: MaintenanceDashboardWidgets,
        staff: StaffDashboardWidgets
    }
    return components[props.role] || StaffDashboardWidgets
}

const navigateTo = (url) => {
    router.visit(url)
}

// Lifecycle hooks
onMounted(() => {
    updateDateTime()
    const interval = setInterval(updateDateTime, 60000) // Update every minute

    onUnmounted(() => {
        clearInterval(interval)
    })
})
</script>
