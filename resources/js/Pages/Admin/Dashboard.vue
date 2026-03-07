<template>
    <DashboardLayout title="Admin Dashboard" :user="user">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Admin Dashboard</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Welcome back, {{ user.first_name }}! Monitor and manage all hotel operations.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-sm"
                           :style="{ color: themeColors.textTertiary }">{{ currentDateTime }}</p>
                        <p class="text-lg font-semibold"
                           :style="{ color: themeColors.textPrimary }">{{ user.roles[0]?.display_name }}</p>
                    </div>
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

        <!-- Dashboard Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                        <BuildingOfficeIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Rooms</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ dashboardStats?.totalRooms || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                        <CheckCircleIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Occupied</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ dashboardStats?.occupiedRooms || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(250, 204, 21, 0.1)' }">
                        <ClockIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Available</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ dashboardStats?.availableRooms || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(139, 92, 246, 0.1)' }">
                        <UserGroupIcon class="h-6 w-6" :style="{ color: '#8b5cf6' }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Guests</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ dashboardStats?.totalGuests || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(251, 146, 60, 0.1)' }">
                        <CalendarDaysIcon class="h-6 w-6" :style="{ color: '#fb923c' }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Reservations</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ dashboardStats?.totalReservations || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(16, 185, 129, 0.1)' }">
                        <CurrencyDollarIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Revenue</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ formatCurrency(dashboardStats?.todayRevenue || 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <h2 class="text-lg font-semibold mb-6"
                :style="{ color: themeColors.textPrimary }">Analytics</h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Revenue Chart -->
                <div class="flex flex-col">
                    <h3 class="text-lg font-semibold mb-4"
                        :style="{ color: themeColors.textPrimary }">Revenue Trend (Last 30 Days)</h3>
                    <div class="flex-1 bg-white rounded-lg border border-gray-300 p-4 relative" style="min-height: 350px;">
                        <svg v-if="props.charts?.revenue?.length" :key="'revenue-' + revenueDataKey"
                             width="100%" height="100%" viewBox="0 0 600 300" preserveAspectRatio="xMidYMid meet"
                             @mousemove="handleRevenueHover" @mouseleave="hoveredRevenueIndex = null">
                            <!-- Grid lines -->
                            <g stroke="#e0e0e0" stroke-width="1">
                                <line x1="40" y1="30" x2="40" y2="270" />
                                <line x1="40" y1="270" x2="580" y2="270" />
                                <line x1="40" y1="90" x2="580" y2="90" stroke="#f0f0f0" />
                                <line x1="40" y1="150" x2="580" y2="150" stroke="#f0f0f0" />
                                <line x1="40" y1="210" x2="580" y2="210" stroke="#f0f0f0" />
                            </g>
                            <!-- Revenue line -->
                            <polyline v-if="revenuePoints" :points="revenuePoints" fill="none" stroke="#1e40af" stroke-width="2" />
                            <!-- Revenue fill area -->
                            <polygon v-if="revenuePoints" :points="revenueFill" fill="rgba(30, 64, 175, 0.1)" />
                            <!-- Data points with hover -->
                            <circle v-for="(point, idx) in revenueChartPoints" :key="'rp-' + idx"
                                    :cx="point.x" :cy="point.y" :r="hoveredRevenueIndex === idx ? 6 : 4"
                                    :fill="hoveredRevenueIndex === idx ? '#1e40af' : '#1e40af'"
                                    :stroke="hoveredRevenueIndex === idx ? '#333' : '#fff'"
                                    :stroke-width="hoveredRevenueIndex === idx ? 3 : 2"
                                    style="cursor: pointer; transition: all 0.2s ease;" />
                            <!-- X-axis labels -->
                            <text v-for="(label, idx) in xAxisLabels" :key="'xl-' + idx"
                                  :x="xAxisLabelPositions[idx]" y="290"
                                  font-size="10" text-anchor="middle" fill="#666">
                                {{ label }}
                            </text>
                            <!-- Y-axis labels with better formatting -->
                            <text x="35" y="35" font-size="11" font-weight="bold" text-anchor="end" fill="#333">{{ formatRevenueLabelMax }}</text>
                            <text x="35" y="155" font-size="11" text-anchor="end" fill="#666">{{ formatRevenueLabelMid }}</text>
                            <text x="35" y="275" font-size="11" text-anchor="end" fill="#666">$0</text>
                        </svg>
                        <!-- Tooltip -->
                        <div v-if="hoveredRevenueIndex !== null && props.charts?.revenue?.[hoveredRevenueIndex]"
                             class="absolute bg-gray-900 text-white px-3 py-2 rounded shadow-lg text-sm whitespace-nowrap"
                             :style="{
                                 left: tooltipX + 'px',
                                 top: '10px',
                                 pointerEvents: 'none'
                             }">
                            <div>{{ props.charts.revenue[hoveredRevenueIndex].date }}</div>
                            <div class="font-semibold">{{ formatCurrency(props.charts.revenue[hoveredRevenueIndex].amount) }}</div>
                        </div>
                        <div v-else class="flex items-center justify-center h-full text-gray-500">
                            <p>No revenue data available</p>
                        </div>
                    </div>
                </div>

                <!-- Occupancy Chart -->
                <div class="flex flex-col">
                    <h3 class="text-lg font-semibold mb-4"
                        :style="{ color: themeColors.textPrimary }">Occupancy Rate</h3>
                    <div class="flex-1 bg-white rounded-lg border border-gray-300 p-4 relative" style="min-height: 350px;">
                        <svg v-if="props.charts?.occupancy?.length" :key="'occupancy-' + occupancyDataKey"
                             width="100%" height="100%" viewBox="0 0 600 300" preserveAspectRatio="xMidYMid meet"
                             @mousemove="handleOccupancyHover" @mouseleave="hoveredOccupancyIndex = null">
                            <!-- Grid lines -->
                            <g stroke="#e0e0e0" stroke-width="1">
                                <line x1="40" y1="30" x2="40" y2="270" />
                                <line x1="40" y1="270" x2="580" y2="270" />
                                <line x1="40" y1="90" x2="580" y2="90" stroke="#f0f0f0" />
                                <line x1="40" y1="150" x2="580" y2="150" stroke="#f0f0f0" />
                                <line x1="40" y1="210" x2="580" y2="210" stroke="#f0f0f0" />
                            </g>
                            <!-- Occupancy line -->
                            <polyline v-if="occupancyPoints" :points="occupancyPoints" fill="none" stroke="#047857" stroke-width="2" />
                            <!-- Occupancy fill area -->
                            <polygon v-if="occupancyPoints" :points="occupancyFill" fill="rgba(4, 120, 87, 0.1)" />
                            <!-- Data points with hover -->
                            <circle v-for="(point, idx) in occupancyChartPoints" :key="'op-' + idx"
                                    :cx="point.x" :cy="point.y" :r="hoveredOccupancyIndex === idx ? 6 : 4"
                                    :fill="hoveredOccupancyIndex === idx ? '#047857' : '#047857'"
                                    :stroke="hoveredOccupancyIndex === idx ? '#333' : '#fff'"
                                    :stroke-width="hoveredOccupancyIndex === idx ? 3 : 2"
                                    style="cursor: pointer; transition: all 0.2s ease;" />
                            <!-- X-axis labels -->
                            <text v-for="(label, idx) in xAxisLabels" :key="'xl2-' + idx"
                                  :x="xAxisLabelPositions[idx]" y="290"
                                  font-size="10" text-anchor="middle" fill="#666">
                                {{ label }}
                            </text>
                            <!-- Y-axis labels (0-100%) -->
                            <text x="35" y="35" font-size="11" font-weight="bold" text-anchor="end" fill="#333">100%</text>
                            <text x="35" y="155" font-size="11" text-anchor="end" fill="#666">50%</text>
                            <text x="35" y="275" font-size="11" text-anchor="end" fill="#666">0%</text>
                        </svg>
                        <!-- Tooltip -->
                        <div v-if="hoveredOccupancyIndex !== null && props.charts?.occupancy?.[hoveredOccupancyIndex]"
                             class="absolute bg-gray-900 text-white px-3 py-2 rounded shadow-lg text-sm whitespace-nowrap"
                             :style="{
                                 left: tooltipX + 'px',
                                 top: '10px',
                                 pointerEvents: 'none'
                             }">
                            <div>{{ props.charts.occupancy[hoveredOccupancyIndex].date }}</div>
                            <div class="font-semibold">{{ props.charts.occupancy[hoveredOccupancyIndex].rate.toFixed(1) }}%</div>
                        </div>
                        <div v-else class="flex items-center justify-center h-full text-gray-500">
                            <p>No occupancy data available</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Recent Activities -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Recent System Activities</h3>
                <div class="space-y-3">
                    <div v-if="recentActivities && recentActivities.length === 0"
                         class="text-center py-8"
                         :style="{ color: themeColors.textTertiary }">
                        <p class="text-sm">No recent activities</p>
                    </div>
                    <div v-for="activity in recentActivities" :key="activity.id"
                         class="flex items-start space-x-3 p-3 rounded-lg transition-colors"
                         :style="{
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }"
                         @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                         @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                        <div class="flex-shrink-0">
                            <component :is="getActivityIcon(activity.type)"
                                      class="h-5 w-5"
                                      :style="{ color: themeColors.primary }" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textPrimary }">{{ activity.description }}</p>
                            <p class="text-xs"
                               :style="{ color: themeColors.textTertiary }">{{ formatDateTime(activity.created_at) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Status -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">System Status</h3>
                <div class="space-y-4">
                    <div v-for="(status, system) in systemStatus" :key="system"
                         class="flex items-center justify-between">
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textPrimary }">{{ system }}</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :class="getStatusColor(status)">
                            {{ status }}
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
                    <Link :href="route('admin.users.create')"
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
                        Add New User
                    </Link>
                    <Link :href="route('admin.rooms.index')"
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
                        Manage Rooms
                    </Link>
                    <Link :href="route('admin.reports.index')"
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
                    <Link :href="route('admin.settings')"
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
                        <CogIcon class="h-4 w-4 mr-2" />
                        System Settings
                    </Link>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="shadow rounded-lg p-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <h3 class="text-lg font-semibold mb-4"
                :style="{ color: themeColors.textPrimary }">Performance Metrics</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold"
                         :style="{ color: themeColors.primary }">{{ performanceMetricsDisplay.avgOccupancy }}%</div>
                    <div class="text-sm"
                         :style="{ color: themeColors.textTertiary }">Average Occupancy</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold"
                         :style="{ color: themeColors.textPrimary }">{{ formatCurrency(performanceMetricsDisplay.avgDailyRate) }}</div>
                    <div class="text-sm"
                         :style="{ color: themeColors.textTertiary }">Average Daily Rate</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold"
                         :style="{ color: themeColors.textPrimary }">{{ formatCurrency(performanceMetricsDisplay.revPAR) }}</div>
                    <div class="text-sm"
                         :style="{ color: themeColors.textTertiary }">Revenue per Available Room</div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, onMounted, ref, nextTick } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
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
    CogIcon,
    UserPlusIcon,
    BuildingOfficeIcon,
    DocumentTextIcon,
    WrenchScrewdriverIcon,
    BellIcon,
    CheckCircleIcon,
    XCircleIcon,
    ExclamationCircleIcon,
    CalendarDaysIcon,
    ChevronRightIcon
} from '@heroicons/vue/24/outline'
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js'
// Register Chart.js components
ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
)

