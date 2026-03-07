<template>
    <DashboardLayout title="Maintenance Dashboard" :user="user">
        <!-- Dashboard Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Maintenance Operations</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Keep hotel facilities and IPTV systems running smoothly.</p>
                </div>
                <div class="text-right">
                    <p class="text-sm"
                       :style="{ color: themeColors.textTertiary }">{{ currentDateTime }}</p>
                    <p class="text-lg font-semibold"
                       :style="{ color: themeColors.textPrimary }">{{ user.full_name }}</p>
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
                :style="{ color: themeColors.textPrimary }">Work Orders Overview</h2>
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
                            <ExclamationTriangleIcon class="h-8 w-8"
                                                   :style="{ color: themeColors.danger }" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Open Orders</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: themeColors.textPrimary }">{{ workOrderStats.open }}</p>
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
                            <ClockIcon class="h-8 w-8"
                                     :style="{ color: themeColors.warning }" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">In Progress</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: themeColors.textPrimary }">{{ workOrderStats.inProgress }}</p>
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
                            <CheckCircleIcon class="h-8 w-8"
                                            :style="{ color: themeColors.success }" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Completed Today</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: themeColors.textPrimary }">{{ workOrderStats.completedToday }}</p>
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
                            <TvIcon class="h-8 w-8"
                                   :style="{ color: themeColors.primary }" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">IPTV Devices Online</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: themeColors.textPrimary }">{{ iptvStats.onlineDevices }}/{{ iptvStats.totalDevices }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Work Orders & IPTV Status -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- My Work Orders -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">My Work Orders</h3>
                    <Link :href="route('maintenance.time-tracking')"
                          class="text-sm px-3 py-1 rounded transition-colors"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                              color: 'white'
                          }"
                          @mouseenter="$event.target.style.opacity = '0.8'"
                          @mouseleave="$event.target.style.opacity = '1'">
                        Clock In/Out
                    </Link>
                </div>
                <div class="space-y-3">
                    <div v-for="order in myWorkOrders" :key="order.id"
                         class="flex items-center justify-between p-3 rounded-lg"
                         :style="getWorkOrderStyle(order.priority)">
                        <div class="flex items-center">
                            <component :is="getWorkOrderIcon(order.type)" 
                                      class="h-5 w-5 mr-3"
                                      :style="{ color: themeColors.textTertiary }" />
                            <div>
                                <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ order.title }}</p>
                                <p class="text-xs"
                                   :style="{ color: themeColors.textTertiary }">{{ order.location }} • {{ order.priority }} priority</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <button v-if="order.status === 'assigned'" 
                                    class="text-xs px-2 py-1 rounded transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.primary,
                                        color: 'white'
                                    }"
                                    @mouseenter="$event.target.style.opacity = '0.8'"
                                    @mouseleave="$event.target.style.opacity = '1'">
                                Start Work
                            </button>
                            <button v-else-if="order.status === 'in_progress'" 
                                    class="text-xs px-2 py-1 rounded transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.success,
                                        color: 'white'
                                    }"
                                    @mouseenter="$event.target.style.opacity = '0.8'"
                                    @mouseleave="$event.target.style.opacity = '1'">
                                Complete
                            </button>
                            <span v-else class="text-xs px-2 py-1 rounded"
                                  :style="{ 
                                      backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                      color: 'rgb(16, 185, 129)',
                                      border: '1px solid rgb(16, 185, 129)'
                                  }">
                                Done
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- IPTV Device Status -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">IPTV Device Status</h3>
                <div class="space-y-3">
                    <div v-for="device in iptvDevices" :key="device.id"
                         class="flex items-center justify-between p-3 rounded-lg"
                         :style="{ 
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full mr-3"
                                 :style="{ 
                                     backgroundColor: device.online ? 'rgb(16, 185, 129)' : 'rgb(239, 68, 68)'
                                 }"></div>
                            <div>
                                <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textPrimary }">Room {{ device.room_number }}</p>
                                <p class="text-xs"
                                   :style="{ color: themeColors.textTertiary }">{{ device.device_type }} • {{ device.ip_address }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                  :style="device.online ? 'rgba(16, 185, 129, 0.1) rgb(16, 185, 129) 1px solid rgb(16, 185, 129)' : 'rgba(239, 68, 68, 0.1) rgb(239, 68, 68) 1px solid rgb(239, 68, 68)'">
                                {{ device.online ? 'Online' : 'Offline' }}
                            </span>
                            <p class="text-xs mt-1"
                               :style="{ color: themeColors.textTertiary }">{{ device.last_seen }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preventive Maintenance & Tools -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Scheduled Maintenance -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Scheduled Maintenance</h3>
                <div class="space-y-3">
                    <div v-for="task in scheduledTasks" :key="task.id"
                         class="flex items-center justify-between p-3 rounded-lg"
                         :style="{ 
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="flex items-center">
                            <CalendarIcon class="h-5 w-5 mr-3"
                                        :style="{ color: themeColors.textTertiary }" />
                            <div>
                                <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ task.description }}</p>
                                <p class="text-xs"
                                   :style="{ color: themeColors.textTertiary }">{{ task.equipment }} • Due: {{ formatDate(task.due_date) }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                  :style="getTaskStatusStyle(task.status)">
                                {{ task.status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tools & Inventory -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Tools & Parts Inventory</h3>
                <div class="space-y-3">
                    <div v-for="item in inventory" :key="item.id"
                         class="flex items-center justify-between p-3 rounded-lg"
                         :style="{ 
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="flex items-center">
                            <WrenchScrewdriverIcon class="h-5 w-5 mr-3"
                                                 :style="{ color: themeColors.textTertiary }" />
                            <div>
                                <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ item.name }}</p>
                                <p class="text-xs"
                                   :style="{ color: themeColors.textTertiary }">{{ item.category }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium"
                               :style="{ color: getStockColor(item.quantity, item.min_quantity) }">
                                {{ item.quantity }}
                            </p>
                            <p class="text-xs"
                               :style="{ color: themeColors.textTertiary }">Min: {{ item.min_quantity }}</p>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <Link :href="route('maintenance.inventory.request')"
                          class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium rounded transition-colors"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                              color: 'white'
                          }"
                          @mouseenter="$event.target.style.opacity = '0.8'"
                          @mouseleave="$event.target.style.opacity = '1'">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Request Parts
                    </Link>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import {
    ExclamationTriangleIcon,
    ClockIcon,
    CheckCircleIcon,
    TvIcon,
    WrenchScrewdriverIcon,
    CalendarIcon,
    PlusIcon,
    BoltIcon,
    CogIcon
} from '@heroicons/vue/24/outline'

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
    workOrderStats: Object,
    iptvStats: Object,
    myWorkOrders: Array,
    iptvDevices: Array,
    scheduledTasks: Array,
    inventory: Array,
})

