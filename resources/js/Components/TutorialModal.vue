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
const hasAnyRole = (roles) => roles.some((role) => hasRole(role))

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
    if (hasAnyRole(['admin'])) return adminTutorial
    if (hasAnyRole(['manager', 'assistant_manager'])) return managerTutorial
    if (hasAnyRole(['front_desk', 'frontdesk', 'front_desk_manager'])) return frontDeskTutorial
    if (hasAnyRole(['housekeeping', 'housekeeping_manager'])) return housekeepingTutorial
    if (hasAnyRole(['maintenance', 'maintenance_manager'])) return maintenanceTutorial
    if (hasAnyRole(['hr'])) return hrTutorial
    if (hasAnyRole(['bartender'])) return bartenderTutorial
    if (hasAnyRole(['server'])) return serverTutorial
    if (hasAnyRole(['accountant', 'junior_accountant'])) return accountantTutorial
    if (hasAnyRole(['staff'])) return staffTutorial
    return defaultTutorial
})

// ─── ADMIN ────────────────────────────────────────────────────────────────────
const adminTutorial = [
    {
        title: '🏠 Dashboard Overview',
        icon: '📊',
        steps: [
            'After logging in you land on the Admin Dashboard — it shows today\'s occupancy rate, arrivals, departures, total revenue, and any urgent issues at a glance.',
            'The KPI widgets at the top update in real time. Click any number (e.g. "Arrivals Today: 5") to jump directly to that filtered list.',
            'The Revenue chart in the middle shows daily income for the current month. Hover over a bar to see the exact day\'s total.',
            'Room Status at a glance: the coloured room grid shows which rooms are Available (green), Occupied (blue), Dirty (orange), or Out of Order (grey).',
            'Pending tasks panel: any unresolved maintenance requests or unassigned housekeeping tasks appear here as action items.',
            'Use the notification bell in the top bar to monitor new reservations, expense submissions, quotes, purchase activity, purchase receiving, and bill-adjustment requests without leaving the dashboard.',
            'Use the date selector at the top right of any widget to switch between Today, This Week, This Month, or a custom range.',
        ],
    },
    {
        title: '📅 Reservations',
        icon: '📅',
        steps: [
            'Go to Operations → Reservations to see every booking. The status column shows Confirmed, Checked-In, Checked-Out, Cancelled, or No-Show.',
            'Filter reservations by guest name, room type, status, or date range using the toolbar. Results update instantly as you type.',
            'Click "+ New Reservation" to create a booking: search for an existing guest or fill in new guest details, pick a room type, set arrival and departure dates, add any extras (services, packages), then click Save.',
            'Website bookings now enter the system without a physical room assigned. Review them from Reservations or Room Assignment, then let staff choose the exact room later when one is ready.',
            'To edit a reservation, click its row → Edit. You can change dates, room, extras, or special requests. The folio updates automatically with any rate differences.',
            'Open a reservation to review bill-modification requests submitted by front desk. Admin and manager users can approve or reject them, and approved requests post directly to folio charges and balances.',
            'Attach a Quote to a reservation: open the reservation → Attach Quote. Once a quote is accepted and linked, it converts the quote status to "Converted".',
            'Hall Bookings (Operations → Hall Bookings): works identically to room reservations but for event spaces. Set setup time, breakdown time, catering options, and client details.',
            'Waitlist (Operations → Waitlist): when no rooms are available, add the guest here. When a room opens, click "Convert to Reservation" to move them from the waitlist automatically.',
            'Group Bookings: create a parent booking then link multiple room reservations under it. Useful for conferences and group tours.',
        ],
    },
    {
        title: '🔑 Check-in & Check-out',
        icon: '🔑',
        steps: [
            'Operations → Check In shows all of today\'s arriving guests. Filter by time or by room type if needed.',
            'Click a guest\'s row → Check In. Verify their ID, confirm or change the room assignment if required, note any early check-in fee, then click "Confirm Check In". The room status instantly becomes Occupied.',
            'Key Cards: after check-in, go to Key Cards, click "+ New Card", select the room and expiry date, and hand the physical card to the guest.',
            'Early check-in fee: if the guest arrives before standard check-in time, an early fee can be added to their folio from the check-in screen.',
            'Today\'s Guests: in the sidebar go to Operations → Today\'s Guests to see every guest arriving on today\'s date, including confirmed, pending, and already checked-in reservations.',
            'Police Report: in the sidebar go to Operations → Police Report to view all currently checked-in guests in a formatted register for police or immigration submission. Use the Print button to print in black and white, or Export CSV to download the data.',
            'Operations → Check Out shows today\'s departures. Click the guest → Review Folio to see every charge: room nights, services, minibar, laundry, POS charges, etc.',
            'Click "Add Charge" on the folio if any item is missing. Click "Apply Discount" to give a complimentary deduction with a reason code.',
            'Select the payment method (Cash, Card, Bank Transfer), enter the amount received, and click "Check Out". The room status changes to Dirty, triggering a housekeeping task automatically.',
            'After checkout, print or email the guest\'s invoice using the "Print Invoice" or "Email Invoice" button on the folio page.',
        ],
    },
    {
        title: '🏨 Rooms & Property',
        icon: '🏨',
        steps: [
            'Property Management → Rooms: full list of every room. Columns show room number, floor, type, current status, and current guest.',
            'Click "+ New Room" to add a room. Required fields: room number, floor, room type (must exist first), bed configuration, max occupancy, and base nightly rate.',
            'Rooms with any reservation history cannot be deleted — set status to "Out of Order" to pull them from availability without losing data.',
            'Property Management → Room Types: define pricing tiers (Standard, Deluxe, Suite, Penthouse…). Each type has a base rate, description, max occupancy, and an optional photo gallery.',
            'Amenities: attach amenities to a Room Type (Wi‑Fi, balcony, sea view, jacuzzi…). These appear on booking confirmations and reports. To manage amenities, go to Property Management → Room Amenities: create or edit amenity records (name, description, icon), and assign them to room types. Use the Amenities page to add images or mark amenities as premium (affects pricing and package creation).',
            'Floors: add floor records first (Ground Floor, 1st Floor…) so you can assign rooms and generate per-floor housekeeping reports.',
            'Halls: add event spaces under Property Management → Halls. Set capacity, hourly/daily rate, amenities (projector, PA system, catering), and availability calendar.',
            'Building Wings: if your property spans multiple buildings, create Wings and assign floors to them for clearer organisation.',
        ],
    },
    {
        title: '👥 Users & Roles',
        icon: '👥',
        steps: [
            'Users & Groups → Users: complete staff directory. Click "+ New User" and fill in first name, last name, email, phone, password, and role.',
            'Available roles: Admin, Manager, Front Desk, Housekeeping, Maintenance, Bartender/Server, HR, Accountant. Assign the most appropriate role — it controls which pages and actions the user can access.',
            'To reset a user\'s password: click their row → Edit → enter a new password and save. You can also send a password-reset email from this screen.',
            'To deactivate a user (e.g. staff who left): uncheck "Active" on their profile. They can no longer log in but their history is preserved.',
            'Users & Groups → Roles: view default role permissions. Advanced: create a custom role (e.g. "Senior Front Desk") with a specific subset of permissions.',
            'Customers: each guest who has stayed can have a profile under Users & Groups → Customers. Profiles store contact info, stay history, preferences, and invoices.',
            'Customer Groups: create groups (e.g. "Corporate Clients", "Travel Agents") and apply negotiated rates or credit terms to all members at once.',
        ],
    },
    {
        title: '🛒 POS & Bar System',
        icon: '🛒',
        steps: [
            'The POS (Point of Sale) system manages bar, restaurant, and retail sales. Access it via the POS menu in the sidebar.',
            'POS → Products: add every product (drinks, food, merchandise) with its name, category, selling price, cost price, and stock quantity.',
            'Categories help organise the POS screen. Create them first under POS → Categories, then assign products to them.',
            'POS → Sales Register: the live cashier screen. Search or tap a product to add it to the cart. Adjust quantities, apply item-level discounts, then select a payment method and complete the sale.',
            'Charge to Room: on the POS screen, select "Charge to Room", type the room number, and the amount posts directly to the guest\'s folio — no cash changes hands.',
            'POS → Sales History: every sale recorded. Filter by date, cashier, payment method, or customer. Click a sale to see itemised details and reprint the receipt.',
            'POS → Products → Delete All / Delete: admins and managers can remove products individually or clear the entire catalogue when doing a full menu refresh.',
            'Inventory tracking: each sale automatically reduces the product\'s stock. When stock drops below the minimum threshold, the product appears highlighted in the low-stock alert panel.',
        ],
    },
    {
        title: '🛎️ Services & Packages',
        icon: '🛎️',
        steps: [
            'Services → Services: all chargeable extras (room service, airport transfer, spa, laundry, tours…). Click "+ New Service" to add one with name, price, category, and optional tax rate.',
            'Services → Concierge: all active guest requests appear here. View request details, assign a staff member, set a target completion time, and mark complete when done.',
            'Add a service to a guest\'s folio at any time: open the reservation → Folio tab → Add Service → select service → confirm. The charge appears on the checkout bill.',
            'Laundry (Services → Laundry): create a laundry order per room — log each item (shirts, trousers, dresses) with quantity and unit price. Update status through Collected → In Wash → Ready → Delivered.',
            'Packages: bundle multiple services at a flat discounted rate. Create under Services → Packages, set the included services, and price. Guests can choose a package at booking or during their stay.',
            'Mini-bar: if you charge for in-room mini-bar items, log consumption per room via Services → Mini Bar. Items auto-charge to the guest folio.',
        ],
    },
    {
        title: '🔧 Maintenance',
        icon: '🔧',
        steps: [
            'Maintenance → Maintenance Requests: all logged issues, colour-coded by priority — Red (Urgent), Orange (High), Yellow (Medium), Green (Low).',
            'Click a request to view the full description, affected room or area, photos (if attached), and the reporter\'s name.',
            'Assign a technician: open a request → click "Assign" → select a staff member from the list → Save. The assigned technician sees the task in their dashboard.',
            'Track progress: statuses move through Open → Assigned → In Progress → Awaiting Parts → Resolved. Update status at each stage.',
            'Click "+ New Request" to log an issue yourself: select the location (room, floor, or shared area), category (Electrical, Plumbing, HVAC, Furniture…), priority level, description, and upload photos.',
            'Preventive Maintenance: schedule recurring inspections (quarterly HVAC service, monthly fire extinguisher check). Each occurrence auto-creates a task on the scheduled date.',
            'IPTV Devices: manage all in-room TV units — link them to rooms, track serial numbers, IP addresses, and operational status.',
            'Maintenance performance reports are available under Reports → Maintenance showing open rates, average resolution time, and most common issue categories.',
        ],
    },
    {
        title: '🧹 Housekeeping',
        icon: '🧹',
        steps: [
            'Housekeeping tasks are automatically created each morning: one task per occupied room, and one per checkout room.',
            'Operations → Housekeeping Tasks: full list of all tasks with room number, task type (Daily Clean, Turndown, Checkout Clean), assigned cleaner, and status.',
            'Assign cleaners: click a task → Assign → select a staff member. Bulk-assign by selecting multiple tasks and using "Assign Selected".',
            'Housekeeping Schedules: set up recurring assignments (e.g. "Room 201 — Maria — Mon/Wed/Fri") so tasks are auto-assigned without manual intervention each day.',
            'Work Shifts: define shift patterns (Morning 7am-3pm, Evening 3pm-11pm). Assign shifts to housekeeping staff under Staff Schedules.',
            'Inspection: after a room is marked Clean, a supervisor can do a spot-check by opening the task and clicking "Inspect". If issues are found, click "Re-clean" to send it back.',
            'Room Status board (Operations → Room Status): shows every room colour-coded in real time. Click any room to manually change its status (useful after a quick preparatory clean).',
        ],
    },
    {
        title: '💰 Financial & Budget',
        icon: '💰',
        steps: [
            'Financial → Transactions: the complete payment ledger — every charge, payment, refund, and adjustment. Filter by date range, payment method, or amount. Export to CSV or Excel.',
            'Financial → Invoices: all guest invoices. Click an invoice to view, download as PDF, or resend by email. Click "+ New Invoice" on any reservation folio to generate one.',
            'Financial → Quotes: create and send price proposals to prospective guests. Fill in room type, dates, services, discounts, and validity date. Once approved, convert the quote to a full reservation in one click.',
            'Financial → Expenses: log and review operating costs (supplies, repairs, utilities…). Each expense can be attached to a department and an approval request.',
            'Purchase Management covers suppliers, purchase orders, received stock, and related notifications. Use the bell to jump directly to new purchase submissions or received orders that need review.',
            'Budget → Create Budget: set monthly or annual spending allocations per department (e.g. Housekeeping: 500,000 FCFA/month). The system tracks actual spend against each allocation.',
            'Budget → Dashboard: real-time comparison of budget vs actual per department. Bars turn red when overspent, amber when approaching the limit.',
            'Budget → Pending Approvals: expense requests from other staff land here for your sign-off. Approve, reject (with reason), or request more info.',
        ],
    },
    {
        title: '📊 Reports & Analytics',
        icon: '📊',
        steps: [
            'Reports → All Reports: a hub with 8 report categories — Occupancy, Revenue, Financial, Inventory, Guests, Staff, Maintenance, and an Executive Summary.',
            'Occupancy Reports: room utilisation by type, monthly occupancy trend chart, RevPAR (Revenue Per Available Room), and ADR (Average Daily Rate).',
            'Revenue Reports: income breakdown by room type, service category, and date range. Compare current period vs previous period. Download as PDF or Excel.',
            'Financial Reports: P&L summary, invoice ageing, expense breakdown, and payment method distribution.',
            'Guest Reports: new vs returning guests, nationality breakdown, average length of stay, and VIP guest list.',
            'Staff Reports: attendance summary, housekeeping productivity (rooms cleaned per shift), maintenance resolution rate.',
            'Analytics page: live interactive charts with custom date-range filtering. Track bookings, revenue, and occupancy trends side by side.',
            'All report downloads include the hotel logo and are formatted for professional presentation to owners or investors.',
        ],
    },
    {
        title: '⚙️ Settings',
        icon: '⚙️',
        steps: [
            'Settings → General Settings: hotel name, logo (upload PNG/JPG), contact email, phone, address, website URL, check-in time, and check-out time.',
            'Currency: select your currency code (XAF, USD, EUR, GBP…) and symbol. All monetary figures across every page update immediately.',
            'Timezone: set your local timezone so date/time calculations and reports are accurate for your location.',
            'Settings → Email: configure SMTP credentials (host, port, username, password) for automated guest emails (booking confirmation, invoices, password resets).',
            'Settings → Integration: generate your Booking API Token for connecting an external booking website. Also set the Booking CORS allowed origins to restrict which domains can call the API.',
            'Settings → System Backup: create a full database backup at any time. The file downloads to your computer — store it securely or upload to cloud storage.',
            'Settings → License: view the current license status, expiry date, and the number of allowed users. Enter a new license key here when renewing.',
            'Settings → Notifications: configure which system events send email alerts (new booking, checkout today, low stock, maintenance request) and to which email address.',
        ],
    },
]

