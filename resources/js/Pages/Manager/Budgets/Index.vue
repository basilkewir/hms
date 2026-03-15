<template>
    <DashboardLayout title="Budget Management" :user="user">
        <!-- Page Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Budget Management</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Manage budgets, track spending, and monitor financial performance.</p>
                </div>
                <div class="text-right">
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">{{ currentDateTime }}</p>
                    <p class="text-lg font-semibold"
                       :style="{ color: themeColors.textPrimary }">{{ user?.full_name }}</p>
                </div>
            </div>
        </div>

        <!-- Budget Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                        <CurrencyDollarIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Budgets</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ budgets.total || 0 }}</p>
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
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                        <CheckCircleIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Approved</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ getBudgetCountByStatus('approved') }}</p>
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
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(250, 204, 21, 0.1)' }">
                        <ClockIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Pending Approval</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ getBudgetCountByStatus('pending_approval') }}</p>
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
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(139, 92, 246, 0.1)' }">
                        <ExclamationTriangleIcon class="h-6 w-6" :style="{ color: '#8b5cf6' }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Over Budget</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ getOverBudgetCount() }}</p>
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
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(251, 146, 60, 0.1)' }">
                        <CalendarDaysIcon class="h-6 w-6" :style="{ color: '#fb923c' }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Active This Year</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ getActiveThisYearCount() }}</p>
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
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(239, 68, 68, 0.1)' }">
                        <DocumentTextIcon class="h-6 w-6" :style="{ color: themeColors.danger }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Draft</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ getBudgetCountByStatus('draft') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="shadow rounded-lg p-6 mb-6"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Search</label>
                    <input
                        type="text"
                        v-model="filters.search"
                        placeholder="Search budgets..."
                        class="w-full px-3 py-2 rounded-md focus:outline-none transition-colors"
                        :style="{
                            backgroundColor: themeColors.background,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary,
                            borderWidth: '1px',
                            borderStyle: 'solid'
                        }"
                        @input="debouncedSearch"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Status</label>
                    <select
                        v-model="filters.status"
                        class="w-full px-3 py-2 rounded-md focus:outline-none transition-colors"
                        :style="{
                            backgroundColor: themeColors.background,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary,
                            borderWidth: '1px',
                            borderStyle: 'solid'
                        }"
                        @change="searchBudgets"
                    >
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="pending_approval">Pending Approval</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="expired">Expired</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Category</label>
                    <select
                        v-model="filters.category_id"
                        class="w-full px-3 py-2 rounded-md focus:outline-none transition-colors"
                        :style="{
                            backgroundColor: themeColors.background,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary,
                            borderWidth: '1px',
                            borderStyle: 'solid'
                        }"
                        @change="searchBudgets"
                    >
                        <option value="">All Categories</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Department</label>
                    <select
                        v-model="filters.department_id"
                        class="w-full px-3 py-2 rounded-md focus:outline-none transition-colors"
                        :style="{
                            backgroundColor: themeColors.background,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary,
                            borderWidth: '1px',
                            borderStyle: 'solid'
                        }"
                        @change="searchBudgets"
                    >
                        <option value="">All Departments</option>
                        <option v-for="department in departments" :key="department.id" :value="department.id">
                            {{ department.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Year</label>
                    <select
                        v-model="filters.year"
                        class="w-full px-3 py-2 rounded-md focus:outline-none transition-colors"
                        :style="{
                            backgroundColor: themeColors.background,
                            borderColor: themeColors.border,
                            color: themeColors.textPrimary,
                            borderWidth: '1px',
                            borderStyle: 'solid'
                        }"
                        @change="searchBudgets"
                    >
                        <option v-for="year in years" :key="year" :value="year">
                            {{ year }}
                        </option>
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <button
                        @click="searchBudgets"
                        class="flex-1 px-4 py-2 rounded-md transition-colors"
                        :style="{
                            backgroundColor: themeColors.primary,
                            color: 'white'
                        }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.primary"
                    >
                        Search
                    </button>
                    <button
                        @click="resetFilters"
                        class="flex-1 px-4 py-2 rounded-md transition-colors"
                        :style="{
                            backgroundColor: themeColors.secondary,
                            color: themeColors.textPrimary
                        }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.secondary"
                    >
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters and Actions -->
        <div class="shadow rounded-lg"
             :style="{
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border
             }">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderBottomWidth: '1px'
                 }">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium"
                        :style="{ color: themeColors.textPrimary }">Budgets</h3>
                    <div class="flex items-center gap-3">
                        <Link :href="route(`${routePrefix}.budget.archived`)"
                              class="px-4 py-2 rounded-md transition-colors font-medium flex items-center"
                              :style="{ 
                                  backgroundColor: themeColors.secondary,
                                  color: themeColors.textPrimary
                              }"
                              @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                              @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                            Archived
                        </Link>
                        <Link :href="route(`${routePrefix}.budget.create`)"
                              class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                              :style="{ 
                                  backgroundColor: themeColors.primary,
                              }"
                              @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                              @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                            <PlusIcon class="h-4 w-4 mr-2" />
                            Create Budget
                        </Link>
                        <button @click="exportBudgets"
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

            <!-- Loading State -->
            <div v-if="loading" class="p-6 text-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-2 text-gray-600">Loading budgets...</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="budgets.data.length === 0" class="p-6 text-center">
                <div class="text-gray-500 mb-4">
                    <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <p class="text-gray-600">No budgets found.</p>
                <p class="text-sm text-gray-500 mt-1">Create your first budget to get started.</p>
            </div>

            <!-- Table Content -->
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Budget
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Category
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Department
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Spent
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Utilization
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="budget in budgets.data" :key="budget.id" 
                            class="transition-colors"
                            :style="{ 
                                borderBottomStyle: 'solid',
                                borderBottomWidth: '1px',
                                borderColor: themeColors.border
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium"
                                         :style="{ color: themeColors.textPrimary }">{{ budget.name }}</div>
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textSecondary }">{{ formatDate(budget.start_date) }} - {{ formatDate(budget.end_date) }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ budget.category?.name || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ budget.department?.name || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(budget.amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(budget.spent_amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <div class="flex-1 rounded-full h-2"
                                         :style="{ backgroundColor: 'rgba(0, 0, 0, 0.1)' }">
                                        <div class="h-2 rounded-full"
                                             :style="{ 
                                                 width: Math.min(budget.utilization_percentage, 100) + '%',
                                                 backgroundColor: getUtilizationColor(budget.utilization_percentage)
                                             }"></div>
                                    </div>
                                    <span class="text-sm font-medium"
                                          :style="{ color: themeColors.textPrimary }">{{ budget.utilization_percentage }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusBadgeClass(budget.status)">
                                    {{ formatStatus(budget.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link :href="route(`${routePrefix}.budget.show`, { budget: budget.id })"
                                      class="mr-3 transition-colors"
                                      :style="{ color: themeColors.primary }"
                                      @mouseenter="$event.target.style.color = themeColors.hover"
                                      @mouseleave="$event.target.style.color = themeColors.primary">View</Link>
                                <Link v-if="canEdit(budget)" :href="route(`${routePrefix}.budget.edit`, { budget: budget.id })"
                                      class="mr-3 transition-colors"
                                      :style="{ color: themeColors.success }"
                                      @mouseenter="$event.target.style.color = themeColors.hover"
                                      @mouseleave="$event.target.style.color = themeColors.success">Edit</Link>
                                <button v-if="canSubmitForApproval(budget)" @click="submitForApproval(budget)"
                                        class="mr-3 transition-colors"
                                        :style="{ color: '#8b5cf6' }"
                                        @mouseenter="$event.target.style.color = '#7c3aed'"
                                        @mouseleave="$event.target.style.color = '#8b5cf6'">Submit</button>
                                <button v-if="canApprove(budget)" @click="approveBudget(budget)"
                                        class="mr-3 transition-colors"
                                        :style="{ color: themeColors.success }"
                                        @mouseenter="$event.target.style.color = themeColors.hover"
                                        @mouseleave="$event.target.style.color = themeColors.success">Approve</button>
                                <button v-if="canReject(budget)" @click="rejectBudget(budget)"
                                        class="mr-3 transition-colors"
                                        :style="{ color: themeColors.danger }"
                                        @mouseenter="$event.target.style.color = themeColors.hover"
                                        @mouseleave="$event.target.style.color = themeColors.danger">Reject</button>
                                <button v-if="canArchive(budget)" @click="archiveBudget(budget)"
                                        class="transition-colors"
                                        :style="{ color: '#fb923c' }"
                                        @mouseenter="$event.target.style.color = '#f97316'"
                                        @mouseleave="$event.target.style.color = '#fb923c'">Archive</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="budgets.links" class="px-6 py-4 border-t"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderTopWidth: '1px'
                 }">
                <Pagination :links="budgets.links" />
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { debounce } from 'lodash'
import { formatCurrency } from '@/Utils/currency.js'
import { useTheme } from '@/Composables/useTheme.js'
import {
    DocumentArrowDownIcon,
    CalendarDaysIcon,
    CheckCircleIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    DocumentTextIcon,
    PlusIcon,
    CurrencyDollarIcon
} from '@heroicons/vue/24/outline'
import Pagination from '@/Components/Pagination.vue'

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
    budgets: Object,
    categories: Array,
    departments: Array,
    years: Array,
    filters: Object,
    routePrefix: { type: String, default: 'admin' },
})

const loading = ref(false)
const filters = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    category_id: props.filters?.category_id || '',
    department_id: props.filters?.department_id || '',
    year: props.filters?.year || new Date().getFullYear(),
})

