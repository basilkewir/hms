<template>
    <DashboardLayout title="Housekeeping Dashboard" :user="user">
        <!-- Dashboard Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Housekeeping Operations</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Maintain room cleanliness and ensure guest comfort.</p>
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
                :style="{ color: themeColors.textPrimary }">Room Status Overview</h2>
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
                            <ClipboardDocumentListIcon class="h-8 w-8"
                                                     :style="{ color: themeColors.danger }" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Rooms to Clean</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: themeColors.textPrimary }">{{ roomStats?.toClean || 0 }}</p>
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
                               :style="{ color: themeColors.textPrimary }">{{ roomStats?.inProgress || 0 }}</p>
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
                               :style="{ color: themeColors.textPrimary }">{{ roomStats?.completed || 0 }}</p>
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
                            <WrenchScrewdriverIcon class="h-8 w-8"
                                                 :style="{ color: themeColors.secondary }" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Maintenance Issues</p>
                            <p class="text-2xl font-bold"
                               :style="{ color: themeColors.textPrimary }">{{ roomStats?.maintenance || 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Tasks & Room Assignments -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- My Room Assignments -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">My Room Assignments</h3>
                    <Link :href="route('housekeeping.time-tracking')"
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
                    <div v-for="room in myRooms || []" :key="room?.id"
                         class="flex items-center justify-between p-3 rounded-lg"
                         :style="getRoomStatusStyle(room?.status)">
                        <div class="flex items-center">
                            <HomeIcon class="h-5 w-5 mr-3"
                                     :style="{ color: themeColors.textTertiary }" />
                            <div>
                                <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textPrimary }">Room {{ room?.number || 'N/A' }}</p>
                                <p class="text-xs"
                                   :style="{ color: themeColors.textTertiary }">{{ room?.type || 'N/A' }} • {{ room?.priority || 'Normal' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <button v-if="room?.status === 'assigned'" 
                                    class="text-xs px-2 py-1 rounded transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.primary,
                                        color: 'white'
                                    }"
                                    @mouseenter="$event.target.style.opacity = '0.8'"
                                    @mouseleave="$event.target.style.opacity = '1'">
                                Start Cleaning
                            </button>
                            <button v-else-if="room?.status === 'in_progress'" 
                                    class="text-xs px-2 py-1 rounded transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.success,
                                        color: 'white'
                                    }"
                                    @mouseenter="$event.target.style.opacity = '0.8'"
                                    @mouseleave="$event.target.style.opacity = '1'">
                                Mark Complete
                            </button>
                            <span v-else class="text-xs px-2 py-1 rounded"
                                  :style="{ 
                                      backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                      color: 'rgb(16, 185, 129)',
                                      border: '1px solid rgb(16, 185, 129)'
                                  }">
                                Completed
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Tasks -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Daily Tasks</h3>
                <div class="space-y-3">
                    <div v-for="task in dailyTasks" :key="task.id"
                         class="flex items-center justify-between p-3 rounded-lg"
                         :style="{ 
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   :checked="task.completed"
                                   class="h-4 w-4 mr-3">
                            <div>
                                <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ task.description }}</p>
                                <p class="text-xs"
                                   :style="{ color: themeColors.textTertiary }">{{ task.location }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                  :style="task.completed ? 'rgba(16, 185, 129, 0.1) rgb(16, 185, 129) 1px solid rgb(16, 185, 129)' : 'rgba(245, 158, 11, 0.1) rgb(245, 158, 11) 1px solid rgb(245, 158, 11)'">
                                {{ task.completed ? 'Done' : 'Pending' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory & Maintenance Reports -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Inventory Status -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Inventory Status</h3>
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
                            <ArchiveBoxIcon class="h-5 w-5 mr-3"
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
                               :style="{ color: getStockColor(item.stock, item.min_stock) }">
                                {{ item.stock }} {{ item.unit }}
                            </p>
                            <p class="text-xs"
                               :style="{ color: themeColors.textTertiary }">Min: {{ item.min_stock }}</p>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <Link :href="route('housekeeping.inventory.request')"
                          class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium rounded transition-colors"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                              color: 'white'
                          }"
                          @mouseenter="$event.target.style.opacity = '0.8'"
                          @mouseleave="$event.target.style.opacity = '1'">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Request Supplies
                    </Link>
                </div>
            </div>

            <!-- Maintenance Reports -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">My Maintenance Reports</h3>
                <div class="space-y-3">
                    <div v-for="report in maintenanceReports" :key="report.id"
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
                                   :style="{ color: themeColors.textPrimary }">{{ report.issue }}</p>
                                <p class="text-xs"
                                   :style="{ color: themeColors.textTertiary }">Room {{ report.room_number }} • {{ formatDate(report.reported_at) }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                  :style="getMaintenanceStatusStyle(report.status)">
                                {{ report.status }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <Link :href="route('housekeeping.maintenance.report')"
                          class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium rounded transition-colors"
                          :style="{ 
                              backgroundColor: themeColors.danger,
                              color: 'white'
                          }"
                          @mouseenter="$event.target.style.opacity = '0.8'"
                          @mouseleave="$event.target.style.opacity = '1'">
                        <ExclamationTriangleIcon class="h-4 w-4 mr-2" />
                        Report Issue
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
    ClipboardDocumentListIcon,
    ClockIcon,
    CheckCircleIcon,
    WrenchScrewdriverIcon,
    HomeIcon,
    ArchiveBoxIcon,
    PlusIcon,
    ExclamationTriangleIcon
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
    roomStats: Object,
    myRooms: Array,
    dailyTasks: Array,
    inventory: Array,
    maintenanceReports: Array,
})

const currentDateTime = computed(() => {
    return new Date().toLocaleString()
})

const getRoomStatusStyle = (status) => {
    const styles = {
        assigned: 'rgba(59, 130, 246, 0.1) rgb(59, 130, 246) 1px solid rgb(59, 130, 246)',
        in_progress: 'rgba(245, 158, 11, 0.1) rgb(245, 158, 11) 1px solid rgb(245, 158, 11)',
        completed: 'rgba(16, 185, 129, 0.1) rgb(16, 185, 129) 1px solid rgb(16, 185, 129)',
    }
    return styles[status] || 'rgba(107, 114, 128, 0.1) rgb(107, 114, 128) 1px solid rgb(107, 114, 128)'
}

const getStockColor = (current, minimum) => {
    if (current <= minimum) return 'rgb(239, 68, 68)'
    if (current <= minimum * 1.5) return 'rgb(245, 158, 11)'
    return 'rgb(16, 185, 129)'
}

const getMaintenanceStatusStyle = (status) => {
    const styles = {
        pending: 'rgba(245, 158, 11, 0.1) rgb(245, 158, 11) 1px solid rgb(245, 158, 11)',
        in_progress: 'rgba(59, 130, 246, 0.1) rgb(59, 130, 246) 1px solid rgb(59, 130, 246)',
        completed: 'rgba(16, 185, 129, 0.1) rgb(16, 185, 129) 1px solid rgb(16, 185, 129)',
        cancelled: 'rgba(239, 68, 68, 0.1) rgb(239, 68, 68) 1px solid rgb(239, 68, 68)',
    }
    return styles[status] || 'rgba(107, 114, 128, 0.1) rgb(107, 114, 128) 1px solid rgb(107, 114, 128)'
}

const formatDate = (date) => {
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
