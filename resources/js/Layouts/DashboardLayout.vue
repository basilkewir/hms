<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useTheme } from '@/Composables/useTheme.js'
import { navigationConfig, iconPaths } from '@/Config/navigation.js'

const sidebarExpanded = ref(true), sidebarOpen = ref(false), sidebarHovered = ref(false), openSubmenus = ref([])
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
    else if (currentPath?.includes('/manager/operations') || currentPath?.includes('/manager/rooms') || currentPath?.includes('/manager/guests') || currentPath?.includes('/manager/reservations')) openSubmenus.value = ['manager-operations']
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
</script>

<template>
    <div class="min-h-screen" :style="{ backgroundColor: themeColors.background }">
        <aside class="fixed left-0 top-0 z-40 flex h-screen w-[290px] flex-col transition-all duration-300 ease-in-out xl:translate-x-0" :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen, 'xl:w-[290px]': sidebarExpanded || sidebarHovered, 'xl:w-[90px]': !sidebarExpanded && !sidebarHovered }" @mouseenter="handleMouseEnter" @mouseleave="handleMouseLeave">
            <div class="flex flex-col h-full border-r" :style="{ backgroundColor: themeColors.sidebar, borderColor: themeColors.border }">
                <div class="flex items-center justify-between px-6 py-5 border-b" :style="{ borderColor: themeColors.border }">
                    <a href="/" class="flex items-center gap-3">
                        <img class="w-8 h-8" src="/images/logo/logo-icon.svg" alt="Logo" />
                        <span v-show="sidebarExpanded || sidebarHovered" class="text-xl font-semibold" :style="{ color: themeColors.textPrimary }">Hotel Management</span>
                    </a>
                    <button v-show="sidebarExpanded || sidebarHovered" @click="sidebarExpanded = false" class="hidden xl:flex items-center justify-center w-8 h-8 rounded-lg transition-colors" :style="{ color: themeColors.textTertiary }"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg></button>
                </div>
                <nav class="flex-1 px-4 py-4 space-y-6 overflow-y-auto">
                    <template v-for="section in roleNavigation" :key="section.section">
                        <div v-if="checkCondition(section.condition)">
                            <h3 class="mb-2 text-xs font-semibold uppercase tracking-wider"
                                v-show="sidebarExpanded || sidebarHovered"
                                :style="{ color: themeColors.textTertiary }">{{ section.section }}</h3>
                            <!-- Flat direct-link items -->
                            <ul v-if="section.flat" class="space-y-1">
                                <li v-for="item in section.items" :key="item.label">
                                    <Link :href="resolveHref(item)"
                                          class="sidebar-menu-item"
                                          :class="isActive(resolveHref(item)) ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive'">
                                        <span class="sidebar-menu-icon">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIconPath(item.icon || 'home')"/>
                                            </svg>
                                        </span>
                                        <span class="sidebar-menu-text">{{ item.label }}</span>
                                    </Link>
                                </li>
                            </ul>
                            <!-- Collapsible submenu groups -->
                            <ul v-else class="space-y-1">
                                <li v-for="group in section.groups" :key="group.id">
                                    <button @click="toggleSubmenu(group.id)"
                                            class="sidebar-menu-item group w-full"
                                            :class="[isSubmenuOpen(group.id) ? 'sidebar-menu-item-active' : 'sidebar-menu-item-inactive']">
                                        <span class="sidebar-menu-icon">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIconPath(group.icon || 'home')"/>
                                            </svg>
                                        </span>
                                        <span class="sidebar-menu-text">{{ group.label }}</span>
                                        <span v-show="sidebarExpanded || sidebarHovered"
                                              class="sidebar-menu-arrow"
                                              :class="{ 'rotate-180': isSubmenuOpen(group.id) }">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                                    {{ item.label }}
                                                </Link>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </template>

                </nav>
            </div>
        </aside>
        <div class="flex-1 xl:ml-[290px]" :class="{ 'xl:ml-[90px]': !sidebarExpanded && !sidebarHovered, 'ml-0': !sidebarOpen && windowWidth < 1280 }">
            <header class="sticky top-0 z-30 border-b" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center justify-between px-4 py-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="xl:hidden p-2 rounded-lg" :style="{ color: themeColors.textTertiary }"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg></button>
                    <div class="flex-1 max-w-xl mx-4">
                        <div class="relative"><input type="text" placeholder="Search..." class="w-full pl-10 pr-4 py-2 border rounded-lg" :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" /><svg class="absolute left-3 top-2.5 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" :style="{ color: themeColors.textTertiary }"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></div>
                    </div>
                    <div class="flex items-center gap-4">
                        <Link href="/pos" class="p-2 rounded-lg" :style="{ color: themeColors.textTertiary }" title="POS Terminal">
                        <!-- New POS Terminal Icon - Cash Register -->
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18M5 21V7l8-4 8 4v14M9 21v-4h6v4M8 9h.01M16 9h.01M10 9h.01M8 13h.01M16 13h.01M10 13h.01M8 17h.01M16 17h.01M10 17h.01"/>
                            <path d="M9 5h6a1 1 0 011 1v2h-8V6a1 1 0 011-1z"/>
                        </svg>
                    </Link>
                        <button @click="toggleTheme" class="p-2 rounded-lg" :style="{ color: themeColors.textTertiary }"><svg v-if="!isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg><svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg></button>
                        <button class="p-2 rounded-lg" :style="{ color: themeColors.textTertiary }"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg></button>
                        <div id="profile-menu-container" class="relative">
                            <button @click="profileMenuOpen = !profileMenuOpen" class="flex items-center gap-2 p-2 rounded-lg">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-semibold" :style="{ backgroundColor: themeColors.primary, color: themeColors.background }">{{ userInitial }}</div>
                            </button>

                            <div v-show="profileMenuOpen" class="absolute right-0 mt-2 w-48 rounded-lg border shadow-lg overflow-hidden" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                                <Link href="/user/profile" class="block px-4 py-2 text-sm transition-colors" :style="{ color: themeColors.textPrimary }">Profile</Link>
                                <Link href="/logout" method="post" as="button" class="w-full text-left px-4 py-2 text-sm transition-colors" :style="{ color: themeColors.textPrimary }">Logout</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <main class="p-6"><slot /></main>
        </div>
        <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-30 xl:hidden" :style="{ backgroundColor: 'rgba(0, 0, 0, 0.5)' }"></div>
    </div>
</template>
