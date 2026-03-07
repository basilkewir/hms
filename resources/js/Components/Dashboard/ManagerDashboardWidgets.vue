<template>
    <div class="space-y-8">
        <!-- Today's Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="stat in todaysStats" :key="stat.key"
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

        <!-- Room & Staff Status -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Room Status -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <h2 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Room Status</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div v-for="(status, key) in roomStatus" :key="key"
                         class="p-4 rounded-lg text-center"
                         :style="{
                             backgroundColor: `${getRoomStatusColor(key)}20`,
                             borderStyle: 'solid',
                             borderWidth: '1px',
                             borderColor: getRoomStatusColor(key)
                         }">
                        <p class="text-2xl font-bold"
                           :style="{ color: getRoomStatusColor(key) }">{{ status }}</p>
                        <p class="text-sm mt-1"
                           :style="{ color: themeColors.textSecondary }">{{ formatRoomStatusKey(key) }}</p>
                    </div>
                </div>
            </div>

            <!-- Staff Status -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <h2 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Staff Status</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div v-for="(status, key) in staffStatus" :key="key"
                         class="p-4 rounded-lg text-center"
                         :style="{
                             backgroundColor: `${getStaffStatusColor(key)}20`,
                             borderStyle: 'solid',
                             borderWidth: '1px',
                             borderColor: getStaffStatusColor(key)
                         }">
                        <p class="text-2xl font-bold"
                           :style="{ color: getStaffStatusColor(key) }">{{ status }}</p>
                        <p class="text-sm mt-1"
                           :style="{ color: themeColors.textSecondary }">{{ formatStaffStatusKey(key) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Occupancy Chart -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <h2 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Weekly Occupancy</h2>
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
                <h2 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Today's Revenue Breakdown</h2>
                <div class="h-64">
                    <canvas ref="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Check-ins & Pending Tasks -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Check-ins -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Recent Check-ins</h2>
                    <button class="text-sm font-medium transition-colors"
                            :style="{ color: themeColors.primary }"
                            @click="$emit('navigate', '/manager/reservations/checkins')">
                        View All
                    </button>
                </div>
                <div class="space-y-4">
                    <div v-for="checkin in recentCheckins" :key="checkin.id"
                         class="flex items-center p-4 rounded-lg transition-colors hover:bg-gray-50"
                         :style="{ backgroundColor: themeColors.card }">
                        <div class="p-2 rounded-full mr-4"
                             :style="{ backgroundColor: `${themeColors.primary}20` }">
                            <UserIcon class="h-5 w-5"
                                     :style="{ color: themeColors.primary }" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textPrimary }">{{ checkin.guest_name }}</p>
                            <p class="text-xs"
                               :style="{ color: themeColors.textTertiary }">Room {{ checkin.room_number }} • {{ formatTime(checkin.check_in_time) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Tasks -->
            <div class="shadow rounded-lg p-6"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Pending Tasks</h2>
                    <button class="text-sm font-medium transition-colors"
                            :style="{ color: themeColors.primary }"
                            @click="$emit('navigate', '/manager/housekeeping-tasks')">
                        View All
                    </button>
                </div>
                <div class="space-y-4">
                    <div v-for="task in pendingTasks" :key="task.id"
                         class="flex items-center p-4 rounded-lg transition-colors hover:bg-gray-50"
                         :style="{ backgroundColor: themeColors.card }">
                        <div class="p-2 rounded-full mr-4"
                             :style="{ backgroundColor: `${getTaskPriorityColor(task.priority)}20` }">
                            <component :is="getTaskIcon(task.type)" class="h-5 w-5"
                                       :style="{ color: getTaskPriorityColor(task.priority) }" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textPrimary }">{{ task.title }}</p>
                            <p class="text-xs"
                               :style="{ color: themeColors.textTertiary }">{{ task.department }} • {{ task.priority }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import {
    UserIcon,
    HomeIcon,
    CurrencyDollarIcon,
    ArrowRightOnRectangleIcon,
    ArrowLeftOnRectangleIcon,
    WrenchScrewdriverIcon,
    SparklesIcon,
    ClipboardDocumentListIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    data: Object,
    themeColors: Object
})

const emit = defineEmits(['navigate'])

const occupancyChart = ref(null)
const revenueChart = ref(null)
let occupancyChartInstance = null
let revenueChartInstance = null

// Extract data from props
const todaysStats = ref(props.data?.todaysStats || {})
const roomStatus = ref(props.data?.roomStatus || {})
const staffStatus = ref(props.data?.staffStatus || {})
const recentCheckins = ref(props.data?.recentCheckins || [])
const pendingTasks = ref(props.data?.pendingTasks || {})
const charts = ref(props.data?.charts || {})

// Methods
const formatNumber = (num) => {
    if (typeof num !== 'number') return '0'
    return num.toLocaleString()
}

const formatTime = (timeString) => {
    if (!timeString) return ''
    const date = new Date(timeString)
    return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    })
}