let revenueChartInstance = null
let occupancyChartInstance = null

// Interactive chart state
const hoveredRevenueIndex = ref(null)
const hoveredOccupancyIndex = ref(null)
const tooltipX = ref(0)

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
    navigation: Array,
    stats: Object,
    charts: Object,
    recentActivities: Array,
    alerts: Object,
    systemStatus: Object,
    performanceMetrics: Object,
})
const revenueChart = ref(null)
const occupancyChart = ref(null)
const revenueChartPeriod = ref('30')
const revenueDataKey = ref(0)
const occupancyDataKey = ref(0)

// Computed properties for chart rendering
const xAxisLabels = computed(() => {
    if (!props.charts?.revenue?.length) return []
    const labels = props.charts.revenue.map(item => item.date || '')
    // Show every 5th label to avoid crowding, or all if less than 10
    if (labels.length < 10) return labels
    return labels.filter((_, idx) => idx % 5 === 0 || idx === labels.length - 1)
})

const xAxisLabelPositions = computed(() => {
    const labels = xAxisLabels.value
    if (labels.length < 2) {
        return labels.map(() => 310) // Center if only one label
    }
    const spacing = 540 / (labels.length - 1)
    return labels.map((_, idx) => 40 + (idx * spacing))
})

const revenueChartPoints = computed(() => {
    if (!props.charts?.revenue?.length) return []
    const maxValue = Math.max(...props.charts.revenue.map(item => parseFloat(item.amount) || 0))
    const minValue = 0
    const range = maxValue || 1
    const dataLength = props.charts.revenue.length
    const pointSpacing = dataLength > 1 ? 540 / (dataLength - 1) : 0

    return props.charts.revenue.map((item, idx) => {
        const x = 40 + (idx * pointSpacing)
        const value = parseFloat(item.amount) || 0
        const y = 270 - ((value - minValue) / range) * 240
        return { x, y, value }
    })
})

