<template>
    <DashboardLayout title="Recruitment" :user="user">
        <div class="shadow rounded-lg p-6 mb-8" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Recruitment</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Manage job openings and applications</p>
                </div>
                <div class="flex gap-3">
                    <button
                        @click="openPostJobModal"
                        class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                        :style="{ backgroundColor: themeColors.primary }">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Post Job
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" style="background-color: rgba(59, 130, 246, 0.1);">
                        <BriefcaseIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Open Positions</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ jobs.length }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" style="background-color: rgba(34, 197, 94, 0.1);">
                        <UsersIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Applications</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">0</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" style="background-color: rgba(245, 158, 11, 0.1);">
                        <ClockIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Interviews</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">0</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-lg border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Job Postings</h3>
            </div>
            <div v-if="jobs.length" class="divide-y" :style="{ borderColor: themeColors.border }">
                <div
                    v-for="job in jobs"
                    :key="job.id"
                    class="px-6 py-4 flex items-center justify-between gap-4">
                    <div class="flex-1">
                        <p class="font-medium" :style="{ color: themeColors.textPrimary }">{{ job.title }}</p>
                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                            {{ job.department }} • {{ job.location }}
                        </p>
                    </div>
                    <div class="text-sm flex-1">
                        <p :style="{ color: themeColors.textSecondary }">Posted: {{ job.posted_at }}</p>
                        <p :style="{ color: themeColors.textTertiary }">Status: {{ job.status }}</p>
                    </div>
                </div>
            </div>
            <div v-else class="p-8 text-center">
                <p :style="{ color: themeColors.textSecondary }">No active job postings found.</p>
            </div>
        </div>

        <!-- Post Job Modal -->
        <DialogModal :show="showJobModal" @close="closeJobModal">
            <template #title>
                Post Job
            </template>

            <template #content>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Job Title</label>
                        <input v-model="jobForm.title"
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
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Department</label>
                            <input v-model="jobForm.department"
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
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Location</label>
                            <input v-model="jobForm.location"
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
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Status</label>
                            <select v-model="jobForm.status"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid',
                                    }">
                                <option value="Open">Open</option>
                                <option value="Closed">Closed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Posted Date</label>
                            <DatePicker
                                v-model="jobForm.posted_at"
                                class="w-full"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Description</label>
                        <textarea v-model="jobForm.description"
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
                            @click="closeJobModal"
                            class="px-4 py-2 rounded-md text-sm font-medium"
                            :style="{ backgroundColor: themeColors.border, color: themeColors.textPrimary }">
                        Cancel
                    </button>
                    <button type="button"
                            @click="submitJob"
                            class="px-4 py-2 rounded-md text-sm font-medium text-white"
                            :style="{ backgroundColor: themeColors.primary }">
                        Save Job
                    </button>
                </div>
            </template>
        </DialogModal>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DialogModal from '@/Components/DialogModal.vue'
import DatePicker from '@/Components/DatePicker.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { BriefcaseIcon, UsersIcon, ClockIcon, PlusIcon } from '@heroicons/vue/24/outline'

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

defineProps({
    user: Object
})

const showJobModal = ref(false)
const jobs = ref([])

const jobForm = ref({
    title: '',
    department: '',
    location: '',
    status: 'Open',
    posted_at: '',
    description: '',
})

const resetJobForm = () => {
    jobForm.value = {
        title: '',
        department: '',
        location: '',
        status: 'Open',
        posted_at: '',
        description: '',
    }
}

const openPostJobModal = () => {
    resetJobForm()
    showJobModal.value = true
}

const closeJobModal = () => {
    showJobModal.value = false
}

const submitJob = () => {
    if (!jobForm.value.title) {
        return
    }

    jobs.value.push({
        id: Date.now(),
        ...jobForm.value,
    })

    showJobModal.value = false
    resetJobForm()
}
</script>
