<script setup>
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useTheme } from '@/Composables/useTheme.js'

defineProps({ show: Boolean })
const emit = defineEmits(['close'])

const page = usePage()
const user = computed(() => page.props.auth?.user || {})
const userRoles = computed(() => user.value?.roles?.map(r => r.name) || [])
const hasRole = (r) => userRoles.value.includes(r)

const { } = useTheme()
const themeColors = computed(() => ({
    background: 'var(--kotel-background)',
    card: 'var(--kotel-card)',
    border: 'var(--kotel-border)',
    textPrimary: 'var(--kotel-text-primary)',
    textSecondary: 'var(--kotel-text-secondary)',
    textTertiary: 'var(--kotel-text-tertiary)',
    primary: 'var(--kotel-primary)',
    success: 'var(--kotel-success)',
    warning: 'var(--kotel-warning)',
}))

const activeSection = ref(0)

// ─── Tutorial content per role ────────────────────────────────────────────────
const tutorials = computed(() => {
    if (hasRole('admin'))       return adminTutorial
    if (hasRole('manager'))     return managerTutorial
    if (hasRole('front_desk') || hasRole('frontdesk')) return frontDeskTutorial
    if (hasRole('housekeeping')) return housekeepingTutorial
    if (hasRole('maintenance')) return maintenanceTutorial
    if (hasRole('hr'))          return hrTutorial
    if (hasRole('bartender') || hasRole('server')) return bartenderTutorial
    if (hasRole('accountant'))  return accountantTutorial
    return defaultTutorial
})

