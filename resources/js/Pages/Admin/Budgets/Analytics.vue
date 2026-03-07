<template>
    <DashboardLayout title="Budget Analytics" :user="user">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Budget Analytics</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Analytics overview for budgets and utilization.</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('admin.budget.dashboard')"
                          class="px-4 py-2 rounded-md transition-colors flex items-center space-x-2"
                          :style="{ backgroundColor: themeColors.secondary, color: themeColors.textPrimary }"
                          @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                          @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        <ArrowLeftIcon class="h-4 w-4" />
                        <span>Back</span>
                    </Link>
                    <button @click="exportAnalytics"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{ backgroundColor: '#8b5cf6' }"
                            @mouseenter="$event.target.style.backgroundColor = '#7c3aed'"
                            @mouseleave="$event.target.style.backgroundColor = '#8b5cf6'">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                        Export
                    </button>
                </div>
            </div>
        </div>

        <div class="shadow rounded-lg p-6"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="rounded-lg p-4" :style="{ backgroundColor: themeColors.background }">
                    <h3 class="text-lg font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Summary</h3>
                    <div class="space-y-2 text-sm" :style="{ color: themeColors.textSecondary }">
                        <div class="flex justify-between">
                            <span>Total Active Budgets</span>
                            <span :style="{ color: themeColors.textPrimary }">{{ analytics?.summary?.total_active_budgets ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Total Budgeted</span>
                            <span :style="{ color: themeColors.textPrimary }">{{ formatCurrency(analytics?.summary?.total_budgeted ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Total Spent</span>
                            <span :style="{ color: themeColors.textPrimary }">{{ formatCurrency(analytics?.summary?.total_spent ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Average Utilization</span>
                            <span :style="{ color: themeColors.textPrimary }">{{ analytics?.summary?.average_utilization ?? 0 }}%</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg p-4" :style="{ backgroundColor: themeColors.background }">
                    <h3 class="text-lg font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Health Distribution</h3>
                    <div class="space-y-2 text-sm" :style="{ color: themeColors.textSecondary }">
                        <div class="flex justify-between">
                            <span>Good</span>
                            <span :style="{ color: themeColors.success }">{{ analytics?.health_distribution?.good ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Warning</span>
                            <span :style="{ color: themeColors.warning }">{{ analytics?.health_distribution?.warning ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Critical</span>
                            <span :style="{ color: themeColors.danger }">{{ analytics?.health_distribution?.critical ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="rounded-lg p-4" :style="{ backgroundColor: themeColors.background }">
                    <h3 class="text-lg font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Department Breakdown</h3>
                    <div v-if="(analytics?.department_breakdown?.length ?? 0) === 0" class="text-sm" :style="{ color: themeColors.textSecondary }">
                        No department data available.
                    </div>
                    <div v-else class="space-y-3">
                        <div v-for="dept in analytics.department_breakdown" :key="dept.name" class="rounded-lg p-3"
                             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ dept.name }}</span>
                                <span class="text-sm" :style="{ color: themeColors.textSecondary }">{{ dept.utilization }}%</span>
                            </div>
                            <div class="flex justify-between text-xs mt-1" :style="{ color: themeColors.textSecondary }">
                                <span>Budgeted: {{ formatCurrency(dept.total_budgeted ?? 0) }}</span>
                                <span>Spent: {{ formatCurrency(dept.total_spent ?? 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg p-4" :style="{ backgroundColor: themeColors.background }">
                    <h3 class="text-lg font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Category Breakdown</h3>
                    <div v-if="(analytics?.category_breakdown?.length ?? 0) === 0" class="text-sm" :style="{ color: themeColors.textSecondary }">
                        No category data available.
                    </div>
                    <div v-else class="space-y-3">
                        <div v-for="cat in analytics.category_breakdown" :key="cat.name" class="rounded-lg p-3"
                             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ cat.name }}</span>
                                <span class="text-sm" :style="{ color: themeColors.textSecondary }">{{ cat.utilization }}%</span>
                            </div>
                            <div class="flex justify-between text-xs mt-1" :style="{ color: themeColors.textSecondary }">
                                <span>Budgeted: {{ formatCurrency(cat.total_budgeted ?? 0) }}</span>
                                <span>Spent: {{ formatCurrency(cat.total_spent ?? 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'
import { ArrowLeftIcon, DocumentArrowDownIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    analytics: Object,
    timeframe: String
})

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

loadTheme()

const user = computed(() => props.user)
const analytics = computed(() => props.analytics)

const exportAnalytics = () => {
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
        year: new Date().getFullYear()
    })
    
    // Create a form to submit the export request
    const form = document.createElement('form')
    form.method = 'POST'
    form.action = route('admin.budget.export')
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
</script>
