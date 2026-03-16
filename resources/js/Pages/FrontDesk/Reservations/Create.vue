<template>
    <DashboardLayout title="Create Reservation">
        <div class="shadow rounded-lg p-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Create New Reservation</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Create a reservation for walk-in, advance booking, or group booking.</p>
                </div>
                <Link :href="route('front-desk.reservations.index')"
                      class="px-4 py-2 rounded-md transition-colors"
                      :style="{
                          backgroundColor: themeColors.secondary,
                          color: themeColors.textPrimary
                      }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                    <ArrowLeftIcon class="h-4 w-4 mr-2 inline" />
                    Back
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Guest Selection -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Guest Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Select Guest *</label>
                            <select v-model="form.guest_id" required
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }"
                                    @change="onGuestChange">
                                <option value="">Select Existing Guest</option>
                                <option v-for="guest in guests" :key="guest.id" :value="guest.id">
                                    {{ guest.first_name }} {{ guest.last_name }} ({{ guest.email }})
                                    <span v-if="guest.guest_type"> - {{ guest.guest_type.name }}</span>
                                    <span v-if="guest.is_vip"> - VIP</span>
                                </option>
                            </select>
                            <p class="text-xs mt-2">
                                <span :style="{ color: themeColors.textTertiary }">Don't see the guest? </span>
                                <Link :href="route('front-desk.guests.create')"
                                      :style="{ color: themeColors.primary, textDecoration: 'underline' }"
                                      class="hover:opacity-80 transition-opacity">
                                    Create new guest
                                </Link>
                            </p>
                            <div v-if="selectedGuest" class="mt-2 p-2 rounded-md"
                                 :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                                <div v-if="selectedGuest.guest_type" class="flex items-center gap-2 text-sm">
                                    <span class="inline-block w-3 h-3 rounded-full" :style="{ backgroundColor: selectedGuest.guest_type.color }"></span>
                                    <span class="font-medium"
                                          :style="{ color: themeColors.textPrimary }">{{ selectedGuest.guest_type.name }}</span>
                                    <span v-if="selectedGuest.guest_type.discount_percentage > 0"
                                          :style="{ color: themeColors.success }">
                                        ({{ selectedGuest.guest_type.discount_percentage }}% discount)
                                    </span>
                                </div>
                                <div v-if="selectedGuest.is_vip" class="text-sm font-medium mt-1"
                                     :style="{ color: themeColors.warning }">
                                    ⭐ VIP Guest
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Booking Source *</label>
                            <select v-model="form.booking_source" required
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="">Select Source</option>
                                <option v-for="(label, value) in bookingSources" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                        <div v-if="form.booking_source !== 'walk_in'">
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Booking Reference</label>
                            <input v-model="form.booking_reference" type="text"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="Enter booking reference">
                        </div>
                    </div>
                </div>

                <!-- Room Selection -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Room Selection</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Number of Rooms *</label>
                            <input v-model.number="form.number_of_rooms" type="number" min="1" max="10" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   @change="onNumberOfRoomsChange">
                            <p class="text-xs mt-1"
                               :style="{ color: themeColors.textTertiary }">
                                Select how many rooms to book (1-10)
                            </p>
                        </div>
                    </div>

                    <!-- Room Type and Room Selection for Each Room -->
                    <div v-for="(room, index) in roomSelections" :key="index" class="mt-6 p-4 rounded-lg border"
                         :style="{
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderWidth: '1px',
                             borderStyle: 'solid'
                         }">
                        <h4 class="text-md font-medium mb-4"
                            :style="{ color: themeColors.textPrimary }">
                            Room {{ index + 1 }}
                            <span v-if="form.number_of_rooms > 1" class="text-sm font-normal"
                                  :style="{ color: themeColors.textSecondary }">(Select room type and specific room)</span>
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2"
                                       :style="{ color: themeColors.textSecondary }">Room Type *</label>
                                <select v-model="room.room_type_id" required
                                        class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                        :style="{
                                            backgroundColor: themeColors.card,
                                            borderColor: themeColors.border,
                                            color: themeColors.textPrimary,
                                            borderWidth: '1px',
                                            borderStyle: 'solid'
                                        }"
                                        @change="onRoomTypeSelectionChange(index)">
                                    <option value="">Select Room Type</option>
                                    <option v-for="roomType in roomTypes" :key="roomType.id" :value="roomType.id">
                                        {{ roomType.name }} ({{ roomType.code }}) - {{ formatCurrency(roomType.price) }}/night
                                        <span v-if="roomType.capacity"> - {{ roomType.capacity }} guests</span>
                                    </option>
                                </select>
                                <div v-if="room.selectedRoomType" class="mt-2 p-2 rounded-md"
                                     :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textPrimary }">
                                        <div class="font-medium">{{ room.selectedRoomType.name }}</div>
                                        <div>{{ formatCurrency(room.selectedRoomType.price) }}/night</div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2"
                                       :style="{ color: themeColors.textSecondary }">Room Number</label>
                                <select v-model="room.room_id"
                                        class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                        :style="{
                                            backgroundColor: themeColors.card,
                                            borderColor: themeColors.border,
                                            color: themeColors.textPrimary,
                                            borderWidth: '1px',
                                            borderStyle: 'solid'
                                        }"
                                        @change="onRoomSelectionChange(index)">
                                    <option value="">Select Specific Room (Optional)</option>
                                    <option v-for="room in getAvailableRoomsForSelection(index)" :key="room.id" :value="room.id">
                                        {{ room.room_number }} - {{ room.room_type || 'N/A' }}
                                        <span v-if="room.status === 'reserved'"> - Reserved</span>
                                        <span v-else> - Available</span>
                                    </option>
                                </select>
                                <p class="text-xs mt-1"
                                   :style="{ color: themeColors.textTertiary }">
                                    Leave empty to assign room automatically during check-in
                                </p>
                                <div v-if="room.room_id" class="mt-2 p-2 rounded-md"
                                     :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textPrimary }">
                                        <div class="font-medium">Selected: {{ room.selectedRoom?.room_number }}</div>
                                        <div>Type: {{ room.selectedRoom?.room_type || 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2"
                                       :style="{ color: themeColors.textSecondary }">Room Rate per Night</label>
                                <input v-model.number="room.room_rate" type="number" min="0" step="0.01"
                                       class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                       :style="{
                                           backgroundColor: themeColors.card,
                                           borderColor: themeColors.border,
                                           color: themeColors.textPrimary,
                                           borderWidth: '1px',
                                           borderStyle: 'solid'
                                       }"
                                       @input="onRoomRateChange(index)">
                                <p class="text-xs mt-1"
                                   :style="{ color: themeColors.textTertiary }">
                                    Rate for this specific room (defaults to room type rate)
                                </p>
                                <div v-if="room.room_rate && room.selectedRoomType" class="mt-2 p-2 rounded-md"
                                     :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textPrimary }">
                                        <div>Base Rate: {{ formatCurrency(room.selectedRoomType.price) }}/night</div>
                                        <div v-if="room.room_rate !== room.selectedRoomType.price" class="font-medium text-green-600">
                                            Custom Rate: {{ formatCurrency(room.room_rate) }}/night
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Primary Room for main reservation (when multiple rooms) -->
                    <div v-if="form.number_of_rooms > 1 && roomSelections.length > 0" class="mt-4 p-3 rounded-md"
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                        <p class="text-sm" :style="{ color: themeColors.textPrimary }">
                            <strong>Primary Room:</strong> {{ roomSelections[0].room_id ? roomSelections[0].selectedRoom?.room_number : 'Will be assigned automatically' }}
                        </p>
                    </div>
                </div>

                <!-- Dates -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Booking Dates</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Check-in Date *</label>
                            <DatePicker v-model="form.check_in_date" :min="minDate" required
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="{
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px',
                                          borderStyle: 'solid'
                                      }"
                                      placeholder="Select check-in date" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Check-out Date *</label>
                            <DatePicker v-model="form.check_out_date" :min="form.check_in_date" required
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="{
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px',
                                          borderStyle: 'solid'
                                      }"
                                      placeholder="Select check-out date" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Number of Nights</label>
                            <input v-model="form.number_of_nights" type="number" min="1" readonly
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textTertiary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid',
                                       opacity: '0.7'
                                   }">
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Pricing</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Manual Discount Amount</label>
                            <input v-model.number="form.discount_amount" type="number" min="0" step="0.01"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                            <p class="text-xs mt-1"
                               :style="{ color: themeColors.textTertiary }">Additional discount on top of automatic discounts</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Discount Reason</label>
                            <input v-model="form.discount_reason" type="text"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="Reason for manual discount">
                        </div>
                        <div v-if="autoDiscountAmount > 0" class="md:col-span-2 p-3 rounded-md"
                             :style="{
                                 backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                 borderColor: themeColors.success,
                                 borderStyle: 'solid',
                                 borderWidth: '1px'
                             }">
                            <div class="text-sm font-medium mb-1"
                                 :style="{ color: themeColors.success }">Automatic Discounts Applied:</div>
                            <div v-if="guestTypeDiscountAmount > 0" class="text-sm mt-1"
                                 :style="{ color: themeColors.success }">
                                • Guest Type Discount: {{ formatCurrency(guestTypeDiscountAmount) }} ({{ selectedGuest?.guest_type?.discount_percentage }}%)
                            </div>
                            <div v-if="vipDiscountAmount > 0" class="text-sm mt-1"
                                 :style="{ color: themeColors.success }">
                                • VIP Discount: {{ formatCurrency(vipDiscountAmount) }} ({{ vipDiscountPercentage }}%)
                            </div>
                            <div class="text-sm font-semibold mt-2 pt-2 border-t"
                                 :style="{
                                     color: themeColors.success,
                                     borderTopColor: themeColors.success,
                                     borderTopWidth: '1px'
                                 }">
                                Total Automatic Discount: {{ formatCurrency(autoDiscountAmount) }}
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 rounded-lg p-4"
                         :style="{
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="flex justify-between text-sm mb-2">
                            <span :style="{ color: themeColors.textSecondary }">Nights:</span>
                            <span class="font-medium"
                                  :style="{ color: themeColors.textPrimary }">{{ calculatedNights }}</span>
                        </div>
                        <div class="flex justify-between text-sm mb-2">
                            <span :style="{ color: themeColors.textSecondary }">Room Charges:</span>
                            <span class="font-medium"
                                  :style="{ color: themeColors.textPrimary }">{{ formatCurrency(calculatedRoomCharges) }}</span>
                        </div>
                        <div v-if="totalDiscountAmount > 0" class="flex justify-between text-sm mb-2"
                             :style="{ color: themeColors.danger }">
                            <span :style="{ color: themeColors.textSecondary }">Total Discount:</span>
                            <span>-{{ formatCurrency(totalDiscountAmount) }}</span>
                        </div>
                        <div v-if="autoDiscountAmount > 0 && form.discount_amount > 0" class="text-xs mt-1 pl-4"
                             :style="{ color: themeColors.textTertiary }">
                            (Automatic: {{ formatCurrency(autoDiscountAmount) }} + Manual: {{ formatCurrency(form.discount_amount) }})
                        </div>
                        <div class="flex justify-between text-sm font-semibold pt-2 border-t"
                             :style="{
                                 borderTopColor: themeColors.border,
                                 borderTopWidth: '1px'
                             }">
                            <span :style="{ color: themeColors.textSecondary }">Total:</span>
                            <span :style="{ color: themeColors.textPrimary }">{{ formatCurrency(totalAmount) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Group Booking -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Group Booking (Optional)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textPrimary }">Link to Group Booking</label>
                            <select v-model="form.group_booking_id"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option :value="null">Not a group booking</option>
                                <option v-for="group in groupBookings" :key="group.id" :value="group.id">
                                    {{ group.group_number }} - {{ group.group_name }}
                                </option>
                            </select>
                        </div>
                        <div v-if="form.group_booking_id">
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textPrimary }">Billing Type</label>
                            <select v-model="form.billing_type"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="individual">Individual</option>
                                <option value="group_consolidated">Group Consolidated</option>
                                <option value="group_split">Group Split</option>
                            </select>
                        </div>
                        <div class="flex items-center">
                            <input v-model="form.is_group_booking" type="checkbox" class="mr-2 text-blue-600 dark:text-blue-400">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">This is a group booking</label>
                        </div>
                    </div>
                </div>

                <!-- Overbooking Warning -->
                <div v-if="overbookingWarning" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                    <div class="flex items-center">
                        <ExclamationTriangleIcon class="h-5 w-5 text-yellow-600 dark:text-yellow-400 mr-2" />
                        <div class="flex-1">
                            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">{{ overbookingWarning }}</p>
                            <div class="mt-2">
                                <label class="flex items-center">
                                    <input v-model="allowOverbooking" type="checkbox" class="mr-2 text-yellow-600 dark:text-yellow-400">
                                    <span class="text-sm text-yellow-700 dark:text-yellow-400">Allow overbooking</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Special Requests & Preferences -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                       :style="{ color: themeColors.textPrimary }">Special Requests & Preferences</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textPrimary }">Special Requests</label>
                            <textarea v-model="form.special_requests" rows="3"
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="{
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px',
                                          borderStyle: 'solid'
                                      }"
                                      placeholder="Enter any special requests or preferences"></textarea>
                        </div>
                        <div class="flex items-center">
                            <input v-model="form.early_check_in_requested" type="checkbox" class="mr-2 text-blue-600 dark:text-blue-400">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Early Check-in Requested</label>
                        </div>
                        <div class="flex items-center">
                            <input v-model="form.late_check_out_requested" type="checkbox" class="mr-2 text-blue-600 dark:text-blue-400">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Late Check-out Requested</label>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Preferred Check-in Time</label>
                            <TimePicker
                                v-model="form.preferred_check_in_time"
                                placeholder="Select check-in time"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Preferred Check-out Time</label>
                            <TimePicker
                                v-model="form.preferred_check_out_time"
                                placeholder="Select check-out time"
                            />
                        </div>
                        <div class="flex items-center">
                            <input v-model="form.breakfast_included" type="checkbox" class="mr-2 text-blue-600 dark:text-blue-400">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Breakfast Included</label>
                        </div>
                        <div class="flex items-center">
                            <input v-model="form.wifi_included" type="checkbox" class="mr-2 text-blue-600 dark:text-blue-400" checked>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">WiFi Included</label>
                        </div>
                        <div class="flex items-center">
                            <input v-model="form.parking_required" type="checkbox" class="mr-2 text-blue-600 dark:text-blue-400">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Parking Required</label>
                        </div>
                    </div>
                </div>

                <!-- Status & Email -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Status & Confirmation</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textPrimary }">Initial Status</label>
                            <select v-model="form.status"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                            </select>
                        </div>
                        <div class="flex items-center">
                            <input v-model="form.send_confirmation_email" type="checkbox" class="mr-2 text-blue-600 dark:text-blue-400">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Send Confirmation Email</label>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-600">
                    <Link :href="route('front-desk.reservations.index')"
                          class="bg-gray-300 text-gray-700 dark:bg-gray-600 dark:text-white px-6 py-2 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50 transition-colors">
                        <span v-if="form.processing">Creating...</span>
                        <span v-else>Create Reservation</span>
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DatePicker from '@/Components/DatePicker.vue'
import TimePicker from '@/Components/TimePicker.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { formatCurrency } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme.js'
import {
    ArrowLeftIcon,
    ExclamationTriangleIcon
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
    guests: Array,
    roomTypes: Array,
    groupBookings: Array,
    availableRooms: Array,
    bookingSources: Object,
})

