<template>
    <DashboardLayout title="Employee Schedules" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Employee Schedules</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Manage employee work schedules and assignments.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="exportSchedule" 
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{ 
                                backgroundColor: '#8b5cf6',
                            }"
                            @mouseenter="$event.target.style.backgroundColor = '#7c3aed'"
                            @mouseleave="$event.target.style.backgroundColor = '#8b5cf6'">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                        Export CSV
                    </button>
                    <button @click="addSchedule"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Add Schedule
                    </button>
                </div>
            </div>
        </div>

        <!-- Schedule Overview Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                        <CalendarDaysIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">This Week</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ scheduleStats?.thisWeek || 0 }}</p>
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
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                        <UsersIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Scheduled Staff</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ scheduleStats?.scheduledStaff || 0 }}</p>
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
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(250, 204, 21, 0.1)' }">
                        <ExclamationTriangleIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Conflicts</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ scheduleStats?.conflicts || 0 }}</p>
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
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(139, 92, 246, 0.1)' }">
                        <ClockIcon class="h-6 w-6" :style="{ color: '#8b5cf6' }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Hours</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ scheduleStats?.totalHours || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hourly Schedule Grid -->
        <div class="shadow-lg rounded-xl p-4 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xl font-bold mb-1"
                        :style="{ color: themeColors.textPrimary }">Weekly Hourly Schedule</h3>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Click on any time slot to assign a worker</p>
                </div>
                <div class="flex items-center space-x-3 px-3 py-2 rounded-lg"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }">
                    <button @click="previousWeek" 
                            class="p-1.5 rounded-md transition-colors"
                            :style="{ color: themeColors.textSecondary }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                        <ChevronLeftIcon class="h-5 w-5" />
                    </button>
                    <span class="text-sm font-semibold px-3"
                          :style="{ color: themeColors.textPrimary }">{{ currentWeekRange }}</span>
                    <button @click="nextWeek" 
                            class="p-1.5 rounded-md transition-colors"
                            :style="{ color: themeColors.textSecondary }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                        <ChevronRightIcon class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <div class="overflow-auto max-h-[70vh] rounded-lg border relative"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="sticky left-0 z-20 text-left py-3 px-4 font-semibold"
                                :style="{ 
                                    color: themeColors.textSecondary,
                                    borderColor: themeColors.border,
                                    borderStyle: 'solid',
                                    borderWidth: '1px',
                                    backgroundColor: themeColors.card
                                }">
                                Time
                            </th>
                            <th v-for="day in weekDays" :key="day"
                                class="sticky top-0 z-10 text-center py-3 px-3 font-semibold min-w-[120px]"
                                :style="{ 
                                    color: themeColors.textSecondary,
                                    borderColor: themeColors.border,
                                    borderStyle: 'solid',
                                    borderWidth: '1px',
                                    backgroundColor: themeColors.card
                                }">
                                <div class="flex flex-col items-center space-y-1">
                                    <span class="text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textTertiary }">{{ day }}</span>
                                    <span class="text-xs" :style="{ color: themeColors.textTertiary }">{{ getDayDate(day) }}</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody :style="{ backgroundColor: themeColors.card }">
                        <tr v-for="hour in hours" :key="hour" 
                            class="transition-colors">
                            <td class="sticky left-0 z-10 py-2 px-4 font-medium text-xs"
                                :style="{ 
                                    backgroundColor: themeColors.card,
                                    borderColor: themeColors.border,
                                    borderStyle: 'solid',
                                    borderWidth: '1px',
                                    color: themeColors.textSecondary
                                }">
                                {{ hour }}
                            </td>
                            <td v-for="day in weekDays" :key="`${hour}-${day}`"
                                class="py-1 px-1 text-center align-top min-h-[60px] relative"
                                :style="{ 
                                    borderColor: themeColors.border,
                                    borderStyle: 'solid',
                                    borderWidth: '1px'
                                }">
                                <div class="h-full min-h-[50px]">
                                    <div v-for="assignment in getAssignmentsForSlot(day, hour)" 
                                         :key="assignment.id"
                                         class="absolute inset-x-0 top-0 p-1 cursor-pointer hover:shadow-md transition-all rounded"
                                         :class="getAssignmentColorClass(assignment.type)"
                                         @click="editAssignment(assignment)">
                                        <div class="text-xs font-medium truncate">
                                            {{ assignment.user_name }}
                                        </div>
                                        <div class="text-xs opacity-75">
                                            {{ assignment.shift_name }}
                                        </div>
                                    </div>
                                    <div v-if="getAssignmentsForSlot(day, hour).length === 0"
                                         class="h-full flex items-center justify-center cursor-pointer hover:bg-opacity-20 rounded group/slot transition-all min-h-[50px] border-2"
                                         :style="{ 
                                             borderColor: themeColors.border, 
                                             borderStyle: 'dashed', 
                                             borderWidth: '1px',
                                             backgroundColor: 'transparent'
                                         }"
                                         @click="openAssignmentModal(day, hour)"
                                         @mouseenter="$event.target.style.backgroundColor = 'rgba(59, 130, 246, 0.1)'"
                                         @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                                        <div class="opacity-0 group-hover/slot:opacity-100 transition-all">
                                            <PlusIcon class="h-4 w-4 mx-auto mb-1" :style="{ color: themeColors.primary }" />
                                            <div class="text-xs font-medium" :style="{ color: themeColors.primary }">Add</div>
                                        </div>
                                        <div class="opacity-100 group-hover/slot:opacity-0 transition-opacity">
                                            <span class="text-xs" :style="{ color: themeColors.textTertiary }">—</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Schedule Requests -->
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
             class="shadow rounded-lg overflow-hidden border">
            <div :style="{ borderColor: themeColors.border }" class="px-6 py-4 border-b">
                <h3 :style="{ color: themeColors.textPrimary }" class="text-lg font-medium">Schedule Requests</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Employee</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Request Type</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Date/Time</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Reason</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="request in scheduleRequests" :key="request.id"
                            :style="hoveredRow === request.id ? { backgroundColor: themeColors.hover } : {}"
                            @mouseenter="hoveredRow = request.id"
                            @mouseleave="hoveredRow = null"
                            class="transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div :style="{ color: themeColors.textPrimary }" class="text-sm font-medium">{{ request.employee_name }}</div>
                                <div :style="{ color: themeColors.textTertiary }" class="text-sm">{{ request.employee_id }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getRequestTypeColor(request.type)">
                                    {{ formatRequestType(request.type) }}
                                </span>
                            </td>
                            <td :style="{ color: themeColors.textPrimary }" class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ request.date_time }}
                            </td>
                            <td :style="{ color: themeColors.textPrimary }" class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ request.reason }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(request.status)">
                                    {{ formatStatus(request.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button v-if="request.status === 'pending'"
                                            @click="approveRequest(request)"
                                            :style="{ color: themeColors.success }" 
                                            class="hover:opacity-80">Approve</button>
                                    <button v-if="request.status === 'pending'"
                                            @click="rejectRequest(request)"
                                            :style="{ color: themeColors.danger }" 
                                            class="hover:opacity-80">Reject</button>
                                    <button @click="viewRequest(request)" 
                                            :style="{ color: themeColors.primary }" 
                                            class="hover:opacity-80">View</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Schedule Modal -->
        <div v-if="showAddScheduleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="rounded-lg shadow-xl w-full max-w-md p-6" :style="{ backgroundColor: themeColors.card }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Add New Schedule</h3>
                    <button @click="closeAddScheduleModal" class="transition-colors" :style="{ color: themeColors.textSecondary }">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitAddSchedule" class="space-y-4">
                    <div>
                        <label for="user_id" class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                            <UsersIcon class="h-4 w-4 inline mr-1" :style="{ color: themeColors.textSecondary }" />
                            Team Member
                        </label>
                        <select id="user_id" v-model="newSchedule.user_id" required
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="" disabled>Choose a team member...</option>
                            <option v-for="user in staffUsers" :key="user.id" :value="user.id">
                                {{ `${user.first_name} ${user.last_name}`.trim() || user.employee_id || `EMP${user.id}` }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="work_shift_id" class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                            <ClockIcon class="h-4 w-4 inline mr-1" :style="{ color: themeColors.textSecondary }" />
                            Work Shift
                        </label>
                        <select id="work_shift_id" v-model="newSchedule.work_shift_id" required
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="" disabled>Select a shift...</option>
                            <option v-for="shift in availableShifts" :key="shift.id" :value="shift.id">
                                {{ shift.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="date" class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                            <CalendarIcon class="h-4 w-4 inline mr-1" :style="{ color: themeColors.textSecondary }" />
                            Schedule Date
                        </label>
                        <div class="relative">
                            <input
                                type="date"
                                id="date"
                                v-model="newSchedule.date"
                                required
                                @keydown.prevent
                                @click="$event.target.showPicker && $event.target.showPicker()"
                                class="w-full rounded-md pl-10 pr-3 py-2 focus:outline-none transition-colors cursor-pointer"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }"
                            >
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                <CalendarIcon class="h-5 w-5" :style="{ color: themeColors.textSecondary }" />
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg p-4"
                         :style="{ 
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="flex items-center">
                            <input id="is_recurring" type="checkbox" v-model="newSchedule.is_recurring"
                                   class="h-4 w-4 rounded focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                            <label for="is_recurring" class="ml-2 block text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                Repeat this schedule weekly
                            </label>
                        </div>
                    </div>

                    <div v-if="newSchedule.is_recurring" class="space-y-4 rounded-lg p-4"
                         :style="{ 
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Repeat on Days</label>
                            <div class="flex flex-wrap gap-2">
                                <label v-for="day in ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']"
                                       :key="day" 
                                       class="flex items-center px-3 py-2 rounded-lg cursor-pointer transition-colors"
                                       :class="newSchedule.recurring_days.includes(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].indexOf(day) + 1) ? 'border-blue-500' : ''"
                                       :style="{ 
                                           backgroundColor: newSchedule.recurring_days.includes(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].indexOf(day) + 1) ? 'rgba(59, 130, 246, 0.1)' : themeColors.card,
                                           borderColor: newSchedule.recurring_days.includes(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].indexOf(day) + 1) ? themeColors.primary : themeColors.border,
                                           borderStyle: 'solid',
                                           borderWidth: '1px'
                                       }">
                                    <input type="checkbox" :value="['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].indexOf(day) + 1"
                                           v-model="newSchedule.recurring_days"
                                           class="h-4 w-4 rounded focus:outline-none transition-colors"
                                           :style="{ 
                                               backgroundColor: themeColors.background,
                                               borderColor: themeColors.border,
                                               borderWidth: '1px',
                                               borderStyle: 'solid'
                                           }">
                                    <span class="ml-2 text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ day }}</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">End Date (Optional)</label>
                            <div class="relative">
                                <input
                                    type="date"
                                    id="end_date"
                                    v-model="newSchedule.end_date"
                                    @keydown.prevent
                                    @click="$event.target.showPicker && $event.target.showPicker()"
                                    class="w-full rounded-md pl-10 pr-3 py-2 focus:outline-none transition-colors cursor-pointer"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }"
                                >
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                    <CalendarIcon class="h-5 w-5" :style="{ color: themeColors.textSecondary }" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4"
                         :style="{ 
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px 0 0 0'
                         }">
                        <button type="button" @click="closeAddScheduleModal"
                                class="px-4 py-2 text-sm font-medium rounded-md transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white rounded-md transition-colors flex items-center"
                                :style="{ 
                                    backgroundColor: themeColors.primary,
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                            Create Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Schedule Modal -->
        <div v-if="showEditScheduleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="rounded-lg shadow-xl w-full max-w-md p-6" :style="{ backgroundColor: themeColors.card }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Edit Shift Schedule</h3>
                    <button @click="closeEditScheduleModal" class="transition-colors" :style="{ color: themeColors.textSecondary }">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitEditSchedule" class="space-y-4">
                    <div class="rounded-lg p-4 space-y-3"
                         :style="{ 
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div>
                            <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Team Member</label>
                            <div class="flex items-center space-x-2" :style="{ color: themeColors.textPrimary }">
                                <UsersIcon class="h-4 w-4" :style="{ color: themeColors.textSecondary }" />
                                <span class="font-medium">{{ currentSchedule?.employee_name }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Day</label>
                                <div class="flex items-center space-x-2" :style="{ color: themeColors.textPrimary }">
                                    <CalendarDaysIcon class="h-4 w-4" :style="{ color: themeColors.textSecondary }" />
                                    <span class="font-medium">{{ props.weekDays[currentSchedule?.day_index] }}</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">Current Time</label>
                                <div class="flex items-center space-x-2" :style="{ color: themeColors.textPrimary }">
                                    <ClockIcon class="h-4 w-4" :style="{ color: themeColors.textSecondary }" />
                                    <span class="font-medium">
                                        {{ currentSchedule?.shift_details ? `${formatTime(currentSchedule.shift_details.start)} - ${formatTime(currentSchedule.shift_details.end)}` : 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="edit_work_shift_id" class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                            Select New Shift
                        </label>
                        <select id="edit_work_shift_id" v-model="editScheduleData.work_shift_id" required
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="" disabled>Choose a shift...</option>
                            <option v-for="shift in availableShifts" :key="shift.id" :value="shift.id">
                                {{ shift.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="edit_date" class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                            Schedule Date
                        </label>
                        <div class="relative">
                            <input
                                type="date"
                                id="edit_date"
                                v-model="editScheduleData.date"
                                required
                                @keydown.prevent
                                @click="$event.target.showPicker && $event.target.showPicker()"
                                class="w-full rounded-md pl-10 pr-3 py-2 focus:outline-none transition-colors cursor-pointer"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }"
                            >
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                <CalendarIcon class="h-5 w-5" :style="{ color: themeColors.textSecondary }" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4"
                         :style="{ 
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px 0 0 0'
                         }">
                        <button type="button" @click="closeEditScheduleModal"
                                class="px-4 py-2 text-sm font-medium rounded-md transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white rounded-md transition-colors flex items-center"
                                :style="{ 
                                    backgroundColor: themeColors.primary,
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Assignment Modal -->
        <div v-if="showAssignmentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="rounded-lg shadow-xl w-full max-w-md p-6" :style="{ backgroundColor: themeColors.card }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">
                        Assign Worker - {{ selectedSlot.day }} {{ selectedSlot.hour }}
                    </h3>
                    <button @click="closeAssignmentModal" class="transition-colors" :style="{ color: themeColors.textSecondary }">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitAssignment" class="space-y-4">
                    <div>
                        <label for="user_id" class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                            <UsersIcon class="h-4 w-4 inline mr-1" :style="{ color: themeColors.textSecondary }" />
                            Select Worker
                        </label>
                        <select id="user_id" v-model="assignmentForm.user_id" required
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="" disabled>Choose a worker...</option>
                            <option v-for="user in staffUsers" :key="user.id" :value="user.id">
                                {{ getEmployeeDisplayName(user) }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="work_shift_id" class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                            <ClockIcon class="h-4 w-4 inline mr-1" :style="{ color: themeColors.textSecondary }" />
                            Work Shift
                        </label>
                        <select id="work_shift_id" v-model="assignmentForm.work_shift_id" required
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="" disabled>Select a shift...</option>
                            <option v-for="shift in workShifts" :key="shift.id" :value="shift.id">
                                {{ shift.shift_name }} ({{ formatTime(shift.start_time) }} - {{ formatTime(shift.end_time) }})
                            </option>
                        </select>
                    </div>

                    <div class="rounded-lg p-3"
                         :style="{ 
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="text-sm">
                            <div class="flex items-center mb-1">
                                <CalendarDaysIcon class="h-4 w-4 mr-2" :style="{ color: themeColors.textSecondary }" />
                                <span :style="{ color: themeColors.textPrimary }">Day: <strong>{{ selectedSlot.day }}</strong></span>
                            </div>
                            <div class="flex items-center">
                                <ClockIcon class="h-4 w-4 mr-2" :style="{ color: themeColors.textSecondary }" />
                                <span :style="{ color: themeColors.textPrimary }">Time: <strong>{{ selectedSlot.hour }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4"
                         :style="{ 
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px 0 0 0'
                         }">
                        <button type="button" @click="closeAssignmentModal"
                                class="px-4 py-2 text-sm font-medium rounded-md transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium rounded-md transition-colors text-white"
                                :style="{ backgroundColor: themeColors.primary }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                            Assign Worker
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
import { getNavigationForRole } from '@/Utils/navigation.js'
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
    XMarkIcon,
    DocumentArrowDownIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    navigation: { type: Array, default: null },
    workShifts: {
        type: Array,
        default: () => [],
    },
    staffUsers: {
        type: Array,
        default: () => [],
    },
    employeeShifts: {
        type: Array,
        default: () => [],
    },
    currentWeekStart: String,
    currentWeekEnd: String,
    routePrefix: { type: String, default: 'admin' },
})

const { currentTheme } = useTheme()
const navigation = computed(() => props.navigation || getNavigationForRole(props.routePrefix === 'admin' ? 'admin' : 'manager'))
const hoveredRow = ref(null)

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
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)'
}))

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