// ─── ADMIN ────────────────────────────────────────────────────────────────────
const adminTutorial = [
    {
        title: '🏠 Dashboard Overview',
        icon: '📊',
        steps: [
            'After logging in you land on the Admin Dashboard — it shows today\'s occupancy rate, arrivals, departures, revenue, and pending maintenance at a glance.',
            'The dashboard widgets refresh automatically. Use the date filter at the top right to view any period.',
            'Click any widget number to drill down to the full list (e.g. click "Arrivals Today" to see the arrivals list).',
        ],
    },
    {
        title: '📅 Reservations',
        icon: '📅',
        steps: [
            'Go to Operations → Reservations to see all bookings. Use the search bar or date filter to narrow results.',
            'Click "New Reservation" to create a booking. Fill in guest details, select room type, dates, number of guests, then click Save.',
            'To view or modify a reservation, click its row. You can edit dates, add services, attach a quote, or cancel from the detail page.',
            'Hall Bookings (Operations → Hall Bookings) works the same way for event-space reservations.',
            'The Waitlist (Operations → Waitlist) shows guests waiting for rooms. Add them via "New Waitlist Entry" and move them to a reservation when a room becomes available.',
        ],
    },
    {
        title: '🔑 Check-in & Check-out',
        icon: '🔑',
        steps: [
            'Go to Operations → Check-ins. Select a confirmed reservation and click "Check In". The room status changes to Occupied automatically.',
            'You can assign a specific room (if not pre-assigned) and issue a key card from the Check-in screen.',
            'For Check-out: go to Operations → Check-outs, find the guest, review their folio (charges), collect payment, then click "Check Out". The room status changes to Dirty.',
            'Room Status (Operations → Room Status) shows every room\'s current state: Available, Occupied, Dirty, Clean, Out of Order.',
        ],
    },
    {
        title: '🏨 Rooms & Property',
        icon: '🏨',
        steps: [
            'Property Management → Rooms lists all rooms. Click "+ New Room" to add one. Fill number, floor, type, bed type, capacity, and rate.',
            'Note: rooms that have past reservation history cannot be deleted — set their status to "Out of Order" instead to remove them from availability.',
            'Property Management → Room Types lets you define categories (Standard, Deluxe, Suite…) with base rates and descriptions.',
            'Floors and Building Wings organise the property layout. Add them first, then assign rooms to them.',
            'Halls are event/meeting spaces. Add hall details (capacity, rate, amenities) under Property Management → Halls.',
        ],
    },
    {
        title: '👥 Users & Roles',
        icon: '👥',
        steps: [
            'Users & Groups → Users: add a staff member by clicking "+ New User". Set their name, email, password, and role.',
            'Roles available: admin, manager, front_desk, housekeeping, maintenance, bartender, server, hr, accountant.',
            'Users & Groups → Roles lets you create custom roles and assign specific permissions to them.',
            'Customers (Users & Groups → Customers) stores guest profiles for repeat visitors. Customer Groups allow bulk pricing rules.',
        ],
    },
    {
        title: '🛎️ Services & Concierge',
        icon: '🛎️',
        steps: [
            'Services → Services lists all chargeable extras (room service, spa, airport transfer…). Add new services with their price and category.',
            'Services → Concierge shows guest requests. Assign a staff member and mark requests complete once handled.',
            'Laundry (Services → Laundry) tracks laundry items per room/reservation. Log items in, set pricing, and update status.',
            'Packages bundle multiple services at a discounted rate. Create them under Services → Packages.',
        ],
    },
    {
        title: '🔧 Maintenance',
        icon: '🔧',
        steps: [
            'Maintenance → Maintenance Requests: view all open, in-progress, and resolved issues. Click a request to assign staff and update status.',
            'Add a new maintenance issue for any room or area using "+ New Request". Select category, priority, and description.',
            'Maintenance → Preventive Maintenance shows scheduled recurring inspections. Set them up with frequency and assign a technician.',
            'IPTV Devices lists all in-room TV devices. Link them to rooms and track their status.',
        ],
    },
    {
        title: '🧹 Housekeeping',
        icon: '🧹',
        steps: [
            'Employee → Housekeeping Tasks: view all cleaning tasks. Tasks are auto-generated each morning for occupied rooms.',
            'Assign a cleaner by clicking a task and selecting from the staff list. The cleaner\'s mobile app will notify them.',
            'Housekeeping Schedules let you set recurring assignments (e.g. Room 101 always cleaned by Maria on Mon/Wed/Fri).',
            'Work Shifts defines shift patterns. Assign shifts to housekeeping staff under Staff Schedules.',
        ],
    },
    {
        title: '💰 Financial & Budget',
        icon: '💰',
        steps: [
            'Financial → Transactions: full ledger of all payments received. You can filter by date, type, or amount.',
            'Financial → Invoices: generate and send invoices for reservations. Click "+ New Invoice" on any reservation folio.',
            'Financial → Quotes: send pricing quotes to prospective guests. Once accepted, convert a quote to a reservation in one click.',
            'Budget → Dashboard shows spending vs budget for each department. Create budgets under Budget → Create Budget.',
            'Budget → Pending Approvals shows expense requests from other roles awaiting your sign-off.',
        ],
    },
    {
        title: '📊 Reports & Analytics',
        icon: '📊',
        steps: [
            'Reports → All Reports is a summary dashboard with 8 analytics sections: Occupancy, Revenue, Financial, Inventory, Guests, Staff, Maintenance, and Summary.',
            'Occupancy Reports shows room utilisation by type, monthly trends, and recent reservations with status badges.',
            'Revenue Reports breaks down income by source, room type, and period. Download as PDF or Excel.',
            'Analytics provides live charts of bookings, revenue, and occupancy with date range filtering.',
        ],
    },
    {
        title: '⚙️ Settings',
        icon: '⚙️',
        steps: [
            'Settings → General Settings: update hotel name, logo, contact info, currency, and timezone.',
            'To change currency: go to General Settings → Currency and select your currency code. All figures across the system update immediately.',
            'Settings → System Backup: create a full database backup. Download and store it securely.',
            'Settings → License: view license status and enter a new license key if required.',
        ],
    },
]

