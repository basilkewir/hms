<template>
    <div class="space-y-8">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="(value, key) in quickStats" :key="key"
                 class="shadow rounded-lg p-6 transition-all hover:shadow-lg cursor-pointer"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border
                 }"
                 @click="$emit('navigate', getStatRoute(key))">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textSecondary }">{{ getStatLabel(key) }}</p>
                        <p class="text-2xl font-bold mt-2"
                           :style="{ color: themeColors.textPrimary }">{{ value }}</p>
                    </div>
                    <div class="p-3 rounded-full"
                         :style="{ backgroundColor: `${getStatColor(key)}20` }">
                        <component :is="getStatIcon(key)" class="h-6 w-6"
                                   :style="{ color: getStatColor(key) }" />
                    </div>
                </div>
            </div>
        </div>

        <!-- My Requests -->
        <div class="shadow rounded-lg p-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold"
                    :style="{ color: themeColors.textPrimary }">My Requests</h2>
                <button class="text-sm font-medium transition-colors"
                        :style="{ color: themeColors.primary }"
                        @click="$emit('navigate', '/maintenance/requests')">
                    View All
                </button>
            </div>
            <div class="space-y-4">
                <div v-for="request in myRequests" :key="request.id"
                     class="flex items-center p-4 rounded-lg transition-colors hover:bg-gray-50"
                     :style="{ backgroundColor: themeColors.card }">
                    <div class="p-2 rounded-full mr-4"
                         :style="{ backgroundColor: `${getRequestPriorityColor(request.priority)}20` }">
                        <WrenchScrewdriverIcon class="h-5 w-5"
                                              :style="{ color: getRequestPriorityColor(request.priority) }" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium"
                           :style="{ color: themeColors.textPrimary }">{{ request.title }}</p>
                        <p class="text-xs"
                           :style="{ color: themeColors.textTertiary }">Room {{ request.room_number || 'N/A' }} • {{ request.category }}</p>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full"
                          :style="{
                              backgroundColor: `${getRequestStatusColor(request.status)}20`,
                              color: getRequestStatusColor(request.status)
                          }">
                        {{ request.status }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Equipment Status -->
        <div class="shadow rounded-lg p-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <h2 class="text-lg font-semibold mb-4"
                :style="{ color: themeColors.textPrimary }">Equipment Status</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="(status, key) in equipmentStatus" :key="key"
                     class="p-4 rounded-lg text-center cursor-pointer transition-colors hover:opacity-80"
                     :style="{
                         backgroundColor: `${getEquipmentStatusColor(key)}20`,
                         borderStyle: 'solid',
                         borderWidth: '1px',
                         borderColor: getEquipmentStatusColor(key)
                     }"
                     @click="$emit('navigate', '/maintenance/devices')">
                    <p class="text-2xl font-bold"
                       :style="{ color: getEquipmentStatusColor(key) }">{{ status }}</p>
                    <p class="text-sm mt-1"
                       :style="{ color: themeColors.textSecondary }">{{ formatEquipmentStatusKey(key) }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import {
    WrenchScrewdriverIcon,
    ClipboardDocumentListIcon,
    CheckCircleIcon,
    ClockIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    data: Object,
    themeColors: Object
})

const emit = defineEmits(['navigate'])

// Extract data from props
const quickStats = ref(props.data?.quickStats || {})
const myRequests = ref(props.data?.myRequests || [])
const equipmentStatus = ref(props.data?.equipmentStatus || {})

// Methods
const getStatLabel = (key) => {
    const labels = {
        pendingRequests: 'Pending Requests',
        inProgress: 'In Progress',
        completedToday: 'Completed Today',
        urgentRequests: 'Urgent'
    }
    return labels[key] || key
}

const getStatIcon = (key) => {
    const icons = {
        pendingRequests: ClipboardDocumentListIcon,
        inProgress: ClockIcon,
        completedToday: CheckCircleIcon,
        urgentRequests: ExclamationTriangleIcon
    }
    return icons[key] || ClipboardDocumentListIcon
}

const getStatColor = (key) => {
    const colors = {
        pendingRequests: '#F59E0B',
        inProgress: '#3B82F6',
        completedToday: '#10B981',
        urgentRequests: '#EF4444'
    }
    return colors[key] || '#6B7280'
}

const getStatRoute = (key) => {
    const routes = {
        pendingRequests: '/maintenance/requests',
        inProgress: '/maintenance/requests',
        completedToday: '/maintenance/requests',
        urgentRequests: '/maintenance/requests'
    }
    return routes[key] || '/maintenance/dashboard'
}

const getRequestPriorityColor = (priority) => {
    const colors = {
        high: '#EF4444',
        normal: '#F59E0B',
        low: '#10B981'
    }
    return colors[priority] || '#6B7280'
}

const getRequestStatusColor = (status) => {
    const colors = {
        pending: '#F59E0B',
        in_progress: '#3B82F6',
        completed: '#10B981'
    }
    return colors[status] || '#6B7280'
}

const formatEquipmentStatusKey = (key) => {
    const formatted = {
        operational: 'Operational',
        maintenance: 'Maintenance',
        offline: 'Offline',
        error: 'Error'
    }
    return formatted[key] || key
}

const getEquipmentStatusColor = (key) => {
    const colors = {
        operational: '#10B981',
        maintenance: '#F59E0B',
        offline: '#6B7280',
        error: '#EF4444'
    }
    return colors[key] || '#6B7280'
}

// Watch for data changes
watch(() => props.data, (newData) => {
    if (newData) {
        quickStats.value = newData.quickStats || {}
        myRequests.value = newData.myRequests || []
        equipmentStatus.value = newData.equipmentStatus || {}
    }
}, { deep: true })
</script>
