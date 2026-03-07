<template>
    <DashboardLayout title="Edit Key Card" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Edit Key Card</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Update key card information and settings.</p>
                </div>
                <Link :href="route('front-desk.key-cards.show', keyCard.id)" 
                      class="px-4 py-2 rounded-md transition-colors font-medium flex items-center"
                      :style="{ 
                          backgroundColor: themeColors.background,
                          color: themeColors.textPrimary,
                          borderColor: themeColors.border,
                          borderWidth: '1px',
                          borderStyle: 'solid'
                      }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                    <ArrowLeftIcon class="h-4 w-4 mr-2" />
                    Back to Details
                </Link>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="rounded-lg overflow-hidden shadow"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <!-- Form Header -->
            <div class="px-6 py-4 border-b"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderBottomWidth: '1px'
                 }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Key Card Information</h3>
            </div>
            
            <!-- Form Content -->
            <div class="p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Card Number *</label>
                        <input v-model="form.card_number" 
                               type="text" 
                               required
                               class="w-full rounded-md px-3 py-2 focus:outline-none focus:ring-2 transition-colors"
                               :style="{
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }"
                               placeholder="Enter card number or RFID code">
                        <p class="text-xs mt-1"
                           :style="{ color: themeColors.textTertiary }">Unique identifier for the key card</p>
                        <p v-if="form.errors.card_number" 
                           class="mt-1 text-sm"
                           :style="{ color: themeColors.danger }">{{ form.errors.card_number }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Card Type *</label>
                        <select v-model="form.card_type" 
                                required
                                class="w-full rounded-md px-3 py-2 focus:outline-none focus:ring-2 transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="standard">Standard</option>
                            <option value="master">Master</option>
                            <option value="staff">Staff</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                        <p class="text-xs mt-1"
                           :style="{ color: themeColors.textTertiary }">Type of key card access level</p>
                        <p v-if="form.errors.card_type" 
                           class="mt-1 text-sm"
                           :style="{ color: themeColors.danger }">{{ form.errors.card_type }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Status</label>
                        <select v-model="form.status" 
                                class="w-full rounded-md px-3 py-2 focus:outline-none focus:ring-2 transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="available">Available</option>
                            <option value="assigned">Assigned</option>
                            <option value="lost">Lost</option>
                            <option value="damaged">Damaged</option>
                            <option value="deactivated">Deactivated</option>
                        </select>
                        <p class="text-xs mt-1"
                           :style="{ color: themeColors.textTertiary }">Current status of the key card</p>
                        <p v-if="form.errors.status" 
                           class="mt-1 text-sm"
                           :style="{ color: themeColors.danger }">{{ form.errors.status }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Active Status</label>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   v-model="form.is_active"
                                   id="is_active"
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <label for="is_active" class="ml-2 text-sm"
                                   :style="{ color: themeColors.textPrimary }">
                                Key card is active and can be used
                            </label>
                        </div>
                        <p class="text-xs mt-1"
                           :style="{ color: themeColors.textTertiary }">Inactive cards cannot be assigned</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Notes</label>
                        <textarea v-model="form.notes" 
                                  rows="4"
                                  class="w-full rounded-md px-3 py-2 focus:outline-none focus:ring-2 transition-colors"
                                  :style="{
                                      backgroundColor: themeColors.background,
                                      borderColor: themeColors.border,
                                      color: themeColors.textPrimary,
                                      borderWidth: '1px',
                                      borderStyle: 'solid'
                                  }"
                                  placeholder="Additional notes about this key card"></textarea>
                        <p class="text-xs mt-1"
                           :style="{ color: themeColors.textTertiary }">Any additional information or comments</p>
                        <p v-if="form.errors.notes" 
                           class="mt-1 text-sm"
                           :style="{ color: themeColors.danger }">{{ form.errors.notes }}</p>
                    </div>

                    <!-- Current Assignment Warning -->
                    <div v-if="keyCard.status === 'assigned'" class="rounded-lg p-4 border"
                         :style="{ 
                             backgroundColor: 'rgba(251, 146, 60, 0.1)',
                             borderColor: themeColors.warning,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="flex items-start">
                            <ExclamationTriangleIcon class="w-5 h-5 mr-2 mt-0.5" :style="{ color: themeColors.warning }" />
                            <div>
                                <h4 class="font-semibold mb-1"
                                    :style="{ color: themeColors.warning }">Currently Assigned</h4>
                                <p class="text-sm"
                                   :style="{ color: themeColors.textSecondary }">
                                    This key card is currently assigned to {{ keyCard.guest?.name || 'a guest' }} in room {{ keyCard.room?.number || 'unknown' }}. 
                                    Changing the status may affect the current assignment.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-4 pt-6 border-t"
                         :style="{ 
                             borderColor: themeColors.border,
                             borderTopWidth: '1px'
                         }">
                        <Link :href="route('front-desk.key-cards.show', keyCard.id)" 
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
                        </Link>
                        <button type="submit" 
                                :disabled="form.processing"
                                class="px-6 py-2 rounded-md transition-colors font-medium text-white"
                                :style="{
                                    backgroundColor: form.processing ? themeColors.border : themeColors.primary,
                                    opacity: form.processing ? 0.7 : 1
                                }"
                                @mouseenter="!form.processing && ($event.target.style.backgroundColor = themeColors.hover)"
                                @mouseleave="!form.processing && ($event.target.style.backgroundColor = themeColors.primary)">
                            <span v-if="form.processing">Updating...</span>
                            <span v-else>Update Key Card</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="rounded-lg p-4 mt-6 border"
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
                       :style="{ color: themeColors.textSecondary }">Common key card operations</p>
                </div>
                <div class="flex gap-2">
                    <button v-if="keyCard.status === 'assigned'" 
                            @click="returnCard"
                            class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors text-white"
                            :style="{ backgroundColor: themeColors.success }">
                        Return Card
                    </button>
                    <button v-if="keyCard.status === 'assigned'" 
                            @click="markLost"
                            class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors text-white"
                            :style="{ backgroundColor: themeColors.warning }">
                        Mark Lost
                    </button>
                    <button v-if="keyCard.status === 'assigned'" 
                            @click="markDamaged"
                            class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors text-white"
                            :style="{ backgroundColor: themeColors.danger }">
                        Mark Damaged
                    </button>
                    <button @click="deactivateCard"
                            class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors text-white"
                            :style="{ backgroundColor: '#6b7280' }">
                        Deactivate
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
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
    keyCard: Object,
})

