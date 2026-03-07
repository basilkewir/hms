<script setup>
import { ref, computed } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

const page = usePage()
const user = computed(() => page.props.auth.user)
const navigation = computed(() => page.props.navigation)

const props = defineProps({
    department: { type: Object, default: null },
})

const isEdit = computed(() => !!props.department)

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
    hover: `var(--kotel-primary-hover)`
}))

const form = useForm({
    name: props.department?.name ?? '',
    description: props.department?.description ?? '',
    manager_id: props.department?.manager_id ?? ''
})

const submit = () => {
    if (isEdit.value) {
        form.put(route('hr.departments.update', props.department.id))
    } else {
        form.post(route('hr.departments.store'))
    }
}
</script>

<template>
    <DashboardLayout :title="isEdit ? 'Edit Department' : 'Add Department'" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Add Department</h1>
                    <p :style="{ color: themeColors.textSecondary }">Create a new department for your hotel</p>
                </div>
                <Link :href="route('hr.departments.index')"
                      class="px-4 py-2 rounded-lg font-medium transition-colors"
                      :style="{
                          backgroundColor: themeColors.background,
                          color: themeColors.textPrimary,
                          borderColor: themeColors.border,
                          borderStyle: 'solid',
                          borderWidth: '1px'
                      }">
                    Back to Departments
                </Link>
            </div>
        </div>

        <!-- Form -->
        <div class="rounded-lg border shadow-sm"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <form @submit.prevent="submit" class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="lg:col-span-2">
                        <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Basic Information</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                                    Department Name *
                                </label>
                                <input 
                                    v-model="form.name"
                                    type="text" 
                                    required
                                    placeholder="e.g., Front Desk, Housekeeping, Maintenance"
                                    class="w-full px-3 py-2 rounded-lg border"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: form.errors.name ? themeColors.danger : themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderStyle: 'solid',
                                        borderWidth: '1px'
                                    }"
                                />
                                <p v-if="form.errors.name" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                                    Description
                                </label>
                                <textarea 
                                    v-model="form.description"
                                    rows="4"
                                    placeholder="Describe the department's responsibilities and functions..."
                                    class="w-full px-3 py-2 rounded-lg border"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: form.errors.description ? themeColors.danger : themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderStyle: 'solid',
                                        borderWidth: '1px'
                                    }"
                                ></textarea>
                                <p v-if="form.errors.description" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                    {{ form.errors.description }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Management -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Management</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                                    Department Manager
                                </label>
                                <select 
                                    v-model="form.manager_id"
                                    class="w-full px-3 py-2 rounded-lg border"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: form.errors.manager_id ? themeColors.danger : themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderStyle: 'solid',
                                        borderWidth: '1px'
                                    }">
                                    <option value="">Select a manager (optional)</option>
                                    <!-- Options will be populated from backend -->
                                </select>
                                <p v-if="form.errors.manager_id" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                    {{ form.errors.manager_id }}
                                </p>
                                <p class="mt-1 text-sm" :style="{ color: themeColors.textTertiary }">
                                    You can assign a manager later if needed
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Department Settings -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Department Settings</h3>
                        
                        <div class="space-y-4">
                            <div class="p-4 rounded-lg border"
                                 :style="{
                                     backgroundColor: themeColors.background,
                                     borderColor: themeColors.border,
                                     borderStyle: 'solid',
                                     borderWidth: '1px'
                                 }">
                                <h4 class="font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                                    Quick Setup Tips
                                </h4>
                                <ul class="text-sm space-y-1" :style="{ color: themeColors.textSecondary }">
                                    <li>• Choose a clear, descriptive name for the department</li>
                                    <li>• Include key responsibilities in the description</li>
                                    <li>• Assign a manager who has authority over department operations</li>
                                    <li>• You can add employees to this department after creation</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex items-center justify-end space-x-4">
                    <Link :href="route('hr.departments.index')"
                          class="px-4 py-2 rounded-lg font-medium transition-colors"
                          :style="{
                              backgroundColor: themeColors.background,
                              color: themeColors.textPrimary,
                              borderColor: themeColors.border,
                              borderStyle: 'solid',
                              borderWidth: '1px'
                          }">
                        Cancel
                    </Link>
                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 rounded-lg font-medium text-white transition-colors disabled:opacity-50"
                        :style="{ backgroundColor: themeColors.primary }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        {{ form.processing ? 'Creating...' : 'Create Department' }}
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>
