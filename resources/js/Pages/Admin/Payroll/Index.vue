<template>
    <DashboardLayout title="Payroll Management" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Payroll Management</h1>
                    <p class="text-gray-600 mt-2">Manage employee payroll and compensation.</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="processPayroll" 
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                        <CurrencyDollarIcon class="h-4 w-4 mr-2 inline" />
                        Process Payroll
                    </button>
                </div>
            </div>
        </div>

        <!-- Payroll Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <UsersIcon class="h-8 w-8 text-blue-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Employees</p>
                        <p class="text-2xl font-bold text-gray-900">{{ payrollStats.totalEmployees }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <CurrencyDollarIcon class="h-8 w-8 text-green-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Monthly Payroll</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(payrollStats.monthlyPayroll) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <ClockIcon class="h-8 w-8 text-yellow-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Pending Approvals</p>
                        <p class="text-2xl font-bold text-gray-900">{{ payrollStats.pendingApprovals }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <ChartBarIcon class="h-8 w-8 text-purple-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Overtime Hours</p>
                        <p class="text-2xl font-bold text-gray-900">{{ payrollStats.overtimeHours }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Payroll -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Employee Payroll</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Employee
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Department
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Hours Worked
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Overtime
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Gross Pay
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="employee in employeePayroll" :key="employee.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center mr-4">
                                        <span class="text-sm font-medium text-gray-700">
                                            {{ getInitials(employee.name) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ employee.name }}</div>
                                        <div class="text-sm text-gray-500">{{ employee.employee_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatDepartment(employee.department) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ employee.regular_hours }}h
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ employee.overtime_hours }}h
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ formatCurrency(employee.gross_pay) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(employee.status)">
                                    {{ formatStatus(employee.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button @click="viewPayslip(employee)" class="text-blue-600 hover:text-blue-900">View</button>
                                    <button @click="editPayroll(employee)" class="text-green-600 hover:text-green-900">Edit</button>
                                </div>
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
import { getNavigationForRole } from '@/Utils/navigation.js'
import { formatCurrency as formatCurrencyUtil } from '@/Utils/currency.js'
import {
    CurrencyDollarIcon,
    UsersIcon,
    ClockIcon,
    ChartBarIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    settings: Object,
})

const navigation = computed(() => getNavigationForRole('admin'))

const formatCurrency = (amount) => {
    const currency = props.settings?.currency || 'USD'
    return formatCurrencyUtil(amount, currency)
}

const payrollStats = ref({
    totalEmployees: 45,
    monthlyPayroll: 125680,
    pendingApprovals: 3,
    overtimeHours: 156
})

const employeePayroll = ref([
    {
        id: 1,
        name: 'John Smith',
        employee_id: 'EMP001',
        department: 'front_office',
        regular_hours: 160,
        overtime_hours: 8,
        gross_pay: 3200,
        status: 'approved'
    },
    {
        id: 2,
        name: 'Mary Johnson',
        employee_id: 'EMP002',
        department: 'housekeeping',
        regular_hours: 160,
        overtime_hours: 12,
        gross_pay: 2800,
        status: 'pending'
    }
])

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const formatDepartment = (department) => {
    return department.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getStatusColor = (status) => {
    const colors = {
        approved: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        rejected: 'bg-red-100 text-red-800'
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1)
}

const processPayroll = () => {
    console.log('Processing payroll...')
}

const viewPayslip = (employee) => {
    console.log('Viewing payslip for:', employee.name)
}

const editPayroll = (employee) => {
    console.log('Editing payroll for:', employee.name)
}
</script>