const navigation = computed(() => getNavigationForRole('front_desk'))

const form = useForm({
    card_number: props.keyCard.card_number,
    card_type: props.keyCard.card_type,
    status: props.keyCard.status,
    is_active: props.keyCard.is_active,
    notes: props.keyCard.notes || '',
})

const submit = () => {
    form.put(route('front-desk.key-cards.update', props.keyCard.id))
}

const returnCard = () => {
    if (confirm(`Return key card ${props.keyCard.card_number}?`)) {
        router.post(route('front-desk.key-cards.return', props.keyCard.id), {}, {
            onSuccess: () => router.reload()
        })
    }
}

const markLost = () => {
    if (confirm(`Mark key card ${props.keyCard.card_number} as lost?`)) {
        router.post(route('front-desk.key-cards.mark-lost', props.keyCard.id), {}, {
            onSuccess: () => router.reload()
        })
    }
}

const markDamaged = () => {
    if (confirm(`Mark key card ${props.keyCard.card_number} as damaged?`)) {
        router.post(route('front-desk.key-cards.mark-damaged', props.keyCard.id), {}, {
            onSuccess: () => router.reload()
        })
    }
}

const deactivateCard = () => {
    if (confirm(`Deactivate key card ${props.keyCard.card_number}? This action cannot be undone.`)) {
        router.post(route('front-desk.key-cards.deactivate', props.keyCard.id), {}, {
            onSuccess: () => router.reload()
        })
    }
}
</script>