const revenuePoints = computed(() => {
    return revenueChartPoints.value.map(p => `${p.x},${p.y}`).join(' ')
})

const revenueFill = computed(() => {
    if (!revenueChartPoints.value.length) return ''
    const points = revenueChartPoints.value
    return [
        `40,270`,
        ...points.map(p => `${p.x},${p.y}`),
        `${points[points.length - 1].x},270`
    ].join(' ')
})

const yAxisMaxLabel = computed(() => {
    if (!props.charts?.revenue?.length) return '$0'
    const maxValue = Math.max(...props.charts.revenue.map(item => parseFloat(item.amount) || 0))
    return formatCurrency(maxValue)
})

const yAxisMidLabel = computed(() => {
    if (!props.charts?.revenue?.length) return '$0'
    const maxValue = Math.max(...props.charts.revenue.map(item => parseFloat(item.amount) || 0))
    return formatCurrency(maxValue / 2)
})

const formatRevenueLabelMax = computed(() => {
    if (!props.charts?.revenue?.length) return '$0'
    const maxValue = Math.max(...props.charts.revenue.map(item => parseFloat(item.amount) || 0))
    if (maxValue >= 1000000) {
        return '$' + (maxValue / 1000000).toFixed(1) + 'M'
    } else if (maxValue >= 1000) {
        return '$' + (maxValue / 1000).toFixed(1) + 'K'
    }
    return formatCurrency(maxValue)
})

