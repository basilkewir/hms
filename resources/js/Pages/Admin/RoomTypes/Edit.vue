<template>
    <DashboardLayout title="Edit Room Type">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Edit Room Type</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Modify the details of the {{ roomType.name }} room type.</p>
                </div>
                <KotelButton type="button" variant="secondary" @click="cancel">
                    Back to Room Types
                </KotelButton>
            </div>

            <form @submit.prevent="submitForm" class="space-y-6">
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Room Type Name *</label>
                            <KotelTextInput id="name" v-model="form.name" type="text" required class="w-full" />
                            <div v-if="form.errors.name" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label for="code" class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Room Code *</label>
                            <KotelTextInput id="code" v-model="form.code" type="text" required class="w-full" />
                            <div v-if="form.errors.code" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.code }}</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Description</h3>
                    <div>
                        <label for="description" class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Description</label>
                        <textarea id="description" v-model="form.description" rows="3"
                                  class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                  :style="{ 
                                      backgroundColor: themeColors.background,
                                      borderColor: themeColors.border,
                                      color: themeColors.textPrimary,
                                      borderWidth: '1px',
                                      borderStyle: 'solid'
                                  }"></textarea>
                        <div v-if="form.errors.description" class="mt-1 text-sm"
                             :style="{ color: themeColors.danger }">{{ form.errors.description }}</div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Capacity Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="max_occupancy" class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Max Occupancy *</label>
                            <KotelTextInput id="max_occupancy" v-model.number="form.max_occupancy" type="number" required class="w-full" />
                            <div v-if="form.errors.max_occupancy" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.max_occupancy }}</div>
                        </div>

                        <div>
                            <label for="max_adults" class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Max Adults *</label>
                            <KotelTextInput id="max_adults" v-model.number="form.max_adults" type="number" required class="w-full" />
                            <div v-if="form.errors.max_adults" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.max_adults }}</div>
                        </div>

                        <div>
                            <label for="max_children" class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Max Children</label>
                            <KotelTextInput id="max_children" v-model.number="form.max_children" type="number" class="w-full" />
                            <div v-if="form.errors.max_children" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.max_children }}</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Pricing Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="base_price" class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Base Price *</label>
                            <KotelTextInput id="base_price" v-model.number="form.base_price" type="number" step="0.01" required class="w-full" />
                            <div v-if="form.errors.base_price" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.base_price }}</div>
                        </div>

                        <div>
                            <label for="extra_adult_charge" class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Extra Adult Charge</label>
                            <KotelTextInput id="extra_adult_charge" v-model.number="form.extra_adult_charge" type="number" step="0.01" class="w-full" />
                            <div v-if="form.errors.extra_adult_charge" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.extra_adult_charge }}</div>
                        </div>

                        <div>
                            <label for="extra_child_charge" class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Extra Child Charge</label>
                            <KotelTextInput id="extra_child_charge" v-model.number="form.extra_child_charge" type="number" step="0.01" class="w-full" />
                            <div v-if="form.errors.extra_child_charge" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.extra_child_charge }}</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Room Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="room_size_sqft" class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Room Size (sqft)</label>
                            <KotelTextInput id="room_size_sqft" v-model.number="form.room_size_sqft" type="number" class="w-full" />
                            <div v-if="form.errors.room_size_sqft" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.room_size_sqft }}</div>
                        </div>

                        <div>
                            <label for="bed_type" class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Bed Type</label>
                            <KotelTextInput id="bed_type" v-model="form.bed_type" type="text" class="w-full" />
                            <div v-if="form.errors.bed_type" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.bed_type }}</div>
                        </div>

                        <div>
                            <label for="bed_count" class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Bed Count</label>
                            <KotelTextInput id="bed_count" v-model.number="form.bed_count" type="number" class="w-full" />
                            <div v-if="form.errors.bed_count" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.bed_count }}</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Features</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="view_type" class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">View Type</label>
                            <KotelTextInput id="view_type" v-model="form.view_type" type="text" class="w-full" />
                            <div v-if="form.errors.view_type" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.view_type }}</div>
                        </div>

                        <div>
                            <label for="iptv_package" class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">IPTV Package</label>
                            <KotelTextInput id="iptv_package" v-model="form.iptv_package" type="text" class="w-full" />
                            <div v-if="form.errors.iptv_package" class="mt-1 text-sm"
                                 :style="{ color: themeColors.danger }">{{ form.errors.iptv_package }}</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Amenities</h3>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Amenities</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <div v-for="amenity in amenities" :key="amenity.id" class="flex items-center">
                                <KotelCheckbox v-model:checked="form.amenities" :value="amenity.id" :id="'amenity-' + amenity.id" />
                                <label :for="'amenity-' + amenity.id" class="ml-2 text-sm"
                                       :style="{ color: themeColors.textSecondary }">{{ amenity.name }}</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Room Options</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-center">
                            <KotelCheckbox v-model:checked="form.has_balcony" id="has_balcony" />
                            <label for="has_balcony" class="ml-2 text-sm"
                                   :style="{ color: themeColors.textSecondary }">Has Balcony</label>
                        </div>

                        <div class="flex items-center">
                            <KotelCheckbox v-model:checked="form.has_living_room" id="has_living_room" />
                            <label for="has_living_room" class="ml-2 text-sm"
                                   :style="{ color: themeColors.textSecondary }">Has Living Room</label>
                        </div>

                        <div class="flex items-center">
                            <KotelCheckbox v-model:checked="form.is_active" id="is_active" />
                            <label for="is_active" class="ml-2 text-sm"
                                   :style="{ color: themeColors.textSecondary }">Active</label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 pt-6 border-t"
                     :style="{ 
                         borderTopColor: themeColors.border,
                         borderTopWidth: '1px',
                         borderTopStyle: 'solid'
                     }">
                    <KotelButton type="button" variant="secondary" @click="cancel">
                        Cancel
                    </KotelButton>
                    <KotelButton type="submit" variant="primary" :disabled="form.processing">
                        <span v-if="form.processing">Updating...</span>
                        <span v-else>Update Room Type</span>
                    </KotelButton>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import KotelTextInput from '@/Components/KotelTextInput.vue'
import KotelButton from '@/Components/KotelButton.vue'
import KotelCheckbox from '@/Components/KotelCheckbox.vue'
import { useTheme } from '@/Composables/useTheme.js'

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
    roomType: Object,
    amenities: Array,
})

const navigation = computed(() => getNavigationForRole('admin'))

const form = useForm({
    name: props.roomType.name,
    code: props.roomType.code,
    description: props.roomType.description,
    max_occupancy: props.roomType.max_occupancy,
    max_adults: props.roomType.max_adults,
    max_children: props.roomType.max_children,
    base_price: props.roomType.base_price,
    extra_adult_charge: props.roomType.extra_adult_charge,
    extra_child_charge: props.roomType.extra_child_charge,
    amenities: props.roomType.amenities || [],
    iptv_package: props.roomType.iptv_package,
    room_size_sqft: props.roomType.room_size_sqft,
    bed_type: props.roomType.bed_type,
    bed_count: props.roomType.bed_count,
    has_balcony: props.roomType.has_balcony,
    has_living_room: props.roomType.has_living_room,
    view_type: props.roomType.view_type,
    is_active: props.roomType.is_active,
})

const submitForm = () => {
    form.put(`/admin/room-types/${props.roomType.id}`, {
        onSuccess: () => {
            // Success will be handled by redirect
        },
        onError: () => {
            // Errors will be displayed by Inertia
        }
    })
}

const cancel = () => {
    router.get('/admin/room-types')
}
</script>
