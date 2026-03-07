<template>
    <DashboardLayout title="Create Maintenance Category">
        <div class="shadow rounded-lg p-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">

            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Create Maintenance Category</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Add a new category for maintenance requests.</p>
                </div>
                <Link :href="route(`${routePrefix}.maintenance-categories.index`)"
                      class="px-4 py-2 rounded-md transition-colors"
                      :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }">
                    Back to Categories
                </Link>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Category Name *</label>
                        <input v-model="form.name" type="text" required
                               class="w-full px-4 py-2 rounded-md border focus:outline-none transition-colors"
                               :style="inputStyle"
                               placeholder="e.g., HVAC" />
                        <p v-if="form.errors.name" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ form.errors.name }}</p>
                    </div>

                    <!-- Code -->
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Code *</label>
                        <input v-model="form.code" type="text" required
                               class="w-full px-4 py-2 rounded-md border focus:outline-none transition-colors"
                               :style="inputStyle"
                               placeholder="e.g., hvac" />
                        <p class="mt-1 text-xs" :style="{ color: themeColors.textTertiary }">Used in system, lowercase with hyphens</p>
                        <p v-if="form.errors.code" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ form.errors.code }}</p>
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Description</label>
                        <textarea v-model="form.description" rows="3"
                                  class="w-full px-4 py-2 rounded-md border focus:outline-none transition-colors"
                                  :style="inputStyle"
                                  placeholder="Describe this category..."></textarea>
                    </div>

                    <!-- Color -->
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Color</label>
                        <div class="flex gap-3">
                            <input v-model="form.color" type="color"
                                   class="h-10 w-16 rounded border cursor-pointer"
                                   :style="{ borderColor: themeColors.border }" />
                            <input v-model="form.color" type="text"
                                   class="flex-1 px-4 py-2 rounded-md border focus:outline-none transition-colors"
                                   :style="inputStyle"
                                   placeholder="#3b82f6" />
                        </div>
                        <p class="mt-1 text-xs" :style="{ color: themeColors.textTertiary }">Choose a color for visual identification</p>
                    </div>

                    <!-- Icon (picker) -->
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Icon</label>
                        <select v-model="form.icon"
                                class="w-full px-4 py-2 rounded-md border focus:outline-none transition-colors mb-2"
                                :style="inputStyle">
                            <option v-for="option in iconOptions" :key="option.value" :value="option.value">
                                {{ option.label }} ({{ option.value }})
                            </option>
                        </select>
                        <p class="mt-1 text-xs" :style="{ color: themeColors.textTertiary }">
                            Pick a Heroicons outline icon name (without the <span class='font-mono'>Icon</span> suffix).
                        </p>
                    </div>

                    <!-- Sort Order -->
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Sort Order</label>
                        <input v-model.number="form.sort_order" type="number" min="0"
                               class="w-full px-4 py-2 rounded-md border focus:outline-none transition-colors"
                               :style="inputStyle"
                               placeholder="0" />
                    </div>

                    <!-- Active -->
                    <div class="flex items-center pt-6">
                        <label class="flex items-center cursor-pointer gap-3">
                            <input v-model="form.is_active" type="checkbox"
                                   class="w-5 h-5 rounded border"
                                   :style="{ borderColor: themeColors.border }" />
                            <span class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Active</span>
                        </label>
                    </div>
                </div>

                <!-- Preview -->
                <div class="p-4 rounded-lg border"
                     :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                    <h3 class="text-sm font-medium mb-3" :style="{ color: themeColors.textSecondary }">Preview</h3>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                             :style="{ backgroundColor: form.color + '30', border: '1px solid ' + form.color }">
                            <WrenchIcon class="h-5 w-5" :style="{ color: form.color }" />
                        </div>
                        <div>
                            <h4 class="font-semibold" :style="{ color: themeColors.textPrimary }">{{ form.name || 'Category Name' }}</h4>
                            <p class="text-sm" :style="{ color: themeColors.textTertiary }">{{ form.description || 'No description' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4 pt-4 border-t" :style="{ borderColor: themeColors.border }">
                    <Link :href="route(`${routePrefix}.maintenance-categories.index`)"
                          class="px-6 py-2 rounded-md transition-colors"
                          :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                            class="px-6 py-2 rounded-md font-medium transition-colors disabled:opacity-50 text-white"
                            :style="{ backgroundColor: themeColors.primary }">
                        <span v-if="form.processing">Creating...</span>
                        <span v-else>Create Category</span>
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { WrenchIcon } from '@heroicons/vue/24/outline'
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
}))
loadTheme()

const inputStyle = computed(() => ({
    backgroundColor: themeColors.value.background,
    borderColor: themeColors.value.border,
    color: themeColors.value.textPrimary,
    borderWidth: '1px',
    borderStyle: 'solid',
}))

const iconOptions = [
    { label: 'Wrench / Tools', value: 'wrench' },
    { label: 'Cog / Settings', value: 'cog-6-tooth' },
    { label: 'Home / Building', value: 'home' },
    { label: 'Bolt / Electrical', value: 'bolt' },
    { label: 'Fire / Alerts', value: 'fire' },
    { label: 'Shield / Safety', value: 'shield-check' },
    { label: 'Clipboard / Checklist', value: 'clipboard-document-check' },
]

const props = defineProps({
    routePrefix: { type: String, default: 'admin' },
})

const form = useForm({
    name: '',
    code: '',
    description: '',
    color: '#3b82f6',
    icon: iconOptions[0].value,
    sort_order: 0,
    is_active: true
})

const submitForm = () => {
    form.post(route(`${props.routePrefix}.maintenance-categories.store`))
}
</script>
