<template>
    <DashboardLayout title="Create OTA Booking">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Create OTA Booking</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Create a new Online Travel Agency booking.</p>
                </div>
                <Link href="/admin/channel-manager" 
                      class="px-4 py-2 rounded-md transition-colors text-sm font-medium"
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
                <!-- Booking Information -->
                <div class="shadow rounded-lg p-6"
                     :style="{ 
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border 
                     }">
                    <h2 class="text-xl font-bold mb-4 pb-2 border-b"
                        :style="{ 
                            color: themeColors.textPrimary,
                            borderColor: themeColors.border 
                        }">
                        <CalendarIcon class="h-5 w-5 mr-2 inline" />
                        Booking Information
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                <option value="">Select Booking Source</option>
                                <option value="booking_com">Booking.com</option>
                                <option value="expedia">Expedia</option>
                                <option value="agoda">Agoda</option>
                                <option value="travel_agent">Travel Agent</option>
                                <option value="corporate">Corporate</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Booking Reference *</label>
                            <input v-model="form.booking_reference" type="text" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="e.g., BK-123456">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Check-in Date *</label>
                            <div class="relative">
                                <input v-model="form.check_in_date" 
                                       id="checkin-date"
                                       type="date" 
                                       required
                                       class="w-full rounded-md px-3 py-2 pr-10 focus:outline-none focus:ring-2 transition-colors"
                                       :style="{ 
                                           backgroundColor: themeColors.background,
                                           borderColor: themeColors.border,
                                           color: themeColors.textPrimary,
                                           borderWidth: '1px',
                                           borderStyle: 'solid'
                                       }"
                                       @click="$event.target.showPicker?.()"
                                       @change="updatePricing"
                                       :min="new Date().toISOString().split('T')[0]">
                                <CalendarIcon class="absolute right-3 top-2.5 h-5 w-5 cursor-pointer"
                                            :style="{ color: themeColors.textSecondary }" 
                                            @click="openDatePicker('checkin-date')" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Check-out Date *</label>
                            <div class="relative">
                                <input v-model="form.check_out_date" 
                                       id="checkout-date"
                                       type="date" 
                                       required
                                       class="w-full rounded-md px-3 py-2 pr-10 focus:outline-none focus:ring-2 transition-colors"
                                       :style="{ 
                                           backgroundColor: themeColors.background,
                                           borderColor: themeColors.border,
                                           color: themeColors.textPrimary,
                                           borderWidth: '1px',
                                           borderStyle: 'solid'
                                       }"
                                       @click="$event.target.showPicker?.()"
                                       @change="updatePricing"
                                       :min="form.check_in_date || new Date().toISOString().split('T')[0]">
                                <CalendarIcon class="absolute right-3 top-2.5 h-5 w-5 cursor-pointer"
                                            :style="{ color: themeColors.textSecondary }" 
                                            @click="openDatePicker('checkout-date')" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guest Information -->
                <div class="shadow rounded-lg p-6"
                     :style="{ 
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border 
                     }">
                    <h2 class="text-xl font-bold mb-4 pb-2 border-b"
                        :style="{ 
                            color: themeColors.textPrimary,
                            borderColor: themeColors.border 
                        }">
                        <UserIcon class="h-5 w-5 mr-2 inline" />
                        Guest Information
                    </h2>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Select Existing Guest</label>
                        <select v-model="form.guest_id" @change="selectGuest"
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option :value="null">-- Select Guest or fill manually --</option>
                            <option v-for="guest in guests" :key="guest.id" :value="guest.id">
                                {{ guest.first_name }} {{ guest.last_name }}{{ guest.email ? ' (' + guest.email + ')' : '' }}
                            </option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">First Name *</label>
                            <input v-model="form.guest_first_name" type="text" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Last Name *</label>
                            <input v-model="form.guest_last_name" type="text" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Email</label>
                            <input v-model="form.guest_email" type="email"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Phone</label>
                            <input v-model="form.guest_phone" type="tel"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                        </div>
                    </div>
                </div>

                <!-- Room Information -->
                <div class="shadow rounded-lg p-6"
                     :style="{ 
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border 
                     }">
                    <h2 class="text-xl font-bold mb-4 pb-2 border-b"
                        :style="{ 
                            color: themeColors.textPrimary,
                            borderColor: themeColors.border 
                        }">
                        <HomeIcon class="h-5 w-5 mr-2 inline" />
                        Room Information
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Room Type *</label>
                            <select v-model="form.room_type_id" 
                                    required
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors cursor-pointer"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }"
                                    @change="updatePricing">
                                <option value="">Select Room Type</option>
                                <option v-for="roomType in roomTypes" :key="roomType.id" :value="roomType.id">
                                    {{ roomType.name }} - {{ roomType.base_price?.toLocaleString() || 0 }} FCFA/night
                                </option>
                            </select>
                            <div v-if="selectedRoomType" class="mt-2 text-sm"
                                 :style="{ color: themeColors.textSecondary }">
                                Available Rooms: {{ availableRooms }} | Base Price: {{ selectedRoomType.base_price?.toLocaleString() || 0 }} FCFA/night
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Number of Rooms *</label>
                            <input v-model.number="form.number_of_rooms" type="number" required min="1"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Number of Adults *</label>
                            <input v-model.number="form.number_of_adults" type="number" required min="1"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Number of Children</label>
                            <input v-model.number="form.number_of_children" type="number" min="0"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                        </div>
                    </div>
                </div>

                <!-- Pricing Information -->
                <div class="shadow rounded-lg p-6"
                     :style="{ 
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border 
                     }">
                    <h2 class="text-xl font-bold mb-4 pb-2 border-b"
                        :style="{ 
                            color: themeColors.textPrimary,
                            borderColor: themeColors.border 
                        }">
                        <CurrencyDollarIcon class="h-5 w-5 mr-2 inline" />
                        Pricing Information
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Room Rate *</label>
                            <input v-model.number="form.room_rate" 
                                   type="number" 
                                   required 
                                   min="0" 
                                   step="0.01"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors cursor-pointer"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   @input="updatePricing">
                            <div v-if="selectedRoomType" class="mt-1 text-xs"
                                 :style="{ color: themeColors.textSecondary }">
                                Base rate: {{ selectedRoomType.base_price?.toLocaleString() || 0 }} FCFA
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Commission Rate (%)</label>
                            <input v-model.number="form.commission_rate" type="number" min="0" max="100" step="0.1"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Total Amount</label>
                            <input :value="calculatedTotalAmount" type="text" readonly
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Commission Amount</label>
                            <input :value="calculatedCommissionAmount" type="text" readonly
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                        </div>
                    </div>
                    
                    <!-- Pricing Summary -->
                    <div class="mt-6 p-4 rounded-lg border"
                         :style="{ 
                             backgroundColor: themeColors.background,
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <h3 class="text-lg font-semibold mb-3"
                            :style="{ color: themeColors.textPrimary }">Booking Summary</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span :style="{ color: themeColors.textSecondary }">Nights:</span>
                                <span class="font-medium ml-2"
                                      :style="{ color: themeColors.textPrimary }">{{ calculateNights() }}</span>
                            </div>
                            <div>
                                <span :style="{ color: themeColors.textSecondary }">Rooms:</span>
                                <span class="font-medium ml-2"
                                      :style="{ color: themeColors.textPrimary }">{{ form.number_of_rooms }}</span>
                            </div>
                            <div>
                                <span :style="{ color: themeColors.textSecondary }">Rate/Room:</span>
                                <span class="font-medium ml-2"
                                      :style="{ color: themeColors.textPrimary }">{{ form.room_rate?.toLocaleString() || 0 }} FCFA</span>
                            </div>
                            <div>
                                <span :style="{ color: themeColors.textSecondary }">Total:</span>
                                <span class="font-bold ml-2 text-lg"
                                      :style="{ color: themeColors.primary }">{{ calculatedTotalAmount }} FCFA</span>
                            </div>
                        </div>
                        <div v-if="form.commission_rate > 0" class="mt-3 pt-3 border-t"
                             :style="{ borderColor: themeColors.border }">
                            <div class="flex justify-between text-sm">
                                <span :style="{ color: themeColors.textSecondary }">Commission ({{ form.commission_rate }}%):</span>
                                <span class="font-medium"
                                      :style="{ color: themeColors.warning }">{{ calculatedCommissionAmount }} FCFA</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Special Requests -->
                <div class="shadow rounded-lg p-6"
                     :style="{ 
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border 
                     }">
                    <h2 class="text-xl font-bold mb-4 pb-2 border-b"
                        :style="{ 
                            color: themeColors.textPrimary,
                            borderColor: themeColors.border 
                        }">
                        <DocumentTextIcon class="h-5 w-5 mr-2 inline" />
                        Special Requests
                    </h2>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Special Requests</label>
                        <textarea v-model="form.special_requests" rows="4"
                                  class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                  :style="{ 
                                      backgroundColor: themeColors.background,
                                      borderColor: themeColors.border,
                                      color: themeColors.textPrimary,
                                      borderWidth: '1px',
                                      borderStyle: 'solid'
                                  }"
                                  placeholder="Any special requests or notes..."></textarea>
                    </div>
                </div>
                <!-- Form Actions -->
                <div class="shadow rounded-lg p-6"
                     :style="{ 
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border 
                     }">
                    <div class="flex items-center justify-end space-x-3">
                        <Link href="/admin/channel-manager" 
                              class="px-6 py-2 rounded-md transition-colors"
                              :style="{ 
                                  backgroundColor: themeColors.secondary,
                                  color: themeColors.textPrimary 
                              }"
                              @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                              @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing"
                                class="px-6 py-2 rounded-md transition-colors disabled:opacity-50"
                                :style="{ 
                                    backgroundColor: themeColors.primary,
                                    color: 'white' 
                                }"
                                @mouseenter="!form.processing && ($event.target.style.backgroundColor = themeColors.hover)"
                                @mouseleave="!form.processing && ($event.target.style.backgroundColor = themeColors.primary)">
                            <span v-if="form.processing">Creating...</span>
                            <span v-else>Create Booking</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </DashboardLayout>
 </template>