// ─── MANAGER ─────────────────────────────────────────────────────────────────
const managerTutorial = [
    {
        title: '🏠 Dashboard Overview',
        icon: '📊',
        steps: [
            'The Manager Dashboard shows today\'s occupancy, arrivals, departures, revenue KPIs, and staff activity.',
            'Use the widgets to get a quick status snapshot. Click any figure to navigate to the full detail page.',
        ],
    },
    {
        title: '📅 Reservations & Check-in/out',
        icon: '📅',
        steps: [
            'Operations → All Reservations: view every booking. Use filters (date range, status, room type) to find specific bookings.',
            'Click "New Reservation" to create a booking: fill in guest info, select room type, dates, and extras, then Save.',
            'Check In (Operations → Check In): select an arriving reservation, assign a room if not pre-assigned, and click Check In.',
            'Check Out (Operations → Check Out): review the guest folio, ensure all charges are captured, process payment, then Check Out.',
            'Hall Bookings handles event-space reservations. Group Bookings manages multiple rooms for the same party.',
        ],
    },
    {
        title: '🏨 Property Management',
        icon: '🏨',
        steps: [
            'Property Management → Rooms: view and edit room details. To add a room, click "+ New Room" and fill all fields.',
            'Rooms with any reservation history cannot be deleted — mark them Out of Order if they are no longer in service.',
            'Use Room Types to manage pricing categories. Update the base rate here and it applies to all new reservations for that type.',
            'Floors and Building Wings organise your property. Set them up before creating rooms.',
        ],
    },
    {
        title: '👤 Staff & Employee Management',
        icon: '👤',
        steps: [
            'Employee → Staff Management: view all staff members, their role, current shift, and recent activity.',
            'Work Shifts: define shift templates (Morning 7-3, Evening 3-11, Night 11-7). Assign shifts to staff via Staff Schedules.',
            'Housekeeping Tasks: see all cleaning tasks, assign cleaners, and track completion. Tasks auto-generate each morning.',
            'Time Tracking: view clock-in/out records for all staff. Correct any missed punches from here.',
        ],
    },
    {
        title: '🔧 Maintenance',
        icon: '🔧',
        steps: [
            'Maintenance → Maintenance Requests: triage all open issues. Set priority (Low / Medium / High / Urgent) and assign a technician.',
            'Click "+ New Request" to log a room or area issue. Select the category (Electrical, Plumbing, HVAC…) and describe the problem.',
            'Preventive Maintenance shows scheduled inspections. Ensure technicians complete them on time to avoid urgent failures.',
        ],
    },
    {
        title: '🛎️ Services',
        icon: '🛎️',
        steps: [
            'Services → Services: view and manage all chargeable extras. Update pricing or availability here.',
            'Concierge: manage guest requests. Assign staff members and mark requests complete.',
            'Laundry: track laundry orders per room. Log items in, apply charges to the guest folio, and update status.',
        ],
    },
    {
        title: '💰 Budget & Financial',
        icon: '💰',
        steps: [
            'Budget → Dashboard: see how each department is tracking against its allocated budget.',
            'Budget → Pending Approvals: approve or reject expense requests submitted by other staff.',
            'Financial → Transactions: view all payment records. Filter by date or type.',
            'Financial → Invoices & Quotes: manage billing documents for guests.',
        ],
    },
    {
        title: '📊 Reports',
        icon: '📊',
        steps: [
            'Reports → Reports: access occupancy, revenue, and staff performance reports at a glance.',
            'Occupancy Reports shows utilisation by room type and monthly trends.',
            'Revenue Reports breaks income down by source and period.',
        ],
    },
]

