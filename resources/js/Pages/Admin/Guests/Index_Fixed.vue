<script setup>
import { ref, computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme'

const { themeColors } = useTheme()

const props = defineProps({
    user: Object,
    navigation: Object,
    guests: Array
})

// Search and filter functionality
const searchQuery = ref('')
const selectedStatus = ref('all')
const showGuestModal = ref(false)
const selectedGuest = ref(null)

// Computed properties
const filteredGuests = computed(() => {
    let filtered = props.guests || []
    
    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(guest => 
            guest.first_name.toLowerCase().includes(query) ||
            guest.last_name.toLowerCase().includes(query) ||
            guest.email?.toLowerCase().includes(query) ||
            guest.phone?.toLowerCase().includes(query) ||
            guest.id_number?.toLowerCase().includes(query)
        )
    }
    
    // Filter by status
    if (selectedStatus.value !== 'all') {
        filtered = filtered.filter(guest => {
            if (selectedStatus.value === 'checked_in') {
                return guest.current_reservation !== null
            } else if (selectedStatus.value === 'not_checked_in') {
                return guest.current_reservation === null
            }
            return true
        })
    }
    
    return filtered
})

const guestStats = computed(() => {
    const totalGuests = props.guests?.length || 0
    const checkedInGuests = props.guests?.filter(guest => guest.current_reservation !== null).length || 0
    const notCheckedInGuests = totalGuests - checkedInGuests
    
    return {
        total: totalGuests,
        checkedIn: checkedInGuests,
        notCheckedIn: notCheckedInGuests
    }
})

// Methods
const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString()
}

const formatDateTime = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleString()
}

const viewGuest = (guest) => {
    selectedGuest.value = guest
    showGuestModal.value = true
}

const closeModal = () => {
    showGuestModal.value = false
    selectedGuest.value = null
}

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'Checked In':
            return 'bg-green-100 text-green-800'
        case 'Not Checked In':
            return 'bg-gray-100 text-gray-800'
        default:
            return 'bg-blue-100 text-blue-800'
    }
}
</script>

