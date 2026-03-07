<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\GuestFolio;
use Carbon\Carbon;

class SimpleTestPaymentSeeder extends Seeder
{
    public function run()
    {
        // Create some test folios first
        $folios = [
            [
                'folio_number' => 'FOL-001',
                'folio_date' => Carbon::now()->subDays(10),
                'guest_id' => 1,
                'room_id' => 1,
                'reservation_id' => 1,
                'status' => 'closed',
                'total_amount' => 500.00,
                'paid_amount' => 500.00,
                'balance_amount' => 0.00,
                'closed_at' => Carbon::now()->subDays(5),
                'created_at' => Carbon::now()->subDays(10),
            ],
            [
                'folio_number' => 'FOL-002',
                'folio_date' => Carbon::now()->subDays(8),
                'guest_id' => 2,
                'room_id' => 2,
                'reservation_id' => 2,
                'status' => 'closed',
                'total_amount' => 750.00,
                'paid_amount' => 750.00,
                'balance_amount' => 0.00,
                'closed_at' => Carbon::now()->subDays(3),
                'created_at' => Carbon::now()->subDays(8),
            ],
            [
                'folio_number' => 'FOL-003',
                'folio_date' => Carbon::now()->subDays(5),
                'guest_id' => 3,
                'room_id' => 3,
                'reservation_id' => 3,
                'status' => 'closed',
                'total_amount' => 1200.00,
                'paid_amount' => 1200.00,
                'balance_amount' => 0.00,
                'closed_at' => Carbon::now()->subDays(1),
                'created_at' => Carbon::now()->subDays(5),
            ],
        ];

        foreach ($folios as $folioData) {
            GuestFolio::create($folioData);
        }

        // Create test payments
        $payments = [
            [
                'payment_number' => 'PAY-001',
                'transaction_id' => 'TXN-001',
                'guest_folio_id' => 1,
                'reservation_id' => 1,
                'amount' => 200.00,
                'local_amount' => 200.00,
                'payment_method' => 'credit_card',
                'status' => 'completed',
                'processed_at' => Carbon::now()->subDays(5),
                'created_at' => Carbon::now()->subDays(10),
                'notes' => 'Payment for John Smith - Room booking',
                'processed_by' => 1,
            ],
            [
                'payment_number' => 'PAY-002',
                'transaction_id' => 'TXN-002',
                'guest_folio_id' => 2,
                'reservation_id' => 2,
                'amount' => 350.00,
                'local_amount' => 350.00,
                'payment_method' => 'cash',
                'status' => 'completed',
                'processed_at' => Carbon::now()->subDays(3),
                'created_at' => Carbon::now()->subDays(8),
                'notes' => 'Cash payment for Sarah Johnson',
                'processed_by' => 1,
            ],
            [
                'payment_number' => 'PAY-003',
                'transaction_id' => 'TXN-003',
                'guest_folio_id' => 3,
                'reservation_id' => 3,
                'amount' => 600.00,
                'local_amount' => 600.00,
                'payment_method' => 'bank_transfer',
                'status' => 'completed',
                'processed_at' => Carbon::now()->subDays(1),
                'created_at' => Carbon::now()->subDays(5),
                'notes' => 'Bank transfer for Michael Brown',
                'processed_by' => 1,
            ],
            [
                'payment_number' => 'PAY-004',
                'transaction_id' => 'TXN-004',
                'guest_folio_id' => 1,
                'reservation_id' => 1,
                'amount' => 300.00,
                'local_amount' => 300.00,
                'payment_method' => 'debit_card',
                'status' => 'completed',
                'processed_at' => Carbon::now()->subDays(2),
                'created_at' => Carbon::now()->subDays(7),
                'notes' => 'Additional payment for John Smith',
                'processed_by' => 1,
            ],
            [
                'payment_number' => 'PAY-005',
                'transaction_id' => 'TXN-005',
                'guest_folio_id' => 2,
                'reservation_id' => 2,
                'amount' => 400.00,
                'local_amount' => 400.00,
                'payment_method' => 'credit_card',
                'status' => 'completed',
                'processed_at' => Carbon::now()->subDays(4),
                'created_at' => Carbon::now()->subDays(9),
                'notes' => 'Credit card payment for Sarah Johnson',
                'processed_by' => 1,
            ],
        ];

        foreach ($payments as $paymentData) {
            Payment::create($paymentData);
        }

        $this->command->info('Created ' . count($folios) . ' folios and ' . count($payments) . ' test payments');
    }
}