// ─── FRONT DESK ───────────────────────────────────────────────────────────────
const frontDeskTutorial = [
    {
        title: '🏠 Front Desk Dashboard',
        icon: '📊',
        steps: [
            'Your dashboard shows today\'s arrivals, departures, current occupancy, and rooms requiring attention.',
            'Arrivals and Departures lists update in real time. Use them as your daily checklist.',
        ],
    },
    {
        title: '📅 Reservations',
        icon: '📅',
        steps: [
            'Operations → All Reservations: find any booking by guest name, reservation number, dates, or status.',
            'To create a walk-in booking: click "New Reservation", enter guest details, choose an available room, set dates, add any extras, then Save.',
            'To modify a reservation: click its row, update the details (dates, room, extras), and Save.',
            'To cancel a reservation: open it and click Cancel. You will be asked to confirm before it is cancelled.',
        ],
    },
    {
        title: '🔑 Check-in Process',
        icon: '🔑',
        steps: [
            'Go to Operations → Check In. The list shows all guests arriving today.',
            'Click the guest\'s reservation. Verify ID, confirm room assignment (change the room here if needed), then click "Check In".',
            'The room status automatically changes to Occupied.',
            'If the guest needs a key card: go to Key Cards, create a new card entry for that room, and hand the physical card to the guest.',
            'Add any immediate service requests (extra towels, early breakfast) via Services → Concierge.',
        ],
    },
    {
        title: '🔓 Check-out Process',
        icon: '🔓',
        steps: [
            'Go to Operations → Check Out. The list shows all guests departing today.',
            'Click the guest\'s reservation to open their folio. Review all charges: room rate, services, minibar, laundry, etc.',
            'If any charge is missing, add it using "Add Charge" before checkout.',
            'Select the payment method (Cash, Card, Bank Transfer), enter the amount, and click "Check Out".',
            'The room status changes to Dirty, triggering a housekeeping task.',
            'Print or email the invoice using the "Invoice" button on the folio.',
        ],
    },
    {
        title: '🏠 Room Assignment',
        icon: '🏠',
        steps: [
            'Rooms → Room Assignment: see a visual floor plan of all rooms with colour-coded statuses.',
            'Drag and drop a reservation onto an available room to assign it, or use the dropdown on the reservation detail page.',
            'Rooms → Room Status: quick view of every room — Available (green), Occupied (blue), Dirty (orange), Clean (teal), Out of Order (grey).',
        ],
    },
    {
        title: '💳 Payments',
        icon: '💳',
        steps: [
            'Payments → All Payments: view payment history for any reservation.',
            'To record a payment: open a reservation folio, click "Add Payment", select the method and enter the amount.',
            'Partial payments are supported — the folio tracks balance due automatically.',
            'For refunds: open the payment record and click "Refund". Enter the amount and confirm.',
        ],
    },
    {
        title: '🛎️ Services & Concierge',
        icon: '🛎️',
        steps: [
            'Services → Concierge: all active guest requests appear here. Assign them to the right department and mark complete when done.',
            'To add a service charge to a guest folio: open the reservation, click "Add Service", select from the service list, and confirm.',
            'Laundry requests: log pickup items per room, mark them in-progress when collected, and complete when returned.',
        ],
    },
    {
        title: '👥 Guests',
        icon: '👥',
        steps: [
            'Operations → Guests: search for any past or current guest by name, email, or phone.',
            'Guest profiles store contact details, stay history, special preferences, and invoices.',
            'Attach a note to a guest profile (e.g. "Prefers high floor, allergic to feather pillows") for future stays.',
        ],
    },
]