const navigation = computed(() => getNavigationForRole('front_desk'))

const minDate = new Date().toISOString().split('T')[0]

const form = useForm({
    guest_id: '',
    room_type_id: '',
    room_id: null,
    number_of_rooms: 1,
    check_in_date: '',
    check_out_date: '',
    number_of_adults: 1,
    number_of_children: 0,
    infants: 0,
    booking_source: 'walk_in',
    booking_reference: '',
    room_rate: 0,
    discount_amount: 0,
    discount_reason: '',
    special_requests: '',
    room_preferences: [],
    early_check_in_requested: false,
    late_check_out_requested: false,
    preferred_check_in_time: '',
    preferred_check_out_time: '',
    breakfast_included: false,
    wifi_included: true,
    parking_required: false,
    airport_pickup: false,
    airport_drop: false,
    group_booking_id: null,
    is_group_booking: false,
    billing_type: 'individual',
    status: 'pending',
    send_confirmation_email: false,
})

// Auto-select guest from URL query param (e.g. when navigating from guest show page)
;(() => {
    const urlGuestId = new URLSearchParams(window.location.search).get('guest_id')
    if (urlGuestId) {
        form.guest_id = parseInt(urlGuestId)
    }
})()

// Multi-room selection state
const roomSelections = ref([
    { room_type_id: '', room_id: '', selectedRoomType: null, selectedRoom: null }
])

