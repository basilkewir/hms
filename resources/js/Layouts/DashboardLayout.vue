<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useTheme } from '@/Composables/useTheme.js'
import { navigationConfig, iconPaths } from '@/Config/navigation.js'
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue'
import TutorialModal from '@/Components/TutorialModal.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

// Map raw navigation label strings to i18n keys.
// The label may have emoji prefixes (e.g. "📅 Reservations") — strip them first.
const labelToKey = {
    'Admin Dashboard': 'nav_items.admin_dashboard',
    'Manager Dashboard': 'nav_items.manager_dashboard',
    'Accountant Dashboard': 'nav_items.accountant_dashboard',
    'Front Desk Dashboard': 'nav_items.front_desk_dashboard',
    'Housekeeping Dashboard': 'nav_items.housekeeping_dashboard',
    'Maintenance Dashboard': 'nav_items.maintenance_dashboard',
    'HR Dashboard': 'nav_items.hr_dashboard',
    'Dashboard': 'nav_items.dashboard',
    'Reservations': 'nav_items.reservations',
    'Hall Bookings': 'nav_items.hall_bookings',
    'Guests': 'nav_items.guests',
    'Check-ins': 'nav_items.check_ins',
    'Check-outs': 'nav_items.check_outs',
    'Room Status': 'nav_items.room_status',
    'Waitlist': 'nav_items.waitlist',
    'Rooms': 'nav_items.rooms',
    'Room Types': 'nav_items.room_types',
    'Floors': 'nav_items.floors',
    'Building Wings': 'nav_items.building_wings',
    'Bed Types': 'nav_items.bed_types',
    'Halls': 'nav_items.halls',
    'Expenses': 'nav_items.expenses',
    'Expense Categories': 'nav_items.expense_categories',
    'Locations': 'nav_items.locations',
    'Suppliers': 'nav_items.suppliers',
    'Purchase Orders': 'nav_items.purchase_orders',
    'Products': 'nav_items.products',
    'Product Categories': 'nav_items.product_categories',
    'Categories': 'nav_items.categories',
    'Brands': 'nav_items.brands',
    'Units': 'nav_items.units',
    'Stock Overview': 'nav_items.stock_overview',
    'Stock Adjustments': 'nav_items.stock_adjustments',
    'Stock Transfers': 'nav_items.stock_transfers',
    'Stock Movements': 'nav_items.stock_movements',
    'Sales Register': 'nav_items.sales_register',
    'Orders': 'nav_items.orders',
    'Transactions': 'nav_items.transactions',
    'Sales Reports': 'nav_items.sales_reports',
    'Analytics': 'nav_items.analytics',
    'Users': 'nav_items.users',
    'Roles': 'nav_items.roles',
    'Customers': 'nav_items.customers',
    'Customer Groups': 'nav_items.customer_groups',
    'Guest Types': 'nav_items.guest_types',
    'Memberships': 'nav_items.memberships',
    'Services': 'nav_items.services',
    'Concierge': 'nav_items.concierge',
    'Service Charges': 'nav_items.service_charges',
    'Laundry': 'nav_items.laundry',
    'Packages': 'nav_items.packages',
    'Group Bookings': 'nav_items.group_bookings',
    'Maintenance Requests': 'nav_items.maintenance_requests',
    'Maintenance Categories': 'nav_items.maintenance_categories',
    'IPTV Devices': 'nav_items.iptv_devices',
    'Preventive Maintenance': 'nav_items.preventive_maintenance',
    'Work Shifts': 'nav_items.work_shifts',
    'Staff Schedules': 'nav_items.staff_schedules',
    'Housekeeping Schedules': 'nav_items.housekeeping_schedules',
    'Housekeeping Tasks': 'nav_items.housekeeping_tasks',
    'Time Tracking': 'nav_items.time_tracking',
    'Employees': 'nav_items.employees',
    'Departments': 'nav_items.departments',
    'Attendance': 'nav_items.attendance',
    'Payroll': 'nav_items.payroll',
    'HR Reports': 'nav_items.hr_reports',
    'Recruitment': 'nav_items.recruitment',
    'Leave Management': 'nav_items.leave_management',
    'Performance': 'nav_items.performance',
    'Training': 'nav_items.training',
    'Budget Dashboard': 'nav_items.budget_dashboard',
    'All Budgets': 'nav_items.all_budgets',
    'Create Budget': 'nav_items.create_budget',
    'Pending Approvals': 'nav_items.pending_approvals',
    'Reports': 'nav_items.grp_reports',
    'All Reports': 'nav_items.all_reports',
    'Occupancy Reports': 'nav_items.occupancy_reports',
    'Revenue Reports': 'nav_items.revenue_reports',
    'Alerts': 'nav_items.alerts',
    'Archived': 'nav_items.archived',
    'Invoices': 'nav_items.invoices',
    'Quotes': 'nav_items.quotes',
    'General Settings': 'nav_items.general_settings',
    'System Backup': 'nav_items.system_backup',
    'License': 'nav_items.license',
    'Overview': 'nav_items.overview',
    'All Reservations': 'nav_items.all_reservations',
    'New Reservation': 'nav_items.new_reservation',
    'Check In': 'nav_items.check_in',
    'Check Out': 'nav_items.check_out',
    'Channel Manager': 'nav_items.channel_manager',
    'Staff Management': 'nav_items.staff_management',
    'New Request': 'nav_items.new_request',
    'New Category': 'nav_items.new_category',
    'Comparison': 'nav_items.comparison',
    'Forecast': 'nav_items.forecast',
    'Profit & Loss': 'nav_items.profit_loss',
    'Balance Sheet': 'nav_items.balance_sheet',
    'Cash Flow': 'nav_items.cash_flow',
    'Revenue': 'nav_items.revenue',
    'Arrivals': 'nav_items.arrivals',
    'Departures': 'nav_items.departures',
    'Room Assignment': 'nav_items.room_assignment',
    'Payments': 'nav_items.payments',
    'Key Cards': 'nav_items.key_cards',
    'Housekeeping': 'nav_items.housekeeping',
    'Maintenance': 'nav_items.maintenance',
    'To Clean': 'nav_items.to_clean',
    'Daily Tasks': 'nav_items.daily_tasks',
    'Weekly Tasks': 'nav_items.weekly_tasks',
    'Task History': 'nav_items.task_history',
    'Supplies': 'nav_items.supplies',
    'Linens': 'nav_items.linens',
    'Amenities': 'nav_items.amenities',
    'Request Supplies': 'nav_items.request_supplies',
    'Report Maintenance': 'nav_items.report_maintenance',
    'Requests': 'nav_items.requests',
    'IPTV': 'nav_items.iptv',
    'Preventive': 'nav_items.preventive',
    'Drinks Menu': 'nav_items.drinks_menu',
    'Staff Reports': 'nav_items.staff_reports',
    'Financial': 'nav_items.grp_financial',
    'Accounting': 'nav_items.grp_accounting',
    'Operations': 'nav_items.grp_operations',
    'Property Management': 'nav_items.grp_property',
    'Purchase Management': 'nav_items.grp_purchase',
    'Employee': 'nav_items.grp_employee',
    'HR Management': 'nav_items.grp_hr',
    'Budget': 'nav_items.grp_budget',
    'Settings': 'nav_items.grp_settings',
    'Services & Requests': 'nav_items.grp_services_requests',
    'Invoicing': 'nav_items.grp_invoicing',
}