// ─── HOUSEKEEPING ─────────────────────────────────────────────────────────────
const housekeepingTutorial = [
    {
        title: '🏠 Housekeeping Dashboard',
        icon: '📊',
        steps: [
            'Your dashboard shows rooms that need cleaning today, pending tasks assigned to you, and completed tasks.',
            'The colour key: 🟠 Dirty (to clean), 🟡 In Progress, 🟢 Clean, ⚫ Out of Order (skip), 🔵 Occupied (do not disturb unless checkout).',
        ],
    },
    {
        title: '📋 Daily Tasks',
        icon: '📋',
        steps: [
            'Go to Tasks → Daily Tasks to see today\'s assigned cleaning list.',
            'Click a task to see room details: room number, floor, notes from reception (e.g. "DND until 11am", "VIP turndown needed").',
            'Update the task status: click "Start Cleaning" when you enter the room, then "Mark Complete" when finished.',
            'The room status updates automatically to Clean once the task is marked complete.',
        ],
    },
    {
        title: '🏠 Room Status',
        icon: '🏠',
        steps: [
            'Rooms → Room Status: shows every room on one screen.',
            'To manually change a room status (e.g. you cleaned a room that didn\'t have a task): click the room tile and select the new status.',
            'Always mark rooms Clean as soon as they are done — reception cannot assign early check-ins until the room shows as Clean.',
        ],
    },
    {
        title: '🔧 Reporting Maintenance Issues',
        icon: '🔧',
        steps: [
            'If you find a fault while cleaning (broken lamp, leaking tap, damaged furniture): go to Report Maintenance.',
            'Fill in the room number, select a category (Electrical, Plumbing, Furniture…), describe the issue, and Submit.',
            'The maintenance team receives the request immediately. Mark the room Out of Order if it is not safe for guests.',
        ],
    },
    {
        title: '📦 Supplies & Inventory',
        icon: '📦',
        steps: [
            'Supplies → Supplies: view current stock of cleaning products, toiletries, and linens.',
            'To request restocking: go to Supplies → Request Supplies, select items and quantities needed, and Submit. The manager will approve.',
            'Linens: log linen usage per room in Supplies → Linens. Record quantities used and sent to laundry.',
            'Amenities: track in-room amenity stock (soap, shampoo, etc.) and request replenishment when low.',
        ],
    },
    {
        title: '📅 Schedules',
        icon: '📅',
        steps: [
            'Your weekly schedule is visible on the Dashboard or under Schedules.',
            'Scheduled tasks (deep cleaning, inspection days) appear in your task list on the assigned date.',
            'If you cannot attend a shift, notify your manager as early as possible so tasks can be reassigned.',
        ],
    },
]

// ─── MAINTENANCE ─────────────────────────────────────────────────────────────
const maintenanceTutorial = [
    {
        title: '🏠 Maintenance Dashboard',
        icon: '📊',
        steps: [
            'The Maintenance Dashboard shows all open requests, their priority, status, and assigned technician.',
            'Priority levels: 🔴 Urgent → fix within 2 hours, 🟠 High → same day, 🟡 Medium → within 3 days, 🟢 Low → scheduled.',
        ],
    },
    {
        title: '📝 Maintenance Requests',
        icon: '📝',
        steps: [
            'Requests → Maintenance Requests: full list of all issues. Filter by status (Open, In Progress, Resolved) or priority.',
            'Click a request to see the full description, affected room/area, reporter, and any photos attached.',
            'To accept a request: open it and click "Start Work". The status changes to In Progress.',
            'When work is finished: click "Mark Resolved" and add a note describing what was done.',
            'If parts are needed: add a note with the parts required and change status to "Awaiting Parts".',
        ],
    },
    {
        title: '➕ Logging New Issues',
        icon: '➕',
        steps: [
            'You can log issues yourself: click "+ New Request", select the room or area, category, priority, and describe the fault.',
            'Attach photos if available — this speeds up diagnosis and approval.',
        ],
    },
    {
        title: '📋 Preventive Maintenance',
        icon: '📋',
        steps: [
            'Preventive → Scheduled: shows all upcoming scheduled inspections (HVAC filters, fire safety checks, elevator servicing…).',
            'Click on a scheduled item to see the checklist. Tick off each item as you inspect it.',
            'Mark the inspection Complete when the full checklist is done. The next occurrence schedules automatically.',
        ],
    },
    {
        title: '📺 IPTV Devices',
        icon: '📺',
        steps: [
            'IPTV → Devices: lists all in-room TV devices with their IP address, room assignment, and status.',
            'If a TV is offline or faulty: click the device, update its status, and log the fault as a maintenance request.',
            'After replacing or fixing a device: update its serial number and set status back to Active.',
        ],
    },
]

