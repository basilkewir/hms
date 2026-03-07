<template>
    <DashboardLayout title="Dashboard" :user="user" :navigation="navigation">
        <!-- Dashboard Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">{{ getDashboardTitle() }}</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">{{ getRoleWelcomeMessage() }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm"
                       :style="{ color: themeColors.textTertiary }">{{ currentDateTime }}</p>
                    <p class="text-lg font-semibold"
                       :style="{ color: themeColors.textPrimary }">{{ user.roles[0]?.display_name || user.roles[0]?.name }}</p>
                </div>
            </div>
        </div>

        <!-- Critical Alerts -->
        <div v-if="hasAlerts" class="shadow rounded-lg p-6 mb-8"
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

        <!-- Key Metrics -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <h2 class="text-lg font-semibold mb-4"
                :style="{ color: themeColors.textPrimary }">Key Metrics</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="(stat, key) in displayStats" :key="key"
                     class="rounded-lg shadow-sm p-4 transition-shadow cursor-pointer"
                     :style="{
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <component :is="getStatIcon(key)"
                                      class="h-8 w-8"
                                      :style="{ color: themeColors.primary }" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">{{ getStatTitle(key) }}</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: themeColors.textPrimary }">{{ formatStatValue(key, stat) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div v-if="charts && Object.keys(charts).length > 0"
             class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <h2 class="text-lg font-semibold mb-4"
                :style="{ color: themeColors.textPrimary }">Analytics</h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Revenue Chart -->
                <div v-if="charts.revenue">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold"
                            :style="{ color: themeColors.textPrimary }">Revenue Trend (Last 30 Days)</h3>
                        <select v-model="revenueChartPeriod"
                                class="text-sm rounded-md px-3 py-1 focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="30">Last 30 Days</option>
                            <option value="90">Last 90 Days</option>
                            <option value="365">Last Year</option>
                        </select>
                    </div>
                    <div class="h-64">
                        <canvas ref="revenueChart"
                             class="bg-white"></canvas>
                    </div>
                </div>

                <!-- Occupancy Chart -->
                <div v-if="charts.occupancy">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold"
                            :style="{ color: themeColors.textPrimary }">Occupancy Rate</h3>
                        <span class="text-sm"
                              :style="{ color: themeColors.textTertiary }">Current Month</span>
                    </div>
                    <div class="h-64">
                        <canvas ref="occupancyChart"
                             class="bg-white"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role-Specific Sections -->
        <component :is="getRoleSpecificComponent()" />

        <!-- Quick Actions -->
        <div class="shadow rounded-lg p-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <h3 class="text-lg font-semibold mb-4"
                :style="{ color: themeColors.textPrimary }">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <Link v-for="action in quickActions" :key="action.route"
                      :href="action.route"
                      class="flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium transition-colors"
                      :style="{
                          backgroundColor: themeColors.background,
                          borderColor: themeColors.border,
                          color: themeColors.textPrimary,
                          borderStyle: 'solid',
                          borderWidth: '1px'
                      }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                    <component :is="action.icon" class="h-4 w-4 mr-2" />
                    {{ action.label }}
                </Link>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { formatCurrency, initializeCurrencySettings } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme.js'
import {
    ExclamationTriangleIcon,
    HomeIcon,
    UserGroupIcon,
    CurrencyDollarIcon,
    ClockIcon,
    TvIcon,
    ChartBarIcon,
    UserPlusIcon,
    BuildingOfficeIcon,
    CogIcon,
    CalendarDaysIcon,
    KeyIcon,
    CreditCardIcon,
    PlusIcon,
    WrenchScrewdriverIcon,
    ClipboardDocumentListIcon,
    BanknotesIcon,
    ReceiptPercentIcon,
    DocumentTextIcon,
    ArrowUpIcon,
    ArrowDownIcon,
    BellIcon
} from '@heroicons/vue/24/outline'
import Chart from 'chart.js/auto'

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
    stats: Object,
    charts: Object,
    recentActivity: Object,
    alerts: Object,
    financialSummary: Object,
    todaysStats: Object,
    roomStatus: Object,
    staffStatus: Object,
    recentCheckins: Array,
    pendingTasks: Array,
    arrivals: Array,
    departures: Array,
    guestRequests: Array,
    recentTransactions: Array,
    pendingPayments: Array,
    metrics: Object,
})

