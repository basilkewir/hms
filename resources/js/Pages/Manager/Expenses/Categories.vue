<template>
    <DashboardLayout title="Expense Categories" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Expense Categories</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Manage expense categories for organizing your expenses.</p>
                </div>
                <button @click="showAddModal = true"
                    class="px-4 py-2 rounded-md text-sm font-medium flex items-center text-white transition-colors"
                    :style="{ backgroundColor: themeColors.primary }"
                    @mouseenter="$event.currentTarget.style.backgroundColor = themeColors.primaryHover"
                    @mouseleave="$event.currentTarget.style.backgroundColor = themeColors.primary">
                    <PlusIcon class="h-4 w-4 mr-2" />
                    Add Category
                </button>
            </div>
        </div>

        <!-- Categories Table -->
        <div class="shadow rounded-lg overflow-hidden"
             :style="{ backgroundColor: themeColors.card }">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Expenses</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Color / Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!categories || categories.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                     :style="{ stroke: themeColors.textSecondary }">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium" :style="{ color: themeColors.textPrimary }">No categories</h3>
                                <p class="mt-1 text-sm" :style="{ color: themeColors.textSecondary }">Get started by creating a new expense category.</p>
                            </td>
                        </tr>
                        <tr v-for="category in categories" :key="category.id" class="transition-colors border-t"
                            :style="{ borderColor: themeColors.border }"
                            @mouseenter="$event.currentTarget.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.currentTarget.style.backgroundColor = 'transparent'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ category.name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm" :style="{ color: themeColors.textSecondary }">{{ category.code || '—' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm" :style="{ color: themeColors.textSecondary }">{{ category.description || 'No description' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ category.expenses_count || 0 }} expenses
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-full shadow-sm border-2"
                                         :style="{ backgroundColor: category.color || '#3b82f6', borderColor: (category.color || '#3b82f6') + '66' }"
                                         :title="category.color || '#3b82f6'"></div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :class="category.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                        {{ category.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-3">
                                    <button @click="editCategory(category)" class="transition-colors"
                                            :style="{ color: themeColors.primary }"
                                            @mouseenter="$event.target.style.color = themeColors.primaryHover"
                                            @mouseleave="$event.target.style.color = themeColors.primary">Edit</button>
                                    <button @click="deleteCategory(category)"
                                            class="transition-colors disabled:opacity-40"
                                            :style="{ color: themeColors.danger }"
                                            :disabled="(category.expenses_count || 0) > 0"
                                            @mouseenter="$event.target.style.color = themeColors.dangerHover"
                                            @mouseleave="$event.target.style.color = themeColors.danger">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <DialogModal :show="showAddModal || showEditModal" @close="closeModal" max-width="lg">
            <template #title>
                <span :style="{ color: themeColors.textPrimary }">{{ editingCategory ? 'Edit Category' : 'Add New Category' }}</span>
            </template>
            <template #content>
                <div class="space-y-5" :style="{ backgroundColor: themeColors.card }">
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Category Name *</label>
                        <input v-model="form.name" type="text"
                            class="w-full px-3 py-2 rounded-lg border focus:outline-none transition-colors"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }"
                            placeholder="e.g., Office Supplies" />
                        <p v-if="errors.name" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Code</label>
                        <input v-model="form.code" type="text"
                            class="w-full px-3 py-2 rounded-lg border focus:outline-none transition-colors"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }"
                            placeholder="Auto-generated if left empty" />
                        <p class="mt-1 text-xs" :style="{ color: themeColors.textTertiary }">Leave empty to auto-generate from name</p>
                        <p v-if="errors.code" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.code }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Description</label>
                        <textarea v-model="form.description" rows="3"
                            class="w-full px-3 py-2 rounded-lg border focus:outline-none transition-colors"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }"
                            placeholder="Optional description for this category"></textarea>
                        <p v-if="errors.description" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.description }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Color</label>
                        <div class="flex items-center gap-3">
                            <input v-model="form.color" type="color"
                                class="h-10 w-16 rounded-lg border cursor-pointer p-1"
                                :style="{ borderColor: themeColors.border }" />
                            <input v-model="form.color" type="text"
                                class="flex-1 px-3 py-2 rounded-lg border focus:outline-none transition-colors"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }"
                                placeholder="#3b82f6" pattern="^#[0-9A-Fa-f]{6}$" />
                        </div>
                        <p class="mt-1 text-xs" :style="{ color: themeColors.textTertiary }">Choose a color for this category</p>
                        <p v-if="errors.color" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.color }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <input v-model="form.is_active" type="checkbox" id="is-active"
                            class="rounded cursor-pointer"
                            :style="{ accentColor: themeColors.primary }" />
                        <label for="is-active" class="text-sm cursor-pointer" :style="{ color: themeColors.textSecondary }">Active</label>
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <button @click="closeModal" type="button"
                        class="px-4 py-2 rounded-lg border transition-colors font-medium"
                        :style="{ borderColor: themeColors.border, color: themeColors.textSecondary, backgroundColor: 'transparent' }">
                        Cancel
                    </button>
                    <button @click="saveCategory" type="button"
                        class="px-4 py-2 rounded-lg transition-colors font-medium text-white"
                        :style="{ backgroundColor: processing ? themeColors.secondary : (editingCategory ? themeColors.success : themeColors.primary) }"
                        :disabled="processing">
                        {{ processing ? 'Saving...' : (editingCategory ? 'Update Category' : 'Create Category') }}
                    </button>
                </div>
            </template>
        </DialogModal>
    </DashboardLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DialogModal from '@/Components/DialogModal.vue'