// ─── MANAGER ─────────────────────────────────────────────────────────────────
const managerTutorial = [
    {
        title: '🏠 Dashboard Overview',
        icon: '📊',
        steps: [
            'The Manager Dashboard gives you a real-time snapshot of the property: today\'s occupancy percentage, arrivals, departures, total revenue, and active staff.',
            'The revenue chart shows daily income for the selected period. Use the date picker at the top right to switch between Today, This Week, This Month, or a custom range.',
            'Click any KPI number (e.g. "Departures Today: 4") to jump directly to that filtered list — no need to navigate through menus.',
            'The pending approvals badge in the sidebar shows budget requests awaiting your decision. Address these regularly to unblock other departments.',
            'Room Status grid at the bottom of the dashboard colour-codes every room. A quick glance tells you if housekeeping is falling behind or if a room needs urgent attention.',
            'The notification bell is your review queue for new reservations, expense requests, quotes, purchase activity, purchase receiving, and front-desk bill-adjustment requests.',
        ],
    },
    {
        title: '📅 Reservations & Quotes',
        icon: '📅',
        steps: [
            'Operations → Reservations: full list of all bookings. Filter by date range, room type, status (Confirmed, Checked-In, Checked-Out, Cancelled, No-Show), or guest name.',
            'Create a new reservation: click "+ New Reservation", search for or create a guest profile, select a room type, set check-in and check-out dates, add extras, and Save.',
            'Website reservations remain unassigned to a physical room until staff choose one. Use Room Assignment when the stay is ready to be attached to a specific clean room.',
            'Modify a reservation: click its row → Edit. Change dates, upgrade the room, add services, or apply a managerial discount. All changes log to the reservation history.',
            'Reservation detail pages now include bill-adjustment requests. Review the request notes, approve or reject it, and the folio updates automatically if you approve.',
            'Cancellations: open the reservation → Cancel. Select the cancellation reason and whether to apply a fee. The room returns to available immediately.',
            'Financial → Quotes: create price quotes for prospective guests or corporate clients. Set the room type, dates, inclusions, discount, and validity period. Once the client approves, convert to a reservation in one click.',
            'Hall Bookings: manage event-space reservations including setup/breakdown time, catering add-ons, and AV requirements.',
            'Group Bookings: use for parties of 10+ rooms under one account. Set a group rate and manage individual room assignments from one parent record.',
        ],
    },
    {
        title: '🔑 Check-in & Check-out',
        icon: '🔑',
        steps: [
            'Operations → Check In: list of all arriving guests today. You can filter by arrival time, room type, or VIP status.',
            'To check in a guest: click their row → verify ID → confirm room assignment → click "Check In". Room status updates to Occupied instantly.',
            'Early arrivals: if the room is not ready, place the guest in the lounge and mark the reservation "Pre-Checked In". The front desk will complete it when the room is clean.',
            'Operations → Check Out: today\'s departing guests. Click a row to open the folio — review all charges. Use "Add Charge" for any missing items, "Apply Discount" for manual adjustments.',
            'Express checkout: for guests who pre-settled their bill, click "Express Checkout" to skip the payment screen.',
            'Police Report: for properties that need to report guest arrivals to authorities, go to Operations → Police Report, select the date, and download or print the formatted report.',
            'After checkout, the room status changes to Dirty. Housekeeping tasks generate automatically for the departing rooms.',
        ],
    },
    {
        title: '🏨 Property Management',
        icon: '🏨',
        steps: [
            'Property Management → Rooms: view and edit each room\'s details. Change status (Available, Out of Order, Under Renovation), update base rate, or add a room note visible to front desk.',
            'Rooms with any booking history cannot be deleted — set them to Out of Order to remove from availability.',
            'Property Management → Room Types: update base rates here and they automatically apply to all new bookings of that type. You can also set peak-season rates.',
            'Amenities: manage the amenity list (Wi‑Fi, sea view, balcony, jacuzzi, safe…) and attach them to room types. Use Property Management → Room Amenities to add, edit, and remove amenity records; set descriptions, icons, and premium flags so they appear correctly on guest confirmations and in room listings.',
            'Floors & Wings: organise your property structure. Assign rooms to floors and floors to wings for accurate housekeeping reporting.',
            'Halls: manage event spaces with their own capacity, rates, and amenity sets. Hall bookings appear in the calendar separately from room bookings.',
        ],
    },
    {
        title: '👤 Staff Management',
        icon: '👤',
        steps: [
            'Employee → Staff Management: view all active employees, their role, current shift assignment, and today\'s attendance status.',
            'To add a new staff member: go to Users & Groups → Users → "+ New User". Fill in personal details, assign the appropriate role, and set a temporary password to share with them.',
            'Work Shifts: define templates (Morning 7am–3pm, Afternoon 3pm–11pm, Night 11pm–7am) under Employee → Work Shifts.',
            'Staff Schedules: assign shifts to individual staff members using the weekly schedule grid. Tap any cell to assign or remove a shift.',
            'Time Tracking: view clock-in/out logs for all staff under Employee → Time Tracking. Manually correct missed punches to ensure payroll accuracy.',
            'Housekeeping Tasks: assign daily cleaning tasks to housekeeping staff. Use bulk-assign to quickly distribute a full floor\'s tasks to multiple cleaners at once.',
            'Performance tracking: view which cleaners complete tasks fastest and which maintenance staff resolve issues most efficiently via Reports → Staff.',
        ],
    },
    {
        title: '🛒 POS & Bar Sales',
        icon: '🛒',
        steps: [
            'POS → Products: manage all bar and restaurant products. Set the selling price, cost price, category, and minimum stock level for each item.',
            'POS → Categories: organise products into categories (Cocktails, Spirits, Beer, Wine, Soft Drinks, Food…). Categories appear as tabs on the cashier screen.',
            'POS → Sales Register: the live point-of-sale screen used by bartenders and servers. You can observe any active session or process a sale yourself.',
            'POS → Sales History: every recorded sale. Filter by date, cashier, customer, or payment method. Click any sale to see the itemised receipt and reprint if needed.',
            'Search by client: use the "Search by Name" text box in Sales History to find all sales made to a specific guest or walk-in customer.',
            'Delete products: you can delete individual products or use "Delete All" to clear the full catalogue when doing a complete menu refresh. Existing sales records are not affected.',
            'Low stock alerts: products below their minimum stock quantity appear highlighted. Restock them via POS → Products → Adjust Stock.',
        ],
    },
    {
        title: '🔧 Maintenance',
        icon: '🔧',
        steps: [
            'Maintenance → Maintenance Requests: triage all open issues. Sort by priority to address the most urgent items first.',
            'Assign requests: click a request → "Assign Technician" → select from available maintenance staff. The technician sees it immediately on their dashboard.',
            'Priority levels you can set: Urgent (fix within 2 hours — use for safety risks), High (same day), Medium (within 3 days), Low (next available slot).',
            'Track progress: request statuses move through Open → Assigned → In Progress → Awaiting Parts → Resolved. Follow up on anything In Progress for more than expected.',
            'Log an issue yourself: click "+ New Request", select the area, category (Electrical, Plumbing, HVAC, Furniture, Elevator…), priority, and describe the problem.',
            'Preventive Maintenance: check that scheduled inspections are completed on time. Overdue items appear in red.',
        ],
    },
    {
        title: '💰 Financial & Budget',
        icon: '💰',
        steps: [
            'Financial → Transactions: every payment and charge in the system. Use the date filter to view a specific day, week, or month. Export to CSV for further analysis.',
            'Financial → Invoices: all guest invoices. Generate invoices from the reservation folio, then download as PDF or email directly to the guest.',
            'Financial → Expenses: review all operating expenses submitted by staff. Approve legitimate expenses and flag anomalies.',
            'Budget → Dashboard: bar charts showing each department\'s spend vs allocated budget for the current month. Red = over budget, Amber = within 10% of limit, Green = on track.',
            'Budget → Pending Approvals: expense requests from front desk, housekeeping, and maintenance staff arrive here. Approve with a note or reject with a reason.',
            'Budget → Create Budget: set monthly spending allocations per department. Adjust mid-period if needed without losing the original allocation in history.',
        ],
    },
    {
        title: '📊 Reports',
        icon: '📊',
        steps: [
            'Reports → Reports: eight report categories in one place — Occupancy, Revenue, Financial, Inventory, Guests, Staff, Maintenance, and Executive Summary.',
            'Occupancy Report: room utilisation by type, monthly trend, RevPAR, ADR, and average length of stay. Identify which room types drive the most revenue.',
            'Revenue Report: income by source (room nights, services, POS, events), by room type, and by period. Download as PDF or Excel.',
            'Staff Report: attendance summary, housekeeping task completion rate, and maintenance resolution metrics — essential for performance reviews.',
            'Maintenance Report: total requests by category, average resolution time, most problematic rooms. Use it to plan preventive work.',
            'All reports are branded with the hotel logo and can be downloaded for owner presentations or audits.',
        ],
    },
]

