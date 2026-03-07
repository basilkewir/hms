<template>
    <DashboardLayout title="Balance Sheet" :user="user">
        <!-- Header -->
        <div class="bg-gray-800 shadow rounded-lg p-6 mb-8 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">Balance Sheet</h1>
                    <p class="text-gray-300 mt-2">Financial position statement showing assets, liabilities, and equity.</p>
                </div>
                <div class="flex space-x-3">
                    <select v-model="selectedPeriod" @change="applyPeriod"
                            class="border border-gray-600 rounded-md px-3 py-2 bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="current">Current Period</option>
                        <option value="previous">Previous Period</option>
                        <option value="ytd">Year to Date</option>
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

        <!-- Balance Sheet Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gray-800 rounded-lg shadow p-6 border border-gray-700">
                <div class="flex items-center">
                    <BuildingOfficeIcon class="h-8 w-8 text-blue-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-400">Total Assets</p>
                        <p class="text-2xl font-bold text-white">{{ formatCurrency(balanceSheetData.totalAssets || 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-800 rounded-lg shadow p-6 border border-gray-700">
                <div class="flex items-center">
                    <CreditCardIcon class="h-8 w-8 text-red-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-400">Total Liabilities</p>
                        <p class="text-2xl font-bold text-white">{{ formatCurrency(balanceSheetData.totals?.total_liabilities || 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-800 rounded-lg shadow p-6 border border-gray-700">
                <div class="flex items-center">
                    <ChartPieIcon class="h-8 w-8 text-green-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-400">Total Equity</p>
                        <p class="text-2xl font-bold text-white">{{ formatCurrency(balanceSheetData.totals?.total_equity || 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Balance Sheet Report -->
        <div class="bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-700">
            <div class="px-6 py-4 border-b border-gray-700">
                <h3 class="text-lg font-medium text-white">Balance Sheet as of {{ formatDate(reportDate) }}</h3>
            </div>

            <div class="p-6">
                <!-- Assets Section -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-white mb-4 border-b border-gray-700 pb-2">ASSETS</h4>

                    <!-- Current Assets -->
                    <div class="mb-6">
                        <h5 class="text-md font-medium text-gray-300 mb-3">Current Assets</h5>
                        <div class="space-y-2 ml-4">
                            <div v-for="asset in balanceSheetData.currentAssets" :key="asset.account"
                                 class="flex justify-between py-1">
                                <span class="text-gray-300">{{ asset.account }}</span>
                                <span class="font-medium text-white">{{ formatCurrency(asset.amount || 0) }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-t border-gray-700 font-medium">
                                <span class="text-white">Total Current Assets</span>
                                <span class="text-white">{{ formatCurrency(balanceSheetData.totals?.total_current_assets || 0) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Fixed Assets -->
                    <div class="mb-6">
                        <h5 class="text-md font-medium text-gray-300 mb-3">Fixed Assets</h5>
                        <div class="space-y-2 ml-4">
                            <div v-for="asset in balanceSheetData.fixedAssets" :key="asset.account"
                                 class="flex justify-between py-1">
                                <span class="text-gray-300">{{ asset.account }}</span>
                                <span class="font-medium text-white">{{ formatCurrency(asset.amount || 0) }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-t border-gray-700 font-medium">
                                <span class="text-white">Total Fixed Assets</span>
                                <span class="text-white">{{ formatCurrency(balanceSheetData.totals?.total_fixed_assets || 0) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between py-3 border-t-2 border-gray-600 font-bold text-lg">
                        <span class="text-white">TOTAL ASSETS</span>
                        <span class="text-white">{{ formatCurrency(balanceSheetData.totals?.total_assets || 0) }}</span>
                    </div>
                </div>

                <!-- Liabilities Section -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-white mb-4 border-b border-gray-700 pb-2">LIABILITIES</h4>

                    <!-- Current Liabilities -->
                    <div class="mb-6">
                        <h5 class="text-md font-medium text-gray-300 mb-3">Current Liabilities</h5>
                        <div class="space-y-2 ml-4">
                            <div v-for="liability in balanceSheetData.currentLiabilities" :key="liability.account"
                                 class="flex justify-between py-1">
                                <span class="text-gray-300">{{ liability.account }}</span>
                                <span class="font-medium text-white">{{ formatCurrency(liability.amount || 0) }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-t border-gray-700 font-medium">
                                <span class="text-white">Total Current Liabilities</span>
                                <span class="text-white">{{ formatCurrency(balanceSheetData.totals?.total_current_liabilities || 0) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Long-term Liabilities -->
                    <div class="mb-6">
                        <h5 class="text-md font-medium text-gray-300 mb-3">Long-term Liabilities</h5>
                        <div class="space-y-2 ml-4">
                            <div v-for="liability in balanceSheetData.longTermLiabilities" :key="liability.account"
                                 class="flex justify-between py-1">
                                <span class="text-gray-300">{{ liability.account }}</span>
                                <span class="font-medium text-white">{{ formatCurrency(liability.amount || 0) }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-t border-gray-700 font-medium">
                                <span class="text-white">Total Long-term Liabilities</span>
                                <span class="text-white">{{ formatCurrency(balanceSheetData.totals?.total_long_term_liabilities || 0) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between py-3 border-t-2 border-gray-600 font-bold">
                        <span class="text-white">TOTAL LIABILITIES</span>
                        <span class="text-white">{{ formatCurrency(balanceSheetData.totals?.total_liabilities || 0) }}</span>
                    </div>
                </div>

                <!-- Equity Section -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-white mb-4 border-b border-gray-700 pb-2">EQUITY</h4>
                    <div class="space-y-2 ml-4">
                        <div v-for="equity in balanceSheetData.equity" :key="equity.account"
                             class="flex justify-between py-1">
                            <span class="text-gray-300">{{ equity.account }}</span>
                            <span class="font-medium text-white">{{ formatCurrency(equity.amount || 0) }}</span>
                        </div>
                        <div class="flex justify-between py-3 border-t-2 border-gray-600 font-bold">
                            <span class="text-white">TOTAL EQUITY</span>
                            <span class="text-white">{{ formatCurrency(balanceSheetData.totals?.total_equity || 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Total Liabilities and Equity -->
                <div class="border-t-4 border-gray-600 pt-4">
                    <div class="flex justify-between py-3 font-bold text-lg">
                        <span class="text-white">TOTAL LIABILITIES AND EQUITY</span>
                        <span class="text-white">{{ formatCurrency(balanceSheetData.totals?.total_liabilities_and_equity || 0) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'
import {
    DocumentArrowDownIcon,
    BuildingOfficeIcon,
    CreditCardIcon,
    ChartPieIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    balanceSheetData: Object,
    period: String,
    asOfDate: String,
    currency: Object,
})

const selectedPeriod = ref(props.period || 'current')
const selectedFormat = ref('xlsx')
const reportDate = ref(props.asOfDate || new Date().toISOString().split('T')[0])

const balanceSheetData = computed(() => {
    const data = props.balanceSheetData || {}
    const currentAssets = data.current_assets || []
    const fixedAssets = data.fixed_assets || []
    const currentLiabilities = data.current_liabilities || []
    const longTermLiabilities = data.long_term_liabilities || []
    const equity = data.equity || []

    const sum = (items) => items.reduce((total, item) => total + (item.amount || 0), 0)
    const totalCurrentAssets = sum(currentAssets)
    const totalFixedAssets = sum(fixedAssets)
    const totalAssets = totalCurrentAssets + totalFixedAssets
    const totalCurrentLiabilities = sum(currentLiabilities)
    const totalLongTermLiabilities = sum(longTermLiabilities)
    const totalLiabilities = totalCurrentLiabilities + totalLongTermLiabilities
    const totalEquity = sum(equity)

    return {
        currentAssets,
        fixedAssets,
        currentLiabilities,
        longTermLiabilities,
        equity,
        totalCurrentAssets,
        totalFixedAssets,
        totalAssets,
        totalCurrentLiabilities,
        totalLongTermLiabilities,
        totalLiabilities,
        totalEquity,
        totalLiabilitiesAndEquity: totalLiabilities + totalEquity
    }
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const exportReport = () => {
    const formData = new FormData()
    formData.append('type', 'balance-sheet')
    formData.append('period', selectedPeriod.value)
    formData.append('as_of_date', reportDate.value)
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
    formData.append('type', 'balance-sheet')
    formData.append('period', selectedPeriod.value)
    formData.append('as_of_date', reportDate.value)
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

const applyPeriod = () => {
    router.get(route('accountant.reports.balance-sheet'), {
        period: selectedPeriod.value,
        as_of_date: reportDate.value
    }, {
        preserveScroll: true,
        preserveState: true
    })
}
</script>