const navigation = computed(() => {
    const role = props.user.roles[0]?.name || 'staff'
    return getNavigationForRole(role)
})

const revenueChart = ref(null)
const occupancyChart = ref(null)
const revenueChartPeriod = ref('30')

const currentDateTime = computed(() => {
    return new Date().toLocaleString()
})

// Get user role
const userRole = computed(() => {
    return props.user.roles[0]?.name || 'staff'
})

// Get dashboard title based on role
const getDashboardTitle = () => {
    const titles = {
        admin: 'Admin Dashboard',
        manager: 'Manager Dashboard',
        accountant: 'Accountant Dashboard',
        front_desk: 'Front Desk Dashboard',
        housekeeping: 'Housekeeping Dashboard',
        maintenance: 'Maintenance Dashboard',
        staff: 'Staff Dashboard'
    }
    return titles[userRole.value] || 'Dashboard'
}

// Role-specific welcome messages
const getRoleWelcomeMessage = () => {
    const messages = {
        admin: 'You have full system access. Monitor hotel operations and manage all aspects of the business.',
        manager: 'Oversee hotel operations, staff management, and guest services.',
        accountant: 'Manage financial operations, process payments, and generate reports.',
        front_desk: 'Handle guest check-ins, reservations, and provide excellent customer service.',
        housekeeping: 'Maintain room cleanliness and ensure guest comfort.',
        maintenance: 'Keep hotel facilities and IPTV systems running smoothly.',
        staff: 'Welcome to your workspace. Check your schedule and clock in for your shift.'
    }
    return messages[userRole.value] || 'Welcome to the hotel management system.'
}

// Display stats based on role
const displayStats = computed(() => {
    const roleStats = {
        admin: props.stats || {},
        manager: {
            arrivals: props.todaysStats?.arrivals || 0,
            departures: props.todaysStats?.departures || 0,
            occupancyRate: props.todaysStats?.occupancyRate || 0,
            revenue: props.todaysStats?.revenue || 0,
        },
        accountant: {
            todaysRevenue: props.financialSummary?.todaysRevenue || 0,
            monthlyRevenue: props.financialSummary?.monthlyRevenue || 0,
            monthlyExpenses: props.financialSummary?.monthlyExpenses || 0,
            netProfit: props.financialSummary?.netProfit || 0,
        },
        front_desk: {
            arrivals: props.todaysActivities?.arrivals || 0,
            departures: props.todaysActivities?.departures || 0,
            currentGuests: props.todaysActivities?.currentGuests || 0,
            availableRooms: props.todaysActivities?.availableRooms || 0,
        },
        housekeeping: {
            total_rooms: props.stats?.total_rooms || 0,
            occupied_rooms: props.stats?.occupied_rooms || 0,
            cleaning: props.roomStatus?.cleaning || 0,
            available: props.roomStatus?.available || 0,
        },
        maintenance: {
            maintenance_required: props.alerts?.maintenance_required || 0,
            iptv_devices_online: props.stats?.iptv_devices_online || 0,
            total_rooms: props.stats?.total_rooms || 0,
            system_errors: props.alerts?.system_errors || 0,
        },
        staff: {
            is_clocked_in: props.stats?.is_clocked_in || false,
            todays_hours: props.stats?.todays_hours || 0,
            this_week_hours: props.stats?.this_week_hours || 0,
            active_staff: props.stats?.active_staff || 0,
        }
    }

    return roleStats[userRole.value] || props.stats || {}
})

// Has alerts computed property
const hasAlerts = computed(() => {
    return props.alerts && Object.values(props.alerts).some(alert => alert > 0)
})

// Role-specific components
const getRoleSpecificComponent = () => {
    return 'div' // Default empty div
}

// Safe route helper — never throws
const safeRoute = (name, fallback = '#') => {
    try { return route(name) } catch { return fallback }
}

