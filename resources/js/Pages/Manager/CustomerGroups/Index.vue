<template>
    <DashboardLayout title="Customer Groups" :user="user" :navigation="navigation">
        <!-- Header -->
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
             class="shadow rounded-lg p-6 mb-8 border">
            <div class="flex items-center justify-between">
                <div>
                    <h1 :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">Customer Groups Management</h1>
                    <p :style="{ color: themeColors.textSecondary }" class="mt-2">Manage customer groups and discount rates.</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('admin.customer-groups.create')" 
                          :style="{ backgroundColor: themeColors.primary, color: '#000' }"
                          class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity flex items-center">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Add New Group
                    </Link>
                    <button @click="exportCustomerGroups" 
                            :style="{ backgroundColor: themeColors.success, color: '#000' }"
                            class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity flex items-center">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                        Export Groups
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <UsersIcon :style="{ color: themeColors.primary }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Total Groups</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ stats.total }}</p>
                    </div>
                </div>
            </div>
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <CheckCircleIcon :style="{ color: themeColors.success }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Active</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ stats.active }}</p>
                    </div>
                </div>
            </div>
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <XCircleIcon :style="{ color: themeColors.danger }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Inactive</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ stats.inactive }}</p>
                    </div>
                </div>
            </div>
            <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
                 class="rounded-lg shadow p-6 border">
                <div class="flex items-center">
                    <UserGroupIcon :style="{ color: themeColors.primary }" class="h-8 w-8 mr-4" />
                    <div>
                        <p :style="{ color: themeColors.textSecondary }" class="text-sm font-medium">Total Customers</p>
                        <p :style="{ color: themeColors.textPrimary }" class="text-2xl font-bold">{{ stats.totalCustomers }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Groups Table -->
        <div :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }" 
             class="shadow rounded-lg overflow-hidden border">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead :style="{ backgroundColor: themeColors.background }">
                        <tr>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Name</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Description</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Discount</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Customers</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                            <th :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="group in customerGroups.data" :key="group.id"
                            :style="hoveredRow === group.id ? { backgroundColor: themeColors.hover } : {}"
                            @mouseenter="hoveredRow = group.id"
                            @mouseleave="hoveredRow = null"
                            class="transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div :style="{ color: themeColors.textPrimary }" class="text-sm font-medium">{{ group.name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div :style="{ color: themeColors.textSecondary }" class="text-sm">{{ group.description || 'No description' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div :style="{ color: themeColors.success }" class="text-sm font-semibold">{{ group.discount_percentage }}%</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div :style="{ color: themeColors.textPrimary }" class="text-sm">{{ group.customers_count || 0 }} customers</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="group.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    {{ group.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <Link :href="route('admin.customer-groups.show', group.id)" 
                                          :style="{ color: themeColors.primary }" 
                                          class="hover:opacity-80">View</Link>
                                    <Link :href="route('admin.customer-groups.edit', group.id)" 
                                          :style="{ color: themeColors.success }" 
                                          class="hover:opacity-80">Edit</Link>
                                    <button @click="deleteGroup(group)" 
                                            :style="{ color: themeColors.danger }" 
                                            class="hover:opacity-80">Delete</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="customerGroups.data.length === 0">
                            <td colspan="6" :style="{ color: themeColors.textSecondary }" 
                                class="px-6 py-4 text-center text-sm">
                                No customer groups found. Create your first group to get started.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="customerGroups.links && customerGroups.links.length > 3" 
                 :style="{ borderColor: themeColors.border }" 
                 class="px-4 py-3 border-t sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p :style="{ color: themeColors.textSecondary }" class="text-sm">
                                Showing <span class="font-medium">{{ customerGroups.from }}</span> to <span class="font-medium">{{ customerGroups.to }}</span> of <span class="font-medium">{{ customerGroups.total }}</span> results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <Link v-for="link in customerGroups.links" :key="link.label" 
                                      :href="link.url || '#'" 
                                      v-html="link.label"
                                      :class="[
                                          'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                          link.active 
                                            ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' 
                                            : 'border-gray-300 hover:bg-gray-50',
                                          link.url ? '' : 'cursor-not-allowed opacity-50'
                                      ]"
                                      :style="link.active ? {} : { backgroundColor: themeColors.card, color: themeColors.textSecondary }">
                                </Link>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'
import { PlusIcon, UsersIcon, CheckCircleIcon, XCircleIcon, UserGroupIcon, DocumentArrowDownIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    customerGroups: Object,
    stats: Object,
})

const { currentTheme } = useTheme()
const navigation = computed(() => {
    const userRole = props.user?.roles?.[0]?.name || 'admin'
    return getNavigationForRole(userRole)
})
const hoveredRow = ref(null)

