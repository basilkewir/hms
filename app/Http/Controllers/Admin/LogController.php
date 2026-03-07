<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;

class LogController extends Controller
{
    public function index()
    {
        $logPath = storage_path('logs/laravel.log');
        $logs = [];

        if (File::exists($logPath)) {
            $lines = array_reverse(file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
            $id = 1;
            foreach ($lines as $line) {
                if (!preg_match('/^\[(.*?)\]\s+([a-zA-Z0-9\-_]+)\.([A-Z]+):\s+(.*)$/', $line, $matches)) {
                    continue;
                }
                $logs[] = [
                    'id' => $id++,
                    'timestamp' => $matches[1],
                    'level' => strtolower($matches[3]),
                    'category' => 'system',
                    'message' => $matches[4],
                    'user' => null,
                ];
                if (count($logs) >= 500) {
                    break;
                }
            }
        }

        return Inertia::render('Admin/Settings/Logs', [
            'user' => auth()->user()->load('roles'),
            'logs' => $logs,
        ]);
    }

    public function download()
    {
        $logPath = storage_path('logs/laravel.log');
        if (!File::exists($logPath)) {
            return redirect()->route('admin.settings.logs')
                ->with('error', 'Log file not found.');
        }

        return response()->download($logPath, 'laravel.log');
    }

    public function clear()
    {
        $logPath = storage_path('logs/laravel.log');
        if (File::exists($logPath)) {
            File::put($logPath, '');
        }

        return redirect()->route('admin.settings.logs')
            ->with('success', 'Logs cleared successfully.');
    }
}
