# Manager Staff Schedules Implementation

## Summary
Successfully implemented real staff schedules functionality for the Manager role with actual database integration, matching the Admin role's functionality.

## What Was Implemented

### 1. Manager Schedule Controller
**File**: `app/Http/Controllers/Manager/ScheduleController.php`

A complete controller with the following methods:
- `index()` - Display weekly schedules with real data from database
- `store()` - Create new schedules (single and recurring)
- `update()` - Update existing schedules
- `destroy()` - Delete schedules
- `export()` - Export schedules to CSV
- `print()` - Print-friendly schedule view
- `generateSchedule()` - Auto-generate schedules (placeholder)
- `approveRequest()` - Approve leave requests
- `rejectRequest()` - Reject leave requests

### 2. Routes Configuration
**File**: `routes/web.php`

Added complete route configuration for manager schedules:
```php
Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules');
Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
Route::put('/schedules/{schedule}', [ScheduleController::class, 'update'])->name('schedules.update');
Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
Route::post('/schedules/generate', [ScheduleController::class, 'generateSchedule'])->name('schedules.generate');
Route::get('/schedules/export', [ScheduleController::class, 'export'])->name('schedules.export');
Route::get('/schedules/print', [ScheduleController::class, 'print'])->name('schedules.print');
Route::post('/schedules/requests/{request}/approve', [ScheduleController::class, 'approveRequest'])->name('schedules.requests.approve');
Route::post('/schedules/requests/{request}/reject', [ScheduleController::class, 'rejectRequest'])->name('schedules.requests.reject');
```

### 3. Vue Component Updates
**File**: `resources/js/Pages/Manager/Staff/Schedules.vue`

Updated all route references from `admin.schedules.*` to `manager.staff.schedules.*`:
- Print functionality
- Export functionality
- Week navigation
- Schedule CRUD operations
- Request approval/rejection

### 4. Print View
**File**: `resources/js/Pages/Manager/Schedules/Print.vue`

Created print-friendly schedule view for managers.

## Database Models Used

### EmployeeShift Model
- `user_id` - References User
- `work_shift_id` - References WorkShift
- `effective_date` - Date of the shift
- `days_of_week` - JSON array of recurring days
- `is_active` - Boolean status

### WorkShift Model
- `name` - Shift name
- `start_time` - Shift start time
- `end_time` - Shift end time
- `hours` - Total hours
- `is_overnight` - Boolean for night shifts
- `is_active` - Boolean status

### LeaveRequest Model
- Used for schedule change requests
- Tracks leave requests with approval workflow

## Features Implemented

### 1. Weekly Schedule View
- Interactive calendar-style grid
- Employee rows with daily shift assignments
- Color-coded shift types (regular, overtime, part-time, night)
- Hover actions (edit, delete)
- Click empty cells to add shifts

### 2. Schedule Statistics
- Total shifts this week
- Number of scheduled staff
- Conflict detection (placeholder)
- Total hours scheduled

### 3. CRUD Operations
- **Create**: Add single or recurring schedules
- **Read**: View weekly schedules with navigation
- **Update**: Edit existing shift assignments
- **Delete**: Remove schedules with confirmation

### 4. Schedule Requests
- View pending leave/schedule change requests
- Approve/reject requests
- Track request history

### 5. Export & Print
- Export schedules to CSV
- Print-friendly schedule view
- Auto-open print dialog

### 6. Week Navigation
- Navigate between weeks
- Preserves state during navigation
- Date range display

## How to Use

### Viewing Schedules
1. Navigate to `/manager/staff/schedules`
2. View the current week's schedule by default
3. Use arrow buttons to navigate between weeks

### Adding a Schedule
1. Click "Add Schedule" button
2. Select employee, shift, and date
3. Optionally enable recurring schedules
4. Submit to create

### Editing a Schedule
1. Click on an existing shift cell
2. Modify shift details
3. Save changes

### Deleting a Schedule
1. Hover over a shift cell
2. Click the trash icon
3. Confirm deletion

### Exporting Schedules
- Click "Export CSV" to download schedule data
- Click "Print" to open print view

### Managing Requests
- View pending requests in the bottom section
- Click "Approve" or "Reject" to process requests

## Testing Requirements

### Prerequisites
Ensure the following tables exist and have data:
1. `users` - Staff members
2. `work_shifts` - Shift definitions
3. `employee_shifts` - Schedule assignments
4. `leave_requests` - Leave/schedule requests (optional)

### Sample Data Creation
If you need to add sample shifts, you can use the admin panel or run:
```sql
-- Add a work shift
INSERT INTO work_shifts (name, start_time, end_time, hours, is_overnight, is_active) 
VALUES ('Morning Shift', '08:00:00', '16:00:00', 8, 0, 1);

-- Add an employee shift
INSERT INTO employee_shifts (user_id, work_shift_id, effective_date, days_of_week, is_active) 
VALUES (1, 1, CURDATE(), '[1,2,3,4,5]', 1);
```

### Test Scenarios
1. **View schedules**: Access the page and verify data loads
2. **Add schedule**: Create a new shift assignment
3. **Edit schedule**: Modify an existing shift
4. **Delete schedule**: Remove a shift
5. **Navigate weeks**: Move between different weeks
6. **Export CSV**: Download and verify CSV content
7. **Print view**: Open print view and verify layout
8. **Recurring schedules**: Create a recurring schedule
9. **Request approval**: Approve/reject a schedule request

## Route Names Reference

All manager schedule routes use the prefix `manager.staff.schedules`:

- `manager.staff.schedules` - Index/List view
- `manager.staff.schedules.store` - Create new schedule
- `manager.staff.schedules.update` - Update schedule
- `manager.staff.schedules.destroy` - Delete schedule
- `manager.staff.schedules.generate` - Auto-generate
- `manager.staff.schedules.export` - Export CSV
- `manager.staff.schedules.print` - Print view
- `manager.staff.schedules.requests.approve` - Approve request
- `manager.staff.schedules.requests.reject` - Reject request

## Notes

1. **Permissions**: Ensure managers have appropriate permissions to manage schedules
2. **Data Validation**: All inputs are validated server-side
3. **Conflict Detection**: Currently returns 0, can be enhanced with business logic
4. **Auto-generation**: Placeholder for future AI/rule-based schedule generation
5. **Relationships**: Requires proper User model relationships (already implemented)

## Next Steps (Optional Enhancements)

1. Implement conflict detection algorithm
2. Add schedule templates
3. Implement auto-generation with AI/rules
4. Add shift swap functionality
5. Add notifications for schedule changes
6. Implement mobile-responsive view
7. Add drag-and-drop schedule management
8. Add schedule history/audit trail

## Troubleshooting

### Issue: "Route not found"
- Clear route cache: `php artisan route:clear`
- Regenerate routes: `php artisan route:cache`

### Issue: "Call to undefined relationship"
- Ensure User model has `employeeShifts()` relationship
- Check EmployeeShift model has `user()` and `workShift()` relationships

### Issue: "No data showing"
- Verify database tables have data
- Check active scope on User model
- Verify work_shifts have `is_active = 1`

### Issue: "Permission denied"
- Check user has 'manager' role
- Verify middleware on routes
- Check authorization policies

## Support
If you encounter any issues, check:
1. Laravel logs: `storage/logs/laravel.log`
2. Browser console for JavaScript errors
3. Network tab for API request/response details