// ─── FRONT DESK ───────────────────────────────────────────────────────────────
const frontDeskTutorial = [
    {
        title: '🏠 Front Desk Dashboard',
        icon: '📊',
        steps: [
            'Your dashboard shows today\'s arrivals, departures, current occupancy count, and rooms that need attention — this is your daily command centre.',
            'The Arrivals list on the left and Departures list on the right update in real time as check-ins and check-outs are processed.',
            'Room Status grid at the bottom shows every room\'s colour-coded state: green (Available), blue (Occupied), orange (Dirty), teal (Clean/Ready), grey (Out of Order).',
            'Click any room in the grid to see its current guest, reservation number, and a quick link to that reservation.',
            'Notifications bell: front desk only sees operational alerts that support the shift, such as new online bookings, bill-adjustment decisions, overdue departures, and room-assignment or room-readiness issues.',
        ],
    },
    {
        title: '📅 Creating Reservations',
        icon: '📅',
        steps: [
            'Operations → Reservations → "+ New Reservation": start by searching for the guest — type the name or email in the guest search box.',
            'If the guest is new, click "Create New Guest" in the dropdown when no results are found. Fill in first name, last name, phone, email, and ID number.',
            'Select a Room Type. The system shows available types for your chosen dates and the nightly rate for each.',
            'Set check-in and check-out dates. The system prevents booking rooms that are already occupied for those dates.',
            'Adults and Children: enter the correct count for the record and for billing (some services charge per person).',
            'Add extras: click "+ Add Service" to attach pre-booked services (airport pickup, breakfast package, spa treatment) that will appear on the folio.',
            'Special Requests: note preferences in the Special Requests field (e.g. "high floor, twin beds, champagne on arrival"). Housekeeping can see this when preparing the room.',
            'Website reservations can appear in the same queue, but they do not lock a physical room automatically. Confirm the booking first, then assign a clean room later from Room Assignment.',
            'Save the reservation. The guest receives a confirmation email automatically if their email was entered.',
        ],
    },
    {
        title: '🏛️ Hall Bookings',
        icon: '🏛️',
        steps: [
            'Use Hall Bookings when a guest or client needs an event space instead of a guest room. Open Operations → Hall Bookings to see all current and upcoming event reservations.',
            'Create a hall booking by selecting the hall, event date, start and end times, organiser details, and any setup or breakdown notes.',
            'Add service details like catering, projector setup, seating style, or extra staffing in the notes so operations teams can prepare correctly.',
            'Review the hall booking before confirmation to avoid time clashes with existing events, then update the status as the event moves from enquiry to confirmed or completed.',
        ],
    },
    {
        title: '🔑 Check-in Process',
        icon: '🔑',
        steps: [
            'Operations → Check In: shows every guest due to arrive today, sorted by expected arrival time.',
            'Find the guest\'s row and click it. Their reservation details open — verify the room type, number of nights, and any special requests.',
            'ID Verification: check the guest\'s passport or ID. Note the ID number on the reservation if required by local law.',
            'Room Assignment: if a specific room was not pre-assigned, click the Room field and choose from the available clean rooms of the correct type.',
            'Early Check-in: if the guest arrives before standard check-in time and the room is ready, you can check in normally. If not ready, tick "Pre-Checked In (awaiting room)" and the room is assigned when it becomes clean.',
            'Click "Confirm Check In". The room status changes to Occupied instantly. Housekeeping sees it as occupied and will not enter without a do-not-disturb override.',
            'Key Cards: after check-in, go to Key Cards → "+ New Card". Select the room and set the expiry date (usually check-out date). Hand the physical card to the guest.',
            'If the reservation came from the website and still has no room, use Room Assignment before final check-in. The room is chosen by staff at the desk, not by the online booking itself.',
            'Tip: use the "Add Note" field on the checked-in reservation to record anything unusual — e.g. "Guest brought extra luggage, stored in room 102 storage".',
        ],
    },
    {
        title: '🔓 Check-out Process',
        icon: '🔓',
        steps: [
            'Operations → Check Out: all guests departing today, sorted by expected departure time. Guests shown in red have passed their checkout time.',
            'Click the guest\'s row to open their folio. The folio is the complete bill — it lists every charge since check-in: room nights, services, laundry, POS bar charges, minibar, etc.',
            'Review carefully: check that all nights are billed at the correct rate, and that all services requested during the stay appear.',
            'Missing charge? Click "Add Charge" → select the service type → enter the amount and a description → Add. The folio total updates immediately.',
            'Guest discount: click "Apply Discount" → enter the amount or percentage and a reason (e.g. "Manager approved loyalty discount") → Apply.',
            'Payment: click "Record Payment". Select the method (Cash, Card, Bank Transfer, Mobile Money), enter the amount. For card payments, enter the last 4 digits as a reference.',
            'Partial payment: if the guest pays in instalments, record each payment separately. The "Balance Due" field tracks the remaining amount.',
            'Click "Confirm Check Out". The room status changes to Dirty. A housekeeping task is created automatically.',
            'Print or email the invoice: click "Print Invoice" for a paper copy or "Email Invoice" to send it to the guest\'s email. The invoice is also saved under Financial → Invoices.',
        ],
    },
    {
        title: '🏠 Room Assignment & Status',
        icon: '🏠',
        steps: [
            'Operations → Room Status: a full-screen colour-coded board of every room and its current state. Refresh this regularly throughout your shift.',
            'Click any room tile to see who is in it (if occupied), the check-out date, and a quick link to their reservation.',
            'To manually change a room\'s status (e.g. you know a room is clean but the task hasn\'t been marked complete yet): click the room → Change Status → select the correct state.',
            'Room Assignment for walk-ins: go to Reservations, find the walk-in booking, click Edit → assign a specific clean room from the available list.',
            'Do Not Disturb: if a guest has a DND request active, their room shows a DND badge on the status board. Do not send housekeeping until the badge is cleared.',
            'Blocked rooms: rooms set to Out of Order or Under Maintenance show as grey and cannot be assigned to new reservations.',
            'Use Room Assignment for website bookings and unassigned reservations. Those bookings stay pending until you choose a physical room, so the online booking does not occupy inventory by itself.',
        ],
    },
    {
        title: '💳 Payments & Folios',
        icon: '💳',
        steps: [
            'Every reservation has a Folio — its running bill. Access it by opening a reservation → Folio tab.',
            'Pre-authorisation: for guests paying by card, you can record a pre-auth (hold) on the folio. This reserves funds without charging yet.',
            'Record a payment at any point during the stay: Folio → Add Payment → select method → enter amount → Save.',
            'Partial payments are fully supported. The folio shows "Amount Paid", "Balance Due", and "Total Charges" clearly.',
            'If a guest disputes a bill, use "Request Bill Adjustment" from the reservation page. Front desk can submit requests for active stays, checked-out stays, and even fully paid folios; admin or manager must validate the change before it applies.',
            'Refunds: if a charge needs reversing, open the payment record → Refund → enter the amount and a reason. The refund appears as a negative entry on the folio.',
            'Folio split: for guests who want separate bills (e.g. business vs personal expenses), use "Split Folio" to create a second folio for the same reservation.',
            'Watch the notification bell for bill-adjustment approvals or rejections so you can update the guest immediately without refreshing several pages.',
            'All Payments (Financial → Transactions): see every payment processed across all reservations, filterable by date, type, and amount.',
        ],
    },
    {
        title: '💬 Quotes',
        icon: '💬',
        steps: [
            'Financial → Quotes: send a formal price proposal to a prospective guest before they commit to booking.',
            'Click "+ New Quote": select the room type, proposed dates, any included services, and set any discount. Add a validity date (e.g. quote expires in 7 days).',
            'The quote is saved and can be emailed to the guest. When they accept, click "Convert to Reservation" — all quote details carry over automatically.',
            'Quote statuses: Draft (not sent), Sent, Accepted (converted), Rejected, Expired. Track pending quotes in the Quotes list.',
            'If a guest calls to enquire about pricing, create a quick quote on the spot and email it while they are on the phone.',
        ],
    },
    {
        title: '🛎️ Services & Concierge',
        icon: '🛎️',
        steps: [
            'Services is the operational catalogue for guest extras like airport pickup, room service, spa, laundry, transfers, and ad-hoc charges that must reach the folio correctly.',
            'Services → Concierge: all active guest requests appear here. Each request has a status — New, In Progress, Completed.',
            'To add a service request from a guest: click "+ New Request", select the reservation, choose the service type, and add any notes.',
            'Assign the request to the appropriate department (Housekeeping for extra towels, Room Service for food, Maintenance for a broken item).',
            'The assigned department sees the task in their dashboard. Mark the request Complete once you confirm it has been handled.',
            'Add a service charge to the folio: open the reservation → Folio → Add Service → select from the service catalogue → confirm. The charge appears on the checkout bill.',
            'Laundry requests: go to Services → Laundry → "+ New Order". Select the room, log each garment type and quantity, then set the status to Collected. Update to Ready when returned and Delivered once the guest receives the items.',
            'Use concierge notes to record timing, guest preferences, and follow-up instructions so another front desk agent can continue the request without losing context.',
        ],
    },
    {
        title: '👥 Guest Profiles',
        icon: '👥',
        steps: [
            'Operations → Guests: search for any past or current guest by name, email, phone number, or reservation number.',
            'Click a guest\'s name to open their profile — it shows all past stays, total amount spent, preferences, and attached notes.',
            'Add a preference note: open the guest profile → "+ Add Note". Notes like "prefers ground floor", "lactose intolerant", "VIP — always upgrade if available" appear to all staff.',
            'VIP guests: mark a guest as VIP in their profile. A gold star badge appears on their arrivals list entry so front desk staff know to give priority attention.',
            'Guest history: the stays tab shows every reservation the guest has had — room types, dates, total spent. This is invaluable for loyalty conversations.',
            'If two duplicate guest profiles exist, contact the admin to merge them to keep history clean.',
        ],
    },
]

