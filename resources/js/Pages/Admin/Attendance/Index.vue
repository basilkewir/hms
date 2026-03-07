<template>
    <DashboardLayout title="Attendance" :user="user">
        <div class="shadow rounded-lg p-6 mb-8" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Attendance Management</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Track and manage employee attendance records - {{ date }}</p>
                </div>
                <div class="flex gap-3">
                    <button @click="showMarkModal = true" class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center" :style="{ backgroundColor: themeColors.success }">
                        <CheckCircleIcon class="h-4 w-4 mr-2" />
                        Mark Attendance
                    </button>
                    <button @click="exportAttendance" class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center" style="background-color: #8b5cf6;">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                        Export
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" style="background-color: rgba(59, 130, 246, 0.1);">
                        <UserGroupIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Total Employees</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ attendance.length }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" style="background-color: rgba(34, 197, 94, 0.1);">
                        <CheckCircleIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Present Today</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ stats.present }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" style="background-color: rgba(239, 68, 68, 0.1);">
                        <XCircleIcon class="h-6 w-6" :style="{ color: themeColors.danger }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Absent Today</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ stats.absent }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg p-6 border shadow-sm" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4" style="background-color: rgba(251, 146, 60, 0.1);">
                        <ClockIcon class="h-6 w-6" style="color: #fb923c;" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Late Arrivals</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ stats.late }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-lg overflow-hidden shadow" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Employee Attendance</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Position</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Check In</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Check Out</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="employee in attendance" :key="employee.id" class="transition-colors" :style="{ borderBottomStyle: 'solid', borderBottomWidth: '1px', borderColor: themeColors.border }">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ employee.first_name }} {{ employee.last_name }}</div>
                                <div class="text-sm" :style="{ color: themeColors.textSecondary }">{{ employee.email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textSecondary }">
                                {{ employee.department?.name || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textSecondary }">
                                {{ employee.position?.name || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getStatusClass(employee.status)">{{ formatStatus(employee.status) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ employee.check_in || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ employee.check_out || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button v-if="employee.status === 'absent'" @click="markPresent(employee)" class="mr-3 transition-colors" :style="{ color: themeColors.success }">Mark Present</button>
                                <button v-if="employee.status === 'present' || employee.status === 'late'" @click="markCheckOut(employee)" class="mr-3 transition-colors" :style="{ color: themeColors.primary }">Check Out</button>
                                <button @click="editAttendance(employee)" class="transition-colors" :style="{ color: themeColors.warning }">Edit</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Edit Attendance Modal -->
        <DialogModal :show="showMarkModal" @close="closeEditModal">
            <template #title>
                Edit Attendance
            </template>

            <template #content>
                <div v-if="selectedEmployee" class="space-y-4">
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        {{ selectedEmployee.first_name }} {{ selectedEmployee.last_name }} — {{ date }}
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Status</label>
                            <select v-model="editForm.status"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid',
                                    }">
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                                <option value="half_day">Half Day</option>
                                <option value="on_leave">On Leave</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Check In</label>
                            <TimePicker v-model="editForm.check_in" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Check Out</label>
                            <TimePicker v-model="editForm.check_out" />
                        </div>
                    </div>
                </div>
            </template>

            <template #footer>
                <div class="flex items-center justify-end gap-3">
                    <button type="button"
                            @click="closeEditModal"
                            class="px-4 py-2 rounded-md text-sm font-medium"
                            :style="{ backgroundColor: themeColors.border, color: themeColors.textPrimary }">
                        Cancel
                    </button>
                    <button type="button"
                            @click="saveEdit"
                            class="px-4 py-2 rounded-md text-sm font-medium text-white"
                            :style="{ backgroundColor: themeColors.primary }">
                        Save
                    </button>
                </div>
            </template>
        </DialogModal>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DialogModal from '@/Components/DialogModal.vue'
import TimePicker from '@/Components/TimePicker.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { UserGroupIcon, CheckCircleIcon, XCircleIcon, ClockIcon, DocumentArrowDownIcon } from '@heroicons/vue/24/outline'
import { router } from '@inertiajs/vue3'

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
    warning: `var(--kotel-warning)`,
    hover: `rgba(255, 255, 255, 0.1)`
}))

loadTheme()

const props = defineProps({
    user: Object,
    attendance: Array,
    date: String
})

const getRoute = (name) => {
    const prefix = window.location.pathname.startsWith('/hr') ? 'hr.' : 'admin.'
    return route(prefix + name)
}

const showMarkModal = ref(false)
const selectedEmployee = ref(null)
const editForm = ref({
    status: 'present',
    check_in: '',
    check_out: '',
})

const stats = computed(() => {
    const present = props.attendance.filter(e => e.status === 'present').length
    const late = props.attendance.filter(e => e.status === 'late').length
    const absent = props.attendance.filter(e => e.status === 'absent').length
    return { present, late, absent }
})

const formatStatus = (status) => {
    const statusMap = {
        present: 'Present',
        absent: 'Absent',
        late: 'Late',
        half_day: 'Half Day',
        on_leave: 'On Leave'
    }
    return statusMap[status] || status
}

const getStatusClass = (status) => {
    const classes = {
        present: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800',
        late: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800',
        absent: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800',
        half_day: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800',
        on_leave: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800'
    }
    return classes[status] || classes.absent
}

const markPresent = (employee) => {
    const form = {
        user_id: employee.id,
        date: props.date,
        check_in: new Date().toTimeString().slice(0, 5),
        status: 'present'
    }
    
    window.axios.post(getRoute('attendance.mark'), form)
        .then(() => {
            window.location.reload()
        })
        .catch(error => {
            console.error('Error marking attendance:', error)
        })
}

const markCheckOut = (employee) => {
    const form = {
        user_id: employee.id,
        date: props.date,
        check_out: new Date().toTimeString().slice(0, 5)
    }
    
    window.axios.post(getRoute('attendance.check-out'), form)
        .then(() => {
            window.location.reload()
        })
        .catch(error => {
            console.error('Error checking out:', error)
        })
}

const exportAttendance = () => {
    console.log('Export attendance')
}

const editAttendance = (employee) => {
    selectedEmployee.value = employee
    editForm.value.status = employee.status || 'present'
    editForm.value.check_in = (employee.check_in || '').slice(0, 5)
    editForm.value.check_out = (employee.check_out || '').slice(0, 5)
    showMarkModal.value = true
}

const closeEditModal = () => {
    showMarkModal.value = false
    selectedEmployee.value = null
    editForm.value = {
        status: 'present',
        check_in: '',
        check_out: '',
    }
}

const saveEdit = () => {
    if (!selectedEmployee.value) return

    const basePayload = {
        user_id: selectedEmployee.value.id,
        date: props.date,
        check_in: editForm.value.check_in || null,
        status: editForm.value.status,
    }

    // First update status / check-in
    window.axios.post(getRoute('attendance.mark'), basePayload)
        .then(() => {
            // Then update check-out if provided
            if (editForm.value.check_out) {
                const checkoutPayload = {
                    user_id: selectedEmployee.value.id,
                    date: props.date,
                    check_out: editForm.value.check_out,
                }
                return window.axios.post(getRoute('attendance.check-out'), checkoutPayload)
            }
        })
        .then(() => {
            window.location.reload()
        })
        .catch(error => {
            console.error('Error saving attendance:', error)
        })
}
</script>
