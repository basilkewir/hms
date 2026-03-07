<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkShift;
use App\Models\User;
use App\Models\EmployeeShift;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use Carbon\Carbon;

class WorkShiftsSeeder extends Seeder
{
    public function run()
    {
        // Clear existing data in the right order to avoid foreign key constraints
        EmployeeShift::query()->delete();
        LeaveRequest::query()->delete();
        LeaveBalance::query()->delete();
        WorkShift::query()->delete();

        // Create work shifts
        $shifts = [
            [
                'name' => 'Morning Shift',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'hours' => 8.0,
                'break_minutes' => 30,
                'is_overnight' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Day Shift',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'hours' => 8.0,
                'break_minutes' => 30,
                'is_overnight' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Evening Shift',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'hours' => 8.0,
                'break_minutes' => 30,
                'is_overnight' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Night Shift',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'hours' => 8.0,
                'break_minutes' => 30,
                'is_overnight' => true,
                'is_active' => true,
            ],
        ];

        foreach ($shifts as $shift) {
            WorkShift::create($shift);
        }

        // Get users to assign shifts
        $users = User::where('id', '!=', 1)->take(5)->get(); // Skip admin user

        if ($users->isEmpty()) {
            $this->command->info('No users found to assign shifts. Create some users first.');
            return;
        }

        // Get work shifts
        $workShifts = WorkShift::all();

        // Assign shifts to users
        foreach ($users as $user) {
            // Assign different shifts to different users
            $shiftIndex = $users->search($user) % $workShifts->count();
            $workShift = $workShifts[$shiftIndex];

            // Create employee shift assignments
            EmployeeShift::create([
                'user_id' => $user->id,
                'work_shift_id' => $workShift->id,
                'effective_date' => now()->startOfWeek(),
                'days_of_week' => [1, 2, 3, 4, 5], // Monday to Friday
                'is_active' => true,
            ]);

            // Create some leave balances
            LeaveBalance::create([
                'user_id' => $user->id,
                'year' => now()->year,
                'vacation_days_allocated' => 15.0,
                'vacation_days_used' => 0.0,
                'vacation_days_remaining' => 15.0,
                'sick_days_allocated' => 10.0,
                'sick_days_used' => 0.0,
                'sick_days_remaining' => 10.0,
                'personal_days_allocated' => 5.0,
                'personal_days_used' => 0.0,
                'personal_days_remaining' => 5.0,
            ]);

            // Create some leave requests (only for some users)
            if ($users->search($user) < 2) {
                LeaveRequest::create([
                    'user_id' => $user->id,
                    'leave_type' => $users->search($user) == 0 ? 'vacation' : 'sick',
                    'start_date' => now()->addDays(7),
                    'end_date' => now()->addDays(7),
                    'days_requested' => 1.0,
                    'reason' => 'Personal reasons',
                    'status' => 'pending',
                ]);
            }
        }

        $this->command->info('Work shifts and related data seeded successfully!');
    }
}
