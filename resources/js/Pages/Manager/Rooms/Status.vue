<template>
    <DashboardLayout title="Room Status" :user="user">
        <!-- Header -->
        <div class="bg-kotel-dark border border-kotel-yellow/30 rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-kotel-yellow">Room Management</h1>
                    <p class="text-kotel-sky-blue mt-2">View all rooms and manage check-outs.</p>
                </div>
            </div>
        </div>

        <div v-if="statusMessage" class="rounded-lg p-4 mb-6 border"
             :class="statusType === 'error' ? 'bg-red-900/50 border-red-400/50 text-red-300' : 'bg-emerald-900/50 border-emerald-400/50 text-emerald-300'">
            {{ statusMessage }}
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-emerald-900/50 to-emerald-800/30 rounded-lg shadow-lg p-6 border-2 border-emerald-400/30">
                <div class="text-center">
                    <div class="text-4xl font-bold text-emerald-300">{{ roomStatus?.available || 0 }}</div>
                    <div class="text-sm font-medium text-emerald-400 mt-2">Available</div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-blue-900/50 to-blue-800/30 rounded-lg shadow-lg p-6 border-2 border-blue-400/30">
                <div class="text-center">
                    <div class="text-4xl font-bold text-blue-300">{{ roomStatus?.occupied || 0 }}</div>
                    <div class="text-sm font-medium text-blue-400 mt-2">Occupied</div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-amber-900/50 to-amber-800/30 rounded-lg shadow-lg p-6 border-2 border-amber-400/30">
                <div class="text-center">
                    <div class="text-4xl font-bold text-amber-300">{{ roomStatus?.cleaning || 0 }}</div>
                    <div class="text-sm font-medium text-amber-400 mt-2">Cleaning</div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-red-900/50 to-red-800/30 rounded-lg shadow-lg p-6 border-2 border-red-400/30">
                <div class="text-center">
                    <div class="text-4xl font-bold text-red-300">{{ roomStatus?.maintenance || 0 }}</div>
                    <div class="text-sm font-medium text-red-400 mt-2">Maintenance</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-kotel-dark border border-kotel-yellow/30 rounded-lg p-6 mb-8">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" v-model="searchQuery"
                           placeholder="Search by room number, guest name, or type..."
                           class="w-full bg-kotel-black/50 border border-kotel-yellow/30 rounded-md px-4 py-2 text-white placeholder-kotel-sky-blue/60 focus:outline-none focus:ring-2 focus:ring-kotel-yellow focus:border-kotel-yellow">
                </div>
                <div>
                    <select v-model="filterStatus"
                            class="bg-kotel-black/50 border border-kotel-yellow/30 rounded-md px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-kotel-yellow focus:border-kotel-yellow">
                        <option value="" class="bg-kotel-dark">All Status</option>
                        <option value="available" class="bg-kotel-dark">Available</option>
                        <option value="occupied" class="bg-kotel-dark">Occupied</option>
                        <option value="cleaning" class="bg-kotel-dark">Cleaning</option>
                        <option value="maintenance" class="bg-kotel-dark">Maintenance</option>
                        <option value="reserved" class="bg-kotel-dark">Reserved</option>
                    </select>
                </div>
                <div>
                    <select v-model="filterFloor"
                            class="bg-kotel-black/50 border border-kotel-yellow/30 rounded-md px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-kotel-yellow focus:border-kotel-yellow">
                        <option value="" class="bg-kotel-dark">All Floors</option>
                        <option v-for="floor in uniqueFloors" :key="floor" :value="floor" class="bg-kotel-dark">
                            {{ floor }}
                        </option>
                    </select>
                </div>
                <button @click="clearFilters"
                        class="px-4 py-2 text-kotel-sky-blue hover:text-white border border-kotel-yellow/30 rounded-md hover:bg-kotel-yellow/20 transition-colors">
                    Clear Filters
                </button>
            </div>
        </div>

        <!-- Rooms Grid -->
        <div class="bg-kotel-dark border border-kotel-yellow/30 rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-kotel-yellow">All Rooms ({{ filteredRooms.length }})</h3>
                <div class="flex gap-2">
                    <button @click="viewMode = 'grid'"
                            :class="viewMode === 'grid' ? 'bg-kotel-yellow text-kotel-black' : 'bg-kotel-gray text-kotel-sky-blue'"
                            class="px-3 py-1 rounded-md text-sm font-medium transition-colors">
                        Grid
                    </button>
                    <button @click="viewMode = 'list'"
                            :class="viewMode === 'list' ? 'bg-kotel-yellow text-kotel-black' : 'bg-kotel-gray text-kotel-sky-blue'"
                            class="px-3 py-1 rounded-md text-sm font-medium transition-colors">
                        List
                    </button>
                </div>
            </div>

            <!-- Grid View -->
            <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                <div v-for="room in filteredRooms" :key="room.id"
                     class="relative rounded-xl shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer overflow-hidden border-2"
                     :class="getRoomCardClass(room.status)"
                     @click="selectedRoom = room">
                    <!-- Status Indicator -->
                    <div class="absolute top-2 right-2 flex gap-1">
                        <span v-if="room.status === 'available' && room.pending_reservation"
                              class="px-2 py-1 rounded-full text-xs font-semibold shadow-sm bg-emerald-800/50 text-emerald-300 border border-emerald-400/50"
                              title="Pending check-in">
                            Check-in
                        </span>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold shadow-sm"
                              :class="getStatusBadgeClass(room.status)">
                            {{ formatStatus(room.status) }}
                        </span>
                    </div>

                    <!-- Room Number -->
                    <div class="p-4 pb-2">
                        <div class="text-3xl font-bold mb-1" :class="getRoomNumberColor(room.status)">
                            {{ room.number }}
                        </div>
                        <div class="text-sm font-medium text-kotel-sky-blue/80 mb-2">{{ room.type }}</div>
                        <div class="text-xs text-kotel-sky-blue/60">{{ room.floor }}</div>
                    </div>

                    <!-- Pending Check-in Info (if available) -->
                    <div v-if="room.status === 'available' && room.pending_reservation"
                         class="px-4 pb-3 border-t border-emerald-400/30 pt-3 bg-emerald-900/20">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-xs font-medium text-emerald-300">Pending Check-in</span>
                        </div>
                        <div class="text-xs font-semibold text-white">{{ room.pending_reservation.guest_name }}</div>
                        <div class="text-xs text-kotel-sky-blue/80 mt-1">Reservation #{{ room.pending_reservation.reservation_number }}</div>
                    </div>

                    <!-- Guest Info (if occupied) -->
                    <div v-if="room.status === 'occupied' && room.guest"
                         class="px-4 pb-3 border-t border-opacity-20 pt-3"
                         :class="getBorderColor(room.status)">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-4 h-4" :class="getIconColor(room.status)" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zM10 11a6 6 0 00-3.885 5.654A11.947 11.947 0 0110 18a11.947 11.947 0 01-3.885-.346A6 6 0 0010 11zm7-4a1 1 0 10-2 0v.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l2-2a1 1 0 00-1.414-1.414L17 7.586V7z"/>
                            </svg>
                            <span class="text-xs font-medium truncate" :class="getTextColor(room.status)">
                                {{ room.guest }}
                            </span>
                        </div>
                        <div v-if="room.key_card" class="flex items-center gap-1 mb-1">
                            <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-xs font-medium text-amber-300">
                                Card: {{ room.key_card.card_number }}
                            </span>
                        </div>
                        <div v-if="room.check_out" class="text-xs" :class="getTextColor(room.status, true)">
                            Check-out: {{ formatDate(room.check_out) }}
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="px-4 pb-4 pt-2">
                        <div v-if="room.status === 'occupied'" class="flex gap-2">
                            <button v-if="room.reservation_id"
                                    @click.stop="quickCheckOut(room)"
                                    class="flex-1 bg-red-600/90 hover:bg-red-600 text-white text-xs font-medium py-2 px-3 rounded-md transition-colors border border-red-400/50">
                                Check Out
                            </button>
                            <button v-else
                                    @click.stop="manualCheckOut(room)"
                                    class="flex-1 bg-amber-600/90 hover:bg-amber-600 text-white text-xs font-medium py-2 px-3 rounded-md transition-colors border border-amber-400/50"
                                    title="No linked reservation. Mark room available for cleaning.">
                                Manual Checkout
                            </button>
                            <button @click.stop="selectedRoom = room"
                                    class="px-3 py-2 bg-kotel-black/50 border border-kotel-yellow/30 hover:bg-kotel-yellow/20 text-kotel-sky-blue text-xs font-medium rounded-md transition-colors">
                                Details
                            </button>
                        </div>
                        <div v-else-if="room.status === 'available' && room.pending_reservation" class="flex gap-2">
                            <button @click.stop="quickCheckIn(room)"
                                    class="flex-1 bg-emerald-600/90 hover:bg-emerald-600 text-white text-xs font-medium py-2 px-3 rounded-md transition-colors border border-emerald-400/50">
                                Check In
                            </button>
                            <button @click.stop="selectedRoom = room"
                                    class="px-3 py-2 bg-kotel-black/50 border border-kotel-yellow/30 hover:bg-kotel-yellow/20 text-kotel-sky-blue text-xs font-medium rounded-md transition-colors">
                                Details
                            </button>
                        </div>
                        <button v-else @click.stop="selectedRoom = room"
                                class="w-full bg-kotel-black/50 border-2 border-kotel-yellow/30 hover:bg-kotel-yellow/20 text-kotel-sky-blue text-xs font-medium py-2 px-3 rounded-md transition-colors">
                            View Details
                        </button>
                    </div>

                    <!-- Housekeeping Status Badge -->
                    <div v-if="room.housekeeping_status"
                         class="absolute bottom-2 left-2">
                        <span class="px-2 py-1 rounded-full text-xs font-medium border"
                              :class="{
                                  'bg-red-800/60 text-red-200 border-red-400/50':   room.housekeeping_status === 'dirty',
                                  'bg-emerald-800/60 text-emerald-200 border-emerald-400/50': room.housekeeping_status === 'clean' || room.housekeeping_status === 'inspected',
                                  'bg-amber-800/60 text-amber-200 border-amber-400/50':  room.housekeeping_status === 'waiting_for_check',
                                  'bg-orange-800/60 text-orange-200 border-orange-400/50': room.housekeeping_status === 'maintenance_required',
                              }">
                            {{ room.housekeeping_status.replace('_', ' ') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div v-else class="space-y-3">
                <div v-for="room in filteredRooms" :key="room.id"
                     class="border-2 rounded-lg p-4 hover:shadow-md transition-all cursor-pointer"
                     :class="getRoomCardClass(room.status)"
                     @click="selectedRoom = room">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4 flex-1">
                            <div class="text-2xl font-bold" :class="getRoomNumberColor(room.status)">
                                {{ room.number }}
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-white">{{ room.type }}</div>
                                <div class="text-sm text-kotel-sky-blue/80">{{ room.floor }}</div>
                                <div v-if="room.status === 'occupied' && room.guest" class="text-sm mt-1">
                                    <span class="font-medium text-kotel-sky-blue">Guest:</span> <span class="text-white">{{ room.guest }}</span>
                                    <span v-if="room.check_out" class="text-kotel-sky-blue/60 ml-2">
                                        | Check-out: {{ formatDate(room.check_out) }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right flex flex-col items-end gap-1">
                                <span class="px-3 py-1 rounded-full text-sm font-semibold"
                                      :class="getStatusBadgeClass(room.status)">
                                    {{ formatStatus(room.status) }}
                                </span>
                                <span v-if="room.housekeeping_status"
                                      class="px-2 py-0.5 rounded-full text-xs font-medium border"
                                      :class="{
                                          'bg-red-800/60 text-red-200 border-red-400/50':             room.housekeeping_status === 'dirty',
                                          'bg-emerald-800/60 text-emerald-200 border-emerald-400/50': room.housekeeping_status === 'clean' || room.housekeeping_status === 'inspected',
                                          'bg-amber-800/60 text-amber-200 border-amber-400/50':       room.housekeeping_status === 'waiting_for_check',
                                          'bg-orange-800/60 text-orange-200 border-orange-400/50':    room.housekeeping_status === 'maintenance_required',
                                      }">
                                    🧹 {{ room.housekeeping_status.replace('_', ' ') }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-4 flex gap-2">
                            <button v-if="room.status === 'occupied' && room.reservation_id"
                                    @click.stop="quickCheckOut(room)"
                                    class="bg-red-600/90 hover:bg-red-600 text-white text-sm font-medium py-2 px-4 rounded-md transition-colors border border-red-400/50">
                                Check Out
                            </button>
                            <button v-else-if="room.status === 'occupied'"
                                    @click.stop="manualCheckOut(room)"
                                    class="bg-amber-600/90 hover:bg-amber-600 text-white text-sm font-medium py-2 px-4 rounded-md transition-colors border border-amber-400/50"
                                    title="No linked reservation. Mark room available for cleaning.">
                                Manual Checkout
                            </button>
                            <button v-else-if="room.status === 'available' && room.pending_reservation"
                                    @click.stop="quickCheckIn(room)"
                                    class="bg-emerald-600/90 hover:bg-emerald-600 text-white text-sm font-medium py-2 px-4 rounded-md transition-colors border border-emerald-400/50">
                                Check In
                            </button>
                            <button v-else
                                    @click.stop="selectedRoom = room"
                                    class="bg-kotel-yellow text-kotel-black text-sm font-medium py-2 px-4 rounded-md transition-colors">
                                View
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="filteredRooms.length === 0" class="text-center py-12 text-kotel-sky-blue/60">
                <svg class="mx-auto h-12 w-12 text-kotel-sky-blue/40 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <p class="text-lg font-medium text-white">No rooms found</p>
                <p class="text-sm mt-1 text-kotel-sky-blue/60">Try adjusting your filters</p>
            </div>
        </div>

        <!-- Room Details Modal (unified dark theme) -->
        <div v-if="selectedRoom" @click="selectedRoom = null" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
            <div @click.stop class="bg-kotel-dark border border-kotel-yellow/30 rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <!-- Header -->
                <div class="bg-kotel-black/50 border-b border-kotel-yellow/30 p-5 rounded-t-xl">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold text-kotel-yellow">Room {{ selectedRoom.number }}</h2>
                            <p class="text-kotel-sky-blue mt-1">{{ selectedRoom.type }}</p>
                        </div>
                        <button @click="selectedRoom = null" class="text-white/80 hover:text-white p-1 rounded">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="px-3 py-1 rounded-full text-sm font-medium border" :class="getModalStatusBadgeClass(selectedRoom.status)">
                            {{ selectedRoom.status }}
                        </span>
                        <span v-if="selectedRoom.housekeeping_status" class="px-3 py-1 rounded-full text-sm font-medium border border-kotel-yellow/40 bg-kotel-yellow/10 text-kotel-yellow">
                            {{ selectedRoom.housekeeping_status }}
                        </span>
                    </div>
                </div>

                <div class="p-5 space-y-5">
                    <!-- Room Information -->
                    <div>
                        <h3 class="text-kotel-yellow font-semibold mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Room Details
                        </h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-kotel-black/50 border border-kotel-yellow/20 rounded-lg p-3">
                                <span class="text-kotel-sky-blue/90 text-sm block">Floor</span>
                                <p class="text-white font-medium">{{ selectedRoom.floor }}</p>
                            </div>
                            <div class="bg-kotel-black/50 border border-kotel-yellow/20 rounded-lg p-3">
                                <span class="text-kotel-sky-blue/90 text-sm block">Capacity</span>
                                <p class="text-white font-medium">{{ selectedRoom.capacity }} guests</p>
                            </div>
                            <div class="bg-kotel-black/50 border border-kotel-yellow/20 rounded-lg p-3">
                                <span class="text-kotel-sky-blue/90 text-sm block">Bed Type</span>
                                <p class="text-white font-medium">{{ selectedRoom.bed_type }}</p>
                            </div>
                            <div class="bg-kotel-black/50 border border-kotel-yellow/20 rounded-lg p-3">
                                <span class="text-kotel-sky-blue/90 text-sm block">View</span>
                                <p class="text-white font-medium">{{ selectedRoom.view_type }}</p>
                            </div>
                            <div class="bg-kotel-black/50 border border-kotel-yellow/20 rounded-lg p-3">
                                <span class="text-kotel-sky-blue/90 text-sm block">Price/Night</span>
                                <p class="text-emerald-300 font-medium">
                                    {{ formatCurrency(selectedRoom.status === 'occupied' && selectedRoom.room_rate ? selectedRoom.room_rate : selectedRoom.price) }}
                                </p>
                            </div>
                            <div class="bg-kotel-black/50 border border-kotel-yellow/20 rounded-lg p-3">
                                <span class="text-kotel-sky-blue/90 text-sm block">Last Cleaned</span>
                                <p class="text-white font-medium">{{ formatDate(selectedRoom.last_cleaned) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Guest Information (if occupied) -->
                    <div v-if="selectedRoom.guest">
                        <h3 class="text-kotel-yellow font-semibold mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Current Guest
                        </h3>
                        <div class="bg-kotel-black/50 border border-blue-400/30 rounded-lg p-4">
                            <p class="text-white font-semibold text-lg">{{ selectedRoom.guest }}</p>
                            <div class="mt-2 space-y-1 text-kotel-sky-blue/90 text-sm">
                                <p v-if="selectedRoom.guest_phone"><span class="text-kotel-yellow/90">Phone:</span> {{ selectedRoom.guest_phone }}</p>
                                <p v-if="selectedRoom.guest_email"><span class="text-kotel-yellow/90">Email:</span> {{ selectedRoom.guest_email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Reservation (if available) -->
                    <div v-if="selectedRoom.status === 'available' && selectedRoom.pending_reservation">
                        <h3 class="text-kotel-yellow font-semibold mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Pending Check-in
                        </h3>
                        <div class="bg-kotel-black/50 border border-emerald-400/30 rounded-lg p-4">
                            <p class="text-white font-semibold text-lg">{{ selectedRoom.pending_reservation.guest_name }}</p>
                            <div class="mt-2 grid grid-cols-2 gap-3 text-sm">
                                <div><span class="text-kotel-sky-blue/90">Reservation #</span><p class="text-white font-medium">#{{ selectedRoom.pending_reservation.reservation_number }}</p></div>
                                <div><span class="text-kotel-sky-blue/90">Check-in</span><p class="text-white font-medium">{{ formatDate(selectedRoom.pending_reservation.check_in_date) }}</p></div>
                                <div><span class="text-kotel-sky-blue/90">Check-out</span><p class="text-white font-medium">{{ formatDate(selectedRoom.pending_reservation.check_out_date) }}</p></div>
                            </div>
                        </div>
                    </div>

                    <!-- Reservation Details (if occupied) -->
                    <div v-if="selectedRoom.reservation_id">
                        <h3 class="text-kotel-yellow font-semibold mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Reservation Details
                        </h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-kotel-black/50 border border-kotel-yellow/20 rounded-lg p-3">
                                <span class="text-kotel-sky-blue/90 text-sm block">Check-in</span>
                                <p class="text-white font-medium">{{ formatDate(selectedRoom.check_in) }}</p>
                            </div>
                            <div class="bg-kotel-black/50 border border-kotel-yellow/20 rounded-lg p-3">
                                <span class="text-kotel-sky-blue/90 text-sm block">Check-out</span>
                                <p class="text-white font-medium">{{ formatDate(selectedRoom.check_out) }}</p>
                            </div>
                            <div class="bg-kotel-black/50 border border-kotel-yellow/20 rounded-lg p-3">
                                <span class="text-kotel-sky-blue/90 text-sm block">Nights</span>
                                <p class="text-white font-medium">{{ selectedRoom.nights }}</p>
                            </div>
                            <div class="bg-kotel-black/50 border border-kotel-yellow/20 rounded-lg p-3">
                                <span class="text-kotel-sky-blue/90 text-sm block">Reservation ID</span>
                                <p class="text-white font-medium">#{{ selectedRoom.reservation_id }}</p>
                            </div>
                            <div v-if="selectedRoom.room_rate" class="bg-kotel-black/50 border border-kotel-yellow/20 rounded-lg p-3">
                                <span class="text-kotel-sky-blue/90 text-sm block">Reserved Price</span>
                                <p class="text-emerald-300 font-medium">{{ formatCurrency(selectedRoom.room_rate) }}</p>
                            </div>
                            <div v-if="selectedRoom.total_room_charges" class="bg-kotel-black/50 border border-kotel-yellow/20 rounded-lg p-3">
                                <span class="text-kotel-sky-blue/90 text-sm block">Total Room Charges</span>
                                <p class="text-emerald-300 font-medium">{{ formatCurrency(selectedRoom.total_room_charges) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Key Card (if occupied) -->
                    <div v-if="selectedRoom.key_card">
                        <h3 class="text-kotel-yellow font-semibold mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Key Card
                        </h3>
                        <div class="bg-kotel-black/50 border border-amber-400/30 rounded-lg p-4 grid grid-cols-2 gap-3">
                            <div><span class="text-kotel-sky-blue/90 text-sm block">Card Number</span><p class="text-white font-medium">{{ selectedRoom.key_card.card_number }}</p></div>
                            <div><span class="text-kotel-sky-blue/90 text-sm block">Type</span><p class="text-white font-medium capitalize">{{ selectedRoom.key_card.card_type }}</p></div>
                        </div>
                    </div>

                    <!-- Payment (if occupied) -->
                    <div v-if="selectedRoom.total_amount != null">
                        <h3 class="text-kotel-yellow font-semibold mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Payment
                        </h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-kotel-black/50 border border-kotel-yellow/20 rounded-lg p-3">
                                <span class="text-kotel-sky-blue/90 text-sm block">Total</span>
                                <p class="text-white font-medium">{{ formatCurrency(selectedRoom.total_amount) }}</p>
                            </div>
                            <div class="bg-kotel-black/50 border border-kotel-yellow/20 rounded-lg p-3">
                                <span class="text-kotel-sky-blue/90 text-sm block">Balance Due</span>
                                <p class="font-medium" :class="selectedRoom.balance > 0 ? 'text-red-300' : 'text-emerald-300'">{{ formatCurrency(selectedRoom.balance) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Add Service Charge (if occupied with reservation) -->
                    <div v-if="selectedRoom.status === 'occupied' && selectedRoom.reservation_id">
                        <h3 class="text-kotel-yellow font-semibold mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            Add Service Charge
                        </h3>
                        <div class="bg-kotel-black/50 border border-blue-400/30 rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                                <div>
                                    <label class="text-kotel-sky-blue/90 text-sm block mb-1">POS Product</label>
                                    <select v-model="serviceChargeForm.product_id"
                                            @change="onServiceProductChange"
                                            class="w-full bg-kotel-black border border-kotel-yellow/30 rounded-md px-3 py-2 text-white text-sm focus:outline-none focus:ring-1 focus:ring-kotel-yellow">
                                        <option :value="null" class="bg-kotel-dark">Select product</option>
                                        <option :value="customServiceOptionValue" class="bg-kotel-dark">Custom service</option>
                                        <option v-for="product in posProducts" :key="product.id" :value="product.id" class="bg-kotel-dark">
                                            {{ product.name }} <span v-if="product.code">({{ product.code }})</span>
                                        </option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="text-kotel-sky-blue/90 text-sm block mb-1">Description</label>
                                    <input v-model="serviceChargeForm.description" type="text"
                                           class="w-full bg-kotel-black border border-kotel-yellow/30 rounded-md px-3 py-2 text-white text-sm focus:outline-none focus:ring-1 focus:ring-kotel-yellow placeholder-kotel-sky-blue/40"
                                           placeholder="e.g. Laundry service, Room service">
                                </div>
                                <div>
                                    <label class="text-kotel-sky-blue/90 text-sm block mb-1">Amount</label>
                                    <input v-model.number="serviceChargeForm.amount" type="number" min="0.01" step="0.01"
                                           class="w-full bg-kotel-black border border-kotel-yellow/30 rounded-md px-3 py-2 text-white text-sm focus:outline-none focus:ring-1 focus:ring-kotel-yellow placeholder-kotel-sky-blue/40"
                                           placeholder="0.00">
                                </div>
                                <div>
                                    <label class="text-kotel-sky-blue/90 text-sm block mb-1">Qty</label>
                                    <input v-model.number="serviceChargeForm.quantity" type="number" min="1" step="1"
                                           class="w-full bg-kotel-black border border-kotel-yellow/30 rounded-md px-3 py-2 text-white text-sm focus:outline-none focus:ring-1 focus:ring-kotel-yellow">
                                </div>
                            </div>
                            <div class="mt-3 flex justify-end">
                                <button type="button" @click="addServiceCharge" :disabled="isAddingServiceCharge"
                                        class="bg-blue-600/90 hover:bg-blue-600 text-white text-sm font-medium py-2 px-4 rounded-md transition border border-blue-400/50">
                                    <span v-if="isAddingServiceCharge">Adding...</span>
                                    <span v-else>Add Charge</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div v-if="selectedRoom.amenities && selectedRoom.amenities.length > 0">
                        <h3 class="text-kotel-yellow font-semibold mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            Amenities
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="amenity in selectedRoom.amenities" :key="amenity" class="px-3 py-1 rounded-full text-sm border border-kotel-yellow/30 bg-kotel-yellow/10 text-kotel-yellow">
                                {{ amenity }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-5 border-t border-kotel-yellow/30 bg-kotel-black/30 rounded-b-xl flex flex-wrap gap-3">
                    <button v-if="selectedRoom.status === 'occupied' && selectedRoom.reservation_id"
                            @click.stop="quickCheckOut(selectedRoom)"
                            class="flex-1 min-w-[140px] bg-red-600/90 text-white py-3 px-4 rounded-lg hover:bg-red-600 font-medium transition border border-red-400/40">
                        Check Out Guest
                    </button>
                    <button v-if="selectedRoom.status === 'occupied'"
                            @click.stop="manualCheckOut(selectedRoom)"
                            class="flex-1 min-w-[140px] bg-amber-600/90 text-white py-3 px-4 rounded-lg hover:bg-amber-600 font-medium transition border border-amber-400/40"
                            title="No linked reservation. Mark room available for cleaning.">
                        Manual Checkout
                    </button>
                    <button v-if="selectedRoom.status === 'cleaning' || selectedRoom.housekeeping_status === 'dirty' || selectedRoom.housekeeping_status === 'waiting_for_check' || (selectedRoom.status === 'available' && selectedRoom.housekeeping_status !== 'clean')"
                            @click.stop="markRoomClean(selectedRoom)"
                            class="flex-1 min-w-[140px] bg-emerald-600/90 text-white py-3 px-4 rounded-lg hover:bg-emerald-600 font-medium transition border border-emerald-400/40">
                        Mark Clean
                    </button>
                    <button v-else-if="selectedRoom.status === 'available' && selectedRoom.pending_reservation"
                            @click.stop="quickCheckIn(selectedRoom)"
                            class="flex-1 min-w-[140px] bg-emerald-600/90 text-white py-3 px-4 rounded-lg hover:bg-emerald-600 font-medium transition border border-emerald-400/40">
                        Check In Guest
                    </button>
                    <button v-if="selectedRoom.housekeeping_status !== 'dirty'"
                            @click.stop="markRoomDirty(selectedRoom)"
                            class="flex-1 min-w-[140px] bg-orange-600/90 text-white py-3 px-4 rounded-lg hover:bg-orange-600 font-medium transition border border-orange-400/40"
                            title="Flag this room for housekeeping cleaning.">
                        Mark Dirty
                    </button>
                    <button @click="selectedRoom = null"
                            class="flex-1 min-w-[140px] bg-kotel-gray border border-kotel-yellow/30 text-kotel-yellow py-3 px-4 rounded-lg hover:bg-kotel-yellow/20 font-medium transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'

const props = defineProps({
    user: Object,
    rooms: Array,
    roomStatus: Object,
    availableKeyCards: Array,
    posProducts: { type: Array, default: () => [] },
})

const selectedRoom = ref(null)
const statusMessage = ref('')
const statusType = ref('success')
const searchQuery = ref('')
const filterStatus = ref('')
const filterFloor = ref('')
const viewMode = ref('grid')

const uniqueFloors = computed(() => {
    const floors = [...new Set(props.rooms.map(r => r.floor).filter(Boolean))]
    return floors.sort()
})

const filteredRooms = computed(() => {
    let filtered = props.rooms || []

    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(room =>
            room.number.toLowerCase().includes(query) ||
            room.type.toLowerCase().includes(query) ||
            room.guest?.toLowerCase().includes(query) ||
            room.floor?.toLowerCase().includes(query)
        )
    }

    // Filter by status
    if (filterStatus.value) {
        filtered = filtered.filter(room => room.status === filterStatus.value)
    }

    // Filter by floor
    if (filterFloor.value) {
        filtered = filtered.filter(room => room.floor === filterFloor.value)
    }

    return filtered
})

const clearFilters = () => {
    searchQuery.value = ''
    filterStatus.value = ''
    filterFloor.value = ''
}

const getRoomCardClass = (status) => {
    const classes = {
        'available': 'border-emerald-400/50 bg-gradient-to-br from-emerald-900/30 to-emerald-800/20',
        'occupied': 'border-blue-400/50 bg-gradient-to-br from-blue-900/30 to-blue-800/20',
        'cleaning': 'border-amber-400/50 bg-gradient-to-br from-amber-900/30 to-amber-800/20',
        'maintenance': 'border-red-400/50 bg-gradient-to-br from-red-900/30 to-red-800/20',
        'reserved': 'border-purple-400/50 bg-gradient-to-br from-purple-900/30 to-purple-800/20',
    }
    return classes[status] || 'border-kotel-gray bg-kotel-black/30'
}

const getRoomNumberColor = (status) => {
    const classes = {
        'available': 'text-emerald-300',
        'occupied': 'text-blue-300',
        'cleaning': 'text-amber-300',
        'maintenance': 'text-red-300',
        'reserved': 'text-purple-300',
    }
    return classes[status] || 'text-kotel-sky-blue'
}

const getStatusBadgeClass = (status) => {
    const classes = {
        'available': 'bg-emerald-600/80 text-white border border-emerald-400/50',
        'occupied': 'bg-blue-600/80 text-white border border-blue-400/50',
        'cleaning': 'bg-amber-600/80 text-white border border-amber-400/50',
        'maintenance': 'bg-red-600/80 text-white border border-red-400/50',
        'reserved': 'bg-purple-600/80 text-white border border-purple-400/50',
    }
    return classes[status] || 'bg-kotel-gray text-white border border-kotel-gray'
}

const getModalStatusBadgeClass = (status) => {
    const classes = {
        'available': 'bg-emerald-500/30 text-emerald-200 border-emerald-400/40',
        'occupied': 'bg-blue-500/30 text-blue-200 border-blue-400/40',
        'cleaning': 'bg-amber-500/30 text-amber-200 border-amber-400/40',
        'maintenance': 'bg-red-500/30 text-red-200 border-red-400/40',
        'reserved': 'bg-purple-500/30 text-purple-200 border-purple-400/40',
    }
    return classes[status] || 'bg-gray-500/30 text-gray-200 border-gray-400/40'
}

const getBorderColor = (status) => {
    const classes = {
        'available': 'border-emerald-400/30',
        'occupied': 'border-blue-400/30',
        'cleaning': 'border-amber-400/30',
        'maintenance': 'border-red-400/30',
        'reserved': 'border-purple-400/30',
    }
    return classes[status] || 'border-kotel-gray'
}

const getIconColor = (status) => {
    const classes = {
        'available': 'text-emerald-400',
        'occupied': 'text-blue-400',
        'cleaning': 'text-amber-400',
        'maintenance': 'text-red-400',
        'reserved': 'text-purple-400',
    }
    return classes[status] || 'text-kotel-sky-blue'
}

const getTextColor = (status, muted = false) => {
    if (muted) {
        const classes = {
            'available': 'text-emerald-400',
            'occupied': 'text-blue-400',
            'cleaning': 'text-amber-400',
            'maintenance': 'text-red-400',
            'reserved': 'text-purple-400',
        }
        return classes[status] || 'text-kotel-sky-blue'
    }
    const classes = {
        'available': 'text-emerald-200',
        'occupied': 'text-blue-200',
        'cleaning': 'text-amber-200',
        'maintenance': 'text-red-200',
        'reserved': 'text-purple-200',
    }
    return classes[status] || 'text-kotel-sky-blue'
}

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ')
}

const formatDate = (date) => {
    if (!date) return 'N/A'
    if (typeof date === 'string') {
        const d = new Date(date)
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
    }
    return date
}

const quickCheckOut = (room) => {
    if (!room.reservation_id) {
        statusType.value = 'error'
        statusMessage.value = 'No active reservation found for this room. Use Manual Checkout instead.'
        return
    }
    selectedRoom.value = null
    router.get(route('admin.checkout'), { reservation_id: room.reservation_id })
}

const quickCheckIn = (room) => {
    if (!room.pending_reservation) {
        statusType.value = 'error'
        statusMessage.value = 'No pending reservation found for this room.'
        return
    }

    if (confirm(`Check in guest ${room.pending_reservation.guest_name} to room ${room.number}?`)) {
        const userRole = props.user?.roles?.[0]?.name || 'front_desk'
        let routeName = 'admin.checkin.store'

        // Get first available key card if any
        const availableKeyCard = props.availableKeyCards && props.availableKeyCards.length > 0
            ? props.availableKeyCards[0].id
            : null

        router.post(route(routeName), {
            reservation_id: room.pending_reservation.id,
            room_number: room.number,
            key_card_id: availableKeyCard,
        }, {
            onSuccess: () => {
                selectedRoom.value = null
                statusType.value = 'success'
                statusMessage.value = `Checked in ${room.pending_reservation.guest_name} successfully.`
                router.reload({ only: ['rooms', 'roomStatus', 'availableKeyCards'], preserveState: true })
            },
            onError: (errors) => {
                console.error('Check-in error:', errors)
                const errorMessage = errors?.message || errors?.reservation_id?.[0] || 'Failed to check in guest. Please try again.'
                statusType.value = 'error'
                statusMessage.value = errorMessage
            }
        })
    }
}

const manualCheckOut = (room) => {
    if (room.status !== 'occupied') return
    if (!confirm(`Manually check out room ${room.number}? This will mark the room as available and ready for cleaning. No reservation will be updated.`)) return
    router.post(route('admin.rooms.manual-checkout', room.id), {}, {
        onSuccess: () => {
            selectedRoom.value = null
            statusType.value = 'success'
            statusMessage.value = `Room ${room.number} checked out successfully.`
            router.reload({ only: ['rooms', 'roomStatus'], preserveState: true })
        },
        onError: (errors) => {
            console.error('Manual checkout error:', errors)
            statusType.value = 'error'
            statusMessage.value = errors?.message || 'Failed to manually check out room. Please try again.'
        }
    })
}

const markRoomClean = (room) => {
    const message = room.status === 'occupied'
        ? `Mark room ${room.number} as clean? The room will remain occupied.`
        : `Mark room ${room.number} as clean? The room will become available.`;
    if (!confirm(message)) return
    router.post(route('admin.rooms.mark-clean', room.id), {}, {
        onSuccess: () => {
            selectedRoom.value = null
            statusType.value = 'success'
            statusMessage.value = `Room ${room.number} marked clean.`
            router.reload({ only: ['rooms', 'roomStatus'], preserveState: true })
        },
        onError: (errors) => {
            console.error('Mark clean error:', errors)
            statusType.value = 'error'
            statusMessage.value = errors?.message || 'Failed to mark room as clean. Please try again.'
        }
    })
}

const markRoomDirty = (room) => {
    if (!confirm(`Mark room ${room.number} as dirty? Housekeeping will be assigned to clean it.`)) return
    router.post(route('admin.rooms.mark-dirty', room.id), {}, {
        onSuccess: () => {
            selectedRoom.value = null
            statusType.value = 'success'
            statusMessage.value = `Room ${room.number} marked as dirty.`
            router.reload({ only: ['rooms', 'roomStatus'], preserveState: true })
        },
        onError: (errors) => {
            console.error('Mark dirty error:', errors)
            statusType.value = 'error'
            statusMessage.value = errors?.message || 'Failed to mark room as dirty. Please try again.'
        }
    })
}

const serviceChargeForm = ref({ product_id: null, description: '', amount: null, quantity: 1 })
const isAddingServiceCharge = ref(false)
const customServiceOptionValue = 'custom'

const onServiceProductChange = () => {
    if (!serviceChargeForm.value.product_id) return
    if (serviceChargeForm.value.product_id === customServiceOptionValue) return
    const selectedProduct = (props.posProducts || []).find(product => Number(product.id) === Number(serviceChargeForm.value.product_id))
    if (!selectedProduct) return

    if (!serviceChargeForm.value.description) {
        serviceChargeForm.value.description = selectedProduct.name
    }

    if (!serviceChargeForm.value.amount || Number(serviceChargeForm.value.amount) <= 0) {
        serviceChargeForm.value.amount = Number(selectedProduct.price || 0)
    }
}

const addServiceCharge = () => {
    if (!serviceChargeForm.value.description || !serviceChargeForm.value.amount || serviceChargeForm.value.amount <= 0) return
    isAddingServiceCharge.value = true
    const selectedProductId = serviceChargeForm.value.product_id === customServiceOptionValue
        ? null
        : serviceChargeForm.value.product_id

    router.post(route('manager.checkout.service-charge'), {
        reservation_id: selectedRoom.value.reservation_id,
        product_id: selectedProductId,
        description: serviceChargeForm.value.description,
        amount: parseFloat(serviceChargeForm.value.amount),
        quantity: parseInt(serviceChargeForm.value.quantity || 1, 10),
    }, {
        onSuccess: () => {
            serviceChargeForm.value = { product_id: null, description: '', amount: null, quantity: 1 }
            isAddingServiceCharge.value = false
            statusType.value = 'success'
            statusMessage.value = 'Service charge added successfully.'
        },
        onError: () => { isAddingServiceCharge.value = false },
    })
}
</script>
