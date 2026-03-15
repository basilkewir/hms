<template>
    <DashboardLayout title="Maintenance Categories">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Maintenance Categories</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Manage maintenance request categories.</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route(`${routePrefix}.maintenance-categories.create`)"
                          class="px-4 py-2 rounded-md transition-colors text-sm font-medium text-white"
                          :style="{ backgroundColor: themeColors.primary }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4 mr-2 inline" />
                        Add Category
                    </Link>
                </div>
            </div>
        </div>

        <!-- Categories Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="category in categories" :key="category.id"
                 class="rounded-lg shadow p-6 transition-shadow border"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                             :style="{ backgroundColor: (category.color || themeColors.primary) + '30', border: '1px solid ' + (category.color || themeColors.primary) }">
                            <WrenchIcon class="h-5 w-5" :style="{ color: category.color }" />
                        </div>
                        <h3 class="text-lg font-semibold"
                            :style="{ color: themeColors.textPrimary }">{{ category.name }}</h3>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :style="getStatusStyle(category.is_active)">
                        {{ category.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="space-y-2 mb-4">
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">{{ category.description || 'No description' }}</p>
                    <div class="flex items-center text-sm"
                         :style="{ color: themeColors.textTertiary }">
                        <TagIcon class="h-4 w-4 mr-1" />
                        <span>Code: {{ category.code }}</span>
                    </div>
                    <div class="flex items-center text-sm"
                         :style="{ color: themeColors.textTertiary }">
                        <QueueListIcon class="h-4 w-4 mr-1" />
                        <span>Sort Order: {{ category.sort_order }}</span>
                    </div>
                </div>

                <div class="flex space-x-2">
                    <Link :href="route(`${routePrefix}.maintenance-categories.edit`, category.id)"
                          class="flex-1 px-3 py-2 rounded text-sm transition-colors text-center"
                          :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        Edit
                    </Link>
                    <button @click="toggleActive(category)"
                            class="flex-1 px-3 py-2 rounded text-sm transition-colors border"
                            :style="category.is_active ? {
                                backgroundColor: 'rgba(239, 68, 68, 0.2)',
                                color: '#fca5a5',
                                borderColor: 'rgba(248, 113, 113, 0.5)'
                            } : {
                                backgroundColor: 'rgba(16, 185, 129, 0.2)',
                                color: '#86efac',
                                borderColor: 'rgba(52, 211, 153, 0.5)'
                            }">
                        {{ category.is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    <button @click="deleteCategory(category)"
                            class="flex-1 px-3 py-2 rounded text-sm transition-colors border"
                            :style="{ 
                                backgroundColor: 'rgba(220, 38, 38, 0.9)',
                                color: 'white',
                                borderColor: 'rgba(248, 113, 113, 0.5)',
                                borderWidth: '1px',
                                borderStyle: 'solid'
                            }">
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="categories.length === 0"
             class="shadow rounded-lg p-12 text-center"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <WrenchIcon class="h-12 w-12 mx-auto text-kotel-sky-blue/50 mb-4"
                       :style="{ color: themeColors.textTertiary }" />
            <h3 class="text-lg font-medium mb-2"
                :style="{ color: themeColors.textPrimary }">No Categories Found</h3>
            <p class="mb-6"
               :style="{ color: themeColors.textSecondary }">Get started by creating your first maintenance category.</p>
            <Link :href="route(`${routePrefix}.maintenance-categories.create`)"
                  class="px-4 py-2 rounded-md transition-colors inline-block text-white"
                  :style="{ backgroundColor: themeColors.primary }"
                  @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                  @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                <PlusIcon class="h-4 w-4 mr-2 inline" />
                Add Category
            </Link>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import {
    PlusIcon,
    WrenchIcon,
    TagIcon,
    QueueListIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    categories: Array,
    flash: Object,
    routePrefix: { type: String, default: 'admin' },
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

const getStatusStyle = (isActive) => {
    if (isActive) {
        return {
            backgroundColor: 'rgba(16, 185, 129, 0.5)',
            color: '#86efac',
            border: '1px solid rgba(52, 211, 153, 0.5)'
        }
    }
    return {
        backgroundColor: 'rgba(153, 27, 27, 0.5)',
        color: '#fca5a5',
        border: '1px solid rgba(248, 113, 113, 0.5)'
    }
}

const toggleActive = (category) => {
    router.post(route(`${props.routePrefix}.maintenance-categories.toggle-active`, category.id))
}

const deleteCategory = (category) => {
    if (confirm(`Delete category: ${category.name}? This action cannot be undone.`)) {
        router.delete(route(`${props.routePrefix}.maintenance-categories.destroy`, category.id), {
            onSuccess: () => {
                // Success message will be handled by flash
            },
            onError: () => {
                alert('Failed to delete category.')
            }
        })
    }
}
</script>