// Hourly Grid State
const showAssignmentModal = ref(false)
const selectedSlot = ref({
    day: '',
    hour: '',
    date: ''
})
const assignmentForm = ref({
    user_id: null,
    work_shift_id: null,
    date: '',
    day_of_week: null,
    start_time: '',
    end_time: ''
})

// Week days and hours for the grid
const weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
const hours = [
    '00:00', '01:00', '02:00', '03:00', '04:00', '05:00',
    '06:00', '07:00', '08:00', '09:00', '10:00', '11:00',
    '12:00', '13:00', '14:00', '15:00', '16:00', '17:00',
    '18:00', '19:00', '20:00', '21:00', '22:00', '23:00'
]

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


const getRequestTypeColor = (type) => {
    const colors = {
        time_off: 'bg-yellow-100 text-yellow-800',
        shift_swap: 'bg-blue-100 text-blue-800',
        overtime: 'bg-orange-100 text-orange-800',
        schedule_change: 'bg-purple-100 text-purple-800'
    }
    return colors[type] || 'bg-gray-100 text-gray-800'
}

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800'
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
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
    window.open(route('admin.schedules.print', params), '_blank')
}

// Hourly Grid Functions
const currentWeekRange = computed(() => {
    if (!props.currentWeekStart) return ''
    const start = new Date(props.currentWeekStart)
    const end = new Date(props.currentWeekEnd)
    return `${start.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${end.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`
})