const workOrderStats = computed(() => props.workOrderStats || { open: 0, inProgress: 0, completedToday: 0 })
const iptvStats = computed(() => props.iptvStats || { onlineDevices: 0, totalDevices: 0 })
const myWorkOrders = computed(() => props.myWorkOrders || [])
const iptvDevices = computed(() => props.iptvDevices || [])
const scheduledTasks = computed(() => props.scheduledTasks || [])
const inventory = computed(() => props.inventory || [])

const currentDateTime = computed(() => {
    return new Date().toLocaleString()
})

const getWorkOrderStyle = (priority) => {
    const styles = {
        high: 'rgba(239, 68, 68, 0.1) rgb(239, 68, 68) 1px solid rgb(239, 68, 68)',
        medium: 'rgba(245, 158, 11, 0.1) rgb(245, 158, 11) 1px solid rgb(245, 158, 11)',
        low: 'rgba(16, 185, 129, 0.1) rgb(16, 185, 129) 1px solid rgb(16, 185, 129)',
    }
    return styles[priority] || 'rgba(107, 114, 128, 0.1) rgb(107, 114, 128) 1px solid rgb(107, 114, 128)'
}

const getWorkOrderIcon = (type) => {
    const icons = {
        electrical: BoltIcon,
        plumbing: WrenchScrewdriverIcon,
        hvac: CogIcon,
        iptv: TvIcon,
        general: WrenchScrewdriverIcon,
    }
    return icons[type] || WrenchScrewdriverIcon
}

const getTaskStatusStyle = (status) => {
    const styles = {
        pending: 'rgba(245, 158, 11, 0.1) rgb(245, 158, 11) 1px solid rgb(245, 158, 11)',
        overdue: 'rgba(239, 68, 68, 0.1) rgb(239, 68, 68) 1px solid rgb(239, 68, 68)',
        completed: 'rgba(16, 185, 129, 0.1) rgb(16, 185, 129) 1px solid rgb(16, 185, 129)',
    }
    return styles[status] || 'rgba(107, 114, 128, 0.1) rgb(107, 114, 128) 1px solid rgb(107, 114, 128)'
}

const getStockColor = (current, minimum) => {
    if (current <= minimum) return 'rgb(239, 68, 68)'
    if (current <= minimum * 1.5) return 'rgb(245, 158, 11)'
    return 'rgb(16, 185, 129)'
}

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString()
}
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
