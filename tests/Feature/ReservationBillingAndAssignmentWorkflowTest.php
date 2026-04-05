<?php

namespace Tests\Feature;

use App\Models\FolioCharge;
use App\Models\Guest;
use App\Models\GuestBillAdjustmentRequest;
use App\Models\GuestFolio;
use App\Models\Reservation;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Setting;
use App\Models\User;
use App\Http\Middleware\CheckLicense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Middleware\RoleMiddleware;
use Tests\TestCase;

class ReservationBillingAndAssignmentWorkflowTest extends TestCase
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

    public function test_front_desk_can_submit_bill_adjustment_request(): void
    {
        $frontDeskUser = $this->createUserWithRole('front_desk', 'frontdesk-requester@example.test');
        $adminUser = $this->createUserWithRole('admin', 'admin-request-notify@example.test');
        $managerUser = $this->createUserWithRole('manager', 'manager-request-notify@example.test');
        [$reservation] = $this->createReservationContext($frontDeskUser);

        $response = $this->actingAs($frontDeskUser)->post(
            route('front-desk.reservations.bill-adjustment-requests.store', $reservation),
            [
                'adjustment_type' => 'increase',
                'amount' => 45.50,
                'reason' => 'Mini bar correction',
                'request_notes' => 'Guest approved the correction at checkout.',
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('guest_bill_adjustment_requests', [
            'reservation_id' => $reservation->id,
            'requested_by' => $frontDeskUser->id,
            'adjustment_type' => 'increase',
            'amount' => 45.50,
            'reason' => 'Mini bar correction',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $adminUser->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $managerUser->id,
        ]);

        $adminNotification = $adminUser->notifications()->latest()->first();
        $managerNotification = $managerUser->notifications()->latest()->first();

        $this->assertSame('requested', $adminNotification->data['event']);
        $this->assertSame('Bill modification request pending', $adminNotification->data['title']);
        $this->assertSame(route('admin.reservations.show', $reservation), $adminNotification->data['action_url']);
        $this->assertSame('requested', $managerNotification->data['event']);
        $this->assertSame(route('manager.reservations.show', $reservation), $managerNotification->data['action_url']);

        $this->actingAs($adminUser)
            ->getJson(route('notifications.index'))
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('items.0.title', 'Bill modification request pending')
            ->assertJsonPath('items.0.action_url', route('admin.reservations.show', $reservation));
    }

    public function test_front_desk_reservation_creation_notifies_management_and_other_front_desk_users(): void
    {
        $frontDeskUser = $this->createUserWithRole('front_desk', 'frontdesk-create@example.test');
        $otherFrontDeskUser = $this->createUserWithRole('front_desk', 'frontdesk-other@example.test');
        $adminUser = $this->createUserWithRole('admin', 'admin-reservation-notify@example.test');
        $managerUser = $this->createUserWithRole('manager', 'manager-reservation-notify@example.test');
        $guest = $this->createGuest($frontDeskUser);
        $roomType = $this->createRoomType();
        $this->createRoom($roomType, [
            'status' => 'available',
            'housekeeping_status' => 'clean',
        ]);

        $response = $this->actingAs($frontDeskUser)->post(route('front-desk.reservations.store'), [
            'guest_id' => $guest->id,
            'room_type_id' => $roomType->id,
            'room_id' => null,
            'number_of_rooms' => 1,
            'check_in_date' => now()->addDays(3)->toDateString(),
            'check_out_date' => now()->addDays(5)->toDateString(),
            'number_of_adults' => 2,
            'number_of_children' => 0,
            'infants' => 0,
            'booking_source' => 'walk_in',
            'booking_reference' => 'FD-NEW-001',
            'room_rate' => 120,
            'discount_amount' => 0,
            'discount_reason' => null,
            'special_requests' => 'Quiet floor.',
            'room_preferences' => [],
            'early_check_in_requested' => false,
            'late_check_out_requested' => false,
            'preferred_check_in_time' => null,
            'preferred_check_out_time' => null,
            'breakfast_included' => false,
            'wifi_included' => true,
            'parking_required' => false,
            'airport_pickup' => false,
            'airport_drop' => false,
            'group_booking_id' => null,
            'is_group_booking' => false,
            'billing_type' => 'individual',
            'status' => 'pending',
            'send_confirmation_email' => false,
        ]);

        $response->assertRedirect();

        $reservation = Reservation::latest('id')->first();

        $this->assertNotNull($reservation);
        $this->assertSame('New reservation created', $adminUser->notifications()->latest()->first()?->data['title']);
        $this->assertSame(route('admin.reservations.show', $reservation), $adminUser->notifications()->latest()->first()?->data['action_url']);
        $this->assertSame(route('manager.reservations.show', $reservation), $managerUser->notifications()->latest()->first()?->data['action_url']);
        $this->assertSame(route('front-desk.reservations.show', $reservation), $otherFrontDeskUser->notifications()->latest()->first()?->data['action_url']);
        $this->assertCount(0, $frontDeskUser->notifications);
    }

    public function test_website_booking_notifies_operations_users(): void
    {
        $systemUser = $this->createUserWithRole('admin', 'system-booking@example.test');
        $managerUser = $this->createUserWithRole('manager', 'manager-booking@example.test');
        $frontDeskUser = $this->createUserWithRole('front_desk', 'frontdesk-booking@example.test');
        $roomType = $this->createRoomType();
        $this->createRoom($roomType, [
            'status' => 'available',
            'housekeeping_status' => 'clean',
        ]);

        Setting::set('integration.booking_api_token', 'booking-test-token');

        $response = $this->withHeaders([
            'X-Booking-Token' => 'booking-test-token',
        ])->postJson('/api/public/bookings', [
            'guest' => [
                'first_name' => 'Website',
                'last_name' => 'Guest',
                'email' => 'website-guest@example.test',
                'phone' => '+237699999999',
            ],
            'room_type_id' => $roomType->id,
            'check_in_date' => now()->addDays(7)->toDateString(),
            'check_out_date' => now()->addDays(9)->toDateString(),
            'adults' => 2,
            'children' => 0,
            'infants' => 0,
            'booking_reference' => 'WEB-001',
            'special_requests' => 'High floor if possible.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.room_assigned', false);

        $reservation = Reservation::latest('id')->first();

        $this->assertNotNull($reservation);
        $this->assertSame('website', $reservation->booking_source);
        $this->assertSame(route('admin.reservations.show', $reservation), $systemUser->notifications()->latest()->first()?->data['action_url']);
        $this->assertSame(route('manager.reservations.show', $reservation), $managerUser->notifications()->latest()->first()?->data['action_url']);
        $this->assertSame(route('front-desk.reservations.show', $reservation), $frontDeskUser->notifications()->latest()->first()?->data['action_url']);
    }

    public function test_admin_can_approve_bill_adjustment_and_apply_it_to_folio_and_reservation_totals(): void
    {
        $frontDeskUser = $this->createUserWithRole('front_desk', 'frontdesk-approval@example.test');
        $adminUser = $this->createUserWithRole('admin', 'admin-approval@example.test');
        [$reservation, $folio] = $this->createReservationContext($frontDeskUser, [
            'total_amount' => 300,
            'balance_amount' => 300,
        ], [
            'room_charges' => 300,
            'total_amount' => 300,
            'balance_amount' => 300,
        ]);

        $request = GuestBillAdjustmentRequest::create([
            'reservation_id' => $reservation->id,
            'requested_by' => $frontDeskUser->id,
            'adjustment_type' => 'increase',
            'amount' => 40,
            'reason' => 'Late checkout fee',
            'request_notes' => 'Guest departed after grace period.',
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $response = $this->actingAs($adminUser)->post(
            route('admin.reservations.bill-adjustment-requests.approve', [$reservation, $request]),
            ['review_notes' => 'Validated against front desk notes.']
        );

        $response->assertRedirect();

        $request->refresh();
        $folio->refresh();
        $reservation->refresh();

        $this->assertSame('approved', $request->status);
        $this->assertSame($adminUser->id, $request->reviewed_by);
        $this->assertSame($folio->id, $request->guest_folio_id);
        $this->assertNotNull($request->folio_charge_id);

        $charge = FolioCharge::find($request->folio_charge_id);

        $this->assertNotNull($charge);
        $this->assertSame('ADJUSTMENT', $charge->charge_code);
        $this->assertSame('bill_adjustment_request', $charge->reference_type);
        $this->assertSame($request->id, $charge->reference_id);
        $this->assertEquals(40.0, (float) $charge->net_amount);
        $this->assertEquals(340.0, (float) $folio->total_amount);
        $this->assertEquals(340.0, (float) $folio->balance_amount);
        $this->assertEquals(340.0, (float) $reservation->total_amount);
        $this->assertEquals(340.0, (float) $reservation->balance_amount);

        $requesterNotification = $frontDeskUser->notifications()->latest()->first();

        $this->assertNotNull($requesterNotification);
        $this->assertSame('approved', $requesterNotification->data['event']);
        $this->assertSame('Bill modification approved', $requesterNotification->data['title']);
        $this->assertSame(route('front-desk.reservations.show', $reservation), $requesterNotification->data['action_url']);
    }

    public function test_admin_can_approve_bill_adjustment_for_checked_out_paid_reservation(): void
    {
        $frontDeskUser = $this->createUserWithRole('front_desk', 'frontdesk-poststay@example.test');
        $adminUser = $this->createUserWithRole('admin', 'admin-poststay@example.test');
        [$reservation, $folio] = $this->createReservationContext($frontDeskUser, [
            'status' => 'checked_out',
            'paid_amount' => 300,
            'balance_amount' => 0,
            'actual_check_out' => now()->subDay(),
            'checked_out_by' => $adminUser->id,
        ], [
            'status' => 'closed',
            'paid_amount' => 300,
            'total_amount' => 300,
            'balance_amount' => 0,
            'closed_at' => now()->subDay(),
            'closed_by' => $adminUser->id,
        ]);

        $request = GuestBillAdjustmentRequest::create([
            'reservation_id' => $reservation->id,
            'requested_by' => $frontDeskUser->id,
            'adjustment_type' => 'increase',
            'amount' => 50,
            'reason' => 'Post checkout minibar charge',
            'request_notes' => 'Found after room inspection.',
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $response = $this->actingAs($adminUser)->post(
            route('admin.reservations.bill-adjustment-requests.approve', [$reservation, $request]),
            ['review_notes' => 'Validated after checkout audit.']
        );

        $response->assertRedirect();

        $request->refresh();
        $folio->refresh();
        $reservation->refresh();

        $this->assertSame('approved', $request->status);
        $this->assertSame($folio->id, $request->guest_folio_id);
        $this->assertSame('open', $folio->status);
        $this->assertNull($folio->closed_at);
        $this->assertNull($folio->closed_by);
        $this->assertEquals(350.0, (float) $folio->total_amount);
        $this->assertEquals(50.0, (float) $folio->balance_amount);
        $this->assertEquals(350.0, (float) $reservation->total_amount);
        $this->assertEquals(300.0, (float) $reservation->paid_amount);
        $this->assertEquals(50.0, (float) $reservation->balance_amount);
    }

    public function test_manager_can_reject_bill_adjustment_without_creating_a_charge(): void
    {
        $frontDeskUser = $this->createUserWithRole('front_desk', 'frontdesk-reject@example.test');
        $managerUser = $this->createUserWithRole('manager', 'manager-review@example.test');
        [$reservation] = $this->createReservationContext($frontDeskUser);

        $request = GuestBillAdjustmentRequest::create([
            'reservation_id' => $reservation->id,
            'requested_by' => $frontDeskUser->id,
            'adjustment_type' => 'decrease',
            'amount' => 25,
            'reason' => 'Promo correction',
            'request_notes' => 'Guest was promised an adjustment.',
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $response = $this->actingAs($managerUser)->post(
            route('manager.reservations.bill-adjustment-requests.reject', [$reservation, $request]),
            ['review_notes' => 'No supporting authorization was provided.']
        );

        $response->assertRedirect();

        $request->refresh();

        $this->assertSame('rejected', $request->status);
        $this->assertSame($managerUser->id, $request->reviewed_by);
        $this->assertSame('No supporting authorization was provided.', $request->review_notes);
        $this->assertNull($request->folio_charge_id);
        $this->assertDatabaseCount('folio_charges', 0);

        $requesterNotification = $frontDeskUser->notifications()->latest()->first();

        $this->assertNotNull($requesterNotification);
        $this->assertSame('rejected', $requesterNotification->data['event']);
        $this->assertSame('Bill modification rejected', $requesterNotification->data['title']);
        $this->assertSame(route('front-desk.reservations.show', $reservation), $requesterNotification->data['action_url']);
    }

    public function test_front_desk_room_assignment_lists_pending_website_reservations_and_marks_rooms_reserved(): void
    {
        $frontDeskUser = $this->createUserWithRole('front_desk', 'frontdesk-room@example.test');
        [$reservation, , $room] = $this->createReservationContext($frontDeskUser, [
            'room_id' => null,
            'status' => 'pending',
            'booking_source' => 'website',
        ], null, true);

        $pageResponse = $this->actingAs($frontDeskUser)
            ->get(route('front-desk.room-assignment'));

        $pageResponse->assertOk();
        $this->assertStringContainsString($reservation->reservation_number, $pageResponse->getContent());

        $assignResponse = $this->actingAs($frontDeskUser)->post(
            route('front-desk.room-assignment.assign'),
            [
                'reservation_id' => $reservation->id,
                'room_id' => $room->id,
            ]
        );

        $assignResponse->assertRedirect(route('front-desk.room-assignment'));

        $reservation->refresh();
        $room->refresh();

        $this->assertSame($room->id, $reservation->room_id);
        $this->assertSame('reserved', $room->status);
    }

    public function test_reservation_show_pages_expose_bill_adjustment_capabilities_for_front_desk_and_admin(): void
    {
        $frontDeskUser = $this->createUserWithRole('front_desk', 'frontdesk-show@example.test');
        $adminUser = $this->createUserWithRole('admin', 'admin-show@example.test');
        $managerUser = $this->createUserWithRole('manager', 'manager-show@example.test');
        [$reservation] = $this->createReservationContext($frontDeskUser, [
            'room_id' => null,
            'status' => 'pending',
        ], null, true);

        $this->actingAs($frontDeskUser)->post(
            route('front-desk.reservations.bill-adjustment-requests.store', $reservation),
            [
                'adjustment_type' => 'increase',
                'amount' => 20,
                'reason' => 'Mini bar note',
                'request_notes' => 'Room note after review.',
            ]
        )->assertRedirect();

        $this->actingAs($frontDeskUser)
            ->get(route('front-desk.reservations.show', $reservation))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('FrontDesk/Reservations/Show')
                ->where('notifications.unread_count', 0)
                ->where('reservation.can_request_bill_adjustment', true)
                ->where('reservation.can_validate_bill_adjustment', false)
                ->where('reservation.room', null)
            );

        $this->actingAs($adminUser)
            ->get(route('admin.reservations.show', $reservation))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Reservations/Show')
                ->where('notifications.unread_count', 1)
                ->where('notifications.items.0.title', 'Bill modification request pending')
                ->where('notifications.items.0.action_url', route('admin.reservations.show', $reservation))
                ->where('reservation.can_request_bill_adjustment', false)
                ->where('reservation.can_validate_bill_adjustment', true)
            );

        $this->actingAs($managerUser)
            ->get(route('manager.reservations.show', $reservation))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Manager/Reservations/Show')
                ->where('notifications.unread_count', 1)
                ->where('notifications.items.0.title', 'Bill modification request pending')
                ->where('notifications.items.0.action_url', route('manager.reservations.show', $reservation))
                ->where('reservation.can_request_bill_adjustment', false)
                ->where('reservation.can_validate_bill_adjustment', true)
            );
    }

    private function createReservationContext(
        User $user,
        array $reservationOverrides = [],
        ?array $folioOverrides = [],
        bool $keepRoomAvailable = false
    ): array {
        $guest = $this->createGuest($user);
        $roomType = $this->createRoomType();
        $room = $this->createRoom($roomType, [
            'status' => $keepRoomAvailable ? 'available' : 'reserved',
        ]);

        $reservation = Reservation::create(array_merge([
            'reservation_number' => 'RES-' . strtoupper((string) str()->random(8)),
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'room_type_id' => $roomType->id,
            'check_in_date' => now()->addDays(2)->toDateString(),
            'check_out_date' => now()->addDays(5)->toDateString(),
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
                'room_id' => $reservation->room_id ?? $room->id,
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
            'first_name' => 'Test',
            'last_name' => 'Guest',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'nationality' => 'Cameroonian',
            'phone' => '+237600000000',
            'address' => '123 Main Street',
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
            'description' => 'Standard room type for workflow tests.',
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