// ─── HOUSEKEEPING ─────────────────────────────────────────────────────────────
const housekeepingTutorial = [
    {
        title: '🏠 Housekeeping Dashboard',
        icon: '📊',
        steps: [
            'Your dashboard shows rooms that need cleaning today, tasks assigned specifically to you, tasks in progress, and completed tasks for your shift.',
            'Colour legend: 🟠 Dirty (needs cleaning), 🟡 In Progress (being cleaned), 🟢 Clean (completed), 🔵 Occupied (guest still in room — clean only if assigned), ⚫ Out of Order (skip).',
            'Priority tasks are highlighted at the top: checkout rooms need to be turned over first so front desk can re-assign them to arriving guests.',
            'Your schedule for the week is visible on the dashboard — check it at the start of every shift to know which rooms are yours.',
            'Check the notification bell during your shift for re-clean requests, maintenance follow-up cleared by engineering, and any special room-preparation notes coming from front desk.',
            'If you see a room in "Dirty" status that was not assigned to you but other cleaners are busy, you can still take it — just update the task to put your name on it.',
        ],
    },
    {
        title: '📋 Daily Tasks',
        icon: '📋',
        steps: [
            'Go to Tasks → Daily Tasks to see your complete cleaning list for today. Tasks are sorted by floor and room number for efficient routing.',
            'Click any task to see its details: room number, floor, task type (Stayover Clean, Checkout Clean, Turndown Service, Deep Clean, Inspection), and any notes from reception.',
            'Important notes may include: "DND — guest requested no service", "VIP arrival — premium setup required", "Extra pillows needed", or "Pet in room — dogs".',
            'Click "Start Cleaning" when you enter a room. This timestamps your start and signals the room is In Progress. Front desk cannot re-assign the room while In Progress.',
            'Use the in-task checklist if one is shown: tick each item (toiletries replaced, bed made, floors mopped, bin emptied, mirror cleaned) to ensure nothing is missed.',
            'Click "Mark Complete" when the room is fully cleaned and inspected. The room status on the front desk dashboard changes to Clean immediately.',
            'If a task has a note like "VIP — Turndown Service": lay out the bed (fold corner back), place chocolates or amenities, turn on a bedside lamp, and close curtains.',
            'Turndown tasks typically appear in the late afternoon for occupied rooms of VIP or long-stay guests.',
        ],
    },
    {
        title: '🔍 Room Inspection',
        icon: '🔍',
        steps: [
            'After completing a task, a supervisor may inspect the room before marking it officially clean and ready for guests.',
            'If you are a senior cleaner assigned inspections: go to Tasks → Inspections. Click the room to open the inspection checklist.',
            'Inspection checklist typically covers: bed & linen quality, bathroom cleanliness, floor condition, amenities restocked, minibar checked, windows and mirrors smear-free.',
            'If the room passes: click "Approve — Mark Ready". The room status updates to green (Available/Clean) and can be assigned to arriving guests.',
            'If the room fails: click "Needs Re-clean", add a note describing what was missed, and the task goes back to the assigned cleaner with your feedback.',
        ],
    },
    {
        title: '🔧 Reporting Maintenance',
        icon: '🔧',
        steps: [
            'If you discover a fault while cleaning — a broken lamp, leaking tap, damaged furniture, faulty air-con, stained carpet — report it immediately.',
            'While in the task view: click "Report Issue" (the wrench icon). This pre-fills the room number for you.',
            'Alternatively, go to Maintenance → Report Issue from the sidebar. Select the room or area from the dropdown.',
            'Select the category (Electrical, Plumbing, HVAC, Furniture, General), set the urgency (Urgent = guest safety risk, High = affects stay, Medium = fix soon, Low = cosmetic), and describe the problem clearly.',
            'Add photos if you have them — photos dramatically speed up the maintenance team\'s response and diagnosis.',
            'If the fault makes the room unsafe or unfit for guests (e.g. flood, broken door lock, electrical sparks): change the room status to Out of Order from the task screen before leaving, then report immediately.',
            'You will receive a notification when the maintenance team resolves the issue and the room is safe to clean again.',
        ],
    },
    {
        title: '📦 Supplies & Inventory',
        icon: '📦',
        steps: [
            'Supplies → Supplies: view the current stock of cleaning products (chemicals, mops, cloths), toiletries (soap, shampoo, conditioner, shower gel), and linens (sheets, towels, pillowcases).',
            'Check the minimum stock column — highlighted red items are below minimum and need reordering urgently.',
            'To request restocking: Supplies → Request Supplies → select the item and quantity needed → Submit. Your manager or admin will approve and arrange the order.',
            'Linen tracking: Supplies → Linens → log the quantities of linens used per room per day (e.g. Room 201: 2 sheets used, 4 towels used). This feeds into laundry scheduling.',
            'After linen collection: update the linen log to "Sent to Laundry". When fresh linens arrive back: update to "In Stock — Ready for Use".',
            'Amenity tracking: for rooms with a premium amenity set, log which amenities were used per room so replenishment is accurate. Never over-stock rooms to avoid waste.',
        ],
    },
    {
        title: '📅 Schedules & Shifts',
        icon: '📅',
        steps: [
            'Your weekly schedule (which days and shifts you work) is visible on the dashboard under "My Schedule".',
            'Shift types: Morning (usually 7am–3pm) handles morning cleans and checkout turnovers. Afternoon (3pm–11pm) handles late checkout turnovers and evening turndowns.',
            'Scheduled deep cleans (full room strip and deep clean) appear in your task list on the assigned date.',
            'Annual scheduled inspections (checking window seals, grout condition, curtain tracks) also appear as tasks on their scheduled dates.',
            'If you cannot attend your shift, notify your supervisor as early as possible — last-minute absences mean rooms go uncleaned and guests cannot check in.',
            'Overtime or shift swaps: inform your manager and they will update the schedule in the system so task assignments reflect who is actually working.',
        ],
    },
]

