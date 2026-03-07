<template>
    <DashboardLayout title="Manager Dashboard" :user="user" :navigation="navigation">
        <!-- Dashboard Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Manager Dashboard</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Oversee hotel operations, staff management, and guest services.</p>
                </div>
                <div class="text-right">
                    <p class="text-sm"
                       :style="{ color: themeColors.textTertiary }">{{ currentDateTime }}</p>
                    <p class="text-lg font-semibold"
                       :style="{ color: themeColors.textPrimary }">Hotel Manager</p>
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
                :style="{ color: themeColors.textPrimary }">Today's Overview</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="rounded-lg shadow-sm p-4 transition-shadow cursor-pointer"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <CalendarDaysIcon class="h-8 w-8"
                                             :style="{ color: themeColors.primary }" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Today's Arrivals</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: themeColors.textPrimary }">{{ todaysStats.arrivals }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-lg shadow-sm p-4 transition-shadow cursor-pointer"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <CalendarDaysIcon class="h-8 w-8"
                                             :style="{ color: themeColors.danger }" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Today's Departures</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: themeColors.textPrimary }">{{ todaysStats.departures }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-lg shadow-sm p-4 transition-shadow cursor-pointer"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <HomeIcon class="h-8 w-8"
                                     :style="{ color: themeColors.success }" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Occupancy Rate</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: themeColors.textPrimary }">{{ todaysStats.occupancyRate }}%</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-lg shadow-sm p-4 transition-shadow cursor-pointer"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <CurrencyDollarIcon class="h-8 w-8"
                                               :style="{ color: themeColors.warning }" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Today's Revenue</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: themeColors.textPrimary }">{{ formattedRevenue }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operational Status -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Room Status -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Room Status</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textPrimary }">Available</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :style="getPillStyle('success')">
                            {{ roomStatus.available }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textPrimary }">Occupied</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :style="getPillStyle('primary')">
                            {{ roomStatus.occupied }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textPrimary }">Cleaning</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :style="getPillStyle('warning')">
                            {{ roomStatus.cleaning }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textPrimary }">Maintenance</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :style="getPillStyle('danger')">
                            {{ roomStatus.maintenance }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Staff Status -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Staff Status</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textPrimary }">On Duty</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :style="getPillStyle('success')">
                            {{ staffStatus.onDuty }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textPrimary }">Off Duty</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :style="getPillStyle('secondary')">
                            {{ staffStatus.offDuty }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textPrimary }">On Break</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :style="getPillStyle('warning')">
                            {{ staffStatus.onBreak }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textPrimary }">Late/Absent</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :style="getPillStyle('danger')">
                            {{ staffStatus.lateAbsent }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Quick Actions</h3>
                <div class="space-y-3">
                    <Link href="/manager/reservations/create"
                          class="w-full flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium transition-colors"
                          :style="{ 
                              backgroundColor: themeColors.background,
                              borderColor: themeColors.border,
                              color: themeColors.textPrimary,
                              borderStyle: 'solid',
                              borderWidth: '1px'
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        New Reservation
                    </Link>
                    <Link href="/manager/guests/create"
                          class="w-full flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium transition-colors"
                          :style="{ 
                              backgroundColor: themeColors.background,
                              borderColor: themeColors.border,
                              color: themeColors.textPrimary,
                              borderStyle: 'solid',
                              borderWidth: '1px'
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                        <UserPlusIcon class="h-4 w-4 mr-2" />
                        Add Guest
                    </Link>
                    <Link href="/manager/rooms"
                          class="w-full flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium transition-colors"
                          :style="{ 
                              backgroundColor: themeColors.background,
                              borderColor: themeColors.border,
                              color: themeColors.textPrimary,
                              borderStyle: 'solid',
                              borderWidth: '1px'
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                        <BuildingOfficeIcon class="h-4 w-4 mr-2" />
                        Room Management
                    </Link>
                    <Link href="/manager/reports"
                          class="w-full flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium transition-colors"
                          :style="{ 
                              backgroundColor: themeColors.background,
                              borderColor: themeColors.border,
                              color: themeColors.textPrimary,
                              borderStyle: 'solid',
                              borderWidth: '1px'
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                        <ChartBarIcon class="h-4 w-4 mr-2" />
                        View Reports
                    </Link>
                </div>
            </div>
        </div>

        <!-- Performance Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Weekly Occupancy -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Weekly Occupancy Trend</h3>
                <div class="h-64">
                    <canvas ref="occupancyChart"></canvas>
                </div>
            </div>

            <!-- Revenue Breakdown -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Revenue Breakdown</h3>
                <div class="h-64">
                    <canvas ref="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency, initializeCurrencySettings, setCurrentCurrency, setCurrencyPosition } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme.js'
import {
    CalendarDaysIcon,
    HomeIcon,
    CurrencyDollarIcon,
    UserGroupIcon,
    UserIcon,
    PlusIcon,
    UserPlusIcon,
    BuildingOfficeIcon,
    ChartBarIcon,
    ClipboardDocumentListIcon,
    WrenchScrewdriverIcon,
    BellIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'
import Chart from 'chart.js/auto'

// Initialize theme
const { loadTheme, currentTheme } = useTheme()
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
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)'
}))

