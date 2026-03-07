<template>
    <DashboardLayout title="Edit Membership" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Edit Membership</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Update loyalty tier details.</p>
                </div>
                <Link :href="route('admin.memberships.index')"
                      class="px-4 py-2 rounded-md transition-colors"
                      :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                    Back to Memberships
                </Link>
            </div>
        </div>

        <!-- Form Card -->
        <div class="shadow rounded-lg p-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Tier Name *</label>
                        <input v-model="form.name" type="text" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                        <div v-if="form.errors.name" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.name }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Minimum Points *</label>
                        <input v-model.number="form.min_points" type="number" min="0" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                        <div v-if="form.errors.min_points" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.min_points }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Discount Percentage (%)</label>
                        <input v-model.number="form.discount_percentage" type="number" min="0" max="100" step="0.01"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                        <div v-if="form.errors.discount_percentage" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.discount_percentage }}</div>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" v-model="form.is_active" class="mr-2">
                            <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">Active</span>
                        </label>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Description</label>
                        <textarea v-model="form.description" rows="3"
                                  class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                  :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <Link :href="route('admin.memberships.index')"
                          class="px-6 py-2 rounded-md transition-colors"
                          :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                            class="px-6 py-2 rounded-md transition-colors disabled:opacity-50"
                            :style="{ backgroundColor: themeColors.primary, color: '#fff' }"
                            @mouseenter="!form.processing && ($event.target.style.backgroundColor = themeColors.hover)"
                            @mouseleave="!form.processing && ($event.target.style.backgroundColor = themeColors.primary)">
                        {{ form.processing ? 'Updating...' : 'Update Membership' }}
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'

const props = defineProps({
    user: Object,
    membership: Object,
})

const navigation = computed(() => {
    const userRole = props.user?.roles?.[0]?.name || 'admin'
    return getNavigationForRole(userRole)
})

const { currentTheme, loadTheme } = useTheme()
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
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.02)'
}))
loadTheme()

const form = useForm({
    name: props.membership?.name || '',
    min_points: props.membership?.min_points || 0,
    discount_percentage: props.membership?.discount_percentage ?? 0,
    is_active: props.membership?.is_active ?? true,
    description: props.membership?.description || '',
})

const submit = () => {
    form.put(route('admin.memberships.update', props.membership.id))
}
</script>