// ─── HR ───────────────────────────────────────────────────────────────────────
const hrTutorial = [
    {
        title: '🏠 HR Dashboard',
        icon: '📊',
        steps: [
            'The HR Dashboard summarises head count, active staff, today\'s attendance, pending leave requests, and open recruitment positions.',
            'Use the quick links to jump to any HR function.',
        ],
    },
    {
        title: '👥 Employees',
        icon: '👥',
        steps: [
            'HR → Employees: full staff directory. Click a name to view their profile — personal details, role, department, hire date, and documents.',
            'To add a new employee: click "+ New Employee", fill in all fields, assign a department and position, then Save.',
            'Upload documents (contract, ID, certificates) directly to the employee profile.',
            'To terminate an employee: open their profile, click "End Employment", select the reason and last working date.',
        ],
    },
    {
        title: '🏢 Departments',
        icon: '🏢',
        steps: [
            'HR → Departments: view all departments (Front Desk, Housekeeping, Maintenance, F&B, Admin…).',
            'Create a new department: click "+ New Department", give it a name and assign a manager.',
            'Each employee must belong to a department. Assign employees from their profile or from the department page.',
        ],
    },
    {
        title: '⏰ Attendance',
        icon: '⏰',
        steps: [
            'HR → Attendance: view daily attendance records for all staff. Filter by employee, department, or date.',
            'Staff clock in/out via the time tracking system. If a record is missing or incorrect, click the record and edit it manually.',
            'Generate attendance reports for payroll by filtering the date range and exporting.',
        ],
    },
    {
        title: '💰 Payroll',
        icon: '💰',
        steps: [
            'HR → Payroll: process monthly payroll. Select the pay period, review worked hours from attendance records, apply deductions/bonuses.',
            'Click "Generate Payslips" to calculate net pay for each employee.',
            'Mark payroll as Paid after processing. Employees can view their payslips from their own profile or via the HR summary.',
        ],
    },
    {
        title: '🏖️ Leave Management',
        icon: '🏖️',
        steps: [
            'HR → Leave Management: view all leave requests (Annual, Sick, Maternity/Paternity, Unpaid).',
            'To approve a request: click it and select Approve. To reject: click Reject and provide a reason.',
            'You can log leave on behalf of an employee: click "+ New Leave Request", select the employee, dates, and type.',
            'The system tracks remaining leave balances per employee automatically.',
        ],
    },
    {
        title: '🔍 Recruitment',
        icon: '🔍',
        steps: [
            'HR → Recruitment: manage open positions and applicants.',
            'Create a job posting: click "+ New Position", describe the role, requirements, and department.',
            'As applications come in, move them through stages: Applied → Shortlisted → Interview → Offer → Hired.',
            'Once hired, convert the applicant to an employee record in one click from the recruitment panel.',
        ],
    },
    {
        title: '📊 Performance',
        icon: '📊',
        steps: [
            'HR → Performance: conduct and record performance reviews for each employee.',
            'Set goals and KPIs per employee. Log review scores and notes at each review cycle.',
            'Performance history is visible on each employee\'s profile.',
        ],
    },
    {
        title: '📚 Training',
        icon: '📚',
        steps: [
            'HR → Training: schedule and track training sessions for departments or individuals.',
            'Create a training session: click "+ New Training", set the topic, date, trainer, and attendees.',
            'Mark sessions complete after they are delivered. Training history appears on each employee\'s profile.',
        ],
    },
]

