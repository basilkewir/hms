<template>
    <DashboardLayout title="Add New Guest" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Add New Guest</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Create a new guest profile with all required information.</p>
                </div>
                <Link :href="route('front-desk.guests.index')"
                      class="px-4 py-2 rounded-md transition-colors flex items-center gap-2"
                      :style="{
                          backgroundColor: themeColors.secondary,
                          color: themeColors.textPrimary
                      }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                    <ArrowLeftIcon class="h-4 w-4" />
                    Back to Guests
                </Link>
            </div>
        </div>

        <!-- Error Summary -->
        <div v-if="Object.keys(form.errors).length > 0"
             class="mb-6 p-4 rounded-lg border"
             :style="{ backgroundColor: 'rgba(239,68,68,0.08)', borderColor: themeColors.danger }">
            <h4 class="text-sm font-semibold mb-2" :style="{ color: themeColors.danger }">Please fix the following errors:</h4>
            <ul class="list-disc list-inside text-sm space-y-1" :style="{ color: themeColors.danger }">
                <li v-for="(error, field) in form.errors" :key="field">
                    <strong>{{ field }}:</strong> {{ Array.isArray(error) ? error[0] : error }}
                </li>
            </ul>
        </div>

        <!-- Form -->
        <form @submit.prevent="createGuest" class="space-y-6">
            <!-- Personal Information Section -->
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
                    Personal Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Guest Type</label>
                        <select v-model="form.guest_type_id"
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option :value="null">Select Guest Type...</option>
                            <option v-for="type in guestTypes" :key="type.id" :value="type.id">
                                {{ type.name }}{{ type.code ? ` (${type.code})` : '' }}{{ type.discount_percentage > 0 ? ` - ${type.discount_percentage}% discount` : '' }}
                            </option>
                        </select>
                        <p v-if="guestTypes.length === 0" class="mt-1 text-xs"
                           :style="{ color: themeColors.textTertiary }">
                            No guest types available.
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Title</label>
                        <select v-model="form.title"
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="">Select...</option>
                            <option value="Mr">Mr</option>
                            <option value="Mrs">Mrs</option>
                            <option value="Miss">Miss</option>
                            <option value="Ms">Ms</option>
                            <option value="Dr">Dr</option>
                            <option value="Prof">Prof</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">First Name *</label>
                        <input type="text" v-model="form.first_name" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                        <div v-if="form.errors.first_name" class="mt-1 text-sm"
                             :style="{ color: themeColors.danger }">{{ form.errors.first_name }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Middle Name</label>
                        <input type="text" v-model="form.middle_name"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Last Name *</label>
                        <input type="text" v-model="form.last_name" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                        <div v-if="form.errors.last_name" class="mt-1 text-sm"
                             :style="{ color: themeColors.danger }">{{ form.errors.last_name }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Date of Birth</label>
                        <DatePicker v-model="form.date_of_birth"
                                     placeholder="Select date of birth"
                                     :max="maxDate" />
                        <div v-if="form.errors.date_of_birth" class="mt-1 text-sm"
                             :style="{ color: themeColors.danger }">{{ form.errors.date_of_birth }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Gender *</label>
                        <select v-model="form.gender" required
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="">Select...</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                        <div v-if="form.errors.gender" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.gender }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Nationality *</label>
                        <input type="text" v-model="form.nationality" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                        <div v-if="form.errors.nationality" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.nationality }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Occupation</label>
                        <input type="text" v-model="form.occupation"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                    </div>
                </div>
            </div>

            <!-- Contact Information Section -->
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
                    Contact Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Email</label>
                        <input type="email" v-model="form.email"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                        <div v-if="form.errors.email" class="mt-1 text-sm"
                             :style="{ color: themeColors.danger }">{{ form.errors.email }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Phone *</label>
                        <input type="tel" v-model="form.phone" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                        <div v-if="form.errors.phone" class="mt-1 text-sm"
                             :style="{ color: themeColors.danger }">{{ form.errors.phone }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Alternative Phone</label>
                        <input type="tel" v-model="form.alternate_phone"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Address</label>
                        <input type="text" v-model="form.address"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                        <div v-if="form.errors.address" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.address }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">City *</label>
                        <input type="text" v-model="form.city" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                        <div v-if="form.errors.city" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.city }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">State</label>
                        <input type="text" v-model="form.state"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                        <div v-if="form.errors.state" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.state }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">ZIP / Postal Code</label>
                        <input type="text" v-model="form.postal_code"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Country *</label>
                        <input type="text" v-model="form.country" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                        <div v-if="form.errors.country" class="mt-1 text-sm"
                             :style="{ color: themeColors.danger }">{{ form.errors.country }}</div>
                    </div>
                </div>
            </div>

            <!-- Identification Documents Section -->
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
                    Identification Documents
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">ID Type *</label>
                        <select v-model="form.id_type" required
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="">Select ID Type...</option>
                            <option value="passport">Passport</option>
                            <option value="national_id">National ID</option>
                            <option value="drivers_license">Driver's License</option>
                            <option value="other">Other</option>
                        </select>
                        <div v-if="form.errors.id_type" class="mt-1 text-sm"
                             :style="{ color: themeColors.danger }">{{ form.errors.id_type }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">ID Number *</label>
                        <input type="text" v-model="form.id_number" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                        <div v-if="form.errors.id_number" class="mt-1 text-sm"
                             :style="{ color: themeColors.danger }">{{ form.errors.id_number }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Issuing Authority</label>
                        <input type="text" v-model="form.id_issuing_authority"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                        <div v-if="form.errors.id_issuing_authority" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.id_issuing_authority }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Issue Date *</label>
                        <DatePicker v-model="form.id_issue_date"
                                     placeholder="Select issue date"
                                     :required="true"
                                     :max="maxDate" />
                        <div v-if="form.errors.id_issue_date" class="mt-1 text-sm"
                             :style="{ color: themeColors.danger }">{{ form.errors.id_issue_date }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Expiry Date *</label>
                        <DatePicker v-model="form.id_expiry_date"
                                     placeholder="Select expiry date"
                                     :required="true"
                                     :min="minDate" />
                        <div v-if="form.errors.id_expiry_date" class="mt-1 text-sm"
                             :style="{ color: themeColors.danger }">{{ form.errors.id_expiry_date }}</div>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact Section -->
            <div class="shadow rounded-lg p-6"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-xl font-bold mb-4 pb-2 border-b"
                    :style="{ color: themeColors.textPrimary, borderColor: themeColors.border }">
                    Emergency Contact
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Full Name *</label>
                        <input type="text" v-model="form.emergency_contact_name" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                        <div v-if="form.errors.emergency_contact_name" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.emergency_contact_name }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Phone *</label>
                        <input type="tel" v-model="form.emergency_contact_phone" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                        <div v-if="form.errors.emergency_contact_phone" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.emergency_contact_phone }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Relationship *</label>
                        <input type="text" v-model="form.emergency_contact_relationship" required placeholder="e.g. Spouse, Parent, Sibling"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                        <div v-if="form.errors.emergency_contact_relationship" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.emergency_contact_relationship }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Address</label>
                        <input type="text" v-model="form.emergency_contact_address"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                    </div>
                </div>
            </div>

            <!-- Preferences & Special Requests Section -->
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
                    Preferences &amp; Special Requests
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Purpose of Visit *</label>
                        <select v-model="form.purpose_of_visit" required
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="">Select...</option>
                            <option value="Hotel stay">Hotel Stay</option>
                            <option value="business">Business</option>
                            <option value="leisure">Leisure / Tourism</option>
                            <option value="medical">Medical</option>
                            <option value="education">Education</option>
                            <option value="transit">Transit</option>
                            <option value="other">Other</option>
                        </select>
                        <div v-if="form.errors.purpose_of_visit" class="mt-1 text-sm"
                             :style="{ color: themeColors.danger }">{{ form.errors.purpose_of_visit }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Room Preference</label>
                        <select v-model="form.room_preference"
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="">No preference</option>
                            <option value="smoking">Smoking</option>
                            <option value="non_smoking">Non-smoking</option>
                            <option value="high_floor">High floor</option>
                            <option value="low_floor">Low floor</option>
                            <option value="quiet_room">Quiet room</option>
                            <option value="city_view">City view</option>
                            <option value="ocean_view">Ocean view</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Bed Preference</label>
                        <select v-model="form.bed_preference"
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="">No preference</option>
                            <option value="single">Single bed</option>
                            <option value="double">Double bed</option>
                            <option value="twin">Twin beds</option>
                            <option value="king">King bed</option>
                            <option value="queen">Queen bed</option>
                        </select>
                    </div>
                    <div class="lg:col-span-3">
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Dietary Restrictions</label>
                        <textarea v-model="form.dietary_restrictions" rows="2"
                                  placeholder="Enter dietary restrictions (e.g., Vegetarian, Gluten-free, Nut allergy...)"
                                  class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                  :style="{
                                      backgroundColor: themeColors.background,
                                      borderColor: themeColors.border,
                                      color: themeColors.textPrimary,
                                      borderWidth: '1px',
                                      borderStyle: 'solid'
                                  }"></textarea>
                    </div>
                    <div class="lg:col-span-3">
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Special Requests</label>
                        <textarea v-model="form.special_requests" rows="3"
                                  placeholder="Any special requests or preferences..."
                                  class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                  :style="{
                                      backgroundColor: themeColors.background,
                                      borderColor: themeColors.border,
                                      color: themeColors.textPrimary,
                                      borderWidth: '1px',
                                      borderStyle: 'solid'
                                  }"></textarea>
                    </div>
                </div>
            </div>

            <!-- Additional Notes Section -->
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
                    Additional Notes
                </h2>
                <textarea v-model="form.notes" rows="3"
                          placeholder="Any additional notes or remarks about the guest..."
                          class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                          :style="{
                              backgroundColor: themeColors.background,
                              borderColor: themeColors.border,
                              color: themeColors.textPrimary,
                              borderWidth: '1px',
                              borderStyle: 'solid'
                          }"></textarea>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4">
                <Link :href="route('front-desk.guests.index')"
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
                        class="px-6 py-2 rounded-md transition-colors font-medium"
                        :style="{
                            backgroundColor: themeColors.primary,
                            color: '#ffffff',
                            opacity: form.processing ? 0.7 : 1
                        }"
                        @mouseenter="!form.processing && ($event.target.style.backgroundColor = themeColors.hover)"
                        @mouseleave="!form.processing && ($event.target.style.backgroundColor = themeColors.primary)">
                    <span v-if="form.processing">Creating...</span>
                    <span v-else>Create Guest Profile</span>
                </button>
            </div>
        </form>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DatePicker from '@/Components/DatePicker.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { getNavigationForRole } from '@/Utils/navigation.js'
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