// Strip leading emojis/special chars and whitespace from nav labels, then look up key
function translateLabel(raw) {
    if (!raw) return raw
    // Remove leading emoji sequences and whitespace
    const stripped = raw.replace(/^[\p{Emoji}\s🏢🨺🧹🧹💼📅📋📊💰🛒🛍️👤👥🔐💳🏭🛎️🔧⏰📺⏱️🔑🔓🏛️⏳🌐🛏️🏠🏷️🗂️➕🚚🔄📈📉🔮🏖️🎓📚🔍📦🏪📏📉💸🏷🛒💡💾🔑🍹🍸📊🍽️]+/u, '').trim()
    const key = labelToKey[stripped]
    if (key) {
        const translated = t(key)
        // Preserve original emoji prefix if present
        const emojiMatch = raw.match(/^[\p{Emoji}\s🏢🨺🧹🧹💼📅📋📊💰🛒🛍️👤👥🔐💳🏭🛎️🔧⏰📺⏱️🔑🔓🏛️⏳🌐🛏️🏠🏷️🗂️➕🚚🔄📈📉🔮🏖️🎓📚🔍📦🏪📏📉💸🏷🛒💡💾🔑🍹🍸📊🍽️]+/u)
        return emojiMatch ? `${emojiMatch[0].trimEnd()} ${translated}` : translated
    }
    return raw
}

const sidebarExpanded = ref(true), sidebarOpen = ref(false), sidebarHovered = ref(false), openSubmenus = ref([])
const showTutorial = ref(false)
const page = usePage(), currentUrl = computed(() => page.props.url)
const user = computed(() => page.props.auth?.user || {})
const userRoles = computed(() => user.value?.roles?.map(r => r.name) || [])
const userPermissions = computed(() => { const perms = new Set(); user.value?.roles?.forEach(role => { role.permissions?.forEach(p => perms.add(p.name)) }); return Array.from(perms) })

