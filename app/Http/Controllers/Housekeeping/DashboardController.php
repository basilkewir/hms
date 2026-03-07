<?php

namespace App\Http\Controllers\Housekeeping;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the housekeeping dashboard.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get room statistics
        $totalRooms = \App\Models\Room::count();
        $dirtyRooms = \App\Models\Room::where('status', 'dirty')->count();
        $cleanRooms = \App\Models\Room::where('status', 'clean')->count();
        $inProgressRooms = \App\Models\Room::where('status', 'in_progress')->count();
        
        // Get today's tasks
        $todayTasks = \App\Models\HousekeepingTask::whereDate('created_at', now())
            ->where('assigned_to', $user->id)
            ->count();
            
        $completedTasks = \App\Models\HousekeepingTask::whereDate('created_at', now())
            ->where('assigned_to', $user->id)
            ->where('status', 'completed')
            ->count();
        
        return Inertia::render('Housekeeping/Dashboard', [
            'user' => $user,
            'stats' => [
                'totalRooms' => $totalRooms,
                'dirtyRooms' => $dirtyRooms,
                'cleanRooms' => $cleanRooms,
                'inProgressRooms' => $inProgressRooms,
                'todayTasks' => $todayTasks,
                'completedTasks' => $completedTasks,
            ]
        ]);
    }
}
