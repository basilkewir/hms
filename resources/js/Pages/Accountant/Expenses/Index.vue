<template>
    <DashboardLayout title="Expense Management" :user="user">
        <!-- Page Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Expense Management</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Track and manage all business expenses.</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('accountant.expenses.create')" 
                          class="px-4 py-2 rounded-md transition-colors flex items-center"
                          :style="{ 
                              backgroundColor: themeColors.primary,
                              color: '#ffffff'
                          }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Add Expense
                    </Link>
                    <div class="flex space-x-3">
                        <select v-model="selectedFormat"
                                class="rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary
                                }">
                            <option value="xlsx">Excel (.xlsx)</option>
                            <option value="csv">CSV (.csv)</option>
                            <option value="pdf">PDF (.pdf)</option>
                        </select>
                        <button @click="exportExpenses" 
                                class="px-4 py-2 rounded-md transition-colors flex items-center"
                                :style="{ 
                                    backgroundColor: themeColors.primary,
                                    color: '#ffffff'
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                            <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                            Export
                        </button>
                        <button @click="printExpenses"
                                class="px-4 py-2 rounded-md transition-colors flex items-center"
                                :style="{ 
                                    backgroundColor: themeColors.success,
                                    color: '#ffffff'
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                            <PrinterIcon class="h-4 w-4 mr-2" />
                            Print
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expense Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
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
                        <CurrencyDollarIcon class="h-6 w-6" :style="{ color: themeColors.danger }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">This Month</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ formatCurrency(expenseStats.thisMonth || 0) }}</p>
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
                         :style="{ backgroundColor: 'rgba(245, 158, 11, 0.1)' }">
                        <ClockIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Pending Approval</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ expenseStats.pending }}</p>
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
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                        <DocumentTextIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Expenses</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ expenseStats.total }}</p>
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
                        <ChartBarIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Categories</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ expenseStats.categories }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Search</label>
                    <input type="text" :value="searchQuery" @input="searchQuery = $event.target.value" @keyup.enter="applyFilters" placeholder="Search expenses..."
                           class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                           :style="{ 
                               backgroundColor: themeColors.background,
                               borderColor: themeColors.border,
                               color: themeColors.textPrimary
                           }">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Category</label>
                    <select v-model="selectedCategory" @change="applyFilters"
                            class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary
                            }">
                        <option value="">All Categories</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Status</label>
                    <select v-model="selectedStatus" @change="applyFilters"
                            class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary
                            }">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">Start Date</label>
                    <input type="date" v-model="selectedStartDate" @change="applyFilters"
                           @click="$event.target.showPicker()"
                           class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors cursor-pointer"
                           :style="{ 
                               backgroundColor: themeColors.background,
                               borderColor: themeColors.border,
                               color: themeColors.textPrimary
                           }">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"
                           :style="{ color: themeColors.textSecondary }">End Date</label>
                    <input type="date" v-model="selectedEndDate" @change="applyFilters"
                           @click="$event.target.showPicker()"
                           class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors cursor-pointer"
                           :style="{ 
                               backgroundColor: themeColors.background,
                               borderColor: themeColors.border,
                               color: themeColors.textPrimary
                           }">
                </div>
                <div class="flex items-end space-x-2">
                    <button @click="applyFilters"
                            class="flex-1 px-4 py-2 rounded-md font-medium transition-colors flex items-center"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                                color: '#ffffff'
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <MagnifyingGlassIcon class="h-4 w-4 mr-2" />
                        Search
                    </button>
                    <button @click="clearFilters"
                            class="flex-1 px-4 py-2 rounded-md font-medium transition-colors flex items-center"
                            :style="{ 
                                backgroundColor: themeColors.secondary,
                                color: themeColors.textPrimary
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        <ArrowPathIcon class="h-4 w-4 mr-2" />
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Expenses Table -->
        <div class="rounded-lg overflow-hidden shadow"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderBottomWidth: '1px'
                 }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">All Expenses</h3>
            </div>
            
            <!-- Table Content -->
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
                                Description
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Category
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Vendor
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
                        <tr v-for="expense in filteredExpenses" :key="expense.id" 
                            class="transition-colors"
                            :style="{ 
                                borderBottomStyle: 'solid',
                                borderBottomWidth: '1px',
                                borderColor: themeColors.border
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatDate(expense.date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium"
                                     :style="{ color: themeColors.textPrimary }">{{ expense.description }}</div>
                                <div class="text-sm"
                                     :style="{ color: themeColors.textSecondary }">{{ expense.reference_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="expense.category_color ? { backgroundColor: `${expense.category_color}20`, color: expense.category_color } : {}"
                                      :class="!expense.category_color ? getCategoryColor(expense.category_name) : ''">
                                    {{ expense.category_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ formatCurrency(expense.amount || 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                {{ expense.vendor || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getStatusColor(expense.status)">
                                    {{ formatStatus(expense.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click="editExpense(expense)" 
                                        class="mr-3 transition-colors"
                                        :style="{ color: themeColors.primary }"
                                        @mouseenter="$event.target.style.color = themeColors.hover"
                                        @mouseleave="$event.target.style.color = themeColors.primary">Edit</button>
                                                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div v-if="pagination.total > 0" class="px-6 py-4 border-t"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderTopWidth: '1px'
                 }">
                <div class="flex items-center justify-between">
                    <div class="text-sm"
                         :style="{ color: themeColors.textSecondary }">
                        Showing 
                        <span class="font-medium" :style="{ color: themeColors.textPrimary }">{{ pagination.from || 0 }}</span>
                        to 
                        <span class="font-medium" :style="{ color: themeColors.textPrimary }">{{ pagination.to || 0 }}</span>
                        of 
                        <span class="font-medium" :style="{ color: themeColors.textPrimary }">{{ pagination.total }}</span>
                        results
                    </div>
                    <div class="flex items-center space-x-2">
                        <select @change="changePerPage($event.target.value)" 
                                :value="selectedPerPage"
                                class="text-sm rounded-md px-3 py-1 border focus:outline-none"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary
                                }">
                            <option value="10">10 per page</option>
                            <option value="15">15 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                        <div class="flex">
                            <button @click="goToPage(pagination.current_page - 1)" 
                                    :disabled="pagination.current_page <= 1"
                                    class="px-3 py-1 text-sm rounded-l-md border transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        opacity: pagination.current_page <= 1 ? 0.5 : 1
                                    }"
                                    @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                    @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                                Previous
                            </button>
                            <button v-for="page in Math.min(5, pagination.last_page)" :key="page"
                                    @click="goToPage(page)"
                                    class="px-3 py-1 text-sm border transition-colors"
                                    :style="{ 
                                        backgroundColor: page === pagination.current_page ? themeColors.primary : themeColors.background,
                                        borderColor: themeColors.border,
                                        color: page === pagination.current_page ? 'white' : themeColors.textPrimary
                                    }"
                                    @mouseenter="page !== pagination.current_page && ($event.target.style.backgroundColor = themeColors.hover)"
                                    @mouseleave="page !== pagination.current_page && ($event.target.style.backgroundColor = themeColors.background)">
                                {{ page }}
                            </button>
                            <button @click="goToPage(pagination.current_page + 1)" 
                                    :disabled="pagination.current_page >= pagination.last_page"
                                    class="px-3 py-1 text-sm rounded-r-md border transition-colors"
                                    :style="{ 
                                        backgroundColor: themeColors.background,
                                        borderColor: themeColors.border,
                                        color: themeColors.textPrimary,
                                        opacity: pagination.current_page >= pagination.last_page ? 0.5 : 1
                                    }"
                                    @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                    @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'
import { 
    MagnifyingGlassIcon,
    FunnelIcon,
    PlusIcon,
    PencilIcon,
    CheckCircleIcon,
    ClockIcon,
    DocumentTextIcon,
    ChartBarIcon,
    DocumentArrowDownIcon,
    PrinterIcon,
    ArrowPathIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    expenseStats: Object,
    expenses: Object, // Changed to Object for pagination
    categories: Array,
    filters: Object,
})

const { loadTheme } = useTheme();
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
}));

loadTheme();

const searchQuery = ref(props.filters?.search || '')
const selectedCategory = ref(props.filters?.category || '')
const selectedStatus = ref(props.filters?.status || '')
const selectedStartDate = ref(props.filters?.start_date || '')
const selectedEndDate = ref(props.filters?.end_date || '')
const selectedPerPage = ref(props.filters?.per_page || 15)
const selectedFormat = ref('xlsx')

const expenseStats = computed(() => props.expenseStats || {
    thisMonth: 0,
    pending: 0,
    total: 0,
    categories: 0,
})

const expenses = computed(() => props.expenses?.data || [])
const pagination = computed(() => ({
    current_page: props.expenses?.current_page || 1,
    last_page: props.expenses?.last_page || 1,
    per_page: props.expenses?.per_page || 15,
    total: props.expenses?.total || 0,
}))
const categories = computed(() => props.categories || [])

const filteredExpenses = computed(() => expenses.value)

const getCategoryColor = (category) => {
    const categoryLower = (category || '').toLowerCase()
    const colors = {
        office_supplies: 'bg-blue-100 text-blue-800',
        utilities: 'bg-yellow-100 text-yellow-800',
        maintenance: 'bg-orange-100 text-orange-800',
        marketing: 'bg-purple-100 text-purple-800',
        travel: 'bg-green-100 text-green-800',
        other: 'bg-gray-100 text-gray-800'
    }
    return colors[categoryLower] || 'bg-gray-100 text-gray-800'
}

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-blue-100 text-blue-800',
        rejected: 'bg-red-100 text-red-800',
        paid: 'bg-green-100 text-green-800'
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const clearFilters = () => {
    searchQuery.value = ''
    selectedCategory.value = ''
    selectedStatus.value = ''
    selectedStartDate.value = ''
    selectedEndDate.value = ''
    applyFilters()
}

const applyFilters = () => {
    const params = {}
    
    if (searchQuery.value) params.search = searchQuery.value
    if (selectedCategory.value) params.category = selectedCategory.value
    if (selectedStatus.value) params.status = selectedStatus.value
    if (selectedStartDate.value) params.start_date = selectedStartDate.value
    if (selectedEndDate.value) params.end_date = selectedEndDate.value
    if (selectedPerPage.value && selectedPerPage.value !== 15) params.per_page = selectedPerPage.value
    
    router.get(route('accountant.expenses.index'), params, { preserveState: false })
}

const exportExpenses = () => {
    const params = new URLSearchParams()
    
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (selectedCategory.value) params.append('category', selectedCategory.value)
    if (selectedStatus.value) params.append('status', selectedStatus.value)
    if (selectedStartDate.value) params.append('start_date', selectedStartDate.value)
    if (selectedEndDate.value) params.append('end_date', selectedEndDate.value)
    params.append('format', selectedFormat.value)
    
    const queryString = params.toString()
    const url = queryString ? `?${queryString}` : ''
    
    window.location.href = route('accountant.expenses.export') + url
}

const printExpenses = () => {
    const params = new URLSearchParams()
    
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (selectedCategory.value) params.append('category', selectedCategory.value)
    if (selectedStatus.value) params.append('status', selectedStatus.value)
    if (selectedStartDate.value) params.append('start_date', selectedStartDate.value)
    if (selectedEndDate.value) params.append('end_date', selectedEndDate.value)
    params.append('format', 'print')
    
    const queryString = params.toString()
    const url = queryString ? `?${queryString}` : ''
    
    window.open(route('accountant.expenses.export') + url, '_blank')
}

const goToPage = (page) => {
    const params = {}
    
    if (searchQuery.value) params.search = searchQuery.value
    if (selectedCategory.value) params.category = selectedCategory.value
    if (selectedStatus.value) params.status = selectedStatus.value
    if (selectedStartDate.value) params.start_date = selectedStartDate.value
    if (selectedEndDate.value) params.end_date = selectedEndDate.value
    if (selectedPerPage.value && selectedPerPage.value !== 15) params.per_page = selectedPerPage.value
    params.page = page
    
    router.get(route('accountant.expenses.index'), params, { preserveState: false })
}

const changePerPage = (perPage) => {
    selectedPerPage.value = perPage
    applyFilters()
}

const editExpense = (expense) => {
    router.get(route('accountant.expenses.edit', expense.id))
}

</script>
