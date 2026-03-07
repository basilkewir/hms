<template>
    <DashboardLayout title="Profit & Loss Statement" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="bg-gray-800 shadow rounded-lg p-6 mb-8 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">Profit & Loss Statement</h1>
                    <p class="text-gray-300 mt-2">Income statement showing revenue, expenses, and net profit.</p>
                </div>
                <div class="flex space-x-3">
                    <select v-model="selectedPeriod"
                            class="border border-gray-600 rounded-md px-3 py-2 bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="monthly">This Month</option>
                        <option value="quarterly">This Quarter</option>
                        <option value="yearly">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                    <select v-model="selectedFormat"
                            class="border border-gray-600 rounded-md px-3 py-2 bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="xlsx">Excel (.xlsx)</option>
                        <option value="csv">CSV (.csv)</option>
                        <option value="pdf">PDF (.pdf)</option>
                    </select>
                    <button @click="exportReport"
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2 inline" />
                        Export
                    </button>
                    <button @click="printReport"
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                        <PrinterIcon class="h-4 w-4 mr-2 inline" />
                        Print
                    </button>
                </div>
            </div>
        </div>

        <!-- P&L Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gray-800 rounded-lg shadow p-6 border border-gray-700">
                <div class="flex items-center">
                    <CurrencyDollarIcon class="h-8 w-8 text-green-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-400">Total Revenue</p>
                        <p class="text-2xl font-bold text-white">{{ formatCurrencyWithProps(profitLossData.total_revenue) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-800 rounded-lg shadow p-6 border border-gray-700">
                <div class="flex items-center">
                    <MinusCircleIcon class="h-8 w-8 text-red-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-400">Total Expenses</p>
                        <p class="text-2xl font-bold text-white">{{ formatCurrencyWithProps(profitLossData.total_cogs + profitLossData.total_operating_expenses) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-800 rounded-lg shadow p-6 border border-gray-700">
                <div class="flex items-center">
                    <ChartBarIcon class="h-8 w-8 text-blue-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-400">Gross Profit</p>
                        <p class="text-2xl font-bold text-white">{{ formatCurrencyWithProps(profitLossData.gross_profit) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-800 rounded-lg shadow p-6 border border-gray-700">
                <div class="flex items-center">
                    <component :is="profitLossData.netProfit >= 0 ? PlusCircleIcon : MinusCircleIcon"
                              :class="profitLossData.netProfit >= 0 ? 'text-green-500' : 'text-red-500'"
                              class="h-8 w-8 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-400">Net Profit</p>
                        <p class="text-2xl font-bold"
                           :class="profitLossData.netProfit >= 0 ? 'text-green-400' : 'text-red-400'">
                            {{ formatCurrency(Math.abs(profitLossData.netProfit || 0)) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- P&L Statement -->
        <div class="bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-700">
            <div class="px-6 py-4 border-b border-gray-700">
                <h3 class="text-lg font-medium text-white">
                    Profit & Loss Statement for {{ formatPeriod(selectedPeriod) }}
                </h3>
            </div>

            <div class="p-6">
                <!-- Revenue Section -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-white mb-4 border-b border-gray-700 pb-2">REVENUE</h4>
                    <div class="space-y-2 ml-4">
                        <div class="flex justify-between py-1">
                            <span class="text-gray-300">Room Revenue</span>
                            <span class="font-medium text-green-400">{{ formatCurrencyWithProps(profitLossData.revenue?.room_revenue || 0) }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-gray-300">POS Sales</span>
                            <span class="font-medium text-green-400">{{ formatCurrencyWithProps(profitLossData.revenue?.pos_sales || 0) }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-gray-300">Food & Beverage</span>
                            <span class="font-medium text-green-400">{{ formatCurrencyWithProps(profitLossData.revenue?.food_beverage || 0) }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-gray-300">Conference Services</span>
                            <span class="font-medium text-green-400">{{ formatCurrencyWithProps(profitLossData.revenue?.conference_services || 0) }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-gray-300">Other Services</span>
                            <span class="font-medium text-green-400">{{ formatCurrencyWithProps(profitLossData.revenue?.other_services || 0) }}</span>
                        </div>
                        <div class="flex justify-between py-3 border-t-2 border-gray-600 font-bold text-lg">
                            <span class="text-white">TOTAL REVENUE</span>
                            <span class="text-green-400">{{ formatCurrencyWithProps(profitLossData.total_revenue || 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Cost of Goods Sold -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-white mb-4 border-b border-gray-700 pb-2">COST OF GOODS SOLD</h4>
                    <div class="space-y-2 ml-4">
                        <div class="flex justify-between py-1">
                            <span class="text-gray-300">POS Costs</span>
                            <span class="font-medium text-red-400">{{ formatCurrencyWithProps(profitLossData.cogs?.pos_costs || 0) }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-gray-300">Food & Beverage Costs</span>
                            <span class="font-medium text-red-400">{{ formatCurrencyWithProps(profitLossData.cogs?.food_beverage_costs || 0) }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-gray-300">Room Supplies</span>
                            <span class="font-medium text-red-400">{{ formatCurrencyWithProps(profitLossData.cogs?.room_supplies || 0) }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-gray-300">Laundry & Cleaning</span>
                            <span class="font-medium text-red-400">{{ formatCurrencyWithProps(profitLossData.cogs?.laundry_cleaning || 0) }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-gray-300">Direct Utilities (60%)</span>
                            <span class="font-medium text-red-400">{{ formatCurrencyWithProps(profitLossData.cogs?.utilities_direct || 0) }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-gray-300">Other Direct Costs</span>
                            <span class="font-medium text-red-400">{{ formatCurrencyWithProps(profitLossData.cogs?.other_direct_costs || 0) }}</span>
                        </div>
                        <div class="flex justify-between py-3 border-t-2 border-gray-600 font-bold">
                            <span class="text-white">TOTAL COST OF GOODS SOLD</span>
                            <span class="text-red-400">{{ formatCurrencyWithProps(profitLossData.total_cogs || 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Gross Profit -->
                <div class="mb-8">
                    <div class="flex justify-between py-3 border-t-2 border-gray-600 font-bold text-lg bg-gray-700 px-4 rounded">
                        <span class="text-white">GROSS PROFIT</span>
                        <span class="text-blue-400">{{ formatCurrency(profitLossData.grossProfit || 0) }}</span>
                    </div>
                </div>

                <!-- Operating Expenses -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-white mb-4 border-b border-gray-700 pb-2">OPERATING EXPENSES</h4>
                    <div class="space-y-2 ml-4">
                        <div v-for="expense in profitLossData.operatingExpenses" :key="expense.account"
                             class="flex justify-between py-1">
                            <span class="text-gray-300">{{ expense.account }}</span>
                            <span class="font-medium text-red-400">{{ formatCurrency(expense.amount || 0) }}</span>
                        </div>
                        <div class="flex justify-between py-3 border-t-2 border-gray-600 font-bold">
                            <span class="text-white">TOTAL OPERATING EXPENSES</span>
                            <span class="text-red-400">{{ formatCurrency(profitLossData.totalOperatingExpenses || 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Operating Income -->
                <div class="mb-8">
                    <div class="flex justify-between py-3 border-t-2 border-gray-600 font-bold text-lg bg-gray-700 px-4 rounded">
                        <span class="text-white">OPERATING INCOME</span>
                        <span :class="profitLossData.operatingIncome >= 0 ? 'text-green-400' : 'text-red-400'">
                            {{ formatCurrency(Math.abs(profitLossData.operatingIncome || 0)) }}
                        </span>
                    </div>
                </div>

                <!-- Other Income/Expenses -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-white mb-4 border-b border-gray-700 pb-2">OTHER INCOME/EXPENSES</h4>
                    <div class="space-y-2 ml-4">
                        <div v-for="other in profitLossData.otherIncomeExpenses" :key="other.account"
                             class="flex justify-between py-1">
                            <span class="text-gray-300">{{ other.account }}</span>
                            <span class="font-medium"
                                  :class="other.amount >= 0 ? 'text-green-400' : 'text-red-400'">
                                {{ formatCurrency(Math.abs(other.amount || 0)) }}
                            </span>
                        </div>
                        <div class="flex justify-between py-3 border-t-2 border-gray-600 font-bold">
                            <span class="text-white">TOTAL OTHER INCOME/EXPENSES</span>
                            <span :class="profitLossData.totalOtherIncomeExpenses >= 0 ? 'text-green-400' : 'text-red-400'">
                                {{ formatCurrency(Math.abs(profitLossData.totalOtherIncomeExpenses || 0)) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Net Profit -->
                <div class="border-t-4 border-gray-600 pt-4">
                    <div class="flex justify-between py-4 font-bold text-xl bg-gray-700 px-4 rounded">
                        <span class="text-white">NET PROFIT</span>
                        <span :class="profitLossData.net_profit >= 0 ? 'text-green-400' : 'text-red-400'">
                            {{ formatCurrencyWithProps(profitLossData.net_profit) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { formatCurrency } from '@/Utils/currency.js'
import {
    DocumentArrowDownIcon,
    CurrencyDollarIcon,
    MinusCircleIcon,
    PlusCircleIcon,
    ChartBarIcon,
    PrinterIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    profitLossData: Object,
    period: String,
    startDate: String,
    endDate: String,
    currency: Object,
})

const navigation = computed(() => getNavigationForRole('accountant'))

const formatAccountLabel = (key) => {
    return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const profitLossData = computed(() => {
    const data = props.profitLossData || {}
    const mapEntries = (obj) => {
        if (!obj) return []
        return Object.entries(obj).map(([key, amount]) => ({
            account: formatAccountLabel(key),
            amount: amount ?? 0
        }))
    }

    return {
        revenue: data.revenue || {},
        total_revenue: data.total_revenue || 0,
        total_cogs: data.total_cogs || 0,
        total_operating_expenses: data.total_operating_expenses || 0,
        total_other_income_expenses: data.total_other_income_expenses || 0,
        net_profit: data.net_profit ?? 0,
        netProfit: data.net_profit ?? 0,
        gross_profit: data.gross_profit || 0,
        grossProfit: data.gross_profit || 0,
        operating_income: data.operating_income || 0,
        operatingIncome: data.operating_income || 0,
        costOfGoodsSold: mapEntries(data.cogs),
        operatingExpenses: mapEntries(data.operating_expenses),
        otherIncomeExpenses: mapEntries(data.other_income_expenses),
        totalCOGS: data.total_cogs || 0,
        totalOperatingExpenses: data.total_operating_expenses || 0,
        totalOtherIncomeExpenses: data.total_other_income_expenses || 0
    }
})

const selectedPeriod = ref(props.period || 'monthly')
const selectedFormat = ref('xlsx')

// Watch for period changes and reload data
watch(selectedPeriod, (newPeriod) => {
    if (newPeriod !== props.period) {
        router.get(route('accountant.reports.profit-loss'), {
            period: newPeriod
        }, {
            preserveState: true,
            preserveScroll: true
        })
    }
})

// Use admin settings currency (formatCurrency reads from page.props.hotelSettings)
const formatCurrencyWithProps = (amount) => formatCurrency(amount)

const formatPeriod = (period) => {
    const periods = {
        daily: 'Today',
        weekly: 'This Week',
        monthly: 'This Month',
        quarterly: 'This Quarter',
        yearly: 'This Year',
        custom: 'Custom Period'
    }
    return periods[period] || 'Current Period'
}

const exportReport = () => {
    const formData = new FormData()
    formData.append('type', 'profit-loss')
    formData.append('period', selectedPeriod.value)
    if (props.startDate) formData.append('start_date', props.startDate)
    if (props.endDate) formData.append('end_date', props.endDate)
    formData.append('format', selectedFormat.value)
    
    // Create a form and submit it to trigger file download
    const form = document.createElement('form')
    form.method = 'POST'
    form.action = route('accountant.reports.export')
    form.style.display = 'none'
    
    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (csrfToken) {
        const csrfInput = document.createElement('input')
        csrfInput.type = 'hidden'
        csrfInput.name = '_token'
        csrfInput.value = csrfToken
        form.appendChild(csrfInput)
    }
    
    // Add form data
    for (const [key, value] of formData.entries()) {
        const input = document.createElement('input')
        input.type = 'hidden'
        input.name = key
        input.value = value
        form.appendChild(input)
    }
    
    document.body.appendChild(form)
    form.submit()
    document.body.removeChild(form)
}

const printReport = () => {
    const formData = new FormData()
    formData.append('type', 'profit-loss')
    formData.append('period', selectedPeriod.value)
    if (props.startDate) formData.append('start_date', props.startDate)
    if (props.endDate) formData.append('end_date', props.endDate)
    formData.append('format', 'print')
    
    // Create a form and submit it to open print view
    const form = document.createElement('form')
    form.method = 'POST'
    form.action = route('accountant.reports.export')
    form.target = '_blank'
    form.style.display = 'none'
    
    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (csrfToken) {
        const csrfInput = document.createElement('input')
        csrfInput.type = 'hidden'
        csrfInput.name = '_token'
        csrfInput.value = csrfToken
        form.appendChild(csrfInput)
    }
    
    // Add form data
    for (const [key, value] of formData.entries()) {
        const input = document.createElement('input')
        input.type = 'hidden'
        input.name = key
        input.value = value
        form.appendChild(input)
    }
    
    document.body.appendChild(form)
    form.submit()
    document.body.removeChild(form)
}
</script>
