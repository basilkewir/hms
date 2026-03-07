<template>
    <DashboardLayout title="Create Customer Group" :user="user">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Create Customer Group</h1>
                    <p class="text-sm mt-2"
                       :style="{ color: themeColors.textSecondary }">Add a new customer group to your system.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('accountant.customer-groups.index')" 
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ 
                              backgroundColor: themeColors.secondary,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        <ArrowPathIcon class="h-4 w-4 mr-2" />
                        Back to Groups
                    </Link>
                </div>
            </div>
        </div>

        <!-- Customer Group Form -->
        <div class="shadow rounded-lg overflow-hidden"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <div class="px-6 py-4 border-b"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderBottomWidth: '1px'
                 }">
                <h2 class="text-lg font-semibold"
                    :style="{ color: themeColors.textPrimary }">Group Information</h2>
                <p class="text-sm mt-1"
                   :style="{ color: themeColors.textSecondary }">Fill in the customer group details below.</p>
            </div>

            <form @submit.prevent="submitForm" class="p-6 space-y-6">
                <!-- Group Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">
                            Group Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="form.name"
                            class="w-full px-3 py-2 rounded-md focus:outline-none transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary,
                                borderStyle: 'solid',
                                borderWidth: '1px'
                            }"
                            placeholder="Enter group name"
                        />
                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">
                            Discount Percentage <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                v-model="form.discount_percentage"
                                min="0"
                                max="100"
                                step="0.01"
                                class="w-full px-3 py-2 rounded-md focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderStyle: 'solid',
                                    borderWidth: '1px'
                                }"
                                placeholder="0.00"
                            />
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3"
                                 :style="{ color: themeColors.textSecondary }">
                                %
                            </div>
                        </div>
                        <p v-if="errors.discount_percentage" class="mt-1 text-sm text-red-600">{{ errors.discount_percentage }}</p>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Description</label>
                    <textarea
                        v-model="form.description"
                        rows="4"
                        class="w-full px-3 py-2 rounded-md focus:outline-none transition-colors"
                        :style="{ 
                            backgroundColor: themeColors.background,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary,
                            borderStyle: 'solid',
                            borderWidth: '1px'
                        }"
                        placeholder="Enter a description for this customer group"
                    ></textarea>
                    <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Status</label>
                    <div class="flex items-center space-x-4">
                        <label class="inline-flex items-center">
                            <input
                                type="radio"
                                v-model="form.is_active"
                                :value="true"
                                class="form-radio h-4 w-4"
                                :style="{ color: themeColors.primary }"
                            />
                            <span class="ml-2"
                                  :style="{ color: themeColors.textPrimary }">Active</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input
                                type="radio"
                                v-model="form.is_active"
                                :value="false"
                                class="form-radio h-4 w-4"
                                :style="{ color: themeColors.primary }"
                            />
                            <span class="ml-2"
                                  :style="{ color: themeColors.textPrimary }">Inactive</span>
                        </label>
                    </div>
                    <p v-if="errors.is_active" class="mt-1 text-sm text-red-600">{{ errors.is_active }}</p>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-6 border-t"
                     :style="{ borderColor: themeColors.border, borderTopWidth: '1px' }">
                    <Link :href="route('accountant.customer-groups.index')"
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ 
                              backgroundColor: themeColors.secondary,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        <ArrowPathIcon class="h-4 w-4 mr-2" />
                        Cancel
                    </Link>

                    <button
                        type="submit"
                        :disabled="processing"
                        class="px-6 py-2 rounded-md transition-colors font-medium text-white flex items-center disabled:opacity-50 disabled:cursor-not-allowed"
                        :style="{ 
                            backgroundColor: themeColors.primary,
                        }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <span v-if="processing">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Creating...
                        </span>
                        <span v-else>Create Group</span>
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { ArrowPathIcon } from '@heroicons/vue/24/outline'

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
    errors: Object,
})

const processing = ref(false)
const form = ref({
    name: '',
    description: '',
    discount_percentage: 0,
    is_active: true,
})

const submitForm = () => {
    processing.value = true

    router.post(route('accountant.customer-groups.store'), form.value, {
        preserveScroll: true,
        onSuccess: () => {
            processing.value = false
        },
        onError: () => {
            processing.value = false
        }
    })
}
</script>
