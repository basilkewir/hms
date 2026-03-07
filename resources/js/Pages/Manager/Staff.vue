<template>
    <DashboardLayout title="Staff Management" :user="user" :navigation="navigation">
        <!-- Staff Management Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Staff Management</h1>
                    <p class="text-gray-600 mt-2">Manage hotel staff, schedules, and performance.</p>
                </div>
                <div class="flex space-x-3">
                    <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        <UserPlusIcon class="h-4 w-4 mr-2 inline" />
                        Add Staff
                    </button>
                    <button class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                        <CalendarIcon class="h-4 w-4 mr-2 inline" />
                        Manage Schedules
                    </button>
                </div>
            </div>
        </div>

        <!-- Staff Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <UserGroupIcon class="h-8 w-8 text-blue-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Staff</p>
                        <p class="text-2xl font-bold text-gray-900">{{ staffStats.total }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <CheckCircleIcon class="h-8 w-8 text-green-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">On Duty</p>
                        <p class="text-2xl font-bold text-gray-900">{{ staffStats.onDuty }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <ClockIcon class="h-8 w-8 text-yellow-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">On Break</p>
                        <p class="text-2xl font-bold text-gray-900">{{ staffStats.onBreak }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <ExclamationTriangleIcon class="h-8 w-8 text-red-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Absent</p>
                        <p class="text-2xl font-bold text-gray-900">{{ staffStats.absent }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Filters and Search -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
                    <div>
                        <input type="text" v-model="searchQuery" placeholder="Search staff..."
                               class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <select v-model="selectedDepartment"
                                class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Departments</option>
                            <option value="front_desk">Front Desk</option>
                            <option value="housekeeping">Housekeeping</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="management">Management</option>
                            <option value="accounting">Accounting</option>
                        </select>
                    </div>
                    <div>
                        <select v-model="selectedStatus"
                                class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Status</option>
                            <option value="on_duty">On Duty</option>
                            <option value="off_duty">Off Duty</option>
                            <option value="on_break">On Break</option>
                            <option value="absent">Absent</option>
                        </select>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                        <DocumentTextIcon class="h-4 w-4 mr-2 inline" />
                        Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Staff List -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Staff Members</h3>
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
                                Position
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Shift
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Performance
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="staff in filteredStaff" :key="staff.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center mr-4">
                                        <span class="text-sm font-medium text-gray-700">
                                            {{ staff.first_name.charAt(0) }}{{ staff.last_name.charAt(0) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ staff.first_name }} {{ staff.last_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ staff.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getDepartmentColor(staff.department)">
                                    {{ formatDepartment(staff.department) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ staff.position }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(staff.status)">
                                    {{ formatStatus(staff.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ staff.current_shift }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-blue-600 h-2 rounded-full" 
                                             :style="{ width: staff.performance + '%' }"></div>
                                    </div>
                                    <span class="text-sm text-gray-600">{{ staff.performance }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button class="text-blue-600 hover:text-blue-900">View</button>
                                    <button class="text-green-600 hover:text-green-900">Edit</button>
                                    <button class="text-purple-600 hover:text-purple-900">Schedule</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Today's Schedule -->
        <div class="bg-white shadow rounded-lg p-6 mt-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Today's Schedule</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="shift in todaysSchedule" :key="shift.name" class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">{{ shift.name }}</h4>
                    <p class="text-sm text-gray-600 mb-3">{{ shift.time }}</p>
                    <div class="space-y-2">
                        <div v-for="employee in shift.employees" :key="employee.id" 
                             class="flex items-center justify-between text-sm">
                            <span>{{ employee.name }}</span>
                            <span class="text-gray-500">{{ employee.department }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import {
    UserGroupIcon,
    UserPlusIcon,
    CalendarIcon,
    CheckCircleIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    DocumentTextIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
})

const navigation = computed(() => getNavigationForRole('manager'))

const searchQuery = ref('')
const selectedDepartment = ref('')
const selectedStatus = ref('')

const staffStats = {
    total: 45,
    onDuty: 28,
    onBreak: 5,
    absent: 3,
}

const staffMembers = [
    { id: 1, first_name: 'John', last_name: 'Doe', email: 'john.doe@hotel.com', department: 'front_desk', position: 'Front Desk Agent', status: 'on_duty', current_shift: 'Morning', performance: 92 },
    { id: 2, first_name: 'Jane', last_name: 'Smith', email: 'jane.smith@hotel.com', department: 'housekeeping', position: 'Housekeeper', status: 'on_duty', current_shift: 'Morning', performance: 88 },
    { id: 3, first_name: 'Mike', last_name: 'Johnson', email: 'mike.johnson@hotel.com', department: 'maintenance', position: 'Maintenance Tech', status: 'on_break', current_shift: 'Day', performance: 95 },
    { id: 4, first_name: 'Sarah', last_name: 'Wilson', email: 'sarah.wilson@hotel.com', department: 'management', position: 'Assistant Manager', status: 'on_duty', current_shift: 'Day', performance: 97 },
    { id: 5, first_name: 'David', last_name: 'Brown', email: 'david.brown@hotel.com', department: 'accounting', position: 'Accountant', status: 'off_duty', current_shift: 'Day', performance: 90 },
]

const todaysSchedule = [
    {
        name: 'Morning Shift',
        time: '6:00 AM - 2:00 PM',
        employees: [
            { id: 1, name: 'John Doe', department: 'Front Desk' },
            { id: 2, name: 'Jane Smith', department: 'Housekeeping' },
            { id: 3, name: 'Bob Wilson', department: 'Maintenance' },
        ]
    },
    {
        name: 'Day Shift',
        time: '2:00 PM - 10:00 PM',
        employees: [
            { id: 4, name: 'Sarah Wilson', department: 'Management' },
            { id: 5, name: 'Mike Johnson', department: 'Maintenance' },
            { id: 6, name: 'Lisa Davis', department: 'Front Desk' },
        ]
    },
    {
        name: 'Night Shift',
        time: '10:00 PM - 6:00 AM',
        employees: [
            { id: 7, name: 'Tom Anderson', department: 'Security' },
            { id: 8, name: 'Amy Chen', department: 'Front Desk' },
        ]
    }
]

const filteredStaff = computed(() => {
    return staffMembers.filter(staff => {
        const matchesSearch = !searchQuery.value || 
            staff.first_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            staff.last_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            staff.email.toLowerCase().includes(searchQuery.value.toLowerCase())
        
        const matchesDepartment = !selectedDepartment.value || staff.department === selectedDepartment.value
        const matchesStatus = !selectedStatus.value || staff.status === selectedStatus.value
        
        return matchesSearch && matchesDepartment && matchesStatus
    })
})

const getDepartmentColor = (department) => {
    const colors = {
        front_desk: 'bg-blue-100 text-blue-800',
        housekeeping: 'bg-green-100 text-green-800',
        maintenance: 'bg-yellow-100 text-yellow-800',
        management: 'bg-purple-100 text-purple-800',
        accounting: 'bg-indigo-100 text-indigo-800',
    }
    return colors[department] || 'bg-gray-100 text-gray-800'
}

const getStatusColor = (status) => {
    const colors = {
        on_duty: 'bg-green-100 text-green-800',
        off_duty: 'bg-gray-100 text-gray-800',
        on_break: 'bg-yellow-100 text-yellow-800',
        absent: 'bg-red-100 text-red-800',
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const formatDepartment = (department) => {
    return department.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatStatus = (status) => {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}
</script>
