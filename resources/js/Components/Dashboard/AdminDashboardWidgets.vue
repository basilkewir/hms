<template>
    <div class="space-y-8">
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="stat in stats" :key="stat.key"
                 class="shadow rounded-lg p-6 transition-all hover:shadow-lg cursor-pointer"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }"
                 @click="$emit('navigate', getStatRoute(stat.key))">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">{{ stat.label }}</p>
                        <p class="text-2xl font-bold mt-2"
                           :style="{ color: themeColors.textPrimary }">{{ formatNumber(stat.value) }}</p>
                        <p v-if="stat.change" class="text-sm mt-2"
                           :class="stat.change > 0 ? 'text-green-600' : 'text-red-600'">
                            {{ stat.change > 0 ? '↑' : '↓' }} {{ Math.abs(stat.change) }}%
                        </p>
                    </div>
                    <div class="p-3 rounded-full"
                         :style="{ backgroundColor: `${stat.color}20` }">
                        <component :is="stat.icon" class="h-6 w-6"
                                   :style="{ color: stat.color }" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Revenue Chart -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <h2 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Revenue Overview</h2>
                <div class="h-64">
                    <canvas ref="revenueChart"></canvas>
                </div>
            </div>

            <!-- Occupancy Chart -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <h2 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Occupancy Rate</h2>
                <div class="h-64">
                    <canvas ref="occupancyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="shadow rounded-lg p-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold"
                    :style="{ color: themeColors.textPrimary }">Recent Activities</h2>
                <button class="text-sm font-medium transition-colors"
                        :style="{ color: themeColors.primary }"
                        @click="$emit('navigate', '/admin/reports')">
                    View All
                </button>
            </div>
            <div class="space-y-4">
                <div v-for="activity in recentActivities" :key="activity.id"
                     class="flex items-center p-4 rounded-lg transition-colors hover:bg-gray-50"
                     :style="{ backgroundColor: themeColors.card }">
                    <div class="p-2 rounded-full mr-4"
                         :style="{ backgroundColor: `${getActivityColor(activity.type)}20` }">
                        <component :is="getActivityIcon(activity.type)" class="h-5 w-5"
                                   :style="{ color: getActivityColor(activity.type) }" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textPrimary }">{{ activity.description }}</p>
                        <p class="text-xs"
                           :style="{ color: themeColors.textTertiary }">{{ formatDate(activity.created_at) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <h3 class="text-sm font-medium mb-2"
                    :style="{ color: themeColors.textSecondary }">Average Occupancy</h3>
                <p class="text-2xl font-bold"
                   :style="{ color: themeColors.textPrimary }">{{ performanceMetrics.avgOccupancy }}%</p>
            </div>
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <h3 class="text-sm font-medium mb-2"
                    :style="{ color: themeColors.textSecondary }">Average Daily Rate</h3>
                <p class="text-2xl font-bold"
                   :style="{ color: themeColors.textPrimary }">${{ formatNumber(performanceMetrics.avgDailyRate) }}</p>
            </div>
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <h3 class="text-sm font-medium mb-2"
                    :style="{ color: themeColors.textSecondary }">Revenue Per Available Room</h3>
                <p class="text-2xl font-bold"
                   :style="{ color: themeColors.textPrimary }">${{ formatNumber(performanceMetrics.revPAR) }}</p>
            </div>
        </div>

        <!-- System Status -->
        <div class="shadow rounded-lg p-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <h2 class="text-lg font-semibold mb-4"
                :style="{ color: themeColors.textPrimary }">System Status</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div v-for="(status, system) in systemStatus" :key="system"
                     class="flex items-center p-3 rounded-lg"
                     :style="{
                         backgroundColor: status === 'Online' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)',
                         borderStyle: 'solid',
                         borderWidth: '1px',
                         borderColor: status === 'Online' ? '#10B981' : '#EF4444'
                     }">
                    <div class="w-2 h-2 rounded-full mr-3"
                         :style="{ backgroundColor: status === 'Online' ? '#10B981' : '#EF4444' }"></div>
                    <span class="text-sm font-medium"
                          :style="{ color: status === 'Online' ? '#10B981' : '#EF4444' }">
                        {{ system }}: {{ status }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import {
    HomeIcon,
    UserGroupIcon,
    CurrencyDollarIcon,
    CalendarDaysIcon,
    DocumentTextIcon,
    UserIcon,
    CreditCardIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    data: Object,
    themeColors: Object
})

