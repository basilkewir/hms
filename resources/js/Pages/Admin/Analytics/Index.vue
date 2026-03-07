<template>
    <DashboardLayout title="Analytics Dashboard" :user="user">
        <!-- Header -->
        <div class="card bg-kotel-bg-card border border-kotel-border rounded-lg shadow-kotel-card p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-kotel-text-primary">Analytics Dashboard</h1>
                    <p class="text-kotel-text-secondary mt-2">Comprehensive hotel performance analytics and insights.</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="exportAnalytics" 
                            class="btn-purple">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2 inline" />
                        Export Data
                    </button>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="card bg-kotel-bg-card border border-gray-200 rounded-lg shadow-kotel-card p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-kotel-sky-blue/20 rounded-lg flex items-center justify-center mr-4">
                        <ChartBarIcon class="h-6 w-6 text-kotel-sky-blue" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-kotel-text-tertiary">Occupancy Rate</p>
                        <p class="text-2xl font-bold text-kotel-text-primary">{{ analytics.occupancyRate }}%</p>
                        <p class="text-xs" :class="growthColor(analytics.occupancyGrowth)">{{ analytics.occupancyGrowthText }}</p>
                    </div>
                </div>
            </div>
            <div class="card bg-kotel-bg-card border border-gray-200 rounded-lg shadow-kotel-card p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-kotel-green/20 rounded-lg flex items-center justify-center mr-4">
                        <CurrencyDollarIcon class="h-6 w-6 text-kotel-green" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-kotel-text-tertiary">Revenue (This Month)</p>
                        <p class="text-2xl font-bold text-kotel-text-primary">{{ formatCurrency(analytics.revenue) }}</p>
                        <p class="text-xs" :class="growthColor(analytics.revenueGrowth)">{{ analytics.revenueGrowthText }}</p>
                    </div>
                </div>
            </div>
            <div class="card bg-kotel-bg-card border border-gray-200 rounded-lg shadow-kotel-card p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-kotel-purple/20 rounded-lg flex items-center justify-center mr-4">
                        <UserGroupIcon class="h-6 w-6 text-kotel-purple" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-kotel-text-tertiary">Guest Satisfaction</p>
                        <p class="text-2xl font-bold text-kotel-text-primary">{{ analytics.guestSatisfaction }}</p>
                        <p class="text-xs text-kotel-text-tertiary">No data available</p>
                    </div>
                </div>
            </div>
            <div class="card bg-kotel-bg-card border border-gray-200 rounded-lg shadow-kotel-card p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-kotel-orange/20 rounded-lg flex items-center justify-center mr-4">
                        <CalendarDaysIcon class="h-6 w-6 text-kotel-orange" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-kotel-text-tertiary">Avg. Daily Rate (ADR)</p>
                        <p class="text-2xl font-bold text-kotel-text-primary">{{ formatCurrency(analytics.averageDailyRate) }}</p>
                        <p class="text-xs" :class="growthColor(analytics.adrGrowth)">{{ analytics.adrGrowthText }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Revenue Trend -->
            <div class="card bg-kotel-bg-card border border-gray-200 rounded-lg shadow-kotel-card p-6">
                <h3 class="text-lg font-semibold text-kotel-text-primary mb-4">Revenue Breakdown (This Month)</h3>
                <div class="h-64">
                    <canvas ref="revenueChartRef" class="bg-white"></canvas>
                </div>
            </div>

            <!-- Occupancy Trend -->
            <div class="card bg-kotel-bg-card border border-gray-200 rounded-lg shadow-kotel-card p-6">
                <h3 class="text-lg font-semibold text-kotel-text-primary mb-4">Occupancy Trend (Last 7 Days)</h3>
                <div class="h-64">
                    <canvas ref="occupancyChartRef" class="bg-white"></canvas>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Top Performing Rooms -->
            <div class="card bg-kotel-bg-card border border-gray-200 rounded-lg shadow-kotel-card p-6">
                <h3 class="text-lg font-semibold text-kotel-text-primary mb-4">Top Performing Rooms</h3>
                <div class="space-y-3">
                    <div v-for="room in topRooms" :key="room.id" 
                         class="flex items-center justify-between p-3 bg-kotel-gray rounded-lg border border-gray-300">
                        <div>
                            <p class="font-medium text-kotel-text-primary">Room {{ room.number }}</p>
                            <p class="text-sm text-kotel-text-secondary">{{ room.type }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-kotel-green">{{ formatCurrency(room.revenue) }}</p>
                            <p class="text-xs text-kotel-text-tertiary">{{ room.occupancy }}% occupied</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guest Demographics -->
            <div class="card bg-kotel-bg-card border border-gray-200 rounded-lg shadow-kotel-card p-6">
                <h3 class="text-lg font-semibold text-kotel-text-primary mb-4">Guest Demographics</h3>
                <div class="space-y-3">
                    <div v-for="demo in guestDemographics" :key="demo.category"
                         class="flex items-center justify-between">
                        <span class="text-sm text-kotel-text-secondary">{{ demo.category }}</span>
                        <div class="flex items-center">
                            <div class="w-20 bg-gray-300 rounded-full h-2 mr-3">
                                <div class="bg-kotel-sky-blue h-2 rounded-full" :style="{ width: demo.percentage + '%' }"></div>
                            </div>
                            <span class="text-sm font-medium text-kotel-text-primary">{{ demo.percentage }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card bg-kotel-bg-card border border-gray-200 rounded-lg shadow-kotel-card p-6">
                <h3 class="text-lg font-semibold text-kotel-text-primary mb-4">Recent Activity</h3>
                <div class="space-y-3">
                    <div v-for="activity in recentActivity" :key="activity.id"
                         class="flex items-start space-x-3">
                        <div class="w-2 h-2 bg-kotel-sky-blue rounded-full mt-2"></div>
                        <div>
                            <p class="text-sm text-kotel-text-primary">{{ activity.description }}</p>
                            <p class="text-xs text-kotel-text-tertiary">{{ formatTime(activity.timestamp) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Analytics Table -->
        <div class="card bg-kotel-bg-card border border-gray-200 rounded-lg shadow-kotel-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-300">
                <h3 class="text-lg font-medium text-kotel-text-primary">Detailed Analytics</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-kotel-gray-dark">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-kotel-text-tertiary uppercase tracking-wider">
                                Metric
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-kotel-text-tertiary uppercase tracking-wider">
                                Current Period
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-kotel-text-tertiary uppercase tracking-wider">
                                Previous Period
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-kotel-text-tertiary uppercase tracking-wider">
                                Change
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-kotel-text-tertiary uppercase tracking-wider">
                                Trend
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-kotel-bg-card divide-y divide-gray-300">
                        <tr v-for="metric in detailedMetrics" :key="metric.name" class="hover:bg-kotel-gray/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-kotel-text-primary">
                                {{ metric.name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-kotel-text-primary">
                                {{ metric.current }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-kotel-text-primary">
                                {{ metric.previous }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :class="metric.change >= 0 ? 'text-kotel-green' : 'text-kotel-red'">
                                {{ metric.change >= 0 ? '+' : '' }}{{ metric.change }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getTrendColor(metric.trend)">
                                    {{ metric.trend }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'
import {
    DocumentArrowDownIcon,
    ChartBarIcon,
    CurrencyDollarIcon,
    UserGroupIcon,
    ChartPieIcon,
    CalendarDaysIcon
} from '@heroicons/vue/24/outline'
import Chart from 'chart.js/auto'

const props = defineProps({
    user: Object,
    analytics: Object,
    topRooms: Array,
    guestDemographics: Array,
    recentActivity: Array,
    detailedMetrics: Array,
    charts: Object,
})

const analytics = computed(() => props.analytics || {
    occupancyRate: 0,
    revenue: 0,
    guestSatisfaction: 0,
    adr: 0
})

const topRooms = computed(() => props.topRooms || [])

const guestDemographics = computed(() => props.guestDemographics || [])

const recentActivity = computed(() => props.recentActivity || [])

const currencyMetrics = new Set(['Monthly Revenue', 'Avg Daily Rate (ADR)'])

const detailedMetrics = computed(() => {
    if (!props.detailedMetrics) return []
    return props.detailedMetrics.map(metric => {
        const isCurrency = currencyMetrics.has(metric.name)
        return {
            ...metric,
            current: isCurrency && typeof metric.current === 'number'
                ? formatCurrency(metric.current)
                : metric.current,
            previous: isCurrency && typeof metric.previous === 'number'
                ? formatCurrency(metric.previous)
                : metric.previous,
        }
    })
})

const charts = computed(() => props.charts || { occupancy: [], revenue: [] })

const revenueChartRef = ref(null)
const occupancyChartRef = ref(null)
let revenueChartInstance = null
let occupancyChartInstance = null

const growthColor = (pct) => {
    if (pct === null || pct === undefined) return 'text-kotel-text-tertiary'
    return pct >= 0 ? 'text-kotel-green' : 'text-red-500'
}

const getTrendColor = (trend) => {
    const colors = {
        'Increasing': 'badge-green',
        'Decreasing': 'badge-red',
        'Improving': 'badge-blue',
        'Stable': 'badge-gray'
    }
    return colors[trend] || 'badge-gray'
}

const formatTime = (time) => {
    const now = new Date()
    const timeValue = new Date(time)
    const diff = now - timeValue
    const minutes = Math.floor(diff / 60000)
    
    if (minutes < 1) return 'Just now'
    if (minutes < 60) return `${minutes}m ago`
    
    const hours = Math.floor(minutes / 60)
    if (hours < 24) return `${hours}h ago`
    
    const days = Math.floor(hours / 24)
    return `${days}d ago`
}

const exportAnalytics = () => {
    window.location.href = route('admin.analytics.export')
}

const buildRevenueChart = () => {
    if (!revenueChartRef.value) return
    if (revenueChartInstance) revenueChartInstance.destroy()

    const chartData = charts.value.revenue || []
    revenueChartInstance = new Chart(revenueChartRef.value, {
        type: 'bar',
        data: {
            labels: chartData.map(item => item.category),
            datasets: [{
                label: 'Revenue',
                data: chartData.map(item => item.amount),
                backgroundColor: ['#2563eb', '#16a34a', '#f97316'],
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { ticks: { callback: (value) => formatCurrency(value) } }
            }
        }
    })
}

const buildOccupancyChart = () => {
    if (!occupancyChartRef.value) return
    if (occupancyChartInstance) occupancyChartInstance.destroy()

    const chartData = charts.value.occupancy || []
    occupancyChartInstance = new Chart(occupancyChartRef.value, {
        type: 'line',
        data: {
            labels: chartData.map(item => item.date),
            datasets: [{
                label: 'Occupancy %',
                data: chartData.map(item => item.rate),
                borderColor: '#7c3aed',
                backgroundColor: 'rgba(124, 58, 237, 0.2)',
                tension: 0.3,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { max: 100, min: 0 } }
        }
    })
}

onMounted(() => {
    buildRevenueChart()
    buildOccupancyChart()
})

watch(charts, () => {
    buildRevenueChart()
    buildOccupancyChart()
}, { deep: true })
</script>