// ─── MAINTENANCE ─────────────────────────────────────────────────────────────
const maintenanceTutorial = [
    {
        title: '🏠 Maintenance Dashboard',
        icon: '📊',
        steps: [
            'Your dashboard shows all open requests, their priority colour (🔴 Urgent, 🟠 High, 🟡 Medium, 🟢 Low), current status, and who they are assigned to.',
            'The Urgent counter at the top requires immediate attention — these are safety-critical issues. Address them before anything else.',
            'The "My Tasks" section shows only requests assigned directly to you today. Work through these in priority order.',
            'Completed tasks counter shows how many requests you have resolved this week — useful for your performance record.',
            'Use the notification bell for newly assigned jobs, reopened issues, and front-desk escalations tied to occupied or soon-to-arrive rooms.',
            'Overdue tasks (past expected resolution time) are shown in red. If you cannot resolve by the deadline, update the status with a progress note so management is aware.',
        ],
    },
    {
        title: '📝 Working Maintenance Requests',
        icon: '📝',
        steps: [
            'Maintenance → Maintenance Requests: full list. Use the filters to show Open, Assigned, In Progress, Awaiting Parts, or Resolved requests.',
            'Click any request to view: the full description, affected room or area, reporter name and time, photos attached, and priority level set by the manager.',
            'Accept a request: click "Start Work". Your name is recorded as the technician and the status moves to In Progress. This also timestamps your start.',
            'Work through the issue. Use the Notes field to log what you found and what steps you are taking — this creates a useful maintenance history for the room.',
            'If parts or tools are needed: update status to "Awaiting Parts", add a note with exactly what is needed and where you will source it. Notify your manager if it will delay resolution.',
            'On completion: click "Mark Resolved". Add a completion note describing what was done, what was replaced or repaired, and any follow-up recommended.',
            'Resolution note examples: "Replaced faulty light switch in bathroom — check in 2 weeks", "Re-sealed shower tray grout — allow 24h to cure before guest use".',
            'After resolving a request that had the room as Out of Order: remember to notify reception so they can update room status and re-assign it to arriving guests.',
        ],
    },
    {
        title: '➕ Logging New Issues',
        icon: '➕',
        steps: [
            'You can log issues yourself — if you spot a problem while doing other work, click "+ New Request" immediately.',
            'Location: select the specific room number, or choose a common area (Lobby, Corridor Floor 2, Gym, Pool, Restaurant…).',
            'Category: pick the most accurate category — Electrical, Plumbing, HVAC/Air Conditioning, Furniture & Fixtures, Elevator/Lift, Fire Safety, IT & Network, Exterior/Grounds.',
            'Priority: set honestly. Urgent = fire safety, flooding, total power outage, broken guest room door lock. High = no hot water, no AC in occupied room. Medium = intermittent fault, cosmetic damage. Low = minor wear.',
            'Description: be specific. Not "AC broken" but "Room 305 — AC unit does not cool below 28°C even on max setting, making unusual clicking sound since check-in on Monday".',
            'Photos: attach images of the fault — they instantly communicate the problem better than text alone and help identify if specialist tools or parts are needed.',
            'After submitting, the request appears in the queue for a manager to assign. If it is Urgent, call your manager directly as well.',
        ],
    },
    {
        title: '📋 Preventive Maintenance',
        icon: '📋',
        steps: [
            'Preventive → Scheduled: all recurring inspections and service tasks across the property. Sorted by next due date.',
            'Common scheduled tasks: HVAC filter cleaning (monthly), fire extinguisher inspection (quarterly), elevator service (annually), boiler check (annually), generator test run (monthly).',
            'Click a scheduled task to open the inspection checklist. Work through each item methodically — tick only what you have actually verified, not what you assume is fine.',
            'Checklist items for HVAC example: "Filter cleaned/replaced", "Coils cleaned", "Refrigerant level checked", "Thermostat tested", "Air flow adequate".',
            'When the full checklist is complete: click "Mark Inspection Complete". Add a note if anything was found and corrected. The next occurrence schedules automatically.',
            'If a scheduled item fails (e.g. fire extinguisher is expired): log a corrective maintenance request immediately and note the failure in the preventive task record.',
            'Never skip a preventive task — failed inspections can void insurance, violate safety regulations, and lead to expensive emergency breakdowns.',
        ],
    },
    {
        title: '📺 IPTV & Equipment',
        icon: '📺',
        steps: [
            'IPTV → Devices: all in-room television units with their room assignment, IP address, serial number, and current status (Active, Offline, Fault).',
            'If a guest reports a TV problem: go to IPTV → Devices, find the room\'s device, and check if it shows as Offline (network issue) or Active (settings or hardware fault).',
            'Common fixes: restart the device (unplug for 30 seconds, replug), check the ethernet cable is seated, verify the room\'s network port is active.',
            'If the device needs replacement: update its status to "Faulty — Replaced", enter the new device\'s serial number and MAC address, and set to Active.',
            'Wiring/cabling issues: if multiple rooms on the same floor lose IPTV simultaneously, the issue is likely the floor network switch or the IPTV server — escalate to IT or a specialist.',
            'Log every TV fault and repair as a maintenance request so there is a complete service history for each device.',
        ],
    },
    {
        title: '🔩 Parts & Spare Management',
        icon: '🔩',
        steps: [
            'Keep a personal log (or check with your manager) of which spare parts are available on-site: light bulbs, fuses, tap washers, door handles, remote controls, etc.',
            'When you use a spare part for a repair, note it in the resolution note of the maintenance request (e.g. "replaced 15A fuse from stores").',
            'If a part is not available: log what is needed in the "Awaiting Parts" status note so management can order it. Include the exact part specification — brand, model, size.',
            'Suggest to your manager when recurring faults indicate a systemic issue needing a bulk fix — for example, if 10 rooms have the same faulty door lock model, proactively replace all of them.',
        ],
    },
]

