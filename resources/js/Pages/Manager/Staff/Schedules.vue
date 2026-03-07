<template>
    <DashboardLayout title="Employee Schedules" :user="user">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Employee Schedules</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Manage employee work schedules and assignments.</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="printSchedule"
                            class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity"
                            :style="{ backgroundColor: themeColors.border, color: themeColors.textPrimary }">
                        Print
                    </button>
                    <button @click="exportSchedule"
                            class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity"
                            :style="{ backgroundColor: themeColors.success, color: '#000' }">
                        Export CSV
                    </button>
                    <button @click="generateSchedule"
                            class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity"
                            :style="{ backgroundColor: themeColors.warning, color: '#000' }">
                        <CalendarIcon class="h-4 w-4 mr-2 inline" />
                        Auto Generate
                    </button>
                    <button @click="addSchedule"
                            class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity"
                            :style="{ backgroundColor: themeColors.primary, color: '#000' }">
                        <PlusIcon class="h-4 w-4 mr-2 inline" />
                        Add Schedule
                    </button>
                </div>
            </div>
        </div>

        <div v-if="errorMessage" class="rounded-lg p-4 mb-6 border" :style="{ backgroundColor: themeColors.danger, borderColor: themeColors.border, color: '#000' }">
            {{ errorMessage }}
        </div>

        <!-- Schedule Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <CalendarDaysIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.primary }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">This Week</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ scheduleStats.thisWeek }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <UsersIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.success }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Scheduled Staff</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ scheduleStats.scheduledStaff }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <ExclamationTriangleIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.warning }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Conflicts</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ scheduleStats.conflicts }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <ClockIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.secondary }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Total Hours</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ scheduleStats.totalHours }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weekly Calendar View -->
        <div class="shadow-lg rounded-xl p-4 mb-8 border max-h-[70vh] flex flex-col" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xl font-bold" :style="{ color: themeColors.textPrimary }">Weekly Schedule</h3>
                    <p class="text-sm mt-1" :style="{ color: themeColors.textSecondary }">View and manage employee shifts for the week</p>
                </div>
                <div class="flex items-center space-x-3 rounded-lg px-3 py-2 border" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                    <button @click="previousWeek"
                            class="p-1.5 rounded-md transition-opacity hover:opacity-80" :style="{ color: themeColors.textPrimary }">
                        <ChevronLeftIcon class="h-5 w-5" />
                    </button>
                    <span class="text-sm font-semibold px-3" :style="{ color: themeColors.textPrimary }">{{ currentWeek }}</span>
                    <button @click="nextWeek"
                            class="p-1.5 rounded-md transition-opacity hover:opacity-80" :style="{ color: themeColors.textPrimary }">
                        <ChevronRightIcon class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto rounded-lg border flex-1" :style="{ borderColor: themeColors.border }">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="text-left py-3 px-4 font-semibold sticky left-0 z-10 border-r" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                                <div class="flex items-center space-x-2">
                                    <UsersIcon class="h-5 w-5" :style="{ color: themeColors.textSecondary }" />
                                    <span>Team Member</span>
                                </div>
                            </th>
                            <th v-for="(day, dayIndex) in weekDays" :key="day"
                                class="text-center py-3 px-3 font-semibold min-w-[110px] border-r last:border-r-0" :style="{ borderColor: themeColors.border, color: themeColors.textPrimary }">
                                <div class="flex flex-col items-center space-y-1">
                                    <span class="text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">{{ day }}</span>
                                    <span class="text-xs" :style="{ color: themeColors.textTertiary }">{{ getDayDate(dayIndex) }}</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                        <tr v-for="employee in scheduleData" :key="employee.id"
                            class="transition-colors group" :style="hoveredRow === employee.id ? { backgroundColor: themeColors.hover } : {}" @mouseenter="hoveredRow = employee.id" @mouseleave="hoveredRow = null">
                            <td class="py-2.5 px-4 sticky left-0 z-10 border-r" :style="{ backgroundColor: hoveredRow === employee.id ? themeColors.hover : themeColors.card, borderColor: themeColors.border }">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center shadow-sm">
                                        <span class="text-[11px] font-bold text-white">
                                            {{ getInitials(employee.name) }}
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-semibold truncate" :style="{ color: themeColors.textPrimary }">{{ employee.name }}</div>
                                        <div class="text-xs flex items-center mt-0.5" :style="{ color: themeColors.textSecondary }">
                                            <span class="w-1.5 h-1.5 rounded-full mr-1.5" :style="{ backgroundColor: themeColors.textTertiary }"></span>
                                            {{ formatDepartment(employee.department) }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td v-for="(shift, dayIndex) in employee.shifts" :key="dayIndex"
                                class="py-2 px-2 text-center align-top border-r last:border-r-0" :style="{ borderColor: themeColors.border }">
                                <div v-if="shift"
                                     class="group/shift relative">
                                    <div class="cursor-pointer hover:shadow-lg transition-all rounded-lg p-1.5 border-2 min-h-[44px] flex items-center justify-center"
                                         :class="getShiftColorClass(shift.type)"
                                         @click="editSchedule(employee, dayIndex)">
                                        <div class="flex flex-col items-center space-y-1">
                                            <div class="text-xs font-bold">{{ formatTime(shift.start) }}</div>
                                            <div class="w-8 h-0.5 bg-current opacity-50"></div>
                                            <div class="text-xs font-bold">{{ formatTime(shift.end) }}</div>
                                        </div>
                                    </div>
                                    <div class="absolute top-1 right-1 opacity-0 group-hover/shift:opacity-100 transition-opacity flex space-x-1">
                                        <button @click.stop="editSchedule(employee, dayIndex)"
                                                class="p-1 rounded shadow-sm transition-opacity hover:opacity-80"
                                                :style="{ backgroundColor: themeColors.card, color: themeColors.primary, border: `1px solid ${themeColors.border}` }">
                                            <PencilIcon class="h-3 w-3" />
                                        </button>
                                        <button @click.stop="deleteSchedule(employee, dayIndex)"
                                                class="p-1 rounded shadow-sm transition-opacity hover:opacity-80"
                                                :style="{ backgroundColor: themeColors.card, color: themeColors.danger, border: `1px solid ${themeColors.border}` }">
                                            <TrashIcon class="h-3 w-3" />
                                        </button>
                                    </div>
                                </div>
                                <div v-else
                                     class="empty-cell h-12 flex items-center justify-center border-2 border-dashed rounded-lg transition-all cursor-pointer group/empty"
                                     :style="hoveredRow === employee.id ? { borderColor: themeColors.primary, backgroundColor: themeColors.hover } : { borderColor: themeColors.border }"
                                     @click="addScheduleForEmployee(employee, dayIndex)">
                                    <div class="text-center opacity-0 group-hover/empty:opacity-100 transition-opacity">
                                        <PlusIcon class="h-5 w-5 mx-auto mb-1" :style="{ color: themeColors.primary }" />
                                        <span class="text-xs font-medium" :style="{ color: themeColors.primary }">Add Shift</span>
                                    </div>
                                    <div class="text-center group-hover/empty:hidden">
                                        <span class="text-xs" :style="{ color: themeColors.textTertiary }">—</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Schedule Requests -->
        <div class="shadow rounded-lg overflow-hidden border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Schedule Requests</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Employee
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Request Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Date/Time
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Reason
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="request in scheduleRequests" :key="request.id" class="transition-colors" :style="hoveredRow === request.id ? { backgroundColor: themeColors.hover } : {}" @mouseenter="hoveredRow = request.id" @mouseleave="hoveredRow = null">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ request.employee_name }}</div>
                                <div class="text-sm" :style="{ color: themeColors.textSecondary }">{{ request.employee_id }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="getRequestTypePillStyle(request.type)">
                                    {{ formatRequestType(request.type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ request.date_time }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textPrimary }">
                                {{ request.reason }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="getStatusPillStyle(request.status)">
                                    {{ formatStatus(request.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button v-if="request.status === 'pending'"
                                            @click="approveRequest(request)"
                                            class="hover:opacity-80 transition-opacity" :style="{ color: themeColors.success }">Approve</button>
                                    <button v-if="request.status === 'pending'"
                                            @click="rejectRequest(request)"
                                            class="hover:opacity-80 transition-opacity" :style="{ color: themeColors.danger }">Reject</button>
                                    <button @click="viewRequest(request)" class="hover:opacity-80 transition-opacity" :style="{ color: themeColors.primary }">View</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Schedule Modal -->
        <div v-if="showAddScheduleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="rounded-xl shadow-2xl w-full max-w-md border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                            <PlusIcon class="h-5 w-5" />
                            <span>Add New Schedule</span>
                        </h3>
                        <button @click="closeAddScheduleModal" class="text-white/80 hover:text-white transition-colors">
                            <XMarkIcon class="h-6 w-6" />
                        </button>
                    </div>
                </div>

                <form @submit.prevent="submitAddSchedule" class="p-6 space-y-5">
                    <div>
                        <label for="user_id" class="block text-sm font-semibold mb-2" :style="{ color: themeColors.textSecondary }">
                            <UsersIcon class="h-4 w-4 inline mr-1" :style="{ color: themeColors.textTertiary }" />
                            Team Member
                        </label>
                        <select id="user_id" v-model="newSchedule.user_id" required
                                class="w-full rounded-lg border shadow-sm py-2.5 px-3 text-sm focus:outline-none"
                                :style="selectStyle">
                            <option value="" disabled>Choose a team member...</option>
                            <option v-for="user in staffUsers" :key="user.id" :value="user.id">
                                {{ `${user.first_name} ${user.last_name}`.trim() || user.employee_id || `EMP${user.id}` }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="work_shift_id" class="block text-sm font-semibold mb-2" :style="{ color: themeColors.textSecondary }">
                            <ClockIcon class="h-4 w-4 inline mr-1" :style="{ color: themeColors.textTertiary }" />
                            Work Shift
                        </label>
                        <select id="work_shift_id" v-model="newSchedule.work_shift_id" required
                                class="w-full rounded-lg border shadow-sm py-2.5 px-3 text-sm focus:outline-none"
                                :style="selectStyle">
                            <option value="" disabled>Select a shift...</option>
                            <option v-for="shift in availableShifts" :key="shift.id" :value="shift.id">
                                {{ shift.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="date" class="block text-sm font-semibold mb-2" :style="{ color: themeColors.textSecondary }">
                            <CalendarIcon class="h-4 w-4 inline mr-1" :style="{ color: themeColors.textTertiary }" />
                            Schedule Date
                        </label>
                        <input
                            type="date"
                            id="date"
                            v-model="newSchedule.date"
                            required
                            @keydown.prevent
                            @click="$event.target.showPicker && $event.target.showPicker()"
                            class="w-full rounded-lg border shadow-sm cursor-pointer py-2.5 px-3 text-sm focus:outline-none"
                            :style="selectStyle"
                        >
                    </div>

                    <div class="rounded-lg p-4 border" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                        <div class="flex items-center">
                            <input id="is_recurring" type="checkbox" v-model="newSchedule.is_recurring"
                                   class="h-4 w-4 rounded" :style="{ accentColor: themeColors.primary }">
                            <label for="is_recurring" class="ml-2 block text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                Repeat this schedule weekly
                            </label>
                        </div>
                    </div>

                    <div v-if="newSchedule.is_recurring" class="space-y-4 rounded-lg p-4 border" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                        <div>
                            <label class="block text-sm font-semibold mb-2" :style="{ color: themeColors.textSecondary }">Repeat on Days</label>
                            <div class="flex flex-wrap gap-2">
                                <label v-for="day in ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']"
                                       :key="day"
                                       class="flex items-center px-3 py-2 border rounded-lg cursor-pointer transition-colors"
                                       :style="newSchedule.recurring_days.includes(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].indexOf(day) + 1) ? { backgroundColor: themeColors.hover, borderColor: themeColors.primary } : { backgroundColor: themeColors.card, borderColor: themeColors.border }">
                                    <input type="checkbox" :value="['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].indexOf(day) + 1"
                                           v-model="newSchedule.recurring_days"
                                           class="h-4 w-4 rounded" :style="{ accentColor: themeColors.primary }">
                                    <span class="ml-2 text-sm font-medium" :style="{ color: themeColors.textSecondary }">{{ day }}</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-semibold mb-2" :style="{ color: themeColors.textSecondary }">End Date (Optional)</label>
                            <input
                                type="date"
                                id="end_date"
                                v-model="newSchedule.end_date"
                                @keydown.prevent
                                @click="$event.target.showPicker && $event.target.showPicker()"
                                class="w-full rounded-lg border shadow-sm cursor-pointer py-2.5 px-3 text-sm focus:outline-none"
                                :style="selectStyle"
                            >
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t" :style="{ borderColor: themeColors.border }">
                        <button type="button" @click="closeAddScheduleModal"
                                class="px-5 py-2.5 text-sm font-medium rounded-lg hover:opacity-90 transition-opacity border"
                                :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 border border-transparent rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md transition-all">
                            Create Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Schedule Modal -->
        <div v-if="showEditScheduleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="rounded-xl shadow-2xl w-full max-w-md border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                            <PencilIcon class="h-5 w-5" />
                            <span>Edit Shift Schedule</span>
                        </h3>
                        <button @click="closeEditScheduleModal" class="text-white/80 hover:text-white transition-colors">
                            <XMarkIcon class="h-6 w-6" />
                        </button>
                    </div>
                </div>

                <form @submit.prevent="submitEditSchedule" class="p-6 space-y-5">
                    <div class="rounded-lg p-4 space-y-3 border" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border }">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide mb-1" :style="{ color: themeColors.textTertiary }">Team Member</label>
                            <div class="flex items-center space-x-2">
                                <UsersIcon class="h-4 w-4" :style="{ color: themeColors.textTertiary }" />
                                <span class="font-medium" :style="{ color: themeColors.textPrimary }">{{ currentSchedule?.employee_name }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide mb-1" :style="{ color: themeColors.textTertiary }">Day</label>
                                <div class="flex items-center space-x-2">
                                    <CalendarDaysIcon class="h-4 w-4" :style="{ color: themeColors.textTertiary }" />
                                    <span class="font-medium" :style="{ color: themeColors.textPrimary }">{{ props.weekDays[currentSchedule?.day_index] }}</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide mb-1" :style="{ color: themeColors.textTertiary }">Current Time</label>
                                <div class="flex items-center space-x-2">
                                    <ClockIcon class="h-4 w-4" :style="{ color: themeColors.textTertiary }" />
                                    <span class="font-medium" :style="{ color: themeColors.textPrimary }">
                                        {{ currentSchedule?.shift_details ? `${formatTime(currentSchedule.shift_details.start)} - ${formatTime(currentSchedule.shift_details.end)}` : 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="edit_work_shift_id" class="block text-sm font-semibold mb-2" :style="{ color: themeColors.textSecondary }">
                            Select New Shift
                        </label>
                        <select id="edit_work_shift_id" v-model="editScheduleData.work_shift_id" required
                                class="w-full rounded-lg border shadow-sm py-2.5 px-3 text-sm focus:outline-none"
                                :style="selectStyle">
                            <option value="" disabled>Choose a shift...</option>
                            <option v-for="shift in availableShifts" :key="shift.id" :value="shift.id">
                                {{ shift.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="edit_date" class="block text-sm font-semibold mb-2" :style="{ color: themeColors.textSecondary }">
                            Schedule Date
                        </label>
                        <input
                            type="date"
                            id="edit_date"
                            v-model="editScheduleData.date"
                            required
                            @keydown.prevent
                            @click="$event.target.showPicker && $event.target.showPicker()"
                            class="w-full rounded-lg border shadow-sm cursor-pointer py-2.5 px-3 text-sm focus:outline-none"
                            :style="selectStyle"
                        >
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t" :style="{ borderColor: themeColors.border }">
                        <button type="button" @click="closeEditScheduleModal"
                                class="px-5 py-2.5 text-sm font-medium rounded-lg hover:opacity-90 transition-opacity border"
                                :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, color: themeColors.textPrimary }">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 border border-transparent rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md transition-all">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme'
import {
    PlusIcon,
    CalendarIcon,
    CalendarDaysIcon,
    UsersIcon,
    ExclamationTriangleIcon,
    ClockIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    PencilIcon,
    TrashIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    scheduleStats: Object,
    scheduleData: Array,
    scheduleRequests: Array,
    currentWeek: String,
    weekStart: String,
    weekDays: Array,
    staffUsers: {
        type: Array,
        default: () => [],
    },
    workShifts: {
        type: Array,
        default: () => [],
    },
})

const errorMessage = ref('')

const hoveredRow = ref(null)

const { currentTheme } = useTheme()

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
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)'
}))

const selectStyle = computed(() => ({
    backgroundColor: themeColors.value.background,
    borderColor: themeColors.value.border,
    color: themeColors.value.textPrimary,
}))

const workShiftOptions = computed(() => {
    return (props.workShifts || []).map(shift => ({
        id: shift.id,
        name: shift.name,
        start_time: shift.start_time,
        end_time: shift.end_time,
        hours: shift.hours,
        is_overnight: shift.is_overnight,
    }))
})

// Modal State
const showAddScheduleModal = ref(false)
const showEditScheduleModal = ref(false)
const showViewRequestModal = ref(false)
const showGenerateScheduleModal = ref(false)

const newSchedule = ref({
    user_id: null,
    work_shift_id: null,
    date: new Date().toISOString().split('T')[0],
    is_recurring: false,
    recurring_days: [1, 2, 3, 4, 5], // Monday to Friday by default
    end_date: null,
})

const currentSchedule = ref(null)
const currentRequest = ref(null)
const availableShifts = ref([])
const editScheduleData = ref({
    work_shift_id: null,
    date: new Date().toISOString().split('T')[0],
})

const getInitials = (name) => {
    if (!name) return '';
    return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const formatDepartment = (department) => {
    if (!department) return 'General'
    return department.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getShiftColor = (type) => {
    const colors = {
        regular: 'bg-blue-100 text-blue-800',
        overtime: 'bg-orange-100 text-orange-800',
        part_time: 'bg-green-100 text-green-800',
        night: 'bg-purple-100 text-purple-800'
    }
    return colors[type] || 'bg-gray-100 text-gray-800'
}

const getShiftColorClass = (type) => {
    const classes = {
        regular: 'bg-gradient-to-br from-blue-500 to-blue-600 text-white border-blue-400',
        overtime: 'bg-gradient-to-br from-orange-500 to-orange-600 text-white border-orange-400',
        part_time: 'bg-gradient-to-br from-green-500 to-green-600 text-white border-green-400',
        night: 'bg-gradient-to-br from-purple-500 to-purple-600 text-white border-purple-400'
    }
    return classes[type] || 'bg-gradient-to-br from-gray-400 to-gray-500 text-white border-gray-300'
}

const formatTime = (time) => {
    if (!time) return ''
    const [hours, minutes] = time.split(':')
    const hour = parseInt(hours)
    const ampm = hour >= 12 ? 'PM' : 'AM'
    const displayHour = hour % 12 || 12
    return `${displayHour}:${minutes} ${ampm}`
}

const getDayDate = (dayIndex) => {
    if (!props.weekStart) return ''
    const baseDate = new Date(props.weekStart)
    baseDate.setDate(baseDate.getDate() + dayIndex)
    return baseDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const getRequestTypePillStyle = (type) => {
    const key = (type || '').toLowerCase()
    if (key === 'time_off') return { backgroundColor: themeColors.value.warning, color: '#000' }
    if (key === 'shift_swap') return { backgroundColor: themeColors.value.primary, color: '#000' }
    if (key === 'overtime') return { backgroundColor: themeColors.value.warning, color: '#000' }
    if (key === 'schedule_change') return { backgroundColor: themeColors.value.secondary, color: '#000' }
    return { backgroundColor: themeColors.value.border, color: themeColors.value.textPrimary }
}

const getStatusPillStyle = (status) => {
    const key = (status || '').toLowerCase()
    if (key === 'pending') return { backgroundColor: themeColors.value.warning, color: '#000' }
    if (key === 'approved') return { backgroundColor: themeColors.value.success, color: '#000' }
    if (key === 'rejected') return { backgroundColor: themeColors.value.danger, color: '#000' }
    return { backgroundColor: themeColors.value.border, color: themeColors.value.textPrimary }
}

const formatRequestType = (type) => {
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1)
}

const generateSchedule = () => {
    showGenerateScheduleModal.value = true
}

const printSchedule = () => {
    const params = props.weekStart ? { week_start: props.weekStart } : {}
    window.open(route('manager.staff.schedules.print', params), '_blank')
}

const exportSchedule = () => {
    const params = props.weekStart ? { week_start: props.weekStart } : {}
    window.location.href = route('manager.staff.schedules.export', params)
}

const addSchedule = () => {
    availableShifts.value = workShiftOptions.value.map(shift => ({
        id: shift.id,
        name: `${shift.name} (${shift.start_time} - ${shift.end_time})`,
    }))

    showAddScheduleModal.value = true
}

const changeWeek = (direction) => {
    // direction: -1 for previous, +1 for next
    const base = props.weekStart ? new Date(props.weekStart) : new Date()
    // Move by 7 days
    base.setDate(base.getDate() + direction * 7)
    const newStart = base.toISOString().split('T')[0]

    router.get(route('manager.staff.schedules', { week_start: newStart }), {}, {
        preserveScroll: true,
        preserveState: true,
    })
}

const previousWeek = () => changeWeek(-1)
const nextWeek = () => changeWeek(1)

const approveRequest = (request) => {
    router.post(route('manager.staff.schedules.requests.approve', {request: request.id}), {}, {
        onSuccess: () => {
            request.status = 'approved'
            errorMessage.value = ''
        },
        onError: (errors) => {
            console.error('Error approving request:', errors)
            errorMessage.value = 'Error approving request. Please try again.'
        }
    })
}

const rejectRequest = (request) => {
    router.post(route('manager.staff.schedules.requests.reject', {request: request.id}), {}, {
        onSuccess: () => {
            request.status = 'rejected'
            errorMessage.value = ''
        },
        onError: (errors) => {
            console.error('Error rejecting request:', errors)
            errorMessage.value = 'Error rejecting request. Please try again.'
        }
    })
}

const viewRequest = (request) => {
    currentRequest.value = request
    showViewRequestModal.value = true
}

const closeAddScheduleModal = () => {
    showAddScheduleModal.value = false
    newSchedule.value = {
        user_id: null,
        work_shift_id: null,
        date: new Date().toISOString().split('T')[0],
        is_recurring: false,
        recurring_days: [1, 2, 3, 4, 5],
        end_date: null,
    }
}

const closeEditScheduleModal = () => {
    showEditScheduleModal.value = false
    currentSchedule.value = null
}

const closeViewRequestModal = () => {
    showViewRequestModal.value = false
    currentRequest.value = null
}

const closeGenerateScheduleModal = () => {
    showGenerateScheduleModal.value = false
}

const submitAddSchedule = () => {
    router.post(route('manager.staff.schedules.store'), newSchedule.value, {
        onSuccess: () => {
            closeAddScheduleModal()
            window.location.reload()
            errorMessage.value = ''
        },
        onError: (errors) => {
            console.error('Error creating schedule:', errors)
            errorMessage.value = 'Error creating schedule. Please check the form and try again.'
        }
    })
}

const editSchedule = (employee, dayIndex) => {
    const shift = employee.shifts[dayIndex]
    if (!shift) return

    availableShifts.value = workShiftOptions.value.map(shift => ({
        id: shift.id,
        name: `${shift.name} (${shift.start_time} - ${shift.end_time})`,
    }))

    // Set up edit data
    currentSchedule.value = {
        employee_id: employee.id,
        employee_name: employee.name,
        day_index: dayIndex,
        shift_details: shift,
        schedule_id: employee.shifts[dayIndex].id // This would need to be in the data
    }

    // Set default values for edit form
    editScheduleData.value = {
        work_shift_id: shift.work_shift_id || 1,
        date: getDateForDayIndex(dayIndex)
    }

    showEditScheduleModal.value = true
}

const deleteSchedule = (employee, dayIndex) => {
    const shift = employee.shifts[dayIndex]
    if (!shift) return

    // Use the employee_shift id from the schedule data and call the backend destroy route
    router.delete(route('manager.staff.schedules.destroy', { schedule: shift.id }), {
        onSuccess: () => {
            window.location.reload()
            errorMessage.value = ''
        },
        onError: (errors) => {
            console.error('Error deleting schedule:', errors)
            errorMessage.value = 'Error deleting schedule. Please try again.'
        }
    })
}

const submitGenerateSchedule = () => {
    router.post(route('manager.staff.schedules.generate'), {}, {
        onSuccess: () => {
            closeGenerateScheduleModal()
            window.location.reload()
            errorMessage.value = ''
        },
        onError: (errors) => {
            console.error('Error generating schedules:', errors)
            errorMessage.value = 'Error generating schedules. Please try again.'
        }
    })
}

const addScheduleForEmployee = (employee, dayIndex) => {
    // Set up for adding schedule to a specific employee and day
    newSchedule.value = {
        user_id: employee.id,
        work_shift_id: null,
        date: getDateForDayIndex(dayIndex),
        is_recurring: false,
        recurring_days: [dayIndex + 1], // Start with the selected day
        end_date: null,
    }

    availableShifts.value = workShiftOptions.value.map(shift => ({
        id: shift.id,
        name: `${shift.name} (${shift.start_time} - ${shift.end_time})`,
    }))

    showAddScheduleModal.value = true
}

const getDateForDayIndex = (dayIndex) => {
    const base = props.weekStart ? new Date(props.weekStart) : new Date()
    const targetDate = new Date(base)
    targetDate.setDate(base.getDate() + dayIndex)
    return targetDate.toISOString().split('T')[0]
}

const submitEditSchedule = () => {
    // Find the employee shift ID - this would need to be stored in the data
    // For now, we'll simulate an update
    router.put(route('manager.staff.schedules.update', { schedule: currentSchedule.value.schedule_id }), editScheduleData.value, {
        onSuccess: () => {
            closeEditScheduleModal()
            window.location.reload()
            errorMessage.value = ''
        },
        onError: (errors) => {
            console.error('Error updating schedule:', errors)
            errorMessage.value = 'Error updating schedule. Please try again.'
        }
    })
}
</script>