const hasRole = (roles) => { if (!Array.isArray(roles)) roles = [roles]; return roles.some(role => userRoles.value.includes(role)) }
const hasPermission = (permissions) => { if (!Array.isArray(permissions)) permissions = [permissions]; return permissions.some(perm => userPermissions.value.includes(perm)) }
const canAccessPOS = computed(() => hasRole(['admin', 'manager', 'pos', 'accountant']) || hasPermission(['manage_pos', 'view_pos', 'manage_inventory', 'manage_purchases']))
const canManageInventory = computed(() => hasRole(['admin', 'manager', 'pos']) || hasPermission(['manage_inventory', 'manage_products']))
const canManageSuppliers = computed(() => hasRole(['admin', 'manager', 'pos']) || hasPermission(['manage_suppliers', 'manage_purchases']))
const canManagePurchases = computed(() => hasRole(['admin', 'manager', 'pos']) || hasPermission(['manage_purchases', 'approve_purchases']))
const isAdmin = computed(() => hasRole(['admin']))
const isAccountant = computed(() => hasRole(['accountant']))
const isManager = computed(() => hasRole(['manager']))
const isFrontDesk = computed(() => hasRole(['front_desk', 'frontdesk']))
const isHousekeeping = computed(() => hasRole(['housekeeping']))
const isMaintenance = computed(() => hasRole(['maintenance']))
const isHR = computed(() => hasRole(['hr']))

const profileMenuOpen = ref(false)
const userInitial = computed(() => {
    const name = user.value?.name || user.value?.full_name || ''
    const trimmed = String(name).trim()
    return trimmed ? trimmed[0].toUpperCase() : 'A'
})

const { currentTheme, loadTheme } = useTheme()
const themeColors = computed(() => ({ background: `var(--kotel-background)`, sidebar: `var(--kotel-sidebar)`, card: `var(--kotel-card)`, border: `var(--kotel-border)`, textPrimary: `var(--kotel-text-primary)`, textSecondary: `var(--kotel-text-secondary)`, textTertiary: `var(--kotel-text-tertiary)`, primary: `var(--kotel-primary)`, secondary: `var(--kotel-secondary)`, success: `var(--kotel-success)`, warning: `var(--kotel-warning)`, danger: `var(--kotel-danger)` }))

const windowWidth = ref(0), updateWindowWidth = () => { if (typeof window !== 'undefined') windowWidth.value = window.innerWidth }
const isActive = (url) => currentUrl.value === url
const toggleSubmenu = (menuId) => { const index = openSubmenus.value.indexOf(menuId); if (index > -1) openSubmenus.value.splice(index, 1); else openSubmenus.value = [menuId]; localStorage.setItem('openSubmenus', JSON.stringify(openSubmenus.value)) }
const isSubmenuOpen = (menuId) => openSubmenus.value.includes(menuId)
const loadSubmenuState = () => { const saved = localStorage.getItem('openSubmenus'); if (saved) { try { openSubmenus.value = JSON.parse(saved) } catch (e) { openSubmenus.value = [] } } }

