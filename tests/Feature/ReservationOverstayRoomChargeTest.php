<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ReservationController;
use App\Models\FolioCharge;
use App\Models\Guest;
use App\Models\GuestFolio;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use ReflectionMethod;
use Tests\TestCase;

class ReservationOverstayRoomChargeTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_open_folio_creates_missing_room_charge_for_extended_stay(): void
    {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'Agent',
            'email' => 'agent@example.com',
            'password' => bcrypt('password'),
            'country' => 'USA',
        ]);

        $guest = Guest::create([
            'guest_id' => 'GST-1001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'nationality' => 'Cameroonian',
            'phone' => '+237600000000',
            'address' => 'Main Street',
            'city' => 'Douala',
            'state' => 'Littoral',
            'country' => 'Cameroon',
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_phone' => '+237611111111',
            'emergency_contact_relationship' => 'Spouse',
            'id_type' => 'national_id',
            'id_number' => 'ID-12345',
            'id_issuing_authority' => 'Gov',
            'id_issue_date' => '2020-01-01',
            'id_expiry_date' => '2030-01-01',
            'purpose_of_visit' => 'Business',
            'created_by' => $user->id,
        ]);

        $roomType = RoomType::create([
            'name' => 'Standard',
            'code' => 'STD',
            'max_occupancy' => 2,
            'max_adults' => 2,
            'max_children' => 1,
            'base_price' => 100,
            'is_active' => true,
        ]);

        $room = Room::create([
            'room_number' => '101',
            'room_type_id' => $roomType->id,
            'status' => 'occupied',
        ]);

        $reservation = Reservation::create([
            'reservation_number' => 'RES-1001',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'room_type_id' => $roomType->id,
            'check_in_date' => Carbon::parse('2026-03-20'),
            'check_out_date' => Carbon::parse('2026-03-23'),
            'nights' => 3,
            'adults' => 1,
            'children' => 0,
            'infants' => 0,
            'number_of_adults' => 1,
            'number_of_children' => 0,
            'status' => 'checked_in',
            'booking_source' => 'walk_in',
            'room_rate' => 100,
            'total_room_charges' => 300,
            'taxes' => 30,
            'service_charges' => 15,
            'discount_amount' => 0,
            'total_amount' => 345,
            'paid_amount' => 50,
            'balance_amount' => 295,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $folio = GuestFolio::create([
            'folio_number' => 'FOL-1001',
            'reservation_id' => $reservation->id,
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'status' => 'open',
            'folio_date' => Carbon::parse('2026-03-20')->toDateString(),
            'room_charges' => 200,
            'service_charges' => 0,
            'tax_amount' => 20,
            'discount_amount' => 0,
            'total_amount' => 220,
            'paid_amount' => 50,
            'balance_amount' => 170,
        ]);

        FolioCharge::create([
            'guest_folio_id' => $folio->id,
            'charge_code' => 'SERVICE',
            'description' => 'Laundry',
            'charge_date' => Carbon::parse('2026-03-22')->toDateString(),
            'charge_time' => '10:00:00',
            'quantity' => 1,
            'unit_price' => 25,
            'total_amount' => 25,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'discount_rate' => 0,
            'discount_amount' => 0,
            'net_amount' => 25,
            'reference_type' => 'manual',
            'department' => 'Front Desk',
            'posted_by' => $user->id,
            'posted_at' => now(),
        ]);

        $method = new ReflectionMethod(ReservationController::class, 'syncOpenFolioFromReservation');
        $method->setAccessible(true);
        $method->invoke(new ReservationController(), $reservation->fresh(), $folio->fresh());

        $roomCharge = FolioCharge::where('guest_folio_id', $folio->id)
            ->where('charge_code', 'ROOM')
            ->first();

        $this->assertNotNull($roomCharge);
        $this->assertSame('Room charges - 3 night(s)', $roomCharge->description);
        $this->assertSame('3.00', $roomCharge->quantity);
        $this->assertSame('100.00', $roomCharge->unit_price);
        $this->assertSame('300.00', $roomCharge->total_amount);
        $this->assertSame('30.00', $roomCharge->tax_amount);
        $this->assertSame('345.00', $roomCharge->net_amount);

        $folio->refresh();

        $this->assertSame('300.00', $folio->room_charges);
        $this->assertSame('15.00', $folio->service_charges);
        $this->assertSame('370.00', $folio->total_amount);
        $this->assertSame('320.00', $folio->balance_amount);
    }
}