// Load theme on mount
loadTheme()

const props = defineProps({
    user: Object,
    navigation: Array,
    todaysStats: Object,
    roomStatus: Object,
    staffStatus: Object,
    recentCheckins: Array,
    pendingTasks: Array,
    charts: Object,
})
const occupancyChart = ref(null)
const revenueChart = ref(null)

const currentDateTime = computed(() => {
    return new Date().toLocaleString()
})

const getTimeOfDay = () => {
    const hour = new Date().getHours()
    if (hour < 12) return 'morning'
    if (hour < 17) return 'afternoon'
    return 'evening'
}

const getTaskIcon = (type) => {
    const icons = {
        housekeeping: ClipboardDocumentListIcon,
        maintenance: WrenchScrewdriverIcon,
        front_desk: UserGroupIcon,
        management: BellIcon,
    }
    return icons[type] || ClipboardDocumentListIcon
}

const getPriorityColor = (priority) => {
    const colors = {
        high: 'rgba(239, 68, 68, 0.1) rgb(239, 68, 68) 1px solid rgb(239, 68, 68)',
        medium: 'rgba(245, 158, 11, 0.1) rgb(245, 158, 11) 1px solid rgb(245, 158, 11)',
        low: 'rgba(16, 185, 129, 0.1) rgb(16, 185, 129) 1px solid rgb(16, 185, 129)',
    }
    return colors[priority] || 'rgba(107, 114, 128, 0.1) rgb(107, 114, 128) 1px solid rgb(107, 114, 128)'
}

const getPillStyle = (variant) => {
    const color = {
        primary: themeColors.value.primary,
        secondary: themeColors.value.textSecondary,
        success: themeColors.value.success,
        warning: themeColors.value.warning,
        danger: themeColors.value.danger,
    }[variant] || themeColors.value.textSecondary

    const fill = {
        primary: 'rgba(59, 130, 246, 0.1)',
        secondary: 'rgba(107, 114, 128, 0.1)',
        success: 'rgba(16, 185, 129, 0.1)',
        warning: 'rgba(245, 158, 11, 0.1)',
        danger: 'rgba(239, 68, 68, 0.1)',
    }[variant] || 'rgba(107, 114, 128, 0.1)'

    return {
        backgroundColor: 'rgba(0, 0, 0, 0)',
        color,
        border: `1px solid ${color}`,
        boxShadow: `inset 0 0 0 9999px ${fill}`,
    }
}

const formatTime = (time) => {
    return new Date(time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

const formattedRevenue = computed(() => {
    if (!props.todaysStats?.revenue) return formatCurrency(0)
    return formatCurrency(props.todaysStats.revenue)
})

// Initialize currency settings from page props
const page = usePage()
const settings = computed(() => page.props.settings || {})

// Watch for settings changes and update currency
watch(settings, (newSettings) => {
    if (newSettings.currency) {
        setCurrentCurrency(newSettings.currency)
    }
    if (newSettings.currency_position) {
        setCurrencyPosition(newSettings.currency_position)
    }
}, { immediate: true, deep: true })

// Initialize currency settings
onMounted(() => {
    // Initialize from settings prop
    if (settings.value.currency) {
        setCurrentCurrency(settings.value.currency)
    }
    initializeCurrencySettings()
    
    if (props.charts?.occupancy && occupancyChart.value) {
        new Chart(occupancyChart.value, {
            type: 'line',
            data: {
                labels: props.charts.occupancy.map(item => item.date),
                datasets: [{
                    label: 'Occupancy Rate (%)',
                    data: props.charts.occupancy.map(item => item.rate),
                    borderColor: themeColors.value.success,
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

    if (props.charts?.revenue && revenueChart.value) {
        new Chart(revenueChart.value, {
            type: 'doughnut',
            data: {
                labels: props.charts.revenue.map(item => item.category),
                datasets: [{
                    data: props.charts.revenue.map(item => item.amount),
                    backgroundColor: [
                        themeColors.value.primary,
                        themeColors.value.success,
                        themeColors.value.warning,
                        themeColors.value.danger,
                        themeColors.value.secondary
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                const label = context.label || ''
                                const value = context.parsed || 0
                                const currency = settings.value.currency || 'USD'
                                const position = settings.value.currency_position || 'prefix'
                                return label + ': ' + formatCurrency(value, currency, position)
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
