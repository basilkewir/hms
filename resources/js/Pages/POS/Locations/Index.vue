<template>
    <DashboardLayout title="POS Locations" :user="user" :navigation="navigation">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">📍 POS Locations</h1>
                    <p class="text-sm mt-1" :style="{ color: themeColors.textSecondary }">Manage warehouse locations and stock distribution points</p>
                </div>
                <Link :href="route('pos.locations.create')" class="px-4 py-2 rounded-md font-medium" style="background-color: var(--kotel-primary); color: #000000;">
                    + Add Location
                </Link>
            </div>

            <!-- Locations Table -->
            <div class="rounded-lg shadow-sm overflow-hidden" :style="{ backgroundColor: themeColors.card }">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead :style="{ borderColor: themeColors.border, borderBottomWidth: '1px' }">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Warehouse</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Actions</th>
                            </tr>
                        </thead>
                        <tbody :style="{ borderColor: themeColors.border }">
                            <tr v-for="location in locations.data" :key="location.id" :style="{ borderBottomWidth: '1px', borderColor: themeColors.border }">
                                <td class="px-6 py-4" :style="{ color: themeColors.textPrimary }">{{ location.name }}</td>
                                <td class="px-6 py-4" :style="{ color: themeColors.textSecondary }">
                                    <span class="px-2 py-1 rounded text-xs font-medium" :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)', color: themeColors.primary }">
                                        {{ location.type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4" :style="{ color: themeColors.textSecondary }">{{ location.warehouse?.name || 'N/A' }}</td>
                                <td class="px-6 py-4">
                                    <span v-if="location.is_active" class="px-2 py-1 rounded text-xs font-medium" :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)', color: '#22c55e' }">
                                        Active
                                    </span>
                                    <span v-else class="px-2 py-1 rounded text-xs font-medium" :style="{ backgroundColor: 'rgba(239, 68, 68, 0.1)', color: '#ef4444' }">
                                        Inactive
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <Link :href="route('pos.locations.edit', location.id)" class="px-3 py-1 text-sm rounded" style="background-color: var(--kotel-primary); color: #000000;">
                                            Edit
                                        </Link>
                                        <button @click="deleteLocation(location.id)" class="px-3 py-1 text-sm rounded" :style="{ backgroundColor: '#ef4444', color: '#ffffff' }">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { useTheme } from '@/Composables/useTheme'

const props = defineProps({
    user: Object,
    navigation: Object,
    locations: Object
})

const { themeColors: themeColorsObj } = useTheme()

const themeColors = computed(() => ({
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    primary: `var(--kotel-primary)`,
    success: `var(--kotel-success)`
}))

const deleteLocation = (id) => {
    if (confirm('Are you sure you want to delete this location?')) {
        router.delete(route('pos.locations.destroy', id))
    }
}
</script>
