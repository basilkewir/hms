<template>
    <DashboardLayout title="Maintenance Requests">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Maintenance Requests</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Track and manage maintenance issues and repairs.</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route(`${routePrefix}.maintenance-requests.create`)"
                          class="px-4 py-2 rounded-md transition-colors text-sm font-medium"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                              color: 'white'
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4 mr-2 inline" />
                        Add Request
                    </Link>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="p-4 rounded-lg border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="flex items-center">
                        <div class="p-2 rounded-md mr-3"
                             :style="{ backgroundColor: themeColors.primary + '20' }">
                            <WrenchScrewdriverIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                        </div>
                        <div>
                            <h3 class="text-sm font-medium"
                                :style="{ color: themeColors.textSecondary }">Total</h3>
                            <p class="text-2xl font-bold mt-1"
                               :style="{ color: themeColors.textPrimary }">{{ stats.total || 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-lg border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="flex items-center">
                        <div class="p-2 rounded-md mr-3"
                             :style="{ backgroundColor: themeColors.warning + '20' }">
                            <ExclamationCircleIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                        </div>
                        <div>
                            <h3 class="text-sm font-medium"
                                :style="{ color: themeColors.textSecondary }">Open</h3>
                            <p class="text-2xl font-bold mt-1"
                               :style="{ color: themeColors.textPrimary }">{{ stats.open || 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-lg border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="flex items-center">
                        <div class="p-2 rounded-md mr-3"
                             :style="{ backgroundColor: themeColors.primary + '20' }">
                            <ArrowPathIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                        </div>
                        <div>
                            <h3 class="text-sm font-medium"
                                :style="{ color: themeColors.textSecondary }">In Progress</h3>
                            <p class="text-2xl font-bold mt-1"
                               :style="{ color: themeColors.textPrimary }">{{ stats.in_progress || 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-lg border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="flex items-center">
                        <div class="p-2 rounded-md mr-3"
                             :style="{ backgroundColor: themeColors.success + '20' }">
                            <CheckCircleIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                        </div>
                        <div>
                            <h3 class="text-sm font-medium"
                                :style="{ color: themeColors.textSecondary }">Resolved</h3>
                            <p class="text-2xl font-bold mt-1"
                               :style="{ color: themeColors.textPrimary }">{{ stats.resolved || 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-lg border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="flex items-center">
                        <div class="p-2 rounded-md mr-3"
                             :style="{ backgroundColor: themeColors.danger + '20' }">
                            <ExclamationTriangleIcon class="h-6 w-6" :style="{ color: themeColors.danger }" />
                        </div>
                        <div>
                            <h3 class="text-sm font-medium"
                                :style="{ color: themeColors.textSecondary }">Urgent</h3>
                            <p class="text-2xl font-bold mt-1"
                               :style="{ color: themeColors.textPrimary }">{{ stats.urgent || 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rooms Currently in Maintenance Report -->
            <div class="rounded-lg border mb-6" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <div class="flex items-center px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                    <WrenchScrewdriverIcon class="h-5 w-5 mr-2" :style="{ color: themeColors.warning }" />
                    <h2 class="text-base font-semibold" :style="{ color: themeColors.textPrimary }">Rooms Currently in Maintenance</h2>
                    <span class="ml-3 px-2 py-0.5 text-xs font-semibold rounded-full"
                          :style="{ backgroundColor: 'rgba(250,204,21,0.15)', color: themeColors.warning }">
                        {{ (maintenanceRooms || []).length }} room(s) active
                    </span>
                </div>
                <div v-if="!maintenanceRooms || maintenanceRooms.length === 0" class="px-6 py-8 text-center">
                    <CheckCircleIcon class="h-10 w-10 mx-auto mb-2" :style="{ color: themeColors.success }" />
                    <p class="text-sm" :style="{ color: themeColors.textTertiary }">No rooms are currently in active maintenance.</p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y" :style="{ borderColor: themeColors.border }">
                        <thead :style="{ backgroundColor: themeColors.card }">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Room</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Issue / Description</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Category</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Priority</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Assigned To</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Reported By</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Reported</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Days Open</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" :style="{ borderColor: themeColors.border }">
                            <tr v-for="item in maintenanceRooms" :key="item.id"
                                :style="{ backgroundColor: item.priority === 'urgent' ? 'rgba(239,68,68,0.07)' : themeColors.background }">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-bold" :style="{ color: themeColors.textPrimary }">Room {{ item.room_number }}</td>
                                <td class="px-4 py-3 text-sm" style="max-width:280px;">
                                    <div class="font-medium" :style="{ color: themeColors.textPrimary }">{{ item.title }}</div>
                                    <div class="text-xs mt-0.5 truncate" :style="{ color: themeColors.textTertiary }" :title="item.description">{{ item.description }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm capitalize" :style="{ color: themeColors.textSecondary }">{{ item.category }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full" :style="getPriorityStyle(item.priority)">{{ formatStatus(item.priority) }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full" :style="getStatusStyle(item.status)">{{ formatStatus(item.status) }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm" :style="{ color: themeColors.textSecondary }">{{ item.assigned_to || 'Unassigned' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm" :style="{ color: themeColors.textSecondary }">{{ item.reported_by || '—' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm" :style="{ color: themeColors.textTertiary }">{{ item.reported_at }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full"
                                          :style="item.days_open >= 7 ? { backgroundColor: 'var(--kotel-danger)', color: 'white' } : item.days_open >= 3 ? { backgroundColor: 'var(--kotel-warning)', color: 'white' } : { backgroundColor: 'var(--kotel-success)', color: 'white' }">
                                        {{ item.days_open }}d
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recurring Issue Alerts -->
            <div v-if="recurringAlerts && recurringAlerts.length > 0"
                 class="rounded-lg border mb-6 overflow-hidden"
                 :style="{ borderColor: 'var(--kotel-danger)', borderWidth: '2px', borderStyle: 'solid' }">
                <div class="px-6 py-3 flex items-center gap-2" :style="{ backgroundColor: 'var(--kotel-danger)', color: 'white' }">
                    <ExclamationTriangleIcon class="h-5 w-5 flex-shrink-0" />
                    <span class="text-sm font-semibold">Recurring Issue Alerts — Frequent maintenance detected in the last 30 days</span>
                </div>
                <div class="divide-y" :style="{ borderColor: themeColors.border }">
                    <div v-for="alert in recurringAlerts" :key="alert.label + alert.type"
                         class="px-6 py-3 flex items-center justify-between"
                         :style="{ backgroundColor: themeColors.card }">
                        <div class="flex items-center gap-3">
                            <ExclamationCircleIcon class="h-5 w-5 flex-shrink-0"
                                :style="{ color: alert.count >= 5 ? 'var(--kotel-danger)' : 'var(--kotel-warning)' }" />
                            <div>
                                <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ alert.message }}</span>
                                <span class="ml-2 text-xs px-2 py-0.5 rounded-full uppercase font-medium"
                                      :style="alert.type === 'room'
                                        ? { backgroundColor: 'rgba(59,130,246,0.15)', color: 'var(--kotel-primary)' }
                                        : { backgroundColor: 'rgba(139,92,246,0.15)', color: '#8b5cf6' }">
                                    {{ alert.type }}
                                </span>
                            </div>
                        </div>
                        <span class="text-base font-bold flex-shrink-0 ml-4"
                              :style="{ color: alert.count >= 5 ? 'var(--kotel-danger)' : 'var(--kotel-warning)' }">
                            {{ alert.count }}&times;
                        </span>
                    </div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="mb-6">
                <div class="border-b"
                     :style="{ borderColor: themeColors.border }">
                    <nav class="-mb-px flex space-x-8">
                        <button class="py-2 px-1 border-b-2 font-medium text-sm transition-colors"
                                :style="{ 
                                    borderColor: themeColors.primary,
                                    color: themeColors.primary
                                }">
                            All
                        </button>
                        <button class="py-2 px-1 border-b-2 font-medium text-sm transition-colors"
                                :style="{ 
                                    borderColor: 'transparent',
                                    color: themeColors.textSecondary
                                }"
                                @mouseenter="$event.target.style.color = themeColors.textPrimary"
                                @mouseleave="$event.target.style.color = themeColors.textSecondary">
                            Open
                        </button>
                        <button class="py-2 px-1 border-b-2 font-medium text-sm transition-colors"
                                :style="{ 
                                    borderColor: 'transparent',
                                    color: themeColors.textSecondary
                                }"
                                @mouseenter="$event.target.style.color = themeColors.textPrimary"
                                @mouseleave="$event.target.style.color = themeColors.textSecondary">
                            In Progress
                        </button>
                        <button class="py-2 px-1 border-b-2 font-medium text-sm transition-colors"
                                :style="{ 
                                    borderColor: 'transparent',
                                    color: themeColors.textSecondary
                                }"
                                @mouseenter="$event.target.style.color = themeColors.textPrimary"
                                @mouseleave="$event.target.style.color = themeColors.textSecondary">
                            Resolved
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Data Table -->
            <div class="overflow-hidden shadow ring-1 ring-opacity-5 md:rounded-lg"
                 :style="{ 
                     ringColor: themeColors.border,
                     backgroundColor: themeColors.card
                 }">
                <table class="min-w-full divide-y"
                       :style="{ borderColor: themeColors.border }">
                    <thead class="bg-gray-50"
                           :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Request #</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Title</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Location</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Category</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Priority</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Assigned To</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Created</th>
                            <th scope="col" relative="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y"
                           :style="{ borderColor: themeColors.border }">
                        <tr v-for="request in (requests.data || [])" :key="request.id"
                            :style="hoveredRow === request.id ? { 
                                backgroundColor: themeColors.hover
                            } : { 
                                backgroundColor: themeColors.card
                            }"
                            @mouseenter="hoveredRow = request.id"
                            @mouseleave="hoveredRow = null"
                            class="transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">{{ request.request_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">{{ request.title }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">{{ request.location || (request.room && request.room.room_number) || 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">{{ formatCategory(request.category) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                      :style="getPriorityStyle(request.priority)">
                                    {{ formatStatus(request.priority) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                      :style="getStatusStyle(request.status)">
                                    {{ formatStatus(request.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                {{ (request.assigned_to && request.assigned_to.name) || 'Unassigned' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">{{ formatDateTime(request.created_at) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <Link :href="route(`${routePrefix}.maintenance-requests.show`, request.id)"
                                      class="mr-3 transition-colors"
                                      :style="{ color: themeColors.primary }"
                                      @mouseenter="$event.target.style.color = themeColors.hover"
                                      @mouseleave="$event.target.style.color = themeColors.primary">
                                    View
                                </Link>
                                <button @click="openAssignModal(request)"
                                        class="mr-3 transition-colors"
                                        :style="{ color: themeColors.warning }"
                                        @mouseenter="$event.target.style.color = themeColors.hover"
                                        @mouseleave="$event.target.style.color = themeColors.warning">
                                    Assign
                                </button>
                                <button @click="openStatusModal(request)"
                                        class="mr-3 transition-colors"
                                        :style="{ color: themeColors.secondary }"
                                        @mouseenter="$event.target.style.color = themeColors.hover"
                                        @mouseleave="$event.target.style.color = themeColors.secondary">
                                    Update Status
                                </button>
                                <button @click="deleteRequest(request)"
                                        class="transition-colors"
                                        :style="{ color: themeColors.danger }"
                                        @mouseenter="$event.target.style.color = themeColors.hover"
                                        @mouseleave="$event.target.style.color = themeColors.danger">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!requests.data || requests.data.length === 0">
                            <td colspan="9" class="px-6 py-12 text-center"
                                :style="{ color: themeColors.textSecondary }">
                                No maintenance requests found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <Pagination
                v-if="requests && requests.links"
                :links="requests.links"
                :meta="{ from: requests.from, to: requests.to, total: requests.total }"
            />
        </div>

        <!-- Assign Modal -->
        <div v-if="showAssign" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0" :style="{ backgroundColor: 'rgba(0,0,0,0.6)' }" @click="closeAssignModal"></div>
            <div class="relative w-full max-w-lg rounded-lg shadow-xl p-6 border"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Assign Request</h3>

                <div class="mb-4">
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        Request: <span :style="{ color: themeColors.textPrimary }">{{ selectedRequest?.request_number }}</span>
                        - <span :style="{ color: themeColors.textPrimary }">{{ selectedRequest?.title }}</span>
                    </p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Assign To *</label>
                        <select v-model="assignForm.assigned_to" required
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                            <option value="">Select staff</option>
                            <option v-for="staff in (maintenanceStaff || [])" :key="staff.id" :value="staff.id">
                                {{ staff.name }} — {{ staff.roles || staff.email }}
                            </option>
                        </select>
                        <p v-if="assignForm.errors.assigned_to" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ assignForm.errors.assigned_to }}</p>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button" @click="closeAssignModal"
                                class="px-4 py-2 rounded-md transition-colors"
                                :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                            Cancel
                        </button>
                        <button type="button" @click="submitAssign" :disabled="assignForm.processing"
                                class="px-4 py-2 rounded-md transition-colors disabled:opacity-50"
                                :style="{ backgroundColor: themeColors.primary, color: 'white' }"
                                @mouseenter="!assignForm.processing && ($event.target.style.backgroundColor = themeColors.hover)"
                                @mouseleave="!assignForm.processing && ($event.target.style.backgroundColor = themeColors.primary)">
                            Assign
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Modal -->
        <div v-if="showStatus" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0" :style="{ backgroundColor: 'rgba(0,0,0,0.6)' }" @click="closeStatusModal"></div>
            <div class="relative w-full max-w-lg rounded-lg shadow-xl p-6 border"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Update Status</h3>

                <div class="mb-4">
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        Request: <span :style="{ color: themeColors.textPrimary }">{{ selectedRequest?.request_number }}</span>
                        - <span :style="{ color: themeColors.textPrimary }">{{ selectedRequest?.title }}</span>
                    </p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Status *</label>
                        <select v-model="statusForm.status" required
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                            <option value="open">Open</option>
                            <option value="assigned">Assigned</option>
                            <option value="in_progress">In Progress</option>
                            <option value="on_hold">On Hold</option>
                            <option value="resolved">Resolved</option>
                            <option value="closed">Closed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <p v-if="statusForm.errors.status" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ statusForm.errors.status }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Resolution Notes</label>
                        <textarea v-model="statusForm.resolution_notes" rows="3"
                                  class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                  :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }"></textarea>
                        <p v-if="statusForm.errors.resolution_notes" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ statusForm.errors.resolution_notes }}</p>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button" @click="closeStatusModal"
                                class="px-4 py-2 rounded-md transition-colors"
                                :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                            Cancel
                        </button>
                        <button type="button" @click="submitStatus" :disabled="statusForm.processing"
                                class="px-4 py-2 rounded-md transition-colors disabled:opacity-50"
                                :style="{ backgroundColor: themeColors.primary, color: 'white' }"
                                @mouseenter="!statusForm.processing && ($event.target.style.backgroundColor = themeColors.hover)"
                                @mouseleave="!statusForm.processing && ($event.target.style.backgroundColor = themeColors.primary)">
                            Update
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import {
    PlusIcon,
    WrenchScrewdriverIcon,
    ExclamationCircleIcon,
    ArrowPathIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'
import { useTheme } from '@/Composables/useTheme.js'

// Initialize theme
const { loadTheme, currentTheme } = useTheme()
const hoveredRow = ref(null)
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

// Load theme on mount
loadTheme()

const props = defineProps({
    requests: Object,
    stats: Object,
    maintenanceStaff: Array,
    routePrefix: { type: String, default: 'manager' },
    maintenanceRooms: { type: Array, default: () => [] },
    recurringAlerts:  { type: Array, default: () => [] },
})

const showAssign = ref(false)
const showStatus = ref(false)
const selectedRequest = ref(null)

const assignForm = useForm({
    assigned_to: '',
    department_id: null,
    scheduled_date: null,
    scheduled_time: null,
})

const statusForm = useForm({
    status: 'open',
    resolution_notes: '',
    work_performed: '',
    cost: null,
})

const getStatusStyle = (status) => {
    const styles = {
        open: {
            backgroundColor: `var(--kotel-warning)`,
            color: 'white'
        },
        assigned: {
            backgroundColor: `var(--kotel-primary)`,
            color: 'white'
        },
        in_progress: {
            backgroundColor: `var(--kotel-primary)`,
            color: 'white'
        },
        on_hold: {
            backgroundColor: `var(--kotel-secondary)`,
            color: 'white'
        },
        resolved: {
            backgroundColor: `var(--kotel-success)`,
            color: 'white'
        },
        closed: {
            backgroundColor: `var(--kotel-secondary)`,
            color: 'white'
        },
        cancelled: {
            backgroundColor: `var(--kotel-danger)`,
            color: 'white'
        }
    }
    return styles[status] || styles['open']
}

const getPriorityStyle = (priority) => {
    const styles = {
        low: {
            backgroundColor: `var(--kotel-secondary)`,
            color: 'white'
        },
        normal: {
            backgroundColor: `var(--kotel-primary)`,
            color: 'white'
        },
        high: {
            backgroundColor: `var(--kotel-warning)`,
            color: 'white'
        },
        urgent: {
            backgroundColor: `var(--kotel-danger)`,
            color: 'white'
        }
    }
    return styles[priority] || styles['normal']
}

const formatDateTime = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const formatCategory = (category) => {
    if (!category) return 'N/A'
    return category.charAt(0).toUpperCase() + category.slice(1)
}

const formatStatus = (status) => {
    if (!status) return 'N/A'
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const deleteRequest = (request) => {
    if (confirm('Are you sure you want to delete this maintenance request?')) {
        router.delete(route(`${props.routePrefix}.maintenance-requests.destroy`, request.id))
    }
}

const openAssignModal = (request) => {
    selectedRequest.value = request
    assignForm.reset()
    assignForm.clearErrors()
    showAssign.value = true
}

const closeAssignModal = () => {
    showAssign.value = false
    selectedRequest.value = null
}

const submitAssign = () => {
    if (!selectedRequest.value) return
    assignForm.post(route(`${props.routePrefix}.maintenance-requests.assign`, selectedRequest.value.id), {
        onSuccess: () => {
            closeAssignModal()
        }
    })
}

const openStatusModal = (request) => {
    selectedRequest.value = request
    statusForm.reset()
    statusForm.clearErrors()
    statusForm.status = request.status || 'open'
    showStatus.value = true
}

const closeStatusModal = () => {
    showStatus.value = false
    selectedRequest.value = null
}

const submitStatus = () => {
    if (!selectedRequest.value) return
    statusForm.post(route(`${props.routePrefix}.maintenance-requests.update-status`, selectedRequest.value.id), {
        onSuccess: () => {
            closeStatusModal()
        }
    })
}
</script>
