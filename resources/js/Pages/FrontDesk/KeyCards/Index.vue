<template>
    <DashboardLayout title="Key Card Management" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-1"
                        :style="{ color: themeColors.textPrimary }">Key Card Management</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Manage electronic key card assignments and tracking.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('front-desk.key-cards.assignment')" 
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ 
                              backgroundColor: '#10b981',
                          }"
                          @mouseenter="$event.target.style.backgroundColor = '#059669'"
                          @mouseleave="$event.target.style.backgroundColor = '#10b981'">
                        <CreditCardIcon class="h-4 w-4 mr-2" />
                        Assignment Center
                    </Link>
                    <button @click="showAssignModal = true" 
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{ 
                                backgroundColor: '#3b82f6',
                            }"
                            @mouseenter="$event.target.style.backgroundColor = '#2563eb'"
                            @mouseleave="$event.target.style.backgroundColor = '#3b82f6'">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Quick Assign
                    </button>
                    <Link :href="route('front-desk.key-cards.create')" 
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Add New Key Card
                    </Link>
                    <button @click="exportKeyCards" 
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

            <!-- Management Flow Info -->
            <div class="rounded-lg p-3 text-sm mb-8"
                 :style="{ 
                     backgroundColor: 'rgba(59, 130, 246, 0.1)',
                     borderColor: themeColors.primary,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <h4 class="font-semibold mb-2"
                    :style="{ color: themeColors.primary }">Key Card Management Flow:</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="flex items-start">
                        <div class="w-2 h-2 rounded-full mt-1.5 mr-2"
                             :style="{ backgroundColor: themeColors.success }"></div>
                        <div>
                            <span class="font-medium"
                                  :style="{ color: themeColors.textPrimary }">Front Desk Staff:</span>
                            <span :style="{ color: themeColors.textSecondary }"> Creates, assigns, and returns standard key cards to guests</span>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="w-2 h-2 rounded-full mt-1.5 mr-2"
                             :style="{ backgroundColor: '#8b5cf6' }"></div>
                        <div>
                            <span class="font-medium"
                                  :style="{ color: themeColors.textPrimary }">Master Cards:</span>
                            <span :style="{ color: themeColors.textSecondary }"> Access all areas, managed by supervisors</span>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="w-2 h-2 rounded-full mt-1.5 mr-2"
                             :style="{ backgroundColor: themeColors.warning }"></div>
                        <div>
                            <span class="font-medium"
                                  :style="{ color: themeColors.textPrimary }">Maintenance Cards:</span>
                            <span :style="{ color: themeColors.textSecondary }"> For technical staff, managed by maintenance team</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Card Stats Cards -->
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
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                        <CheckCircleIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Available</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.available }}</p>
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
                        <UserGroupIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Assigned</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.assigned }}</p>
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
                           :style="{ color: themeColors.textSecondary }">Lost</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.lost }}</p>
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
                        <XCircleIcon class="h-6 w-6" :style="{ color: themeColors.danger }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Damaged</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.damaged }}</p>
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
                         :style="{ backgroundColor: 'rgba(107, 114, 128, 0.1)' }">
                        <CreditCardIcon class="h-6 w-6" :style="{ color: '#6b7280' }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Cards</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ stats.total }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="rounded-lg p-4 mb-8 border"
             :style="{ 
                 backgroundColor: 'rgba(59, 130, 246, 0.05)',
                 borderColor: themeColors.primary,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="font-semibold mb-1"
                        :style="{ color: themeColors.primary }">Quick Actions</h4>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Common key card management tasks</p>
                </div>
                <div class="flex gap-2">
                    <button @click="bulkReturnCards" 
                            :disabled="!selectedCards.length"
                            class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors"
                            :style="{
                                backgroundColor: selectedCards.length ? themeColors.success : themeColors.border,
                                color: selectedCards.length ? 'white' : themeColors.textTertiary,
                                opacity: selectedCards.length ? 1 : 0.5
                            }">
                        Return Selected ({{ selectedCards.length }})
                    </button>
                    <button @click="generateReport" 
                            class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors text-white"
                            :style="{ backgroundColor: '#8b5cf6' }">
                        Generate Report
                    </button>
                </div>
            </div>
        </div>

        <!-- Key Cards Table -->
        <div class="rounded-lg overflow-hidden shadow"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderBottomWidth: '1px'
                 }">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium"
                        :style="{ color: themeColors.textPrimary }">All Key Cards</h3>
                    
                    <!-- Card Type Legend -->
                    <div class="flex items-center gap-4 text-xs">
                        <span class="font-medium"
                              :style="{ color: themeColors.textSecondary }">Card Types:</span>
                        <div class="flex items-center gap-1">
                            <span class="px-2 py-1 rounded-full font-medium bg-blue-100 text-blue-800">Standard</span>
                            <span :style="{ color: themeColors.textTertiary }">Guest rooms</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="px-2 py-1 rounded-full font-medium bg-purple-100 text-skyblue">Master</span>
                            <span :style="{ color: themeColors.textTertiary }">All areas</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="px-2 py-1 rounded-full font-medium bg-green-100 text-green-800">Staff</span>
                            <span :style="{ color: themeColors.textTertiary }">Staff areas</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="px-2 py-1 rounded-full font-medium bg-orange-100 text-skyblue">Maintenance</span>
                            <span :style="{ color: themeColors.textTertiary }">Technical access</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Table Content -->
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Card Number
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Room
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Guest
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Issued
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Returned
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="card in keyCards.data" :key="card.id" 
                            class="transition-colors"
                            :style="{ 
                                borderBottomStyle: 'solid',
                                borderBottomWidth: '1px',
                                borderColor: themeColors.border
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <CreditCardIcon class="w-5 h-5 mr-2" :style="{ color: themeColors.textSecondary }" />
                                    <span class="font-medium"
                                          :style="{ color: themeColors.textPrimary }">{{ card.card_number }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getCardTypeClass(card.card_type)">
                                    {{ formatCardType(card.card_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusClass(card.status)">
                                    {{ formatStatus(card.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: card.room ? themeColors.textPrimary : themeColors.textTertiary }">
                                {{ card.room?.number || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: card.guest ? themeColors.textPrimary : themeColors.textTertiary }">
                                {{ card.guest?.name || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                {{ card.issued_at || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                {{ card.returned_at || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-2">
                                    <Link :href="route('front-desk.key-cards.show', card.id)"
                                          class="transition-colors"
                                          :style="{ color: themeColors.primary }"
                                          @mouseenter="$event.target.style.color = themeColors.hover"
                                          @mouseleave="$event.target.style.color = themeColors.primary">
                                        View
                                    </Link>
                                    <Link :href="route('front-desk.key-cards.edit', card.id)"
                                          class="transition-colors"
                                          :style="{ color: themeColors.success }"
                                          @mouseenter="$event.target.style.color = '#059669'"
                                          @mouseleave="$event.target.style.color = themeColors.success">
                                        Edit
                                    </Link>
                                    <button v-if="card.status === 'assigned'" 
                                            @click="returnCard(card)"
                                            class="transition-colors"
                                            :style="{ color: themeColors.primary }"
                                            @mouseenter="$event.target.style.color = themeColors.hover"
                                            @mouseleave="$event.target.style.color = themeColors.primary">
                                        Return
                                    </button>
                                    <button v-if="card.status === 'assigned'" 
                                            @click="markLost(card)"
                                            class="transition-colors"
                                            :style="{ color: themeColors.warning }"
                                            @mouseenter="$event.target.style.color = '#f59e0b'"
                                            @mouseleave="$event.target.style.color = themeColors.warning">
                                        Mark Lost
                                    </button>
                                    <button v-if="card.status === 'assigned'" 
                                            @click="markDamaged(card)"
                                            class="transition-colors"
                                            :style="{ color: themeColors.danger }"
                                            @mouseenter="$event.target.style.color = '#dc2626'"
                                            @mouseleave="$event.target.style.color = themeColors.danger">
                                        Mark Damaged
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div v-if="keyCards.links" class="px-6 py-4 border-t"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderTopWidth: '1px'
                 }">
                <Pagination :links="keyCards.links" />
            </div>
        </div>

        <!-- Assignment Modal -->
        <div v-if="showAssignModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     color: themeColors.textPrimary 
                 }">
                <div class="p-6 border-b"
                     :style="{ borderColor: themeColors.border, borderBottomWidth: '1px' }">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Assign Key Card</h3>
                        <button @click="showAssignModal = false"
                                :style="{ color: themeColors.textSecondary }"
                                @mouseenter="$event.target.style.color = themeColors.textPrimary"
                                @mouseleave="$event.target.style.color = themeColors.textSecondary">
                            <XMarkIcon class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                <form @submit.prevent="assignKeyCard" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Select Key Card *</label>
                        <select v-model="assignForm.key_card_id" required
                                class="w-full rounded-md px-3 py-2 focus:outline-none focus:ring-2 transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="">Select an available key card</option>
                            <option v-for="card in availableKeyCards" :key="card.id" :value="card.id">
                                {{ card.card_number }} - {{ formatCardType(card.card_type) }}
                            </option>
                        </select>
                        <p class="text-xs mt-1"
                           :style="{ color: themeColors.textTertiary }">Only available key cards are shown</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Select Reservation *</label>
                        <select v-model="assignForm.reservation_id" required
                                @change="loadReservationDetails"
                                class="w-full rounded-md px-3 py-2 focus:outline-none focus:ring-2 transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="">Select a checked-in reservation</option>
                            <option v-for="reservation in checkedInReservations" :key="reservation.id" :value="reservation.id">
                                {{ reservation.reservation_number }} - {{ reservation.guest_name }} (Room {{ reservation.room_number }})
                            </option>
                        </select>
                        <p class="text-xs mt-1"
                           :style="{ color: themeColors.textTertiary }">Only checked-in reservations are shown</p>
                    </div>

                    <div v-if="selectedReservation">
                        <div class="rounded-lg p-3 text-sm"
                             :style="{ 
                                 backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                 borderColor: themeColors.primary,
                                 borderStyle: 'solid',
                                 borderWidth: '1px'
                             }">
                            <h4 class="font-semibold mb-2"
                                :style="{ color: themeColors.primary }">Reservation Details:</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <span class="font-medium"
                                          :style="{ color: themeColors.textPrimary }">Guest:</span>
                                    <span :style="{ color: themeColors.textSecondary }"> {{ selectedReservation.guest_name }}</span>
                                </div>
                                <div>
                                    <span class="font-medium"
                                          :style="{ color: themeColors.textPrimary }">Room:</span>
                                    <span :style="{ color: themeColors.textSecondary }"> {{ selectedReservation.room_number }}</span>
                                </div>
                                <div>
                                    <span class="font-medium"
                                          :style="{ color: themeColors.textPrimary }">Check-in:</span>
                                    <span :style="{ color: themeColors.textSecondary }"> {{ selectedReservation.check_in_date }}</span>
                                </div>
                                <div>
                                    <span class="font-medium"
                                          :style="{ color: themeColors.textPrimary }">Check-out:</span>
                                    <span :style="{ color: themeColors.textSecondary }"> {{ selectedReservation.check_out_date }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="selectedReservation">
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Select Room *</label>
                        <select v-model="assignForm.room_id" required
                                class="w-full rounded-md px-3 py-2 focus:outline-none focus:ring-2 transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="">Select a room</option>
                            <option v-for="room in availableRooms" :key="room.id" :value="room.id">
                                {{ room.room_number }} - {{ room.room_type?.name || 'Standard' }}
                            </option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end space-x-4 pt-4 border-t"
                         :style="{ 
                             borderColor: themeColors.border,
                             borderTopWidth: '1px'
                         }">
                        <button type="button" @click="showAssignModal = false"
                                class="px-6 py-2 rounded-md transition-colors font-medium"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    color: themeColors.textPrimary,
                                    borderColor: themeColors.border,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                            Cancel
                        </button>
                        <button type="submit" :disabled="assignForm.processing"
                                class="px-6 py-2 rounded-md transition-colors font-medium text-white"
                                :style="{
                                    backgroundColor: assignForm.processing ? themeColors.border : '#10b981',
                                    opacity: assignForm.processing ? 0.7 : 1
                                }"
                                @mouseenter="!assignForm.processing && ($event.target.style.backgroundColor = '#059669')"
                                @mouseleave="!assignForm.processing && ($event.target.style.backgroundColor = '#10b981')">
                            <span v-if="assignForm.processing">Assigning...</span>
                            <span v-else>Assign Key Card</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme.js'
import {
    DocumentArrowDownIcon,
    PlusIcon,
    CheckCircleIcon,
    UserGroupIcon,
    ExclamationTriangleIcon,
    XCircleIcon,
    CreditCardIcon,
    XMarkIcon
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
    keyCards: Object,
    stats: Object,
    availableKeyCards: Array,
    checkedInReservations: Array,
    availableRooms: Array,
})

const navigation = computed(() => getNavigationForRole('front_desk'))

// Assignment modal state
const showAssignModal = ref(false)
const selectedReservation = ref(null)

// Bulk operations state
const selectedCards = ref([])

// Assignment form
const assignForm = useForm({
    key_card_id: '',
    reservation_id: '',
    room_id: '',
})

// Toggle card selection
const toggleCardSelection = (cardId) => {
    const index = selectedCards.value.indexOf(cardId)
    if (index > -1) {
        selectedCards.value.splice(index, 1)
    } else {
        selectedCards.value.push(cardId)
    }
}

// Select all cards
const selectAllCards = () => {
    if (selectedCards.value.length === props.keyCards.data.length) {
        selectedCards.value = []
    } else {
        selectedCards.value = props.keyCards.data.map(card => card.id)
    }
}

// Bulk return cards
const bulkReturnCards = () => {
    if (!selectedCards.value.length) return
    
    if (confirm(`Return ${selectedCards.value.length} selected key cards?`)) {
        router.post(route('front-desk.key-cards.bulk-return'), {
            card_ids: selectedCards.value
        }, {
            onSuccess: () => {
                selectedCards.value = []
                router.reload()
            },
            onError: (errors) => {
                console.error('Bulk return error:', errors)
            }
        })
    }
}

// Generate comprehensive report
const generateReport = () => {
    const reportData = {
        generated_at: new Date().toISOString(),
        stats: props.stats,
        key_cards: props.keyCards.data,
        summary: {
            total_cards: props.stats.total,
            utilization_rate: ((props.stats.assigned / props.stats.total) * 100).toFixed(1) + '%',
            issues_count: props.stats.lost + props.stats.damaged,
            available_for_assignment: props.stats.available
        }
    }
    
    const blob = new Blob([JSON.stringify(reportData, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `key_cards_report_${new Date().toISOString().split('T')[0]}.json`
    link.click()
    URL.revokeObjectURL(url)
    
    showNotification('Key cards report generated successfully!', 'success')
}

// Load reservation details when selected
const loadReservationDetails = () => {
    const reservation = props.checkedInReservations.find(r => r.id === assignForm.reservation_id)
    selectedReservation.value = reservation
    
    // Auto-select the room if reservation has a room
    if (reservation && reservation.room_id) {
        assignForm.room_id = reservation.room_id
    }
}

// Assign key card
const assignKeyCard = () => {
    assignForm.post(route('front-desk.key-cards.assign'), {
        onSuccess: () => {
            showAssignModal.value = false
            assignForm.reset()
            selectedReservation.value = null
            router.reload()
        },
        onError: (errors) => {
            console.error('Assignment error:', errors)
        }
    })
}

const getStatusClass = (status) => {
    const classes = {
        'available': 'bg-green-100 text-black',
        'assigned': 'bg-blue-100 text-black',
        'lost': 'bg-yellow-100 text-black',
        'damaged': 'bg-red-100 text-black',
        'deactivated': 'bg-gray-100 text-black',
    }
    return classes[status] || 'bg-gray-100 text-black'
}

const getCardTypeClass = (type) => {
    const classes = {
        'standard': 'bg-blue-100 text-blue-800',
        'master': 'bg-purple-100 text-skyblue',
        'staff': 'bg-green-100 text-green-800',
        'maintenance': 'bg-orange-100 text-skyblue',
    }
    return classes[type] || 'bg-gray-100 text-gray-800'
}

const formatStatus = (status) => {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatCardType = (type) => {
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const returnCard = (card) => {
    if (confirm(`Return key card ${card.card_number}?`)) {
        router.post(route('front-desk.key-cards.return', card.id), {}, {
            onSuccess: () => router.reload()
        })
    }
}

const markLost = (card) => {
    if (confirm(`Mark key card ${card.card_number} as lost?`)) {
        router.post(route('front-desk.key-cards.mark-lost', card.id), {}, {
            onSuccess: () => router.reload()
        })
    }
}

const markDamaged = (card) => {
    if (confirm(`Mark key card ${card.card_number} as damaged?`)) {
        router.post(route('front-desk.key-cards.mark-damaged', card.id), {}, {
            onSuccess: () => router.reload()
        })
    }
}

const exportKeyCards = () => {
    showExportDialog()
}

const showExportDialog = () => {
    // Create modal dialog
    const modal = document.createElement('div')
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50'
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6" style="background-color: var(--kotel-card); color: var(--kotel-text-primary);">
            <h3 class="text-lg font-semibold mb-4">Choose Export Format</h3>
            <div class="space-y-3">
                <button onclick="exportData('csv')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div>
                            <div class="font-medium">CSV</div>
                            <div class="text-sm text-gray-500">Excel-compatible spreadsheet format</div>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                <button onclick="exportData('excel')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v6a2 2 0 002 2h2m4-4h.01M17 16h.01"></path>
                        </svg>
                        <div>
                            <div class="font-medium">Excel</div>
                            <div class="text-sm text-gray-500">HTML table for Excel import</div>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                <button onclick="exportData('pdf')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <div class="font-medium">PDF</div>
                            <div class="text-sm text-gray-500">Portable Document Format</div>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
            <div class="flex gap-3 mt-6">
                <button onclick="closeExportDialog()" class="flex-1 px-4 py-2 border rounded-lg hover:bg-gray-50 transition-colors" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    Cancel
                </button>
            </div>
        </div>
    `
    
    // Add to page
    document.body.appendChild(modal)
    
    // Make functions globally available
    window.exportData = (format) => {
        closeExportDialog()
        performExport(format)
    }
    
    window.closeExportDialog = () => {
        document.body.removeChild(modal)
        delete window.exportData
        delete window.closeExportDialog
    }
    
    // Close on backdrop click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeExportDialog()
        }
    })
}

const performExport = (format) => {
    try {
        const today = new Date().toISOString().split('T')[0]
        let filename, content, mimeType
        
        switch (format) {
            case 'csv':
                filename = `key_cards_${today}.csv`
                content = generateKeyCardsCSV()
                mimeType = 'text/csv;charset=utf-8;'
                break
            case 'excel':
                filename = `key_cards_${today}.html`
                content = generateExcelContent()
                mimeType = 'text/html;charset=utf-8;'
                break
            case 'pdf':
                filename = `key_cards_${today}.pdf`
                content = generatePDFContent()
                mimeType = 'application/pdf'
                break
        }
        
        // Create blob and download
        const blob = new Blob([content], { type: mimeType })
        const link = document.createElement('a')
        const url = URL.createObjectURL(blob)
        
        link.setAttribute('href', url)
        link.setAttribute('download', filename)
        link.style.visibility = 'hidden'
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        
        // Clean up
        URL.revokeObjectURL(url)
        
        // Show success message
        showNotification(`Key cards exported as ${format.toUpperCase()} successfully!`, 'success')
    } catch (error) {
        console.error('Export error:', error)
        showNotification(`Failed to export as ${format.toUpperCase()}`, 'error')
    }
}

const generateKeyCardsCSV = () => {
    const headers = ['Card Number', 'Type', 'Status', 'Room', 'Guest', 'Issued At', 'Returned At', 'Notes']
    let csv = headers.join(',') + '\n'
    
    props.keyCards.data.forEach(card => {
        const row = [
            `"${card.card_number || ''}"`,
            `"${formatCardType(card.card_type) || ''}"`,
            `"${formatStatus(card.status) || ''}"`,
            `"${card.room?.number || ''}"`,
            `"${card.guest?.name || ''}"`,
            `"${card.issued_at || ''}"`,
            `"${card.returned_at || ''}"`,
            `"${card.notes || ''}"`
        ]
        csv += row.join(',') + '\n'
    })
    
    return csv
}

const generateExcelContent = () => {
    // Generate HTML table that can be opened in Excel
    let htmlTable = '<table>\n'
    
    // Add headers
    htmlTable += '<thead><tr>'
    const headers = ['Card Number', 'Type', 'Status', 'Room', 'Guest', 'Issued At', 'Returned At', 'Notes']
    headers.forEach(header => {
        htmlTable += `<th>${header}</th>`
    })
    htmlTable += '</tr></thead>\n'
    
    // Add data rows
    htmlTable += '<tbody>'
    props.keyCards.data.forEach(card => {
        if (card) {
            const data = [
                card.card_number || '',
                formatCardType(card.card_type) || '',
                formatStatus(card.status) || '',
                card.room?.number || '',
                card.guest?.name || '',
                card.issued_at || '',
                card.returned_at || '',
                card.notes || ''
            ]
            
            htmlTable += '<tr>'
            data.forEach(cell => {
                htmlTable += `<td>${String(cell).replace(/"/g, '')}</td>`
            })
            htmlTable += '</tr>\n'
        }
    })
    htmlTable += '</tbody></table>'
    
    // Add HTML structure
    const htmlContent = `
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Key Cards Export</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Key Cards Export - ${new Date().toLocaleDateString()}</h2>
    ${htmlTable}
</body>
</html>`
    
    return htmlContent
}

const generatePDFContent = () => {
    // Generate a simple PDF content
    const keyCards = props.keyCards.data
    const totalRecords = keyCards.length
    
    // Create PDF content as a base64-encoded string
    const pdfHeader = '%PDF-1.4\n'
    const catalog = '1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n'
    const pages = '2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n'
    
    // Create page content
    let pageContent = '3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n/Contents 4 0 R\n/Resources <<\n/Font <<\n/F1 5 0 R\n>>\n>>\n>>\nendobj\n'
    
    // Create content stream
    let content = 'BT\n/F1 12 Tf\n72 720 Td\n(Key Cards Report) Tj\n'
    content += '0 -20 Td\n/F1 10 Tf\n'
    content += '(Generated on ' + new Date().toLocaleDateString() + ' at ' + new Date().toLocaleTimeString() + ') Tj\n'
    content += '0 -15 Td\n'
    content += '(Total Records: ' + totalRecords + ') Tj\n'
    content += '0 -30 Td\n/F1 8 Tf\n'
    
    // Add table headers
    const headers = ['Card Number', 'Type', 'Status', 'Room', 'Guest', 'Issued At', 'Returned At', 'Notes']
    headers.forEach((header, index) => {
        content += '(' + header + ') Tj\n'
        if (index < headers.length - 1) {
            content += '80 0 Td\n'
        } else {
            content += '0 -15 Td\n'
        }
    })
    
    // Add data rows
    keyCards.forEach((card, rowIndex) => {
        if (rowIndex < 15) { // Limit to 15 rows for space
            const yPos = 600 - (rowIndex * 12)
            content += '72 ' + yPos + ' Td\n'
            
            const data = [
                String(card.card_number || ''),
                String(formatCardType(card.card_type) || ''),
                String(formatStatus(card.status) || ''),
                String(card.room?.number || ''),
                String(card.guest?.name || ''),
                String(card.issued_at || ''),
                String(card.returned_at || ''),
                String(card.notes || '')
            ]
            
            data.forEach((cell, cellIndex) => {
                // Escape PDF special characters
                const escapedCell = cell.replace(/[\(\)]/g, '\\$&')
                content += '(' + escapedCell + ') Tj\n'
                if (cellIndex < data.length - 1) {
                    content += '80 0 Td\n'
                }
            })
            
            if (rowIndex < keyCards.length - 1 && rowIndex < 14) {
                content += '0 -12 Td\n'
            }
        }
    })
    
    content += 'ET\n'
    
    const contentLength = content.length
    const contentObj = '4 0 obj\n<<\n/Length ' + contentLength + '\n>>\nstream\n' + content + '\nendstream\nendobj\n'
    
    const fontObj = '5 0 obj\n<<\n/Type /Font\n/Subtype /Type1\n/BaseFont /Helvetica\n>>\nendobj\n'
    
    // Calculate offsets
    const offset1 = pdfHeader.length
    const offset2 = offset1 + catalog.length
    const offset3 = offset2 + pages.length
    const offset4 = offset3 + pageContent.length
    const offset5 = offset4 + contentObj.length
    const offset6 = offset5 + fontObj.length
    
    // Create cross-reference table
    const xref = 'xref\n0 6\n0000000000 65535 f \n' +
        '0000000009 00000 n \n' +
        String(offset1).padStart(10, '0') + ' 00000 n \n' +
        String(offset2).padStart(10, '0') + ' 00000 n \n' +
        String(offset3).padStart(10, '0') + ' 00000 n \n' +
        String(offset4).padStart(10, '0') + ' 00000 n \n' +
        String(offset5).padStart(10, '0') + ' 00000 n \n'
    
    const trailer = 'trailer\n<<\n/Size 6\n/Root 1 0 R\n>>\nstartxref\n' + offset6 + '\n%%EOF'
    
    return pdfHeader + catalog + pages + pageContent + contentObj + fontObj + xref + trailer
}

const showNotification = (message, type = 'success') => {
    // Create notification element
    const notification = document.createElement('div')
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 flex items-center ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } text-white`
    notification.innerHTML = `
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            ${type === 'success' 
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>'
            }
        </svg>
        ${message}
    `
    
    document.body.appendChild(notification)
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (document.body.contains(notification)) {
            document.body.removeChild(notification)
        }
    }, 3000)
}
</script>