// ─── BARTENDER / SERVER ──────────────────────────────────────────────────────
const bartenderTutorial = [
    {
        title: '🏠 Dashboard',
        icon: '📊',
        steps: [
            'Your dashboard shows today\'s orders, revenue from your shift, and inventory alerts for low-stock items.',
        ],
    },
    {
        title: '🍹 Drinks Menu',
        icon: '🍹',
        steps: [
            'Drinks Menu: browse the full bar menu with prices, categories (Cocktails, Spirits, Beer, Soft Drinks…), and availability.',
            'If an item is out of stock, mark it Unavailable so it doesn\'t appear in orders. Update stock once restocked.',
            'To add a new drink: only managers/admins can do this. Contact your manager to update the menu.',
        ],
    },
    {
        title: '💰 Taking & Processing Orders',
        icon: '💰',
        steps: [
            'Sales → Sales Register: the main point-of-sale screen. Select items from the menu, set quantities, and click "Place Order".',
            'To charge to a room: on the order screen select "Charge to Room", enter the room number, and confirm. The charge posts to the guest\'s folio.',
            'For cash/card payments: select the payment method, enter the amount, and complete the sale.',
            'View a receipt or print it using the receipt button after each completed sale.',
        ],
    },
    {
        title: '📦 Inventory',
        icon: '📦',
        steps: [
            'Inventory: view current bar stock levels. Items highlighted in red are below the minimum threshold and need reordering.',
            'After receiving a delivery, update stock quantities by clicking the item and entering the new quantity.',
            'Stock Movements: tracks every change in quantity — sales, deliveries, and adjustments — for full audit trail.',
        ],
    },
    {
        title: '📋 Orders History',
        icon: '📋',
        steps: [
            'Sales → Orders: full history of all orders during your shift and previous shifts.',
            'Click any order to see the items, amounts, and payment method.',
            'If a customer disputes a charge: locate the order in history and show them the itemised breakdown.',
        ],
    },
]

// ─── ACCOUNTANT ───────────────────────────────────────────────────────────────
const accountantTutorial = [
    {
        title: '🏠 Accountant Dashboard',
        icon: '📊',
        steps: [
            'The Accountant Dashboard shows total revenue, expenses, outstanding invoices, and cash flow for the current month.',
        ],
    },
    {
        title: '💰 Transactions & Accounting',
        icon: '💰',
        steps: [
            'Accounting → Transactions: every financial movement (receipts, payments, refunds). Filter by date, type, or amount. Export to CSV/Excel.',
            'Accounting → Invoices: view and manage all guest invoices. Generate missing invoices from a reservation folio if needed.',
            'Accounting → Expenses: review all operating expenses. Approve or query expense claims submitted by other departments.',
            'Accounting → Payroll: review payroll records. Verify amounts before the manager marks them as paid.',
        ],
    },
    {
        title: '📊 Reports',
        icon: '📊',
        steps: [
            'Reports → Profit & Loss: compare revenue against expenses by period.',
            'Reports → Balance Sheet: snapshot of assets and liabilities.',
            'Reports → Cash Flow: track actual cash in and out.',
            'Reports → Revenue: breakdown by room type, service, and source.',
            'All reports can be exported to PDF or Excel using the Download button.',
        ],
    },
    {
        title: '💲 Budget',
        icon: '💲',
        steps: [
            'Budget → Dashboard: monitor department spending vs budget.',
            'Budget → Pending Approvals: approve expense requests that are within budget. Flag items that exceed allocation.',
        ],
    },
    {
        title: '👥 Customers',
        icon: '👥',
        steps: [
            'Customers: view guest account balances and outstanding invoices.',
            'Customer Groups: apply group pricing or credit terms to specific companies or travel agents.',
        ],
    },
]

