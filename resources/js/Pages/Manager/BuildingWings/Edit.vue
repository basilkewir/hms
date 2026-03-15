<template>
    <DashboardLayout title="Edit Building Wing" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-8"
            :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Edit Building Wing</h1>
                    <p class="text-sm"
                        :style="{ color: themeColors.textSecondary }">Update building wing information and availability.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.building-wings.index')"
                        class="px-4 py-2 rounded-md transition-colors font-medium"
                        :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border, borderWidth: '1px' }">
                        Back
                    </Link>
                </div>
            </div>
        </div>

        <div class="rounded-lg border shadow-sm p-6"
            :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                            :style="{ color: themeColors.textPrimary }">Name *</label>
                        <input v-model="form.name" type="text" required
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                            :style="{ color: themeColors.textPrimary }">Code</label>
                        <input v-model="form.code" type="text"
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2"
                            :style="{ color: themeColors.textPrimary }">Description</label>
                        <textarea v-model="form.description" rows="3"
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                            :style="{ color: themeColors.textPrimary }">Sort Order</label>
                        <input v-model="form.sort_order" type="number"
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                    </div>

                    <div class="flex items-center">
                        <input v-model="form.is_active" type="checkbox" class="mr-2"
                            :style="{ accentColor: themeColors.primary }">
                        <label class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">Active</label>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" :disabled="form.processing"
                        class="px-4 py-2 rounded-md transition-colors font-medium text-white disabled:opacity-50"
                        :style="{ backgroundColor: themeColors.primary }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        Update Building Wing
                    </button>
                    <Link :href="route('admin.building-wings.index')"
                        class="px-4 py-2 rounded-md transition-colors font-medium"
                        :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border, borderWidth: '1px' }">
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
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
    wing: Object,
})

const navigation = computed(() => getNavigationForRole('admin'))

const form = useForm({
    name: props.wing.name,
    code: props.wing.code || '',
    description: props.wing.description || '',
    is_active: props.wing.is_active,
    sort_order: props.wing.sort_order || 0,
})

const submit = () => {
    form.put(route('admin.building-wings.update', props.wing.id))
}
</script>
