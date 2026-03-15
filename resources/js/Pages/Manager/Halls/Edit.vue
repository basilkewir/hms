<template>
    <DashboardLayout title="Edit Hall" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Edit Hall</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">{{ hall?.name || 'Hall' }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route(`${routePrefix}.halls.show`, hall.id)"
                        class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                        :style="{ backgroundColor: themeColors.primary }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.primary"
                    >
                        View
                    </Link>
                    <Link
                        :href="route(`${routePrefix}.halls.index`)"
                        class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                        :style="{ backgroundColor: themeColors.warning }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.warning"
                    >
                        Back
                    </Link>
                </div>
            </div>
        </div>

        <div class="rounded-lg overflow-hidden shadow border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Hall Details</h3>
            </div>

            <form class="p-6" @submit.prevent="submit">
                <div v-if="form.hasErrors" class="mb-6 p-4 rounded-md border" :style="{ borderColor: themeColors.danger, backgroundColor: themeColors.danger + '10', color: themeColors.textPrimary }">
                    <p class="font-medium">Please fix the errors below.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Name</label>
                        <input v-model="form.name" type="text" class="w-full rounded-md border px-3 py-2" :style="inputStyle" />
                        <div v-if="form.errors.name" class="text-sm mt-1" :style="{ color: themeColors.danger }">{{ form.errors.name }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Code</label>
                        <input v-model="form.code" type="text" class="w-full rounded-md border px-3 py-2" :style="inputStyle" />
                        <div v-if="form.errors.code" class="text-sm mt-1" :style="{ color: themeColors.danger }">{{ form.errors.code }}</div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Description</label>
                        <textarea v-model="form.description" rows="3" class="w-full rounded-md border px-3 py-2" :style="inputStyle"></textarea>
                        <div v-if="form.errors.description" class="text-sm mt-1" :style="{ color: themeColors.danger }">{{ form.errors.description }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Capacity</label>
                        <input v-model.number="form.capacity" type="number" min="1" step="1" class="w-full rounded-md border px-3 py-2" :style="inputStyle" />
                        <div v-if="form.errors.capacity" class="text-sm mt-1" :style="{ color: themeColors.danger }">{{ form.errors.capacity }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Base Price</label>
                        <input v-model.number="form.base_price" type="number" min="0" step="0.01" class="w-full rounded-md border px-3 py-2" :style="inputStyle" />
                        <div v-if="form.errors.base_price" class="text-sm mt-1" :style="{ color: themeColors.danger }">{{ form.errors.base_price }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Size</label>
                        <select v-model="form.size" class="w-full rounded-md border px-3 py-2" :style="inputStyle">
                            <option value="small">Small</option>
                            <option value="medium">Medium</option>
                            <option value="large">Large</option>
                        </select>
                        <div v-if="form.errors.size" class="text-sm mt-1" :style="{ color: themeColors.danger }">{{ form.errors.size }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Type</label>
                        <select v-model="form.type" class="w-full rounded-md border px-3 py-2" :style="inputStyle">
                            <option value="conference">Conference</option>
                            <option value="banquet">Banquet</option>
                            <option value="meeting">Meeting</option>
                        </select>
                        <div v-if="form.errors.type" class="text-sm mt-1" :style="{ color: themeColors.danger }">{{ form.errors.type }}</div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" v-model="form.is_active" />
                            <span :style="{ color: themeColors.textPrimary }">Active</span>
                        </label>
                        <div v-if="form.errors.is_active" class="text-sm mt-1" :style="{ color: themeColors.danger }">{{ form.errors.is_active }}</div>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-end gap-3">
                    <button type="button" @click="resetToOriginal" class="px-4 py-2 rounded-md border" :style="{ borderColor: themeColors.border, color: themeColors.textPrimary }">Reset</button>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded-md font-medium text-white" :style="{ backgroundColor: themeColors.primary, opacity: form.processing ? 0.7 : 1 }">Save</button>
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
import { useNotification } from '@/Composables/useNotification'

const props = defineProps({
    user: Object,
    hall: Object,
    routePrefix: { type: String, default: 'admin' },
})

const { currentTheme } = useTheme()
const navigation = computed(() => getNavigationForRole('admin'))
const notification = useNotification()

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
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)'
}))

const inputStyle = computed(() => ({
    backgroundColor: themeColors.value.background,
    borderColor: themeColors.value.border,
    color: themeColors.value.textPrimary,
}))

const original = computed(() => props.hall || {})

const form = useForm({
    name: original.value.name || '',
    code: original.value.code || '',
    description: original.value.description || '',
    capacity: original.value.capacity ?? 1,
    base_price: original.value.base_price ?? 0,
    size: original.value.size || 'medium',
    type: original.value.type || 'conference',
    is_active: !!original.value.is_active,
})

const submit = () => {
    form.put(route(`${props.routePrefix}.halls.update`, original.value.id), {
        onSuccess: () => notification.success('Hall updated successfully'),
    })
}

const resetToOriginal = () => {
    form.name = original.value.name || ''
    form.code = original.value.code || ''
    form.description = original.value.description || ''
    form.capacity = original.value.capacity ?? 1
    form.base_price = original.value.base_price ?? 0
    form.size = original.value.size || 'medium'
    form.type = original.value.type || 'conference'
    form.is_active = !!original.value.is_active
}
</script>
