<template>
    <DashboardLayout title="Maintenance Services" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Maintenance Services</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Track and monitor maintenance requests.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="showNewRequest = true"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{
                                backgroundColor: themeColors.primary,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        New Request
                    </button>
                    <button @click="exportRequests"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{
                                backgroundColor: '#8b5cf6',
                            }"
                            @mouseenter="$event.target.style.backgroundColor = '#7c3aed'"
                            @mouseleave="$event.target.style.backgroundColor = '#8b5cf6'">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                        Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Maintenance Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-8">
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
                        <WrenchScrewdriverIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.total }}</p>
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
                        <ExclamationCircleIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Open</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.open }}</p>
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
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                        <ArrowPathIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">In Progress</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.in_progress }}</p>
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
                        <CheckCircleIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Resolved</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.resolved }}</p>
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
                         :style="{ backgroundColor: 'rgba(239, 68, 68, 0.1)' }">
                        <ExclamationTriangleIcon class="h-6 w-6" :style="{ color: themeColors.danger }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Urgent</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.urgent }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rooms Currently in Maintenance Report -->
        <div class="rounded-lg border mb-8" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
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
                    <thead :style="{ backgroundColor: themeColors.background }">
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
                            :style="{ backgroundColor: item.priority === 'urgent' ? 'rgba(239,68,68,0.07)' : themeColors.card }">
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-bold" :style="{ color: themeColors.textPrimary }">Room {{ item.room_number }}</td>
                            <td class="px-4 py-3 text-sm" style="max-width:280px;">
                                <div class="font-medium" :style="{ color: themeColors.textPrimary }">{{ item.title }}</div>
                                <div class="text-xs mt-0.5 truncate" :style="{ color: themeColors.textTertiary }" :title="item.description">{{ item.description }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm capitalize" :style="{ color: themeColors.textSecondary }">{{ item.category }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full" :style="getMaintenancePriorityStyle(item.priority)">{{ formatStatus(item.priority) }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full" :style="getMaintenanceStatusStyle(item.status)">{{ formatStatus(item.status) }}</span>
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
             class="rounded-lg border mb-8 overflow-hidden"
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

        <!-- Requests Table -->
        <div class="shadow rounded-lg overflow-hidden mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Recent Maintenance Requests</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y" :style="{ borderColor: themeColors.border }">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Request #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Title / Photos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Room/Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Assigned To</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Reported</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                        <tr v-if="requests.data.length === 0">
                            <td colspan="8" class="px-6 py-4 text-center" :style="{ color: themeColors.textTertiary }">No maintenance requests found.</td>
                        </tr>
                        <tr v-for="request in requests.data" :key="request.id"
                            class="transition-colors"
                            :style="{ backgroundColor: themeColors.card }">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ request.request_number }}</td>
                            <td class="px-6 py-4 text-sm" style="max-width: 260px;">
                                <div class="font-medium" :style="{ color: themeColors.textPrimary }">{{ request.title }}</div>
                                <div v-if="request.photos && request.photos.length > 0" class="flex gap-1 flex-wrap mt-1">
                                    <img v-for="(photo, idx) in request.photos" :key="idx"
                                         :src="photo"
                                         @click="openLightbox(photo)"
                                         class="h-10 w-10 object-cover rounded cursor-pointer border hover:opacity-80 transition-opacity"
                                         :style="{ borderColor: themeColors.border }"
                                         title="Click to view full image" />
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textSecondary }">
                                <span v-if="request.room">{{ request.room.room_number }}</span>
                                <span v-else>{{ request.location || 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm capitalize" :style="{ color: themeColors.textSecondary }">{{ formatCategory(request.category) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full"
                                      :style="getMaintenancePriorityStyle(request.priority)">
                                    {{ formatStatus(request.priority) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full"
                                      :style="getMaintenanceStatusStyle(request.status)">
                                    {{ formatStatus(request.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textSecondary }">{{ request.assigned_to?.name || 'Unassigned' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: themeColors.textSecondary }">{{ formatDateTime(request.reported_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="requests.links" class="px-6 py-4 border-t"
                 :style="{ borderColor: themeColors.border }">
                <Pagination :links="requests.links" />
            </div>
        </div>

        <!-- New Request Modal -->
        <div v-if="showNewRequest" @click="showNewRequest = false"
             class="fixed inset-0 flex items-center justify-center z-50"
             :style="{ backgroundColor: 'rgba(0, 0, 0, 0.5)' }">
            <div @click.stop
                 class="rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px' }">
                <h2 class="text-xl font-bold mb-4" :style="{ color: themeColors.textPrimary }">New Maintenance Request</h2>
                <form @submit.prevent="submitRequest">
                    <div class="space-y-5">
                        <!-- Request Information -->
                        <div>
                            <h3 class="text-base font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Request Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Room</label>
                                    <select v-model="newRequest.room_id"
                                            class="w-full rounded-md px-3 py-2 transition-colors"
                                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }">
                                        <option :value="null">Not Room-Specific</option>
                                        <option v-for="room in rooms" :key="room.id" :value="room.id">Room {{ room.room_number }}</option>
                                    </select>
                                    <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">Select the room if the issue is room-specific</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Category *</label>
                                    <select v-model="newRequest.category" required
                                            class="w-full rounded-md px-3 py-2 transition-colors"
                                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }">
                                        <option value="">Select Category</option>
                                        <option value="plumbing">Plumbing</option>
                                        <option value="electrical">Electrical</option>
                                        <option value="hvac">HVAC</option>
                                        <option value="furniture">Furniture</option>
                                        <option value="appliances">Appliances</option>
                                        <option value="security">Security</option>
                                        <option value="it">IT</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Priority *</label>
                                    <select v-model="newRequest.priority" required
                                            class="w-full rounded-md px-3 py-2 transition-colors"
                                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }">
                                        <option value="low">Low</option>
                                        <option value="normal">Normal</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                    <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">Select urgency level</p>
                                </div>
                                <div v-if="departments.length > 0">
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Department</label>
                                    <select v-model="newRequest.department_id"
                                            class="w-full rounded-md px-3 py-2 transition-colors"
                                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }">
                                        <option :value="null">Select Department</option>
                                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                    </select>
                                    <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">Assign to specific department if needed</p>
                                </div>
                            </div>
                        </div>
                        <!-- Request Details -->
                        <div>
                            <h3 class="text-base font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Request Details</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Title *</label>
                                    <input v-model="newRequest.title" type="text" required placeholder="Brief description of the issue"
                                           class="w-full rounded-md px-3 py-2 transition-colors"
                                           :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Description *</label>
                                    <textarea v-model="newRequest.description" rows="4" required placeholder="Detailed description of the maintenance issue..."
                                              class="w-full rounded-md px-3 py-2 transition-colors"
                                              :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }"></textarea>
                                </div>
                            </div>
                        </div>
                        <!-- Location Information -->
                        <div>
                            <h3 class="text-base font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Location Information</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Location</label>
                                    <input v-model="newRequest.location" type="text" placeholder="e.g., Lobby, Restaurant, Pool Area"
                                           class="w-full rounded-md px-3 py-2 transition-colors"
                                           :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }">
                                    <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">General area where the issue is located</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Location Details</label>
                                    <textarea v-model="newRequest.location_details" rows="2" placeholder="Specific location details..."
                                              class="w-full rounded-md px-3 py-2 transition-colors"
                                              :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }"></textarea>
                                    <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">More specific location information</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Photos</h3>
                            <div>
                                <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Upload Photos</label>
                                <input @change="handlePhotoUpload" type="file" multiple accept="image/*"
                                       class="w-full rounded-md px-3 py-2 transition-colors"
                                       :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }">
                                <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">You can select multiple photos to document the issue (max 5 MB each)</p>
                                <div v-if="photoFiles.length > 0" class="mt-3 flex flex-wrap gap-2">
                                    <div v-for="(file, idx) in photoFiles" :key="idx"
                                         class="text-sm px-2 py-1 rounded-md flex items-center gap-1"
                                         :style="{ backgroundColor: themeColors.primary + '20', color: themeColors.primary }">
                                        <span>{{ file.name }}</span>
                                        <button @click="removePhoto(idx)" type="button"
                                                class="ml-1 font-bold"
                                                :style="{ color: themeColors.danger }">&#x00D7;</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-6 pt-4 border-t" :style="{ borderColor: themeColors.border }">
                        <button type="submit"
                                class="flex-1 py-2 rounded-md transition-colors font-medium text-white"
                                :style="{ backgroundColor: themeColors.primary }">
                            Submit Request
                        </button>
                        <button type="button" @click="showNewRequest = false"
                                class="flex-1 py-2 rounded-md transition-colors font-medium"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderStyle: 'solid', borderWidth: '1px', color: themeColors.textPrimary }">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <teleport to="body">
            <div v-if="lightboxImage"
                 @click="closeLightbox"
                 class="fixed inset-0 z-[200] flex items-center justify-center"
                 style="background: rgba(0,0,0,0.92);">
                <button @click.stop="closeLightbox"
                        class="absolute top-5 right-6 text-white text-3xl font-bold leading-none opacity-80 hover:opacity-100 transition-opacity z-[201]"
                        style="line-height:1;"
                        title="Close">&#x2715;</button>
                <div class="absolute bottom-6 flex items-center gap-4 z-[201]"
                     style="left:50%; transform:translateX(-50%);">
                    <button @click.stop="zoomOut"
                            class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xl font-bold"
                            style="background: rgba(255,255,255,0.2);"
                            title="Zoom out">&#x2212;</button>
                    <span class="text-white text-sm font-semibold min-w-[3.5rem] text-center">
                        {{ Math.round(lightboxZoom * 100) }}%
                    </span>
                    <button @click.stop="zoomIn"
                            class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xl font-bold"
                            style="background: rgba(255,255,255,0.2);"
                            title="Zoom in">+</button>
                </div>
                <div @click.stop class="overflow-auto rounded" style="max-width: 90vw; max-height: 82vh;">
                    <img :src="lightboxImage"
                         :style="{ transform: 'scale(' + lightboxZoom + ')', transformOrigin: 'top left', transition: 'transform 0.2s ease', display: 'block' }"
                         class="rounded shadow-2xl" />
                </div>
            </div>
        </teleport>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme.js'