const getDayDate = (day) => {
    if (!props.currentWeekStart) return ''
    const dayIndex = weekDays.indexOf(day)
    const baseDate = new Date(props.currentWeekStart)
    baseDate.setDate(baseDate.getDate() + dayIndex)
    return baseDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const getAssignmentsForSlot = (day, hour) => {
    const dayIndex = weekDays.indexOf(day)
    const slotDate = new Date(props.currentWeekStart)
    slotDate.setDate(slotDate.getDate() + dayIndex)
    const dateStr = slotDate.toISOString().split('T')[0]
    
    return props.employeeShifts.filter(shift => {
        if (shift.effective_date !== dateStr) return false
        
        const shiftStart = new Date(`${dateStr}T${shift.workShift?.start_time || '00:00:00'}`)
        const shiftEnd = new Date(`${dateStr}T${shift.workShift?.end_time || '23:59:59'}`)
        const slotTime = new Date(`${dateStr}T${hour}:00:00`)
        
        return slotTime >= shiftStart && slotTime < shiftEnd
    }).map(shift => ({
        id: shift.id,
        user_id: shift.user_id,
        user_name: shift.user ? `${shift.user.first_name} ${shift.user.last_name}`.trim() : 'Unknown',
        shift_name: shift.workShift?.name || 'Unknown Shift',
        type: shift.workShift?.is_overnight ? 'night' : 'regular',
        start_time: shift.workShift?.start_time,
        end_time: shift.workShift?.end_time
    }))
}

const getAssignmentColorClass = (type) => {
    const classes = {
        regular: 'bg-blue-100 text-blue-800 border-blue-200',
        night: 'bg-purple-100 text-purple-800 border-purple-200',
        overtime: 'bg-orange-100 text-orange-800 border-orange-200'
    }
    return classes[type] || 'bg-gray-100 text-gray-800 border-gray-200'
}

const getEmployeeDisplayName = (user) => {
    if (!user) return 'Unknown User'
    
    const fullName = `${user.first_name || ''} ${user.last_name || ''}`.trim()
    if (fullName) {
        return fullName
    }
    
    if (user.employee_id) {
        return user.employee_id
    }
    
    if (user.email) {
        return user.email
    }
    
    return `User ${user.id}`
}

const openAssignmentModal = (day, hour) => {
    const dayIndex = weekDays.indexOf(day)
    const slotDate = new Date(props.currentWeekStart)
    slotDate.setDate(slotDate.getDate() + dayIndex)
    
    selectedSlot.value = {
        day,
        hour,
        date: slotDate.toISOString().split('T')[0]
    }
    
    assignmentForm.value = {
        user_id: null,
        work_shift_id: null,
        date: slotDate.toISOString().split('T')[0],
        day_of_week: dayIndex + 1,
        start_time: hour,
        end_time: hour
    }
    
    showAssignmentModal.value = true
}

const closeAssignmentModal = () => {
    showAssignmentModal.value = false
    selectedSlot.value = { day: '', hour: '', date: '' }
    assignmentForm.value = {
        user_id: null,
        work_shift_id: null,
        date: '',
        day_of_week: null,
        start_time: '',
        end_time: ''
    }
}

const submitAssignment = () => {
    // This would create an EmployeeShift record
    router.post(route('admin.employee-shifts.store'), assignmentForm.value, {
        onSuccess: () => {
            closeAssignmentModal()
        },
        onError: (errors) => {
            console.error('Error creating assignment:', errors)
            alert('Error creating assignment. Please try again.')
        }
    })
}

const editAssignment = (assignment) => {
    // This would open an edit modal for the assignment
    console.log('Edit assignment:', assignment)
}


const exportSchedule = () => {
    const params = props.weekStart ? { week_start: props.weekStart } : {}
    window.location.href = route('admin.schedules.export', params)
}

const addSchedule = () => {
    // This would come from workShifts data in a real implementation
    availableShifts.value = [
        { id: 1, name: 'Morning Shift (6:00 AM - 2:00 PM)' },
        { id: 2, name: 'Day Shift (8:00 AM - 4:00 PM)' },
        { id: 3, name: 'Evening Shift (2:00 PM - 10:00 PM)' },
        { id: 4, name: 'Night Shift (10:00 PM - 6:00 AM)' },
    ]

    showAddScheduleModal.value = true
}

const changeWeek = (direction) => {
    // direction: -1 for previous, +1 for next
    const base = props.weekStart ? new Date(props.weekStart) : new Date()
    // Move by 7 days
    base.setDate(base.getDate() + direction * 7)
    const newStart = base.toISOString().split('T')[0]

    const indexRoute = props.routePrefix === 'admin' ? 'admin.schedules.index' : 'manager.staff.schedules'
    router.get(route(indexRoute, { week_start: newStart }), {}, {
        preserveScroll: true,
        preserveState: true,
    })
}

const previousWeek = () => changeWeek(-1)
const nextWeek = () => changeWeek(1)

const approveRequest = (request) => {
    if (confirm(`Approve ${request.type} request for ${request.employee_name}?`)) {
        router.post(route('admin.schedules.requests.approve', {request: request.id}), {}, {
            onSuccess: () => {
                request.status = 'approved'
            },
            onError: (errors) => {
                console.error('Error approving request:', errors)
                alert('Error approving request. Please try again.')
            }
        })
    }
}

const rejectRequest = (request) => {
    if (confirm(`Reject ${request.type} request for ${request.employee_name}?`)) {
        router.post(route('admin.schedules.requests.reject', {request: request.id}), {}, {
            onSuccess: () => {
                request.status = 'rejected'
            },
            onError: (errors) => {
                console.error('Error rejecting request:', errors)
                alert('Error rejecting request. Please try again.')
            }
        })
    }
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
    router.post(route('admin.schedules.store'), newSchedule.value, {
        onSuccess: () => {
            closeAddScheduleModal()
            window.location.reload()
        },
        onError: (errors) => {
            console.error('Error creating schedule:', errors)
            alert('Error creating schedule. Please check the form and try again.')
        }
    })
}

const editSchedule = (employee, dayIndex) => {
    const shift = employee.shifts[dayIndex]
    if (!shift) return

    // Load available shifts for edit
    availableShifts.value = [
        { id: 1, name: 'Morning Shift (6:00 AM - 2:00 PM)' },
        { id: 2, name: 'Day Shift (8:00 AM - 4:00 PM)' },
        { id: 3, name: 'Evening Shift (2:00 PM - 10:00 PM)' },
        { id: 4, name: 'Night Shift (10:00 PM - 6:00 AM)' },
    ]

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

    if (confirm(`Delete schedule for ${employee.name} on ${props.weekDays[dayIndex]}? This cannot be undone.`)) {
        // Use the employee_shift id from the schedule data and call the backend destroy route
        router.delete(route('admin.schedules.destroy', { schedule: shift.id }), {
            onSuccess: () => {
                window.location.reload()
            },
            onError: (errors) => {
                console.error('Error deleting schedule:', errors)
                alert('Error deleting schedule. Please try again.')
            }
        })
    }
}

const submitGenerateSchedule = () => {
    if (confirm('This will auto-generate schedules for all employees. Continue?')) {
        router.post(route('admin.schedules.generate'), {}, {
            onSuccess: () => {
                closeGenerateScheduleModal()
                window.location.reload()
            },
            onError: (errors) => {
                console.error('Error generating schedules:', errors)
                alert('Error generating schedules. Please try again.')
            }
        })
    }
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

    availableShifts.value = [
        { id: 1, name: 'Morning Shift (6:00 AM - 2:00 PM)' },
        { id: 2, name: 'Day Shift (8:00 AM - 4:00 PM)' },
        { id: 3, name: 'Evening Shift (2:00 PM - 10:00 PM)' },
        { id: 4, name: 'Night Shift (10:00 PM - 6:00 AM)' },
    ]

    showAddScheduleModal.value = true
}

const getDateForDayIndex = (dayIndex) => {
    const startOfWeek = new Date()
    startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay() + 1) // Start on Monday
    const targetDate = new Date(startOfWeek)
    targetDate.setDate(startOfWeek.getDate() + dayIndex)
    return targetDate.toISOString().split('T')[0]
}

const submitEditSchedule = () => {
    // Find the employee shift ID - this would need to be stored in the data
    // For now, we'll simulate an update
    router.put(route('admin.schedules.update', { schedule: currentSchedule.value.schedule_id }), editScheduleData.value, {
        onSuccess: () => {
            closeEditScheduleModal()
            window.location.reload()
        },
        onError: (errors) => {
            console.error('Error updating schedule:', errors)
            alert('Error updating schedule. Please try again.')
        }
    })
}
</script>
