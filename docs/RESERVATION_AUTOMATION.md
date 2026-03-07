# Reservation Automation System

## Overview

The Hotel Management System now includes a comprehensive reservation automation system that automatically manages reservation statuses based on time and business rules. This reduces manual work and ensures consistent handling of reservation lifecycle events.

## Features

### 🤖 Automated Status Updates

1. **No-Show Detection**
   - Automatically marks confirmed reservations as "no-show" if guests don't check in by the configured time
   - Default: 2 hours after check-in time or preferred check-in time
   - Releases room back to available status
   - Applies no-show charges according to hotel policy

2. **Auto Check-Out**
   - Automatically checks out guests who stayed past their check-out date
   - Default: Runs at 11:30 AM daily
   - Updates room status to "needs_cleaning"
   - Creates housekeeping tasks for room preparation

3. **Pending Reservation Cancellation**
   - Cancels unconfirmed pending reservations after timeout
   - Default: 24 hours after creation
   - Releases assigned rooms
   - Sends cancellation notifications

4. **Modified Reservation Confirmation**
   - Automatically confirms modified reservations after review period
   - Default: 2 hours after modification
   - Ensures reservations don't stay in "modified" status indefinitely

### 📊 Daily Reporting

- **Daily Summary Reports**: Automated email summaries sent to management
- **Automation Metrics**: Track how many reservations were processed automatically
- **Revenue Tracking**: Daily revenue summaries
- **Occupancy Reports**: Current occupancy statistics

### 📧 Notification System

- **Guest Notifications**: Email alerts for status changes
- **Staff Notifications**: Alerts to front desk and management
- **Daily Summaries**: Comprehensive daily reports
- **Configurable**: Enable/disable notifications as needed

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
# Hotel Automation Settings
HOTEL_NO_SHOW_HOURS=2
HOTEL_AUTO_CHECKOUT_HOUR=11
HOTEL_PENDING_CONFIRMATION_HOURS=24
HOTEL_ENABLE_NOTIFICATIONS=true
HOTEL_ENABLE_ROOM_UPDATES=true
HOTEL_ENABLE_HOUSEKEEPING_TASKS=true

# Hotel Check-in/Check-out Settings
HOTEL_CHECK_IN_TIME=14:00
HOTEL_EARLIEST_CHECK_IN=12:00
HOTEL_LATEST_CHECK_IN=22:00
HOTEL_CHECK_OUT_TIME=11:00
HOTEL_LATEST_CHECK_OUT=12:00

# Hotel Policies
HOTEL_NO_SHOW_CHARGE=first_night
HOTEL_NO_SHOW_CHARGE_PERCENTAGE=100
HOTEL_NO_SHOW_CHARGE_FIXED=0
HOTEL_CANCELLATION_POLICY=flexible
HOTEL_CANCELLATION_DEADLINE_HOURS=24

# Hotel Notification Settings
HOTEL_EMAIL_NOTIFICATIONS=true
HOTEL_FROM_EMAIL=reservations@hotel.com
HOTEL_FROM_NAME="Hotel Management"
HOTEL_SMS_NOTIFICATIONS=false
HOTEL_SMS_PROVIDER=twilio
HOTEL_IN_APP_NOTIFICATIONS=true

# Hotel Room Management
HOTEL_AUTO_RELEASE_NO_SHOW=true
HOTEL_AUTO_SET_NEEDS_CLEANING=true
HOTEL_ROOM_BUFFER_TIME=30

# Hotel Reporting Settings
HOTEL_DAILY_SUMMARY_TIME=23:59
HOTEL_WEEKLY_SUMMARY_DAY=sunday
HOTEL_MONTHLY_SUMMARY_DAY=1
```

### Configuration File

The `config/hotel.php` file contains all automation settings with detailed comments.

## Installation & Setup

### 1. Run Migrations

```bash
php artisan migrate
```

### 2. Update Environment

Copy the hotel automation settings from `.env.example` to your `.env` file and adjust as needed.

### 3. Set Up Scheduler

Add the Laravel scheduler to your cron job:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Test the System

```bash
# Test automation manually
php artisan reservations:automate-statuses

# Test with dry run (no actual changes)
php artisan reservations:automate-statuses --dry-run

# Send test daily summary
php artisan reservations:daily-summary --email=your@email.com
```

## Automation Rules

### No-Show Detection Logic

```php
// Conditions for no-show:
1. Status is 'confirmed'
2. Check-in date is today
3. Actual check-in is NULL
4. Current time > (preferred check-in time + no_show_hours)
   OR no preferred check-in time set