const currentDateTime = computed(() => {
    return new Date().toLocaleString()
})

const debouncedSearch = debounce(() => {
    searchBudgets()
}, 500)

const getListRouteName = () => {
    if (props.filters?.status === 'archived') return `${props.routePrefix}.budget.archived`
    return `${props.routePrefix}.budget.index`
}

const searchBudgets = () => {
    loading.value = true
    router.get(route(getListRouteName()), {
        search: filters.value.search,
        status: filters.value.status,
        category_id: filters.value.category_id,
        department_id: filters.value.department_id,
        year: filters.value.year,
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false
        },
        onError: () => {
            loading.value = false
        }
    })
}

const resetFilters = () => {
    filters.value = {
        search: '',
        status: '',
        category_id: '',
        department_id: '',
        year: new Date().getFullYear(),
    }
    searchBudgets()
}

const loadPage = (url) => {
    if (!url) return
    loading.value = true
    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false
        },
        onError: () => {
            loading.value = false
        }
    })
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const getBudgetCountByStatus = (status) => {
    if (!props.budgets?.data) return 0
    return props.budgets.data.filter(budget => budget.status === status).length
}

const getOverBudgetCount = () => {
    if (!props.budgets?.data) return 0
    return props.budgets.data.filter(budget => budget.utilization_percentage >= 100).length
}