// ─── HR ───────────────────────────────────────────────────────────────────────
const hrTutorial = [
    {
        title: '🏠 HR Dashboard',
        icon: '📊',
        steps: [
            'The HR Dashboard gives you a complete workforce snapshot: total headcount, active employees today, today\'s attendance rate (%), pending leave requests, and open recruitment positions.',
            'The attendance widget shows a live count of who has clocked in vs who was scheduled. Click it to see exactly who is absent and whether it is approved leave or unexplained.',
            'Pending leave requests badge: address these quickly — staff need decisions before they can finalise personal plans.',
            'Anniversary and birthday alerts appear in the notification panel — a simple acknowledgement boosts morale significantly.',
            'Use the notification bell for leave requests, onboarding tasks, review reminders, and other HR actions that need attention between payroll or recruitment work.',
            'Quick links at the top of the dashboard take you directly to the most common HR tasks: Add Employee, Approve Leave, Run Payroll.',
        ],
    },
    {
        title: '👥 Employee Management',
        icon: '👥',
        steps: [
            'HR → Employees: complete staff directory with name, department, position, hire date, contract type (Permanent, Part-time, Contract), and current status.',
            'Click "+ New Employee": enter personal details (name, date of birth, gender, nationality), contact information, emergency contact, position, department, and start date.',
            'Upload documents directly to the employee profile: ID/passport scan, employment contract, tax forms, certificates. Keep these up to date for compliance.',
            'Contract expiry: the system alerts you 30 days before a contract expires. Use this window to initiate renewal discussions with the employee and manager.',
            'Employee profile tabs: Personal → contact and personal info. Employment → role, salary, contract. Documents → uploaded files. Leave → balance and history. Performance → review history. Training → courses completed.',
            'To terminate: open the employee\'s profile → End Employment tab → set the last working date → select the reason (Resignation, Redundancy, Misconduct, Contract End) → Save. Their access is revoked on the last working day.',
            'Transferred employee: if someone changes department, update their profile → Employment tab → change Department and Position. History of the old role is preserved.',
        ],
    },
    {
        title: '🏢 Departments & Positions',
        icon: '🏢',
        steps: [
            'HR → Departments: view all operational departments (Front Desk, Housekeeping, Maintenance, Food & Beverage, Administration, Finance, HR…).',
            'Create a department: click "+ New Department", enter a name, assign a department head (must be an existing user), and set a cost centre code if used for accounting.',
            'Each employee must belong to exactly one department. Assign or move employees via their profile → Employment tab.',
            'HR → Positions: manage job titles (Receptionist, Senior Receptionist, Chief Engineer, Sous Chef…). Positions belong to departments and can have a salary grade attached.',
            'Organisational chart: view the reporting structure under HR → Org Chart. This shows who reports to whom, useful for new employees understanding the hierarchy.',
        ],
    },
    {
        title: '⏰ Attendance & Time Tracking',
        icon: '⏰',
        steps: [
            'HR → Attendance: view daily clock-in/clock-out records for all staff. Filter by department, employee name, or date range.',
            'Each row shows: employee name, department, scheduled shift, clock-in time, clock-out time, total hours worked, and status (Present, Late, Absent, Half-Day).',
            'A clock-in more than 15 minutes after shift start is automatically flagged as Late. You can override flags manually if the reason is valid (e.g. manager-approved late start).',
            'Correct a missing record: click the blank entry → Add Attendance → enter the actual times with a correction note. Include why the automatic record is missing (e.g. "system was offline", "employee forgot to clock in").',
            'Attendance export: filter the date range for a pay period → click "Export to Excel". This file feeds directly into payroll calculations.',
            'Monthly attendance summary: use the department filter to view your team\'s full month — quickly identify patterns (e.g. a specific employee always late on Mondays).',
        ],
    },
    {
        title: '💰 Payroll',
        icon: '💰',
        steps: [
            'HR → Payroll: manage monthly salary processing. Select the pay period (e.g. March 1–31, 2026) to start.',
            'The system pre-calculates gross pay based on: base salary + overtime hours (from attendance) + approved bonuses - approved deductions.',
            'Review each employee\'s line: check hours worked match attendance records, any overtime is authorised, and deductions (tax, pension, absence) are correctly applied.',
            'Add a one-off bonus: click the employee row → "+ Add Bonus" → enter amount and reason (e.g. "Q1 performance bonus"). It is included in the current payrun.',
            'Add a deduction: click "Add Deduction" → enter amount and reason (e.g. "Uniform advance recovery"). Statutory deductions (income tax, social security) are pre-configured.',
            'Click "Generate Payslips" to calculate net pay for all employees. Review the summary totals — check they are consistent with last month.',
            'Mark payroll as Paid after salaries are disbursed. Employees can view their digital payslip from their profile at any time.',
        ],
    },
    {
        title: '🏖️ Leave Management',
        icon: '🏖️',
        steps: [
            'HR → Leave Management: all leave requests listed with employee name, leave type, dates, duration, and current status (Pending, Approved, Rejected).',
            'Leave types: Annual Leave, Sick Leave, Maternity/Paternity Leave, Emergency Leave, Study Leave, Unpaid Leave.',
            'Approve a request: click the request → review the reason and dates → click "Approve". The employee\'s leave balance decreases automatically.',
            'Reject a request: click "Reject" → add a clear reason (e.g. "Busy season — insufficient cover available. Please reapply for a different period").',
            'Counter-propose: if the dates are problematic, you can message the employee directly and ask them to resubmit for alternative dates.',
            'Log leave on behalf of an employee: click "+ New Leave Request" → select the employee → fill in dates and type. Useful for sick leave when the employee calls in and cannot log it themselves.',
            'Leave balance: each employee\'s remaining leave days are visible on their profile and on the leave request screen. Entitlements reset on the employee\'s anniversary date.',
            'Calendar view: use HR → Leave Calendar to see all approved leave on a calendar grid — instantly identify days with many absences and plan cover accordingly.',
        ],
    },
    {
        title: '🔍 Recruitment',
        icon: '🔍',
        steps: [
            'HR → Recruitment: manage the full hiring pipeline from job posting to onboarding.',
            'Create a job posting: click "+ New Position" → enter job title, department, required qualifications, responsibilities, contract type, and salary range. Set the closing date.',
            'As applications arrive, add them to the system: Applicant → "+New Applicant" → attach their CV and cover letter, note the source (referral, job board, walk-in).',
            'Pipeline stages: Applied → Shortlisted → Phone Screen → Interview Scheduled → Interview Complete → Offer Sent → Offer Accepted → Hired → Onboarding.',
            'Move applicants between stages by clicking their card and selecting the next stage. Add interview notes and scores at each stage.',
            'Declined applicants: click "Decline" and select the reason. A polite rejection email can be sent from within the system.',
            'Once an offer is accepted: click "Convert to Employee" on the applicant record. Their contact details pre-fill the new employee form — complete the remaining employment fields and save.',
        ],
    },
    {
        title: '📊 Performance & Training',
        icon: '📊',
        steps: [
            'HR → Performance: conduct and record formal performance reviews for each employee.',
            'Create a review cycle: select the review period (Quarterly, Annual), and the employees to include. Assign a reviewer (usually their direct manager).',
            'Each review has: KPI scores (1–5 rating), qualitative comments, goal achievement summary, and development recommendations.',
            'After completing a review, both the reviewer and employee sign off on it (digitally). It is then locked and stored permanently on the employee\'s profile.',
            'HR → Training: create and track training sessions for teams or individuals.',
            'New training session: click "+ New Training" → set topic, date and time, trainer name, venue or online link, and select attendees from the staff list.',
            'Mark a session Complete after delivery. Attendance is recorded per attendee. The training appears on each participant\'s profile as a completed qualification.',
            'Mandatory training reminders: if a training is set as mandatory and recurring (e.g. annual fire safety training), the system alerts HR and the employee when the next due date approaches.',
        ],
    },
]