const allowOverbooking = ref(false)
const overbookingWarning = ref('')

// Initialize room selections when number_of_rooms changes
const onNumberOfRoomsChange = () => {
    const numRooms = form.number_of_rooms || 1
    const currentLength = roomSelections.value.length

    if (numRooms > currentLength) {
        // Add more room selections
        for (let i = currentLength; i < numRooms; i++) {
            roomSelections.value.push({
                room_type_id: '',
                room_id: '',
                selectedRoomType: null,
                selectedRoom: null
            })
        }
    } else if (numRooms < currentLength) {
        // Remove excess room selections
        roomSelections.value.splice(numRooms)
    }

    // Sync first room selection with form's room_type_id for backward compatibility
    if (roomSelections.value.length > 0 && form.room_type_id) {
        roomSelections.value[0].room_type_id = form.room_type_id
        roomSelections.value[0].selectedRoomType = props.roomTypes.find(t => t.id == form.room_type_id)
    }
}

// Get available rooms for a specific room selection (excluding already selected rooms)
const getAvailableRoomsForSelection = (index) => {
    const selectedRoom = roomSelections.value[index]
    if (!selectedRoom.room_type_id) return []

    // Get rooms already selected in other slots
    const otherSelectedRoomIds = roomSelections.value
        .filter((_, i) => i !== index && _.room_id)
        .map(_ => _.room_id)

    // Filter by room type and exclude already selected rooms
    return props.availableRooms.filter(room => {
        const matchesType = room.room_type_id == selectedRoom.room_type_id
        const notAlreadySelected = !otherSelectedRoomIds.includes(room.id)
        return matchesType && notAlreadySelected
    })
}