const formatRevenueLabelMid = computed(() => {
    if (!props.charts?.revenue?.length) return '$0'
    const maxValue = Math.max(...props.charts.revenue.map(item => parseFloat(item.amount) || 0))
    const midValue = maxValue / 2
    if (maxValue >= 1000000) {
        return '$' + (midValue / 1000000).toFixed(1) + 'M'
    } else if (maxValue >= 1000) {
        return '$' + (midValue / 1000).toFixed(1) + 'K'
    }
    return formatCurrency(midValue)
})

const occupancyChartPoints = computed(() => {
    if (!props.charts?.occupancy?.length) return []
    const dataLength = props.charts.occupancy.length
    const pointSpacing = dataLength > 1 ? 540 / (dataLength - 1) : 0

    return props.charts.occupancy.map((item, idx) => {
        const x = 40 + (idx * pointSpacing)
        const value = parseFloat(item.rate) || 0
        const y = 270 - (value / 100) * 240
        return { x, y, value }
    })
})

const occupancyPoints = computed(() => {
    return occupancyChartPoints.value.map(p => `${p.x},${p.y}`).join(' ')
})

const occupancyFill = computed(() => {
    if (!occupancyChartPoints.value.length) return ''
    const points = occupancyChartPoints.value
    return [
        `40,270`,
        ...points.map(p => `${p.x},${p.y}`),
        `${points[points.length - 1].x},270`
    ].join(' ')
})

// Dashboard stats - display real data from backend
const dashboardStats = computed(() => {
    return {
        totalRooms: props.stats?.total_rooms ?? 0,
        occupiedRooms: props.stats?.occupied_rooms ?? 0,
        availableRooms: props.stats?.available_rooms ?? 0,
        totalGuests: props.stats?.total_guests ?? 0,
        totalReservations: props.stats?.total_reservations ?? 0,
        todayRevenue: props.stats?.todays_revenue ?? 0,
    }
})

// Quick actions matching reservation page design
const quickActions = computed(() => {
    return [
        { label: 'Add New User', route: route('admin.users.create') },
        { label: 'Manage Rooms', route: route('admin.rooms.index') },
        { label: 'View Reports', route: route('admin.reports.index') },
        { label: 'System Settings', route: route('admin.settings') },
    ]
})

const currentDateTime = computed(() => {
    return new Date().toLocaleString()
})

const hasAlerts = computed(() => {
    return props.alerts && Object.values(props.alerts).some(alert => alert > 0)
})

