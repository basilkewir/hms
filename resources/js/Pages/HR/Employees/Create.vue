<script setup>
import { ref, computed } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

const page = usePage()
const user = computed(() => page.props.auth.user)
const navigation = computed(() => page.props.navigation)

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
    hover: `var(--kotel-primary-hover)`
}))

const roles = computed(() => page.props.roles)
const departments = computed(() => page.props.departments)

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    employee_id: '',
    phone: '',
    address: '',
    date_of_birth: '',
    hire_date: '',
    salary: '',
    role_id: '',
    department_id: '',
    is_active: true
})

const submit = () => {
    form.post(route('hr.employees.store'))
}

const formatDateForInput = (date) => {
    if (!date) return ''
    return new Date(date).toISOString().split('T')[0]
}
</script>

<template>
    <DashboardLayout title="Add Employee" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Add Employee</h1>
                    <p :style="{ color: themeColors.textSecondary }">Create a new employee account</p>
                </div>
                <Link :href="route('hr.employees.index')"
                      class="px-4 py-2 rounded-lg font-medium transition-colors"
                      :style="{
                          backgroundColor: themeColors.background,
                          color: themeColors.textPrimary,
                          borderColor: themeColors.border,
                          borderStyle: 'solid',
                          borderWidth: '1px'
                      }">
                    Back to Employees
                </Link>
            </div>
        </div>

        <!-- Form -->
        <div class="rounded-lg border shadow-sm"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <form @submit.prevent="submit" class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Personal Information -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Personal Information</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                                    Full Name *
                                </label>
                                <input 
                                    v-model="form.name"
                                    type="text" 
                                    required
                                    class="w-full px-3 py-2 rounded-lg border"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: form.errors.name ? themeColors.danger : themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderStyle: 'solid',
                                        borderWidth: '1px'
                                    }"
                                />
                                <p v-if="form.errors.name" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                                    Email Address *
                                </label>
                                <input 
                                    v-model="form.email"
                                    type="email" 
                                    required
                                    class="w-full px-3 py-2 rounded-lg border"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: form.errors.email ? themeColors.danger : themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderStyle: 'solid',
                                        borderWidth: '1px'
                                    }"
                                />
                                <p v-if="form.errors.email" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                                    Phone Number
                                </label>
                                <input 
                                    v-model="form.phone"
                                    type="tel" 
                                    class="w-full px-3 py-2 rounded-lg border"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: form.errors.phone ? themeColors.danger : themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderStyle: 'solid',
                                        borderWidth: '1px'
                                    }"
                                />
                                <p v-if="form.errors.phone" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                    {{ form.errors.phone }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                                    Address
                                </label>
                                <textarea 
                                    v-model="form.address"
                                    rows="3"
                                    class="w-full px-3 py-2 rounded-lg border"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: form.errors.address ? themeColors.danger : themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderStyle: 'solid',
                                        borderWidth: '1px'
                                    }"
                                ></textarea>
                                <p v-if="form.errors.address" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                    {{ form.errors.address }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                                    Date of Birth
                                </label>
                                <div class="relative">
                                    <input 
                                        ref="dobInput"
                                        v-model="form.date_of_birth"
                                        type="date" 
                                        class="w-full px-3 py-2 rounded-lg border cursor-pointer"
                                        :style="{
                                            backgroundColor: themeColors.background,
                                            borderColor: form.errors.date_of_birth ? themeColors.danger : themeColors.border,
                                            color: themeColors.textPrimary,
                                            borderStyle: 'solid',
                                            borderWidth: '1px'
                                        }"
                                    />
                                    <div class="absolute inset-0 cursor-pointer" @click="dobInput?.showPicker ? dobInput.showPicker() : dobInput?.focus()"></div>
                                </div>
                                <p v-if="form.errors.date_of_birth" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                    {{ form.errors.date_of_birth }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Employment Information -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Employment Information</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                                    Employee ID
                                </label>
                                <input 
                                    v-model="form.employee_id"
                                    type="text" 
                                    class="w-full px-3 py-2 rounded-lg border"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: form.errors.employee_id ? themeColors.danger : themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderStyle: 'solid',
                                        borderWidth: '1px'
                                    }"
                                />
                                <p v-if="form.errors.employee_id" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                    {{ form.errors.employee_id }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                                    Role *
                                </label>
                                <select 
                                    v-model="form.role_id"
                                    required
                                    class="w-full px-3 py-2 rounded-lg border"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: form.errors.role_id ? themeColors.danger : themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderStyle: 'solid',
                                        borderWidth: '1px'
                                    }">
                                    <option value="">Select a role</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.id">
                                        {{ role.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.role_id" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                    {{ form.errors.role_id }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                                    Department
                                </label>
                                <select 
                                    v-model="form.department_id"
                                    class="w-full px-3 py-2 rounded-lg border"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: form.errors.department_id ? themeColors.danger : themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderStyle: 'solid',
                                        borderWidth: '1px'
                                    }">
                                    <option value="">Select a department</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                        {{ dept.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.department_id" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                    {{ form.errors.department_id }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                                    Hire Date
                                </label>
                                <div class="relative">
                                    <input 
                                        ref="hireDateInput"
                                        v-model="form.hire_date"
                                        type="date" 
                                        class="w-full px-3 py-2 rounded-lg border cursor-pointer"
                                        :style="{
                                            backgroundColor: themeColors.background,
                                            borderColor: form.errors.hire_date ? themeColors.danger : themeColors.border,
                                            color: themeColors.textPrimary,
                                            borderStyle: 'solid',
                                            borderWidth: '1px'
                                        }"
                                    />
                                    <div class="absolute inset-0 cursor-pointer" @click="hireDateInput?.showPicker ? hireDateInput.showPicker() : hireDateInput?.focus()"></div>
                                </div>
                                <p v-if="form.errors.hire_date" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                    {{ form.errors.hire_date }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                                    Salary
                                </label>
                                <input 
                                    v-model="form.salary"
                                    type="number" 
                                    step="0.01"
                                    min="0"
                                    class="w-full px-3 py-2 rounded-lg border"
                                    :style="{
                                        backgroundColor: themeColors.background,
                                        borderColor: form.errors.salary ? themeColors.danger : themeColors.border,
                                        color: themeColors.textPrimary,
                                        borderStyle: 'solid',
                                        borderWidth: '1px'
                                    }"
                                />
                                <p v-if="form.errors.salary" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                    {{ form.errors.salary }}
                                </p>
                            </div>

                            <div class="flex items-center">
                                <input 
                                    v-model="form.is_active"
                                    type="checkbox" 
                                    id="is_active"
                                    class="rounded"
                                />
                                <label for="is_active" class="ml-2 text-sm" :style="{ color: themeColors.textPrimary }">
                                    Active Employee
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="mt-8 border-t pt-6" :style="{ borderColor: themeColors.border }">
                    <h3 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Account Information</h3>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                                Password *
                            </label>
                            <input 
                                v-model="form.password"
                                type="password" 
                                required
                                class="w-full px-3 py-2 rounded-lg border"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: form.errors.password ? themeColors.danger : themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderStyle: 'solid',
                                    borderWidth: '1px'
                                }"
                            />
                            <p v-if="form.errors.password" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">
                                Confirm Password *
                            </label>
                            <input 
                                v-model="form.password_confirmation"
                                type="password" 
                                required
                                class="w-full px-3 py-2 rounded-lg border"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: form.errors.password_confirmation ? themeColors.danger : themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderStyle: 'solid',
                                    borderWidth: '1px'
                                }"
                            />
                            <p v-if="form.errors.password_confirmation" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                {{ form.errors.password_confirmation }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex items-center justify-end space-x-4">
                    <Link :href="route('hr.employees.index')"
                          class="px-4 py-2 rounded-lg font-medium transition-colors"
                          :style="{
                              backgroundColor: themeColors.background,
                              color: themeColors.textPrimary,
                              borderColor: themeColors.border,
                              borderStyle: 'solid',
                              borderWidth: '1px'
                          }">
                        Cancel
                    </Link>
                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 rounded-lg font-medium text-white transition-colors disabled:opacity-50"
                        :style="{ backgroundColor: themeColors.primary }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        {{ form.processing ? 'Creating...' : 'Create Employee' }}
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>