const initializeActiveSubmenu = () => {
    const currentPath = currentUrl.value
    if (!currentPath) return

    if (currentPath?.includes('/hr/dashboard') || currentPath?.includes('/hr/employees') || currentPath?.includes('/hr/departments') || currentPath?.includes('/hr/attendance') || currentPath?.includes('/hr/payroll') || currentPath?.includes('/hr/reports')) openSubmenus.value = ['hr-management']
    else if (currentPath?.includes('/admin/reservations') || currentPath?.includes('/admin/guests') || currentPath?.includes('/admin/checkin') || currentPath?.includes('/admin/checkout') || currentPath?.includes('/admin/channel-manager') || currentPath?.includes('/admin/waitlist')) openSubmenus.value = ['operations']
    else if (currentPath?.includes('/admin/rooms') || currentPath?.includes('/admin/room-types') || currentPath?.includes('/admin/floors') || currentPath?.includes('/admin/building-wings') || currentPath?.includes('/admin/bed-types') || currentPath?.includes('/admin/halls') || currentPath?.includes('/admin/hall-bookings')) openSubmenus.value = ['property-management']
    else if (currentPath?.includes('/admin/services') || currentPath?.includes('/admin/reservations/service-charges') || currentPath?.includes('/admin/packages') || currentPath?.includes('/admin/group-bookings') || currentPath?.includes('/admin/laundry')) openSubmenus.value = ['services']
    else if (currentPath?.includes('/admin/maintenance') || currentPath?.includes('/admin/maintenance-requests') || currentPath?.includes('/admin/devices') || currentPath?.includes('/admin/maintenance/preventive') || currentPath?.includes('/admin/housekeeping-tasks') || currentPath?.includes('/admin/time-tracking')) openSubmenus.value = ['maintenance']
    else if (currentPath?.includes('/admin/work-shifts') || currentPath?.includes('/admin/schedules') || currentPath?.includes('/admin/housekeeping/schedules') || currentPath?.includes('/admin/departments') || currentPath?.includes('/admin/positions') || currentPath?.includes('/admin/attendance') || currentPath?.includes('/admin/performance')) openSubmenus.value = ['employee-management']
    else if (currentPath?.includes('/admin/users') || currentPath?.includes('/admin/departments') || currentPath?.includes('/admin/roles')) openSubmenus.value = ['user-management']
    else if (currentPath?.includes('/admin/transactions') || currentPath?.includes('/admin/expenses') || currentPath?.includes('/admin/budget')) openSubmenus.value = ['financial-management']
    else if (currentPath?.includes('/admin/reports') || currentPath?.includes('/analytics')) openSubmenus.value = ['reports']
    else if (currentPath?.includes('/admin/settings') || currentPath?.includes('/admin/settings/')) openSubmenus.value = ['settings']
    else if (currentPath?.includes('/accountant/reports')) openSubmenus.value = ['accountant-reports']
    else if (currentPath?.includes('/accountant/customers') || currentPath?.includes('/accountant/customer-groups')) openSubmenus.value = ['accountant-users']
    else if (currentPath?.includes('/accountant/transactions') || currentPath?.includes('/accountant/expenses') || currentPath?.includes('/accountant/invoices') || currentPath?.includes('/accountant/payroll')) openSubmenus.value = ['accountant-accounting']
    else if (currentPath?.includes('/manager/operations') || currentPath?.includes('/manager/rooms') || currentPath?.includes('/manager/guests') || currentPath?.includes('/manager/reservations') || currentPath?.includes('/manager/checkin') || currentPath?.includes('/manager/checkout')) openSubmenus.value = ['manager-operations']
    else if (currentPath?.includes('/manager/employee') || currentPath?.includes('/manager/staff') || currentPath?.includes('/manager/schedules')) openSubmenus.value = ['manager-employee']
    else if (currentPath?.includes('/manager/maintenance') || currentPath?.includes('/manager/housekeeping') || currentPath?.includes('/manager/iptv')) openSubmenus.value = ['manager-maintenance']
    else if (currentPath?.includes('/manager/reports')) openSubmenus.value = ['manager-reports']
    else if (currentPath?.includes('/front-desk/operations') || currentPath?.includes('/front-desk/reservations') || currentPath?.includes('/front-desk/guests') || currentPath?.includes('/front-desk/checkin') || currentPath?.includes('/front-desk/checkout')) openSubmenus.value = ['frontdesk-operations']
    else if (currentPath?.includes('/housekeeping/rooms') || currentPath?.includes('/housekeeping/tasks') || currentPath?.includes('/housekeeping/inventory')) openSubmenus.value = ['housekeeping']
    else if (currentPath?.includes('/maintenance/requests') || currentPath?.includes('/maintenance/devices') || currentPath?.includes('/maintenance/schedule') || currentPath?.includes('/maintenance/inventory')) openSubmenus.value = ['maintenance']
    else if (currentPath?.includes('/pos/products') || currentPath?.includes('/pos/categories') || currentPath?.includes('/pos/brands') || currentPath?.includes('/pos/units') || currentPath?.includes('/pos/warehouses')) openSubmenus.value = ['pos-products']
    else if (currentPath?.includes('/pos/inventory') || currentPath?.includes('/pos/adjustments') || currentPath?.includes('/pos/transfers') || currentPath?.includes('/pos/stock')) openSubmenus.value = ['pos-inventory']
    else if (currentPath?.includes('/pos/sales') || currentPath?.includes('/pos/orders') || currentPath?.includes('/pos/transactions')) openSubmenus.value = ['pos-sales']
    else if (currentPath?.includes('/pos/reports') || currentPath?.includes('/pos/analytics')) openSubmenus.value = ['pos-reports']
    else if (currentPath?.includes('/pos/settings') || currentPath?.includes('/pos/config')) openSubmenus.value = ['pos-settings']
    else if (currentPath?.includes('/front-desk/customers') || currentPath?.includes('/front-desk/customer-groups')) openSubmenus.value = ['frontdesk-users']
    else if (currentPath?.includes('/front-desk/rooms') || currentPath?.includes('/front-desk/room-assignment')) openSubmenus.value = ['frontdesk-rooms']
    else if (currentPath?.includes('/front-desk/payments')) openSubmenus.value = ['frontdesk-payments']
    else if (currentPath?.includes('/front-desk/key-cards')) openSubmenus.value = ['frontdesk-keycards']
    else if (currentPath?.includes('/front-desk/services')) openSubmenus.value = ['frontdesk-services']
    else if (currentPath?.includes('/front-desk/reports')) openSubmenus.value = ['frontdesk-reports']
    else if (currentPath?.includes('/server/reports')) openSubmenus.value = ['server-operations']
    else if (currentPath?.includes('/front-desk')) openSubmenus.value = ['frontdesk-operations']
    else if (currentPath?.includes('/maintenance')) openSubmenus.value = ['maintenance']
    else if (currentPath?.includes('/housekeeping')) openSubmenus.value = ['housekeeping']
}

