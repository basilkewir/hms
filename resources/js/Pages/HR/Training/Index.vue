<template>
    <DashboardLayout title="Training" :user="user">
        <div class="shadow rounded-lg p-6 mb-8" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Training & Development</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Manage employee training programs and certifications</p>
                </div>
                <button
                    @click="showCreateModal = true"
                    class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                    :style="{ backgroundColor: themeColors.primary }">
                    <PlusIcon class="h-4 w-4 mr-2" />
                    Add Program
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" style="background-color: rgba(59, 130, 246, 0.1);">
                        <AcademicCapIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Active Programs</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ stats?.active ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" style="background-color: rgba(34, 197, 94, 0.1);">
                        <UserGroupIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Enrolled Staff</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ stats?.enrolled ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" style="background-color: rgba(251, 146, 60, 0.1);">
                        <CheckBadgeIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Completed</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ stats?.completed ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-lg border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Training Programs</h3>
            </div>
            <div v-if="programs && programs.length" class="divide-y" :style="{ borderColor: themeColors.border }">
                <div v-for="program in programs" :key="program.id" class="px-6 py-4 flex items-center justify-between gap-4">
                    <div class="flex-1">
                        <p class="font-medium" :style="{ color: themeColors.textPrimary }">{{ program.name }}</p>
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                            {{ formatDate(program.start_date) }} - {{ formatDate(program.end_date) }}
                        </p>
                    </div>
                    <p class="text-sm flex-1" :style="{ color: themeColors.textTertiary }">{{ program.description }}</p>
                    <button
                        type="button"
                        @click="openEditProgram(program)"
                        class="px-3 py-1 rounded-md text-sm font-medium"
                        :style="{ backgroundColor: themeColors.border, color: themeColors.textPrimary }">
                        Edit
                    </button>
                </div>
            </div>
            <div v-else class="p-8 text-center">
                <p :style="{ color: themeColors.textSecondary }">No training programs found.</p>
            </div>
        </div>

        <!-- Create Training Program Modal -->
        <DialogModal :show="showCreateModal" @close="closeCreateModal">
            <template #title>
                {{ editingProgram ? 'Edit Training Program' : 'Create Training Program' }}
            </template>

            <template #content>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Program Name</label>
                        <input v-model="form.name"
                               type="text"
                               class="w-full rounded-md px-3 py-2 focus:outline-none"
                               :style="{
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid',
                               }" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Start Date</label>
                            <DatePicker v-model="form.start_date" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">End Date</label>
                            <DatePicker v-model="form.end_date" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Description</label>
                        <textarea v-model="form.description"
                                  rows="3"
                                  class="w-full rounded-md px-3 py-2 focus:outline-none"
                                  :style="{
                                      backgroundColor: themeColors.background,
                                      borderColor: themeColors.border,
                                      color: themeColors.textPrimary,
                                      borderWidth: '1px',
                                      borderStyle: 'solid',
                                  }" />
                    </div>
                </div>
            </template>

            <template #footer>
                <div class="flex items-center justify-end gap-3">
                    <button type="button"
                            @click="closeCreateModal"
                            class="px-4 py-2 rounded-md text-sm font-medium"
                            :style="{ backgroundColor: themeColors.border, color: themeColors.textPrimary }">
                        Cancel
                    </button>
                    <button type="button"
                            @click="submitCreateProgram"
                            class="px-4 py-2 rounded-md text-sm font-medium text-white"
                            :disabled="form.processing"
                            :style="{ backgroundColor: themeColors.primary, opacity: form.processing ? 0.7 : 1 }">
                        {{ editingProgram ? 'Update Program' : 'Save Program' }}
                    </button>
                </div>
            </template>
        </DialogModal>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DialogModal from '@/Components/DialogModal.vue'
import DatePicker from '@/Components/DatePicker.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { AcademicCapIcon, UserGroupIcon, CheckBadgeIcon, PlusIcon } from '@heroicons/vue/24/outline'

const { loadTheme } = useTheme()
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

loadTheme()

const showCreateModal = ref(false)
const editingProgram = ref(null)
const form = useForm({
    name: '',
    start_date: '',
    end_date: '',
    description: '',
})

const closeCreateModal = () => {
    showCreateModal.value = false
    editingProgram.value = null
}

const submitCreateProgram = () => {
    if (editingProgram.value) {
        form.put(route('hr.training.programs.update', editingProgram.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                showCreateModal.value = false
                editingProgram.value = null
                form.reset()
            },
        })
    } else {
        form.post(route('hr.training.programs.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showCreateModal.value = false
                form.reset()
            },
        })
    }
}

const openEditProgram = (program) => {
    editingProgram.value = program
    form.name = program.name
    form.start_date = program.start_date
    form.end_date = program.end_date
    form.description = program.description || ''
    showCreateModal.value = true
}

const formatDate = (value) => {
    if (!value) return '-'
    const d = new Date(value)
    if (Number.isNaN(d.getTime())) return value
    return d.toLocaleDateString()
}

const props = defineProps({
    user: Object,
    programs: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
})

const programs = computed(() => props.programs || [])
const stats = computed(() => props.stats || {})
</script>