```

### Auto Check-Out Logic

```php
// Conditions for auto check-out:
1. Status is 'checked_in'
2. Check-out date < today
   OR (check-out date = today AND actual check-out is NULL)
3. Current time >= auto_checkout_hour
```

### Pending Cancellation Logic

```php
// Conditions for pending cancellation:
1. Status is 'pending'
2. Created_at < (now - pending_confirmation_hours)
```

### Modified Confirmation Logic

```php
// Conditions for modified confirmation:
1. Status is 'modified'
2. Updated_at < (now - 2 hours)
```

## Email Templates

### Reservation Status Changed

- **Template**: `resources/views/emails/reservation-status-changed.blade.php`
- **Purpose**: Notify guests of automated status changes
- **Triggers**: No-show, auto check-out, cancellation, confirmation

### Daily Reservation Summary

- **Template**: `resources/views/emails/daily-reservation-summary.blade.php`
- **Purpose**: Daily management report
- **Recipients**: Admin and manager users
- **Schedule**: Daily at 23:55

## Logging & Monitoring

### Reservation Logs

All automated status changes are logged in the `reservation_logs` table:

```php
// Log entry structure:
[
    'reservation_id' => 123,
    'old_status' => 'confirmed',
    'new_status' => 'no_show',
    'reason' => 'Automated no-show - guest did not check in by required time',
    'automated' => true,
    'changed_by' => 1, // System user
    'metadata' => [...],
    'created_at' => '2026-02-06 14:30:00',
]
```

### System Logs

Automation events are logged to Laravel's log files:

```bash
# View automation logs
tail -f storage/logs/laravel.log | grep "reservation"
```

## Monitoring & Alerts

### Daily Summary Includes

- Today's arrivals and departures
- No-shows and cancellations
- Current occupancy
- Automation metrics
- Revenue summary
- Issues requiring attention

### Critical Alerts

System highlights issues that need manual attention:
- High no-show rates
- Unusual cancellation patterns
- Automation failures
- Revenue discrepancies

## Customization

### Adding New Automation Rules

1. Create a new method in `AutomateReservationStatuses.php`
2. Add configuration to `config/hotel.php`
3. Update the `handle()` method to call your new rule
4. Add logging and notifications as needed

### Custom Notification Channels

Extend the notification system to support:
- SMS notifications
- Push notifications
- Slack/Teams integrations
- Custom webhooks

### Business Rules

Customize the business logic in each automation method:
- Different charge calculation methods
- Custom grace periods
- Role-based automation rules
- Location-specific policies

## Troubleshooting

### Common Issues

1. **Scheduler Not Running**
   - Verify cron job is set up correctly
   - Check `php artisan schedule:run` works manually
   - Review Laravel logs for scheduler errors

2. **Notifications Not Sending**
   - Verify mail configuration in `.env`
   - Check mail logs
   - Test with `php artisan tinker` mail sending

3. **Incorrect Status Updates**
   - Review automation configuration
   - Check reservation data integrity
   - Verify timezone settings

4. **Performance Issues**
   - Optimize database queries
   - Add database indexes
   - Consider queueing for large datasets

### Debug Mode

Run with detailed logging:

```bash
php artisan reservations:automate-statuses --verbose
```

### Dry Run Mode

Test without making changes:

```bash
php artisan reservations:automate-statuses --dry-run
```

## Security Considerations

- All automated actions are logged
- System user (ID: 1) performs automated changes
- Sensitive operations require manual override
- Audit trail maintained in reservation_logs

## Performance Optimization

- Database indexes on status and date fields
- Efficient queries with proper limits
- Queue system for email notifications
- Scheduled tasks during low-traffic periods

## Future Enhancements

### Planned Features

1. **Machine Learning**: Predict no-show probability
2. **Dynamic Pricing**: Adjust rates based on automation patterns
3. **Mobile App**: Real-time staff notifications
4. **API Integration**: Channel manager automation
5. **Advanced Analytics**: Trend analysis and reporting

### Integration Opportunities

- Property Management Systems
- Channel managers (Booking.com, Expedia)
- Revenue management systems
- Guest communication platforms
- Housekeeping management systems

## Support

For questions or issues with the reservation automation system:

1. Check this documentation
2. Review Laravel logs
3. Test with dry-run mode
4. Contact development team

---

**Last Updated**: February 6, 2026
**Version**: 1.0.0
**Compatibility**: Laravel 10.x