// Date constraints
const maxDate = computed(() => {
    return new Date().toISOString().split('T')[0]
})

const minDate = computed(() => {
    return new Date().toISOString().split('T')[0]
})

const props = defineProps({
    user: Object,
    guestTypes: {
        type: Array,
        default: () => []
    },
})

const navigation = computed(() => getNavigationForRole('front_desk'))

const form = useForm({
    // Personal Information
    guest_type_id: '',
    title: '',
    first_name: '',
    last_name: '',
    middle_name: '',
    date_of_birth: '',
    gender: '',
    nationality: '',
    occupation: '',

    // Contact Information
    email: '',
    phone: '',
    alternate_phone: '',
    address: '',
    city: '',
    state: '',
    country: '',
    postal_code: '',

    // Emergency Contact
    emergency_contact_name: '',
    emergency_contact_phone: '',
    emergency_contact_relationship: '',
    emergency_contact_address: '',

    // Identification Documents
    id_type: '',
    id_number: '',
    id_issuing_authority: '',
    id_issue_date: '',
    id_expiry_date: '',
    id_document: null,

    // Preferences & Travel
    purpose_of_visit: '',
    room_preference: '',
    bed_preference: '',
    special_requests: '',
    dietary_restrictions: '',
    preferences: {},
    notes: '',
    medical_conditions: '',
    is_vip: false,
})