const getSubmenuForCurrentRoute = () => {
    const currentPath = currentUrl.value

    if (currentPath?.includes('/hr/dashboard') || currentPath?.includes('/hr/employees') || currentPath?.includes('/hr/departments') || currentPath?.includes('/hr/attendance') || currentPath?.includes('/hr/payroll') || currentPath?.includes('/hr/reports')) return 'hr-management'
    if (currentPath?.includes('/pos/purchases') || currentPath?.includes('/pos/suppliers')) return 'purchase-management'
    if (currentPath?.includes('/pos')) return 'pos-management'
    if (currentPath?.includes('/admin/reservations') || currentPath?.includes('/admin/checkin') || currentPath?.includes('/admin/checkout') || currentPath?.includes('/admin/guests')) return 'operations'
    if (currentPath?.includes('/admin/customers')) return 'user-management'
    if (currentPath?.includes('/admin/rooms')) return 'property-management'
    if (currentPath?.includes('/admin/halls') || currentPath?.includes('/admin/hall-bookings')) return 'property-management'
    if (currentPath?.includes('/admin/housekeeping-tasks')) return 'admin-housekeeping'
    if (currentPath?.includes('/admin/maintenance-requests')) return 'admin-maintenance'
    if (currentPath?.includes('/admin/services')) return 'admin-services'
    if (currentPath?.includes('/admin/work-shifts') || currentPath?.includes('/admin/schedules') || currentPath?.includes('/admin/housekeeping/schedules')) return 'admin-employee'
    if (currentPath?.includes('/admin/users') || currentPath?.includes('/admin/departments') || currentPath?.includes('/admin/roles')) return 'user-management'
    if (currentPath?.includes('/admin/transactions') || currentPath?.includes('/admin/expenses') || currentPath?.includes('/admin/budget')) return 'financial-management'
    if (currentPath?.includes('/admin/reports')) return 'reports'
    if (currentPath?.includes('/admin/settings')) return 'settings'
    if (currentPath?.includes('/accountant/customers') || currentPath?.includes('/accountant/customer-groups')) return 'accountant-users'
    if (currentPath?.includes('/accountant/transactions') || currentPath?.includes('/accountant/expenses') || currentPath?.includes('/accountant/invoices') || currentPath?.includes('/accountant/payroll')) return 'accountant-accounting'
    if (currentPath?.includes('/accountant/reports')) return 'accountant-reports'
    if (currentPath?.includes('/accountant/budget')) return 'accountant-financial'
    if (currentPath?.includes('/accountant')) return 'accountant-financial'
    if (currentPath?.includes('/manager/staff/schedules') || currentPath?.includes('/manager/staff/time-tracking') || currentPath?.includes('/manager/staff')) return 'manager-employee'
    if (currentPath?.includes('/manager/guests')) return 'manager-operations'
    if (currentPath?.includes('/manager/rooms')) return 'manager-rooms'
    if (currentPath?.includes('/manager/housekeeping-tasks') || currentPath?.includes('/manager/maintenance-requests')) return 'manager-maintenance'
    if (currentPath?.includes('/manager/customers') || currentPath?.includes('/manager/customer-groups')) return 'manager-users'
    if (currentPath?.includes('/manager/reports')) return 'manager-reports'
    if (currentPath?.includes('/manager')) return 'manager-operations'
    if (currentPath?.includes('/front-desk/reservations')) return 'frontdesk-operations'
    if (currentPath?.includes('/front-desk/guests')) return 'frontdesk-operations'
    if (currentPath?.includes('/front-desk/customers') || currentPath?.includes('/front-desk/customer-groups')) return 'frontdesk-users'
    if (currentPath?.includes('/front-desk/rooms') || currentPath?.includes('/front-desk/room-assignment')) return 'frontdesk-rooms'
    if (currentPath?.includes('/front-desk/payments')) return 'frontdesk-payments'
    if (currentPath?.includes('/front-desk/key-cards')) return 'frontdesk-keycards'
    if (currentPath?.includes('/front-desk/services')) return 'frontdesk-services'
    if (currentPath?.includes('/front-desk/reports')) return 'frontdesk-reports'
    if (currentPath?.includes('/server/reports')) return 'server-operations'
    if (currentPath?.includes('/front-desk')) return 'frontdesk-operations'
    if (currentPath?.includes('/maintenance')) return 'maintenance'
    if (currentPath?.includes('/housekeeping')) return 'housekeeping'
    return null
}

