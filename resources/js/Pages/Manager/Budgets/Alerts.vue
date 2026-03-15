<template>
    <DashboardLayout title="Budget Alerts" :user="user">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Budget Alerts</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Budgets that require attention (over budget, near budget, or nearing expiration).</p>
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
                    <button @click="exportAlerts"
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

        <div class="space-y-4">
            <div v-for="item in alertBudgets" :key="item.budget?.id" class="shadow rounded-lg p-6"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">{{ item.budget?.name }}</h3>
                        <p class="text-sm mt-1" :style="{ color: themeColors.textSecondary }">
                            {{ item.budget?.category?.name || 'N/A' }}
                            <span class="mx-2">|</span>
                            {{ item.budget?.department?.name || 'N/A' }}
                        </p>
                        <div class="mt-2 text-sm" :style="{ color: themeColors.textSecondary }">
                            <span>Utilization: </span>
                            <span :style="{ color: themeColors.textPrimary }">{{ item.utilization_percentage ?? 0 }}%</span>
                            <span class="mx-2">|</span>
                            <span>Days Remaining: </span>
                            <span :style="{ color: themeColors.textPrimary }">{{ item.days_remaining ?? 0 }}</span>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span v-if="item.alerts?.over_budget" class="px-2 py-1 text-xs rounded-full"
                                  :style="{ backgroundColor: 'rgba(239, 68, 68, 0.1)', color: themeColors.danger }">Over Budget</span>
                            <span v-if="item.alerts?.critical" class="px-2 py-1 text-xs rounded-full"
                                  :style="{ backgroundColor: 'rgba(239, 68, 68, 0.1)', color: themeColors.danger }">Critical</span>
                            <span v-if="item.alerts?.warning" class="px-2 py-1 text-xs rounded-full"
                                  :style="{ backgroundColor: 'rgba(245, 158, 11, 0.1)', color: themeColors.warning }">Warning</span>
                            <span v-if="item.alerts?.near_expiration" class="px-2 py-1 text-xs rounded-full"
                                  :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)', color: themeColors.primary }">Near Expiration</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link v-if="item.budget?.id" :href="route('admin.budget.show', item.budget.id)"
                              class="px-4 py-2 rounded-md transition-colors"
                              :style="{ backgroundColor: themeColors.primary, color: '#ffffff' }"
                              @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                              @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                            View
                        </Link>
                    </div>
                </div>
            </div>

            <div v-if="alertBudgets.length === 0" class="shadow rounded-lg p-12 text-center"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h3 class="text-lg font-semibold mb-2" :style="{ color: themeColors.textPrimary }">No Alerts</h3>
                <p :style="{ color: themeColors.textSecondary }">All budgets are within healthy thresholds.</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { ArrowLeftIcon, DocumentArrowDownIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    alertBudgets: Array
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
const alertBudgets = computed(() => props.alertBudgets || [])

const exportAlerts = () => {
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
