<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\POS\POSController;
use App\Http\Controllers\POS\SupplierController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\HousekeepingController;
use App\Http\Controllers\Admin\ReportController;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public Routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Auth::routes();

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('Admin/Dashboard', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('dashboard.index');

    // Users
    Route::get('/users', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('Admin/Users/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('users.index');

    // Products
    Route::get('/products', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('Admin/Products/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('products.index');

    // Reservations
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
    Route::get('/reservations/{reservation}/edit', [ReservationController::class, 'edit'])->name('reservations.edit');
    Route::put('/reservations/{reservation}', [ReservationController::class, 'update'])->name('reservations.update');
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'store'])->name('settings.store');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Users Export
    Route::get('/users/export', function (Request $request) {
        try {
            $format = $request->get('format', 'excel');
            
            // Get users with their roles
            $users = \App\Models\User::with('roles')->get()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? 'N/A',
                    'employee_id' => $user->employee_id ?? 'N/A',
                    'department' => $user->department ?? 'N/A',
                    'position' => $user->position ?? 'N/A',
                    'status' => $user->is_active ? 'Active' : 'Inactive',
                    'role' => $user->roles->first()?->name ?? 'No Role',
                    'hire_date' => $user->hire_date ? $user->hire_date->format('Y-m-d') : 'N/A',
                    'created_at' => $user->created_at->format('Y-m-d H:i:s')
                ];
            });

            $filename = 'users_export_' . date('Y_m_d_His');

            switch ($format) {
                case 'excel':
                    return response()->streamDownload(function () use ($users, $filename) {
                        echo generateUsersExcelExport($users, $filename);
                    }, $filename . '.html', [
                        'Content-Type' => 'text/html',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '.html"'
                    ]);

                case 'pdf':
                    return response()->streamDownload(function () use ($users, $filename) {
                        echo generateUsersPDFExport($users, $filename);
                    }, $filename . '.pdf', [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '.pdf"'
                    ]);

                case 'word':
                    return response()->streamDownload(function () use ($users, $filename) {
                        echo generateUsersWordExport($users, $filename);
                    }, $filename . '.doc', [
                        'Content-Type' => 'application/msword',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '.doc"'
                    ]);

                default:
                    return response()->json(['error' => 'Unsupported export format'], 400);
            }

        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to export users'], 500);
        }
    })->name('users.export');

    // Helper functions for export formats
    function generateUsersExcelExport($data, $filename) {
        $headers = ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Employee ID', 'Department', 'Position', 'Status', 'Role', 'Hire Date', 'Created At'];

        $html = '<!DOCTYPE html>';
        $html .= '<html>';
        $html .= '<head>';
        $html .= '<meta charset="utf-8">';
        $html .= '<title>' . htmlspecialchars($filename) . '</title>';
        $html .= '<style>';
        $html .= 'table { border-collapse: collapse; width: 100%; } ';
        $html .= 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } ';
        $html .= 'th { background-color: #f2f2f2; font-weight: bold; } ';
        $html .= '</style>';
        $html .= '</head>';
        $html .= '<body>';
        $html .= '<h1>' . $filename . '</h1>';
        $html .= '<table><thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $key => $value) {
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body>';
        $html .= '</html>';
        return $html;
    }

    function generateUsersPDFExport($data, $filename) {
        // Generate PDF-optimized HTML document
        $headers = ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Employee ID', 'Department', 'Position', 'Status', 'Role', 'Hire Date', 'Created At'];

        $html = '<!DOCTYPE html>';
        $html .= '<html>';
        $html .= '<head>';
        $html .= '<meta charset="utf-8">';
        $html .= '<title>' . htmlspecialchars($filename) . '</title>';
        $html .= '<style>';
        $html .= 'body { font-family: Arial, sans-serif; margin: 20px; }';
        $html .= 'table { border-collapse: collapse; width: 100%; } ';
        $html .= 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } ';
        $html .= 'th { background-color: #f2f2f2; font-weight: bold; } ';
        $html .= '</style>';
        $html .= '</head>';
        $html .= '<body>';
        $html .= '<h1>' . $filename . '</h1>';
        $html .= '<table><thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $key => $value) {
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body>';
        $html .= '</html>';
        return $html;
    }

    function generateUsersWordExport($data, $filename) {
        // Word-compatible HTML
        $headers = ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Employee ID', 'Department', 'Position', 'Status', 'Role', 'Hire Date', 'Created At'];

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office">';
        $html .= '<head>';
        $html .= '<meta charset="utf-8">';
        $html .= '<title>' . htmlspecialchars($filename) . '</title>';
        $html .= '<style>';
        $html .= 'table { border-collapse: collapse; width: 100%; } ';
        $html .= 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } ';
        $html .= 'th { background-color: #f2f2f2; font-weight: bold; } ';
        $html .= '</style>';
        $html .= '</head>';
        $html .= '<body>';
        $html .= '<h1>' . $filename . '</h1>';
        $html .= '<table><thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $key => $value) {
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body>';
        $html .= '</html>';
        return $html;
    }
});