// ─── BARTENDER / SERVER ──────────────────────────────────────────────────────
const bartenderTutorial = [
    {
        title: '🏠 Dashboard',
        icon: '📊',
        steps: [
            'Your dashboard shows today\'s total sales, number of transactions processed in your shift, revenue generated, and stock alerts for items running low.',
            'The Recent Sales list shows the last 10 transactions — useful to quickly confirm the last order if a customer queries something.',
            'Low Stock Alerts appear prominently on the dashboard when any product drops below its minimum threshold. Notify your manager to reorder before you run out.',
            'Check the notification bell for return requests, room-charge follow-up, and restocking updates that affect your bar shift.',
            'Your shift summary (if timekeeping is used) shows your clock-in time and hours worked so far.',
        ],
    },
    {
        title: '🛒 POS Sales Register',
        icon: '🛒',
        steps: [
            'POS → Sales Register: your main cashier screen. Products are organised in category tabs (Cocktails, Spirits, Beer, Wine, Soft Drinks, Snacks…).',
            'Add an item to the cart: click its tile. The item appears in the Order panel on the right with quantity 1.',
            'Adjust quantity: click the + or − buttons next to the item in the cart, or click the item name and type the quantity directly.',
            'Remove an item: click the trash icon next to it in the cart, or swipe left on mobile.',
            'Apply an item discount: click the item in the cart → "Discount" → enter percentage or fixed amount → Confirm. Only managers can apply discounts above a set threshold.',
            'Customer name: optionally type the customer\'s name or tab number at the top of the order for identification. For hotel guests, link the order to their room.',
            'Order notes: add a special note to the order (e.g. "No ice", "Extra lime", "Allergy — no nuts") using the Notes field below the cart.',
            'Complete the sale: select the payment method (Cash, Card, Mobile Money) → enter amount received → click "Complete Sale". The receipt displays automatically.',
        ],
    },
    {
        title: '🏨 Charge to Room',
        icon: '🏨',
        steps: [
            'Hotel guests can have their bar bill charged directly to their room account — no cash needed at the bar.',
            'On the POS Register: after adding items to the cart, click "Charge to Room" instead of a payment method.',
            'Type the room number in the search box. The system shows the guest\'s name for confirmation — verify with the guest before confirming.',
            'Click "Confirm Room Charge". The amount posts directly to the guest\'s folio and will appear on their checkout bill.',
            'Always verify the guest\'s identity (ask their name) before accepting a room charge — do not allow strangers to charge to occupied rooms.',
            'If a room is checked out or the number does not exist, the system will show an error — ask the guest for their correct room number.',
        ],
    },
    {
        title: '📦 Products & Inventory',
        icon: '📦',
        steps: [
            'POS → Products: the full catalogue of bar items with their selling price, cost price, category, current stock, and minimum stock level.',
            'Stock overview: items highlighted in red or amber are at or below minimum stock. Notify your manager immediately so restocking can be arranged before you run out.',
            'After receiving a delivery: go to POS → Products → click the product → "Adjust Stock" → enter the quantity received → Save. Stock levels update instantly.',
            'New product: if your manager gives you approval, click "+ New Product" → fill in the name, category, price, cost, and initial stock quantity. Upload a product image for easy recognition on the POS screen.',
            'Mark unavailable: if a product runs out mid-shift, click it → "Mark Unavailable". It will be greyed out on the POS screen and cannot be added to orders. Re-enable when restocked.',
            'Recipe note: some cocktails may have a recipe note attached to the product. Click the product name in the catalogue to view the recipe card.',
        ],
    },
    {
        title: '💰 Payments & Receipts',
        icon: '💰',
        steps: [
            'After completing a sale, the receipt appears on screen automatically. Click "Print Receipt" to print a thermal receipt for the customer.',
            'Cash payment: enter the amount the customer handed you. The system calculates and displays the change due.',
            'Card payment: select "Card" → enter the last 4 digits of the card as a reference (or leave blank) → confirm. Do not enter full card numbers — process physical card payments on your card terminal separately.',
            'Mobile money / transfer: select the appropriate method. Enter the transaction reference number if provided by the customer.',
            'Split payments: if a customer pays part cash and part card, record the cash amount first → "Add Another Payment" → record the card amount. The system tracks both.',
            'Reprint a past receipt: go to POS → Sales History → find the sale → click "Reprint Receipt". You can reprint any past sale.',
        ],
    },
    {
        title: '📋 Sales History & Reporting',
        icon: '📋',
        steps: [
            'POS → Sales History: every sale ever recorded. Filter by date, payment method, cashier, or customer name.',
            'Search by client name: use the "Search by Name" text box to find all purchases made by a specific guest or walk-in customer — useful when a guest disputes a charge.',
            'Click any sale to view the full itemised breakdown, payment method, time, and cashier. From here you can reprint the receipt.',
            'Your shift summary: filter Sales History by today\'s date and your name to see your personal sales total for the shift.',
            'Void a sale: if a sale was processed incorrectly, notify your manager. Only managers can void completed sales — they will do so from the same sales history screen.',
            'Daily sales total: the dashboard shows a running total for the current day. Your manager reviews this against the cash drawer at end of shift.',
        ],
    },
    {
        title: '🔄 End of Shift',
        icon: '🔄',
        steps: [
            'At the end of your shift, count the physical cash in the drawer and note the total.',
            'Your manager or the system will compare this against the day\'s cash sales total. Report any discrepancy immediately — do not adjust it yourself.',
            'Card receipts: collect all printed card slips and hand them to your manager for reconciliation.',
            'Stock check: quickly count remaining stock of high-value or high-volume items (top spirits, popular beers) and record the counts if your property does this.',
            'Restock the bar station: flag any items that need restocking for the next shift using the low-stock notification on the dashboard.',
            'Clock out: if the system tracks your hours, remember to clock out before leaving. Missed clock-outs cause payroll discrepancies.',
        ],
    },
]

// ─── SERVER ─────────────────────────────────────────────────────────────────
const serverTutorial = [
    {
        title: '🏠 Dashboard',
        icon: '📊',
        steps: [
            'Your dashboard shows today\'s service revenue, recent orders, return activity, and your current shift schedule.',
            'Use the notification bell for return updates, room-charge issues, and service items that need quick follow-up during active meal periods.',
            'Recent Sales helps you confirm the last order quickly when a guest asks about an item or receipt.',
            'Revenue and sales widgets are live, so you can monitor how service periods are performing without leaving your dashboard.',
        ],
    },
    {
        title: '🍽️ POS Terminal & Orders',
        icon: '🛒',
        steps: [
            'Open POS Terminal to create restaurant and room-service orders. Add items by tapping the product tiles or searching by name.',
            'Adjust quantities, add order notes like allergies or special preparation requests, and confirm the order before taking payment.',
            'For hotel guests, use Charge to Room after verifying the room number and guest name. The order posts directly to the reservation folio.',
            'If an order needs correction after payment, notify your manager and use the return flow rather than editing a completed sale directly.',
        ],
    },
    {
        title: '📊 Reports & Schedule',
        icon: '📅',
        steps: [
            'Use Sales Reports to review your shift totals, item performance, and payment-method mix for the day.',
            'Revenue Reports help supervisors compare meal periods and room-service activity. Review them when preparing for busy service windows.',
            'My Schedule shows upcoming shifts and any changes made by management, so check it before clocking out.',
            'Return Reports list product returns and adjustments. Review them to make sure incorrect charges were handled properly and consistently.',
        ],
    },
]

