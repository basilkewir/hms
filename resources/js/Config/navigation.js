/**
 * Navigation configuration for each user role.
 *
 * Structure per role: array of section objects.
 *
 * Section object:
 *   section  {string}        - h3 heading label shown above the group
 *   condition {string|null}  - key resolved in DashboardLayout (e.g. 'isAdmin')
 *   flat     {boolean}       - if true, items are rendered as direct links (no collapse)
 *   items    {array}         - used when flat:true  [{ label, routeName?, href?, icon? }]
 *   groups   {array}         - used when flat:false [{ id, label, icon, items[] }]
 *
 * Item object:
 *   label      {string}  - display text (may include emoji prefix)
 *   routeName  {string}  - Ziggy named route (resolved at render time)
 *   href       {string}  - plain URL path (used when no named route exists)
 *   icon       {string}  - named icon key (resolved in DashboardLayout icon map)
 *   directLink {boolean} - render as a flat Link inside a group (no sub-indent)
 */

export const navigationConfig = {

  /* ─────────────────────────────── ADMIN ─────────────────────────────── */
  admin: [
    {
      section: 'Main',
      condition: null,
      flat: true,
      items: [
        { label: 'Admin Dashboard', routeName: 'admin.dashboard', icon: 'home' },
      ],
    },
    {
      section: '🏢 Operations',
      condition: 'isAdmin',
      groups: [
        {
          id: 'operations',
          label: 'Operations',
          icon: 'building',
          items: [
            { label: '📅 Reservations',    routeName: 'admin.reservations.index' },
            { label: '🏛️ Hall Bookings',   routeName: 'admin.hall-bookings.index' },
            { label: '👥 Guests',          routeName: 'admin.guests.index' },
            { label: '🔑 Check-ins',       routeName: 'admin.checkin' },
            { label: '🔓 Check-outs',      routeName: 'admin.checkout' },
            { label: '🏠 Room Status',     routeName: 'admin.rooms.status' },
            { label: '🌐 Channel Manager', href: '/admin/channel-manager' },
            { label: '⏳ Waitlist',        href: '/admin/waitlist' },
          ],
        },
      ],
    },
    {
      section: '🏨 Property Management',
      condition: 'isAdmin',
      groups: [
        {
          id: 'property-management',
          label: 'Property Management',
          icon: 'home',
          items: [
            { label: '🛏️ Rooms',              routeName: 'admin.rooms.index' },
            { label: '🏠 Room Status',         routeName: 'admin.rooms.status' },
            { label: '🏷️ Room Types',          routeName: 'admin.room-types.index' },
            { label: '🏷️ Room Amenities',      routeName: 'admin.room-amenities.index' },
            { label: '🏢 Floors',              href: '/admin/floors' },
            { label: '🏗️ Building Wings',      href: '/admin/building-wings' },
            { label: '🛏️ Bed Types',           href: '/admin/bed-types' },
            { label: '🏛️ Halls',               routeName: 'admin.halls.index' },
            { label: '💸 Expenses',            routeName: 'admin.expenses.index' },
            { label: '🏷️ Expense Categories',  routeName: 'admin.expenses.categories' },
            { label: '📍 Locations',           routeName: 'admin.locations.index' },
          ],
        },
      ],
    },
    {
      section: '🛒 Purchase Management',
      condition: 'isAdminWithPurchases',
      groups: [
        {
          id: 'purchase-management',
          label: 'Purchase Management',
          icon: 'cart',
          items: [
            { label: '🏭 Suppliers',          href: '/pos/suppliers' },
            { label: '📋 Purchase Orders',    href: '/pos/purchases' },
            { label: '📦 Products',           href: '/pos/products' },
            { label: '🏷️ Product Categories', href: '/pos/categories' },
          ],
        },
      ],
    },
    {
      section: '🛍️ POS Management',
      condition: 'canAccessPOS',
      groups: [
        {
          id: 'pos-products',
          label: 'Products',
          icon: 'archive',
          items: [
            { label: '📦 Products',    routeName: 'pos.products.index' },
            { label: '🏷️ Categories', routeName: 'pos.categories.index' },
            { label: '🏪 Brands',     routeName: 'pos.brands.index' },
            { label: '📏 Units',      routeName: 'pos.units.index' },
          ],
        },
        {
          id: 'pos-inventory',
          label: 'Inventory',
          icon: 'cube',
          items: [
            { label: '📊 Stock Overview',    routeName: 'pos.inventory.index' },
            { label: '🔄 Stock Adjustments', routeName: 'pos.adjustments.index' },
            { label: '🚚 Stock Transfers',   routeName: 'pos.transfers.index' },
            { label: '📈 Stock Movements',   routeName: 'pos.stock-movements.index' },
          ],
        },
        {
          id: 'pos-sales',
          label: 'Sales',
          icon: 'shopping',
          items: [
            { label: '💰 Sales Register', href: '/pos/sales' },
            { label: '📋 Orders',         href: '/pos/orders' },
            { label: '💳 Transactions',   href: '/pos/transactions' },
          ],
        },
        {
          id: 'pos-reports',
          label: 'Reports',
          icon: 'chart',
          items: [
            { label: '📊 Sales Reports', href: '/pos/reports' },
            { label: '📈 Analytics',     href: '/pos/analytics' },
          ],
        },
      ],
    },
    {
      section: '👤 Users & Groups Management',
      condition: 'isAdmin',
      groups: [
        {
          id: 'user-management',
          label: 'Users & Groups',
          icon: 'users',
          items: [
            { label: '👥 Users',           routeName: 'admin.users.index' },
            { label: '🔐 Roles',           routeName: 'admin.roles.index' },
            { label: '👨‍👩‍👧‍👦 Customers',     routeName: 'admin.customers.index' },
            { label: '👥 Customer Groups', routeName: 'admin.customer-groups.index' },
            { label: '🏷️ Guest Types',     href: '/admin/guest-types' },
            { label: '💳 Memberships',     href: '/admin/memberships' },
            { label: '🏭 Suppliers',       routeName: 'pos.suppliers.index' },
          ],
        },
      ],
    },
    {
      section: '🛎️ Services',
      condition: 'isAdmin',
      groups: [
        {
          id: 'services',
          label: 'Services',
          icon: 'clipboard',
          items: [
            { label: '🛎️ Services',        routeName: 'admin.services.index' },
            { label: '🔔 Concierge',       routeName: 'admin.services.concierge' },
            { label: '💰 Service Charges', href: '/admin/reservations/service-charges' },
            { label: '👕 Laundry',         routeName: 'admin.laundry.index' },
            { label: '📦 Packages',        href: '/admin/packages' },
            { label: '👥 Group Bookings',  href: '/admin/group-bookings' },
          ],
        },
      ],
    },
    {
      section: '🧹 Maintenance',
      condition: 'isAdmin',
      groups: [
        {
          id: 'admin-maintenance',
          label: 'Maintenance',
          icon: 'cog',
          items: [
            { label: '🔧 Maintenance Dashboard',  routeName: 'admin.maintenance' },
            { label: '📝 Maintenance Requests',   routeName: 'admin.maintenance-requests.index' },
            { label: '📋 Maintenance Categories', routeName: 'admin.maintenance-categories.index' },
            { label: '📺 IPTV Devices',           routeName: 'admin.devices.index' },
            { label: '📅 Preventive Maintenance', routeName: 'admin.maintenance.preventive.scheduled' },
          ],
        },
      ],
    },
    {
      section: '� Employee Management',
      condition: 'isAdmin',
      groups: [
        {
          id: 'admin-employee',
          label: 'Employee',
          icon: 'users',
          items: [
            { label: '⏰ Work Shifts',             routeName: 'admin.work-shifts.index' },
            { label: '📅 Staff Schedules',         routeName: 'admin.schedules.index' },
            { label: '🧹 Housekeeping Schedules',  routeName: 'admin.housekeeping-schedules.index' },
            { label: '📋 Housekeeping Tasks',      routeName: 'admin.housekeeping-tasks.index' },
            { label: '⏱️ Time Tracking',           routeName: 'admin.time-tracking.index' },
          ],
        },
      ],
    },
    {
      section: '�👥 HR Management',
      condition: 'isAdminOrHR',
      groups: [
        {
          id: 'hr-management',
          label: 'HR Management',
          icon: 'users',
          items: [
            { label: '📊 HR Dashboard',    href: '/hr/dashboard' },
            { label: '👥 Employees',       href: '/hr/employees' },
            { label: '🏢 Departments',     href: '/hr/departments' },
            { label: '⏰ Attendance',      href: '/hr/attendance' },
            { label: '💰 Payroll',         href: '/hr/payroll' },
            { label: '📈 HR Reports',      href: '/hr/reports' },
            { label: '🔍 Recruitment',     href: '/hr/recruitment' },
            { label: '🏖️ Leave Management',href: '/hr/leave-management' },
            { label: '📊 Performance',     href: '/hr/performance' },
            { label: '📚 Training',        href: '/hr/training' },
            { label: '⏱️ Time Tracking',   routeName: 'admin.time-tracking.index' },
          ],
        },
      ],
    },
    {
      section: '💰 Budget',
      condition: 'isAdmin',
      groups: [
        {
          id: 'admin-budget',
          label: 'Budget',
          icon: 'dollar',
          items: [
            { label: '📊 Dashboard',          routeName: 'admin.budget.dashboard' },
            { label: '📋 All Budgets',         routeName: 'admin.budget.index' },
            { label: '➕ Create Budget',       routeName: 'admin.budget.create' },
            { label: '⏳ Pending Approvals',   routeName: 'admin.budget.expenses.pending-approvals' },
            { label: '📈 Reports',             routeName: 'admin.budget.reports' },
            { label: '🔔 Alerts',              routeName: 'admin.budget.alerts' },
            { label: '🗂️ Archived',            routeName: 'admin.budget.archived' },
          ],
        },
      ],
    },
    {
      section: 'Reports',
      condition: 'isAdmin',
      groups: [
        {
          id: 'reports',
          label: 'Reports',
          icon: 'chart',
          items: [
            { label: 'All Reports',       routeName: 'admin.reports.index' },
            { label: 'Occupancy Reports', routeName: 'admin.reports.occupancy' },
            { label: 'Revenue Reports',   routeName: 'admin.reports.revenue' },
            { label: 'Analytics',         routeName: 'admin.analytics.index' },
          ],
        },
      ],
    },
    {
      section: 'Financial',
      condition: 'isAdmin',
      groups: [
        {
          id: 'financial-management',
          label: 'Financial',
          icon: 'dollar',
          items: [
            { label: 'Transactions', routeName: 'admin.transactions.index', permission: 'view_transactions' },
            { label: 'Invoices',      routeName: 'admin.invoices.index' },
            { label: 'Quotes',        routeName: 'admin.quotes.index' },
            { label: 'Expenses',     routeName: 'admin.expenses.index', permission: 'view_expenses' },
          ],
        },
      ],
    },
    {
      section: 'Settings',
      condition: 'isAdmin',
      groups: [
        {
          id: 'settings',
          label: 'Settings',
          icon: 'cog',
          items: [
            { label: '⚙️ General Settings', routeName: 'admin.settings' },
            { label: '💾 System Backup',    routeName: 'admin.settings.backup' },
            { label: '🔑 License',          routeName: 'admin.settings.license' },
          ],
        },
      ],
    },
  ],

  /* ─────────────────────────────── MANAGER ─────────────────────────────── */
  manager: [
    {
      section: 'Main',
      condition: null,
      flat: true,
      items: [
        { label: 'Manager Dashboard', routeName: 'manager.dashboard', icon: 'home' },
      ],
    },
    {
      section: '🏢 Operations',
      condition: 'isManager',
      groups: [
        {
          id: 'manager-operations',
          label: 'Operations',
          icon: 'building',
          items: [
            { label: '📋 Overview',          routeName: 'manager.operations.index' },
            { label: '📅 All Reservations',  routeName: 'manager.reservations.index' },
            { label: '➕ New Reservation',   routeName: 'manager.reservations.create' },
            { label: '🔑 Check In',          routeName: 'manager.checkin' },
            { label: '🔓 Check Out',         routeName: 'manager.checkout' },
            { label: '🏛️ Hall Bookings',     routeName: 'manager.hall-bookings.index' },
            { label: '⏳ Waitlist',          routeName: 'manager.waitlist.index' },
            { label: '👥 Group Bookings',    routeName: 'manager.group-bookings.index' },
            { label: '🌐 Channel Manager',   routeName: 'manager.channel-manager.index' },
          ],
        },
      ],
    },
    {
      section: '🏨 Property Management',
      condition: 'isManager',
      groups: [
        {
          id: 'manager-property',
          label: 'Property Management',
          icon: 'home',
          items: [
            { label: '🛏️ Rooms',              routeName: 'manager.rooms.index' },
            { label: '🏠 Room Status',         routeName: 'manager.rooms.status' },
            { label: '🏷️ Room Types',          routeName: 'manager.room-types.index' },
            { label: '🏷️ Room Amenities',      routeName: 'manager.room-amenities.index' },
            { label: '🏢 Floors',              routeName: 'manager.floors.index' },
            { label: '🏗️ Building Wings',      routeName: 'manager.building-wings.index' },
            { label: '🛏️ Bed Types',           routeName: 'manager.bed-types.index' },
            { label: '🏛️ Halls',               routeName: 'manager.halls.index' },
            { label: '💸 Expenses',            routeName: 'manager.expenses.index' },
            { label: '🏷️ Expense Categories',  routeName: 'manager.expenses.categories' },
            { label: '📍 Locations',           routeName: 'manager.locations.index' },
          ],
        },
      ],
    },
    {
      section: '👤 Employee Management',
      condition: 'isManager',
      groups: [
        {
          id: 'manager-employee',
          label: 'Employee',
          icon: 'users',
          items: [
            { label: '👥 Staff Management',        routeName: 'manager.staff.index' },
            { label: '⏰ Work Shifts',              routeName: 'manager.work-shifts.index' },
            { label: '📅 Staff Schedules',          routeName: 'manager.staff.schedules' },
            { label: '🧹 Housekeeping Schedules',   routeName: 'manager.housekeeping-schedules.index' },
            { label: '📋 Housekeeping Tasks',       routeName: 'manager.housekeeping-tasks.index' },
            { label: '⏱️ Time Tracking',            routeName: 'manager.staff.time-tracking' },
            { label: '📊 Performance',              routeName: 'manager.staff.performance' },
          ],
        },
      ],
    },
    {
      section: '👥 HR Management',
      condition: 'isManager',
      groups: [
        {
          id: 'manager-hr-management',
          label: 'HR Management',
          icon: 'users',
          items: [
            { label: '📊 HR Dashboard',     href: '/hr/dashboard' },
            { label: '👥 Employees',        href: '/hr/employees' },
            { label: '🏢 Departments',      href: '/hr/departments' },
            { label: '⏰ Attendance',       href: '/hr/attendance' },
            { label: '💰 Payroll',          href: '/hr/payroll' },
            { label: '📈 HR Reports',       href: '/hr/reports' },
            { label: '🔍 Recruitment',      href: '/hr/recruitment' },
            { label: '🏖️ Leave Management', href: '/hr/leave-management' },
            { label: '📊 Performance',      href: '/hr/performance' },
            { label: '📚 Training',         href: '/hr/training' },
            { label: '⏱️ Time Tracking',    routeName: 'manager.staff.time-tracking' },
          ],
        },
      ],
    },
    {
      section: '🧹 Maintenance',
      condition: 'isManager',
      groups: [
        {
          id: 'manager-maintenance',
          label: 'Maintenance',
          icon: 'wrench',
          items: [
            { label: '📝 Maintenance Requests',   routeName: 'manager.maintenance-requests.index' },
            { label: '➕ New Request',            routeName: 'manager.maintenance-requests.create' },
            { label: '📋 Maintenance Categories', routeName: 'manager.maintenance-categories.index' },
            { label: '➕ New Category',           routeName: 'manager.maintenance-categories.create' },
            { label: '📅 Preventive Maintenance', routeName: 'admin.maintenance.preventive.scheduled' },
          ],
        },
      ],
    },
    {
      section: '🛎️ Services',
      condition: 'isManager',
      groups: [
        {
          id: 'manager-services',
          label: 'Services',
          icon: 'clipboard',
          items: [
            { label: '🛎️ Services',        routeName: 'manager.services.index' },
            { label: '🔔 Concierge',       routeName: 'manager.services.concierge' },
            { label: '💰 Service Charges', href: '/manager/reservations/service-charges' },
            { label: '👕 Laundry',         routeName: 'manager.laundry.index' },
            { label: '📦 Packages',        href: '/manager/packages' },
            { label: '👥 Group Bookings',  routeName: 'manager.group-bookings.index' },
          ],
        },
      ],
    },
    {
      section: '💸 Expenses',
      condition: 'isManager',
      groups: [
        {
          id: 'manager-expenses',
          label: 'Expenses',
          icon: 'dollar',
          items: [
            { label: '💸 All Expenses',        routeName: 'manager.expenses.index' },
            { label: '🏷️ Expense Categories',  routeName: 'manager.expenses.categories' },
          ],
        },
      ],
    },
    {
      section: '💰 Budget',
      condition: 'isManager',
      groups: [
        {
          id: 'manager-budget',
          label: 'Budget',
          icon: 'dollar',
          items: [
            { label: '📊 Dashboard',        routeName: 'manager.budget.dashboard' },
            { label: '📋 All Budgets',       routeName: 'manager.budget.index' },
            { label: '➕ Create Budget',     routeName: 'manager.budget.create' },
            { label: '⏳ Pending Approvals', routeName: 'manager.budget.expenses.pending-approvals' },
            { label: '📈 Reports',           routeName: 'manager.budget.reports' },
            { label: '🔔 Alerts',            routeName: 'manager.budget.alerts' },
            { label: '🗂️ Archived',          routeName: 'manager.budget.archived' },
          ],
        },
      ],
    },
    {
      section: '� Financial',
      condition: 'isManager',
      groups: [
        {
          id: 'manager-financial',
          label: 'Financial',
          icon: 'dollar',
          items: [
            { label: 'Transactions', routeName: 'manager.transactions.index' },
            { label: 'Invoices',      routeName: 'manager.invoices.index' },
            { label: 'Quotes',        routeName: 'manager.quotes.index' },
          ],
        },
      ],
    },
    {
      section: '� Reports',
      condition: 'isManager',
      groups: [
        {
          id: 'manager-reports',
          label: 'Reports',
          icon: 'chart',
          items: [
            { label: '📊 Reports',           routeName: 'manager.reports' },
            { label: '🏠 Occupancy Reports', routeName: 'manager.reports.occupancy' },
            { label: '💰 Revenue Reports',   routeName: 'manager.reports.revenue' },
            { label: '👥 Staff Reports',     routeName: 'manager.reports.staff' },
          ],
        },
      ],
    },
    {
      section: 'Users',
      condition: 'isManager',
      groups: [
        {
          id: 'manager-users',
          label: 'Users',
          icon: 'users',
          items: [
            { label: '👥 Customers',       routeName: 'manager.customers.index' },
            { label: '👨‍👩‍👧‍👦 Customer Groups', routeName: 'manager.customer-groups.index' },
            { label: '🧑 Guests',          routeName: 'manager.guests.index' },
            { label: '🏷️ Guest Types',     routeName: 'manager.guest-types.index' },
          ],
        },
      ],
    },
    {
      section: '🛒 Purchase Management',
      condition: 'isManagerWithPurchases',
      groups: [
        {
          id: 'purchase-management',
          label: 'Purchase Management',
          icon: 'cart',
          items: [
            { label: '🏭 Suppliers',          routeName: 'manager.suppliers.index' },
            { label: '📋 Purchase Orders',    routeName: 'manager.purchases.index' },
            { label: '📦 Products',           href: '/pos/products' },
            { label: '🏷️ Product Categories', href: '/pos/categories' },
          ],
        },
      ],
    },
    {
      section: '🛍️ POS Management',
      condition: 'canAccessPOS',
      groups: [
        {
          id: 'pos-products',
          label: 'Products',
          icon: 'archive',
          items: [
            { label: '📦 Products',    routeName: 'pos.products.index' },
            { label: '🏷️ Categories', routeName: 'pos.categories.index' },
            { label: '🏪 Brands',     routeName: 'pos.brands.index' },
            { label: '📏 Units',      routeName: 'pos.units.index' },
          ],
        },
        {
          id: 'pos-inventory',
          label: 'Inventory',
          icon: 'cube',
          items: [
            { label: '📊 Stock Overview',    routeName: 'pos.inventory.index' },
            { label: '🔄 Stock Adjustments', routeName: 'pos.adjustments.index' },
            { label: '🚚 Stock Transfers',   routeName: 'pos.transfers.index' },
            { label: '📈 Stock Movements',   routeName: 'pos.stock-movements.index' },
          ],
        },
        {
          id: 'pos-sales',
          label: 'Sales',
          icon: 'shopping',
          items: [
            { label: '💰 Sales Register', href: '/pos/sales' },
            { label: '📋 Orders',         href: '/pos/orders' },
            { label: '💳 Transactions',   href: '/pos/transactions' },
          ],
        },
      ],
    },
  ],

  /* ─────────────────────────────── ACCOUNTANT ─────────────────────────────── */
  accountant: [
    {
      section: 'Main',
      condition: null,
      flat: true,
      items: [
        { label: 'Accountant Dashboard', routeName: 'accountant.dashboard', icon: 'home' },
      ],
    },
    {
      section: 'Users',
      condition: 'isAccountant',
      groups: [
        {
          id: 'accountant-users',
          label: 'Users',
          icon: 'users',
          items: [
            { label: 'Customers',       routeName: 'accountant.customers.index' },
            { label: 'Customer Groups', routeName: 'accountant.customer-groups.index' },
          ],
        },
      ],
    },
    {
      section: '💰 Budget',
      condition: 'isAccountant',
      groups: [
        {
          id: 'accountant-budget',
          label: 'Budget',
          icon: 'dollar',
          items: [
            { label: '📊 Overview',     routeName: 'accountant.budget.index' },
            { label: '📉 Comparison',   routeName: 'accountant.budget.comparison' },
            { label: '🔮 Forecast',     routeName: 'accountant.budget.forecast' },
          ],
        },
      ],
    },
    {
      section: 'Accounting',
      condition: 'isAccountant',
      groups: [
        {
          id: 'accountant-accounting',
          label: 'Accounting',
          icon: 'dollar',
          items: [
            { label: 'Transactions', routeName: 'accountant.transactions.index' },
            { label: 'Expenses',     routeName: 'accountant.expenses.index' },
            { label: 'Invoices',     routeName: 'accountant.invoices.index' },
            { label: 'Quotes',       routeName: 'accountant.quotes.index' },
            { label: 'Payroll',      routeName: 'accountant.payroll.index' },
          ],
        },
      ],
    },
    {
      section: 'Reports',
      condition: 'isAccountant',
      groups: [
        {
          id: 'accountant-reports',
          label: 'Reports',
          icon: 'chart',
          items: [
            { label: 'Profit & Loss', routeName: 'accountant.reports.profit-loss' },
            { label: 'Balance Sheet', routeName: 'accountant.reports.balance-sheet' },
            { label: 'Cash Flow',     routeName: 'accountant.reports.cash-flow' },
            { label: 'Revenue',       routeName: 'accountant.reports.revenue' },
          ],
        },
      ],
    },
  ],

  /* ─────────────────────────────── FRONT DESK ─────────────────────────────── */
  front_desk: [
    {
      section: 'Main',
      condition: null,
      flat: true,
      items: [
        { label: 'Front Desk Dashboard', routeName: 'front-desk.dashboard', icon: 'home' },
      ],
    },
    // ...existing code...
    {
      section: 'Front Desk',
      condition: 'isFrontDesk',
      groups: [
        {
          id: 'frontdesk-operations',
          label: 'Operations',
          icon: 'building',
          items: [
            { label: 'Reservations', routeName: 'front-desk.reservations.index' },
            { label: 'Arrivals',     routeName: 'front-desk.reservations.arrivals' },
            { label: 'Departures',   routeName: 'front-desk.reservations.departures' },
            { label: 'Guests',       routeName: 'front-desk.guests.index' },
            { label: 'Check-ins',    routeName: 'front-desk.checkin' },
            { label: 'Check-outs',   routeName: 'front-desk.checkout' },
          ],
        },
        {
          id: 'frontdesk-rooms',
          label: 'Rooms',
          icon: 'home',
          items: [
            { label: 'Rooms',           routeName: 'front-desk.rooms.index' },
            { label: 'Room Assignment', routeName: 'front-desk.room-assignment' },
          ],
        },
        {
          id: 'frontdesk-payments',
          label: 'Payments',
          icon: 'dollar',
          items: [
            { label: 'Payments',      routeName: 'front-desk.payments.process' },
            { label: 'My Transactions', routeName: 'front-desk.transactions.index' },
          ],
        },
        {
          id: 'frontdesk-keycards',
          label: 'Key Cards',
          icon: 'key',
          items: [
            { label: 'Key Cards', routeName: 'front-desk.key-cards.index' },
          ],
        },
        {
          id: 'frontdesk-services',
          label: 'Services & Requests',
          icon: 'bell',
          items: [
            { label: 'Concierge',     routeName: 'front-desk.services.concierge' },
            { label: 'Hall Bookings', routeName: 'front-desk.services.hall-bookings' },
            { label: 'Housekeeping',  routeName: 'front-desk.services.housekeeping' },
            { label: 'Maintenance',   routeName: 'front-desk.services.maintenance' },
          ],
        },
      ],
    },
    {
      section: '💰 Invoicing',
      condition: 'isFrontDesk',
      groups: [
        {
          id: 'frontdesk-invoicing',
          label: 'Invoicing',
          icon: 'dollar',
          items: [
            { label: 'Invoices',     routeName: 'front-desk.invoices.index' },
            { label: 'Quotes',       routeName: 'front-desk.quotes.index' },
          ],
        },
        {
          id: 'frontdesk-expenses',
          label: 'Expenses',
          icon: 'dollar',
          items: [
            { label: 'All Expenses',    routeName: 'front-desk.expenses.index' },
            { label: 'Record Expense',  routeName: 'front-desk.expenses.create' },
          ],
        },
      ],
    },
  ],

  /* ─────────────────────────────── HOUSEKEEPING ─────────────────────────────── */
  housekeeping: [
    {
      section: 'Main',
      condition: null,
      flat: true,
      items: [
        { label: 'Housekeeping Dashboard', routeName: 'housekeeping.dashboard', icon: 'home' },
      ],
    },
    {
      section: 'Housekeeping',
      condition: 'isHousekeeping',
      groups: [
        {
          id: 'housekeeping',
          label: 'Housekeeping',
          icon: 'home',
          items: [
            { label: 'Rooms',             routeName: 'housekeeping.rooms.index' },
            { label: 'To Clean',          routeName: 'housekeeping.rooms.to-clean' },
            { label: 'Daily Tasks',       routeName: 'housekeeping.tasks.daily' },
            { label: 'Weekly Tasks',      routeName: 'housekeeping.tasks.weekly' },
            { label: 'Task History',      routeName: 'housekeeping.tasks.history' },
            { label: 'Supplies',          routeName: 'housekeeping.inventory.supplies' },
            { label: 'Linens',            routeName: 'housekeeping.inventory.linens' },
            { label: 'Amenities',         routeName: 'housekeeping.inventory.amenities' },
            { label: 'Request Supplies',  routeName: 'housekeeping.inventory.request' },
            { label: 'Report Maintenance',routeName: 'housekeeping.maintenance.report' },
          ],
        },
      ],
    },
  ],

  /* ─────────────────────────────── MAINTENANCE ─────────────────────────────── */
  maintenance: [
    {
      section: 'Main',
      condition: null,
      flat: true,
      items: [
        { label: 'Maintenance Dashboard', routeName: 'maintenance.dashboard', icon: 'home' },
      ],
    },
    {
      section: 'Maintenance',
      condition: 'isMaintenance',
      groups: [
        {
          id: 'maintenance',
          label: 'Maintenance',
          icon: 'wrench',
          items: [
            { label: 'Dashboard',  routeName: 'maintenance.dashboard' },
            { label: 'Requests',   routeName: 'admin.maintenance-requests.index' },
            { label: 'IPTV',       routeName: 'maintenance.iptv.devices' },
            { label: 'Preventive', routeName: 'maintenance.preventive.scheduled' },
          ],
        },
      ],
    },
  ],

  /* ─────────────────────────────── BARTENDER ─────────────────────────────── */
  bartender: [
    {
      section: 'Main',
      condition: null,
      flat: true,
      items: [
        { label: '🍹 Dashboard', href: '/bartender/dashboard', icon: 'home' },
      ],
    },
    {
      section: '🍸 Bar Operations',
      condition: null,
      groups: [
        {
          id: 'bartender-operations',
          label: 'Operations',
          icon: 'shopping',
          items: [
            { label: '🍹 Drinks Menu',     href: '/bartender/drinks' },
            { label: '📦 Inventory',        href: '/bartender/inventory' },
            { label: '📊 Sales Reports',    href: '/bartender/sales' },
          ],
        },
      ],
    },
  ],

  /* ─────────────────────────────── SERVER ─────────────────────────────── */
  server: [
    {
      section: 'Main',
      condition: null,
      flat: true,
      items: [
        { label: '🍽️ Dashboard', href: '/server/dashboard', icon: 'home' },
      ],
    },
    {
      section: '🍽️ Restaurant Operations',
      condition: null,
      groups: [
        {
          id: 'server-operations',
          label: 'Operations',
          icon: 'shopping',
          items: [
            { label: '📊 Sales Reports',    href: '/server/sales' },
          ],
        },
      ],
    },
  ],

  /* ─────────────────────────────── HR ─────────────────────────────── */
  hr: [
    {
      section: 'Main',
      condition: null,
      flat: true,
      items: [
        { label: '📊 HR Dashboard', href: '/hr/dashboard', icon: 'home' },
      ],
    },
    {
      section: '👥 HR Management',
      condition: null,
      groups: [
        {
          id: 'hr-management',
          label: 'HR Management',
          icon: 'users',
          items: [
            { label: '👥 Employees',        href: '/hr/employees' },
            { label: '🏢 Departments',      href: '/hr/departments' },
            { label: '⏰ Attendance',         href: '/hr/attendance' },
            { label: '💰 Payroll',          href: '/hr/payroll' },
            { label: '📈 HR Reports',      href: '/hr/reports' },
            { label: '🔍 Recruitment',      href: '/hr/recruitment' },
            { label: '🏖️ Leave Management', href: '/hr/leave-management' },
            { label: '📊 Performance',     href: '/hr/performance' },
            { label: '📚 Training',         href: '/hr/training' },
          ],
        },
      ],
    },
  ],
}

/**
 * Icon SVG path data map used by DashboardLayout to render submenu icons.
 * Each key maps to an SVG <path> d-attribute string (24x24 viewBox, stroke-based).
 */
export const iconPaths = {
  home:      'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
  building:  'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
  cog:       'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
  chart:     'M9 17v1a3 3 0 006 0v-1m6 0H9m3 0h3m-3 0a3 3 0 01-3-3V8a3 3 0 00-3-3H9a3 3 0 00-3 3v8a3 3 0 013 3z',
  dollar:    'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
  users:     'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
  cart:      'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
  archive:   'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
  cube:      'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
  shopping:  'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
  clipboard: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
  wrench:    'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
  help:      'M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
  key:       'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z',
  bell:      'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
  chevron:   'M19 9l-7 7-7-7',
}
