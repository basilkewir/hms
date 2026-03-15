<template>
    <DashboardLayout title="Devices">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Devices</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Manage hotel devices and integrations.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <Link :href="route('maintenance.iptv.devices')" class="rounded-lg p-6 border shadow-sm transition-colors"
                  :style="cardStyle"
                  @mouseenter="$event.target.style.backgroundColor = themeColors.background"
                  @mouseleave="$event.target.style.backgroundColor = themeColors.card">
                <h2 class="text-lg font-medium mb-2" :style="{ color: themeColors.textPrimary }">IPTV Devices</h2>
                <p class="text-sm" :style="{ color: themeColors.textSecondary }">View and manage IPTV devices.</p>
            </Link>

            <div class="rounded-lg p-6 border shadow-sm" :style="cardStyle">
                <h2 class="text-lg font-medium mb-2" :style="{ color: themeColors.textPrimary }">Coming Soon</h2>
                <p class="text-sm" :style="{ color: themeColors.textSecondary }">More device modules can be added here.</p>
            </div>
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

const cardStyle = computed(() => ({
    backgroundColor: themeColors.value.card,
    borderColor: themeColors.value.border,
    borderStyle: 'solid',
    borderWidth: '1px'
}))
</script>
