<template>
    <DashboardLayout title="Create Key Card" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Create New Key Card</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Add a new electronic key card to the system.</p>
                </div>
                <Link :href="route('front-desk.key-cards.index')" 
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
                    Back to Key Cards
                </Link>
            </div>
        </div>

        <!-- Create Form -->
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
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Notes</label>
                        <textarea v-model="form.notes" 
                                  rows="3"
                                  class="w-full rounded-md px-3 py-2 focus:outline-none focus:ring-2 transition-colors"
                                  :style="{
                                      backgroundColor: themeColors.background,
                                      borderColor: themeColors.border,
                                      color: themeColors.textPrimary,
                                      borderWidth: '1px',
                                      borderStyle: 'solid'
                                  }"
                                  placeholder="Additional notes about this key card"></textarea>
                    </div>

                    <div class="flex items-center justify-end space-x-4 pt-6 border-t"
                         :style="{ 
                             borderColor: themeColors.border,
                             borderTopWidth: '1px'
                         }">
                        <Link :href="route('front-desk.key-cards.index')" 
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
                            <span v-if="form.processing">Creating...</span>
                            <span v-else>Create Key Card</span>
                        </button>
                    </div>
                </form>
            </div>
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
})

const navigation = computed(() => getNavigationForRole('front_desk'))

const form = useForm({
    card_number: '',
    card_type: 'standard',
    notes: '',
})

const submit = () => {
    form.post(route('front-desk.key-cards.store'))
}
</script>