// ─── ACCOUNTANT ───────────────────────────────────────────────────────────────
const accountantTutorial = [
    {
        title: '🏠 Accountant Dashboard',
        icon: '📊',
        steps: [
            'The Accountant Dashboard shows this month\'s total revenue, total expenses, net profit, outstanding invoice value, and overdue payment count.',
            'The revenue vs expense chart compares income against costs by day or month — watch for expense spikes that need investigation.',
            'Outstanding invoices widget: the dollar amount here represents money owed by guests. Click it to see the aged debtor list and chase overdue accounts.',
            'Cash flow summary: shows cash inflows (payments received) vs outflows (expenses paid) for the selected period. Negative cash flow days are highlighted in red.',
            'Pending budget approvals badge: flags expense requests from departments awaiting your review — address these to keep operations running smoothly.',
            'The notification bell helps you monitor new expenses, quotes, reservation activity, purchase orders, purchase receiving, and bill-adjustment events that affect financial records.',
        ],
    },
    {
        title: '💰 Transactions & Ledger',
        icon: '💰',
        steps: [
            'Financial → Transactions: the complete transaction ledger — every payment, service charge, room charge, refund, and adjustment posted to any guest folio or internal account.',
            'Filter options: date range, transaction type (Payment, Charge, Refund, Discount), payment method (Cash, Card, Bank Transfer, Mobile Money), and amount range.',
            'Each transaction row shows: date/time, reference number, guest or account name, description, amount, and the user who processed it.',
            'Click any transaction to view its full detail — including which reservation it belongs to, which user processed it, and any attached notes.',
            'Export: filter to your desired period → click "Export to CSV" or "Export to Excel" for use in your accounting software or external reporting.',
            'Reconciliation: compare daily Cash totals in the transactions export against physical cash counts to verify no shortfalls.',
        ],
    },
    {
        title: '🧾 Invoices & Quotes',
        icon: '🧾',
        steps: [
            'Financial → Invoices: all guest invoices across every reservation. Status options: Draft, Sent, Paid, Partially Paid, Overdue, Void.',
            'Generate an invoice from a reservation: open the reservation → Folio tab → click "Generate Invoice". The invoice is created with all charges pre-populated.',
            'Review before sending: check all line items, amounts, tax treatment, and guest details. Make corrections while still in Draft status.',
            'Send the invoice: click "Email Invoice" to send it directly to the guest\'s email. A PDF copy is always stored in the system.',
            'Download: click "Download PDF" to get a print-ready PDF with the hotel\'s letterhead and logo.',
            'Overdue invoices: sort by status → Overdue. These are guests who were invoiced but have not paid. Send a follow-up email or flag for the manager.',
            'Financial → Quotes: view all price proposals. Track which quotes converted to reservations and which expired without conversion — conversion rate is a useful sales metric.',
        ],
    },
    {
        title: '💸 Expenses',
        icon: '💸',
        steps: [
            'Financial → Expenses: every operating cost logged in the system — supplies, repairs, utilities, salaries, marketing, and miscellaneous.',
            'Filter expenses by department, category (Rooms, F&B, Maintenance, Administration, HR…), date range, or approval status.',
            'Review submitted expenses: click any expense to see the amount, description, supporting documents (receipts, invoices), and the submitting employee.',
            'Approve a legitimate expense: click "Approve". The amount is posted to the relevant cost centre and shows in Financial reports.',
            'Reject a questionable expense: click "Reject" → add a clear explanation. The submitting staff member receives the reason.',
            'Purchase orders and received stock should be reviewed alongside expenses so incoming stock and outgoing cash stay aligned. Use purchase notifications to jump into those records from the bell.',
            'Flag anomalies: repeated expense submissions for the same category, unusually large amounts, or expenses without receipts — these need investigation before approval.',
            'Monthly expense vs budget: compare total expenses by department against that department\'s budget using the Budget → Dashboard view.',
        ],
    },
    {
        title: '📊 Financial Reports',
        icon: '📊',
        steps: [
            'Reports → Reports → Financial: your primary reporting section. Access Profit & Loss, Revenue, Expense Breakdown, Invoice Ageing, and Payment Method Summary.',
            'Profit & Loss (P&L): shows revenue by category minus expenses by category for the selected period. Net profit figure at the bottom.',
            'Revenue Report: income broken down by source — Room Revenue, Service Revenue, POS/Bar Revenue, Event Revenue, Other. Compare period-over-period.',
            'Expense Report: costs by department and category. Identify which departments are overspending and investigate.',
            'Invoice Ageing Report: lists all unpaid invoices grouped by age (0-30 days, 31-60 days, 61-90 days, 90+ days). The older columns need urgent follow-up.',
            'Payment Method Report: how much was received in Cash vs Card vs Bank Transfer vs Mobile Money. Useful for cash flow management and reconciliation.',
            'Occupancy & RevPAR: even as an accountant, reviewing occupancy directly impacts revenue projections. Access these under the Reports → Occupancy tab.',
            'All reports export to PDF (for presentations) or Excel (for further analysis in your spreadsheet software).',
        ],
    },
    {
        title: '💲 Budget Management',
        icon: '💲',
        steps: [
            'Budget → Dashboard: bar charts showing each department\'s allocated budget vs actual spend for the current month. Instantly see which departments are on track and which are over.',
            'Click any department bar to drill down to its individual expense list for the period.',
            'Budget → Pending Approvals: expense requests from all departments land here for financial review. Your job is to verify that: the amount is reasonable, a receipt or justification exists, and it fits within budget.',
            'Approve with comment: add an accounting note when approving (e.g. "Approved — charge to Maintenance Cost Centre"). This helps with later reconciliation.',
            'Reject over-budget items: if a request would push a department over its allocation, reject it and advise the department head to seek a budget amendment.',
            'Budget amendments: if a department legitimately needs more than their allocated budget (unexpected repair, price increase), the admin creates a budget amendment. You should review its financial impact before it is approved.',
        ],
    },
    {
        title: '💼 Payroll Review',
        icon: '💼',
        steps: [
            'HR → Payroll: as the accountant, you review payroll before it is marked as Paid — you do not process it, but you verify financial accuracy.',
            'Check that: total gross salaries match HR records, overtime hours are authorised, bonus amounts are approved, and deductions (tax, pension) are correctly calculated.',
            'Compare this month\'s payroll total against last month\'s — flag significant variances for HR to explain.',
            'After your review, confirm to the HR manager or admin that the figures are correct so they can proceed with disbursement.',
            'Payroll expense appears in Financial → Expenses as "Payroll — [Month]". Verify it posts to the correct cost centre.',
        ],
    },
    {
        title: '👥 Customer Accounts',
        icon: '👥',
        steps: [
            'Customers: view the financial summary for each guest — total amount spent, open invoices, credit notes, and payment history.',
            'Corporate accounts: some guests may have credit accounts (pay at end of month). Monitor their outstanding balance and send statements regularly.',
            'Customer Groups: groups of guests (travel agents, corporates) may have negotiated rates. Verify that invoices generated for group bookings use the correct contracted rate.',
            'Credit notes: if a guest overpaid or is owed a refund, a credit note should be issued from their account. The credit note reduces the next invoice total.',
            'Audit: periodically cross-check that every guest who checked out has a paid or partially paid invoice — no checkouts should have a completely open invoice older than 7 days.',
        ],
    },
]

// ─── STAFF ──────────────────────────────────────────────────────────────────
const staffTutorial = [
    {
        title: '🏠 Dashboard',
        icon: '📊',
        steps: [
            'Your staff dashboard is focused on attendance and time tracking rather than hotel operations. Use it to see your current shift status and quick links to schedule tools.',
            'Check the notification bell for schedule changes, attendance reminders, and approvals that affect your working day.',
            'If you need access to operational screens like reservations or POS, ask an administrator to assign the correct role instead of sharing accounts.',
        ],
    },
    {
        title: '📅 My Schedule',
        icon: '📅',
        steps: [
            'Open My Schedule to see your upcoming shifts and assigned working days.',
            'Review schedule changes before each shift, especially if your department swaps coverage at short notice.',
            'If a shift looks incorrect, report it to your manager early so the change can be made before payroll or attendance discrepancies appear.',
        ],
    },
    {
        title: '⏱️ Timesheet & Clock In/Out',
        icon: '⏰',
        steps: [
            'Use Clock In/Out at the start and end of every shift. Missed punches create payroll corrections and should be avoided.',
            'My Timesheet shows your recorded hours, late marks, and any attendance adjustments already applied.',
            'If you forgot to clock in or out, notify your manager or HR so they can correct the record with a note rather than sharing someone else\'s login.',
            'Review your timesheet before the pay period closes to catch mistakes while they are still easy to correct.',
        ],
    },
]

// ─── DEFAULT ─────────────────────────────────────────────────────────────────
const defaultTutorial = [
    {
        title: '🏠 Getting Started',
        icon: '📊',
        steps: [
            'Welcome to the Hotel Management System (HMS). Use the left sidebar to navigate between sections available for your role.',
            'Click your Dashboard link at the top of the sidebar to return to your personalised overview at any time.',
            'The notification bell (top right) shows alerts relevant to your role — check it regularly throughout your shift.',
            'If you need to access a feature that is not visible, contact your administrator — your role may need additional permissions.',
            'For help at any time, click "❓ Help & Tutorial" in the bottom of the left sidebar to re-open this guide.',
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
