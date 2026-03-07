<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DashboardController;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use ZipArchive;

class BackupController extends Controller
{
    private string $backupDirectory = 'backups';

    public function index()
    {
        $diskPath = storage_path('app/' . $this->backupDirectory);
        if (!File::exists($diskPath)) {
            File::makeDirectory($diskPath, 0755, true);
        }

        $backups = collect(File::files($diskPath))
            ->filter(fn ($file) => $file->getExtension() === 'zip')
            ->map(function ($file, $index) {
                return [
                    'id' => $index + 1,
                    'name' => $file->getFilename(),
                    'type' => str_contains($file->getFilename(), 'manual') ? 'manual' : 'automatic',
                    'size' => $this->formatBytes($file->getSize()),
                    'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
                    'status' => 'completed',
                ];
            })
            ->sortByDesc('created_at')
            ->values();

        $backupSettings = [
            'frequency' => Setting::get('backup.frequency', 'daily'),
            'backup_time' => Setting::get('backup.backup_time', '02:00'),
            'retention_days' => (int) Setting::get('backup.retention_days', 30),
            'storage_location' => Setting::get('backup.storage_location', 'local'),
            'include_database' => (bool) Setting::get('backup.include_database', true),
            'include_files' => (bool) Setting::get('backup.include_files', true),
            'include_uploads' => (bool) Setting::get('backup.include_uploads', true),
            'include_logs' => (bool) Setting::get('backup.include_logs', false),
        ];

        $latestBackup = $backups->first();
        $backupStatus = [
            'lastBackup' => $latestBackup['created_at'] ?? 'Never',
            'nextBackup' => 'Configured by schedule',
            'storageUsed' => $this->formatBytes($this->directorySize($diskPath)),
        ];

        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('Admin/Settings/Backup', [
            'navigation'    => app(DashboardController::class)->getNavigationForRole($role),
            'backupSettings' => $backupSettings,
            'backupStatus'   => $backupStatus,
            'backupHistory'  => $backups,
        ]);
    }

    public function create(Request $request)
    {
        $diskPath = storage_path('app/' . $this->backupDirectory);
        if (!File::exists($diskPath)) {
            File::makeDirectory($diskPath, 0755, true);
        }

        $settings = [
            'include_database' => (bool) Setting::get('backup.include_database', true),
            'include_files' => (bool) Setting::get('backup.include_files', true),
            'include_uploads' => (bool) Setting::get('backup.include_uploads', true),
            'include_logs' => (bool) Setting::get('backup.include_logs', false),
        ];

        $timestamp = now()->format('Y_m_d_His');
        $backupName = 'backup_' . $timestamp . '.zip';
        $backupPath = $diskPath . DIRECTORY_SEPARATOR . $backupName;

        $zip = new ZipArchive();
        if ($zip->open($backupPath, ZipArchive::CREATE) !== true) {
            return redirect()->route('admin.settings.backup')
                ->with('error', 'Failed to create backup archive.');
        }

        if ($settings['include_database']) {
            $databaseDump = $this->exportDatabase();
            $zip->addFromString('database.json', json_encode($databaseDump, JSON_PRETTY_PRINT));
        }

        if ($settings['include_uploads']) {
            $this->addDirectoryToZip($zip, storage_path('app/public'), 'storage/public');
        }

        if ($settings['include_files']) {
            $this->addDirectoryToZip($zip, storage_path('app'), 'storage/app', ['backups']);
        }

        if ($settings['include_logs']) {
            $logPath = storage_path('logs/laravel.log');
            if (File::exists($logPath)) {
                $zip->addFile($logPath, 'logs/laravel.log');
            }
        }

        $zip->close();

        return redirect()->route('admin.settings.backup')
            ->with('success', 'Backup created successfully.');
    }

    public function download(string $backup)
    {
        $filePath = storage_path('app/' . $this->backupDirectory . '/' . $backup);
        if (!File::exists($filePath)) {
            return redirect()->route('admin.settings.backup')
                ->with('error', 'Backup file not found.');
        }

        return response()->download($filePath, $backup);
    }

    public function delete(string $backup)
    {
        $filePath = storage_path('app/' . $this->backupDirectory . '/' . $backup);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        return redirect()->route('admin.settings.backup')
            ->with('success', 'Backup deleted successfully.');
    }

    public function restore(string $backup)
    {
        $filePath = storage_path('app/' . $this->backupDirectory . '/' . $backup);
        if (!File::exists($filePath)) {
            return redirect()->route('admin.settings.backup')
                ->with('error', 'Backup file not found.');
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return redirect()->route('admin.settings.backup')
                ->with('error', 'Unable to open backup archive.');
        }

        $databaseJson = $zip->getFromName('database.json');
        $zip->close();

        if (!$databaseJson) {
            return redirect()->route('admin.settings.backup')
                ->with('error', 'Database snapshot not found in backup.');
        }

        $data = json_decode($databaseJson, true);
        if (!is_array($data)) {
            return redirect()->route('admin.settings.backup')
                ->with('error', 'Invalid backup format.');
        }

        DB::beginTransaction();
        try {
            foreach ($data as $table => $rows) {
                if (!Schema::hasTable($table) || !is_array($rows)) {
                    continue;
                }

                if (Schema::hasColumn($table, 'id')) {
                    $payload = array_filter($rows, fn ($row) => is_array($row) && array_key_exists('id', $row));
                    if (!empty($payload)) {
                        DB::table($table)->upsert($payload, ['id']);
                    }
                } else {
                    if (!empty($rows)) {
                        DB::table($table)->insertOrIgnore($rows);
                    }
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('admin.settings.backup')
                ->with('error', 'Backup restore failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.settings.backup')
            ->with('success', 'Backup restored successfully.');
    }

    private function exportDatabase(): array
    {
        $tables = [
            'users',
            'roles',
            'permissions',
            'model_has_roles',
            'model_has_permissions',
            'customers',
            'customer_groups',
            'guests',
            'guest_types',
            'rooms',
            'room_types',
            'reservations',
            'guest_folios',
            'folio_charges',
            'payments',
            'expenses',
            'expense_categories',
            'inventory_requests',
            'maintenance_requests',
            'housekeeping_tasks',
            'time_entries',
            'work_shifts',
            'employee_shifts',
            'settings',
        ];

        $dump = [];
        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            $dump[$table] = DB::table($table)->get()->map(fn ($row) => (array) $row)->all();
        }

        return $dump;
    }

    private function addDirectoryToZip(ZipArchive $zip, string $path, string $zipPath, array $exclude = []): void
    {
        if (!File::exists($path)) {
            return;
        }

        $files = File::allFiles($path);
        foreach ($files as $file) {
            $relativePath = str_replace($path . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $segments = explode(DIRECTORY_SEPARATOR, $relativePath);
            if (!empty($exclude) && in_array($segments[0], $exclude, true)) {
                continue;
            }
            $zip->addFile($file->getPathname(), $zipPath . '/' . $relativePath);
        }
    }

    private function directorySize(string $path): int
    {
        if (!File::exists($path)) {
            return 0;
        }
        $size = 0;
        foreach (File::allFiles($path) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
