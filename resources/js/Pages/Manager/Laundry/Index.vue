<template>
    <DashboardLayout title="Laundry" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-6" :style="{ backgroundColor: themeColors.card }">
            <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Laundry</h1>
            <p class="text-sm" :style="{ color: themeColors.textSecondary }">Manage laundry orders and items.</p>
        </div>

        <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <p class="text-sm" :style="{ color: themeColors.textSecondary }">This is the Manager view for Laundry. If Admin laundry pages exist, functionality can be extended here to reuse the same endpoints and UI patterns.</p>
            <div class="mt-4 flex gap-3">
                <Link href="/admin/laundry" class="text-sm hover:underline" :style="{ color: themeColors.primary }">Open Admin Laundry</Link>
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
