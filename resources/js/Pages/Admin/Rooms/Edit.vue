<template>
    <DashboardLayout title="Edit Room">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Edit Room {{ room.room_number }}</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Update room information and settings.</p>
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

            <form @submit.prevent="updateRoom" class="space-y-6">
                <!-- Validation Errors -->
                <div v-if="Object.keys(form.errors).length"
                     class="rounded-md p-4 border"
                     :style="{ backgroundColor: 'rgba(239,68,68,0.1)', borderColor: themeColors.danger }">
                    <p class="text-sm font-semibold mb-1" :style="{ color: themeColors.danger }">Please fix the following errors:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li v-for="(msg, field) in form.errors" :key="field" class="text-sm" :style="{ color: themeColors.danger }">{{ msg }}</li>
                    </ul>
                </div>

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
                            <div v-if="form.errors.room_number" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.room_number }}</div>
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
                                    {{ roomType.name }} {{ roomType.code ? '(' + roomType.code + ')' : '' }}
                                </option>
                            </select>
                            <div v-if="form.errors.room_type_id" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.room_type_id }}</div>
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
                            <div v-if="form.errors.floor_id" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.floor_id }}</div>
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
                                <option :value="null">Select Building/Wing</option>
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
                                <option :value="null">Select Bed Type</option>
                                <option v-for="bedType in bedTypes" :key="bedType.id" :value="bedType.id">
                                    {{ bedType.name }} {{ bedType.code ? '(' + bedType.code + ')' : '' }}
                                </option>
                            </select>
                            <p v-if="!bedTypes || bedTypes.length === 0" class="text-sm mt-2"
                               :style="{ color: themeColors.textTertiary }">
                                No bed types available. 
                                <Link :href="route('admin.bed-types.create')" 
                                      :style="{ color: themeColors.primary }">Add bed types</Link>
                            </p>
                        </div>
                        <div class="flex items-center pt-6">
                            <input type="checkbox" id="is_active" v-model="form.is_active"
                                   class="h-4 w-4 rounded focus:outline-none"
                                   :style="{ accentColor: themeColors.primary }">
                            <label for="is_active" class="ml-2 block text-sm font-medium"
                                   :style="{ color: themeColors.textPrimary }">Room is Active</label>
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
                    <p v-if="amenities.length === 0" class="text-sm mt-2"
                       :style="{ color: themeColors.textTertiary }">No amenities available. Add amenities in Room Amenities management.</p>
                </div>

                <!-- IPTV Configuration -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">IPTV Configuration</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-center">
                            <input type="checkbox" v-model="form.iptv_active"
                                   class="h-4 w-4 rounded focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.primary
                                   }">
                            <label class="ml-2 block text-sm"
                                   :style="{ color: themeColors.textPrimary }">Enable IPTV</label>
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
                                   :style="{ color: themeColors.textSecondary }">Status *</label>
                            <select v-model="form.status" required
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="available">Available</option>
                                <option value="occupied">Occupied</option>
                                <option value="cleaning">Being Cleaned</option>
                                <option value="maintenance">Under Maintenance</option>
                                <option value="reserved">Reserved</option>
                                <option value="out_of_order">Out of Order</option>
                            </select>
                            <div v-if="form.errors.status" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.status }}</div>
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
                                <option value="inspected">Inspected</option>
                                <option value="maintenance_required">Maintenance Required</option>
                                <option value="waiting_for_check">Waiting for Check</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Special Features -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Additional Information</h3>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Special Features</label>
                            <textarea v-model="form.special_features" rows="3"
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="{ 
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px',
                                          borderStyle: 'solid'
                                      }"
                                      placeholder="e.g., Ocean view, Balcony, Jacuzzi"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Internal Notes</label>
                            <textarea v-model="form.notes" rows="3"
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="{ 
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px',
                                          borderStyle: 'solid'
                                      }"
                                      placeholder="Internal notes about this room"></textarea>
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
                    <Link :href="route('admin.rooms.index')"
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
                        <span v-if="form.processing">Updating...</span>
                        <span v-else>Update Room</span>
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
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
    room: Object,
    roomTypes: Array,
    amenities: Array,
    floors: {
        type: Array,
        default: () => []
    },
    buildingWings: {
        type: Array,
        default: () => []
    },
    bedTypes: {
        type: Array,
        default: () => []
    },
})

const navigation = computed(() => getNavigationForRole('admin'))

// Initialize amenities - features can be array of IDs or array of names
const initializeAmenities = () => {
    const features = props.room.features || []
    if (!Array.isArray(features) || features.length === 0) return []
    
    // If features contains IDs (numbers), use them directly
    // If features contains names (strings), find matching amenity IDs
    return features.map(feature => {
        if (typeof feature === 'number' || (typeof feature === 'string' && /^\d+$/.test(feature))) {
            // It's an ID (number or numeric string)
            return parseInt(feature)
        } else if (typeof feature === 'string') {
            // It's a name, find matching amenity ID
            const amenity = props.amenities.find(a => a.name === feature)
            return amenity ? amenity.id : null
        }
        return null
    }).filter(id => id !== null && !isNaN(id))
}

const form = useForm({
    room_number: props.room.room_number || '',
    room_type_id: props.room.room_type_id || '',
    floor_id: props.room.floor_id || null,
    building_wing_id: props.room.building_wing_id || null,
    bed_type_id: props.room.bed_type_id || null,
    status: props.room.status || 'available',
    housekeeping_status: props.room.housekeeping_status || 'clean',
    iptv_active: props.room.iptv_active ?? false,
    is_active: props.room.is_active ?? true,
    amenities: initializeAmenities(),
    special_features: props.room.special_features || '',
    notes: props.room.notes || '',
})

const updateRoom = () => {
    form.put(route('admin.rooms.update', props.room.id), {
        preserveScroll: true,
    })
}
</script>