// ─── DEFAULT ─────────────────────────────────────────────────────────────────
const defaultTutorial = [
    {
        title: '🏠 Getting Started',
        icon: '📊',
        steps: [
            'Use the left sidebar to navigate between sections.',
            'Click your role\'s Dashboard link to see your personalised overview.',
            'Contact your administrator if you need access to additional features.',
        ],
    },
]
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div v-if="show" class="fixed inset-0 z-[9999] flex items-center justify-center p-4" style="background: rgba(0,0,0,0.6);" @click.self="emit('close')">
                <div class="relative w-full max-w-3xl max-h-[90vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden"
                     :style="{ backgroundColor: themeColors.card, border: `1px solid ${themeColors.border}` }">

                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b flex-shrink-0" :style="{ borderColor: themeColors.border }">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg" :style="{ backgroundColor: themeColors.primary, color: '#fff' }">?</div>
                            <div>
                                <h2 class="text-lg font-bold" :style="{ color: themeColors.textPrimary }">Help & Tutorial</h2>
                                <p class="text-xs" :style="{ color: themeColors.textSecondary }">Step-by-step guide for your role</p>
                            </div>
                        </div>
                        <button @click="emit('close')" class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors hover:opacity-70" :style="{ color: themeColors.textTertiary }">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="flex flex-1 overflow-hidden">
                        <!-- Section tabs (left) -->
                        <div class="w-52 flex-shrink-0 overflow-y-auto border-r py-3" :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background }">
                            <button
                                v-for="(section, i) in tutorials"
                                :key="i"
                                @click="activeSection = i"
                                class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors flex items-center gap-2"
                                :style="activeSection === i
                                    ? { backgroundColor: themeColors.primary + '22', color: themeColors.primary, borderLeft: `3px solid ${themeColors.primary}` }
                                    : { color: themeColors.textSecondary, borderLeft: '3px solid transparent' }"
                            >
                                <span>{{ section.icon }}</span>
                                <span class="leading-tight">{{ section.title.replace(/^[^\s]*\s/, '') }}</span>
                            </button>
                        </div>

                        <!-- Content (right) -->
                        <div class="flex-1 overflow-y-auto px-6 py-5">
                            <Transition name="section-fade" mode="out-in">
                                <div :key="activeSection">
                                    <h3 class="text-base font-bold mb-4" :style="{ color: themeColors.textPrimary }">
                                        {{ tutorials[activeSection].title }}
                                    </h3>
                                    <ol class="space-y-3">
                                        <li
                                            v-for="(step, si) in tutorials[activeSection].steps"
                                            :key="si"
                                            class="flex gap-3 text-sm leading-relaxed"
                                            :style="{ color: themeColors.textSecondary }"
                                        >
                                            <span class="flex-shrink-0 w-6 h-6 rounded-full text-xs font-bold flex items-center justify-center mt-0.5"
                                                  :style="{ backgroundColor: themeColors.primary + '22', color: themeColors.primary }">
                                                {{ si + 1 }}
                                            </span>
                                            <span>{{ step }}</span>
                                        </li>
                                    </ol>
                                </div>
                            </Transition>
                        </div>
                    </div>

                    <!-- Footer nav -->
                    <div class="flex items-center justify-between px-6 py-3 border-t flex-shrink-0" :style="{ borderColor: themeColors.border }">
                        <button
                            @click="activeSection = Math.max(0, activeSection - 1)"
                            :disabled="activeSection === 0"
                            class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm transition-opacity"
                            :style="{ color: themeColors.primary, opacity: activeSection === 0 ? 0.3 : 1 }"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            Previous
                        </button>
                        <span class="text-xs" :style="{ color: themeColors.textTertiary }">
                            {{ activeSection + 1 }} / {{ tutorials.length }}
                        </span>
                        <button
                            @click="activeSection = Math.min(tutorials.length - 1, activeSection + 1)"
                            :disabled="activeSection === tutorials.length - 1"
                            class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm transition-opacity"
                            :style="{ color: themeColors.primary, opacity: activeSection === tutorials.length - 1 ? 0.3 : 1 }"
                        >
                            Next
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.section-fade-enter-active, .section-fade-leave-active { transition: opacity 0.15s ease; }
.section-fade-enter-from, .section-fade-leave-to { opacity: 0; }
</style>
