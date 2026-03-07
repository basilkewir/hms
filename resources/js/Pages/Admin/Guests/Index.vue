<template>
    <DashboardLayout title="Guests" :user="user">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                  backgroundColor: themeColors.card,
                  borderColor: themeColors.border,
                  borderWidth: '1px',
                  borderStyle: 'solid'
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
                <div class="flex items-center gap-3">
                    <button @click="printCurrentLoggedGuests"
                        class="px-4 py-2 rounded-lg font-medium transition-colors"
                        :style="{
                            backgroundColor: themeColors.background,
                            color: themeColors.textPrimary,
                            borderColor: themeColors.border,
                            borderWidth: '1px'
                        }">
                        Print Logged Guests
                    </button>
                    <button @click="showAddGuestModal = true"
                        class="px-4 py-2 rounded-lg font-medium transition-colors"
                        :style="{
                            backgroundColor: themeColors.primary,
                            color: '#ffffff'
                        }">
                        Add Guest
                    </button>
                </div>
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
                    :style="{ color: themeColors.success }">{{ guestStats.checkedIn }}</div>
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
                    :style="{ color: themeColors.textTertiary }">{{ guestStats.notCheckedIn }}</div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderWidth: '1px',
                 borderStyle: 'solid'
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
                            color: themeColors.textPrimary
                        }"
                    />
                </div>

                <div>
                    <select v-model="selectedStatus"
                        class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                        :style="{
                            backgroundColor: themeColors.background,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary
                        }">
                        <option value="all">All Guests</option>
                        <option value="checked_in">Checked In</option>
                        <option value="not_checked_in">Not Checked In</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Guests Table -->
        <div class="shadow rounded-lg overflow-hidden"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderWidth: '1px',
                 borderStyle: 'solid'
              }">
            <div class="p-6 overflow-x-auto">
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
                            :style="{ borderColor: themeColors.border }">
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
                                    :style="getStatusBadgeStyle(guest.status)">
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
                                        class="text-sm font-medium transition-colors"
                                        :style="{ color: themeColors.danger }">
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

            <div v-if="!Array.isArray(props.guests) && props.guests?.links" class="px-6 py-4 border-t"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderTopWidth: '1px'
                 }">
                <Pagination :links="props.guests.links" />
            </div>
        </div>

        <!-- Add Guest Modal -->
        <div v-if="showAddGuestModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="rounded-lg p-6 w-full max-w-4xl max-h-screen overflow-y-auto"
                 :style="{ backgroundColor: themeColors.card }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">
                        Add Guest
                    </h3>
                    <button @click="closeAddGuestModal"
                            class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitAddGuest" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">First Name</label>
                            <input v-model="addGuestForm.first_name"
                                   type="text"
                                   class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                            <div v-if="addGuestForm.errors.first_name" class="text-sm mt-1" :style="{ color: themeColors.danger }">
                                {{ addGuestForm.errors.first_name }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Last Name</label>
                            <input v-model="addGuestForm.last_name"
                                   type="text"
                                   class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                            <div v-if="addGuestForm.errors.last_name" class="text-sm mt-1" :style="{ color: themeColors.danger }">
                                {{ addGuestForm.errors.last_name }}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Date of Birth</label>
                            <div class="grid grid-cols-3 gap-2">
                                <select v-model="dobYear"
                                        class="w-full px-3 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                        :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                                    <option value="">Year</option>
                                    <option v-for="y in dobYears" :key="y" :value="y">{{ y }}</option>
                                </select>
                                <select v-model="dobMonth"
                                        class="w-full px-3 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                        :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                                    <option value="">Month</option>
                                    <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                                </select>
                                <select v-model="dobDay"
                                        class="w-full px-3 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                        :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                                    <option value="">Day</option>
                                    <option v-for="d in dobDays" :key="d" :value="d">{{ d }}</option>
                                </select>
                            </div>
                            <div v-if="addGuestForm.errors.date_of_birth" class="text-sm mt-1" :style="{ color: themeColors.danger }">
                                {{ addGuestForm.errors.date_of_birth }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Gender</label>
                            <select v-model="addGuestForm.gender"
                                    class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                    :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <div v-if="addGuestForm.errors.gender" class="text-sm mt-1" :style="{ color: themeColors.danger }">
                                {{ addGuestForm.errors.gender }}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Nationality</label>
                            <input v-model="addGuestForm.nationality"
                                   type="text"
                                   class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                            <div v-if="addGuestForm.errors.nationality" class="text-sm mt-1" :style="{ color: themeColors.danger }">
                                {{ addGuestForm.errors.nationality }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Phone</label>
                            <input v-model="addGuestForm.phone"
                                   type="text"
                                   class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                            <div v-if="addGuestForm.errors.phone" class="text-sm mt-1" :style="{ color: themeColors.danger }">
                                {{ addGuestForm.errors.phone }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Email (optional)</label>
                        <input v-model="addGuestForm.email"
                               type="email"
                               class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                        <div v-if="addGuestForm.errors.email" class="text-sm mt-1" :style="{ color: themeColors.danger }">
                            {{ addGuestForm.errors.email }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Address</label>
                        <input v-model="addGuestForm.address"
                               type="text"
                               class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                        <div v-if="addGuestForm.errors.address" class="text-sm mt-1" :style="{ color: themeColors.danger }">
                            {{ addGuestForm.errors.address }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">City</label>
                            <input v-model="addGuestForm.city"
                                   type="text"
                                   class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                            <div v-if="addGuestForm.errors.city" class="text-sm mt-1" :style="{ color: themeColors.danger }">
                                {{ addGuestForm.errors.city }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">State</label>
                            <input v-model="addGuestForm.state"
                                   type="text"
                                   class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                            <div v-if="addGuestForm.errors.state" class="text-sm mt-1" :style="{ color: themeColors.danger }">
                                {{ addGuestForm.errors.state }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Country</label>
                            <input v-model="addGuestForm.country"
                                   type="text"
                                   class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                            <div v-if="addGuestForm.errors.country" class="text-sm mt-1" :style="{ color: themeColors.danger }">
                                {{ addGuestForm.errors.country }}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Emergency Contact Name</label>
                            <input v-model="addGuestForm.emergency_contact_name"
                                   type="text"
                                   class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                            <div v-if="addGuestForm.errors.emergency_contact_name" class="text-sm mt-1" :style="{ color: themeColors.danger }">
                                {{ addGuestForm.errors.emergency_contact_name }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">Emergency Contact Phone</label>
                            <input v-model="addGuestForm.emergency_contact_phone"
                                   type="text"
                                   class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                            <div v-if="addGuestForm.errors.emergency_contact_phone" class="text-sm mt-1" :style="{ color: themeColors.danger }">
                                {{ addGuestForm.errors.emergency_contact_phone }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Emergency Contact Relationship</label>
                        <input v-model="addGuestForm.emergency_contact_relationship"
                               type="text"
                               class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                        <div v-if="addGuestForm.errors.emergency_contact_relationship" class="text-sm mt-1" :style="{ color: themeColors.danger }">
                            {{ addGuestForm.errors.emergency_contact_relationship }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">ID Type</label>
                            <select v-model="addGuestForm.id_type"
                                    class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                    :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                                <option value="passport">Passport</option>
                                <option value="national_id">National ID</option>
                                <option value="drivers_license">Driver's License</option>
                                <option value="other">Other</option>
                            </select>
                            <div v-if="addGuestForm.errors.id_type" class="text-sm mt-1" :style="{ color: themeColors.danger }">
                                {{ addGuestForm.errors.id_type }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">ID Number</label>
                            <input v-model="addGuestForm.id_number"
                                   type="text"
                                   class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                            <div v-if="addGuestForm.errors.id_number" class="text-sm mt-1" :style="{ color: themeColors.danger }">
                                {{ addGuestForm.errors.id_number }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">ID Issuing Authority</label>
                        <input v-model="addGuestForm.id_issuing_authority"
                               type="text"
                               class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                        <div v-if="addGuestForm.errors.id_issuing_authority" class="text-sm mt-1" :style="{ color: themeColors.danger }">
                            {{ addGuestForm.errors.id_issuing_authority }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">ID Issue Date</label>
                            <div class="grid grid-cols-3 gap-2">
                                <select v-model="idIssueYear"
                                        class="w-full px-3 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                        :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                                    <option value="">Year</option>
                                    <option v-for="y in idYears" :key="y" :value="y">{{ y }}</option>
                                </select>
                                <select v-model="idIssueMonth"
                                        class="w-full px-3 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                        :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                                    <option value="">Month</option>
                                    <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                                </select>
                                <select v-model="idIssueDay"
                                        class="w-full px-3 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                        :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                                    <option value="">Day</option>
                                    <option v-for="d in idIssueDays" :key="d" :value="d">{{ d }}</option>
                                </select>
                            </div>
                            <div v-if="addGuestForm.errors.id_issue_date" class="text-sm mt-1" :style="{ color: themeColors.danger }">
                                {{ addGuestForm.errors.id_issue_date }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2"
                                   :style="{ color: themeColors.textSecondary }">ID Expiry Date</label>
                            <div class="grid grid-cols-3 gap-2">
                                <select v-model="idExpiryYear"
                                        class="w-full px-3 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                        :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                                    <option value="">Year</option>
                                    <option v-for="y in idYears" :key="y" :value="y">{{ y }}</option>
                                </select>
                                <select v-model="idExpiryMonth"
                                        class="w-full px-3 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                        :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                                    <option value="">Month</option>
                                    <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                                </select>
                                <select v-model="idExpiryDay"
                                        class="w-full px-3 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                        :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                                    <option value="">Day</option>
                                    <option v-for="d in idExpiryDays" :key="d" :value="d">{{ d }}</option>
                                </select>
                            </div>
                            <div v-if="addGuestForm.errors.id_expiry_date" class="text-sm mt-1" :style="{ color: themeColors.danger }">
                                {{ addGuestForm.errors.id_expiry_date }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">ID Document (optional)</label>
                        <input type="file"
                               accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"
                               @change="e => addGuestForm.id_document = e.target.files?.[0] || null" />
                        <div v-if="addGuestForm.errors.id_document" class="text-sm mt-1" :style="{ color: themeColors.danger }">
                            {{ addGuestForm.errors.id_document }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Purpose of Visit</label>
                        <input v-model="addGuestForm.purpose_of_visit"
                               type="text"
                               class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                        <div v-if="addGuestForm.errors.purpose_of_visit" class="text-sm mt-1" :style="{ color: themeColors.danger }">
                            {{ addGuestForm.errors.purpose_of_visit }}
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="closeAddGuestModal"
                                class="px-4 py-2 rounded-lg font-medium transition-colors"
                                :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border, borderWidth: '1px' }">
                            Cancel
                        </button>
                        <button type="submit"
                                :disabled="addGuestForm.processing"
                                class="px-4 py-2 rounded-lg font-medium transition-colors"
                                :style="{ backgroundColor: themeColors.primary, color: '#ffffff', opacity: addGuestForm.processing ? 0.7 : 1 }">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Guest Details Modal -->
        <div v-if="showGuestDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="rounded-lg p-6 w-full max-w-2xl max-h-screen overflow-y-auto"
                :style="{ backgroundColor: themeColors.card }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">
                        Guest Details
                    </h3>
                    <button @click="closeGuestDetailsModal"
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
                    <button @click="printSelectedGuest"
                        class="px-4 py-2 rounded-lg font-medium transition-colors mr-3"
                        :style="{
                            backgroundColor: themeColors.primary,
                            color: '#ffffff'
                        }">
                        Print
                    </button>
                    <button @click="closeGuestDetailsModal"
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

<script setup>
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    user: Object,
    navigation: Object,
    guests: {
        type: [Object, Array],
        default: () => ({ data: [], links: [] })
    },
    guestStats: Object
})

// Theme colors using CSS variables
const themeColors = computed(() => ({
    background: 'var(--kotel-background, #f8fafc)',
    card: 'var(--kotel-card, #ffffff)',
    border: 'var(--kotel-border, #e2e8f0)',
    primary: 'var(--kotel-primary, #4F46E5)',
    secondary: 'var(--kotel-secondary, #64748b)',
    success: 'var(--kotel-success, #22c55e)',
    warning: 'var(--kotel-warning, #f59e0b)',
    danger: 'var(--kotel-danger, #ef4444)',
    textPrimary: 'var(--kotel-text-primary, #1e293b)',
    textSecondary: 'var(--kotel-text-secondary, #64748b)',
    textTertiary: 'var(--kotel-text-tertiary, #94a3b8)'
}))

// Search and filter functionality
const searchQuery = ref('')
const selectedStatus = ref('all')
const showAddGuestModal = ref(false)
const showGuestDetailsModal = ref(false)
const selectedGuest = ref(null)

const addGuestForm = useForm({
    first_name: '',
    last_name: '',
    phone: '',
    email: '',
    date_of_birth: '',
    gender: 'other',
    nationality: '',
    address: '',
    city: '',
    state: '',
    country: '',
    emergency_contact_name: '',
    emergency_contact_phone: '',
    emergency_contact_relationship: '',
    id_type: 'other',
    id_number: '',
    id_issuing_authority: '',
    id_issue_date: '',
    id_expiry_date: '',
    id_document: null,
    purpose_of_visit: ''
})

const months = [
    { value: '01', label: 'Jan' },
    { value: '02', label: 'Feb' },
    { value: '03', label: 'Mar' },
    { value: '04', label: 'Apr' },
    { value: '05', label: 'May' },
    { value: '06', label: 'Jun' },
    { value: '07', label: 'Jul' },
    { value: '08', label: 'Aug' },
    { value: '09', label: 'Sep' },
    { value: '10', label: 'Oct' },
    { value: '11', label: 'Nov' },
    { value: '12', label: 'Dec' },
]

const dobYear = ref('')
const dobMonth = ref('')
const dobDay = ref('')

const idIssueYear = ref('')
const idIssueMonth = ref('')
const idIssueDay = ref('')

const idExpiryYear = ref('')
const idExpiryMonth = ref('')
const idExpiryDay = ref('')

const dobYears = computed(() => {
    const years = []
    const current = new Date().getFullYear()
    for (let y = current; y >= 1900; y--) years.push(String(y))
    return years
})

const idYears = computed(() => {
    const years = []
    const current = new Date().getFullYear()
    for (let y = current + 20; y >= current - 30; y--) years.push(String(y))
    return years
})

const daysInMonth = (year, month) => {
    if (!year || !month) return 31
    const y = Number(year)
    const m = Number(month)
    return new Date(y, m, 0).getDate()
}

const buildDate = (year, month, day) => {
    if (!year || !month || !day) return ''
    const dd = String(day).padStart(2, '0')
    return `${year}-${month}-${dd}`
}

const dobDays = computed(() => {
    const max = daysInMonth(dobYear.value, dobMonth.value)
    return Array.from({ length: max }, (_, i) => String(i + 1).padStart(2, '0'))
})

const idIssueDays = computed(() => {
    const max = daysInMonth(idIssueYear.value, idIssueMonth.value)
    return Array.from({ length: max }, (_, i) => String(i + 1).padStart(2, '0'))
})

const idExpiryDays = computed(() => {
    const max = daysInMonth(idExpiryYear.value, idExpiryMonth.value)
    return Array.from({ length: max }, (_, i) => String(i + 1).padStart(2, '0'))
})

watch([dobYear, dobMonth, dobDay], () => {
    addGuestForm.date_of_birth = buildDate(dobYear.value, dobMonth.value, dobDay.value)
})

watch([idIssueYear, idIssueMonth, idIssueDay], () => {
    addGuestForm.id_issue_date = buildDate(idIssueYear.value, idIssueMonth.value, idIssueDay.value)
})

watch([idExpiryYear, idExpiryMonth, idExpiryDay], () => {
    addGuestForm.id_expiry_date = buildDate(idExpiryYear.value, idExpiryMonth.value, idExpiryDay.value)
})

const guestList = computed(() => {
    if (Array.isArray(props.guests)) return props.guests
    return props.guests?.data || []
})

const loggedGuests = computed(() => {
    return guestList.value.filter(g => g.status === 'checked_in')
})

// Guest statistics
const guestStats = computed(() => {
    if (props.guestStats) {
        return {
            total: props.guestStats.total ?? 0,
            checkedIn: props.guestStats.current ?? 0,
            notCheckedIn: (props.guestStats.total ?? 0) - (props.guestStats.current ?? 0)
        }
    }

    const guests = guestList.value
    const checkedIn = guests.filter(g => g.current_reservation !== null).length
    return {
        total: guests.length,
        checkedIn: checkedIn,
        notCheckedIn: guests.length - checkedIn
    }
})

// Filtered guests based on search and status
const filteredGuests = computed(() => {
    let filtered = guestList.value

    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(guest =>
            guest.first_name?.toLowerCase().includes(query) ||
            guest.last_name?.toLowerCase().includes(query) ||
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

// Status badge styling
const getStatusBadgeStyle = (status) => {
    const styles = {
        checked_in: {
            backgroundColor: 'var(--kotel-success, #22c55e)',
            color: 'white'
        },
        checked_out: {
            backgroundColor: 'var(--kotel-secondary, #64748b)',
            color: 'white'
        },
        reserved: {
            backgroundColor: 'var(--kotel-primary, #4F46E5)',
            color: 'white'
        },
        cancelled: {
            backgroundColor: 'var(--kotel-danger, #ef4444)',
            color: 'white'
        }
    }
    return styles[status] || styles['checked_out']
}

// View guest details
const viewGuest = (guest) => {
    selectedGuest.value = guest
    showGuestDetailsModal.value = true
}

const closeGuestDetailsModal = () => {
    showGuestDetailsModal.value = false
    selectedGuest.value = null
}

const closeAddGuestModal = () => {
    showAddGuestModal.value = false
    addGuestForm.reset()
    addGuestForm.clearErrors()
    dobYear.value = ''
    dobMonth.value = ''
    dobDay.value = ''
    idIssueYear.value = ''
    idIssueMonth.value = ''
    idIssueDay.value = ''
    idExpiryYear.value = ''
    idExpiryMonth.value = ''
    idExpiryDay.value = ''
}

const submitAddGuest = () => {
    addGuestForm.post(route('admin.guests.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            closeAddGuestModal()
        }
    })
}

const escapeHtml = (value) => {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;')
}

const openPrintWindow = (title, bodyHtml) => {
    const w = window.open('', '_blank')
    if (!w) return

    const primary = getComputedStyle(document.documentElement).getPropertyValue('--kotel-primary')?.trim() || '#3b82f6'
    const textPrimary = getComputedStyle(document.documentElement).getPropertyValue('--kotel-text-primary')?.trim() || '#1e293b'
    const textSecondary = getComputedStyle(document.documentElement).getPropertyValue('--kotel-text-secondary')?.trim() || '#64748b'
    const border = getComputedStyle(document.documentElement).getPropertyValue('--kotel-border')?.trim() || '#e2e8f0'
    const card = getComputedStyle(document.documentElement).getPropertyValue('--kotel-card')?.trim() || '#ffffff'
    const background = getComputedStyle(document.documentElement).getPropertyValue('--kotel-background')?.trim() || '#f8fafc'

    w.document.open()
    w.document.write(`
        <!doctype html>
        <html>
            <head>
                <meta charset="utf-8" />
                <meta name="viewport" content="width=device-width, initial-scale=1" />
                <title>${escapeHtml(title)}</title>
                <style>
                    body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial; margin: 24px; color: ${textPrimary}; background: ${background}; }
                    h1 { font-size: 18px; margin: 0 0 12px 0; }
                    .sub { color: ${textSecondary}; font-size: 12px; margin-bottom: 16px; }
                    .card { background: ${card}; border: 1px solid ${border}; border-radius: 10px; padding: 16px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid ${border}; padding: 10px; font-size: 12px; text-align: left; }
                    th { background: ${background}; }
                    .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; background: ${primary}; color: #fff; font-size: 11px; }
                    @media print { body { margin: 0; } .card { border: none; } }
                </style>
            </head>
            <body>
                <h1>${escapeHtml(title)}</h1>
                <div class="sub">Generated: ${escapeHtml(new Date().toLocaleString())}</div>
                <div class="card">${bodyHtml}</div>
            </body>
        </html>
    `)
    w.document.close()
    w.focus()
    w.print()
    w.close()
}

const printSelectedGuest = () => {
    if (!selectedGuest.value) return
    const g = selectedGuest.value
    const body = `
        <table>
            <tr><th>Guest ID</th><td>${escapeHtml(g.guest_id || '')}</td></tr>
            <tr><th>Name</th><td>${escapeHtml((g.first_name || '') + ' ' + (g.last_name || ''))}</td></tr>
            <tr><th>Email</th><td>${escapeHtml(g.email || 'N/A')}</td></tr>
            <tr><th>Phone</th><td>${escapeHtml(g.phone || 'N/A')}</td></tr>
            <tr><th>Nationality</th><td>${escapeHtml(g.nationality || 'N/A')}</td></tr>
            <tr><th>ID Number</th><td>${escapeHtml(g.id_number || 'N/A')}</td></tr>
            <tr><th>Status</th><td><span class="badge">${escapeHtml(g.status || '')}</span></td></tr>
            <tr><th>Current Room</th><td>${escapeHtml(g.current_room || 'N/A')}</td></tr>
            <tr><th>Checkout Date</th><td>${escapeHtml(g.checkout_date || 'N/A')}</td></tr>
        </table>
    `
    openPrintWindow(`Guest Details - ${g.first_name || ''} ${g.last_name || ''}`.trim(), body)
}

const printCurrentLoggedGuests = () => {
    const rows = loggedGuests.value.map(g => {
        return `
            <tr>
                <td>${escapeHtml(g.guest_id || '')}</td>
                <td>${escapeHtml((g.first_name || '') + ' ' + (g.last_name || ''))}</td>
                <td>${escapeHtml(g.phone || 'N/A')}</td>
                <td>${escapeHtml(g.current_room || 'N/A')}</td>
                <td><span class="badge">${escapeHtml(g.status || '')}</span></td>
            </tr>
        `
    }).join('')

    const body = `
        <table>
            <thead>
                <tr>
                    <th>Guest ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Room</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                ${rows || '<tr><td colspan="5">No logged guests found</td></tr>'}
            </tbody>
        </table>
    `
    openPrintWindow('Current Logged Guests', body)
}
</script>

<style scoped>
/* Component specific styles */
</style>
