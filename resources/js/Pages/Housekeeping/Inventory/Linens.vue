<template>
    <DashboardLayout title="Linens Inventory" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-6" :style="{ backgroundColor: themeColors.card }">
            <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Linens Inventory</h1>
            <p class="mt-1 text-sm" :style="{ color: themeColors.textSecondary }">Track sheets, pillowcases, towels, and other linens.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div v-for="item in items" :key="item.name" class="rounded-lg shadow p-5" :style="{ backgroundColor: themeColors.card }">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ item.name }}</span>
                    <span class="text-xs px-2 py-1 rounded-full" :class="item.stock > 10 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">{{ item.stock > 10 ? 'In Stock' : 'Low Stock' }}</span>
                </div>
                <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ item.stock }}</p>
                <p class="text-xs mt-1" :style="{ color: themeColors.textSecondary }">units available</p>
            </div>
            <div v-if="!items || items.length === 0" class="col-span-3 text-center py-10 text-sm" :style="{ color: themeColors.textSecondary }">No linen inventory data found.</div>
        </div>
    </DashboardLayout>
</template>
<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useTheme } from '@/Composables/useTheme';
const { themeColors } = useTheme();
defineProps({ user: Object, navigation: Array, items: { type: Array, default: () => [] } });
</script>