// Housekeeping Routes
Route::middleware(['auth', 'role:housekeeping'])->prefix('housekeeping')->name('housekeeping.')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('Housekeeping/Dashboard', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('dashboard.index');

    // Tasks
    Route::get('/tasks', [HousekeepingController::class, 'tasksIndex'])->name('tasks.index');
    Route::get('/tasks/create', [HousekeepingController::class, 'tasksCreate'])->name('tasks.create');
    Route::post('/tasks', [HousekeepingController::class, 'tasksStore'])->name('tasks.store');
    Route::get('/tasks/{task}', [HousekeepingController::class, 'tasksShow'])->name('tasks.show');
    Route::get('/tasks/{task}/edit', [HousekeepingController::class, 'tasksEdit'])->name('tasks.edit');
    Route::put('/tasks/{task}', [HousekeepingController::class, 'tasksUpdate'])->name('tasks.update');
    Route::delete('/tasks/{task}', [HousekeepingController::class, 'tasksDestroy'])->name('tasks.destroy');

    // Task History
    Route::get('/tasks/history', [HousekeepingController::class, 'tasksHistory'])->name('tasks.history');

    // Inventory
    Route::get('/inventory/amenities', function () {
        return Inertia::render('Housekeeping/Inventory/Amenities');
    })->name('inventory.amenities');

    Route::get('/inventory/linens', function () {
        return Inertia::render('Housekeeping/Inventory/Linens');
    })->name('inventory.linens');

    Route::get('/inventory/supplies', function () {
        return Inertia::render('Housekeeping/Inventory/Supplies');
    })->name('inventory.supplies');

    Route::get('/inventory/request', function () {
        return Inertia::render('Housekeeping/Inventory/Request');
    })->name('inventory.request');

    // Maintenance Report
    Route::get('/maintenance/report', function () {
        return Inertia::render('Housekeeping/Maintenance/Report');
    })->name('maintenance.report');

    // Staff Management
    Route::get('/staff', function () {
        return Inertia::render('Housekeeping/Staff/Index');
    })->name('staff.index');

    // Schedules
    Route::get('/schedules', function () {
        return Inertia::render('Housekeeping/Schedules/Index');
    })->name('schedules.index');

    // Lost and Found
    Route::get('/lost-found', function () {
        return Inertia::render('Housekeeping/LostFound/Index');
    })->name('lost-found.index');

    // Guest Requests
    Route::get('/guest-requests', function () {
        return Inertia::render('Housekeeping/GuestRequests/Index');
    })->name('guest-requests.index');

    // Room Status
    Route::get('/room-status', function () {
        return Inertia::render('Housekeeping/RoomStatus/Index');
    })->name('room-status.index');
});