const isDark = ref(false), toggleTheme = () => { isDark.value = !isDark.value; localStorage.setItem('theme', isDark.value ? 'dark' : 'light'); updateTheme() }
const updateTheme = () => { if (isDark.value) document.documentElement.classList.add('dark'); else document.documentElement.classList.remove('dark') }
const loadThemeSettings = () => { const savedTheme = localStorage.getItem('theme'); if (savedTheme) isDark.value = savedTheme === 'dark'; else if (typeof window !== 'undefined') isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches; updateTheme() }
const handleMouseEnter = () => { if (typeof window !== 'undefined' && !sidebarExpanded.value && window.innerWidth >= 1280) sidebarHovered.value = true }
const handleMouseLeave = () => { sidebarHovered.value = false }

const handleClickOutsideProfileMenu = (event) => {
    if (!profileMenuOpen.value) return
    const target = event?.target
    if (!(target instanceof Element)) return
    const container = document.getElementById('profile-menu-container')
    if (container && !container.contains(target)) profileMenuOpen.value = false
}

onMounted(() => {
    loadThemeSettings();
    updateWindowWidth();
    loadSubmenuState();
    initializeActiveSubmenu();
    loadTheme();
    if (typeof window !== 'undefined') {
        window.addEventListener('resize', updateWindowWidth)
        window.addEventListener('click', handleClickOutsideProfileMenu)
    }
})

onUnmounted(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('resize', updateWindowWidth)
        window.removeEventListener('click', handleClickOutsideProfileMenu)
    }
})
watch(currentUrl, () => { const currentSubmenu = getSubmenuForCurrentRoute(); if (currentSubmenu && !openSubmenus.value.includes(currentSubmenu)) { openSubmenus.value = [currentSubmenu]; localStorage.setItem('openSubmenus', JSON.stringify(openSubmenus.value)) } }, { immediate: false })

const primaryRole = computed(() => {
    if (userRoles.value.includes('admin'))       return 'admin'
    if (userRoles.value.includes('manager'))     return 'manager'
    if (userRoles.value.includes('accountant'))  return 'accountant'
    if (userRoles.value.includes('front_desk') || userRoles.value.includes('frontdesk')) return 'front_desk'
    if (userRoles.value.includes('housekeeping')) return 'housekeeping'
    if (userRoles.value.includes('maintenance')) return 'maintenance'
    if (userRoles.value.includes('bartender'))   return 'bartender'
    if (userRoles.value.includes('server') || userRoles.value.includes('restaurant_staff')) return 'server'
    if (userRoles.value.includes('hr'))          return 'hr'
    return userRoles.value[0] || 'staff'
})

const roleNavigation = computed(() => navigationConfig[primaryRole.value] || [])

const checkCondition = (condition) => {
    if (!condition) return true
    const map = {
        isAdmin:                isAdmin.value,
        isManager:              isManager.value,
        isAccountant:           isAccountant.value,
        isFrontDesk:            isFrontDesk.value,
        isHousekeeping:         isHousekeeping.value,
        isMaintenance:          isMaintenance.value,
        isHR:                   isHR.value,
        isAdminOrHR:            isAdmin.value || isHR.value,
        canAccessPOS:           canAccessPOS.value,
        isAdminWithPurchases:   (isAdmin.value || isManager.value) && canManagePurchases.value,
        isManagerWithPurchases: isManager.value && canManagePurchases.value,
        hasBudgetPermission:    hasPermission(['view_budgets']),
    }
    return map[condition] ?? true
}

const resolveHref = (item) => {
    if (item.routeName) {
        try { return route(item.routeName) } catch (e) { return item.href || '#' }
    }
    return item.href || '#'
}

const getIconPath = (iconName) => iconPaths[iconName] || iconPaths.home

