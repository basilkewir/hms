<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import {
    UserIcon,
    CalendarDaysIcon,
    HomeIcon,
    CreditCardIcon,
    CheckCircleIcon,
    ClockIcon,
    MagnifyingGlassIcon,
    DocumentTextIcon,
    KeyIcon,
    BellIcon,
    PlusIcon,
    DocumentArrowDownIcon
} from '@heroicons/vue/24/outline'

// Theme system
const { currentTheme } = useTheme()

// Create themeColors object for compatibility
const themeColors = computed(() => ({
    primary: currentTheme.value.theme_primary_color,
    hover: currentTheme.value.theme_secondary_color,
    background: currentTheme.value.theme_background_color,
    card: currentTheme.value.theme_card_color,
    border: currentTheme.value.theme_border_color,
    textPrimary: currentTheme.value.theme_text_primary,
    textSecondary: currentTheme.value.theme_text_secondary,
    textTertiary: currentTheme.value.theme_text_tertiary,
    success: currentTheme.value.theme_success_color,
    warning: currentTheme.value.theme_warning_color,
    error: currentTheme.value.theme_danger_color
}))

// Props
const props = defineProps({
    user: Object,
    navigation: Object,
    activeCheckouts: Array,
    pendingCheckouts: Array
})

// Data
const searchQuery = ref('')
const selectedCheckout = ref(null)
const loading = ref(false)

// Computed properties
const filteredCheckouts = computed(() => {
    if (!searchQuery.value) return props.activeCheckouts

    const query = searchQuery.value.toLowerCase()
    return props.activeCheckouts.filter(checkout =>
        checkout.guest_name.toLowerCase().includes(query) ||
        checkout.room_number.toLowerCase().includes(query) ||
        checkout.guest_email?.toLowerCase().includes(query)
    )
})

// Methods
const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString()
}

const formatTime = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleTimeString()
}

const formatCurrency = (amount) => {
    if (!amount) return '0 FCFA'
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF'
    }).format(amount)
}

const checkoutGuest = (checkout) => {
    loading.value = true
    // In a real implementation, this would make an API call
    setTimeout(() => {
        loading.value = false
        alert(`Checking out ${checkout.guest_name} from room ${checkout.room_number}`)
    }, 1000)
}

const generateInvoice = (checkout) => {
    alert(`Generating invoice for ${checkout.guest_name}`)
}

const printReceipt = (checkout) => {
    alert(`Printing receipt for ${checkout.guest_name}`)
}

const exportCheckouts = () => {
    alert('Exporting checkout data...')
}
</script>

