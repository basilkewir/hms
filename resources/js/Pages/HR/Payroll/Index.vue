<script setup>
import { ref, computed } from 'vue'
import { Link, usePage, useForm, router } from '@inertiajs/vue3'
import { formatCurrency } from '@/Utils/currency.js'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

const page = usePage()
const user = computed(() => page.props.auth.user)
const navigation = computed(() => page.props.navigation)

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

const employees = computed(() => page.props.employees)

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const calculateMonthlySalary = (salary) => {
    return formatCurrency((salary || 0) * 12 / 12)
}

const calculateAnnualSalary = (salary) => {
    return formatCurrency((salary || 0) * 12)
}

const getSalaryLevel = (salary) => {
    if (!salary) return { text: 'Not Set', color: themeColors.value.textTertiary }
    
    const annual = salary * 12
    if (annual < 30000) return { text: 'Entry Level', color: themeColors.value.success }
    if (annual < 60000) return { text: 'Mid Level', color: themeColors.value.primary }
    if (annual < 100000) return { text: 'Senior Level', color: themeColors.value.warning }
    return { text: 'Executive', color: themeColors.value.danger }
}

const totalPayroll = computed(() => {
    return employees.value.reduce((total, emp) => total + (emp.salary || 0), 0)
})

const activeEmployees = computed(() => {
    return employees.value.filter(emp => emp.is_active).length
})

const averageSalary = computed(() => {
    const activeEmps = employees.value.filter(emp => emp.is_active && emp.salary)
    if (activeEmps.length === 0) return 0
    return activeEmps.reduce((total, emp) => total + emp.salary, 0) / activeEmps.length
})

const salaryModal = ref(false)
const selectedEmployee = ref(null)
const salaryForm = useForm({ salary: '' })

const openSalaryModal = (employee) => {
    selectedEmployee.value = employee
    salaryForm.salary = employee.salary || ''
    salaryModal.value = true
}

const closeSalaryModal = () => {
    salaryModal.value = false
    selectedEmployee.value = null
    salaryForm.reset()
}

const submitSalary = () => {
    salaryForm.put(route('hr.payroll.update-salary', selectedEmployee.value.id), {
        onSuccess: () => closeSalaryModal()
    })
}
</script>