// Handle room type selection change
const onRoomTypeSelectionChange = (index) => {
    const room = roomSelections.value[index]
    if (room.room_type_id) {
        room.selectedRoomType = props.roomTypes.find(t => t.id == room.room_type_id)
        room.room_id = ''
        room.selectedRoom = null

        // Update form's room_type_id and room_rate for backward compatibility
        if (index === 0) {
            form.room_type_id = room.room_type_id
            form.room_rate = room.selectedRoomType?.price || 0
        }
    }
}

// Handle room selection change
const onRoomSelectionChange = (index) => {
    const room = roomSelections.value[index]
    if (room.room_id) {
        room.selectedRoom = props.availableRooms.find(r => r.id == room.room_id)

        // Update form's room_id for backward compatibility
        if (index === 0) {
            form.room_id = room.room_id
        }
    } else {
        room.selectedRoom = null
        if (index === 0) {
            form.room_id = null
        }
    }
}

// Computed property for backward compatibility
const filteredAvailableRooms = computed(() => {
    if (!form.room_type_id) return props.availableRooms
    return props.availableRooms.filter(room => room.room_type_id == form.room_type_id)
})

const selectedRoom = computed(() => {
    if (!form.room_id) return null
    return props.availableRooms.find(room => room.id == form.room_id)
})