const keyMetrics = computed(() => {
    return {
        total_rooms: props.stats?.total_rooms || 0,
        occupied_rooms: props.stats?.occupied_rooms || 0,
        todays_revenue: props.stats?.todays_revenue || 0,
        active_users: props.stats?.active_users || 0,
    }
})

const performanceMetricsDisplay = computed(() => {
    return {
        avgOccupancy: props.performanceMetrics?.avgOccupancy || 0,
        avgDailyRate: props.performanceMetrics?.avgDailyRate || 0,
        revPAR: props.performanceMetrics?.revPAR || 0,
    }
})

// Methods
const getStatIcon = (key) => {
    const icons = {
        total_rooms: HomeIcon,
        occupied_rooms: HomeIcon,
        todays_revenue: CurrencyDollarIcon,
        active_users: UserGroupIcon,
    }
    return icons[key] || ChartBarIcon
}

const getStatColor = (key) => {
    // Icons are now styled with text-blue-600 in template
    return ''
}

const getStatTitle = (key) => {
    const titles = {
        total_rooms: 'Total Rooms',
        occupied_rooms: 'Occupied Rooms',
        todays_revenue: "Today's Revenue",
        active_users: 'Active Users',
    }
    return titles[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatStatValue = (key, value) => {
    if (key.includes('revenue') || key.includes('amount')) {
        return formatCurrency(value)
    }
    return value
}

// Chart hover handlers
const handleRevenueHover = (event) => {
    const svg = event.currentTarget
    const rect = svg.getBoundingClientRect()
    const parentRect = svg.parentElement.getBoundingClientRect()

    const mouseX = event.clientX - parentRect.left
    const viewBoxWidth = 600
    const svgWidth = rect.width
    const scaleX = viewBoxWidth / svgWidth
    const viewX = mouseX * scaleX

    // Calculate which data point is nearest
    if (props.charts?.revenue?.length) {
        const chartPointX = viewX - 40
        const pointSpacing = 540 / (props.charts.revenue.length - 1)
        const nearestIdx = Math.round(chartPointX / pointSpacing)

        if (nearestIdx >= 0 && nearestIdx < props.charts.revenue.length) {
            hoveredRevenueIndex.value = nearestIdx
            tooltipX.value = mouseX - 50
        }
    }
}

const handleOccupancyHover = (event) => {
    const svg = event.currentTarget
    const rect = svg.getBoundingClientRect()
    const parentRect = svg.parentElement.getBoundingClientRect()

    const mouseX = event.clientX - parentRect.left
    const viewBoxWidth = 600
    const svgWidth = rect.width
    const scaleX = viewBoxWidth / svgWidth
    const viewX = mouseX * scaleX

    // Calculate which data point is nearest
    if (props.charts?.occupancy?.length) {
        const chartPointX = viewX - 40
        const pointSpacing = 540 / (props.charts.occupancy.length - 1)
        const nearestIdx = Math.round(chartPointX / pointSpacing)

        if (nearestIdx >= 0 && nearestIdx < props.charts.occupancy.length) {
            hoveredOccupancyIndex.value = nearestIdx
            tooltipX.value = mouseX - 50
        }
    }
}

const getAlertTitle = (key) => {
    const titles = {
        maintenance_required: 'Maintenance Required',
        system_errors: 'System Errors',
        pending_approvals: 'Pending Approvals',
        offline_devices: 'Offline Devices',
    }
    return titles[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getActivityIcon = (type) => {
    const icons = {
        user_login: UserGroupIcon,
        reservation: CalendarDaysIcon,
        payment: CurrencyDollarIcon,
        maintenance: WrenchScrewdriverIcon,
        system: CogIcon,
    }
    return icons[type] || BellIcon
}

const getStatusColor = (status) => {
    const colors = {
        'Online': 'bg-green-100 text-green-800',
        'Offline': 'bg-red-100 text-red-800',
        'Warning': 'bg-yellow-100 text-yellow-800',
        'Maintenance': 'bg-blue-100 text-blue-800',
        'Pending': 'bg-amber-100 text-amber-800',
        'Completed': 'bg-green-100 text-green-800',
        'Normal': 'bg-green-100 text-green-800',
    }
    return colors[status] || 'bg-gray-700 text-gray-200'
}

const formatDateTime = (datetime) => {
    return new Date(datetime).toLocaleString()
}

// Chart initialization - using SVG rendering (no Canvas needed)
onMounted(() => {
    initializeCurrencySettings()
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
