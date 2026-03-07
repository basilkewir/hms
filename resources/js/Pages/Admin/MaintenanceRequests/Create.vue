<template>
    <DashboardLayout title="Create Maintenance Request">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Create Maintenance Request</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Report a maintenance issue or request repair.</p>
                </div>
                <Link :href="route(`${routePrefix}.maintenance-requests.index`)"
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

            <form @submit.prevent="submit" class="space-y-6" enctype="multipart/form-data">
                <!-- Request Information -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Request Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Room</label>
                            <select v-model="form.room_id"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option :value="null">Not Room-Specific</option>
                                <option v-for="room in rooms" :key="room.id" :value="room.id">
                                    {{ room.room_number }}
                                </option>
                            </select>
                            <p class="text-xs mt-1"
                               :style="{ color: themeColors.textTertiary }">Select the room if the issue is room-specific</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Category *</label>
                            <select v-model="form.category" required
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
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
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Priority *</label>
                            <select v-model="form.priority" required
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="low">Low</option>
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                            <p class="text-xs mt-1"
                               :style="{ color: themeColors.textTertiary }">Select urgency level</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Department</label>
                            <select v-model="form.department_id"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option :value="null">Select Department</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                    {{ dept.name }}
                                </option>
                            </select>
                            <p class="text-xs mt-1"
                               :style="{ color: themeColors.textTertiary }">Assign to specific department if needed</p>
                        </div>
                    </div>
                </div>

                <!-- Request Details -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Request Details</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Title *</label>
                            <input v-model="form.title" type="text" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="Brief description of the issue">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Description *</label>
                            <textarea v-model="form.description" rows="4" required
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="{ 
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px',
                                          borderStyle: 'solid'
                                      }"
                                      placeholder="Detailed description of the maintenance issue..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Location Information -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Location Information</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Location</label>
                            <input v-model="form.location" type="text"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="e.g., Lobby, Restaurant, Pool Area">
                            <p class="text-xs mt-1"
                               :style="{ color: themeColors.textTertiary }">General area where the issue is located</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Location Details</label>
                            <textarea v-model="form.location_details" rows="2"
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="{ 
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px',
                                          borderStyle: 'solid'
                                      }"
                                      placeholder="Specific location details..."></textarea>
                            <p class="text-xs mt-1"
                               :style="{ color: themeColors.textTertiary }">More specific location information</p>
                        </div>
                    </div>
                </div>

                <!-- Photos -->
                <div>
                    <h3 class="text-lg font-medium mb-4"
                        :style="{ color: themeColors.textPrimary }">Photos</h3>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Upload Photos</label>
                        <input @change="handlePhotoUpload" type="file" multiple accept="image/*"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                        <p class="text-xs mt-1"
                           :style="{ color: themeColors.textTertiary }">You can select multiple photos to help document the issue</p>
                        <div v-if="form.photos && form.photos.length > 0" class="mt-3">
                            <div class="flex flex-wrap gap-2">
                                <div v-for="(photo, index) in form.photos" :key="index" 
                                     class="text-sm px-2 py-1 rounded-md"
                                     :style="{ 
                                         backgroundColor: themeColors.primary + '20',
                                         color: themeColors.primary
                                     }">
                                    {{ photo.name }}
                                    <button @click="removePhoto(index)" type="button"
                                            class="ml-2"
                                            :style="{ color: themeColors.danger }">
                                        ×
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t"
                     :style="{ borderColor: themeColors.border }">
                    <Link :href="route(`${routePrefix}.maintenance-requests.index`)"
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
                            class="px-6 py-2 rounded-md transition-colors text-sm font-medium"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                                color: 'white'
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <span v-if="form.processing">Creating...</span>
                        <span v-else>Create Request</span>
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
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
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
    rooms: Array,
    departments: Array,
    staff: Array,
    routePrefix: { type: String, default: 'admin' },
})

const form = useForm({
    room_id: null,
    title: '',
    description: '',
    category: 'other',
    priority: 'normal',
    location: '',
    location_details: '',
    department_id: null,
    photos: [],
})

const handlePhotoUpload = (event) => {
    const files = Array.from(event.target.files)
    form.photos = files
}

const removePhoto = (index) => {
    form.photos.splice(index, 1)
}

const submit = () => {
    form.post(route(`${props.routePrefix}.maintenance-requests.store`), {
        forceFormData: true,
    })
}
</script>
