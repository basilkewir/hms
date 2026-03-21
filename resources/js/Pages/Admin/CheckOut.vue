<template>
    <DashboardLayout title="Guest Check-Out" :user="user" :navigation="navigation">
        <!-- Check-Out Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Guest Check-Out</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Process guest departures and final billing.</p>
                </div>
            </div>
        </div>

        <!-- All Checked-In Guests -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-medium mb-1"
                        :style="{ color: themeColors.textPrimary }">All Checked-In Guests</h3>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Check out any guest below, including early checkouts (before scheduled date).</p>
                </div>
                <button @click="showManualSearch = !showManualSearch"
                        class="px-3 py-1 rounded-md text-sm font-medium transition-colors"
                        :style="{ 
                            backgroundColor: themeColors.background,
                            color: themeColors.primary,
                            borderColor: themeColors.border,
                            borderWidth: '1px',
                            borderStyle: 'solid'
                        }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                    {{ showManualSearch ? 'Hide' : 'Show' }} Search
                </button>
            </div>
            <div v-if="showManualSearch" class="mb-4">
                <input type="text" v-model="searchQuery"
                       placeholder="Filter by guest name, reservation number, or room (optional)"
                       class="w-full rounded-md px-4 py-2 focus:outline-none transition-colors"
                       :style="{
                           backgroundColor: themeColors.background,
                           borderColor: themeColors.border,
                           color: themeColors.textPrimary,
                           borderWidth: '1px',
                           borderStyle: 'solid'
                       }">
            </div>
            <div v-if="showManualSearch && filteredCheckedIn.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="reservation in filteredCheckedIn" :key="reservation.id"
                     class="rounded-lg p-4 cursor-pointer transition-colors border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }"
                     @click="selectGuest(reservation)"
                     @mouseenter="$event.target.style.borderColor = themeColors.primary"
                     @mouseleave="$event.target.style.borderColor = themeColors.border">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium"
                            :style="{ color: themeColors.textPrimary }">{{ reservation.guestName }}</h4>
                        <span class="text-xs px-2 py-1 rounded-full font-medium"
                              :class="getStatusBadgeClass('checked_in')">
                            Checked In
                        </span>
                    </div>
                    <div class="text-sm space-y-1">
                        <div>
                            <span class="font-medium"
                                  :style="{ color: themeColors.textSecondary }">Reservation:</span>
                            <span :style="{ color: themeColors.textPrimary }"> {{ reservation.reservation_number }}</span>
                        </div>
                        <div>
                            <span class="font-medium"
                                  :style="{ color: themeColors.textSecondary }">Room:</span>
                            <span :style="{ color: themeColors.textPrimary }"> {{ reservation.roomNumber }}</span>
                        </div>
                        <div>
                            <span class="font-medium"
                                  :style="{ color: themeColors.textSecondary }">Nights:</span>
                            <span :style="{ color: themeColors.textPrimary }"> {{ reservation.nights }}</span>
                        </div>
                        <div>
                            <span class="font-medium"
                                  :style="{ color: themeColors.textSecondary }">Check-out:</span>
                            <span :style="{ color: themeColors.textPrimary }"> {{ reservation.check_out_date }}</span>
                        </div>
                        <div>
                            <span class="font-medium"
                                  :style="{ color: themeColors.textSecondary }">Balance:</span>
                            <span class="font-medium"
                                  :style="{ 
                                      color: getBalanceColor(reservation.unifiedBalance || reservation.balanceAmount) 
                                  }">
                                {{ formatCurrency(parseFloat(reservation.unifiedBalance?.replace(/,/g, '') || reservation.balanceAmount?.replace(/,/g, '') || 0)) }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button @click.stop="startCheckOut(reservation)"
                                class="w-full px-3 py-2 rounded-md text-sm font-medium transition-colors text-white"
                                :style="{ 
                                    backgroundColor: themeColors.danger,
                                }"
                                @mouseenter="$event.target.style.backgroundColor = '#dc2626'"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.danger">
                            Check Out
                        </button>
                    </div>
                </div>
            </div>
            <div v-if="showManualSearch && filteredCheckedIn.length === 0" class="text-center py-8"
                 :style="{ color: themeColors.textTertiary }">
                {{ searchQuery ? 'No checked-in guests found matching your search.' : 'No checked-in guests.' }}
            </div>
        </div>

        <!-- Today's Departures -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <h3 class="text-lg font-medium mb-4"
                :style="{ color: themeColors.textPrimary }">Today's Expected Departures</h3>
            <div v-if="(todaysDepartures || []).length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="departure in (todaysDepartures || [])" :key="departure.id"
                     class="rounded-lg p-4 cursor-pointer transition-colors border"
                     :style="{ 
                         backgroundColor: themeColors.background,
                         borderColor: themeColors.border,
                         borderStyle: 'solid',
                         borderWidth: '1px'
                     }"
                     @click="selectGuest(departure)"
                     @mouseenter="$event.target.style.borderColor = themeColors.primary"
                     @mouseleave="$event.target.style.borderColor = themeColors.border">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium"
                            :style="{ color: themeColors.textPrimary }">{{ departure.guestName }}</h4>
                        <span class="text-xs px-2 py-1 rounded-full font-medium"
                              :class="getStatusBadgeClass(departure.status)">
                            {{ departure.status }}
                        </span>
                    </div>
                    <div class="text-sm space-y-1">
                        <div>
                            <span class="font-medium"
                                  :style="{ color: themeColors.textSecondary }">Room:</span>
                            <span :style="{ color: themeColors.textPrimary }"> {{ departure.roomNumber }}</span>
                        </div>
                        <div>
                            <span class="font-medium"
                                  :style="{ color: themeColors.textSecondary }">Nights:</span>
                            <span :style="{ color: themeColors.textPrimary }"> {{ departure.nights }}</span>
                        </div>
                        <div>
                            <span class="font-medium"
                                  :style="{ color: themeColors.textSecondary }">Total:</span>
                            <span :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(parseFloat(departure.unifiedTotal?.replace(/,/g, '') || departure.totalAmount?.replace(/,/g, '') || 0)) }}
                            </span>
                        </div>
                        <div>
                            <span class="font-medium"
                                  :style="{ color: themeColors.textSecondary }">Balance:</span>
                            <span class="font-medium"
                                  :style="{ 
                                      color: getBalanceColor(departure.unifiedBalance || departure.balanceAmount) 
                                  }">
                                {{ formatCurrency(parseFloat(departure.unifiedBalance?.replace(/,/g, '') || departure.balanceAmount?.replace(/,/g, '') || 0)) }}
                            </span>
                        </div>
                        <div>
                            <span class="font-medium"
                                  :style="{ color: themeColors.textSecondary }">Departure:</span>
                            <span :style="{ color: themeColors.textPrimary }"> {{ formatTime(departure.departureTime) }}</span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button @click.stop="startCheckOut(departure)"
                                class="w-full px-3 py-2 rounded-md text-sm font-medium transition-colors text-white"
                                :style="{ 
                                    backgroundColor: themeColors.primary,
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                            Start Check-Out
                        </button>
                    </div>
                </div>
            </div>
            <div v-else class="text-center py-8"
                 :style="{ color: themeColors.textTertiary }">
                No departures scheduled for today.
            </div>
        </div>

        <!-- Check-Out Form -->
        <div v-if="selectedGuest" class="shadow rounded-lg p-6"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Check-Out: {{ selectedGuest.guestName }}</h3>
                <button @click="selectedGuest = null"
                        :style="{ color: themeColors.textSecondary }"
                        @mouseenter="$event.target.style.color = themeColors.textPrimary"
                        @mouseleave="$event.target.style.color = themeColors.textSecondary">
                    <XMarkIcon class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="processCheckOut" class="space-y-6">
                <!-- Unified Final Bill -->
                <div>
                    <h4 class="text-md font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Unified Bill Summary</h4>
                    <div class="rounded-lg p-4 space-y-4 border"
                         :style="{ 
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <!-- Early Checkout Notice -->
                        <div v-if="selectedGuest.is_early_checkout" class="rounded-lg p-3 mb-4"
                             :style="{ 
                                 backgroundColor: 'rgba(250, 204, 21, 0.1)',
                                 borderColor: themeColors.warning,
                                 borderStyle: 'solid',
                                 borderWidth: '1px'
                             }">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 mr-2" :style="{ color: themeColors.warning }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium"
                                       :style="{ color: themeColors.warning }">Early Checkout Detected</p>
                                    <p class="text-xs mt-1"
                                       :style="{ color: themeColors.warning, opacity: 0.8 }">
                                        Guest stayed {{ selectedGuest.actual_nights }} night(s) instead of scheduled {{ selectedGuest.scheduled_nights }} night(s).
                                        Bill has been recalculated based on actual stay.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Room Charges -->
                        <div>
                            <h5 class="font-semibold text-gray-200 mb-2">Room Charges</h5>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm text-gray-300">
                                    <span>Room</span>
                                    <span>{{ selectedGuest.roomNumber }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-300">
                                    <span>Rate per night</span>
                                    <span>{{ formatCurrency(parseFloat(selectedGuest.room_rate?.replace(/,/g, '') || 0)) }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-300">
                                    <span>Nights stayed: {{ selectedGuest.actual_nights || selectedGuest.nights }}</span>
                                    <span v-if="selectedGuest.is_early_checkout" class="text-xs text-gray-400">
                                        (Scheduled: {{ selectedGuest.scheduled_nights }})
                                    </span>
                                </div>
                                <div class="flex justify-between font-semibold pt-2 border-t border-kotel-yellow/20 text-white">
                                    <span>Total Room Charges</span>
                                    <span>{{ formatCurrency(parseFloat(selectedGuest.roomCharges?.replace(/,/g, '') || 0)) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- POS Charges (Restaurant/Bar) -->
                        <div v-if="selectedGuest.posSales && selectedGuest.posSales.length > 0">
                            <h5 class="font-semibold text-gray-200 mb-2">Restaurant & Bar Charges</h5>

                            <!-- Unpaid Bills Warning -->
                            <div v-if="selectedGuest.hasUnpaidBills" class="bg-red-500/20 border border-red-400/50 rounded-lg p-3 mb-4">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-red-300 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-red-200">Unpaid Bills Detected</p>
                                        <p class="text-xs text-red-300/90 mt-1">
                                            {{ selectedGuest.unpaidBillsCount }} unpaid bill(s) totaling {{ formatCurrency(parseFloat(selectedGuest.unpaidBillsTotal?.replace(/,/g, '') || 0)) }}.
                                            All bills must be paid at checkout.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2 mb-2">
                                <div v-for="sale in selectedGuest.posSales" :key="sale.id"
                                     :class="['rounded border', sale.payment_status === 'pending' ? 'border-red-400/50 bg-red-500/10' : 'border-kotel-yellow/20 bg-kotel-gray']">
                                    <div class="flex justify-between items-center px-2 py-1.5 border-b border-kotel-yellow/10">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="text-[11px] font-medium text-gray-300">Sale #{{ sale.sale_number }}</span>
                                            <span v-if="sale.payment_status === 'pending'"
                                                  class="text-[11px] px-2 py-0.5 rounded-full bg-red-500/30 text-red-200 font-medium">
                                                Unpaid
                                            </span>
                                            <span v-else
                                                  class="text-[11px] px-2 py-0.5 rounded-full bg-green-500/30 text-green-200 font-medium">
                                                Paid
                                            </span>
                                        </div>
                                        <span class="text-[11px] text-gray-400 whitespace-nowrap">{{ sale.sale_date }}</span>
                                    </div>
                                    <div class="px-2 py-1.5 space-y-1">
                                        <div v-for="(item, idx) in sale.items" :key="idx" class="flex justify-between gap-2 text-[11px] text-gray-300">
                                            <span class="min-w-0 flex-1 truncate">{{ item.product_name }} x{{ item.quantity }}</span>
                                            <span class="tabular-nums whitespace-nowrap">{{ formatCurrency(item.total_price) }}</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between font-semibold text-[12px] px-2 py-1.5 border-t border-kotel-yellow/20 text-white">
                                        <span>Subtotal</span>
                                        <span class="tabular-nums">{{ formatCurrency(sale.total_amount) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-between font-medium pt-2 border-t border-kotel-yellow/20 text-gray-200">
                                <span>Total Restaurant/Bar Charges</span>
                                <span>{{ formatCurrency(parseFloat(selectedGuest.posCharges?.replace(/,/g, '') || 0)) }}</span>
                            </div>
                        </div>

                        <!-- Service Charges (Car Wash, Laundry, etc.) -->
                        <div v-if="selectedGuest.folio && selectedGuest.folio.charges && selectedGuest.folio.charges.filter(c => c.charge_code === 'SERVICE').length > 0">
                            <h5 class="font-semibold text-gray-200 mb-2">Service Charges</h5>
                            <div class="space-y-1 mb-2">
                                <div v-for="charge in selectedGuest.folio.charges.filter(c => c.charge_code === 'SERVICE')"
                                     :key="charge.id"
                                     class="flex justify-between text-sm bg-kotel-gray rounded p-2 border border-kotel-yellow/20">
                                    <div>
                                        <span class="text-gray-300">{{ charge.description }}</span>
                                        <span class="text-xs text-gray-400 ml-2">{{ charge.charge_date }} {{ charge.charge_time || '' }}</span>
                                    </div>
                                    <span class="text-gray-200">{{ formatCurrency(charge.net_amount) }}</span>
                                </div>
                            </div>
                            <div class="flex justify-between font-medium pt-2 border-t border-kotel-yellow/20 text-gray-200">
                                <span>Total Service Charges</span>
                                <span>{{ formatCurrency(parseFloat(selectedGuest.serviceCharges?.replace(/,/g, '') || 0)) }}</span>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="pt-4 border-t-2 border-kotel-yellow/30 space-y-2 text-gray-300">
                            <div class="flex justify-between">
                                <span>Room Charges</span>
                                <span>{{ formatCurrency(parseFloat(selectedGuest.roomCharges?.replace(/,/g, '') || 0)) }}</span>
                            </div>
                            <div v-if="parseFloat(selectedGuest.posCharges?.replace(/,/g, '') || 0) > 0" class="flex justify-between">
                                <span>Restaurant/Bar Charges</span>
                                <span>{{ formatCurrency(parseFloat(selectedGuest.posCharges?.replace(/,/g, '') || 0)) }}</span>
                            </div>
                            <div v-if="parseFloat(selectedGuest.serviceCharges?.replace(/,/g, '') || 0) > 0" class="flex justify-between">
                                <span>Service Charges</span>
                                <span>{{ formatCurrency(parseFloat(selectedGuest.serviceCharges?.replace(/,/g, '') || 0)) }}</span>
                            </div>
                            <div v-if="parseFloat(selectedGuest.taxAmount?.replace(/,/g, '') || 0) > 0" class="flex justify-between">
                                <span>Taxes</span>
                                <span>{{ formatCurrency(parseFloat(selectedGuest.taxAmount?.replace(/,/g, '') || 0)) }}</span>
                            </div>
                            <div v-if="parseFloat(selectedGuest.discountAmount?.replace(/,/g, '') || 0) > 0" class="flex justify-between text-green-300">
                                <span>Discount</span>
                                <span>-{{ formatCurrency(parseFloat(selectedGuest.discountAmount?.replace(/,/g, '') || 0)) }}</span>
                            </div>
                            <hr class="my-2 border-kotel-yellow/20">
                            <div class="flex justify-between font-bold text-lg text-white">
                                <span>Total Amount</span>
                                <span>{{ formatCurrency(parseFloat(selectedGuest.unifiedTotal?.replace(/,/g, '') || selectedGuest.totalAmount?.replace(/,/g, '') || 0)) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Paid Amount</span>
                                <span>{{ formatCurrency(parseFloat(selectedGuest.paidAmount?.replace(/,/g, '') || 0)) }}</span>
                            </div>
                            <div v-if="parseFloat(selectedGuest.unifiedBalance?.replace(/,/g, '') || selectedGuest.balanceAmount?.replace(/,/g, '') || 0) > 0"
                                 class="flex justify-between text-red-300 font-semibold mt-2 pt-2 border-t border-kotel-yellow/20">
                                <span>Balance Due</span>
                                <span>{{ formatCurrency(parseFloat(selectedGuest.unifiedBalance?.replace(/,/g, '') || selectedGuest.balanceAmount?.replace(/,/g, '') || 0)) }}</span>
                            </div>
                            <div v-else class="flex justify-between text-green-300 font-semibold mt-2 pt-2 border-t border-kotel-yellow/20">
                                <span>Payment Status</span>
                                <span>Fully Paid</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service Charges -->
                <div>
                    <h4 class="text-md font-medium text-kotel-yellow mb-3">Add Service Charge</h4>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div class="md:col-span-2">
                            <label class="block text-xs text-gray-300 mb-1">Description</label>
                            <input v-model="serviceChargeForm.description"
                                   type="text"
                                   class="w-full bg-kotel-black/50 border border-kotel-yellow/30 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-kotel-yellow focus:border-kotel-yellow"
                                   placeholder="e.g. Laundry service, Room service">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-300 mb-1">Amount</label>
                            <input v-model.number="serviceChargeForm.amount"
                                   type="number"
                                   min="0.01"
                                   step="0.01"
                                   class="w-full bg-kotel-black/50 border border-kotel-yellow/30 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-kotel-yellow focus:border-kotel-yellow"
                                   placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-300 mb-1">Quantity</label>
                            <input v-model.number="serviceChargeForm.quantity"
                                   type="number"
                                   min="1"
                                   step="1"
                                   class="w-full bg-kotel-black/50 border border-kotel-yellow/30 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-kotel-yellow focus:border-kotel-yellow">
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-end">
                        <button type="button"
                                @click="addServiceCharge"
                                :disabled="isAddingServiceCharge"
                                class="px-4 py-2 rounded-md text-sm font-medium text-white bg-kotel-yellow hover:bg-kotel-yellow/90 disabled:opacity-50 transition-colors">
                            <span v-if="isAddingServiceCharge">Adding...</span>
                            <span v-else>Add Charge</span>
                        </button>
                    </div>
                </div>

                <!-- Damages / Incidental Charges -->
                <div>
                    <h4 class="text-md font-medium text-red-400 mb-3 flex items-center justify-between">
                        <span>Damages / Incidentals</span>
                        <button type="button"
                                @click="addDamageRow"
                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md border border-kotel-yellow/40 text-kotel-yellow hover:bg-kotel-yellow/10">
                            + Add Damage
                        </button>
                    </h4>
                    <div v-if="checkOutForm.damages && checkOutForm.damages.length" class="space-y-2 mb-2">
                        <div v-for="(damage, index) in checkOutForm.damages" :key="index"
                             class="flex flex-col md:flex-row md:items-center gap-2 bg-kotel-black/50 border border-kotel-yellow/20 rounded-lg p-3">
                            <div class="flex-1">
                                <label class="block text-xs text-gray-300 mb-1">Description</label>
                                <input v-model="damage.description"
                                       type="text"
                                       class="w-full bg-kotel-black/50 border border-kotel-yellow/30 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-kotel-yellow focus:border-kotel-yellow"
                                       placeholder="e.g. Broken glass, damaged linen">
                            </div>
                            <div class="w-full md:w-40">
                                <label class="block text-xs text-gray-300 mb-1">Amount</label>
                                <input v-model.number="damage.amount"
                                       type="number"
                                       min="0"
                                       step="0.01"
                                       class="w-full bg-kotel-black/50 border border-kotel-yellow/30 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-kotel-yellow focus:border-kotel-yellow"
                                       placeholder="0.00">
                            </div>
                            <button type="button"
                                    @click="removeDamageRow(index)"
                                    class="self-start md:self-center text-xs text-red-300 hover:text-red-100">
                                Remove
                            </button>
                        </div>
                    </div>
                    <p v-if="damageTotal > 0" class="text-xs text-gray-300 mt-1">
                        Damage total to be added at checkout:
                        <span class="font-semibold text-red-300">{{ formatCurrency(damageTotal) }}</span>
                    </p>
                </div>

                <!-- Key Card Return -->
                <div v-if="selectedGuest.key_card">
                    <h4 class="text-md font-medium text-white mb-4">Key Card Return</h4>
                    <div class="bg-blue-500/10 border border-blue-400/30 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-white">Key Card: {{ selectedGuest.key_card.card_number }}</p>
                                <p class="text-sm text-gray-300">Type: {{ selectedGuest.key_card.card_type }}</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-200 mb-2">Key Card Status</label>
                        <select v-model="checkOutForm.keyCardStatus" required
                                class="w-full bg-kotel-black/50 border border-kotel-yellow/30 text-white rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-kotel-yellow focus:border-kotel-yellow">
                            <option value="returned">Returned</option>
                            <option value="lost">Lost</option>
                            <option value="damaged">Damaged</option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Select the status of the key card return</p>
                    </div>
                </div>

                <!-- Payment Settlement -->
                <div>
                    <h4 class="text-md font-medium text-white mb-4">Payment Settlement</h4>

                    <!-- Warning if unpaid bills exist -->
                    <div v-if="selectedGuest.hasUnpaidBills" class="bg-yellow-500/20 border border-yellow-400/50 rounded-lg p-3 mb-4">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-yellow-300 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <p class="text-sm text-yellow-200">
                                <strong>Full payment required:</strong> All restaurant/bar bills must be paid at checkout.
                                Partial payment is not allowed when there are unpaid bills.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Payment Status</label>
                            <select v-model="checkOutForm.paymentStatus" required
                                    :disabled="selectedGuest.hasUnpaidBills"
                                    :class="['w-full rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-kotel-yellow focus:border-kotel-yellow',
                                             selectedGuest.hasUnpaidBills ? 'bg-kotel-gray/50 border border-kotel-yellow/20 cursor-not-allowed text-gray-400' : 'bg-kotel-black/50 border border-kotel-yellow/30 text-white']">
                                <option value="paid">Fully Paid</option>
                                <option value="partial" :disabled="selectedGuest.hasUnpaidBills">Partially Paid</option>
                                <option value="pending" :disabled="selectedGuest.hasUnpaidBills">Payment Pending</option>
                            </select>
                            <p v-if="selectedGuest.hasUnpaidBills" class="text-xs text-gray-400 mt-1">
                                Full payment required due to unpaid restaurant/bar bills
                            </p>
                        </div>
                        <div v-if="checkOutForm.paymentStatus === 'partial' && !selectedGuest.hasUnpaidBills">
                            <label class="block text-sm font-medium text-gray-200 mb-2">Outstanding Amount</label>
                            <input type="number" step="0.01" v-model="checkOutForm.outstandingAmount"
                                   class="w-full bg-kotel-black/50 border border-kotel-yellow/30 text-white rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-kotel-yellow focus:border-kotel-yellow">
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-kotel-yellow/20">
                    <button type="button" @click="selectedGuest = null"
                            class="bg-kotel-gray border border-kotel-yellow/30 text-kotel-yellow px-6 py-2 rounded-md hover:bg-kotel-yellow/20 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" :disabled="isProcessing"
                            class="bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700 disabled:opacity-50 transition-colors">
                        <span v-if="isProcessing">Processing...</span>
                        <span v-else>Complete Check-Out</span>
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { formatCurrency } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme.js'
import { XMarkIcon } from '@heroicons/vue/24/outline'

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
    todaysDepartures: Array,
    allCheckedIn: Array,
    selectedReservationId: [Number, String],
})

const navigation = computed(() => getNavigationForRole('admin'))

const selectedGuest = ref(null)
const isProcessing = ref(false)
const showManualSearch = ref(true)
const searchQuery = ref('')

// Pre-select guest when arriving with ?reservation_id= (e.g. from Reservations Index)
const id = props.selectedReservationId != null ? Number(props.selectedReservationId) : null
if (id && props.allCheckedIn?.length) {
    const reservation = (props.todaysDepartures || []).find(r => r.id === id) ?? props.allCheckedIn.find(r => r.id === id)
    if (reservation) selectedGuest.value = reservation
}

const filteredCheckedIn = computed(() => {
    if (!props.allCheckedIn) return []
    if (!searchQuery.value?.trim()) return props.allCheckedIn
    const q = searchQuery.value.toLowerCase().trim()
    return props.allCheckedIn.filter(r =>
        r.guestName?.toLowerCase().includes(q) ||
        r.reservation_number?.toLowerCase().includes(q) ||
        r.roomNumber?.toLowerCase().includes(q)
    )
})


const checkOutForm = ref({
    paymentStatus: 'paid',
    outstandingAmount: 0,
    keyCardStatus: 'returned',
    damages: [],
})

const serviceChargeForm = ref({
    description: '',
    amount: null,
    quantity: 1,
})
const isAddingServiceCharge = ref(false)

const selectGuest = (guest) => {
    selectedGuest.value = guest
}

const startCheckOut = (guest) => {
    selectGuest(guest)
    const balance = parseFloat(guest.unifiedBalance?.replace(/,/g, '') || guest.balanceAmount?.replace(/,/g, '') || 0)
    const paidAmount = parseFloat(guest.paidAmount?.replace(/,/g, '') || 0)

    // If no payments have been made yet, force full payment
    if (paidAmount === 0) {
        checkOutForm.value.paymentStatus = 'paid'
        checkOutForm.value.outstandingAmount = 0
    } else {
        // If payments have been made, allow partial or full payment
        checkOutForm.value.paymentStatus = balance > 0 ? 'partial' : 'paid'
        checkOutForm.value.outstandingAmount = balance
    }

    // Reset damages when starting a new checkout
    checkOutForm.value.damages = []
    serviceChargeForm.value = {
        description: '',
        amount: null,
        quantity: 1,
    }
}

const addServiceCharge = () => {
    if (!selectedGuest.value?.id) return
    if (!serviceChargeForm.value.description || !serviceChargeForm.value.amount || serviceChargeForm.value.amount <= 0) return

    isAddingServiceCharge.value = true

    router.post(route('admin.checkout.service-charge'), {
        reservation_id: selectedGuest.value.id,
        description: serviceChargeForm.value.description,
        amount: parseFloat(serviceChargeForm.value.amount),
        quantity: parseInt(serviceChargeForm.value.quantity || 1, 10),
    }, {
        onSuccess: () => {
            const reservationId = selectedGuest.value.id
            serviceChargeForm.value = {
                description: '',
                amount: null,
                quantity: 1,
            }
            router.visit(route('admin.checkout', { reservation_id: reservationId }))
        },
        onFinish: () => {
            isAddingServiceCharge.value = false
        },
    })
}

const processCheckOut = () => {
    isProcessing.value = true

    router.post(route('admin.checkout.store'), {
        reservation_id: selectedGuest.value.id,
        payment_status: checkOutForm.value.paymentStatus,
        key_card_id: selectedGuest.value.key_card?.id || null,
        key_card_status: selectedGuest.value.key_card ? checkOutForm.value.keyCardStatus : null,
        damages: (checkOutForm.value.damages || [])
            .filter(d => d.description && d.amount && parseFloat(d.amount) > 0)
            .map(d => ({
                description: d.description,
                amount: parseFloat(d.amount),
            })),
    }, {
        onSuccess: () => {
            isProcessing.value = false
            const reservationId = selectedGuest.value?.id
            selectedGuest.value = null
            checkOutForm.value = {
                paymentStatus: 'paid',
                outstandingAmount: 0,
                keyCardStatus: 'returned',
                damages: [],
            }
            if (reservationId) {
                router.visit(route('admin.checkout.print', { reservation_id: reservationId }))
            }
        },
        onError: (errors) => {
            isProcessing.value = false
            console.error('Check-out error:', errors)
        }
    })
}

const damageTotal = computed(() => {
    if (!checkOutForm.value.damages || !checkOutForm.value.damages.length) return 0
    return checkOutForm.value.damages.reduce((sum, d) => {
        const amount = parseFloat(d.amount)
        return sum + (isNaN(amount) ? 0 : amount)
    }, 0)
})

const addDamageRow = () => {
    if (!checkOutForm.value.damages) {
        checkOutForm.value.damages = []
    }
    checkOutForm.value.damages.push({ description: '', amount: null })
}

const removeDamageRow = (index) => {
    if (!checkOutForm.value.damages) return
    checkOutForm.value.damages.splice(index, 1)
}

const formatTime = (time) => {
    if (!time) return 'N/A'
    if (typeof time === 'string' && time.includes('T')) {
        return new Date(time).toLocaleDateString()
    }
    return time
}

const getStatusBadgeClass = (status) => {
    const classes = {
        checked_in: 'bg-blue-100 text-blue-800',
        checked_out: 'bg-gray-100 text-gray-800',
        pending: 'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
        no_show: 'bg-red-100 text-red-800',
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const getBalanceColor = (balance) => {
    const balanceAmount = parseFloat(balance?.replace(/,/g, '') || 0)
    if (balanceAmount > 0) {
        return themeColors.value.danger
    } else if (balanceAmount < 0) {
        return themeColors.success
    } else {
        return themeColors.textPrimary
    }
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

/* Custom animations and transitions */
.transition-colors {
    transition-property: background-color, border-color, color;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Hover effects for interactive elements */
button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

button:active {
    transform: translateY(0);
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

/* Status badge improvements */
.rounded-full {
    border-radius: 9999px;
}

/* Card shadow improvements */
.shadow {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

.rounded-lg {
    border-radius: 0.5rem;
}

.rounded-md {
    border-radius: 0.375rem;
}

/* Grid utilities */
.grid {
    display: grid;
}

.grid-cols-1 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
}

.grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.grid-cols-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.gap-4 {
    gap: 1rem;
}

.gap-6 {
    gap: 1.5rem;
}

/* Flex utilities */
.flex {
    display: flex;
}

.items-center {
    align-items: center;
}

.justify-between {
    justify-content: space-between;
}

.flex-1 {
    flex: 1 1 0%;
}

/* Spacing utilities */
.p-2 {
    padding: 0.5rem;
}

.p-3 {
    padding: 0.75rem;
}

.p-4 {
    padding: 1rem;
}

.p-6 {
    padding: 1.5rem;
}

.px-3 {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
}

.px-4 {
    padding-left: 1rem;
    padding-right: 1rem;
}

.py-1 {
    padding-top: 0.25rem;
    padding-bottom: 0.25rem;
}

.py-2 {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
}

.py-3 {
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
}

.py-4 {
    padding-top: 1rem;
    padding-bottom: 1rem;
}

.mb-1 {
    margin-bottom: 0.25rem;
}

.mb-2 {
    margin-bottom: 0.5rem;
}

.mb-3 {
    margin-bottom: 0.75rem;
}

.mb-4 {
    margin-bottom: 1rem;
}

.mb-6 {
    margin-bottom: 1.5rem;
}

.mb-8 {
    margin-bottom: 2rem;
}

.mt-1 {
    margin-top: 0.25rem;
}

.mt-2 {
    margin-top: 0.5rem;
}

.mt-3 {
    margin-top: 0.75rem;
}

.mr-2 {
    margin-right: 0.5rem;
}

.ml-1 {
    margin-left: 0.25rem;
}

/* Text utilities */
.text-sm {
    font-size: 0.875rem;
    line-height: 1.25rem;
}

.text-xs {
    font-size: 0.75rem;
    line-height: 1rem;
}

.text-lg {
    font-size: 1.125rem;
    line-height: 1.75rem;
}

.text-2xl {
    font-size: 1.5rem;
    line-height: 2rem;
}

.font-medium {
    font-weight: 500;
}

.font-semibold {
    font-weight: 600;
}

.font-bold {
    font-weight: 700;
}

/* Width utilities */
.w-full {
    width: 100%;
}

.w-5 {
    width: 1.25rem;
}

.w-40 {
    width: 10rem;
}

.h-5 {
    height: 1.25rem;
}

/* Display utilities */
.block {
    display: block;
}

.inline-flex {
    display: inline-flex;
}

.cursor-pointer {
    cursor: pointer;
}

.cursor-not-allowed {
    cursor: not-allowed;
}

/* Border utilities */
.border {
    border-width: 1px;
}

.border-t {
    border-top-width: 1px;
}

.pt-2 {
    padding-top: 0.5rem;
}

.space-y-1 > :not([hidden]) ~ :not([hidden]) {
    margin-top: 0.25rem;
}

.space-y-2 > :not([hidden]) ~ :not([hidden]) {
    margin-top: 0.5rem;
}

.space-y-4 > :not([hidden]) ~ :not([hidden]) {
    margin-top: 1rem;
}

.space-y-6 > :not([hidden]) ~ :not([hidden]) {
    margin-top: 1.5rem;
}

.space-x-4 > :not([hidden]) ~ :not([hidden]) {
    margin-left: 1rem;
}

/* Status badge colors */
.bg-yellow-100 {
    background-color: rgb(254 249 195);
}

.text-yellow-800 {
    color: rgb(133 77 14);
}

.bg-blue-100 {
    background-color: rgb(219 234 254);
}

.text-blue-800 {
    color: rgb(30 64 175);
}

.bg-green-100 {
    background-color: rgb(220 252 231);
}

.text-green-800 {
    color: rgb(22 101 52);
}

.bg-gray-100 {
    background-color: rgb(243 244 246);
}

.text-gray-800 {
    color: rgb(31 41 55);
}

.bg-red-100 {
    background-color: rgb(254 226 226);
}

.text-red-800 {
    color: rgb(153 27 27);
}

/* Responsive utilities */
@media (min-width: 768px) {
    .md\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    
    .md\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    
    .md\:flex-row {
        flex-direction: row;
    }
    
    .md\:items-center {
        align-items: center;
    }
    
    .md\:self-center {
        align-self: center;
    }
    
    .md\:self-start {
        align-self: flex-start;
    }
}

@media (min-width: 1024px) {
    .lg\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
</style>
