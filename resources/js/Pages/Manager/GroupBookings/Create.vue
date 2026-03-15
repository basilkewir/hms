<template>
    <DashboardLayout title="Create Group Booking">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Create Group Booking</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Create a new group reservation with consolidated billing.</p>
                </div>
                <Link href="/admin/group-bookings" 
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
                <div v-if="Object.keys(form.errors).length" class="p-4 rounded-lg border"
                     :style="{ 
                         backgroundColor: 'rgba(239, 68, 68, 0.1)',
                         borderColor: themeColors.danger,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <p class="text-sm font-medium" :style="{ color: themeColors.danger }">Please fix the errors below and try again.</p>
                </div>

                <!-- Group Information -->
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
                        <UserGroupIcon class="h-5 w-5 mr-2 inline" />
                        Group Information
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Group Name *</label>
                            <input v-model="form.group_name" type="text" required
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                   placeholder="e.g., ABC Corp Conference">
                            <p v-if="form.errors.group_name" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.group_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Primary Guest *</label>
                            <select v-model="form.primary_guest_id" required
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="">Select Primary Guest</option>
                                <option v-for="guest in guestList" :key="guest.id" :value="guest.id">
                                    {{ guest.first_name }} {{ guest.last_name }} ({{ guest.email }})
                                </option>
                            </select>
                            <p class="text-xs mt-1"
                               :style="{ color: themeColors.textTertiary }">
                                Or
                                <Link :href="route('admin.guests.create')" :style="{ color: themeColors.primary }">create new guest</Link>
                            </p>
                            <p v-if="form.errors.primary_guest_id" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.primary_guest_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Contact Person</label>
                            <select v-model="form.contact_person_id"
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option :value="null">Same as Primary Guest</option>
                                <option v-for="guest in guestList" :key="guest.id" :value="guest.id">
                                    {{ guest.first_name }} {{ guest.last_name }} ({{ guest.email }})
                                </option>
                            </select>
                            <p class="text-xs mt-1"
                               :style="{ color: themeColors.textTertiary }">
                                If the contact isn't listed,
                                <Link :href="route('admin.guests.create')" :style="{ color: themeColors.primary }">create a guest</Link>
                                and then select them here.
                            </p>
                            <p v-if="form.errors.contact_person_id" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.contact_person_id }}</p>
                        </div>
                    </div>
                </div>

                <!-- Booking Dates -->
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
                        <ClockIcon class="h-5 w-5 mr-2 inline" />
                        Booking Details
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Check-in Date *</label>
                            <DatePicker v-model="form.check_in_date" required
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                      placeholder="Select check-in date" />
                            <p v-if="form.errors.check_in_date" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.check_in_date }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Check-out Date *</label>
                            <DatePicker v-model="form.check_out_date" required
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }"
                                      placeholder="Select check-out date" />
                            <p v-if="form.errors.check_out_date" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.check_out_date }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Total Rooms *</label>
                            <input v-model.number="form.total_rooms" type="number" required min="1"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                            <p v-if="form.errors.total_rooms" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.total_rooms }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Total Adults *</label>
                            <input v-model.number="form.total_adults" type="number" required min="1"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                            <p v-if="form.errors.total_adults" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.total_adults }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Total Children</label>
                            <input v-model.number="form.total_children" type="number" min="0"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                            <p v-if="form.errors.total_children" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.total_children }}</p>
                        </div>
                    </div>
                </div>

                <!-- Billing & Discount -->
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
                        Billing & Discount
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Billing Type *</label>
                            <select v-model="form.billing_type" required
                                    class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderWidth: '1px',
                                        borderStyle: 'solid'
                                    }">
                                <option value="consolidated">Consolidated (Single Bill)</option>
                                <option value="individual">Individual (Separate Bills)</option>
                                <option value="split">Split (Custom Split)</option>
                            </select>
                            <p v-if="form.errors.billing_type" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.billing_type }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Group Discount (%)</label>
                            <input v-model.number="form.group_discount_percentage" type="number" min="0" max="100"
                                   class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                   :style="{ 
                                       backgroundColor: themeColors.background,
                                       borderColor: themeColors.border,
                                       color: themeColors.textPrimary,
                                       borderWidth: '1px',
                                       borderStyle: 'solid'
                                   }">
                            <p v-if="form.errors.group_discount_percentage" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.group_discount_percentage }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Billing Instructions</label>
                            <textarea v-model="form.billing_instructions" rows="3"
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="{ 
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px',
                                          borderStyle: 'solid'
                                      }"
                                      placeholder="Special billing instructions..."></textarea>
                            <p v-if="form.errors.billing_instructions" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.billing_instructions }}</p>
                        </div>
                    </div>
                </div>

                <!-- Facilities & Packages -->
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
                        <GiftIcon class="h-5 w-5 mr-2 inline" />
                        Facilities & Packages
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-3"
                                   :style="{ color: themeColors.textSecondary }">Select Halls (Optional)</label>
                            <div class="space-y-2 max-h-48 overflow-y-auto p-2 rounded border"
                                 :style="{ borderColor: themeColors.border }">
                                <div v-for="hall in hallList" :key="hall.id" class="flex items-center">
                                    <input type="checkbox" :id="'hall-'+hall.id" :value="hall.id" v-model="form.hall_ids"
                                           class="rounded focus:ring-opacity-50"
                                           :style="{ 
                                               backgroundColor: themeColors.background,
                                               borderColor: themeColors.border
                                           }">
                                    <label :for="'hall-'+hall.id" class="ml-2 text-sm cursor-pointer" :style="{ color: themeColors.textPrimary }">
                                        {{ hall.name }} (Capacity: {{ hall.capacity }})
                                    </label>
                                </div>
                                <p v-if="hallList.length === 0" class="text-sm italic" :style="{ color: themeColors.textTertiary }">No halls available</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-3"
                                   :style="{ color: themeColors.textSecondary }">Select Packages (Optional)</label>
                            <div class="space-y-2 max-h-48 overflow-y-auto p-2 rounded border"
                                 :style="{ borderColor: themeColors.border }">
                                <div v-for="pkg in packageList" :key="pkg.id" class="flex items-center">
                                    <input type="checkbox" :id="'pkg-'+pkg.id" :value="pkg.id" v-model="form.package_ids"
                                           class="rounded focus:ring-opacity-50"
                                           :style="{ 
                                               backgroundColor: themeColors.background,
                                               borderColor: themeColors.border
                                           }">
                                    <label :for="'pkg-'+pkg.id" class="ml-2 text-sm cursor-pointer" :style="{ color: themeColors.textPrimary }">
                                        {{ pkg.name }} ({{ pkg.type }})
                                    </label>
                                </div>
                                <p v-if="packageList.length === 0" class="text-sm italic" :style="{ color: themeColors.textTertiary }">No packages available</p>
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
                        Special Requests & Notes
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Special Requests</label>
                            <textarea v-model="form.special_requests" rows="3"
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="{ 
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px',
                                          borderStyle: 'solid'
                                      }"></textarea>
                            <p v-if="form.errors.special_requests" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.special_requests }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Group Notes</label>
                            <textarea v-model="form.group_notes" row, GiftIcons="3"
                                      class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                      :style="{ 
                                          backgroundColor: themeColors.background,
                                          borderColor: themeColors.border,
                                          color: themeColors.textPrimary,
                                          borderWidth: '1px',
                                          borderStyle: 'solid'
                                      }"></textarea>
                            <p v-if="form.errors.group_notes" class="text-xs mt-1" :style="{ color: themeColors.danger }">{{ form.errors.group_notes }}</p>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="shadow rounded-lg p-6"
                     :style="{ 
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border 
                     }">
                    <div class="flex items-center justify-end space-x-3">
                        <Link href="/admin/group-bookings" 
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
                            <span v-else>Create Group Booking</span>
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
import DatePicker from '@/Components/DatePicker.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { ArrowLeftIcon, UserGroupIcon, ClockIcon, CurrencyDollarIcon } from '@heroicons/vue/24/outline'

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
    guests: Array,
    roomTypes: Array,
    halls: Array,
    packages: Array,
})

const guestList = computed(() => props?.guests || [])
const hallList = computed(() => props?.halls || [])
const packageList = computed(() => props?.packages || [])

const navigation = computed(() => getNavigationForRole('admin'))

const form = useForm({
    group_name: '',
    primary_guest_id: '',
    contact_person_id: null,
    check_in_date: '',
    check_out_date: '',
    total_rooms: 1,
    total_adults: 1,
    total_children: 0,
    group_discount_percentage: 0,
    billing_type: 'consolidated',
    billing_instructions: '',
    special_requests: '',
    group_notes: '',
    hall_ids: [],
    package_ids: [],
})

const submit = () => {
    form.post(route('admin.group-bookings.store'))
}
</script>