import {
    WrenchScrewdriverIcon,
    ExclamationCircleIcon,
    ArrowPathIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    PlusIcon,
    DocumentArrowDownIcon
} from '@heroicons/vue/24/outline'

// Initialize theme
const { loadTheme } = useTheme()
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
    hover: `rgba(255, 255, 255, 0.1)`
}))

// Load theme on mount
loadTheme()

const props = defineProps({
    user: Object,
    requests: Object,
    stats: Object,
    rooms: { type: Array, default: () => [] },
    departments: { type: Array, default: () => [] },
    maintenanceRooms: { type: Array, default: () => [] },
    recurringAlerts:  { type: Array, default: () => [] },
})

const navigation = computed(() => getNavigationForRole('front_desk'))

const requests = computed(() => props.requests || { data: [], links: [] })
const stats = computed(() => props.stats || {
    total: 0,
    open: 0,
    in_progress: 0,
    resolved: 0,
    urgent: 0
})

const showNewRequest = ref(false)
const newRequest = ref({
    room_id: null,
    title: '',
    description: '',
    category: '',
    priority: 'normal',
    location: '',
    location_details: '',
    department_id: null,
})
const photoFiles = ref([])

const lightboxImage = ref(null)
const lightboxZoom = ref(1.0)

const openLightbox = (url) => {
    lightboxImage.value = url
    lightboxZoom.value = 1.0
}

