# HMS Payment Integration Guide

## Overview

Your payment system now automatically records PayPal payments to the HMS backend. Here's how it works:

## Payment Flow

```
1. Guest initiates booking + payment
2. Frontend creates PayPal order via POST /payment/create-order
3. Payment record created locally with status='pending'
4. Guest approves payment on PayPal
5. PaymentController.approve() captures the transaction
6. Payment status updated to 'completed'
7. syncPaymentToHms() called automatically
8. Payment details sent to HMS backend
9. HMS sync status tracked in payments.hms_sync_status
```

## Database Fields

### New Columns in `payments` Table

| Column | Type | Purpose |
|--------|------|---------|
| `hms_sync_status` | enum (pending\|synced\|failed) | Tracks HMS sync state |
| `hms_sync_response` | json | HMS response data |
| `hms_sync_error` | string | Error message if sync failed |

## Payment Data Sent to HMS

After PayPal capture, the following data is sent to HMS:

```json
{
  "booking_reference": "string",
  "external_reference": "string",
  "payment": {
    "amount": 100000,
    "currency": "XAF",
    "payment_method": "paypal",
    "payment_type": "full|deposit",
    "transaction_id": "paypal_transaction_123",
    "paypal_order_id": "paypal_order_123",
    "payer_email": "guest@example.com",
    "payer_name": "Guest Name",
    "status": "completed",
    "processed_at": "2024-03-21T10:30:00Z"
  }
}
```

## HMS Endpoint Integration

The system attempts to sync payment data to HMS using these endpoints (in order):

1. **POST** `/booking/payment-confirm` (preferred)
   - Used if HMS supports dedicated payment confirmation endpoint
   
2. **PATCH** `/booking/{booking_reference}/payment` (fallback)
   - Updates an existing booking with payment details

## Configuration

### Required Environment Variables

```env
HMS_BASE_URL=https://donzebemanagement.qzz.io/api
HMS_BOOKING_TOKEN=your_booking_token_here  # Optional
```

### PayPal Configuration

```env
PAYPAL_CLIENT_ID=your_client_id
PAYPAL_SECRET=your_secret
PAYPAL_MODE=sandbox  # or 'live'
```

## Monitoring Payment Sync

### Check Payment Sync Status

Query the `payments` table:

```sql
-- All pending syncs
SELECT * FROM payments WHERE hms_sync_status = 'pending';

-- Failed syncs (requires retry)
SELECT * FROM payments WHERE hms_sync_status = 'failed';

-- View sync errors
SELECT booking_reference, hms_sync_error FROM payments WHERE hms_sync_error IS NOT NULL;
```

### View Logs

```bash
# Check payment sync logs
tail -f storage/logs/laravel.log | grep "Payment HMS sync"

# Find all payment-related events
grep -i "payment" storage/logs/laravel.log
```

## Troubleshooting

### Issue: HMS Sync Status = 'failed'

1. **Check HMS endpoint availability**
   ```bash
   curl -X POST https://donzebemanagement.qzz.io/api/booking/payment-confirm \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -d '{"test": true}'
   ```

2. **Verify booking_reference exists at HMS**
   ```bash
   curl https://donzebemanagement.qzz.io/api/booking/YOUR_BOOKING_REF
   ```

3. **Review hms_sync_error column**
   - Contains detailed error message
   - Check logs for more context

### Issue: Payment recorded but HMS not updated

1. Check `hms_sync_status` in payments table
2. If 'pending' → sync hasn't run yet (check cron jobs)
3. If 'failed' → check hms_sync_error and logs

## Manual Payment Sync

If a payment failed to sync to HMS, you can resync manually:

```bash
php artisan tinker
> $payment = Payment::find(123);
> app(PaymentController::class)->syncPaymentToHms($payment);
```

## Admin Panel

### Payment Settings (Admin > Settings > Payment)

- `payment_enabled`: Enable/disable payment processing
- `payment_display_text`: Custom text shown to guests
- `payment_method`: 'full' (charge full amount) or 'deposit' (charge %)
- `payment_deposit_percent`: Percentage of total for deposit
- `paypal_client_id`: PayPal sandbox/live client ID
- `paypal_mode`: 'sandbox' or 'live'

## API Endpoints

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/payment/create-order` | Create PayPal order |
| GET | `/payment/client-id` | Get PayPal client ID |
| GET | `/payment/approve?token=` | Approve & capture order |
| GET | `/payment/cancel?token=` | Cancel payment |
| GET | `/payment/success` | Payment success response |
| GET | `/payment/fail` | Payment failure response |

## Data Retention

- All payment records stored indefinitely
- HMS sync responses stored in `hms_sync_response` JSON column
- Audit trail available through created_at/updated_at timestamps