const createGuest = () => {
    // Build preferences object from individual fields
    const prefs = {}
    if (form.room_preference) prefs.room_preference = form.room_preference
    if (form.bed_preference)  prefs.bed_preference  = form.bed_preference
    form.preferences = prefs

    // Fill required fields with sensible defaults if left blank
    if (!form.date_of_birth) {
        const d = new Date(); d.setFullYear(d.getFullYear() - 18)
        form.date_of_birth = d.toISOString().split('T')[0]
    }
    if (!form.id_type)     form.id_type = 'other'
    if (!form.id_number)   form.id_number = 'N/A'
    if (!form.id_issue_date) form.id_issue_date = form.date_of_birth
    if (!form.id_expiry_date) {
        const exp = new Date(form.id_issue_date); exp.setFullYear(exp.getFullYear() + 10)
        form.id_expiry_date = exp.toISOString().split('T')[0]
    }
    if (!form.id_issuing_authority) form.id_issuing_authority = 'Not provided'
    if (!form.address)     form.address     = 'Not provided'
    if (!form.city)        form.city        = 'Not provided'
    if (!form.state)       form.state       = 'Not provided'
    if (!form.country)     form.country     = form.nationality || 'Not provided'
    if (!form.nationality) form.nationality = form.country || 'Not provided'
    if (!form.gender)      form.gender      = 'other'
    if (!form.emergency_contact_name)         form.emergency_contact_name         = 'Not provided'
    if (!form.emergency_contact_phone)        form.emergency_contact_phone        = form.phone || 'Not provided'
    if (!form.emergency_contact_relationship) form.emergency_contact_relationship = 'Not specified'
    if (!form.purpose_of_visit)               form.purpose_of_visit               = 'Hotel stay'

    form.post(route('front-desk.guests.store'), {
        forceFormData: true,
        preserveScroll: true,
        onError: (errors) => { console.error('Guest creation errors:', errors) },
    })
}
</script>

<style scoped>
/* Fix placeholder colors for inputs */
input::placeholder,
textarea::placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input::-webkit-input-placeholder,
textarea::-webkit-input-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input::-moz-placeholder,
textarea::-moz-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input:-ms-input-placeholder,
textarea:-ms-input-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

/* Fix placeholder colors for select options */
select option:disabled,
select option[disabled] {
    color: var(--kotel-text-tertiary) !important;
}

select option[value=""] {
    color: var(--kotel-text-tertiary) !important;
}
</style>
