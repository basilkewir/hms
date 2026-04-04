<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckLicense;
use App\Models\Guest;
use App\Models\GuestBillAdjustmentRequest;
use App\Models\GuestFolio;
use App\Models\Reservation;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Middleware\RoleMiddleware;
use Tests\TestCase;

class BillAdjustmentReportingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            CheckLicense::class,
            RoleMiddleware::class,
        ]);
    }

    public function test_approved_bill_adjustment_is_included_in_admin_and_accountant_reports(): void
    {
        $frontDeskUser = $this->createUserWithRole('front_desk', 'frontdesk-reporting@example.test');
        $adminUser = $this->createUserWithRole('admin', 'admin-reporting@example.test');
        $accountantUser = $this->createUserWithRole('accountant', 'accountant-reporting@example.test');
        [$reservation] = $this->createReservationContext($frontDeskUser);

        $request = GuestBillAdjustmentRequest::create([
            'reservation_id' => $reservation->id,
            'requested_by' => $frontDeskUser->id,
            'adjustment_type' => 'increase',
            'amount' => 40,
            'reason' => 'Approved minibar correction',
            'request_notes' => 'Validated by night audit.',
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $this->actingAs($adminUser)
            ->post(route('admin.reservations.bill-adjustment-requests.approve', [$reservation, $request]), [
                'review_notes' => 'Approved for ledger posting.',
            ])
            ->assertRedirect();

        $this->actingAs($adminUser)
            ->get(route('admin.reports.revenue'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Reports/Revenue')
                ->where('revenueData.bill_adjustment_revenue', 40)
                ->where('revenueData.formatted_bill_adjustment_revenue', '40.00')
                ->where('revenueData.total_revenue', 40)
            );

        $this->actingAs($accountantUser)
            ->get(route('accountant.reports.revenue'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Accountant/Reports/Revenue')
                ->where('revenueData.bill_adjustment_revenue', 40)
            );

        $this->actingAs($accountantUser)
            ->get(route('accountant.reports.profit-loss'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Accountant/Reports/ProfitLoss')
                ->where('profitLossData.revenue.bill_adjustments', 40)
            );
    }

    public function test_approved_bill_adjustment_is_shown_as_a_bill_adjustment_transaction(): void
    {
        $frontDeskUser = $this->createUserWithRole('front_desk', 'frontdesk-transaction@example.test');
        $adminUser = $this->createUserWithRole('admin', 'admin-transaction@example.test');
        [$reservation] = $this->createReservationContext($frontDeskUser);

        $request = GuestBillAdjustmentRequest::create([
            'reservation_id' => $reservation->id,
            'requested_by' => $frontDeskUser->id,
            'adjustment_type' => 'increase',
            'amount' => 55,
            'reason' => 'Late checkout billing update',
            'request_notes' => 'Apply after manager review.',
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $this->actingAs($adminUser)
            ->post(route('admin.reservations.bill-adjustment-requests.approve', [$reservation, $request]), [
                'review_notes' => 'Approved.',
            ])
            ->assertRedirect();

        $this->actingAs($adminUser)
            ->get(route('admin.transactions.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Transactions/Index')
                ->where('transactions.0.type', 'bill_adjustment')
                ->where('transactions.0.source_label', 'Bill Adjustment')
                ->where('transactions.0.amount', 55)
                ->where('transactions.0.reference', 'Reservation ' . $reservation->reservation_number)
            );
    }

    public function test_admin_bill_adjustment_management_page_lists_requests_and_stats(): void
    {
        $frontDeskUser = $this->createUserWithRole('front_desk', 'frontdesk-management@example.test');
        $adminUser = $this->createUserWithRole('admin', 'admin-management@example.test');
        [$reservation] = $this->createReservationContext($frontDeskUser);

        $request = GuestBillAdjustmentRequest::create([
            'reservation_id' => $reservation->id,
            'requested_by' => $frontDeskUser->id,
            'adjustment_type' => 'decrease',
            'amount' => 25,
            'reason' => 'Goodwill discount',
            'request_notes' => 'Guest service recovery.',
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $this->actingAs($adminUser)
            ->get(route('admin.bill-adjustment-requests.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Reservations/BillAdjustmentRequests/Index')
                ->where('routePrefix', 'admin')
                ->where('stats.total', 1)
                ->where('stats.pending', 1)
                ->where('requests.data.0.id', $request->id)
                ->where('requests.data.0.reservation_number', $reservation->reservation_number)
                ->where('requests.data.0.adjustment_type', 'decrease')
                ->where('requests.data.0.signed_amount', -25)
            );
    }

    private function createReservationContext(
        User $user,
        array $reservationOverrides = [],
        ?array $folioOverrides = []
    ): array {
        $guest = $this->createGuest($user);
        $roomType = $this->createRoomType();
        $room = $this->createRoom($roomType, ['status' => 'reserved']);

        $reservation = Reservation::create(array_merge([
            'reservation_number' => 'RES-' . strtoupper((string) str()->random(8)),
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'room_type_id' => $roomType->id,
            'check_in_date' => now()->subDay()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
            'nights' => 3,
            'adults' => 1,
            'children' => 0,
            'infants' => 0,
            'number_of_adults' => 1,
            'number_of_children' => 0,
            'status' => 'confirmed',
            'booking_source' => 'walk_in',
            'room_rate' => 100,
            'total_room_charges' => 300,
            'taxes' => 0,
            'service_charges' => 0,
            'discount_amount' => 0,
            'total_amount' => 300,
            'paid_amount' => 0,
            'balance_amount' => 300,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ], $reservationOverrides));

        $folio = null;

        if ($folioOverrides !== null) {
            $folio = GuestFolio::create(array_merge([
                'folio_number' => 'FOL-' . strtoupper((string) str()->random(8)),
                'reservation_id' => $reservation->id,
                'guest_id' => $guest->id,
                'room_id' => $room->id,
                'status' => 'open',
                'folio_date' => now()->toDateString(),
                'room_charges' => 300,
                'service_charges' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 300,
                'paid_amount' => 0,
                'balance_amount' => 300,
            ], $folioOverrides));
        }

        return [$reservation, $folio, $room, $guest, $roomType];
    }

    private function createGuest(User $user): Guest
    {
        return Guest::create([
            'guest_id' => 'GST-' . strtoupper((string) str()->random(8)),
            'first_name' => 'Report',
            'last_name' => 'Guest',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'nationality' => 'Cameroonian',
            'phone' => '+237600000001',
            'address' => '123 Reporting Street',
            'city' => 'Douala',
            'state' => 'Littoral',
            'country' => 'Cameroon',
            'emergency_contact_name' => 'Emergency Contact',
            'emergency_contact_phone' => '+237611111111',
            'emergency_contact_relationship' => 'Sibling',
            'id_type' => 'national_id',
            'id_number' => 'ID-' . strtoupper((string) str()->random(6)),
            'id_issuing_authority' => 'Gov',
            'id_issue_date' => '2020-01-01',
            'id_expiry_date' => '2030-01-01',
            'purpose_of_visit' => 'Business',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    private function createRoomType(): RoomType
    {
        return RoomType::create([
            'name' => 'Standard',
            'code' => 'STD-' . strtoupper((string) str()->random(4)),
            'description' => 'Standard room type for reporting tests.',
            'max_occupancy' => 2,
            'max_adults' => 2,
            'max_children' => 1,
            'base_price' => 100,
            'is_active' => true,
        ]);
    }

    private function createRoom(RoomType $roomType, array $overrides = []): Room
    {
        return Room::create(array_merge([
            'room_number' => (string) random_int(100, 999),
            'room_type_id' => $roomType->id,
            'status' => 'available',
            'housekeeping_status' => 'clean',
            'is_active' => true,
        ], $overrides));
    }

    private function createUserWithRole(string $roleName, string $email): User
    {
        $role = Role::firstOrCreate(
            ['name' => $roleName],
            [
                'display_name' => ucwords(str_replace('_', ' ', $roleName)),
                'description' => ucfirst(str_replace('_', ' ', $roleName)) . ' test role',
                'is_active' => true,
            ]
        );

        $user = User::create([
            'first_name' => ucfirst(str_replace('_', ' ', $roleName)),
            'last_name' => 'User',
            'email' => $email,
            'password' => bcrypt('password'),
            'country' => 'Cameroon',
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();
        $user->roles()->attach($role->id, ['assigned_by' => $user->id]);

        return $user->fresh('roles');
    }
}