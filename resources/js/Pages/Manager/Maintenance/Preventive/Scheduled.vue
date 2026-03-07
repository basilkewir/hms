<template>
    <DashboardLayout title="Preventive Maintenance">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Preventive Maintenance</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Scheduled preventive maintenance tasks and planning.</p>
                </div>
                <Link :href="route('admin.maintenance')"
                      class="px-4 py-2 rounded-md transition-colors font-medium"
                      :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                    Back
                </Link>
            </div>
        </div>

        <div class="shadow rounded-lg p-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                This page is ready for the preventive maintenance schedule UI.
            </p>
            <p class="text-sm mt-2" :style="{ color: themeColors.textTertiary }">
                Next step: connect scheduled tasks data and add filters (date range, assignee, status).
            </p>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'

const props = defineProps({
    user: Object,
    navigation: Array,
})

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
</script>