const emit = defineEmits(['navigate'])

const revenueChart = ref(null)
const occupancyChart = ref(null)
let revenueChartInstance = null
let occupancyChartInstance = null

// Extract data from props
const stats = ref(props.data?.stats || {})
const charts = ref(props.data?.charts || {})
const recentActivities = ref(props.data?.recentActivities || [])
const performanceMetrics = ref(props.data?.performanceMetrics || {})
const systemStatus = ref(props.data?.systemStatus || {})

// Methods
const formatNumber = (num) => {
    if (typeof num !== 'number') return '0'
    return num.toLocaleString()
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getActivityIcon = (type) => {
    const icons = {
        reservation: CalendarDaysIcon,
        payment: CurrencyDollarIcon,
        user: UserIcon,
        system: DocumentTextIcon
    }
    return icons[type] || DocumentTextIcon
}

const getActivityColor = (type) => {
    const colors = {
        reservation: '#3B82F6',
        payment: '#10B981',
        user: '#F59E0B',
        system: '#EF4444'
    }
    return colors[type] || '#6B7280'
}

const getStatRoute = (key) => {
    const routes = {
        total_rooms: '/admin/rooms',
        occupied_rooms: '/admin/rooms',
        available_rooms: '/admin/rooms',
        total_guests: '/admin/guests',
        total_reservations: '/admin/reservations',
        todays_revenue: '/admin/reports/revenue',
        active_users: '/admin/users'
    }
    return routes[key] || '/admin/dashboard'
}

const initializeCharts = () => {
    // Initialize Revenue Chart
    if (revenueChart.value && charts.value?.revenue) {
        const ctx = revenueChart.value.getContext('2d')
        // Chart.js implementation would go here
        // For now, we'll just show a placeholder message
        ctx.fillStyle = props.themeColors.textSecondary
        ctx.font = '14px Arial'
        ctx.textAlign = 'center'
        ctx.fillText('Revenue Chart (Chart.js integration needed)', ctx.canvas.width / 2, ctx.canvas.height / 2)
    }

    // Initialize Occupancy Chart
    if (occupancyChart.value && charts.value?.occupancy) {
        const ctx = occupancyChart.value.getContext('2d')
        // Chart.js implementation would go here
        // For now, we'll just show a placeholder message
        ctx.fillStyle = props.themeColors.textSecondary
        ctx.font = '14px Arial'
        ctx.textAlign = 'center'
        ctx.fillText('Occupancy Chart (Chart.js integration needed)', ctx.canvas.width / 2, ctx.canvas.height / 2)
    }
}

const destroyCharts = () => {
    if (revenueChartInstance) {
        revenueChartInstance.destroy()
        revenueChartInstance = null
    }
    if (occupancyChartInstance) {
        occupancyChartInstance.destroy()
        occupancyChartInstance = null
    }
}

// Lifecycle hooks
onMounted(() => {
    setTimeout(() => {
        initializeCharts()
    }, 100)
})

onUnmounted(() => {
    destroyCharts()
})

// Watch for data changes
watch(() => props.data, (newData) => {
    if (newData) {
        stats.value = newData.stats || {}
        charts.value = newData.charts || {}
        recentActivities.value = newData.recentActivities || []
        performanceMetrics.value = newData.performanceMetrics || {}
        systemStatus.value = newData.systemStatus || {}
        
        destroyCharts()
        setTimeout(() => {
            initializeCharts()
        }, 100)
    }
}, { deep: true })
</script>
