<template>
    <DashboardLayout title="Edit Bed Type">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Edit Bed Type</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Modify the details of the {{ bedType.name }} bed type.</p>
                </div>
                <Link :href="route('admin.bed-types.index')"
                      class="px-4 py-2 rounded-md transition-colors"
                      :style="{ 
                          backgroundColor: themeColors.secondary,
                          color: themeColors.textPrimary 
                      }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                    Back to Bed Types
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Basic Information</h3>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Name *</label>
                            <input v-model="form.name" type="text" required 
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                            <div v-if="form.errors.name" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.name }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Code</label>
                            <input v-model="form.code" type="text" 
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                            <div v-if="form.errors.code" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.code }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Description</label>
                            <textarea v-model="form.description" rows="3"
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="{ 
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px',
                                          borderStyle: 'solid'
                                      }"></textarea>
                            <div v-if="form.errors.description" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.description }}</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Dimensions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Width (inches)</label>
                            <input v-model="form.width_inches" type="number" step="0.01"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                            <div v-if="form.errors.width_inches" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.width_inches }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Length (inches)</label>
                            <input v-model="form.length_inches" type="number" step="0.01"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                            <div v-if="form.errors.length_inches" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.length_inches }}</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Settings</h3>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Sort Order</label>
                            <input v-model="form.sort_order" type="number" 
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                            <div v-if="form.errors.sort_order" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.sort_order }}</div>
                        </div>
                        <div class="flex items-center">
                            <input v-model="form.is_active" type="checkbox" 
                                   class="h-4 w-4 rounded focus:outline-none transition-colors mr-2"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.primary
                                   }">
                            <label class="text-sm font-medium"
                                   :style="{ color: themeColors.textPrimary }">Active</label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 pt-6 border-t"
                     :style="{ 
                         borderTopColor: themeColors.border,
                         borderTopWidth: '1px',
                         borderTopStyle: 'solid'
                     }">
                    <Link :href="route('admin.bed-types.index')"
                          class="px-6 py-2 rounded-md transition-colors"
                          :style="{ 
                              backgroundColor: themeColors.secondary,
                              color: themeColors.textPrimary 
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing" 
                            class="px-6 py-2 rounded-md transition-colors disabled:opacity-50"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                                color: 'white'
                            }"
                            @mouseenter="!form.processing && ($event.target.style.backgroundColor = themeColors.hover)"
                            @mouseleave="!form.processing && ($event.target.style.backgroundColor = themeColors.primary)">
                        {{ form.processing ? 'Updating...' : 'Update Bed Type' }}
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
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme.js'

// Initialize theme
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

// Load theme on mount
loadTheme()

const props = defineProps({
    user: Object,
    bedType: Object,
})

const navigation = computed(() => getNavigationForRole('admin'))

const form = useForm({
    name: props.bedType.name,
    code: props.bedType.code || '',
    description: props.bedType.description || '',
    width_inches: props.bedType.width_inches,
    length_inches: props.bedType.length_inches,
    is_active: props.bedType.is_active,
    sort_order: props.bedType.sort_order || 0,
})

const submit = () => {
    form.put(route('admin.bed-types.update', props.bedType.id))
}
</script>