const statusDate = computed(() => new Date().toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' }))

const roleLabel = computed(() => {
    const map = { admin: 'Admin', manager: 'Manager', accountant: 'Accountant', front_desk: 'Front Desk', housekeeping: 'Housekeeping', maintenance: 'Maintenance', bartender: 'Bartender', server: 'Server', hr: 'HR' }
    return map[primaryRole.value] || (primaryRole.value.charAt(0).toUpperCase() + primaryRole.value.slice(1))
})
</script>

<template>
    <div class="min-h-screen" :style="{ backgroundColor: themeColors.background }">
        <aside class="fixed left-0 top-0 z-40 flex h-screen w-[240px] flex-col transition-all duration-200 ease-in-out xl:translate-x-0" :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen, 'xl:w-[240px]': sidebarExpanded || sidebarHovered, 'xl:w-[52px]': !sidebarExpanded && !sidebarHovered }" @mouseenter="handleMouseEnter" @mouseleave="handleMouseLeave">
            <div class="flex flex-col h-full border-r" :style="{ backgroundColor: themeColors.sidebar, borderColor: themeColors.border }">
                <div class="flex items-center justify-between px-3 py-2.5 border-b" :style="{ borderColor: themeColors.border }">
                    <a href="/" class="flex items-center gap-2.5 min-w-0">
                        <img class="w-6 h-6 flex-shrink-0" src="/images/logo/logo-icon.svg" alt="Logo" />
                        <span v-show="sidebarExpanded || sidebarHovered" class="text-[11px] font-bold tracking-widest uppercase whitespace-nowrap overflow-hidden" :style="{ color: themeColors.textPrimary }">Hotel Management</span>
                    </a>
                    <button v-show="sidebarExpanded || sidebarHovered" @click="sidebarExpanded = false" class="hidden xl:flex items-center justify-center w-6 h-6 rounded transition-colors flex-shrink-0" :style="{ color: themeColors.textTertiary }"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg></button>
                </div>
                <nav class="flex-1 px-2 py-2 space-y-0 overflow-y-auto overflow-x-hidden">
                    <template v-for="section in roleNavigation" :key="section.section">
                        <div v-if="checkCondition(section.condition)">
                            <h3 class="mt-3 mb-0.5 text-[9px] font-bold uppercase tracking-widest px-2"
                                v-show="sidebarExpanded || sidebarHovered"
                                :style="{ color: themeColors.textTertiary }">{{ translateLabel(section.section) }}</h3>
                            <!-- Flat direct-link items -->
                            <ul v-if="section.flat" class="space-y-0">
                                <li v-for="item in section.items" :key="item.label">
                                    <!-- Tutorial trigger item -->
                                    <button v-if="item.tutorial"
                                        @click="showTutorial = true"
                                        class="sidebar-menu-item sidebar-menu-item-inactive w-full text-left"
                                    >
                                        <span class="sidebar-menu-icon">
                                            <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIconPath(item.icon || 'help')"/>
                                            </svg>
                                        </span>
                                        <span class="sidebar-menu-text">{{ translateLabel(item.label) }}</span>
                                    </button>
                                    <!-- Normal nav link -->
                                    <Link v-else :href="resolveHref(item)"
                                          class="sidebar-menu-item"
                                          :class="isActive(resolveHref(item)) ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive'">
                                        <span class="sidebar-menu-icon">
                                            <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIconPath(item.icon || 'home')"/>
                                            </svg>
                                        </span>
                                        <span class="sidebar-menu-text">{{ translateLabel(item.label) }}</span>
                                    </Link>
                                </li>
                            </ul>
                            <!-- Collapsible submenu groups -->
                            <ul v-else class="space-y-0">
                                <li v-for="group in section.groups" :key="group.id">
                                    <button @click="toggleSubmenu(group.id)"
                                            class="sidebar-menu-item group w-full"
                                            :class="[isSubmenuOpen(group.id) ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive']">
                                        <span class="sidebar-menu-icon">
                                            <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIconPath(group.icon || 'home')"/>
                                            </svg>
                                        </span>
                                        <span class="sidebar-menu-text">{{ translateLabel(group.label) }}</span>
                                        <span v-show="sidebarExpanded || sidebarHovered"
                                              class="sidebar-menu-arrow"
                                              :class="{ 'rotate-180': isSubmenuOpen(group.id) }">
                                            <svg class="w-[11px] h-[11px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </span>
                                    </button>
                                    <div v-show="isSubmenuOpen(group.id) && (sidebarExpanded || sidebarHovered)"
                                         class="sidebar-submenu">
                                        <ul class="space-y-1">
                                            <li v-for="item in group.items" :key="item.label">
                                                <Link :href="resolveHref(item)"
                                                      class="sidebar-submenu-item"
                                                      :class="isActive(resolveHref(item)) ? 'sidebar-submenu-item-active' : 'sidebar-submenu-item-inactive'">
                                                    {{ translateLabel(item.label) }}
                                                </Link>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </template>

                </nav>

                <!-- Help & Tutorial button pinned to sidebar bottom -->
                <div class="flex-shrink-0 border-t px-2 py-2" :style="{ borderColor: themeColors.border }">
                    <button
                        @click="showTutorial = true"
                        class="flex items-center gap-2.5 w-full px-2 py-1.5 rounded-[3px] transition-colors text-[12px] font-medium hover:opacity-80"
                        :style="{ color: themeColors.textSecondary }"
                        title="Help & Tutorial"
                    >
                        <svg class="w-[15px] h-[15px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3m0 4h.01"/></svg>
                        <span v-show="sidebarExpanded || sidebarHovered" class="whitespace-nowrap">Help &amp; Tutorial</span>
                    </button>
                </div>
            </div>
        </aside>

        <TutorialModal :show="showTutorial" @close="showTutorial = false" />
        <div class="flex-1 xl:ml-[240px]" :class="{ 'xl:ml-[52px]': !sidebarExpanded && !sidebarHovered, 'ml-0': !sidebarOpen && windowWidth < 1280 }">
            <header class="sticky top-0 z-30 border-b flex items-center h-10" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center justify-between px-3 w-full h-full gap-2">
                    <!-- Mobile hamburger -->
                    <button @click="sidebarOpen = !sidebarOpen" class="xl:hidden p-1.5 rounded" :style="{ color: themeColors.textTertiary }">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <!-- Expand sidebar button (desktop collapsed only) -->
                    <button v-if="!sidebarExpanded && !sidebarHovered" @click="sidebarExpanded = true" class="hidden xl:flex p-1.5 rounded" :style="{ color: themeColors.textTertiary }">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                    </button>
                    <!-- Command palette trigger -->
                    <button class="hidden sm:flex items-center gap-2 px-2.5 py-1 rounded text-[11px] border transition-colors"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textTertiary }">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <span>Search...</span>
                        <span class="ml-2 px-1 rounded text-[9px] border opacity-60" :style="{ borderColor: themeColors.border }">⌘K</span>
                    </button>
                    <div class="flex-1"></div>
                    <!-- Action toolbar -->
                    <div class="flex items-center gap-1">
                        <button @click="showTutorial = true" class="p-1.5 rounded transition-colors hover:opacity-80" :style="{ color: themeColors.textTertiary }" title="Help">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3m0 4h.01"/></svg>
                        </button>
                        <Link href="/pos" class="p-1.5 rounded" :style="{ color: themeColors.textTertiary }" title="POS Terminal">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8m-4-4v4"/></svg>
                        </Link>
                        <button @click="toggleTheme" class="p-1.5 rounded" :style="{ color: themeColors.textTertiary }" :title="isDark ? 'Light mode' : 'Dark mode'">
                            <svg v-if="!isDark" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </button>
                        <LanguageSwitcher :style="{ color: themeColors.textPrimary }" />
                        <button class="p-1.5 rounded" :style="{ color: themeColors.textTertiary }" title="Notifications">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        </button>
                        <!-- Thin divider -->
                        <span class="w-px h-5 mx-1" :style="{ backgroundColor: themeColors.border }"></span>
                        <div id="profile-menu-container" class="relative">
                            <button @click="profileMenuOpen = !profileMenuOpen" class="flex items-center gap-1.5 px-1.5 py-1 rounded transition-colors">
                                <div class="w-6 h-6 rounded flex items-center justify-center text-[11px] font-bold" :style="{ backgroundColor: themeColors.primary, color: themeColors.background }">{{ userInitial }}</div>
                                <span class="hidden sm:block text-[11px] font-medium max-w-[100px] truncate" :style="{ color: themeColors.textSecondary }">{{ user.name }}</span>
                                <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24" :style="{ color: themeColors.textTertiary }"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div v-show="profileMenuOpen" class="absolute right-0 mt-1 w-44 rounded border shadow-lg overflow-hidden z-50" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                                <div class="px-3 py-2 border-b" :style="{ borderColor: themeColors.border }">
                                    <p class="text-[11px] font-semibold truncate" :style="{ color: themeColors.textPrimary }">{{ user.name }}</p>
                                    <p class="text-[10px] truncate" :style="{ color: themeColors.textTertiary }">{{ roleLabel }}</p>
                                </div>
                                <Link href="/user/profile" class="block px-3 py-1.5 text-[12px] transition-colors hover:opacity-80" :style="{ color: themeColors.textPrimary }">{{ $t('nav.profile') }}</Link>
                                <Link href="/logout" method="post" as="button" class="w-full text-left px-3 py-1.5 text-[12px] transition-colors hover:opacity-80" :style="{ color: themeColors.textPrimary }">{{ $t('auth.logout') }}</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <main class="p-4 pb-10"><slot /></main>
        </div>
        <!-- Status Bar -->
        <div class="fixed bottom-0 left-0 right-0 z-20 h-6 border-t flex items-center text-[10px] select-none"
             :style="{ backgroundColor: themeColors.sidebar, borderColor: themeColors.border, paddingLeft: (sidebarExpanded || sidebarHovered) ? '248px' : '56px', paddingRight: '12px' }">
            <span class="flex items-center gap-1.5" :style="{ color: themeColors.textTertiary }">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block flex-shrink-0"></span>
                Online
            </span>
            <span class="mx-2 opacity-30" :style="{ color: themeColors.textTertiary }">|</span>
            <span :style="{ color: themeColors.textTertiary }">{{ roleLabel }}</span>
            <span class="mx-2 opacity-30" :style="{ color: themeColors.textTertiary }">|</span>
            <span :style="{ color: themeColors.textTertiary }">{{ user.name }}</span>
            <span class="ml-auto" :style="{ color: themeColors.textTertiary }">{{ statusDate }}</span>
        </div>
        <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-30 xl:hidden" :style="{ backgroundColor: 'rgba(0, 0, 0, 0.5)' }"></div>
    </div>
</template>