<template>
    <DashboardLayout title="Payroll" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Payroll Management</h1>
                    <p :style="{ color: themeColors.textSecondary }">Manage employee salaries and compensation</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="px-4 py-2 rounded-lg font-medium text-white transition-colors"
                            :style="{ backgroundColor: themeColors.primary }">
                        Generate Payslips
                    </button>
                    <button class="px-4 py-2 rounded-lg font-medium text-white transition-colors"
                            :style="{ backgroundColor: themeColors.success }">
                        Process Payroll
                    </button>
                </div>
            </div>
        </div>

        <!-- Payroll Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Total Payroll</p>
                        <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">
                            {{ formatCurrency(totalPayroll) }}
                        </p>
                        <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">Per month</p>
                    </div>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center"
                         :style="{ backgroundColor: themeColors.primary + '20', color: themeColors.primary }">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Active Employees</p>
                        <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">{{ activeEmployees }}</p>
                        <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">Currently active</p>
                    </div>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center"
                         :style="{ backgroundColor: themeColors.success + '20', color: themeColors.success }">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Average Salary</p>
                        <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">
                            {{ formatCurrency(averageSalary) }}
                        </p>
                        <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">Per month</p>
                    </div>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center"
                         :style="{ backgroundColor: themeColors.warning + '20', color: themeColors.warning }">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Annual Budget</p>
                        <p class="text-2xl font-bold mt-1" :style="{ color: themeColors.textPrimary }">
                            {{ formatCurrency(totalPayroll * 12) }}
                        </p>
                        <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">Per year</p>
                    </div>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center"
                         :style="{ backgroundColor: themeColors.danger + '20', color: themeColors.danger }">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <button class="p-4 rounded-lg border text-left hover:opacity-80 transition-opacity"
                        :style="{
                            backgroundColor: themeColors.card,
                            borderColor: themeColors.border,
                            borderStyle: 'solid',
                            borderWidth: '1px',
                            color: themeColors.textPrimary
                        }">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mb-3"
                         :style="{ backgroundColor: themeColors.primary + '20', color: themeColors.primary }">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="font-medium">Generate Payslips</p>
                    <p class="text-sm mt-1" :style="{ color: themeColors.textSecondary }">Create monthly payslips</p>
                </button>

                <button class="p-4 rounded-lg border text-left hover:opacity-80 transition-opacity"
                        :style="{
                            backgroundColor: themeColors.card,
                            borderColor: themeColors.border,
                            borderStyle: 'solid',
                            borderWidth: '1px',
                            color: themeColors.textPrimary
                        }">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mb-3"
                         :style="{ backgroundColor: themeColors.success + '20', color: themeColors.success }">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <p class="font-medium">Process Payroll</p>
                    <p class="text-sm mt-1" :style="{ color: themeColors.textSecondary }">Run monthly payroll</p>
                </button>

                <button class="p-4 rounded-lg border text-left hover:opacity-80 transition-opacity"
                        :style="{
                            backgroundColor: themeColors.card,
                            borderColor: themeColors.border,
                            borderStyle: 'solid',
                            borderWidth: '1px',
                            color: themeColors.textPrimary
                        }">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mb-3"
                         :style="{ backgroundColor: themeColors.warning + '20', color: themeColors.warning }">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                    </div>
                    <p class="font-medium">Salary Adjustments</p>
                    <p class="text-sm mt-1" :style="{ color: themeColors.textSecondary }">Update salaries</p>
                </button>

                <button class="p-4 rounded-lg border text-left hover:opacity-80 transition-opacity"
                        :style="{
                            backgroundColor: themeColors.card,
                            borderColor: themeColors.border,
                            borderStyle: 'solid',
                            borderWidth: '1px',
                            color: themeColors.textPrimary
                        }">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mb-3"
                         :style="{ backgroundColor: themeColors.danger + '20', color: themeColors.danger }">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a3 3 0 003 3h0a3 3 0 003-3v-1m-6 0h6m-6 0h6m-6 0a3 3 0 01-3-3v-1m6 0v1a3 3 0 003 3h0a3 3 0 003-3v-1m-6 0h6M9 11V9a2 2 0 012-2h2a2 2 0 012 2v2m0 0h6m-6 0h6"/>
                        </svg>
                    </div>
                    <p class="font-medium">Tax Reports</p>
                    <p class="text-sm mt-1" :style="{ color: themeColors.textSecondary }">Generate tax documents</p>
                </button>
            </div>
        </div>

        <!-- Employee Salary Table -->
        <div class="rounded-lg border shadow-sm overflow-hidden"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <div class="p-6 border-b" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Employee Salaries</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="text-left p-4 font-medium text-sm" :style="{ color: themeColors.textSecondary }">Employee</th>
                            <th class="text-left p-4 font-medium text-sm" :style="{ color: themeColors.textSecondary }">Department</th>
                            <th class="text-left p-4 font-medium text-sm" :style="{ color: themeColors.textSecondary }">Monthly Salary</th>
                            <th class="text-left p-4 font-medium text-sm" :style="{ color: themeColors.textSecondary }">Annual Salary</th>
                            <th class="text-left p-4 font-medium text-sm" :style="{ color: themeColors.textSecondary }">Level</th>
                            <th class="text-left p-4 font-medium text-sm" :style="{ color: themeColors.textSecondary }">Status</th>
                            <th class="text-left p-4 font-medium text-sm" :style="{ color: themeColors.textSecondary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="employee in employees" :key="employee.id"
                            class="border-t transition-colors hover:opacity-80"
                            :style="{ 
                                borderColor: themeColors.border,
                                borderStyle: 'solid',
                                borderWidth: '1px'
                            }">
                            <td class="p-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm"
                                         :style="{ backgroundColor: themeColors.primary, color: 'white' }">
                                        {{ getInitials(employee.name) }}
                                    </div>
                                    <div>
                                        <p class="font-medium" :style="{ color: themeColors.textPrimary }">{{ employee.name }}</p>
                                        <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                                            {{ employee.employee_id || 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <span v-if="employee.departments?.length > 0" 
                                      class="text-sm"
                                      :style="{ color: themeColors.textPrimary }">
                                    {{ employee.departments[0].name }}
                                </span>
                                <span v-else 
                                      class="text-sm"
                                      :style="{ color: themeColors.textTertiary }">
                                    No department
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="font-medium" :style="{ color: themeColors.textPrimary }">
                                    {{ calculateMonthlySalary(employee.salary) }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="font-medium" :style="{ color: themeColors.textPrimary }">
                                    {{ calculateAnnualSalary(employee.salary) }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-1 rounded text-xs font-medium"
                                      :style="{
                                          backgroundColor: getSalaryLevel(employee.salary).color + '20',
                                          color: getSalaryLevel(employee.salary).color
                                      }">
                                    {{ getSalaryLevel(employee.salary).text }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-1 rounded-full text-xs font-medium"
                                      :style="{
                                          backgroundColor: employee.is_active ? themeColors.success + '20' : themeColors.danger + '20',
                                          color: employee.is_active ? themeColors.success : themeColors.danger
                                      }">
                                    {{ employee.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center space-x-2">
                                    <Link :href="route('hr.employees.edit', employee.id)"
                                          class="p-1 rounded hover:opacity-80 transition-opacity"
                                          :style="{ color: themeColors.primary }">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </Link>
                                    <button class="p-1 rounded hover:opacity-80 transition-opacity"
                                            :style="{ color: themeColors.warning }"
                                            @click="openSalaryModal(employee)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Salary Edit Modal -->
        <div v-if="salaryModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black bg-opacity-50" @click="closeSalaryModal"></div>
            <div class="relative rounded-xl shadow-xl p-6 w-full max-w-md"
                 :style="{ backgroundColor: themeColors.card, color: themeColors.textPrimary }">
                <h3 class="text-lg font-semibold mb-4">Edit Salary — {{ selectedEmployee?.name }}</h3>
                <form @submit.prevent="submitSalary">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Monthly Salary</label>
                        <input v-model="salaryForm.salary"
                               type="number"
                               min="0"
                               step="0.01"
                               class="w-full px-3 py-2 rounded-lg border text-sm"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"
                               placeholder="0.00" />
                        <p v-if="salaryForm.errors.salary" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ salaryForm.errors.salary }}</p>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" @click="closeSalaryModal"
                                class="px-4 py-2 rounded-lg text-sm font-medium border"
                                :style="{ borderColor: themeColors.border, color: themeColors.textSecondary }">
                            Cancel
                        </button>
                        <button type="submit"
                                :disabled="salaryForm.processing"
                                class="px-4 py-2 rounded-lg text-sm font-medium text-white disabled:opacity-50"
                                :style="{ backgroundColor: themeColors.primary }">
                            {{ salaryForm.processing ? 'Saving...' : 'Save Salary' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