const calculatedNights = computed(() => {
    if (!form.check_in_date || !form.check_out_date) return 0
    const checkIn = new Date(form.check_in_date)
    const checkOut = new Date(form.check_out_date)
    return Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24))
})

// Calculate room charges for all rooms
const calculatedRoomCharges = computed(() => {
    let total = 0
    const nights = calculatedNights.value

    if (form.number_of_rooms > 1 && roomSelections.value.length > 0) {
        // Calculate charges for each room selection
        roomSelections.value.forEach(room => {
            if (room.room_type_id && room.selectedRoomType) {
                // Use the room's specific rate if available, otherwise use room type rate
                const rate = room.selectedRoom?.room_rate || room.selectedRoomType.price || form.room_rate
                total += rate * nights
            }
        })
    } else {
        // Single room - use original calculation
        total = (form.room_rate * nights) || 0
    }

    return total
})

// Get all selected room IDs
const selectedRoomIds = computed(() => {
    if (form.number_of_rooms > 1) {
        return roomSelections.value
            .filter(r => r.room_id)
            .map(r => r.room_id)
    }
    return form.room_id ? [form.room_id] : []
})

// Get all selected room types
const selectedRoomTypeIds = computed(() => {
    if (form.number_of_rooms > 1) {
        return roomSelections.value
            .filter(r => r.room_type_id)
            .map(r => r.room_type_id)
    }
    return form.room_type_id ? [form.room_type_id] : []
})

