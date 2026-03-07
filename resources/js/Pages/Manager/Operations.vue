<template>
    <DashboardLayout title="Hotel Operations" :user="user" :navigation="navigation">
        <!-- Operations Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Hotel Operations</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Monitor and manage daily hotel operations and performance.</p>
                </div>
                <div class="flex space-x-3">
                    <button class="text-white px-4 py-2 rounded-md transition-colors" :style="{ backgroundColor: themeColors.primary }">
                        <ArrowPathIcon class="h-4 w-4 mr-2 inline" />
                        Refresh Data
                    </button>
                    <button class="text-white px-4 py-2 rounded-md transition-colors" :style="{ backgroundColor: themeColors.success }">
                        <DocumentTextIcon class="h-4 w-4 mr-2 inline" />
                        Generate Report
                    </button>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg shadow p-6" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <HomeIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.primary }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Occupancy Rate</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ metrics.occupancyRate }}%</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <CurrencyDollarIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.success }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Daily Revenue</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(parseFloat(metrics.dailyRevenue) || 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <UserGroupIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.secondary }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Guest Satisfaction</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ metrics.guestSatisfaction || 0 }}/5</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <ClockIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.warning }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Avg Check-in Time</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ metrics.avgCheckinTime || 0 }}min</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operations Dashboard -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Room Status Overview -->
            <div class="rounded-lg shadow p-6" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Room Status Overview</h3>
                <div class="space-y-4">
                    <div v-for="status in roomStatus" :key="status.type" 
                         class="flex items-center justify-between p-3 rounded-lg"
                         :style="getStatusContainerStyle(status.type)">
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full mr-3" :style="{ backgroundColor: getStatusColor(status.type) }"></div>
                            <span class="font-medium" :style="{ color: themeColors.textPrimary }">{{ status.label }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-bold" :style="{ color: themeColors.textPrimary }">{{ status.count }}</span>
                            <span class="text-sm ml-1" :style="{ color: themeColors.textTertiary }">rooms</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Activities -->
            <div class="rounded-lg shadow p-6" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Today's Activities</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 rounded-lg"
                         :style="getActivityContainerStyle('primary')">
                        <div class="flex items-center">
                            <CalendarIcon class="h-5 w-5 mr-3" :style="{ color: themeColors.primary }" />
                            <span class="font-medium" :style="{ color: themeColors.textPrimary }">Check-ins</span>
                        </div>
                        <span class="text-lg font-bold" :style="{ color: themeColors.primary }">{{ activities.checkins }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg"
                         :style="getActivityContainerStyle('danger')">
                        <div class="flex items-center">
                            <CalendarIcon class="h-5 w-5 mr-3" :style="{ color: themeColors.danger }" />
                            <span class="font-medium" :style="{ color: themeColors.textPrimary }">Check-outs</span>
                        </div>
                        <span class="text-lg font-bold" :style="{ color: themeColors.danger }">{{ activities.checkouts }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg"
                         :style="getActivityContainerStyle('success')">
                        <div class="flex items-center">
                            <PlusIcon class="h-5 w-5 mr-3" :style="{ color: themeColors.success }" />
                            <span class="font-medium" :style="{ color: themeColors.textPrimary }">New Reservations</span>
                        </div>
                        <span class="text-lg font-bold" :style="{ color: themeColors.success }">{{ activities.newReservations }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg"
                         :style="getActivityContainerStyle('warning')">
                        <div class="flex items-center">
                            <ExclamationTriangleIcon class="h-5 w-5 mr-3" :style="{ color: themeColors.warning }" />
                            <span class="font-medium" :style="{ color: themeColors.textPrimary }">Pending Issues</span>
                        </div>
                        <span class="text-lg font-bold" :style="{ color: themeColors.warning }">{{ activities.pendingIssues }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Alerts -->
        <div class="rounded-lg shadow p-6" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Recent Alerts & Notifications</h3>
            <div class="space-y-3">
                <div v-for="alert in recentAlerts" :key="alert.id" 
                     class="flex items-center justify-between p-3 rounded-lg"
                     :style="getAlertContainerStyle(alert.type)">
                    <div class="flex items-center">
                        <component :is="getAlertIcon(alert.type)" class="h-5 w-5 mr-3" :style="{ color: getAlertAccentColor(alert.type) }" />
                        <div>
                            <p class="font-medium" :style="{ color: themeColors.textPrimary }">{{ alert.title }}</p>
                            <p class="text-sm" :style="{ color: themeColors.textSecondary, opacity: 0.9 }">{{ alert.description }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">{{ formatTime(alert.created_at) }}</p>
                        <button class="text-xs underline" :style="{ color: themeColors.primary }">View Details</button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { formatCurrency, initializeCurrencySettings } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme.js'
import {
    HomeIcon,
    CurrencyDollarIcon,
    UserGroupIcon,
    ClockIcon,
    CalendarIcon,
    PlusIcon,
    ExclamationTriangleIcon,
    ArrowPathIcon,
    DocumentTextIcon,
    WrenchScrewdriverIcon,
    BellIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    metrics: {
        type: Object,
        default: () => ({})
    },
    roomStatus: {
        type: Array,
        default: () => []
    },
    activities: {
        type: Object,
        default: () => ({})
    },
    recentAlerts: {
        type: Array,
        default: () => []
    }
})

const navigation = computed(() => getNavigationForRole('manager'))

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
 }))

loadTheme()

const metrics = computed(() => props.metrics || {})
const roomStatus = computed(() => props.roomStatus || [])
const activities = computed(() => props.activities || {})
const recentAlerts = computed(() => props.recentAlerts || [])

const getStatusColor = (type) => {
    const colors = {
        available: themeColors.value.success,
        occupied: themeColors.value.primary,
        cleaning: themeColors.value.warning,
        maintenance: themeColors.value.danger,
    }
    return colors[type] || themeColors.value.textSecondary
}

const getStatusContainerStyle = (type) => {
    const accent = getStatusColor(type)
    return {
        backgroundColor: themeColors.value.background,
        border: `1px solid ${themeColors.value.border}`,
        boxShadow: `inset 4px 0 0 0 ${accent}`,
    }
}

const getActivityContainerStyle = (variant) => {
    const accent = {
        primary: themeColors.value.primary,
        success: themeColors.value.success,
        warning: themeColors.value.warning,
        danger: themeColors.value.danger,
    }[variant] || themeColors.value.textSecondary

    return {
        backgroundColor: themeColors.value.background,
        border: `1px solid ${themeColors.value.border}`,
        boxShadow: `inset 4px 0 0 0 ${accent}`,
    }
}

const getAlertAccentColor = (type) => {
    const colors = {
        maintenance: themeColors.value.danger,
        guest: themeColors.value.primary,
        system: themeColors.value.textSecondary,
    }
    return colors[type] || themeColors.value.textSecondary
}

const getAlertContainerStyle = (type) => {
    const accent = getAlertAccentColor(type)
    return {
        backgroundColor: themeColors.value.background,
        border: `1px solid ${themeColors.value.border}`,
        boxShadow: `inset 4px 0 0 0 ${accent}`,
    }
}

const getAlertIcon = (type) => {
    const icons = {
        maintenance: WrenchScrewdriverIcon,
        guest: UserGroupIcon,
        system: BellIcon,
    }
    return icons[type] || BellIcon
}

const formatTime = (time) => {
    if (!time) return ''
    return new Date(time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

// Initialize currency settings
onMounted(() => {
    initializeCurrencySettings()
})
</script>