<script setup>
import { computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { ArrowLeftIcon, CalendarIcon, UserIcon, HomeIcon, CurrencyDollarIcon, DocumentTextIcon } from '@heroicons/vue/24/outline'

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
    roomTypes: Array,
    commission_rates: Object,
    guests: Array
})

const form = useForm({
    booking_source: '',
    booking_reference: '',
    check_in_date: '',
    check_out_date: '',
    guest_id: null,
    guest_first_name: '',
    guest_last_name: '',
    guest_email: '',
    guest_phone: '',
    room_type_id: null,
    number_of_rooms: 1,
    number_of_adults: 1,
    number_of_children: 0,
    room_rate: 0,
    commission_rate: 0,
    special_requests: '',
})

const calculatedTotalAmount = computed(() => {
    const nights = calculateNights()
    return (form.room_rate * form.number_of_rooms * nights).toFixed(2)
})

const calculatedCommissionAmount = computed(() => {
    const total = parseFloat(calculatedTotalAmount.value)
    return (total * (form.commission_rate / 100)).toFixed(2)
})

const calculateNights = () => {
    if (!form.check_in_date || !form.check_out_date) return 0
    const checkIn = new Date(form.check_in_date)
    const checkOut = new Date(form.check_out_date)
    return Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24))
}

const selectedRoomType = computed(() => {
    return props.roomTypes.find(rt => rt.id === form.room_type_id)
})

const availableRooms = computed(() => {
    if (!selectedRoomType.value) return 0
    // This would normally come from an API call to check real availability
    // For now, we'll simulate it
    return Math.floor(Math.random() * 5) + 1
})

const selectGuest = () => {
    const guest = props.guests.find(g => g.id === form.guest_id)
    if (guest) {
        form.guest_first_name = guest.first_name
        form.guest_last_name = guest.last_name
        form.guest_email = guest.email || ''
        form.guest_phone = guest.phone || ''
    }
}

const updatePricing = () => {
    if (selectedRoomType.value) {
        form.room_rate = selectedRoomType.value.base_price || 0
        form.commission_rate = props.commission_rates[form.booking_source] || 0
    }
}

const openDatePicker = (inputId) => {
    const input = document.getElementById(inputId)
    if (input && input.showPicker) {
        input.showPicker()
    }
}

const submit = () => {
    form.post('/admin/channel-manager')
}
</script>