const selectedGuest = computed(() => {
    if (!form.guest_id) return null
    return props.guests.find(g => g.id == form.guest_id)
})

const vipDiscountPercentage = computed(() => {
    // This should come from settings, but for now use default
    return 10 // Default VIP discount
})

const guestTypeDiscountAmount = computed(() => {
    if (!selectedGuest.value || !selectedGuest.value.guest_type || !selectedGuest.value.guest_type.discount_percentage) {
        return 0
    }
    const discountPercentage = selectedGuest.value.guest_type.discount_percentage
    return (calculatedRoomCharges.value * discountPercentage) / 100
})

const vipDiscountAmount = computed(() => {
    if (!selectedGuest.value || !selectedGuest.value.is_vip) {
        return 0
    }
    return (calculatedRoomCharges.value * vipDiscountPercentage.value) / 100
})

const autoDiscountAmount = computed(() => {
    return guestTypeDiscountAmount.value + vipDiscountAmount.value
})

const totalDiscountAmount = computed(() => {
    return autoDiscountAmount.value + (form.discount_amount || 0)
})

const totalAmount = computed(() => {
    return calculatedRoomCharges.value - totalDiscountAmount.value
})

const selectedRoomType = computed(() => {
    if (!form.room_type_id) return null
    return props.roomTypes.find(t => t.id == form.room_type_id)
})

// Update number_of_nights when dates change
watch([() => form.check_in_date, () => form.check_out_date], () => {
    form.number_of_nights = calculatedNights.value
})

const onRoomTypeChange = () => {
    const selectedType = props.roomTypes.find(t => t.id == form.room_type_id)
    if (selectedType) {
        form.room_rate = selectedType.price || 0
    }
    // Clear room selection when room type changes
    form.room_id = null
}

const onRoomChange = () => {
    // Optional: Auto-update room rate based on selected room's type
    if (form.room_id && selectedRoom.value) {
        const roomType = props.roomTypes.find(t => t.id == selectedRoom.value.room_type_id)
        if (roomType) {
            form.room_rate = roomType.price || 0
            form.room_type_id = roomType.id
        }
    }
}

const onGuestChange = () => {
    // Guest type discount is automatically calculated via computed properties
    // This could load guest preferences here if needed
}

watch([() => form.check_in_date, () => form.check_out_date, () => form.room_type_id], () => {
    // Check for overbooking
    if (form.check_in_date && form.check_out_date && form.room_type_id) {
        // This would be checked server-side, but we can show a warning
        overbookingWarning.value = ''
    }
})

const submit = () => {
    if (overbookingWarning.value && !allowOverbooking.value) {
        alert('Please allow overbooking or adjust dates to proceed.')
        return
    }

    // Prepare form data
    const formData = {
        ...form.data(),
        allow_overbooking: allowOverbooking.value,
        number_of_rooms: form.number_of_rooms,
    }

    // For single room, use the traditional format
    if (form.number_of_rooms === 1 && roomSelections.value.length > 0) {
        const room = roomSelections.value[0]
        if (room.room_type_id) {
            formData.room_type_id = room.room_type_id
            formData.room_rate = room.room_rate || (room.selectedRoomType?.price || 0)
        }
        if (room.room_id) {
            formData.room_id = room.room_id
        }
    } else if (form.number_of_rooms > 1) {
        // For multiple rooms, include the room selections
        formData.selected_rooms = roomSelections.value
    }

    form.post(route('front-desk.reservations.store'), {
        data: formData
    })
}
</script>

<style scoped>
/* Fix placeholder colors for inputs */
input::placeholder,
textarea::placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input::-webkit-input-placeholder,
textarea::-webkit-input-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input::-moz-placeholder,
textarea::-moz-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input:-ms-input-placeholder,
textarea:-ms-input-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

/* Fix placeholder colors for select options */
select option:disabled,
select option[disabled] {
    color: var(--kotel-text-tertiary) !important;
}

select option[value=""] {
    color: var(--kotel-text-tertiary) !important;
}
</style>
