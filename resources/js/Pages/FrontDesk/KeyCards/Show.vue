<template>
    <DashboardLayout title="Key Card Details" :user="user" :navigation="navigation">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Key Card Details</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">View detailed information about this key card.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('front-desk.key-cards.edit', keyCard.id)" 
                          class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PencilIcon class="h-4 w-4 mr-2" />
                        Edit
                    </Link>
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
        </div>

        <!-- Key Card Information -->
        <div class="rounded-lg overflow-hidden shadow mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <!-- Card Header -->
            <div class="px-6 py-4 border-b"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderBottomWidth: '1px'
                 }">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium"
                        :style="{ color: themeColors.textPrimary }">Key Card Information</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="getStatusClass(keyCard.status)">
                        {{ formatStatus(keyCard.status) }}
                    </span>
                </div>
            </div>
            
            <!-- Card Content -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Card Number</label>
                            <div class="flex items-center">
                                <CreditCardIcon class="w-5 h-5 mr-2" :style="{ color: themeColors.textSecondary }" />
                                <span class="text-lg font-medium"
                                      :style="{ color: themeColors.textPrimary }">{{ keyCard.card_number }}</span>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Card Type</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                  :class="getCardTypeClass(keyCard.card_type)">
                                {{ formatCardType(keyCard.card_type) }}
                            </span>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Status</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                  :class="getStatusClass(keyCard.status)">
                                {{ formatStatus(keyCard.status) }}
                            </span>
                        </div>
                        
                        <div v-if="keyCard.notes">
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Notes</label>
                            <p class="text-sm"
                               :style="{ color: themeColors.textPrimary }">{{ keyCard.notes }}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Issued At</label>
                            <p class="text-sm"
                               :style="{ color: themeColors.textPrimary }">{{ keyCard.issued_at || 'Never issued' }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Returned At</label>
                            <p class="text-sm"
                               :style="{ color: themeColors.textPrimary }">{{ keyCard.returned_at || 'Not returned' }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Expires At</label>
                            <p class="text-sm"
                               :style="{ color: themeColors.textPrimary }">{{ keyCard.expires_at || 'No expiration' }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Active</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                  :class="keyCard.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                {{ keyCard.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Assignment -->
        <div v-if="keyCard.status === 'assigned'" class="rounded-lg overflow-hidden shadow mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <!-- Assignment Header -->
            <div class="px-6 py-4 border-b"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderBottomWidth: '1px'
                 }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Current Assignment</h3>
            </div>
            
            <!-- Assignment Content -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Guest</label>
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textPrimary }">
                                {{ keyCard.guest?.name || keyCard.reservation?.guest?.name || 'Unknown' }}
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Room</label>
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textPrimary }">
                                {{ keyCard.room?.number || 'Not assigned' }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Reservation</label>
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textPrimary }">
                                {{ keyCard.reservation?.number || 'Not assigned' }}
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Issued By</label>
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textPrimary }">
                                {{ keyCard.issued_by || 'Unknown' }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex gap-3 mt-6 pt-6 border-t"
                     :style="{ 
                         borderColor: themeColors.border,
                         borderTopWidth: '1px'
                     }">
                    <button @click="returnCard" 
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                            :style="{ backgroundColor: themeColors.success }"
                            @mouseenter="$event.target.style.backgroundColor = '#059669'"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                        Return Card
                    </button>
                    <button @click="markLost" 
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                            :style="{ backgroundColor: themeColors.warning }"
                            @mouseenter="$event.target.style.backgroundColor = '#d97706'"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.warning">
                        Mark as Lost
                    </button>
                    <button @click="markDamaged" 
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                            :style="{ backgroundColor: themeColors.danger }"
                            @mouseenter="$event.target.style.backgroundColor = '#dc2626'"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.danger">
                        Mark as Damaged
                    </button>
                </div>
            </div>
        </div>

        <!-- Assignment History -->
        <div class="rounded-lg overflow-hidden shadow"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <!-- History Header -->
            <div class="px-6 py-4 border-b"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderBottomWidth: '1px'
                 }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Assignment History</h3>
            </div>
            
            <!-- History Content -->
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Guest
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Room
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Action
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Staff
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="history in assignmentHistory" :key="history.id"
                            class="transition-colors"
                            :style="{ 
                                borderBottomStyle: 'solid',
                                borderBottomWidth: '1px',
                                borderColor: themeColors.border
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatDate(history.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ history.guest?.name || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ history.room?.number || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ history.action || 'Assigned' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ history.assignedBy?.full_name || history.returnedTo?.full_name || '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div v-if="!assignmentHistory.length" class="text-center py-8"
                     :style="{ color: themeColors.textTertiary }">
                    No assignment history available
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme.js'
import {
    CreditCardIcon,
    PencilIcon,
    ArrowLeftIcon
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
    assignmentHistory: Array,
})

const navigation = computed(() => getNavigationForRole('front_desk'))

const getStatusClass = (status) => {
    const classes = {
        'available': 'bg-green-100 text-black',
        'assigned': 'bg-blue-100 text-black',
        'lost': 'bg-yellow-100 text-black',
        'damaged': 'bg-red-100 text-black',
        'deactivated': 'bg-gray-100 text-black',
    }
    return classes[status] || 'bg-gray-100 text-black'
}

const getCardTypeClass = (type) => {
    const classes = {
        'standard': 'bg-blue-100 text-blue-800',
        'master': 'bg-purple-100 text-skyblue',
        'staff': 'bg-green-100 text-green-800',
        'maintenance': 'bg-orange-100 text-skyblue',
    }
    return classes[type] || 'bg-gray-100 text-gray-800'
}

const formatStatus = (status) => {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatCardType = (type) => {
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString()
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
</script>
