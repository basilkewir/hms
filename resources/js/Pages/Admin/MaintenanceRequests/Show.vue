<template>
    <DashboardLayout title="Maintenance Request Details">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-1"
                        :style="{ color: themeColors.textPrimary }">{{ request.title }}</h1>
                    <p class="mt-1" :style="{ color: themeColors.textSecondary }">Request #: {{ request.request_number }}</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('admin.maintenance-requests.index')"
                          class="px-4 py-2 rounded-md transition-colors"
                          :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        Back to List
                    </Link>
                </div>
            </div>

            <!-- Status & Priority Badges -->
            <div class="flex items-center gap-3 mb-6">
                <span class="px-3 py-1 rounded-full text-xs font-semibold" :style="getStatusStyle(request.status)">
                    {{ formatStatus(request.status) }}
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-semibold" :style="getPriorityStyle(request.priority)">
                    {{ formatStatus(request.priority) }} Priority
                </span>
            </div>

            <!-- Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="rounded-lg p-4 border"
                     :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                    <h3 class="font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Request Information</h3>
                    <div class="space-y-2 text-sm">
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Category:</span> {{ formatCategory(request.category) }}</div>
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Priority:</span> {{ formatStatus(request.priority) }}</div>
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Status:</span> {{ formatStatus(request.status) }}</div>
                        <div v-if="request.room" :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Room:</span> {{ request.room.room_number }}</div>
                        <div v-if="request.location" :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Location:</span> {{ request.location }}</div>
                        <div v-if="request.location_details" :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Location Details:</span> {{ request.location_details }}</div>
                    </div>
                </div>
                <div class="rounded-lg p-4 border"
                     :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                    <h3 class="font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Assignment & Timeline</h3>
                    <div class="space-y-2 text-sm">
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Reported By:</span> {{ request.reported_by?.name || 'N/A' }}</div>
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Assigned To:</span> {{ request.assigned_to?.name || 'Unassigned' }}</div>
                        <div v-if="request.department" :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Department:</span> {{ request.department.name }}</div>
                        <div :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Reported:</span> {{ formatDateTime(request.reported_at) }}</div>
                        <div v-if="request.assigned_at" :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Assigned:</span> {{ formatDateTime(request.assigned_at) }}</div>
                        <div v-if="request.started_at" :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Started:</span> {{ formatDateTime(request.started_at) }}</div>
                        <div v-if="request.resolved_at" :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Resolved:</span> {{ formatDateTime(request.resolved_at) }}</div>
                        <div v-if="request.scheduled_date" :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Scheduled:</span> {{ request.scheduled_date }} {{ request.scheduled_time || '' }}</div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="mb-6 rounded-lg p-4 border"
                 :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <h3 class="font-semibold mb-2" :style="{ color: themeColors.textPrimary }">Description</h3>
                <p class="text-sm whitespace-pre-wrap" :style="{ color: themeColors.textSecondary }">{{ request.description }}</p>
            </div>

            <!-- Resolution Info -->
            <div v-if="request.resolution_notes || request.work_performed || request.cost"
                 class="mb-6 rounded-lg p-4 border"
                 :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <h3 class="font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Resolution Details</h3>
                <div class="space-y-2 text-sm">
                    <div v-if="request.resolution_notes" :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Notes:</span> {{ request.resolution_notes }}</div>
                    <div v-if="request.work_performed" :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Work Performed:</span> {{ request.work_performed }}</div>
                    <div v-if="request.cost" :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Cost:</span> ${{ Number(request.cost).toFixed(2) }}</div>
                    <div v-if="request.resolved_by" :style="{ color: themeColors.textSecondary }"><span class="font-medium" :style="{ color: themeColors.textPrimary }">Resolved By:</span> {{ request.resolved_by.name }}</div>
                </div>
            </div>

            <!-- Follow Up -->
            <div v-if="request.requires_follow_up"
                 class="mb-6 rounded-lg p-4 border"
                 :style="{ backgroundColor: themeColors.warning + '10', borderColor: themeColors.warning, borderWidth: '1px', borderStyle: 'solid' }">
                <h3 class="font-semibold mb-2" :style="{ color: themeColors.warning }">Follow-Up Required</h3>
                <p v-if="request.follow_up_notes" class="text-sm" :style="{ color: themeColors.textSecondary }">{{ request.follow_up_notes }}</p>
            </div>

            <!-- Photos -->
            <div v-if="request.photos && request.photos.length > 0" class="mb-6">
                <h3 class="font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Photos</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <img v-for="(photo, index) in request.photos" :key="index" :src="photo"
                         class="w-full h-32 object-cover rounded-lg border"
                         :style="{ borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
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
loadTheme()

const props = defineProps({
    user: Object,
    request: Object,
})

const getStatusStyle = (status) => {
    const styles = {
        open: { backgroundColor: `var(--kotel-warning)`, color: 'white' },
        assigned: { backgroundColor: `var(--kotel-primary)`, color: 'white' },
        in_progress: { backgroundColor: `var(--kotel-primary)`, color: 'white' },
        on_hold: { backgroundColor: `var(--kotel-secondary)`, color: 'white' },
        resolved: { backgroundColor: `var(--kotel-success)`, color: 'white' },
        closed: { backgroundColor: `var(--kotel-secondary)`, color: 'white' },
        cancelled: { backgroundColor: `var(--kotel-danger)`, color: 'white' },
    }
    return styles[status] || styles['open']
}

const getPriorityStyle = (priority) => {
    const styles = {
        low: { backgroundColor: `var(--kotel-secondary)`, color: 'white' },
        normal: { backgroundColor: `var(--kotel-primary)`, color: 'white' },
        high: { backgroundColor: `var(--kotel-warning)`, color: 'white' },
        urgent: { backgroundColor: `var(--kotel-danger)`, color: 'white' },
    }
    return styles[priority] || styles['normal']
}

const formatDateTime = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const formatCategory = (category) => {
    if (!category) return 'N/A'
    return category.charAt(0).toUpperCase() + category.slice(1)
}

const formatStatus = (status) => {
    if (!status) return 'N/A'
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}
</script>