// Quick actions based on role — lazy: only resolve routes for the active role
const quickActions = computed(() => {
    const role = userRole.value
    if (role === 'admin') return [
        { route: safeRoute('admin.users.create'), label: 'Add New User', icon: UserPlusIcon },
        { route: safeRoute('admin.rooms.index'), label: 'Manage Rooms', icon: BuildingOfficeIcon },
        { route: safeRoute('admin.reports.index'), label: 'View Reports', icon: ChartBarIcon },
        { route: safeRoute('admin.settings'), label: 'System Settings', icon: CogIcon },
    ]
    if (role === 'manager') return [
        { route: '/manager/reservations/create', label: 'New Reservation', icon: PlusIcon },
        { route: '/manager/guests/create', label: 'Add Guest', icon: UserPlusIcon },
        { route: '/manager/rooms', label: 'Room Management', icon: BuildingOfficeIcon },
        { route: '/manager/reports', label: 'View Reports', icon: ChartBarIcon },
    ]
    if (role === 'accountant') return [
        { route: safeRoute('accountant.expenses.create'), label: 'Add Expense', icon: PlusIcon },
        { route: safeRoute('accountant.transactions.index'), label: 'View Transactions', icon: CurrencyDollarIcon },
        { route: safeRoute('accountant.payroll.index'), label: 'Process Payroll', icon: BanknotesIcon },
        { route: safeRoute('accountant.reports.profit-loss'), label: 'Financial Reports', icon: DocumentTextIcon },
    ]
    if (role === 'front_desk' || role === 'front-desk') return [
        { route: safeRoute('front-desk.checkin'), label: 'Quick Check-in', icon: KeyIcon },
        { route: safeRoute('front-desk.checkout'), label: 'Quick Check-out', icon: KeyIcon },
        { route: safeRoute('front-desk.reservations.create'), label: 'New Reservation', icon: PlusIcon },
        { route: safeRoute('front-desk.payments.process'), label: 'Process Payment', icon: CreditCardIcon },
    ]
    if (role === 'housekeeping') return [
        { route: '/housekeeping/rooms', label: 'Room Status', icon: HomeIcon },
        { route: '/housekeeping/tasks', label: 'Cleaning Tasks', icon: ClipboardDocumentListIcon },
        { route: '/housekeeping/reports', label: 'Reports', icon: ChartBarIcon },
        { route: '/housekeeping/schedule', label: 'Schedule', icon: CalendarDaysIcon },
    ]
    if (role === 'maintenance') return [
        { route: '/maintenance/requests', label: 'Maintenance Requests', icon: WrenchScrewdriverIcon },
        { route: '/maintenance/iptv', label: 'IPTV Status', icon: TvIcon },
        { route: '/maintenance/facilities', label: 'Facilities', icon: BuildingOfficeIcon },
        { route: '/maintenance/reports', label: 'Reports', icon: ChartBarIcon },
    ]
    if (role === 'staff') return [
        { route: safeRoute('time-tracking.clock'), label: props.stats?.is_clocked_in ? 'Clock Out' : 'Clock In', icon: ClockIcon },
        { route: '/staff/schedule', label: 'My Schedule', icon: CalendarDaysIcon },
        { route: '/staff/tasks', label: 'My Tasks', icon: ClipboardDocumentListIcon },
        { route: '/staff/profile', label: 'My Profile', icon: UserGroupIcon },
    ]
    return []
})

// Methods
const getStatIcon = (key) => {
    const icons = {
        total_rooms: HomeIcon,
        occupied_rooms: HomeIcon,
        available_rooms: HomeIcon,
        current_guests: UserGroupIcon,
        todays_revenue: CurrencyDollarIcon,
        monthly_revenue: CurrencyDollarIcon,
        todays_hours: ClockIcon,
        iptv_devices_online: TvIcon,
        occupancy_rate: ChartBarIcon,
        arrivals: CalendarDaysIcon,
        departures: CalendarDaysIcon,
        occupancyRate: HomeIcon,
        revenue: CurrencyDollarIcon,
        todaysRevenue: CurrencyDollarIcon,
        monthlyExpenses: ReceiptPercentIcon,
        netProfit: ChartBarIcon,
        currentGuests: UserGroupIcon,
        availableRooms: HomeIcon,
        cleaning: ClipboardDocumentListIcon,
        maintenance_required: WrenchScrewdriverIcon,
        system_errors: ExclamationTriangleIcon,
        is_clocked_in: ClockIcon,
        this_week_hours: ClockIcon,
        active_staff: UserGroupIcon,
    }
    return icons[key] || ChartBarIcon
}

