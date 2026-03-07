<template>
    <DashboardLayout title="Create New Room">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Create New Room</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Add a new room to the hotel inventory.</p>
                </div>
                <Link :href="route('admin.rooms.index')"
                      class="px-4 py-2 rounded-md transition-colors"
                      :style="{ 
                          backgroundColor: themeColors.secondary,
                          color: themeColors.textPrimary 
                      }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                    <ArrowLeftIcon class="h-4 w-4 mr-2 inline" />
                    Back to Rooms
                </Link>
            </div>

            <form @submit.prevent="createRoom" class="space-y-6">
                <!-- Basic Information -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Room Number *</label>
                            <input type="text" v-model="form.room_number" required
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
                                   :style="{ color: themeColors.textSecondary }">Room Type *</label>
                            <select v-model="form.room_type_id" required
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="">Select Room Type</option>
                                <option v-for="roomType in roomTypes" :key="roomType.id" :value="roomType.id">
                                    {{ roomType.name }} ({{ roomType.code }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Floor *</label>
                            <select v-model="form.floor_id" required
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="">Select Floor</option>
                                <option v-for="floor in floors" :key="floor.id" :value="floor.id">
                                    {{ floor.name || 'Floor ' + floor.floor_number }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Building/Wing</label>
                            <select v-model="form.building_wing_id"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="">Select Building/Wing</option>
                                <option v-for="wing in buildingWings" :key="wing.id" :value="wing.id">
                                    {{ wing.name }} {{ wing.code ? '(' + wing.code + ')' : '' }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Room Details -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Room Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Maximum Occupancy *</label>
                            <input type="number" v-model="form.max_occupancy" required min="1" max="10"
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
                                   :style="{ color: themeColors.textSecondary }">Number of Beds *</label>
                            <input type="number" v-model="form.bed_count" required min="1" max="5"
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
                                   :style="{ color: themeColors.textSecondary }">Bed Type</label>
                            <select v-model="form.bed_type_id"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="">Select Bed Type</option>
                                <option v-for="bedType in bedTypes" :key="bedType.id" :value="bedType.id">
                                    {{ bedType.name }} {{ bedType.code ? '(' + bedType.code + ')' : '' }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Room Size (sq ft)</label>
                            <input type="number" v-model="form.room_size"
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

                <!-- Amenities -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Amenities</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <div v-for="amenity in amenities" :key="amenity.id" class="flex items-center">
                            <input type="checkbox" :value="amenity.id" v-model="form.amenities"
                                   class="h-4 w-4 rounded focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.primary
                                   }">
                            <label class="ml-2 block text-sm"
                                   :style="{ color: themeColors.textPrimary }">{{ amenity.name }}</label>
                        </div>
                    </div>
                </div>

                <!-- IPTV Configuration -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">IPTV Configuration</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-center">
                            <input type="checkbox" v-model="form.has_iptv"
                                   class="h-4 w-4 rounded focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.primary
                                   }">
                            <label class="ml-2 block text-sm"
                                   :style="{ color: themeColors.textPrimary }">Enable IPTV</label>
                        </div>
                        <div v-if="form.has_iptv">
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">IPTV Package</label>
                            <select v-model="form.iptv_package"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="">Select Package</option>
                                <option value="basic">Basic Package</option>
                                <option value="premium">Premium Package</option>
                                <option value="vip">VIP Package</option>
                            </select>
                        </div>
                        <div v-if="form.has_iptv">
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Device MAC Address</label>
                            <input type="text" v-model="form.iptv_mac_address"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                        </div>
                        <div v-if="form.has_iptv">
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Device IP Address</label>
                            <input type="text" v-model="form.iptv_ip_address"
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

                <!-- Pricing -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Pricing</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Base Rate *</label>
                            <input type="number" step="0.01" v-model="form.base_rate" required
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
                                   :style="{ color: themeColors.textSecondary }">Weekend Rate</label>
                            <input type="number" step="0.01" v-model="form.weekend_rate"
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
                                   :style="{ color: themeColors.textSecondary }">Holiday Rate</label>
                            <input type="number" step="0.01" v-model="form.holiday_rate"
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

                <!-- Status -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Room Status</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Initial Status</label>
                            <select v-model="form.status"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="available">Available</option>
                                <option value="maintenance">Under Maintenance</option>
                                <option value="out_of_order">Out of Order</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Housekeeping Status</label>
                            <select v-model="form.housekeeping_status"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="clean">Clean</option>
                                <option value="dirty">Dirty</option>
                                <option value="cleaning">Being Cleaned</option>
                                <option value="inspected">Inspected</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t"
                     :style="{ 
                         borderTopColor: themeColors.border,
                         borderTopWidth: '1px',
                         borderTopStyle: 'solid'
                     }">
                    <button type="button" @click="resetForm"
                            class="px-6 py-2 rounded-md transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.secondary,
                                color: themeColors.textPrimary 
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        Reset Form
                    </button>
                    <button type="submit" :disabled="isSubmitting"
                            class="px-6 py-2 rounded-md transition-colors disabled:opacity-50"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                                color: 'white'
                            }"
                            @mouseenter="!isSubmitting && ($event.target.style.backgroundColor = themeColors.hover)"
                            @mouseleave="!isSubmitting && ($event.target.style.backgroundColor = themeColors.primary)">
                        <span v-if="isSubmitting">Creating...</span>
                        <span v-else>Create Room</span>
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme.js'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

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
    roomTypes: Array,
    floors: Array,
    buildingWings: Array,
    bedTypes: Array,
})

const navigation = computed(() => getNavigationForRole('admin'))
const isSubmitting = ref(false)

const amenities = [
    { id: 1, name: 'Air Conditioning' },
    { id: 2, name: 'WiFi' },
    { id: 3, name: 'TV' },
    { id: 4, name: 'Mini Bar' },
    { id: 5, name: 'Safe' },
    { id: 6, name: 'Balcony' },
    { id: 7, name: 'Ocean View' },
    { id: 8, name: 'City View' },
    { id: 9, name: 'Kitchenette' },
    { id: 10, name: 'Jacuzzi' },
    { id: 11, name: 'Work Desk' },
    { id: 12, name: 'Coffee Maker' },
]

const form = ref({
    room_number: '',
    room_type_id: '',
    floor_id: '',
    building_wing_id: '',
    max_occupancy: 2,
    bed_count: 1,
    bed_type_id: '',
    room_size: '',
    amenities: [],
    has_iptv: true,
    iptv_package: 'basic',
    iptv_mac_address: '',
    iptv_ip_address: '',
    base_rate: '',
    weekend_rate: '',
    holiday_rate: '',
    status: 'available',
    housekeeping_status: 'clean',
})

const createRoom = () => {
    isSubmitting.value = true
    // Simulate API call
    setTimeout(() => {
        alert('Room created successfully!')
        isSubmitting.value = false
        resetForm()
    }, 2000)
}

const resetForm = () => {
    form.value = {
        room_number: '',
        room_type_id: '',
        floor_id: '',
        building_wing_id: '',
        max_occupancy: 2,
        bed_count: 1,
        bed_type_id: '',
        room_size: '',
        amenities: [],
        has_iptv: true,
        iptv_package: 'basic',
        iptv_mac_address: '',
        iptv_ip_address: '',
        base_rate: '',
        weekend_rate: '',
        holiday_rate: '',
        status: 'available',
        housekeeping_status: 'clean',
    }
}
</script>
