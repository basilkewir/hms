<template>
    <DashboardLayout title="Create Customer Group" :user="user" :navigation="navigation">
        <!-- Header -->
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
             class="shadow rounded-lg p-6 mb-8 border">
            <div class="flex items-center justify-between">
                <div>
                    <h1 :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">Create New Customer Group</h1>
                    <p :style="{ color: themeColors.textSecondary }" class="mt-2">Add a new customer group with discount rates.</p>
                </div>
                <Link :href="route('admin.customer-groups.index')" 
                      :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }"
                      class="px-4 py-2 rounded-md border hover:opacity-80">
                    Back to Groups
                </Link>
            </div>
        </div>

        <!-- Form -->
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
             class="shadow rounded-lg p-6 border">
            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label :style="{ color: themeColors.textPrimary }" class="block text-sm font-medium mb-2">Group Name *</label>
                        <input type="text" v-model="form.name" required
                               :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background, color: themeColors.textPrimary }"
                               class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                    </div>
                    <div>
                        <label :style="{ color: themeColors.textPrimary }" class="block text-sm font-medium mb-2">Discount Percentage (%) *</label>
                        <input type="number" v-model.number="form.discount_percentage" required min="0" max="100" step="0.01"
                               :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background, color: themeColors.textPrimary }"
                               class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <div v-if="form.errors.discount_percentage" class="mt-1 text-sm text-red-600">{{ form.errors.discount_percentage }}</div>
                        <p :style="{ color: themeColors.textTertiary }" class="mt-1 text-xs">Enter the discount percentage (0-100)</p>
                    </div>
                    <div class="md:col-span-2">
                        <label :style="{ color: themeColors.textPrimary }" class="block text-sm font-medium mb-2">Description</label>
                        <textarea v-model="form.description" rows="3"
                                  :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background, color: themeColors.textPrimary }"
                                  class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" v-model="form.is_active" class="mr-2">
                            <span :style="{ color: themeColors.textPrimary }" class="text-sm font-medium">Active</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <Link :href="route('admin.customer-groups.index')" 
                          :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }"
                          class="px-6 py-2 rounded-md border hover:opacity-80">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                            :style="{ backgroundColor: themeColors.primary, color: '#000' }"
                            class="px-6 py-2 rounded-md hover:opacity-90 disabled:opacity-50">
                        {{ form.processing ? 'Creating...' : 'Create Group' }}
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
})

const { currentTheme } = useTheme()
const navigation = computed(() => getNavigationForRole('admin'))

const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    primary: `var(--kotel-primary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
}))

const form = useForm({
    name: '',
    description: '',
    discount_percentage: 0,
    is_active: true,
})

const submit = () => {
    form.post(route('admin.customer-groups.store'))
}
</script>
