<template>
    <DashboardLayout title="Add Guest">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Register New Guest</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Complete guest registration with all required information including police verification details.</p>
                </div>
                <Link href="/admin/guests"
                      class="px-4 py-2 rounded-md transition-colors"
                      :style="{ 
                          backgroundColor: themeColors.secondary,
                          color: themeColors.textPrimary 
                      }"
                      @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                      @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                    <ArrowLeftIcon class="h-4 w-4 mr-2 inline" />
                    Back to Guests
                </Link>
            </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-6">
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
                    <UserIcon class="h-5 w-5 mr-2 inline" />
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
                            No guest types available. <Link href="/admin/guest-types/create" 
                                                  :style="{ color: themeColors.primary }">Create one first</Link>
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
                               :style="{ color: themeColors.textSecondary }">Date of Birth *</label>
                        <DatePicker v-model="form.date_of_birth" :required="true" />
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
                        <div v-if="form.errors.gender" class="mt-1 text-sm"
                             :style="{ color: themeColors.danger }">{{ form.errors.gender }}</div>
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
                        <div v-if="form.errors.nationality" class="mt-1 text-sm"
                             :style="{ color: themeColors.danger }">{{ form.errors.nationality }}</div>
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
                    <UserIcon class="h-5 w-5 mr-2 inline" />
                    Contact Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                               :style="{ color: themeColors.textSecondary }">Alternate Phone</label>
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
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Address *</label>
                        <textarea v-model="form.address" rows="2" required
                                  class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                  :style="{ 
                                      backgroundColor: themeColors.background,
                                      borderColor: themeColors.border,
                                      color: themeColors.textPrimary,
                                      borderWidth: '1px',
                                      borderStyle: 'solid'
                                  }"></textarea>
                        <div v-if="form.errors.address" class="mt-1 text-sm"
                             :style="{ color: themeColors.danger }">{{ form.errors.address }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">City *</label>
                        <input type="text" v-model="form.city" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                                }">
                        <div v-if="form.errors.city" class="mt-1 text-sm"
                             :style="{ color: themeColors.danger }">{{ form.errors.city }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">State/Province *</label>
                        <input type="text" v-model="form.state" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                                }">
                        <div v-if="form.errors.state" class="mt-1 text-sm"
                             :style="{ color: themeColors.danger }">{{ form.errors.state }}</div>
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
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Postal Code</label>
                        <input type="text" v-model="form.postal_code"
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

            <!-- Emergency Contact Section -->
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
                    <UserIcon class="h-5 w-5 mr-2 inline" />
                    Emergency Contact
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Emergency Contact Name *</label>
                        <input type="text" v-model="form.emergency_contact_name" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                                }">
                        <div v-if="form.errors.emergency_contact_name" class="mt-1 text-sm"
                             :style="{ color: themeColors.danger }">{{ form.errors.emergency_contact_name }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Emergency Contact Phone *</label>
                        <input type="tel" v-model="form.emergency_contact_phone" required
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                                }">
                        <div v-if="form.errors.emergency_contact_phone" class="mt-1 text-sm"
                             :style="{ color: themeColors.danger }">{{ form.errors.emergency_contact_phone }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Relationship *</label>
                        <input type="text" v-model="form.emergency_contact_relationship" required
                               placeholder="e.g., Spouse, Parent, Sibling"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                                }">
                        <div v-if="form.errors.emergency_contact_relationship" class="mt-1 text-sm"
                             :style="{ color: themeColors.danger }">{{ form.errors.emergency_contact_relationship }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Emergency Contact Address</label>
                        <textarea v-model="form.emergency_contact_address" rows="2"
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

            <!-- Identification Documents Section (Police Required) -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.danger,
                     borderWidth: '2px',
                     borderStyle: 'solid'
                 }">
                <h2 class="text-xl font-bold mb-2"
                    :style="{ color: themeColors.textPrimary }">
                    <UserIcon class="h-5 w-5 mr-2 inline" :style="{ color: themeColors.danger }" /> Identification Documents (Police Required) *
                </h2>
                <p class="text-sm mb-4"
                   :style="{ color: themeColors.textSecondary }">All identification documents are mandatory for police verification.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                        <div v-if="form.errors.id_number" class="mt-1 text-sm text-red-600">{{ form.errors.id_number }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Issuing Authority *</label>
                        <input type="text" v-model="form.id_issuing_authority" required
                               placeholder="e.g., Government of [Country]"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                        <div v-if="form.errors.id_issuing_authority" class="mt-1 text-sm text-red-600">{{ form.errors.id_issuing_authority }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">ID Issue Date *</label>
                        <DatePicker v-model="form.id_issue_date" :required="true" />
                        <div v-if="form.errors.id_issue_date" class="mt-1 text-sm"
                             :style="{ color: themeColors.danger }">{{ form.errors.id_issue_date }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">ID Expiry Date *</label>
                        <DatePicker v-model="form.id_expiry_date" :required="true" />
                        <div v-if="form.errors.id_expiry_date" class="mt-1 text-sm"
                             :style="{ color: themeColors.danger }">{{ form.errors.id_expiry_date }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">ID Document (Upload)</label>
                        <input type="file" @change="handleIdDocumentChange" accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                        <p class="mt-1 text-xs"
                           :style="{ color: themeColors.textTertiary }">Accepted: PDF, JPG, PNG (Max 5MB)</p>
                    </div>
                </div>
            </div>

            <!-- Passport Details Section -->
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
                    <UserIcon class="h-5 w-5 mr-2 inline" /> Passport Details (If Applicable)
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Passport Number</label>
                        <input type="text" v-model="form.passport_number"
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
                               :style="{ color: themeColors.textSecondary }">Issuing Country</label>
                        <input type="text" v-model="form.passport_issuing_country"
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
                               :style="{ color: themeColors.textSecondary }">Issue Date</label>
                        <DatePicker v-model="form.passport_issue_date" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Expiry Date</label>
                        <DatePicker v-model="form.passport_expiry_date" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Passport Document (Upload)</label>
                        <input type="file" @change="handlePassportDocumentChange" accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                        <p class="mt-1 text-xs"
                           :style="{ color: themeColors.textTertiary }">Accepted: PDF, JPG, PNG (Max 5MB)</p>
                    </div>
                </div>
            </div>

            <!-- Visa Details Section -->
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
                    <UserIcon class="h-5 w-5 mr-2 inline" /> Visa Details (If Applicable)
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Visa Number</label>
                        <input type="text" v-model="form.visa_number"
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
                               :style="{ color: themeColors.textSecondary }">Visa Type</label>
                        <input type="text" v-model="form.visa_type"
                               placeholder="e.g., Tourist, Business, Student"
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
                               :style="{ color: themeColors.textSecondary }">Issue Date</label>
                        <DatePicker v-model="form.visa_issue_date" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Expiry Date</label>
                        <DatePicker v-model="form.visa_expiry_date" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Visa Document (Upload)</label>
                        <input type="file" @change="handleVisaDocumentChange" accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                        <p class="mt-1 text-xs"
                           :style="{ color: themeColors.textTertiary }">Accepted: PDF, JPG, PNG (Max 5MB)</p>
                    </div>
                </div>
            </div>

            <!-- Travel Information Section -->
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
                    <UserIcon class="h-5 w-5 mr-2 inline" /> Travel Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Arrival From</label>
                        <input type="text" v-model="form.arrival_from"
                               placeholder="Previous location/city"
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
                               :style="{ color: themeColors.textSecondary }">Departure To</label>
                        <input type="text" v-model="form.departure_to"
                               placeholder="Next destination/city"
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
                            <option value="">Select purpose...</option>
                            <option value="business">Business</option>
                            <option value="tourism">Tourism</option>
                            <option value="medical">Medical</option>
                            <option value="education">Education</option>
                            <option value="family_visit">Family Visit</option>
                            <option value="conference">Conference</option>
                            <option value="other">Other</option>
                        </select>
                        <div v-if="form.errors.purpose_of_visit" class="mt-1 text-sm text-red-600">{{ form.errors.purpose_of_visit }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Expected Duration (Days)</label>
                        <input type="number" v-model.number="form.expected_duration_days" min="1"
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
                               :style="{ color: themeColors.textSecondary }">Total Companions</label>
                        <input type="number" v-model.number="form.total_companions" min="0"
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

            <!-- Vehicle Information Section -->
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
                    <UserIcon class="h-5 w-5 mr-2 inline" /> Vehicle Information (If Applicable)
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Vehicle Registration Number</label>
                        <input type="text" v-model="form.vehicle_registration"
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
                               :style="{ color: themeColors.textSecondary }">Make & Model</label>
                        <input type="text" v-model="form.vehicle_make_model"
                               placeholder="e.g., Toyota Camry"
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
                               :style="{ color: themeColors.textSecondary }">Color</label>
                        <input type="text" v-model="form.vehicle_color"
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
                    <UserIcon class="h-5 w-5 mr-2 inline" /> Preferences & Special Requests
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
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
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Medical Conditions</label>
                        <textarea v-model="form.medical_conditions" rows="3"
                                  placeholder="Any medical conditions or allergies..."
                                  class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Dietary Restrictions</label>
                        <textarea v-model="form.dietary_restrictions" rows="3"
                                  placeholder="Any dietary restrictions or preferences..."
                                  class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Additional Notes</label>
                        <textarea v-model="form.notes" rows="3"
                                  placeholder="Any additional notes or information..."
                                  class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }"></textarea>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" v-model="form.is_vip" id="is_vip"
                               class="h-4 w-4 rounded transition-colors"
                               :style="{ 
                                   accentColor: themeColors.primary,
                                   borderColor: themeColors.border
                               }">
                        <label for="is_vip" class="ml-2 block text-sm"
                               :style="{ color: themeColors.textSecondary }">Mark as VIP Guest</label>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="shadow rounded-lg p-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <div class="flex justify-end space-x-3">
                    <Link href="/admin/guests" 
                          class="px-6 py-2 rounded-md transition-colors"
                          :style="{ 
                              backgroundColor: themeColors.secondary,
                              color: themeColors.textPrimary 
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        Cancel
                    </Link>
                    <button type="submit" 
                            :disabled="form.processing"
                            class="px-6 py-2 rounded-md transition-colors disabled:opacity-50"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                                color: 'white' 
                            }"
                            @mouseenter="!form.processing && ($event.target.style.backgroundColor = themeColors.hover)"
                            @mouseleave="!form.processing && ($event.target.style.backgroundColor = themeColors.primary)">
                        <span v-if="form.processing">Creating...</span>
                        <span v-else>Create Guest</span>
                    </button>
                </div>
            </div>
        </form>
    </DashboardLayout>
</template>

<script setup>
import { computed, reactive } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DatePicker from '@/Components/DatePicker.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { ArrowLeftIcon, UserIcon } from '@heroicons/vue/24/outline'

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
    guestTypes: {
        type: Array,
        default: () => []
    },
})

const form = useForm({
    // Personal Information
    guest_type_id: null,
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
    
    // Passport Details
    passport_number: '',
    passport_issuing_country: '',
    passport_issue_date: '',
    passport_expiry_date: '',
    passport_document: null,
    
    // Visa Details
    visa_number: '',
    visa_type: '',
    visa_issue_date: '',
    visa_expiry_date: '',
    visa_document: null,
    
    // Travel Information
    arrival_from: '',
    departure_to: '',
    purpose_of_visit: '',
    expected_duration_days: null,
    total_companions: 0,
    
    // Vehicle Information
    vehicle_registration: '',
    vehicle_make_model: '',
    vehicle_color: '',
    
    // Preferences
    special_requests: '',
    medical_conditions: '',
    dietary_restrictions: '',
    is_vip: false,
    notes: '',
})

const handleIdDocumentChange = (event) => {
    form.id_document = event.target.files[0]
}

const handlePassportDocumentChange = (event) => {
    form.passport_document = event.target.files[0]
}

const handleVisaDocumentChange = (event) => {
    form.visa_document = event.target.files[0]
}

const submit = () => {
    form.post('/admin/guests', {
        forceFormData: true,
        preserveScroll: true,
    })
}
</script>
