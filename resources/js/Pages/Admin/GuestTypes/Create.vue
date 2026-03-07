<template>
    <DashboardLayout title="Create Guest Type" :user="user" :navigation="navigation">
        <!-- Header -->
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
             class="shadow rounded-lg p-6 mb-8 border">
            <div class="flex items-center justify-between">
                <div>
                    <h1 :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">Create New Guest Type</h1>
                    <p :style="{ color: themeColors.textSecondary }" class="mt-2">Add a new guest type with configurations.</p>
                </div>
                <Link :href="route('admin.guest-types.index')" 
                      :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }"
                      class="px-4 py-2 rounded-md border hover:opacity-80">
                    Back to Guest Types
                </Link>
            </div>
        </div>

        <!-- Form -->
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
             class="shadow rounded-lg p-6 border">
            <form @submit.prevent="submit">
                <div class="space-y-6">
                    <div>
                        <label :style="{ color: themeColors.textPrimary }" class="block text-sm font-medium mb-2">Name *</label>
                        <input v-model="form.name" type="text" required 
                               :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background, color: themeColors.textPrimary }"
                               class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                    </div>
                    <div>
                        <label :style="{ color: themeColors.textPrimary }" class="block text-sm font-medium mb-2">Code</label>
                        <input v-model="form.code" type="text" 
                               :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background, color: themeColors.textPrimary }"
                               class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="e.g., VIP, CORP, REG">
                        <div v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</div>
                    </div>
                    <div>
                        <label :style="{ color: themeColors.textPrimary }" class="block text-sm font-medium mb-2">Description</label>
                        <textarea v-model="form.description" rows="3"
                                  :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background, color: themeColors.textPrimary }"
                                  class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label :style="{ color: themeColors.textPrimary }" class="block text-sm font-medium mb-2">Color</label>
                            <div class="flex items-center gap-3">
                                <input v-model="form.color" type="color" 
                                       class="h-10 w-20 border rounded cursor-pointer"
                                       :style="{ borderColor: themeColors.border }">
                                <input v-model="form.color" type="text" 
                                       placeholder="#000000"
                                       :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background, color: themeColors.textPrimary }"
                                       class="flex-1 border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <p :style="{ color: themeColors.textTertiary }" class="mt-1 text-xs">Color for UI display</p>
                        </div>
                        <div>
                            <label :style="{ color: themeColors.textPrimary }" class="block text-sm font-medium mb-2">Discount Percentage</label>
                            <input v-model.number="form.discount_percentage" type="number" step="0.01" min="0" max="100"
                                   :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background, color: themeColors.textPrimary }"
                                   class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p :style="{ color: themeColors.textTertiary }" class="mt-1 text-xs">Discount percentage for this guest type (0-100)</p>
                        </div>
                    </div>
                    <div>
                        <label :style="{ color: themeColors.textPrimary }" class="block text-sm font-medium mb-2">Sort Order</label>
                        <input v-model.number="form.sort_order" type="number" 
                               :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background, color: themeColors.textPrimary }"
                               class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center">
                        <input v-model="form.is_active" type="checkbox" class="mr-2">
                        <label :style="{ color: themeColors.textPrimary }" class="text-sm font-medium">Active</label>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="submit" :disabled="form.processing" 
                            :style="{ backgroundColor: themeColors.primary, color: '#000' }"
                            class="px-4 py-2 rounded hover:opacity-90 disabled:opacity-50">
                        Create Guest Type
                    </button>
                    <Link :href="route('admin.guest-types.index')" 
                          :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }"
                          class="px-4 py-2 rounded border hover:opacity-80">
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
}))

const form = useForm({
    name: '',
    code: '',
    description: '',
    color: '#3B82F6',
    discount_percentage: 0,
    is_active: true,
    sort_order: 0,
})

const submit = () => {
    form.post(route('admin.guest-types.store'))
}
</script>