const formatRoomStatusKey = (key) => {
    const formatted = {
        available: 'Available',
        occupied: 'Occupied',
        cleaning: 'Cleaning',
        maintenance: 'Maintenance'
    }
    return formatted[key] || key
}

const formatStaffStatusKey = (key) => {
    const formatted = {
        onDuty: 'On Duty',
        offDuty: 'Off Duty',
        onBreak: 'On Break',
        lateAbsent: 'Late/Absent'
    }
    return formatted[key] || key
}

const getRoomStatusColor = (key) => {
    const colors = {
        available: '#10B981',
        occupied: '#3B82F6',
        cleaning: '#F59E0B',
        maintenance: '#EF4444'
    }
    return colors[key] || '#6B7280'
}

const getStaffStatusColor = (key) => {
    const colors = {
        onDuty: '#10B981',
        offDuty: '#6B7280',
        onBreak: '#F59E0B',
        lateAbsent: '#EF4444'
    }
    return colors[key] || '#6B7280'
}

const getTaskPriorityColor = (priority) => {
    const colors = {
        high: '#EF4444',
        normal: '#F59E0B',
        low: '#10B981'
    }
    return colors[priority] || '#6B7280'
}

const getTaskIcon = (type) => {
    const icons = {
        housekeeping: SparklesIcon,
        maintenance: WrenchScrewdriverIcon
    }
    return icons[type] || ClipboardDocumentListIcon
}

const getStatRoute = (key) => {
    const routes = {
        arrivals: '/manager/reservations/checkins',
        departures: '/manager/reservations/checkouts',
        occupancyRate: '/manager/rooms/status',
        revenue: '/manager/reports'
    }
    return routes[key] || '/manager/dashboard'
}

const initializeCharts = () => {
    // Initialize Occupancy Chart
    if (occupancyChart.value && charts.value?.occupancy) {
        const ctx = occupancyChart.value.getContext('2d')
        // Chart.js implementation would go here
        ctx.fillStyle = props.themeColors.textSecondary
        ctx.font = '14px Arial'
        ctx.textAlign = 'center'
        ctx.fillText('Occupancy Chart (Chart.js integration needed)', ctx.canvas.width / 2, ctx.canvas.height / 2)
    }

    // Initialize Revenue Chart
    if (revenueChart.value && charts.value?.revenue) {
        const ctx = revenueChart.value.getContext('2d')
        // Chart.js implementation would go here
        ctx.fillStyle = props.themeColors.textSecondary
        ctx.font = '14px Arial'
        ctx.textAlign = 'center'
        ctx.fillText('Revenue Chart (Chart.js integration needed)', ctx.canvas.width / 2, ctx.canvas.height / 2)
    }
}

const destroyCharts = () => {
    if (occupancyChartInstance) {
        occupancyChartInstance.destroy()
        occupancyChartInstance = null
    }
    if (revenueChartInstance) {
        revenueChartInstance.destroy()
        revenueChartInstance = null
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
        todaysStats.value = newData.todaysStats || {}
        roomStatus.value = newData.roomStatus || {}
        staffStatus.value = newData.staffStatus || {}
        recentCheckins.value = newData.recentCheckins || []
        pendingTasks.value = newData.pendingTasks || {}
        charts.value = newData.charts || {}
        
        destroyCharts()
        setTimeout(() => {
            initializeCharts()
        }, 100)
    }
}, { deep: true })
</script>