<template>
    <DashboardLayout title="Guests" :user="user">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
              }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold"
                        :style="{ color: themeColors.textPrimary }">Guests</h1>
                    <p class="mt-2"
                        :style="{ color: themeColors.textSecondary }">
                        Manage hotel guests and their reservations
                    </p>
                </div>
                <button @click="showGuestModal = true"
                    class="px-4 py-2 rounded-lg font-medium transition-colors"
                    :style="{
                        backgroundColor: themeColors.primary,
                        color: '#ffffff'
                    }">
                    Add Guest
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                  }">
                <div class="text-sm font-medium mb-2"
                    :style="{ color: themeColors.textSecondary }">Total Guests</div>
                <div class="text-3xl font-bold"
                    :style="{ color: themeColors.textPrimary }">{{ guestStats.total }}</div>
            </div>
            
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                  }">
                <div class="text-sm font-medium mb-2"
                    :style="{ color: themeColors.textSecondary }">Checked In</div>
                <div class="text-3xl font-bold text-green-600"
                    :style="{ color: themeColors.textPrimary }">{{ guestStats.checkedIn }}</div>
            </div>
            
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                  }">
                <div class="text-sm font-medium mb-2"
                    :style="{ color: themeColors.textSecondary }">Not Checked In</div>
                <div class="text-3xl font-bold text-gray-600"
                    :style="{ color: themeColors.textPrimary }">{{ guestStats.notCheckedIn }}</div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
              }">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Search guests..."
                        class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                        :style="{
                            backgroundColor: themeColors.background,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary,
                            focusRingColor: themeColors.primary
                        }"
                    />
                </div>
                
                <div>
                    <select v-model="selectedStatus"
                        class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                        :style="{
                            backgroundColor: themeColors.background,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary,
                            focusRingColor: themeColors.primary
                        }">
                        <option value="all">All Guests</option>
                        <option value="checked_in">Checked In</option>
                        <option value="not_checked_in">Not Checked In</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Guests Table -->
        <div class="shadow rounded-lg p-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
              }">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b"
                            :style="{ borderColor: themeColors.border }">
                            <th class="text-left py-3 px-4 font-medium"
                                :style="{ color: themeColors.textPrimary }">Name</th>
                            <th class="text-left py-3 px-4 font-medium"
                                :style="{ color: themeColors.textPrimary }">Email</th>
                            <th class="text-left py-3 px-4 font-medium"
                                :style="{ color: themeColors.textPrimary }">Phone</th>
                            <th class="text-left py-3 px-4 font-medium"
                                :style="{ color: themeColors.textPrimary }">ID Number</th>
                            <th class="text-left py-3 px-4 font-medium"
                                :style="{ color: themeColors.textPrimary }">Status</th>
                            <th class="text-left py-3 px-4 font-medium"
                                :style="{ color: themeColors.textPrimary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="guest in filteredGuests" :key="guest.id"
                            class="border-b transition-colors hover:bg-opacity-50"
                            :style="{ 
                                borderColor: themeColors.border,
                                hoverBackgroundColor: themeColors.primary + '20'
                            }">
                            <td class="py-3 px-4"
                                :style="{ color: themeColors.textPrimary }">
                                <div>
                                    <div class="font-medium">{{ guest.first_name }} {{ guest.last_name }}</div>
                                    <div class="text-sm"
                                        :style="{ color: themeColors.textSecondary }">
                                        {{ guest.email }}
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4"
                                :style="{ color: themeColors.textSecondary }">{{ guest.email || 'N/A' }}</td>
                            <td class="py-3 px-4"
                                :style="{ color: themeColors.textSecondary }">{{ guest.phone || 'N/A' }}</td>
                            <td class="py-3 px-4"
                                :style="{ color: themeColors.textSecondary }">{{ guest.id_number || 'N/A' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 rounded text-xs font-medium"
                                    :class="getStatusBadgeClass(guest.status)">
                                    {{ guest.status }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex space-x-2">
                                    <button @click="viewGuest(guest)"
                                        class="text-sm font-medium transition-colors"
                                        :style="{ color: themeColors.primary }">
                                        View Details
                                    </button>
                                    <button v-if="guest.current_reservation"
                                        class="text-sm font-medium text-red-600 hover:text-red-800">
                                        Check Out
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div v-if="filteredGuests.length === 0" class="text-center py-8"
                    :style="{ color: themeColors.textSecondary }">
                    No guests found
                </div>
            </div>
        </div>

        <!-- Guest Details Modal -->
        <div v-if="showGuestModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="rounded-lg p-6 w-full max-w-2xl max-h-screen overflow-y-auto"
                :style="{ backgroundColor: themeColors.card }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">
                        Guest Details
                    </h3>
                    <button @click="closeModal"
                        class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div v-if="selectedGuest" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                :style="{ color: themeColors.textSecondary }">First Name</label>
                            <div class="px-4 py-2 rounded-lg border"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary
                                }">
                                {{ selectedGuest.first_name }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                :style="{ color: themeColors.textSecondary }">Last Name</label>
                            <div class="px-4 py-2 rounded-lg border"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary
                                }">
                                {{ selectedGuest.last_name }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                :style="{ color: themeColors.textSecondary }">Email</label>
                            <div class="px-4 py-2 rounded-lg border"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary
                                }">
                                {{ selectedGuest.email || 'N/A' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                :style="{ color: themeColors.textSecondary }">Phone</label>
                            <div class="px-4 py-2 rounded-lg border"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary
                                }">
                                {{ selectedGuest.phone || 'N/A' }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                :style="{ color: themeColors.textSecondary }">Nationality</label>
                            <div class="px-4 py-2 rounded-lg border"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary
                                }">
                                {{ selectedGuest.nationality || 'Unknown' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                :style="{ color: themeColors.textSecondary }">ID Number</label>
                            <div class="px-4 py-2 rounded-lg border"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary
                                }">
                                {{ selectedGuest.id_number || 'N/A' }}
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="selectedGuest.current_reservation" class="space-y-4">
                        <h4 class="text-md font-semibold mb-2"
                            :style="{ color: themeColors.textPrimary }">Current Reservation</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2"
                                    :style="{ color: themeColors.textSecondary }">Room</label>
                                <div class="px-4 py-2 rounded-lg border"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary
                                    }">
                                    {{ selectedGuest.current_room || 'N/A' }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2"
                                    :style="{ color: themeColors.textSecondary }">Status</label>
                                <div class="px-4 py-2 rounded-lg border"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary
                                    }">
                                    {{ selectedGuest.status }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end mt-6">
                    <button @click="closeModal"
                        class="px-4 py-2 rounded-lg font-medium transition-colors"
                        :style="{
                            backgroundColor: themeColors.background,
                            color: themeColors.textPrimary,
                            borderColor: themeColors.border,
                            borderWidth: '1px'
                        }">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
/* Component specific styles */
</style>