const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    primary: `var(--kotel-primary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)'
}))

const exportCustomerGroups = () => {
    showExportDialog()
}

const showExportDialog = () => {
    // Create modal dialog
    const modal = document.createElement('div')
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50'
    
    // Build modal HTML using string concatenation
    let modalHTML = '<div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6" style="background-color: var(--kotel-card); color: var(--kotel-text-primary);">'
    modalHTML += '<h3 class="text-lg font-semibold mb-4">Choose Export Format</h3>'
    modalHTML += '<div class="space-y-3">'
    
    // CSV Button
    modalHTML += '<button onclick="exportCustomerGroupsData(\'csv\')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">'
    modalHTML += '<div class="flex items-center">'
    modalHTML += '<svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>'
    modalHTML += '<div><div class="font-medium">CSV</div><div class="text-sm text-gray-500">Excel-compatible spreadsheet format</div></div>'
    modalHTML += '</div>'
    modalHTML += '<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>'
    modalHTML += '</button>'
    
    // Excel Button
    modalHTML += '<button onclick="exportCustomerGroupsData(\'excel\')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">'
    modalHTML += '<div class="flex items-center">'
    modalHTML += '<svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v6a2 2 0 002 2h2m4-4h.01M17 16h.01"></path></svg>'
    modalHTML += '<div><div class="font-medium">Excel</div><div class="text-sm text-gray-500">HTML table for Excel import</div></div>'
    modalHTML += '</div>'
    modalHTML += '<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>'
    modalHTML += '</button>'
    
    // PDF Button
    modalHTML += '<button onclick="exportCustomerGroupsData(\'pdf\')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">'
    modalHTML += '<div class="flex items-center">'
    modalHTML += '<svg class="w-5 h-5 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>'
    modalHTML += '<div><div class="font-medium">PDF</div><div class="text-sm text-gray-500">Portable Document Format</div></div>'
    modalHTML += '</div>'
    modalHTML += '<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>'
    modalHTML += '</button>'
    
    // Word Button
    modalHTML += '<button onclick="exportCustomerGroupsData(\'word\')" class="w-full text-left px-4 py-3 rounded-lg border hover:bg-gray-50 transition-colors flex items-center justify-between" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">'
    modalHTML += '<div class="flex items-center">'
    modalHTML += '<svg class="w-5 h-5 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v6a2 2 0 002 2h2m4-4h.01M17 16h.01"></path></svg>'
    modalHTML += '<div><div class="font-medium">Word</div><div class="text-sm text-gray-500">Word document format</div></div>'
    modalHTML += '</div>'
    modalHTML += '<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>'
    modalHTML += '</button>'
    
    modalHTML += '</div>'
    modalHTML += '<div class="flex gap-3 mt-6">'
    modalHTML += '<button onclick="closeExportDialog()" class="flex-1 px-4 py-2 border rounded-lg hover:bg-gray-50 transition-colors" style="border-color: var(--kotel-border); color: var(--kotel-text-primary);">Cancel</button>'
    modalHTML += '</div>'
    modalHTML += '</div>'
    modalHTML += '</div>'
    
    modal.innerHTML = modalHTML
    
    // Make functions globally available
    window.exportCustomerGroupsData = (format) => {
        closeExportDialog()
        performCustomerGroupsExport(format)
    }
    
    window.closeExportDialog = () => {
        document.body.removeChild(modal)
        delete window.exportCustomerGroupsData
        delete window.closeExportDialog
    }
    
    // Close on backdrop click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeExportDialog()
        }
    })
    
    // Add modal to body
    document.body.appendChild(modal)
}

const performCustomerGroupsExport = (format) => {
    try {
        // Show loading notification
        showNotification('Preparing ' + format.toUpperCase() + ' export...', 'info')
        
        // Use backend export route
        const exportUrl = route('admin.customer-groups.export', { format: format })
        
        // Create a temporary link to download the file
        const link = document.createElement('a')
        link.href = exportUrl
        link.style.display = 'none'
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        
        // Show success message
        showNotification('Customer groups exported as ' + format.toUpperCase() + ' successfully!', 'success')
    } catch (error) {
        console.error('Export error:', error)
        showNotification('Failed to export as ' + format.toUpperCase(), 'error')
    }
}

const showNotification = (message, type = 'info') => {
    // Simple notification implementation
    const notification = document.createElement('div')
    var className = 'fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm text-white'
    if (type === 'success') {
        className += ' bg-green-500'
    } else if (type === 'error') {
        className += ' bg-red-500'
    } else {
        className += ' bg-blue-500'
    }
    notification.className = className
    notification.textContent = message
    document.body.appendChild(notification)
    
    setTimeout(() => {
        document.body.removeChild(notification)
    }, 3000)
}

const deleteGroup = (group) => {
    if (confirm('Are you sure you want to delete "' + group.name + '"?')) {
        router.delete(route('admin.customer-groups.destroy', group.id))
    }
}
</script>