import { PlusIcon } from '@heroicons/vue/24/outline'
import { notify } from '@/Composables/useNotification.js'
import { initializeCurrencySettings } from '@/Utils/currency.js'
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
    primaryHover: `var(--kotel-primary-hover)`,
    secondary: `var(--kotel-secondary)`,
    success: `var(--kotel-success)`,
    danger: `var(--kotel-danger)`,
    dangerHover: `var(--kotel-danger-hover)`,
    warning: `var(--kotel-warning)`,
    hover: `rgba(255, 255, 255, 0.05)`,
}))

const props = defineProps({
    user: Object,
    navigation: Array,
    routePrefix: { type: String, default: 'admin' },
    categories: {
        type: Array,
        default: () => []
    }
})

const showAddModal = ref(false)
const showEditModal = ref(false)
const editingCategory = ref(null)
const processing = ref(false)
const errors = ref({})

const form = reactive({
    name: '',
    code: '',
    description: '',
    color: '#3b82f6',
    is_active: true
})

const editCategory = (category) => {
    editingCategory.value = category
    form.name = category.name
    form.code = category.code || ''
    form.description = category.description || ''
    form.color = category.color || '#3b82f6'
    form.is_active = category.is_active
    showEditModal.value = true
}

const deleteCategory = (category) => {
    if (category.expenses_count > 0) {
        notify.error('Cannot delete category with existing expenses.')
        return
    }

    if (confirm(`Are you sure you want to delete "${category.name}"?`)) {
        router.delete(route(`${props.routePrefix}.expenses.categories.destroy`, category.id), {
            preserveScroll: true,
            onSuccess: () => {
                notify.success('Category deleted successfully.')
            },
            onError: (errors) => {
                notify.error(errors.message || 'Failed to delete category.')
            }
        })
    }
}

const saveCategory = () => {
    processing.value = true
    errors.value = {}

    if (editingCategory.value) {
        router.put(route(`${props.routePrefix}.expenses.categories.update`, editingCategory.value.id), form, {
            preserveScroll: true,
            onSuccess: () => {
                notify.success('Category updated successfully.')
                closeModal()
            },
            onError: (err) => {
                errors.value = err
                notify.error('Failed to update category.')
            },
            onFinish: () => {
                processing.value = false
            }
        })
    } else {
        router.post(route(`${props.routePrefix}.expenses.categories.store`), form, {
            preserveScroll: true,
            onSuccess: () => {
                notify.success('Category created successfully.')
                closeModal()
            },
            onError: (err) => {
                errors.value = err
                notify.error('Failed to create category.')
            },
            onFinish: () => {
                processing.value = false
            }
        })
    }
}

const closeModal = () => {
    showAddModal.value = false
    showEditModal.value = false
    editingCategory.value = null
    form.name = ''
    form.code = ''
    form.description = ''
    form.color = '#3b82f6'
    form.is_active = true
    errors.value = {}
}

onMounted(() => {
    loadTheme()
    initializeCurrencySettings()
})
</script>