const getActiveThisYearCount = () => {
    if (!props.budgets?.data) return 0
    const currentYear = new Date().getFullYear()
    return props.budgets.data.filter(budget => {
        const startYear = new Date(budget.start_date).getFullYear()
        const endYear = new Date(budget.end_date).getFullYear()
        return budget.status === 'approved' && (startYear === currentYear || endYear === currentYear)
    }).length
}

const getStatusBadgeClass = (status) => {
    const classes = {
        pending_approval: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
        draft: 'bg-gray-100 text-gray-800',
        expired: 'bg-orange-100 text-orange-800',
        archived: 'bg-blue-100 text-blue-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatStatus = (status) => {
    return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getUtilizationColor = (percentage) => {
    if (percentage >= 100) return '#ef4444'
    if (percentage >= 80) return '#eab308'
    return '#22c55e'
}

const exportBudgets = () => {
    showExportDialog()
}

const showExportDialog = () => {
    // Create modal dialog
    const modal = document.createElement('div')
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50'
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6" style="background-color: var(--kotel-card); color: var(--kotel-text-primary);">
            <h3 class="text-lg font-semibold mb-4">Choose Export Format</h3>
            <div class="space-y-3">
                <button onclick="exportData('csv')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div>
                            <div class="font-medium">CSV</div>
                            <div class="text-sm text-gray-500">Excel-compatible spreadsheet format</div>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                <button onclick="exportData('xlsx')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v6a2 2 0 002 2h2m4-4h.01M17 16h.01"></path>
                        </svg>
                        <div>
                            <div class="font-medium">Excel</div>
                            <div class="text-sm text-gray-500">Microsoft Excel format</div>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                <button onclick="exportData('pdf')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <div class="font-medium">PDF</div>
                            <div class="text-sm text-gray-500">Portable Document Format</div>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                <button onclick="exportData('docx')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div>
                            <div class="font-medium">Word</div>
                            <div class="text-sm text-gray-500">Microsoft Word format</div>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
            <div class="flex gap-3 mt-6">
                <button onclick="closeExportDialog()" class="flex-1 px-4 py-2 border rounded-lg hover:bg-gray-50 transition-colors" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">
                    Cancel
                </button>
            </div>
        </div>
    `
    
    // Add to page
    document.body.appendChild(modal)
    
    // Make functions globally available
    window.exportData = (format) => {
        closeExportDialog()
        performExport(format)
    }
    
    window.closeExportDialog = () => {
        document.body.removeChild(modal)
        delete window.exportData
        delete window.closeExportDialog
    }
    
    // Close on backdrop click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeExportDialog()
        }
    })
}

const performExport = (format) => {
    const params = new URLSearchParams({
        format: format,
        year: filters.value.year || new Date().getFullYear(),
        search: filters.value.search || '',
        status: filters.value.status || '',
        category_id: filters.value.category_id || '',
        department_id: filters.value.department_id || ''
    })
    
    // Create a form to submit the export request
    const form = document.createElement('form')
    form.method = 'POST'
    form.action = route(`${props.routePrefix}.budget.export`)
    form.style.display = 'none'
    
    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')
    if (csrfToken) {
        const csrfInput = document.createElement('input')
        csrfInput.type = 'hidden'
        csrfInput.name = '_token'
        csrfInput.value = csrfToken.getAttribute('content')
        form.appendChild(csrfInput)
    }
    
    // Add parameters
    params.toString().split('&').forEach(param => {
        const [key, value] = param.split('=')
        const input = document.createElement('input')
        input.type = 'hidden'
        input.name = key
        input.value = decodeURIComponent(value)
        form.appendChild(input)
    })
    
    document.body.appendChild(form)
    form.submit()
    document.body.removeChild(form)
}

const getStatusColor = (status) => {
    switch (status) {
        case 'approved': return 'bg-green-100 text-green-800'
        case 'pending_approval': return 'bg-yellow-100 text-yellow-800'
        case 'rejected': return 'bg-red-100 text-red-800'
        case 'draft': return 'bg-gray-100 text-gray-800'
        case 'expired': return 'bg-orange-100 text-orange-800'
        case 'archived': return 'bg-blue-100 text-blue-800'
        default: return 'bg-gray-100 text-gray-800'
    }
}

const canEdit = (budget) => {
    return budget.status !== 'approved'
}

const canSubmitForApproval = (budget) => {
    return budget.status === 'draft'
}

const canApprove = (budget) => {
    return budget.status === 'pending_approval'
}

const canReject = (budget) => {
    return budget.status === 'pending_approval'
}

const canArchive = (budget) => {
    return budget.status === 'approved'
}

const submitForApproval = (budget) => {
    if (confirm('Are you sure you want to submit this budget for approval?')) {
        router.post(route(`${props.routePrefix}.budget.submit-for-approval`, { budget: budget.id }), {}, {
            onSuccess: () => {
                // Budget will be updated via inertia
            }
        })
    }
}

const approveBudget = (budget) => {
    if (confirm('Are you sure you want to approve this budget?')) {
        router.post(route(`${props.routePrefix}.budget.approve`, { budget: budget.id }), {}, {
            onSuccess: () => {
                // Budget will be updated via inertia
            }
        })
    }
}

const rejectBudget = (budget) => {
    if (confirm('Are you sure you want to reject this budget?')) {
        router.post(route(`${props.routePrefix}.budget.reject`, { budget: budget.id }), {}, {
            onSuccess: () => {
                // Budget will be updated via inertia
            }
        })
    }
}

const archiveBudget = (budget) => {
    if (confirm('Are you sure you want to archive this budget?')) {
        router.post(route(`${props.routePrefix}.budget.archive`, { budget: budget.id }), {}, {
            onSuccess: () => {
                // Budget will be updated via inertia
            }
        })
    }
}
</script>
