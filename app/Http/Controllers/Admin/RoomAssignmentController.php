<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomAssignmentController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        // Get pending reservations with more detailed data
        $pendingReservations = Reservation::with(['guest', 'roomType'])
            ->where('status', 'confirmed')
            ->whereNull('room_id')
            ->orderBy('check_in_date', 'asc')
            ->get()
            ->map(function ($reservation) {
                // Calculate nights if not already set
                if (!$reservation->nights) {
                    $checkIn = Carbon::parse($reservation->check_in_date);
                    $checkOut = Carbon::parse($reservation->check_out_date);
                    $reservation->nights = $checkIn->diffInDays($checkOut);
                }
                return $reservation;
            });
        
        // Get available rooms with more detailed data
        $availableRooms = Room::with(['roomType', 'floorRelation', 'buildingWing'])
            ->where('status', 'available')
            ->where('housekeeping_status', 'clean') // Only show rooms that are clean and ready
            ->orderBy('room_number', 'asc')
            ->get()
            ->map(function ($room) {
                // Add floor name - prioritize relationship over string field
                if ($room->floorRelation) {
                    $room->floor_name = $room->floorRelation->name ?? "Floor {$room->floorRelation->floor_number}";
                    $room->floor_number = $room->floorRelation->floor_number;
                } else {
                    // Fallback to string field if relationship doesn't exist
                    $room->floor_name = $room->floor ?? 'Unknown';
                    $room->floor_number = null;
                }
                return $room;
            });
        
        // Get statistics for the dashboard
        $stats = [
            'pending' => $pendingReservations->count(),
            'available' => $availableRooms->count(),
            'totalRooms' => Room::where('is_active', true)->count(),
            'todayCheckins' => $pendingReservations->filter(function ($reservation) use ($today) {
                return Carbon::parse($reservation->check_in_date)->isSameDay($today);
            })->count(),
            'occupancyRate' => $this->calculateOccupancyRate(),
            'cleanRooms' => Room::where('housekeeping_status', 'clean')->count(),
            'dirtyRooms' => Room::where('housekeeping_status', 'dirty')->count(),
        ];
        
        return Inertia::render('Admin/RoomAssignment', [
            'user' => Auth::user(),
            'pendingReservations' => $pendingReservations,
            'availableRooms' => $availableRooms,
            'stats' => $stats,
        ]);
    }
    
    public function assign(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'room_id' => 'required|exists:rooms,id',
        ]);
        
        $reservation = Reservation::findOrFail($validated['reservation_id']);
        $room = Room::findOrFail($validated['room_id']);
        
        // Check if room is actually available and clean
        if ($room->status !== 'available' || $room->housekeeping_status !== 'clean') {
            return redirect()->back()->with('error', 'Selected room is not available for assignment.');
        }
        
        // Check if reservation is confirmed and doesn't have a room
        if ($reservation->status !== 'confirmed' || $reservation->room_id) {
            return redirect()->back()->with('error', 'Reservation cannot be assigned a room.');
        }
        
        // Update room status to occupied
        $room->update([
            'status' => 'occupied',
            'housekeeping_status' => 'clean', // Keep it clean until guest checks in
        ]);
        
        // Assign room to reservation
        $reservation->room_id = $room->id;
        $reservation->save();
        
        return redirect()->back()->with('success', 'Room assigned successfully');
    }
    
    private function calculateOccupancyRate()
    {
        $totalRooms = Room::where('is_active', true)->count();
        $occupiedRooms = Room::where('status', 'occupied')->count();
        
        return $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;
    }
}
