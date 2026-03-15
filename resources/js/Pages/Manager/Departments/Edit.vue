<template>
    <DashboardLayout title="Edit Department">
        <div style="box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 2rem;"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Edit Department</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Update department information.</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="`/admin/departments/${department.id}`"
                          class="px-4 py-2 rounded-md transition-colors"
                          :style="{ 
                              backgroundColor: themeColors.secondary,
                              color: themeColors.textPrimary 
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        <ArrowLeftIcon class="h-4 w-4 mr-2 inline" />
                        View Department
                    </Link>
                    <Link href="/admin/departments"
                          class="px-4 py-2 rounded-md transition-colors"
                          :style="{ 
                              backgroundColor: themeColors.secondary,
                              color: themeColors.textPrimary 
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        <ArrowLeftIcon class="h-4 w-4 mr-2 inline" />
                        Back to Departments
                    </Link>
                </div>
            </div>

            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Department Information -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Department Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Department Name *</label>
                            <input type="text" v-model="form.name" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: form.errors.name ? themeColors.danger : themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                            <p v-if="form.errors.name" class="mt-1 text-sm"
                               :style="{ color: themeColors.danger }">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Description</label>
                            <textarea v-model="form.description" rows="4"
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors resize-none"
                                      :style="{ 
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px',
                                          borderStyle: 'solid'
                                      }"
                                      placeholder="Enter department description..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Department Settings -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Department Settings</h3>
                    <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                        <div class="flex items-center">
                            <input type="checkbox" v-model="form.is_active"
                                   class="h-4 w-4 rounded focus:outline-none transition-colors"
                                   :style="{ 
                                       accentColor: themeColors.primary,
                                       borderColor: themeColors.border
                                   }">
                            <label class="ml-2 block text-sm"
                                   :style="{ color: themeColors.textSecondary }">Department is active</label>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t"
                     :style="{ borderColor: themeColors.border }">
                    <button type="button" @click="cancel"
                            class="px-6 py-2 rounded-md transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.secondary,
                                color: themeColors.textPrimary 
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        Cancel
                    </button>
                    <button type="submit" :disabled="form.processing"
                            class="px-6 py-2 rounded-md transition-colors disabled:opacity-50"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                                color: 'white' 
                            }"
                            @mouseenter="!form.processing && ($event.target.style.backgroundColor = themeColors.hover)"
                            @mouseleave="!form.processing && ($event.target.style.backgroundColor = themeColors.primary)">
                        <span v-if="form.processing">Updating...</span>
                        <span v-else>Update Department</span>
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

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
    department: Object,
    errors: Object
})

const form = useForm({
    name: props.department.name || '',
    description: props.department.description || '',
    is_active: props.department.is_active ?? true,
})

const submitForm = () => {
    form.put(`/admin/departments/${props.department.id}`, {
        onSuccess: () => {
            // Success handled by redirect
        },
        onError: () => {
            // Errors handled by form.errors
        }
    })
}

const cancel = () => {
    router.get('/admin/departments')
}
</script>