const getStatTitle = (key) => {
    const titles = {
        total_rooms: 'Total Rooms',
        occupied_rooms: 'Occupied Rooms',
        available_rooms: 'Available Rooms',
        maintenance_rooms: 'Maintenance',
        todays_arrivals: "Today's Arrivals",
        todays_departures: "Today's Departures",
        current_guests: 'Current Guests',
        todays_revenue: "Today's Revenue",
        monthly_revenue: 'Monthly Revenue',
        occupancy_rate: 'Occupancy Rate',
        active_staff: 'Active Staff',
        iptv_devices_online: 'IPTV Online',
        is_clocked_in: 'Clock Status',
        todays_hours: "Today's Hours",
        this_week_hours: 'Week Hours',
        arrivals: "Today's Arrivals",
        departures: "Today's Departures",
        occupancyRate: 'Occupancy Rate',
        revenue: "Today's Revenue",
        todaysRevenue: "Today's Revenue",
        monthlyExpenses: 'Monthly Expenses',
        netProfit: 'Net Profit',
        availableRooms: 'Available Rooms',
        cleaning: 'Cleaning',
        maintenance_required: 'Maintenance Required',
        system_errors: 'System Errors',
    }
    return titles[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatStatValue = (key, value) => {
    if (key.includes('revenue') || key.includes('amount') || key === 'netProfit' || key === 'monthlyExpenses' || key === 'todaysRevenue' || key === 'monthlyRevenue') {
        return formatCurrency(value)
    }
    if (key === 'occupancy_rate' || key === 'occupancyRate') {
        return `${value}%`
    }
    if (key === 'is_clocked_in') {
        return value ? 'Clocked In' : 'Clocked Out'
    }
    if (key.includes('hours')) {
        return `${value}h`
    }
    return value
}

const getAlertTitle = (key) => {
    const titles = {
        maintenance_required: 'Maintenance Required',
        expiring_documents: 'Expiring Documents',
        pending_police_verification: 'Pending Verification',
        offline_iptv_devices: 'IPTV Devices Offline',
        overdue_payments: 'Overdue Payments',
        late_checkouts: 'Late Checkouts',
        no_shows: 'No Shows',
        system_errors: 'System Errors',
    }
    return titles[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDateTime = (datetime) => {
    return new Date(datetime).toLocaleString()
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const formatTime = (time) => {
    return new Date(time).toLocaleTimeString()
}

// Chart initialization
onMounted(() => {
    initializeCurrencySettings()
    if (props.charts?.revenue && revenueChart.value) {
        new Chart(revenueChart.value, {
            type: 'line',
            data: {
                labels: props.charts.revenue.map(item => item.date),
                datasets: [{
                    label: 'Revenue',
                    data: props.charts.revenue.map(item => item.revenue || item.amount),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value)
                            }
                        }
                    }
                }
            }
        })
    }

    if (props.charts?.occupancy && occupancyChart.value) {
        new Chart(occupancyChart.value, {
            type: 'line',
            data: {
                labels: props.charts.occupancy.map(item => item.date),
                datasets: [{
                    label: 'Occupancy Rate (%)',
                    data: props.charts.occupancy.map(item => item.occupancy || item.rate),
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%'
                            }
                        }
                    }
                }
            }
        })
    }
})
</script>

<style scoped>
/* Fix placeholder colors for inputs */
input::placeholder,
textarea::placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input::-webkit-input-placeholder,
textarea::-webkit-input-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input::-moz-placeholder,
textarea::-moz-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input:-ms-input-placeholder,
textarea:-ms-input-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

/* Fix placeholder colors for select options */
select option:disabled,
select option[disabled] {
    color: var(--kotel-text-tertiary) !important;
}

select option[value=""] {
    color: var(--kotel-text-tertiary) !important;
}
</style>
