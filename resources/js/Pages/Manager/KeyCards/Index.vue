<template>
    <DashboardLayout title="Key Cards" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-6" :style="{ backgroundColor: themeColors.card }">
            <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Key Cards</h1>
            <p class="text-sm" :style="{ color: themeColors.textSecondary }">Manage room key cards and assignments.</p>
        </div>

        <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <p class="text-sm" :style="{ color: themeColors.textSecondary }">Temporary Manager view for Key Cards. This can be enhanced to reuse Front Desk flows directly if desired.</p>
            <div class="mt-4 flex gap-3">
                <Link href="/front-desk/key-cards" class="text-sm hover:underline" :style="{ color: themeColors.primary }">Open Front Desk Key Cards</Link>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme'

const { currentTheme } = useTheme()
const themeColors = computed(() => ({
    background: 'var(--kotel-background)',
    card: 'var(--kotel-card)',
    border: 'var(--kotel-border)',
    textPrimary: 'var(--kotel-text-primary)',
    textSecondary: 'var(--kotel-text-secondary)',
    primary: 'var(--kotel-primary)'
}))

defineProps({
    user: Object,
    navigation: Array,
})
</script>