<template>
    <DashboardLayout title="Check-out Management" :user="user">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Check-out Management</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Manage guest check-outs and billing</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link href="/admin/reservations"
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{
                              backgroundColor: themeColors.primary,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        View Reservations
                    </Link>
                    <button @click="exportCheckouts"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{
                                backgroundColor: '#8b5cf6',
                            }"
                            @mouseenter="$event.target.style.backgroundColor = '#7c3aed'"
                            @mouseleave="$event.target.style.backgroundColor = '#8b5cf6'">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                        Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                  }">
                <div class="flex items-center">
                    <div class="p-3 rounded-full mr-4"
                         :style="{ backgroundColor: themeColors.warning + '20' }">
                        <ClockIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <div class="text-sm font-medium mb-2"
                            :style="{ color: themeColors.textSecondary }">Active Check-outs</div>
                        <div class="text-3xl font-bold"
                             :style="{ color: themeColors.textPrimary }">{{ activeCheckouts?.length || 0 }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                  }">
                <div class="flex items-center">
                    <div class="p-3 rounded-full mr-4"
                         :style="{ backgroundColor: themeColors.success + '20' }">
                        <CheckCircleIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <div class="text-sm font-medium mb-2"
                            :style="{ color: themeColors.textSecondary }">Pending Check-outs</div>
                        <div class="text-3xl font-bold"
                             :style="{ color: themeColors.textPrimary }">{{ pendingCheckouts?.length || 0 }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                  }">
                <div class="flex items-center">
                    <div class="p-3 rounded-full mr-4"
                         :style="{ backgroundColor: themeColors.primary + '20' }">
                        <CreditCardIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <div class="text-sm font-medium mb-2"
                            :style="{ color: themeColors.textSecondary }">Total Revenue Today</div>
                        <div class="text-3xl font-bold"
                             :style="{ color: themeColors.textPrimary }">
                            {{ formatCurrency(activeCheckouts?.reduce((sum, checkout) => sum + (checkout.total_amount || 0), 0)) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
              }">
            <div class="relative">
                <MagnifyingGlassIcon class="absolute left-3 top-3 h-5 w-5"
                    :style="{ color: themeColors.textSecondary }" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search check-outs by guest name, room, or email..."
                    class="w-full pl-10 pr-4 py-3 rounded-lg border focus:outline-none focus:ring-2"
                    :style="{
                        backgroundColor: themeColors.background,
                        borderColor: themeColors.border,
                        color: themeColors.textPrimary,
                        borderWidth: '1px',
                        borderStyle: 'solid'
                    }"
                />
            </div>
        </div>

        <!-- Active Check-outs -->
        <div class="rounded-lg border shadow-sm"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
              }">
            <div class="p-6 border-b"
                 :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-semibold"
                    :style="{ color: themeColors.textPrimary }">Active Check-outs</h3>
            </div>
            <div class="p-6">
                <div v-if="filteredCheckouts.length === 0" class="text-center py-8"
                    :style="{ color: themeColors.textSecondary }">
                    No active check-outs
                </div>
                <div v-else class="space-y-4">
                    <div v-for="checkout in filteredCheckouts" :key="checkout.id"
                         class="border rounded-lg p-4 transition-colors hover:opacity-80"
                         :style="{
                             borderColor: themeColors.border,
                             borderStyle: 'solid',
                             borderWidth: '1px'
                         }">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3"
                                     :style="{ backgroundColor: themeColors.warning + '20' }">
                                    <UserIcon class="h-5 w-5" :style="{ color: themeColors.warning }" />
                                </div>
                                <div>
                                    <div class="font-medium"
                                         :style="{ color: themeColors.textPrimary }">{{ checkout.guest_name }}</div>
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textSecondary }">{{ checkout.guest_email }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium"
                                     :style="{ color: themeColors.textSecondary }">Room {{ checkout.room_number }}</div>
                                <div class="text-lg font-bold"
                                     :style="{ color: themeColors.primary }">{{ formatCurrency(checkout.total_amount) }}</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm mb-3">
                            <div>
                                <span :style="{ color: themeColors.textSecondary }">Check-in:</span>
                                <span :style="{ color: themeColors.textPrimary }">{{ formatDate(checkout.check_in_date) }}</span>
                            </div>
                            <div>
                                <span :style="{ color: themeColors.textSecondary }">Check-out:</span>
                                <span :style="{ color: themeColors.textPrimary }">{{ formatDate(checkout.check_out_date) }}</span>
                            </div>
                        </div>
                        <div class="text-sm mb-3">
                            <span :style="{ color: themeColors.textSecondary }">Nights:</span>
                            <span :style="{ color: themeColors.textPrimary }">{{ checkout.total_nights || 0 }}</span>
                        </div>
                        <div class="flex gap-2">
                            <button @click="checkoutGuest(checkout)"
                                class="px-4 py-2 rounded-md font-medium text-white transition-colors flex-1 flex items-center justify-center"
                                :style="{ backgroundColor: themeColors.success }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.success + 'dd'"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                                <CheckCircleIcon class="h-4 w-4 mr-2" />
                                Process Check-out
                            </button>
                            <button @click="generateInvoice(checkout)"
                                class="px-4 py-2 rounded-md font-medium transition-colors flex-1 flex items-center justify-center"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    color: themeColors.textPrimary,
                                    borderColor: themeColors.border,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                                <DocumentTextIcon class="h-4 w-4 mr-2" />
                                Invoice
                            </button>
                            <button @click="printReceipt(checkout)"
                                class="px-4 py-2 rounded-md font-medium transition-colors flex-1 flex items-center justify-center"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    color: themeColors.textPrimary,
                                    borderColor: themeColors.border,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                                <DocumentTextIcon class="h-4 w-4 mr-2" />
                                Receipt
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
/* Component specific styles */
</style>
