<template>
    <DashboardLayout title="My Schedule" :user="user">
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">My Schedule</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">View-only shifts assigned by Admin/Manager for this week.</p>
                </div>
                <div class="text-sm" :style="{ color: themeColors.textSecondary }">
                    Week of {{ weekStart }}
                </div>
            </div>
        </div>

        <div class="shadow rounded-lg overflow-hidden border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Weekly Schedule</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Day
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Shift
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Time
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(day, index) in schedule"
                            :key="index"
                            class="transition-colors"
                            :style="hoveredRow === index ? { backgroundColor: themeColors.hover } : {}"
                            @mouseenter="hoveredRow = index"
                            @mouseleave="hoveredRow = null"
                        >
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                {{ day.day }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ day.date }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ day.shift_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textPrimary }">
                                <span v-if="day.start && day.end">
                                    {{ day.start }} - {{ day.end }}
                                </span>
                                <span v-else :style="{ color: themeColors.textSecondary }">Off</span>
                            </td>
                        </tr>
                        <tr v-if="!schedule?.length">
                            <td colspan="4" class="px-6 py-10 text-center text-sm" :style="{ color: themeColors.textSecondary }">
                                No assigned shifts found for this week.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme'

const props = defineProps({
    user: Object,
    weekStart: String,
    schedule: Array,
})

const hoveredRow = ref(null)

const { currentTheme, loadTheme } = useTheme()
loadTheme()

const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)'
}))
</script>