const closeLightbox = () => {
    lightboxImage.value = null
    lightboxZoom.value = 1.0
}

const zoomIn = () => {
    lightboxZoom.value = Math.min(lightboxZoom.value + 0.25, 4.0)
}

const zoomOut = () => {
    lightboxZoom.value = Math.max(lightboxZoom.value - 0.25, 0.25)
}

const handlePhotoUpload = (event) => {
    photoFiles.value = Array.from(event.target.files || [])
}

const removePhoto = (index) => {
    photoFiles.value.splice(index, 1)
}

const formatDateTime = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const formatCategory = (category) => {
    return category ? category.charAt(0).toUpperCase() + category.slice(1) : 'N/A'
}

const formatStatus = (status) => {
    return status ? status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A'
}

const getMaintenanceStatusStyle = (status) => {
    const map = {
        open:        { backgroundColor: 'var(--kotel-warning)',   color: 'white' },
        assigned:    { backgroundColor: 'var(--kotel-primary)',   color: 'white' },
        in_progress: { backgroundColor: 'var(--kotel-primary)',   color: 'white' },
        on_hold:     { backgroundColor: 'var(--kotel-secondary)', color: 'white' },
        resolved:    { backgroundColor: 'var(--kotel-success)',   color: 'white' },
        closed:      { backgroundColor: 'var(--kotel-secondary)', color: 'white' },
        cancelled:   { backgroundColor: 'var(--kotel-danger)',    color: 'white' },
    }
    return map[status] || map['open']
}

const getMaintenancePriorityStyle = (priority) => {
    const map = {
        low:    { backgroundColor: 'var(--kotel-secondary)', color: 'white' },
        normal: { backgroundColor: 'var(--kotel-primary)',   color: 'white' },
        high:   { backgroundColor: 'var(--kotel-warning)',   color: 'white' },
        urgent: { backgroundColor: 'var(--kotel-danger)',    color: 'white' },
    }
    return map[priority] || map['normal']
}

const exportRequests = () => {
    const requestsData = props.requests.data || []

    // Create CSV headers
    const headers = [
        'Request #',
        'Title',
        'Room/Location',
        'Category',
        'Priority',
        'Status',
        'Assigned To',
        'Reported At',
        'Description',
        'Location Details'
    ]

    // Create CSV rows
    const rows = requestsData.map(request => [
        request.request_number || '',
        request.title || '',
        request.room ? request.room.room_number : (request.location || 'N/A'),
        formatCategory(request.category),
        request.priority || '',
        formatStatus(request.status),
        request.assigned_to?.name || 'Unassigned',
        formatDateTime(request.reported_at),
        (request.description || '').replace(/\n/g, ' ').replace(/,/g, ';'),
        (request.location_details || '').replace(/\n/g, ' ').replace(/,/g, ';')
    ])

    // Build CSV content
    const csvContent = [
        headers.join(','),
        ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
    ].join('\n')

    // Create and download CSV file
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `maintenance_requests_${new Date().toISOString().split('T')[0]}.csv`
    link.click()
    URL.revokeObjectURL(url)
}

const submitRequest = () => {
    router.post(route('front-desk.services.maintenance.store'), {
        ...newRequest.value,
        photos: photoFiles.value,
    }, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showNewRequest.value = false
            photoFiles.value = []
            newRequest.value = {
                room_id: null,
                title: '',
                description: '',
                category: '',
                priority: 'normal',
                location: '',
                location_details: '',
                department_id: null,
            }
        }
    })
}
</script>
