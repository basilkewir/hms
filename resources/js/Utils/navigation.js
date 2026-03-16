// Role-based navigation system - Updated 2025-02-11
// Maps routes to permissions based on web.php route definitions

export const getNavigationForRole = (role, userPermissions = []) => {
    // Handle if role is an object (extract role name)
    let roleString = role;
    if (typeof role === 'object' && role !== null) {
        roleString = role.roles?.[0]?.name || role.name || 'admin';
    }

    // Normalize role name
    const normalizedRole = (roleString || '').toLowerCase().replace(/\s+/g, '_');

    // Define navigation items with their required permissions
    // Admin has all permissions
    const isAdmin = normalizedRole === 'admin';

    // Admin gets all permissions
    const allPermissions = isAdmin
        ? ['manage_users', 'manage_roles', 'manage_staff', 'manage_guests', 'manage_reservations', 'manage_rooms', 'view_rooms', 'check_in_out', 'manage_financials', 'manage_expenses', 'view_budgets', 'manage_budgets', 'approve_expenses', 'manage_payroll', 'view_reports', 'access_pos', 'process_sales', 'manage_purchases', 'manage_inventory', 'manage_suppliers', 'manage_supplies', 'view_pos_reports', 'manage_cash_drawer', 'manage_services', 'manage_maintenance', 'view_schedule', 'manage_settings', 'view_logs', 'manage_iptv', 'manage_iptv_devices', 'view_iptv', 'manage_invoices', 'manage_payments', 'process_refunds']
        : userPermissions;

    // All possible navigation items
    const allNavigationItems = [
        // ==================== DASHBOARD ====================
        {
            name: 'Dashboard',
            href: isAdmin ? '/admin/dashboard' : `/${normalizedRole}/dashboard`,
            icon: 'HomeIcon',
            permission: null,
            roles: ['admin', 'manager', 'accountant', 'front_desk', 'housekeeping', 'maintenance']
        },

        // ==================== OPERATIONS ====================
        {
            name: 'Operations',
            icon: 'CogIcon',
            permission: null,
            roles: ['admin', 'manager', 'front_desk'],
            children: [
                { name: 'Overview',          href: `/${normalizedRole}/operations`,                permission: null,                 roles: ['manager'] },
                { name: 'All Reservations',  href: isAdmin ? '/admin/reservations'  : `/${normalizedRole}/reservations`,         permission: 'manage_reservations', roles: ['admin', 'manager', 'front_desk'] },
                { name: 'Check-ins Today',   href: `/${normalizedRole}/reservations/checkins`,   permission: 'manage_reservations', roles: ['manager'] },
                { name: 'Check-outs Today',  href: `/${normalizedRole}/reservations/checkouts`,  permission: 'manage_reservations', roles: ['manager'] },
                { name: 'New Reservation',   href: isAdmin ? '/admin/reservations/create' : `/${normalizedRole}/reservations/create`, permission: 'manage_reservations', roles: ['admin', 'manager', 'front_desk'] },
                { name: 'Check-in',          href: isAdmin ? '/admin/checkin'  : `/${normalizedRole}/checkin`,  permission: 'check_in_out', roles: ['admin', 'manager', 'front_desk'] },
                { name: 'Check-out',         href: isAdmin ? '/admin/checkout' : `/${normalizedRole}/checkout`, permission: 'check_in_out', roles: ['admin', 'manager', 'front_desk'] },
                { name: 'Room Assignment',   href: isAdmin ? '/admin/room-assignment' : `/${normalizedRole}/room-assignment`, permission: 'manage_rooms', roles: ['admin', 'manager', 'front_desk'] },
                { name: 'Key Cards',         href: isAdmin ? '/admin/key-cards' : `/${normalizedRole}/key-cards`, permission: 'manage_rooms', roles: ['admin', 'manager', 'front_desk'] },
                { name: 'Housekeeping Tasks', href: isAdmin ? '/admin/housekeeping-tasks' : `/${normalizedRole}/housekeeping-tasks`, permission: 'view_rooms', roles: ['admin', 'manager'] },
                { name: 'Maintenance',        href: isAdmin ? '/admin/maintenance-requests' : `/${normalizedRole}/maintenance-requests`, permission: 'manage_maintenance', roles: ['admin', 'manager'] },
                { name: 'Laundry',            href: isAdmin ? '/admin/laundry' : `/${normalizedRole}/laundry`, permission: 'manage_services', roles: ['admin', 'manager'] },
                { name: 'Waitlist',          href: isAdmin ? '/admin/waitlist'         : `/${normalizedRole}/waitlist`,         permission: 'manage_reservations', roles: ['admin', 'manager'] },
                { name: 'Group Bookings',    href: isAdmin ? '/admin/group-bookings'   : `/${normalizedRole}/group-bookings`,   permission: 'manage_reservations', roles: ['admin', 'manager'] },
                { name: 'Channel Manager',   href: isAdmin ? '/admin/channel-manager'  : `/${normalizedRole}/channel-manager`,  permission: 'manage_reservations', roles: ['admin', 'manager'] },
            ]
        },

        // ==================== ROOM / PROPERTY MANAGEMENT ====================
        {
            name: 'Property Management',
            icon: 'BuildingOfficeIcon',
            permission: 'manage_rooms',
            roles: ['admin', 'manager'],
            children: [
                { name: 'All Rooms',          href: isAdmin ? '/admin/rooms'          : `/${normalizedRole}/rooms`,              permission: 'manage_rooms', roles: ['admin', 'manager'] },
                { name: 'Room Status',        href: isAdmin ? '/admin/rooms/status'   : `/${normalizedRole}/rooms/status`,       permission: 'view_rooms',   roles: ['admin', 'manager'] },
                { name: 'Housekeeping Rooms', href: `/${normalizedRole}/rooms/housekeeping`, permission: 'view_rooms',              roles: ['manager'] },
                { name: 'Maintenance Rooms',  href: `/${normalizedRole}/rooms/maintenance`,  permission: 'manage_maintenance',      roles: ['manager'] },
                { name: 'Room Types',         href: isAdmin ? '/admin/room-types'     : `/${normalizedRole}/room-types`,         permission: 'manage_rooms', roles: ['admin', 'manager'] },
                { name: 'Room Amenities',     href: isAdmin ? '/admin/room-amenities' : `/${normalizedRole}/room-amenities`,     permission: 'manage_rooms', roles: ['admin', 'manager'] },
                { name: 'Floors',             href: isAdmin ? '/admin/floors'         : `/${normalizedRole}/floors`,             permission: 'manage_rooms', roles: ['admin', 'manager'] },
                { name: 'Halls',              href: isAdmin ? '/admin/halls'          : `/${normalizedRole}/halls`,              permission: 'manage_rooms', roles: ['admin', 'manager'] },
                { name: 'Hall Bookings',      href: isAdmin ? '/admin/hall-bookings'  : `/${normalizedRole}/hall-bookings`,      permission: 'manage_rooms', roles: ['admin', 'manager'] },
            ]
        },

        // ==================== PURCHASE MANAGEMENT (after Property) ====================
        {
            name: 'Purchase Management',
            icon: 'ShoppingBagIcon',
            permission: 'manage_purchases',
            roles: ['admin', 'manager'],
            children: [
                { name: 'Purchase Orders', href: '/pos/purchases', permission: 'manage_purchases', roles: ['admin', 'manager'] },
                { name: 'Suppliers', href: '/pos/suppliers', permission: 'manage_suppliers', roles: ['admin', 'manager'] },
            ]
        },

        // ==================== HOUSEKEEPING ====================
        {
            name: 'Housekeeping',
            icon: 'SparklesIcon',
            permission: 'view_rooms',
            roles: ['admin', 'manager', 'housekeeping'],
            children: [
                { name: 'Tasks',       href: isAdmin ? '/admin/housekeeping-tasks'        : `/${normalizedRole}/housekeeping-tasks`,        permission: 'view_rooms', roles: ['admin', 'manager'] },
                { name: 'Create Task', href: isAdmin ? '/admin/housekeeping-tasks/create' : `/${normalizedRole}/housekeeping-tasks/create`, permission: 'view_rooms', roles: ['admin', 'manager'] },
                { name: 'My Tasks',    href: '/housekeeping/rooms', permission: null, roles: ['housekeeping'] },
            ]
        },

        // ==================== MAINTENANCE ====================
        {
            name: 'Maintenance',
            icon: 'WrenchScrewdriverIcon',
            permission: 'manage_maintenance',
            roles: ['admin', 'manager', 'maintenance'],
            children: [
                { name: 'Requests', href: isAdmin ? '/admin/maintenance-requests' : `/${normalizedRole}/maintenance-requests`, permission: 'manage_maintenance', roles: ['admin', 'manager'] },
                { name: 'Categories', href: isAdmin ? '/admin/maintenance-categories' : `/${normalizedRole}/maintenance-categories`, permission: 'manage_maintenance', roles: ['admin', 'manager'] },
                { name: 'Dashboard', href: '/maintenance/dashboard', permission: null, roles: ['maintenance'] },
                { name: 'Work Orders', href: '/maintenance/work-orders', permission: null, roles: ['maintenance'] },
                { name: 'IPTV', href: '/admin/iptv/devices', permission: 'manage_iptv', roles: ['admin'] },
                { name: 'Preventive', href: '/maintenance/preventive/scheduled', permission: null, roles: ['maintenance'] },
                { name: 'Time Tracking', href: isAdmin ? '/admin/time-tracking' : `/${normalizedRole}/staff/time-tracking`, permission: null, roles: ['admin', 'manager', 'maintenance'] },
                { name: 'Report Issue', href: '/housekeeping/maintenance/report', permission: null, roles: ['housekeeping'] },
            ]
        },



        // ==================== SERVICES ====================
        {
            name: 'Services',
            icon: 'SparkleIcon',
            permission: 'manage_services',
            roles: ['admin', 'manager', 'front_desk'],
            children: [
                { name: 'Concierge', href: isAdmin ? '/admin/services/concierge' : `/${normalizedRole}/services/concierge`, permission: null, roles: ['admin', 'manager', 'front_desk'] },
                { name: 'Housekeeping Requests', href: '/front-desk/services/housekeeping', permission: null, roles: ['front_desk'] },
                { name: 'Maintenance Requests', href: '/front-desk/services/maintenance', permission: null, roles: ['front_desk'] },
            ]
        },

        // ==================== TRANSACTIONS & PAYMENTS ====================
        {
            name: 'Transactions',
            icon: 'CreditCardIcon',
            permission: 'manage_financials',
            roles: ['admin', 'accountant'],
            children: [
                { name: 'All Transactions', href: '/admin/transactions', permission: 'manage_financials', roles: ['admin', 'accountant'] },
                { name: 'Payments', href: '/accountant/transactions/payments', permission: null, roles: ['accountant'] },
                { name: 'Refunds', href: '/accountant/transactions/refunds', permission: null, roles: ['accountant'] },
                { name: 'Pending', href: '/accountant/transactions/pending', permission: null, roles: ['accountant'] },
                { name: 'Process Payment', href: '/front-desk/payments/process', permission: null, roles: ['front_desk'] },
            ]
        },

        // ==================== EXPENSES ====================
        {
            name: 'Expenses',
            icon: 'BanknotesIcon',
            permission: 'manage_expenses',
            roles: ['admin', 'manager', 'accountant', 'front_desk'],
            children: [
                { name: 'All Expenses', href: isAdmin ? '/admin/expenses' : `/${normalizedRole}/expenses`, permission: 'manage_expenses', roles: ['admin', 'manager', 'accountant', 'front_desk'] },
                { name: 'Create Expense', href: isAdmin ? '/admin/expenses/create' : `/${normalizedRole}/expenses/create`, permission: 'manage_expenses', roles: ['admin', 'manager', 'front_desk'] },
                { name: 'Categories', href: isAdmin ? '/admin/expenses/categories' : `/${normalizedRole}/expenses/categories`, permission: 'manage_expenses', roles: ['admin', 'accountant'] },
                { name: 'Reports', href: '/accountant/expenses/reports', permission: null, roles: ['accountant'] },
            ]
        },

        // ==================== BUDGET ====================
        {
            name: 'Budget Management',
            icon: 'ChartBarIcon',
            permission: 'view_budgets',
            roles: ['admin', 'manager', 'accountant'],
            children: [
                { name: 'Budget Dashboard',  href: isAdmin ? '/admin/budget/dashboard'                  : `/${normalizedRole}/budget/dashboard`,                  permission: 'view_budgets',    roles: ['admin', 'manager'] },
                { name: 'All Budgets',       href: isAdmin ? '/admin/budget'                            : `/${normalizedRole}/budget`,                            permission: 'view_budgets',    roles: ['admin', 'manager'] },
                { name: 'Create Budget',     href: isAdmin ? '/admin/budget/create'                     : `/${normalizedRole}/budget/create`,                     permission: 'manage_budgets',  roles: ['admin', 'manager'] },
                { name: 'Budget Reports',    href: isAdmin ? '/admin/budget/reports'                    : `/${normalizedRole}/budget/reports`,                    permission: 'view_budgets',    roles: ['admin', 'manager'] },
                { name: 'Budget Expenses',   href: isAdmin ? '/admin/budget/expenses'                   : `/${normalizedRole}/budget/expenses`,                   permission: 'view_budgets',    roles: ['admin', 'manager'] },
                { name: 'Expense Approvals', href: isAdmin ? '/admin/budget/expenses/pending-approvals' : `/${normalizedRole}/budget/expenses/pending-approvals`, permission: 'approve_expenses', roles: ['admin', 'manager'] },
                { name: 'Budget Overview',   href: '/accountant/budget', permission: null, roles: ['accountant'] },
            ]
        },

        // ==================== PAYROLL ====================
        {
            name: 'Payroll',
            icon: 'CurrencyDollarIcon',
            permission: 'manage_payroll',
            roles: ['admin', 'accountant', 'hr'],
            children: [
                { name: 'Payroll', href: '/admin/payroll', permission: 'manage_payroll', roles: ['admin'] },
                { name: 'Payroll Overview', href: '/hr/payroll', permission: null, roles: ['hr'] },
                { name: 'Payroll History', href: '/accountant/payroll/history', permission: null, roles: ['accountant'] },
                { name: 'Payroll History', href: '/hr/payroll/history', permission: null, roles: ['hr'] },
                { name: 'Process Payroll', href: '/accountant/payroll/process', permission: null, roles: ['accountant'] },
                { name: 'Process Payroll', href: '/hr/payroll/process', permission: null, roles: ['hr'] },
                { name: 'Tax Information', href: '/accountant/payroll/taxes', permission: null, roles: ['accountant'] },
                { name: 'Tax Information', href: '/hr/payroll/taxes', permission: null, roles: ['hr'] },
            ]
        },

        // ==================== INVOICES ====================
        {
            name: 'Invoices',
            icon: 'DocumentTextIcon',
            permission: 'manage_invoices',
            roles: ['accountant'],
            children: [
                { name: 'All Invoices', href: '/accountant/invoices', permission: null, roles: ['accountant'] },
                { name: 'Overdue', href: '/accountant/invoices/overdue', permission: null, roles: ['accountant'] },
                { name: 'Paid', href: '/accountant/invoices/paid', permission: null, roles: ['accountant'] },
            ]
        },

        // ==================== FINANCIAL REPORTS ====================
        {
            name: 'Financial Reports',
            icon: 'ChartPieIcon',
            permission: 'view_reports',
            roles: ['admin', 'manager', 'accountant'],
            children: [
                { name: 'Profit & Loss', href: '/accountant/reports/profit-loss', permission: null, roles: ['accountant'] },
                { name: 'Balance Sheet', href: '/accountant/reports/balance-sheet', permission: null, roles: ['accountant'] },
                { name: 'Cash Flow', href: '/accountant/reports/cash-flow', permission: null, roles: ['accountant'] },
                { name: 'Revenue Report', href: isAdmin ? '/admin/reports/revenue' : `/${normalizedRole}/reports/revenue`, permission: 'view_reports', roles: ['admin', 'manager'] },
                { name: 'Financial Overview', href: '/admin/financial-reports', permission: 'view_reports', roles: ['admin'] },
            ]
        },

        // ==================== POS - POINT OF SALE ====================
        {
            name: 'POS',
            icon: 'ShoppingCartIcon',
            permission: 'access_pos',
            roles: ['admin', 'manager', 'front_desk'],
            children: [
                { name: 'POS Terminal', href: '/pos', permission: 'access_pos', roles: ['admin', 'manager', 'front_desk'] },
                { name: 'Sales', href: '/pos/sales', permission: 'view_pos_reports', roles: ['admin', 'manager', 'accountant'] },
                { name: 'Products', href: '/admin/pos/products', permission: 'manage_inventory', roles: ['admin'] },
                { name: 'Categories', href: '/admin/pos/categories', permission: 'manage_inventory', roles: ['admin'] },
                { name: 'Inventory', href: '/pos/inventory', permission: 'manage_inventory', roles: ['admin'] },
                { name: 'Stock Batches', href: '/pos/stock-batches', permission: 'manage_inventory', roles: ['admin'] },
            ]
        },

        // ==================== USERS / EMPLOYEE MANAGEMENT (after Maintenance) ====================
        {
            name: 'Users',
            icon: 'UsersIcon',
            permission: null,
            roles: ['admin', 'manager', 'accountant'],
            children: [
                { name: 'Staff', href: '/admin/users', permission: 'manage_users', roles: ['admin'] },
                { name: 'Customers', href: isAdmin ? '/admin/customers' : `/${normalizedRole}/customers`, permission: 'manage_guests', roles: ['admin', 'manager', 'accountant', 'front_desk'] },
                { name: 'Customer Groups', href: '/admin/customer-groups', permission: 'manage_guests', roles: ['admin', 'manager', 'accountant', 'front_desk'] },
                { name: 'Guest Types', href: '/admin/guest-types', permission: 'manage_guests', roles: ['admin'] },
                { name: 'Roles & Permissions', href: '/admin/roles', permission: 'manage_roles', roles: ['admin'] },
                { name: 'Departments', href: '/admin/departments', permission: 'manage_staff', roles: ['admin'] },
                { name: 'Positions', href: '/admin/positions', permission: 'manage_staff', roles: ['admin'] },
                { name: 'Schedules', href: isAdmin ? '/admin/schedules' : '/manager/staff/schedules', permission: 'manage_staff', roles: ['admin', 'manager'] },
                { name: 'Work Shifts', href: '/admin/work-shifts', permission: 'manage_staff', roles: ['admin'] },
                { name: 'Time Tracking', href: isAdmin ? '/admin/time-tracking' : '/manager/staff/time-tracking', permission: 'view_schedule', roles: ['admin', 'manager'] },
                { name: 'Performance', href: isAdmin ? '/admin/performance' : '/manager/staff/performance', permission: 'manage_staff', roles: ['admin', 'manager'] },
            ]
        },

        // ==================== REPORTS ====================
        {
            name: 'Reports',
            icon: 'ClipboardDocumentListIcon',
            permission: 'view_reports',
            roles: ['admin', 'manager', 'accountant'],
            children: [
                { name: 'All Reports', href: '/admin/reports', permission: 'view_reports', roles: ['admin'] },
                { name: 'Occupancy', href: isAdmin ? '/admin/reports/occupancy' : `/${normalizedRole}/reports/occupancy`, permission: 'view_reports', roles: ['admin', 'manager'] },
                { name: 'Staff Reports', href: '/admin/reports/staff', permission: 'view_reports', roles: ['admin'] },
                { name: 'Analytics', href: '/admin/analytics', permission: 'view_reports', roles: ['admin'] },
            ]
        },

        // ==================== IPTV ====================
        // Kept for backwards compatibility; Maintenance now contains primary IPTV entry points.
        {
            name: 'IPTV',
            icon: 'TvIcon',
            permission: 'manage_iptv',
            roles: ['admin'],
            children: [
                { name: 'Devices', href: '/admin/iptv/devices', permission: 'manage_iptv_devices', roles: ['admin'] },
                { name: 'Content', href: '/admin/iptv/content', permission: 'manage_iptv', roles: ['admin'] },
                { name: 'Packages', href: '/admin/iptv/packages', permission: 'manage_iptv', roles: ['admin'] },
            ]
        },

        // ==================== HOUSEKEEPING ====================
        {
            name: 'Housekeeping',
            icon: 'SparklesIcon',
            permission: null,
            roles: ['housekeeping'],
            children: [
                { name: 'My Tasks', href: '/housekeeping/rooms', permission: null, roles: ['housekeeping'] },
                { name: 'Daily Tasks', href: '/housekeeping/tasks/daily', permission: null, roles: ['housekeeping'] },
                { name: 'Weekly Tasks', href: '/housekeeping/tasks/weekly', permission: null, roles: ['housekeeping'] },
                { name: 'Deep Cleaning', href: '/housekeeping/tasks/deep-cleaning', permission: null, roles: ['housekeeping'] },
                { name: 'Task History', href: '/housekeeping/tasks/history', permission: null, roles: ['housekeeping'] },
                { name: 'Inventory', href: '/housekeeping/inventory', permission: null, roles: ['housekeeping'] },
                { name: 'Maintenance', href: '/housekeeping/maintenance', permission: null, roles: ['housekeeping'] },
            ]
        },

        // ==================== MAINTENANCE - STAFF ====================
        {
            name: 'Maintenance',
            icon: 'WrenchScrewdriverIcon',
            permission: null,
            roles: ['maintenance'],
            children: [
                { name: 'Dashboard', href: '/maintenance/dashboard', permission: null, roles: ['maintenance'] },
                {
                    name: 'Work Orders',
                    icon: 'ClipboardDocumentListIcon',
                    permission: null,
                    roles: ['maintenance'],
                    children: [
                        { name: 'All Orders', href: '/maintenance/work-orders', permission: null, roles: ['maintenance'] },
                        { name: 'Open', href: '/maintenance/work-orders/open', permission: null, roles: ['maintenance'] },
                        { name: 'In Progress', href: '/maintenance/work-orders/in-progress', permission: null, roles: ['maintenance'] },
                        { name: 'Completed', href: '/maintenance/work-orders/completed', permission: null, roles: ['maintenance'] },
                    ]
                },
                {
                    name: 'IPTV',
                    icon: 'TvIcon',
                    permission: null,
                    roles: ['maintenance'],
                    children: [
                        { name: 'Devices', href: '/maintenance/iptv/devices', permission: null, roles: ['maintenance'] },
                        { name: 'Channels', href: '/maintenance/iptv/channels', permission: null, roles: ['maintenance'] },
                        { name: 'Troubleshoot', href: '/maintenance/iptv/troubleshoot', permission: null, roles: ['maintenance'] },
                        { name: 'Installation', href: '/maintenance/iptv/installation', permission: null, roles: ['maintenance'] },
                    ]
                },
                {
                    name: 'Preventive',
                    icon: 'CalendarIcon',
                    permission: null,
                    roles: ['maintenance'],
                    children: [
                        { name: 'Scheduled', href: '/maintenance/preventive/scheduled', permission: null, roles: ['maintenance'] },
                        { name: 'Overdue', href: '/maintenance/preventive/overdue', permission: null, roles: ['maintenance'] },
                        { name: 'Calendar', href: '/maintenance/preventive/calendar', permission: null, roles: ['maintenance'] },
                        { name: 'Equipment', href: '/maintenance/preventive/equipment', permission: null, roles: ['maintenance'] },
                    ]
                },
                {
                    name: 'Inventory',
                    icon: 'CubeIcon',
                    permission: null,
                    roles: ['maintenance'],
                    children: [
                        { name: 'Parts', href: '/maintenance/inventory/parts', permission: null, roles: ['maintenance'] },
                        { name: 'Tools', href: '/maintenance/inventory/tools', permission: null, roles: ['maintenance'] },
                        { name: 'Request', href: '/maintenance/inventory/request', permission: null, roles: ['maintenance'] },
                        { name: 'Vendors', href: '/maintenance/inventory/vendors', permission: null, roles: ['maintenance'] },
                    ]
                },
                { name: 'Time Tracking', href: '/maintenance/time-tracking', permission: null, roles: ['maintenance'] },
            ]
        },

        // ==================== BARTENDER ====================
        {
            name: 'Bartender',
            icon: 'SparklesIcon',
            permission: null,
            roles: ['bartender'],
            children: [
                { name: 'Dashboard', href: '/bartender/dashboard', permission: null, roles: ['bartender'] },
                { name: 'Drinks Menu', href: '/bartender/drinks', permission: null, roles: ['bartender'] },
                { name: 'Inventory', href: '/bartender/inventory', permission: null, roles: ['bartender'] },
                { name: 'Sales', href: '/bartender/sales', permission: null, roles: ['bartender'] },
                { name: 'Orders', href: '/bartender/orders', permission: null, roles: ['bartender'] },
            ]
        },

        // ==================== SERVER/RESTAURANT ====================
        {
            name: 'Restaurant',
            icon: 'SparklesIcon',
            permission: null,
            roles: ['server', 'restaurant_staff'],
            children: [
                { name: 'Dashboard', href: '/server/dashboard', permission: null, roles: ['server', 'restaurant_staff'] },
                { name: 'Sales', href: '/server/sales', permission: null, roles: ['server', 'restaurant_staff'] },
            ]
        },

        // ==================== STAFF GENERAL ====================
        {
            name: 'Staff Portal',
            icon: 'BriefcaseIcon',
            permission: null,
            roles: ['housekeeping', 'maintenance', 'staff'],
            children: [
                { name: 'My Schedule', href: '/staff/time-tracking/schedule', permission: null, roles: ['housekeeping', 'maintenance', 'staff'] },
                { name: 'My Timesheet', href: '/staff/time-tracking/timesheet', permission: null, roles: ['housekeeping', 'maintenance', 'staff'] },
                { name: 'Clock In/Out', href: '/staff/time-tracking/clock', permission: null, roles: ['housekeeping', 'maintenance', 'staff'] },
                { name: 'My Tasks', href: '/staff/tasks/assigned', permission: null, roles: ['housekeeping', 'maintenance', 'staff'] },
                { name: 'Profile', href: '/staff/profile', permission: null, roles: ['housekeeping', 'maintenance', 'staff'] },
                { name: 'Messages', href: '/staff/messages', permission: null, roles: ['housekeeping', 'maintenance', 'staff'] },
                { name: 'Announcements', href: '/staff/announcements', permission: null, roles: ['housekeeping', 'maintenance', 'staff'] },
            ]
        },
    ];

    // Filter navigation items based on role and permissions
    const filterNavigationItems = (items, userRole, userPermissions) => {
        return items.filter(item => {
            // Check if item is for this role
            if (item.roles && !item.roles.includes(userRole) && !isAdmin) {
                return false;
            }

            // Check if user has permission (admin bypass)
            if (!isAdmin && item.permission && !userPermissions.includes(item.permission)) {
                return false;
            }

            // If item has children, filter them too
            if (item.children) {
                item.children = filterNavigationItems(item.children, userRole, userPermissions);
                // Show parent if at least one child is visible
                return item.children.length > 0;
            }

            return true;
        });
    };

    // Return filtered navigation for the user's role
    return filterNavigationItems(allNavigationItems, normalizedRole, allPermissions);
};