// POS Routes
Route::middleware(['auth', 'verified'])->prefix('pos')->name('pos.')->group(function () {
    Route::get('/', [\App\Http\Controllers\POS\POSController::class, 'index'])->name('index');
    Route::post('/process-sale', [\App\Http\Controllers\POS\POSController::class, 'processSale'])->name('process-sale');
    Route::post('/open-drawer', [\App\Http\Controllers\POS\POSController::class, 'openDrawer'])->name('open-drawer');

    // Suppliers
    Route::get('/suppliers', [\App\Http\Controllers\POS\SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers', [\App\Http\Controllers\POS\SupplierController::class, 'store'])->name('suppliers.store');

    // Products
    Route::get('/products', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // Get settings for currency display
        $settings = [
            'currency' => \App\Models\Setting::where('key', 'currency')->first()?->value ?? 'USD',
            'currency_position' => \App\Models\Setting::where('key', 'currency_position')->first()?->value ?? 'before',
            'decimal_separator' => \App\Models\Setting::where('key', 'decimal_separator')->first()?->value ?? '.',
            'thousand_separator' => \App\Models\Setting::where('key', 'thousand_separator')->first()?->value ?? ',',
            'currency_decimals' => \App\Models\Setting::where('key', 'currency_decimals')->first()?->value ?? 2,
        ];

        return Inertia::render('POS/Products/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'products' => \App\Models\Product::with('category', 'brand', 'unit')->orderBy('name')->get(),
            'categories' => \App\Models\ProductCategory::orderBy('name')->get(),
            'brands' => \App\Models\Brand::orderBy('name')->get(),
            'units' => \App\Models\Unit::orderBy('name')->get(),
            'settings' => $settings
        ]);
    })->name('products.index');

    // Product CRUD
    Route::post('/products', [\App\Http\Controllers\Admin\ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [\App\Http\Controllers\Admin\ProductController::class, 'update'])->name('products.update');

    // Stock Adjustment
    Route::post('/adjust-stock', [\App\Http\Controllers\POS\POSController::class, 'adjustStock'])->name('pos.adjust-stock');

    // Product History
    Route::get('/product-history/{product}', [\App\Http\Controllers\POS\POSController::class, 'productHistory'])->name('pos.product-history');

    // Stock Adjustments
    Route::post('/adjustments', [\App\Http\Controllers\POS\POSController::class, 'storeAdjustment'])->name('pos.adjustments.store');

    // Stock Transfers
    Route::post('/transfers', [\App\Http\Controllers\POS\POSController::class, 'storeTransfer'])->name('pos.transfers.store');

    // Categories
    Route::get('/categories', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // Get categories with product counts
        $categories = \App\Models\ProductCategory::withCount('products')->orderBy('name')->get();

        return Inertia::render('POS/Categories/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'categories' => $categories
        ]);
    })->name('categories.index');

    // Brands
    Route::get('/brands', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('POS/Brands/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'brands' => \App\Models\Brand::withCount('products')->orderBy('name')->get()
        ]);
    })->name('brands.index');

    // Units
    Route::get('/units', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('POS/Units/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'units' => \App\Models\Unit::withCount('products')->orderBy('name')->get()
        ]);
    })->name('units.index');

    // Warehouses
    Route::get('/warehouses', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('POS/Warehouses/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'warehouses' => \App\Models\Warehouse::withCount('products')->orderBy('name')->get()
        ]);
    })->name('warehouses.index');

    // Inventory
    Route::get('/inventory', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('POS/Inventory/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'products' => \App\Models\Product::with('category', 'brand', 'unit', 'warehouses')->orderBy('name')->get()
        ]);
    })->name('inventory.index');

    // Stock Adjustments
    Route::get('/adjustments', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('POS/Adjustments/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'adjustments' => \App\Models\StockAdjustment::with('product', 'warehouse')->orderBy('created_at', 'desc')->get()
        ]);
    })->name('adjustments.index');

    // Stock Transfers
    Route::get('/transfers', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('POS/Transfers/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'transfers' => \App\Models\StockTransfer::with('product', 'fromWarehouse', 'toWarehouse')->orderBy('created_at', 'desc')->get()
        ]);
    })->name('transfers.index');

    // Stock Movements
    Route::get('/stock-movements', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('POS/StockMovements/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'movements' => \App\Models\StockMovement::with('product', 'warehouse')->orderBy('created_at', 'desc')->get()
        ]);
    })->name('stock-movements.index');

    // API endpoints for dropdowns
    Route::get('/api/products', function () {
        return response()->json([
            'products' => \App\Models\Product::orderBy('name')->get(),
            'warehouses' => \App\Models\Warehouse::orderBy('name')->get()
        ]);
    });

    Route::get('/products-and-warehouses', function () {
        return response()->json([
            'products' => \App\Models\Product::orderBy('name')->get(),
            'warehouses' => \App\Models\Warehouse::orderBy('name')->get()
        ]);
    });
});

// HR Management Routes
Route::middleware(['auth', 'role:hr'])->prefix('hr')->name('hr.')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('HR/Dashboard', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('dashboard.index');

    // Reports
    Route::get('/reports', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('HR/Reports/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('reports.index');
});
