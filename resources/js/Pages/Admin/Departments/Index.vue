<template>
    <DashboardLayout title="Departments" :user="user">
        <div class="shadow rounded-lg p-6 mb-8" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Departments</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Manage hotel departments and organizational structure</p>
                </div>
                <button @click="showCreateModal = true" class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center" :style="{ backgroundColor: themeColors.primary }">
                    <PlusIcon class="h-4 w-4 mr-2" />
                    New Department
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" style="background-color: rgba(59, 130, 246, 0.1);">
                        <BuildingOfficeIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Total Departments</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ departments.length }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" style="background-color: rgba(34, 197, 94, 0.1);">
                        <UserGroupIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Total Positions</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ totalPositions }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" style="background-color: rgba(139, 92, 246, 0.1);">
                        <CheckCircleIcon class="h-6 w-6" style="color: #8b5cf6;" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Active Departments</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ departments.length }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" style="background-color: rgba(251, 146, 60, 0.1);">
                        <UsersIcon class="h-6 w-6" style="color: #fb923c;" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Avg Positions/Dept</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ avgPositions }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-lg overflow-hidden shadow" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">All Departments</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Department Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Positions</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="department in departments" :key="department.id" class="transition-colors" :style="{ borderBottomStyle: 'solid', borderBottomWidth: '1px', borderColor: themeColors.border }">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ department.name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm" :style="{ color: themeColors.textSecondary }">{{ department.description || 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ department.positions?.length || 0 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click="editDepartment(department)" class="mr-3 transition-colors" :style="{ color: themeColors.primary }">Edit</button>
                                <button @click="deleteDepartment(department.id)" class="transition-colors" :style="{ color: themeColors.danger }">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { BuildingOfficeIcon, UserGroupIcon, CheckCircleIcon, UsersIcon, PlusIcon } from '@heroicons/vue/24/outline'

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
    danger: `var(--kotel-danger)`,
    hover: `rgba(255, 255, 255, 0.1)`
}))

loadTheme()

const props = defineProps({
    user: Object,
    departments: Array
})

const showCreateModal = ref(false)

const totalPositions = computed(() => {
    return props.departments.reduce((sum, dept) => sum + (dept.positions?.length || 0), 0)
})

const avgPositions = computed(() => {
    return props.departments.length > 0 ? Math.round(totalPositions.value / props.departments.length) : 0
})

const editDepartment = (department) => {
    console.log('Edit department:', department)
}

const deleteDepartment = (id) => {
    if (confirm('Are you sure you want to delete this department?')) {
        console.log('Delete department:', id)
    }
}
</script>
