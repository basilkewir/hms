<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\StockBatch;
use App\Models\PurchaseDocument;
use App\Models\ExpenseDocument;
use App\Models\DeliveryDocument;
use App\Models\CashDrawerSession;
use App\Models\PosTransaction;
use App\Models\PosExpense;
use App\Models\PosExpenseCategory;
use App\Models\Setting;
use App\Models\Customer;
use App\Models\Room;
use App\Models\Reservation;
use App\Models\Guest;
use App\Models\GuestFolio;
use App\Models\FolioCharge;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class POSController extends Controller
{
    public function index()
    {
        // Check if user has active cash drawer session
        $activeSession = CashDrawerSession::where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        $categories = ProductCategory::where('is_active', true)
            ->withCount('products')
            ->get();

        $products = Product::with('category', 'brand', 'unit')
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->get();

        // Get default POS tax rate from settings (general.tax_rate), fallback to 0%
        $defaultTaxRate = (float) Setting::get('tax_rate', 0);

        // Get today's sales for the current user only (so staff see only their own today's totals)
        $today = now()->toDateString();
        $todaySales = Sale::where('user_id', auth()->id())
            ->whereDate('sale_date', $today)
            ->sum('total_amount');
        $todaySalesCount = Sale::where('user_id', auth()->id())
            ->whereDate('sale_date', $today)
            ->count();

        $recentSales = Sale::with([
            'customer:id,first_name,last_name,customer_code',
            'user:id,first_name,last_name,email',
            'items.product:id,name,code',
        ])
            ->where('user_id', auth()->id())
            ->orderByDesc('sale_date')
            ->limit(20)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'sale_number' => $sale->sale_number,
                    'sale_date' => $sale->sale_date,
                    'payment_method' => $sale->payment_method,
                    'is_walk_in' => (bool) $sale->is_walk_in,
                    'customer_name' => $sale->is_walk_in
                        ? 'Walk-In'
                        : ($sale->customer ? trim($sale->customer->first_name . ' ' . $sale->customer->last_name) : ($sale->customer_name ?? 'N/A')),
                    'items_count' => $sale->items?->count() ?? 0,
                    'total_amount' => (float) $sale->total_amount,
                ];
            });

        return Inertia::render('POS/StandaloneIndex', [
            'user' => auth()->user()->load('roles'),
            'categories' => $categories,
            'products' => $products->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'barcode' => $product->barcode,
                'price' => (float) $product->price,
                'stock_quantity' => $product->stock_quantity,
                'category_id' => $product->category_id,
                'category' => $product->category->name ?? 'Uncategorized',
                'category_color' => $product->category->color ?? '#6B7280',
                'emoji' => $product->emoji ?? '🍽️',
                'image' => $product->image ?? null,
                'description' => $product->description ?? '',
                'is_service' => $product->is_service,
                'tax_rate' => (float) $product->tax_rate,
                'is_low_stock' => $product->isLowStock()
            ]),
            'customers' => $this->getCustomersWithGuests(),
            'activeSession' => $activeSession,
            'canManageInventory' => true,
            'canManageExpenses' => true,
            'taxRate' => $defaultTaxRate,
            'todaySales' => (float) $todaySales,
            'todaySalesCount' => $todaySalesCount,
            'recentSales' => $recentSales,
            'hotelName' => Setting::get('hotel_name', 'Grand Hotel'),
            'hotelAddress' => Setting::get('hotel_address', ''),
            'hotelPhone' => Setting::get('hotel_phone', ''),
            'hotelEmail' => Setting::get('hotel_email', ''),
            'receiptSizeRestaurant' => Setting::get('receipt_size_restaurant', '80mm'),
            'exitUrl' => $this->getDashboardUrlForCurrentUser(),
        ]);
    }

    public function processSale(Request $request)
    {
        // Check if user has active cash drawer session (not required for room charges)
        $isChargedToRoom = $request->input('is_charged_to_room', false);

        if (!$isChargedToRoom) {
            $activeSession = CashDrawerSession::where('user_id', auth()->id())
                ->where('is_active', true)
                ->first();

            if (!$activeSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please open cash drawer first'
                ], 400);
            }
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'customer_id' => 'nullable|string', // Can be customer ID or guest identifier
            'is_walk_in' => 'boolean',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'room_number' => 'nullable|string|max:20',
            'is_charged_to_room' => 'boolean',
            'payment_method' => 'required|in:cash,card,bank_transfer,mobile,room_charge',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'guest_id' => 'nullable|exists:guests,id',
            'reservation_id' => 'nullable|exists:reservations,id',
            'room_id' => 'nullable|exists:rooms,id'
        ]);

        DB::beginTransaction();

        try {
            // Check if customer_id is a guest identifier (format: guest_{guest_id}_{reservation_id})
            $isGuestSelection = false;
            $selectedCustomerId = null;
            $selectedGuestId = null;
            $selectedReservationId = null;
            $selectedRoomId = null;

            if (!empty($validated['customer_id'])) {
                if (str_starts_with($validated['customer_id'], 'guest_')) {
                    // This is a guest selection
                    $isGuestSelection = true;
                    $parts = explode('_', $validated['customer_id']);
                    if (count($parts) >= 3) {
                        $selectedGuestId = (int) $parts[1];
                        $selectedReservationId = (int) $parts[2];

                        // Get reservation to find room
                        $reservation = Reservation::with(['room', 'guest'])->find($selectedReservationId);
                        if ($reservation && $reservation->guest_id == $selectedGuestId) {
                            $selectedRoomId = $reservation->room_id;
                            $selectedGuestId = $reservation->guest_id;
                        }
                    }
                } else {
                    // Regular customer
                    $selectedCustomerId = (int) $validated['customer_id'];
                }
            }

            // Get customer discount if customer is selected
            $customerDiscount = 0;
            if ($selectedCustomerId && !($validated['is_walk_in'] ?? false)) {
                $customer = Customer::with('customerGroup')->find($selectedCustomerId);
                if ($customer && $customer->customerGroup && $customer->customerGroup->is_active) {
                    $customerDiscount = $customer->customerGroup->discount_percentage;
                }
            }

            // Get guest type discount if guest is selected
            $guestTypeDiscount = 0;
            $guestTypeDiscountAmount = 0;
            $vipDiscount = 0;
            $vipDiscountAmount = 0;

            if (!empty($validated['guest_id']) && !($validated['is_walk_in'] ?? false)) {
                $guest = Guest::with('guestType')->find($validated['guest_id']);

                if ($guest) {
                    // Check guest type discount
                    $autoApplyGuestTypeDiscount = Setting::get('auto_apply_guest_type_discount', true);
                    if ($autoApplyGuestTypeDiscount && $guest->guestType && $guest->guestType->is_active) {
                        $guestTypeDiscount = (float) $guest->guestType->discount_percentage;
                    }

                    // Check VIP discount
                    $autoApplyVipDiscount = Setting::get('auto_apply_vip_discount', true);
                    $vipDiscountPercentage = (float) Setting::get('vip_discount_percentage', 0);
                    if ($autoApplyVipDiscount && $guest->is_vip && $vipDiscountPercentage > 0) {
                        $vipDiscount = $vipDiscountPercentage;
                    }
                }
            }

            // Calculate totals
            $subtotal = 0;
            $taxAmount = 0;

            // Get tax rate from settings first (respects 0% if set)
            $settingsTaxRate = (float) Setting::get('tax_rate', 0);

            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);

                if (!$product) {
                    throw new \Exception("Product with ID {$item['product_id']} not found");
                }

                $itemTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $itemTotal;

                // Always use settings tax_rate if it's set to 0 (global override)
                // Otherwise, use product tax_rate if explicitly set, or fall back to settings
                if ($settingsTaxRate == 0) {
                    $productTaxRate = 0; // Global 0% tax override
                } else {
                    $productTaxRate = $product->tax_rate !== null ? (float) $product->tax_rate : $settingsTaxRate;
                }
                $taxAmount += $itemTotal * ($productTaxRate / 100);
            }

            // Apply customer group discount if applicable
            $customerGroupDiscountAmount = 0;
            if ($customerDiscount > 0) {
                $customerGroupDiscountAmount = ($subtotal * $customerDiscount) / 100;
            }

            // Apply guest type discount if applicable
            if ($guestTypeDiscount > 0) {
                $guestTypeDiscountAmount = ($subtotal * $guestTypeDiscount) / 100;
            }

            // Apply VIP discount if applicable
            if ($vipDiscount > 0) {
                $vipDiscountAmount = ($subtotal * $vipDiscount) / 100;
            }

            $manualDiscountAmount = $validated['discount_amount'] ?? 0;

            // Combine discounts based on discount combination mode
            $discountCombinationMode = Setting::get('discount_combination_mode', 'add');
            if ($discountCombinationMode === 'override' && $manualDiscountAmount > 0) {
                // Manual discount overrides automatic discounts
                $totalDiscountAmount = $manualDiscountAmount;
            } else {
                // Add all discounts
                $totalDiscountAmount = $customerGroupDiscountAmount + $guestTypeDiscountAmount + $vipDiscountAmount + $manualDiscountAmount;
            }

            $totalAmount = $subtotal + $taxAmount - $totalDiscountAmount;

            // Handle room billing
            $roomId = null;
            $reservationId = null;
            $guestId = null;
            $isChargedToRoom = $validated['is_charged_to_room'] ?? false;

            // Priority 1: Direct guest/reservation/room IDs provided (from customer dropdown guest selection)
            if (!empty($validated['guest_id']) && !empty($validated['reservation_id']) && !empty($validated['room_id'])) {
                $isChargedToRoom = true;
                $roomId = $validated['room_id'];
                $reservationId = $validated['reservation_id'];
                $guestId = $validated['guest_id'];
            }
            // Priority 2: Guest identifier in customer_id (fallback)
            // Sets the room/reservation/guest IDs for sale tracking but RESPECTS the
            // is_charged_to_room checkbox — guest may choose to pay cash at the counter.
            elseif ($isGuestSelection && $selectedReservationId && $selectedRoomId) {
                $roomId = $selectedRoomId;
                $reservationId = $selectedReservationId;
                $guestId = $selectedGuestId;
                // $isChargedToRoom remains as the user set it via the checkbox
            }
            // Priority 3: Room number lookup (legacy method)
            elseif (!empty($validated['room_number']) && $isChargedToRoom) {
                $room = Room::where('room_number', $validated['room_number'])->first();
                if ($room) {
                    $roomId = $room->id;

                    // Find active reservation for this room
                    $reservation = Reservation::where('room_id', $room->id)
                        ->where('status', 'checked_in')
                        ->whereDate('check_in_date', '<=', now())
                        ->whereDate('check_out_date', '>=', now())
                        ->with('guest')
                        ->first();

                    if ($reservation) {
                        $reservationId = $reservation->id;
                        $guestId = $reservation->guest_id;
                    } else {
                        // If no active reservation found, don't charge to room
                        $isChargedToRoom = false;
                    }
                } else {
                    // Room not found, don't charge to room
                    $isChargedToRoom = false;
                }
            }

            // Create sale
            $sale = Sale::create([
                'sale_number' => (new Sale())->generateSaleNumber(),
                'user_id' => auth()->id(),
                'customer_id' => $selectedCustomerId, // Only set if it's a regular customer
                'room_id' => $roomId,
                'reservation_id' => $reservationId,
                'guest_id' => $guestId,
                'is_walk_in' => $validated['is_walk_in'] ?? false,
                'is_charged_to_room' => $isChargedToRoom,
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $totalDiscountAmount,
                'total_amount' => $totalAmount,
                'payment_method' => $isChargedToRoom ? 'room_charge' : $validated['payment_method'],
                'payment_status' => $isChargedToRoom ? 'pending' : 'completed',
                'notes' => $validated['notes'] ?? null,
                'sale_date' => now()
            ]);

            // Create sale items and update stock
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);

                if (!$product) {
                    throw new \Exception("Product with ID {$item['product_id']} not found when creating sale items");
                }

                // Get the product's cost price at time of sale for profit calculation
                $unitCost = $product->cost_price ?? 0;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'unit_cost' => $unitCost,
                    'total_price' => $item['quantity'] * $item['unit_price']
                ]);

                // Update stock for physical products and record movement
                if (!$product->is_service) {
                    // Check if enough stock is available
                    if ($product->stock_quantity < $item['quantity']) {
                        throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$product->stock_quantity}, Requested: {$item['quantity']}");
                    }

                    StockMovement::recordMovement(
                        $item['product_id'],
                        'out',
                        $item['quantity'],
                        'sale',
                        $sale->id,
                        'Sale: ' . $sale->sale_number
                    );
                }
            }

            // If charged to room, create folio charge
            if ($isChargedToRoom && $reservationId) {
                // Get or create folio
                $folio = GuestFolio::where('reservation_id', $reservationId)
                    ->where('status', 'open')
                    ->first();

                if (!$folio) {
                    // Create folio if it doesn't exist
                    $reservation = Reservation::with('guest', 'room')->findOrFail($reservationId);
                    // Generate a unique folio number to avoid unique constraint violations
                    // if a previous folio (e.g. closed) existed for the same reservation.
                    $folioNumber = 'FOL-' . str_pad($reservationId, 6, '0', STR_PAD_LEFT);
                    if (GuestFolio::where('folio_number', $folioNumber)->exists()) {
                        $folioNumber = 'FOL-' . strtoupper(\Illuminate\Support\Str::random(8));
                    }
                    $folio = GuestFolio::create([
                        'folio_number' => $folioNumber,
                        'reservation_id' => $reservationId,
                        'guest_id' => $reservation->guest_id,
                        'room_id' => $reservation->room_id,
                        'status' => 'open',
                        'folio_date' => now(),
                        'room_charges' => 0,
                        'service_charges' => 0,
                        'tax_amount' => 0,
                        'discount_amount' => 0,
                        'total_amount' => 0,
                        'paid_amount' => 0,
                        'balance_amount' => 0,
                    ]);
                }

                // Create folio charge for the sale
                $itemNames = [];
                foreach ($validated['items'] as $item) {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $itemNames[] = $product->name . ' (x' . $item['quantity'] . ')';
                    }
                }

                $folioCharge = FolioCharge::create([
                    'guest_folio_id' => $folio->id,
                    'charge_code' => 'POS',
                    'description' => 'POS Sale: ' . $sale->sale_number . ' - ' . implode(', ', $itemNames),
                    'charge_date' => now()->toDateString(),
                    'charge_time' => now(),
                    'quantity' => 1,
                    'unit_price' => $totalAmount,
                    'total_amount' => $totalAmount,
                    'tax_rate' => $settingsTaxRate,
                    'tax_amount' => $taxAmount,
                    'discount_rate' => $customerDiscount,
                    'discount_amount' => $totalDiscountAmount,
                    'net_amount' => $totalAmount,
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'department' => 'Restaurant/Bar',
                    'posted_by' => auth()->id(),
                    'posted_at' => now(),
                ]);

                // Update folio totals
                $folio->service_charges += $totalAmount;
                $folio->tax_amount += $taxAmount;
                $folio->discount_amount += $totalDiscountAmount;
                $folio->total_amount = $folio->room_charges + $folio->service_charges + $folio->tax_amount - $folio->discount_amount;
                $folio->balance_amount = $folio->total_amount - $folio->paid_amount;
                $folio->save();
            } else {
                // Record POS transaction only if not charged to room
                if (!$isChargedToRoom) {
                    $activeSession = CashDrawerSession::where('user_id', auth()->id())
                        ->where('is_active', true)
                        ->first();

                    if ($activeSession) {
                        PosTransaction::create([
                            'cash_drawer_session_id' => $activeSession->id,
                            'sale_id' => $sale->id,
                            'user_id' => auth()->id(),
                            'type' => 'sale',
                            'amount' => $totalAmount,
                            'payment_method' => $validated['payment_method'],
                            'description' => 'Sale: ' . $sale->sale_number
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'sale' => $sale->load(['items.product.category', 'user', 'customer']),
                'message' => 'Sale completed successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Sale processing error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Sale failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function openDrawer(Request $request)
    {
        $validated = $request->validate([
            'opening_balance' => 'required|numeric|min:0'
        ]);

        // Close any existing active session
        CashDrawerSession::where('user_id', auth()->id())
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $session = CashDrawerSession::create([
            'user_id' => auth()->id(),
            'opening_balance' => $validated['opening_balance'],
            'opened_at' => now(),
            'is_active' => true
        ]);

        Log::info('Cash drawer opened by user: ' . auth()->user()->name . ' with balance: ' . $validated['opening_balance']);

        return response()->json([
            'success' => true,
            'session' => $session,
            'message' => 'Cash drawer opened successfully'
        ]);
    }

    public function closeDrawer(Request $request)
    {
        $validated = $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $session = CashDrawerSession::where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No active cash drawer session found'
            ], 400);
        }

        $expectedBalance = $session->calculateExpectedBalance();
        $difference = $validated['closing_balance'] - $expectedBalance;

        $session->update([
            'closing_balance' => $validated['closing_balance'],
            'expected_balance' => $expectedBalance,
            'difference' => $difference,
            'closed_at' => now(),
            'notes' => $validated['notes'],
            'is_active' => false
        ]);

        return response()->json([
            'success' => true,
            'session' => $session,
            'message' => 'Cash drawer closed successfully'
        ]);
    }

    // Inventory Management
    public function inventory()
    {
        $this->authorize('manage_inventory');

        $products = Product::with(['category', 'brand', 'unit', 'stockMovements' => function($query) {
            $query->latest()->limit(5);
        }])->get();

        $lowStockProducts = Product::whereRaw('stock_quantity <= min_stock_level')
            ->where('is_active', true)
            ->count();

        return Inertia::render('POS/Inventory/Index', [
            'user' => auth()->user()->load('roles'),
            'products' => $products->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'stock_quantity' => $product->stock_quantity,
                'min_stock_level' => $product->min_stock_level,
                'cost_price' => $product->cost_price,
                'price' => $product->price,
                'category' => $product->category->name ?? 'Uncategorized',
                'brand' => $product->brand->name ?? 'No Brand',
                'unit' => $product->unit->name ?? 'No Unit',
                'is_low_stock' => $product->isLowStock(),
                'total_value' => $product->getTotalValueAttribute(),
                'recent_movements' => $product->stockMovements
            ]),
            'lowStockCount' => $lowStockProducts
        ]);
    }

    public function stockMovements()
    {
        $this->authorize('manage_inventory');

        $movements = StockMovement::with(['product', 'user'])
            ->latest()
            ->paginate(50);

        return Inertia::render('POS/Inventory/StockMovements', [
            'user' => auth()->user()->load('roles'),
            'movements' => $movements
        ]);
    }

    public function adjustStock(Request $request)
    {
        $this->authorize('manage_inventory');

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer',
            'notes' => 'nullable|string'
        ]);

        $product = Product::find($validated['product_id']);
        $currentStock = $product->stock_quantity;
        $newStock = $validated['quantity'];
        $difference = $newStock - $currentStock;

        if ($difference != 0) {
            $type = $difference > 0 ? 'in' : 'out';
            $quantity = abs($difference);

            StockMovement::create([
                'product_id' => $validated['product_id'],
                'type' => 'adjustment',
                'quantity' => $quantity,
                'previous_stock' => $currentStock,
                'new_stock' => $newStock,
                'reference_type' => 'adjustment',
                'notes' => $validated['notes'] ?? 'Stock adjustment',
                'user_id' => auth()->id()
            ]);

            $product->update(['stock_quantity' => $newStock]);
        }

        return redirect()->back()
            ->with('success', 'Stock adjusted successfully');
    }

    // Product History
    public function productHistory(Product $product)
    {
        $this->authorize('manage_inventory');

        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // Get stock movements for this product
        $stockMovements = StockMovement::with('user')
            ->where('product_id', $product->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('POS/ProductHistory/Index', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role),
            'product' => $product,
            'stockMovements' => $stockMovements
        ]);
    }

    // Store Stock Adjustment
    public function storeAdjustment(Request $request)
    {
        $this->authorize('manage_inventory');

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string'
        ]);

        $product = Product::find($validated['product_id']);
        $currentStock = $product->stock_quantity;
        $newStock = $currentStock;

        if ($validated['type'] === 'in') {
            $newStock = $currentStock + $validated['quantity'];
        } elseif ($validated['type'] === 'out') {
            $newStock = max(0, $currentStock - $validated['quantity']);
        } else {
            // adjustment - set to exact quantity
            $newStock = $validated['quantity'];
        }

        // Update product stock
        $product->stock_quantity = $newStock;
        $product->save();

        // Create stock movement record
        StockMovement::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'quantity' => $validated['type'] === 'adjustment' ? $newStock - $currentStock : ($validated['type'] === 'in' ? $validated['quantity'] : -$validated['quantity']),
            'previous_stock' => $currentStock,
            'new_stock' => $newStock,
            'reference_type' => 'adjustment',
            'notes' => $validated['notes'] ?? 'Stock adjustment',
            'created_at' => now()
        ]);

        return redirect()->back()
            ->with('success', 'Stock adjustment created successfully');
    }

    // Store Stock Transfer
    public function storeTransfer(Request $request)
    {
        $this->authorize('manage_inventory');

        $validated = $request->validate([
            'product_id'       => 'required|exists:products,id',
            'from_location_id' => 'required|exists:locations,id',
            'to_location_id'   => 'required|exists:locations,id|different:from_location_id',
            'quantity'         => 'required|numeric|min:0.01',
            'notes'            => 'nullable|string'
        ]);

        $product = Product::find($validated['product_id']);

        // Create stock transfer record using location columns
        StockTransfer::create([
            'product_id'             => $product->id,
            'from_location_id'       => $validated['from_location_id'],
            'destination_location_id'=> $validated['to_location_id'],
            'user_id'                => auth()->id(),
            'quantity'               => $validated['quantity'],
            'status'                 => 'pending',
            'notes'                  => $validated['notes'] ?? 'Stock transfer',
        ]);

        return redirect()->back()
            ->with('success', 'Stock transfer created successfully');
    }

    // Purchase Orders
    public function purchases()
    {
        $this->authorize('manage_purchases');

        // ... (rest of the code remains the same)
        $purchaseOrders = PurchaseOrder::with(['supplier', 'user', 'items.product', 'purchaseDocuments', 'deliveryDocuments', 'payments'])
            ->latest()
            ->paginate(20);

        // Convert decimal fields to float for proper JSON serialization
        $purchaseOrders->getCollection()->transform(function ($order) {
            $order->subtotal = (float) $order->subtotal;
            $order->tax_rate = (float) $order->tax_rate;
            $order->tax_amount = (float) $order->tax_amount;
            $order->shipping_cost = (float) $order->shipping_cost;
            $order->total_amount = (float) $order->total_amount;
            // Calculate from actual payments relationship (already eager loaded)
            $actualPaid = $order->payments->sum('amount');
            $order->paid_amount = (float) $actualPaid;
            $order->remaining_amount = (float) max(0, $order->total_amount - $actualPaid);
            return $order;
        });

        $suppliers = Supplier::where('is_active', true)->get();

        $products = Product::where('is_active', true)
            ->with('category', 'brand', 'unit')
            ->get()
            ->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'barcode' => $product->barcode,
                'cost_price' => (float) $product->cost_price,
                'price' => (float) $product->price,
                'stock_quantity' => $product->stock_quantity
            ]);

        $user = auth()->user()->load('roles');

        return Inertia::render('POS/Purchases/Index', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($user->roles->first()?->name ?? 'staff'),
            'purchaseOrders' => $purchaseOrders,
            'suppliers' => $suppliers,
            'products' => $products
        ]);
    }

    public function createPurchase()
    {
        $this->authorize('manage_purchases');

        $suppliers = Supplier::where('is_active', true)->get();

        $products = Product::where('is_active', true)
            ->with('category', 'brand', 'unit')
            ->get()
            ->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'barcode' => $product->barcode,
                'cost_price' => (float) $product->cost_price,
                'price' => (float) $product->price,
                'stock_quantity' => $product->stock_quantity
            ]);

        $locations = \App\Models\Location::where('is_active', true)->orderBy('name')->get(['id', 'name', 'type']);

        $budgets = \App\Models\Budget::whereIn('status', ['active', 'approved'])->orderBy('name')->get(['id', 'name', 'amount']);

        $user = auth()->user()->load('roles');

        return Inertia::render('POS/Purchases/Create', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($user->roles->first()?->name ?? 'staff'),
            'suppliers' => $suppliers,
            'products' => $products,
            'locations' => $locations,
            'budgets' => $budgets
        ]);
    }

    public function createPurchaseOrder(Request $request)
    {
        $this->authorize('manage_purchases');

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'shipping_cost' => 'nullable|numeric|min:0',
            'expected_date' => 'nullable|date',
            'delivery_time_days' => 'nullable|integer|min:0',
            'purchase_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
            'payment_status' => 'nullable|in:unpaid,partial,paid',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:cash,bank_transfer,cheque,credit_card',
            'purchase_type' => 'nullable|in:resale,expense',
            'expense_category' => 'nullable|string|max:255',
            'budget_id' => 'nullable|exists:budgets,id',
            'location_id' => 'nullable|exists:locations,id'
        ]);

        DB::beginTransaction();

        try {
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['quantity'] * $item['unit_cost'];
            }

            // Use tax_percentage if provided, otherwise tax_rate
            $taxRate = $validated['tax_percentage'] ?? $validated['tax_rate'] ?? 0;
            $taxAmount = $subtotal * ($taxRate / 100);
            $shippingCost = $validated['shipping_cost'] ?? 0;
            $totalAmount = $subtotal + $taxAmount + $shippingCost;

            $purchaseOrder = PurchaseOrder::create([
                'po_number' => (new PurchaseOrder())->generatePoNumber(),
                'supplier_id' => $validated['supplier_id'],
                'user_id' => auth()->id(),
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'remaining_amount' => $totalAmount,
                'order_date' => now()->toDateString(),
                'expected_date' => $validated['expected_date'] ?? null,
                'delivery_time_days' => $validated['delivery_time_days'] ?? null,
                'purchase_conditions' => $validated['purchase_conditions'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'location_id' => !empty($validated['location_id']) ? $validated['location_id'] : null,
                'purchase_type' => $validated['purchase_type'] ?? 'resale',
                'expense_category' => $validated['expense_category'] ?? null,
                'budget_id' => !empty($validated['budget_id']) ? $validated['budget_id'] : null,
            ]);

            foreach ($validated['items'] as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['quantity'] * $item['unit_cost']
                ]);
            }

            // Handle partial or full payment if specified
            $paymentMethod = $validated['payment_method'] ?? 'cash';

            if ($validated['payment_status'] === 'partial' && !empty($validated['amount_paid']) && $validated['amount_paid'] > 0) {
                SupplierPayment::create([
                    'supplier_id' => $validated['supplier_id'],
                    'purchase_order_id' => $purchaseOrder->id,
                    'payment_number' => SupplierPayment::generatePaymentNumber(),
                    'payment_type' => 'partial',
                    'amount' => $validated['amount_paid'],
                    'payment_method' => $paymentMethod,
                    'payment_date' => now()->toDateString(),
                    'user_id' => auth()->id()
                ]);
                $purchaseOrder->updatePaymentStatus();
            } elseif ($validated['payment_status'] === 'paid') {
                SupplierPayment::create([
                    'supplier_id' => $validated['supplier_id'],
                    'purchase_order_id' => $purchaseOrder->id,
                    'payment_number' => SupplierPayment::generatePaymentNumber(),
                    'payment_type' => 'full',
                    'amount' => $totalAmount,
                    'payment_method' => $paymentMethod,
                    'payment_date' => now()->toDateString(),
                    'user_id' => auth()->id()
                ]);
                $purchaseOrder->updatePaymentStatus();
            }

            DB::commit();

            $user = auth()->user()->load('roles');
            $role = $user->roles->first()?->name ?? 'admin';
            $showRoute = $role === 'manager' ? 'manager.purchases.show' : 'pos.purchases.show';
            return redirect()->route($showRoute, $purchaseOrder->id)
                ->with('success', 'Purchase order created successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create purchase order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showPurchase(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('manage_purchases');

        $purchaseOrder->load([
            'supplier',
            'items.product.category',
            'user',
            'payments',
            'location'
        ]);

        // Convert decimal fields to float for proper JSON serialization
        $purchaseOrder->subtotal = (float) $purchaseOrder->subtotal;
        $purchaseOrder->tax_rate = (float) $purchaseOrder->tax_rate;
        $purchaseOrder->tax_amount = (float) $purchaseOrder->tax_amount;
        $purchaseOrder->shipping_cost = (float) $purchaseOrder->shipping_cost;
        $purchaseOrder->total_amount = (float) $purchaseOrder->total_amount;
        // Use calculated values from accessors instead of stored values
        $purchaseOrder->paid_amount = (float) $purchaseOrder->paid_amount;
        $purchaseOrder->remaining_amount = (float) $purchaseOrder->remaining_amount;

        // Convert item amounts as well
        $purchaseOrder->items->transform(function ($item) {
            $item->unit_cost = (float) $item->unit_cost;
            $item->total_cost = (float) $item->total_cost;
            return $item;
        });

        $user = auth()->user()->load('roles');

        return Inertia::render('POS/Purchases/Show', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($user->roles->first()?->name ?? 'staff'),
            'purchaseOrder' => $purchaseOrder
        ]);
    }

    public function editPurchase(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('manage_purchases');

        // Only allow editing if status is pending or in_transit
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'admin';
        $showRoute = $role === 'manager' ? 'manager.purchases.show' : 'pos.purchases.show';

        if (!in_array($purchaseOrder->status, ['pending', 'in_transit'])) {
            return redirect()->route($showRoute, $purchaseOrder->id)
                ->with('error', 'Cannot edit purchase order with status: ' . $purchaseOrder->status);
        }

        $purchaseOrder->load(['supplier', 'items.product']);

        $suppliers = Supplier::where('is_active', true)->get();

        $products = Product::where('is_active', true)
            ->with('category')
            ->get()
            ->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'barcode' => $product->barcode,
                'cost_price' => (float) $product->cost_price,
                'price' => (float) $product->price,
                'stock_quantity' => $product->stock_quantity
            ]);

        $user = auth()->user()->load('roles');

        return Inertia::render('POS/Purchases/Edit', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($user->roles->first()?->name ?? 'staff'),
            'purchaseOrder' => $purchaseOrder,
            'suppliers' => $suppliers,
            'products' => $products
        ]);
    }

    public function updatePurchaseOrder(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('manage_purchases');

        // Only allow updating if status is pending or in_transit
        if (!in_array($purchaseOrder->status, ['pending', 'in_transit'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit purchase order with status: ' . $purchaseOrder->status
            ], 400);
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'nullable|date',
            'expected_delivery_date' => 'nullable|date',
            'reference' => 'nullable|string|max:255',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'budget_id' => 'nullable|exists:budgets,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();

        try {
            // Calculate new totals
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['quantity'] * $item['unit_cost'];
            }

            $taxAmount = $subtotal * (($validated['tax_rate'] ?? 0) / 100);
            $shippingCost = $validated['shipping_cost'] ?? 0;
            $totalAmount = $subtotal + $taxAmount + $shippingCost;

            // Update purchase order
            $purchaseOrder->update([
                'supplier_id' => $validated['supplier_id'],
                'order_date' => $validated['order_date'] ?? $purchaseOrder->order_date,
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'tax_rate' => $validated['tax_rate'] ?? 0,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'remaining_amount' => $totalAmount - $purchaseOrder->paid_amount,
                'notes' => $validated['notes'] ?? null,
                'budget_id' => !empty($validated['budget_id']) ? $validated['budget_id'] : null,
            ]);

            // Delete existing items
            $purchaseOrder->items()->delete();

            // Create new items
            foreach ($validated['items'] as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $item['quantity'],
                    'quantity_received' => 0,
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['quantity'] * $item['unit_cost']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase order updated successfully',
                'purchaseOrder' => $purchaseOrder->fresh(['items.product', 'supplier'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update purchase order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function receivePurchaseOrder(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('manage_purchases');

        $validated = $request->validate([
            'received_items' => 'required|array|min:1',
            'received_items.*.item_id' => 'required|exists:purchase_order_items,id',
            'received_items.*.product_id' => 'required|exists:products,id',
            'received_items.*.received_quantity' => 'required|integer|min:0',
            'received_items.*.unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'payment_amount' => 'nullable|numeric|min:0.01',
            'payment_method' => 'nullable|in:cash,bank_transfer,cheque,credit_card',
            'payment_reference' => 'nullable|string|max:255',
            'close_deal' => 'nullable|boolean',
            'total_accepted_value' => 'nullable|numeric|min:0',
            'settlement_balance' => 'nullable|numeric',
            'settlement_amount' => 'nullable|numeric|min:0.01',
            'settlement_method' => 'nullable|in:cash,bank_transfer,cheque,credit_card',
            'settlement_reference' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            foreach ($validated['received_items'] as $itemData) {
                $quantityReceived = (int) $itemData['received_quantity'];

                if ($quantityReceived > 0) {
                    $product = Product::find($itemData['product_id']);
                    $poItem = PurchaseOrderItem::find($itemData['item_id']);
                    $unitCost = $itemData['unit_cost'] ?? $poItem->unit_cost;

                    // --- Margin-preserving price calculation ---
                    $currentCost  = (float) $product->cost_price;
                    $currentPrice = (float) $product->price;

                    // Markup on cost: (sale - cost) / cost
                    $markupFraction = ($currentCost > 0)
                        ? ($currentPrice - $currentCost) / $currentCost
                        : (float) ($product->margin_percentage ?? 0) / 100;

                    // Derive new sale price using the same markup
                    $newSalePrice = ($unitCost > 0)
                        ? round($unitCost * (1 + $markupFraction), 2)
                        : $currentPrice;

                    // Create stock batch with sale_price and location
                    $locationId = $purchaseOrder->location_id ?? null;
                    $batch = StockBatch::create([
                        'product_id'       => $itemData['product_id'],
                        'purchase_order_id'=> $purchaseOrder->id,
                        'batch_number'     => StockBatch::generateBatchNumber(),
                        'quantity'         => $quantityReceived,
                        'unit_cost'        => $unitCost,
                        'sale_price'       => $newSalePrice,
                        'location_id'      => $locationId,
                        'received_date'    => now()->toDateString(),
                        'notes'            => $validated['notes'] ?? null,
                        'user_id'          => auth()->id()
                    ]);

                    // Update product stock
                    $product->increment('stock_quantity', $quantityReceived);

                    // Update product cost price, sale price, and stored margin
                    if ($unitCost > 0) {
                        $product->update([
                            'cost_price'        => $unitCost,
                            'price'             => $newSalePrice,
                            'margin_percentage' => round($markupFraction * 100, 4),
                        ]);
                    }

                    // Update purchase order item quantity received
                    if ($poItem) {
                        $poItem->update([
                            'quantity_received' => $poItem->quantity_received + $quantityReceived
                        ]);
                    }

                    // Record stock movement with location
                    StockMovement::recordMovement(
                        $itemData['product_id'],
                        'in',
                        $quantityReceived,
                        'purchase',
                        $purchaseOrder->id,
                        'Purchase order: ' . $purchaseOrder->po_number . ' - Batch: ' . $batch->batch_number,
                        $locationId
                    );
                }
            }

            // Record regular payment if provided
            if (!empty($validated['payment_amount']) && !empty($validated['payment_method'])) {
                SupplierPayment::create([
                    'supplier_id' => $purchaseOrder->supplier_id,
                    'purchase_order_id' => $purchaseOrder->id,
                    'payment_number' => SupplierPayment::generatePaymentNumber(),
                    'payment_type' => 'partial',
                    'amount' => $validated['payment_amount'],
                    'payment_method' => $validated['payment_method'],
                    'payment_date' => now()->toDateString(),
                    'reference_number' => $validated['payment_reference'] ?? null,
                    'user_id' => auth()->id()
                ]);
                $purchaseOrder->updatePaymentStatus();
            }

            // Handle Close Deal with partial goods
            if (!empty($validated['close_deal'])) {
                $totalAcceptedValue = $validated['total_accepted_value'] ?? 0;
                $settlementBalance = $validated['settlement_balance'] ?? 0;

                // Adjust PO total_amount to only reflect accepted goods
                $purchaseOrder->update([
                    'total_amount' => $totalAcceptedValue,
                    'notes' => ($purchaseOrder->notes ? $purchaseOrder->notes . "\n" : '')
                        . 'Deal closed with partial delivery. Accepted value: ' . $totalAcceptedValue
                ]);

                if ($settlementBalance > 0) {
                    // Supplier owes purchaser — record as a credit note (negative payment)
                    SupplierPayment::create([
                        'supplier_id' => $purchaseOrder->supplier_id,
                        'purchase_order_id' => $purchaseOrder->id,
                        'payment_number' => SupplierPayment::generatePaymentNumber(),
                        'payment_type' => 'credit_note',
                        'amount' => -abs($settlementBalance),
                        'payment_method' => 'credit_note',
                        'payment_date' => now()->toDateString(),
                        'reference_number' => $validated['settlement_reference'] ?? 'Credit note - supplier owes purchaser',
                        'user_id' => auth()->id()
                    ]);
                } elseif ($settlementBalance < 0 && !empty($validated['settlement_method'])) {
                    // Purchaser owes supplier — record settlement payment
                    $settleAmount = $validated['settlement_amount'] ?? abs($settlementBalance);
                    SupplierPayment::create([
                        'supplier_id' => $purchaseOrder->supplier_id,
                        'purchase_order_id' => $purchaseOrder->id,
                        'payment_number' => SupplierPayment::generatePaymentNumber(),
                        'payment_type' => 'settlement',
                        'amount' => $settleAmount,
                        'payment_method' => $validated['settlement_method'],
                        'payment_date' => now()->toDateString(),
                        'reference_number' => $validated['settlement_reference'] ?? null,
                        'user_id' => auth()->id()
                    ]);
                }

                $purchaseOrder->updatePaymentStatus();

                // Mark as closed regardless of item receipt
                $purchaseOrder->update([
                    'status' => 'closed',
                    'received_date' => now()->toDateString()
                ]);

                DB::commit();
                $user = auth()->user()->load('roles');
                $role = $user->roles->first()?->name ?? 'admin';
                $showRoute = $role === 'manager' ? 'manager.purchases.show' : 'pos.purchases.show';
                return redirect()->route($showRoute, $purchaseOrder->id)
                    ->with('success', 'Deal closed. Partial delivery accepted and balances settled.');
            }

            // Update purchase order status (normal flow — not closing deal)
            $purchaseOrder->load('items');
            $allReceived = $purchaseOrder->items->every(function($item) {
                return $item->quantity_received >= $item->quantity_ordered;
            });

            $isFullyPaid = $purchaseOrder->isFullyPaid();

            if ($allReceived && $isFullyPaid) {
                $newStatus = 'received';
            } elseif ($allReceived && !$isFullyPaid) {
                $newStatus = 'pending_payment';
            } else {
                $newStatus = 'in_transit';
            }

            $purchaseOrder->update([
                'status' => $newStatus,
                'received_date' => $allReceived ? now()->toDateString() : $purchaseOrder->received_date
            ]);

            DB::commit();

            $user = auth()->user()->load('roles');
            $role = $user->roles->first()?->name ?? 'admin';
            $showRoute = $role === 'manager' ? 'manager.purchases.show' : 'pos.purchases.show';
            return redirect()->route($showRoute, $purchaseOrder->id)
                ->with('success', 'Purchase order received successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to receive purchase order: ' . $e->getMessage());
        }
    }

    public function uploadPurchaseDocument(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('manage_purchases');

        $validated = $request->validate([
            'document_type' => 'required|string|in:receipt,invoice,delivery_note,other',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'description' => 'nullable|string'
        ]);

        try {
            $file = $request->file('file');
            $path = $file->store('purchase-documents', 'public');

            $document = PurchaseDocument::create([
                'purchase_order_id' => $purchaseOrder->id,
                'document_type' => $validated['document_type'],
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'description' => $validated['description'] ?? null,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'document' => $document,
                'message' => 'Document uploaded successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadDeliveryDocument(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('manage_purchases');

        $validated = $request->validate([
            'document_type' => 'required|string|in:delivery_note,proof_of_delivery,other',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'description' => 'nullable|string'
        ]);

        try {
            $file = $request->file('file');
            $path = $file->store('delivery-documents', 'public');

            $document = DeliveryDocument::create([
                'purchase_order_id' => $purchaseOrder->id,
                'document_type' => $validated['document_type'],
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'description' => $validated['description'] ?? null,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'document' => $document,
                'message' => 'Delivery document uploaded successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload delivery document: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadExpenseDocument(Request $request, PosExpense $expense)
    {
        $this->authorize('manage_expenses');

        $validated = $request->validate([
            'document_type' => 'required|string|in:receipt,invoice,other',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'description' => 'nullable|string'
        ]);

        try {
            $file = $request->file('file');
            $path = $file->store('expense-documents', 'public');

            $document = ExpenseDocument::create([
                'expense_id' => $expense->id,
                'document_type' => $validated['document_type'],
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'description' => $validated['description'] ?? null,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'document' => $document,
                'message' => 'Expense document uploaded successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload expense document: ' . $e->getMessage()
            ], 500);
        }
    }

    public function searchProductByBarcode(Request $request)
    {
        $validated = $request->validate([
            'barcode' => 'required|string'
        ]);

        $product = Product::where('barcode', $validated['barcode'])
            ->where('is_active', true)
            ->with(['category', 'stockBatches'])
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found with barcode: ' . $validated['barcode']
            ], 404);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'barcode' => $product->barcode,
                'price' => (float) $product->price,
                'cost_price' => (float) $product->cost_price,
                'stock_quantity' => $product->stock_quantity,
                'category' => $product->category->name ?? 'Uncategorized',
                'batches' => $product->stockBatches->map(function($batch) {
                    return [
                        'id' => $batch->id,
                        'batch_number' => $batch->batch_number,
                        'quantity' => $batch->quantity,
                        'unit_cost' => (float) $batch->unit_cost,
                        'received_date' => $batch->received_date,
                        'expiry_date' => $batch->expiry_date,
                        'is_expired' => $batch->isExpired(),
                        'is_expiring_soon' => $batch->isExpiringSoon()
                    ];
                })
            ]
        ]);
    }

    public function stockBatches(Request $request)
    {
        $this->authorize('manage_inventory');

        $query = StockBatch::with(['product', 'purchaseOrder', 'user']);

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('expiring_soon')) {
            $query->whereNotNull('expiry_date')
                  ->where('expiry_date', '<=', now()->addDays(30))
                  ->where('expiry_date', '>', now());
        }

        $batches = $query->latest('received_date')->paginate(50);

        return Inertia::render('POS/Inventory/StockBatches', [
            'user' => auth()->user()->load('roles'),
            'batches' => $batches,
            'filters' => $request->only(['product_id', 'expiring_soon'])
        ]);
    }

    public function productStockBatches(Product $product)
    {
        $this->authorize('manage_inventory');

        $batches = $product->stockBatches()
            ->with(['purchaseOrder', 'user'])
            ->latest('received_date')
            ->get();

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'total_stock' => $product->stock_quantity,
                'total_from_batches' => $batches->sum('quantity')
            ],
            'batches' => $batches->map(function($batch) {
                return [
                    'id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'quantity' => $batch->quantity,
                    'unit_cost' => (float) $batch->unit_cost,
                    'total_cost' => (float) $batch->total_cost,
                    'received_date' => $batch->received_date,
                    'expiry_date' => $batch->expiry_date,
                    'is_expired' => $batch->isExpired(),
                    'is_expiring_soon' => $batch->isExpiringSoon(),
                    'purchase_order' => $batch->purchaseOrder ? [
                        'id' => $batch->purchaseOrder->id,
                        'po_number' => $batch->purchaseOrder->po_number
                    ] : null
                ];
            })
        ]);
    }

    // Expenses
    public function expenses()
    {
        $this->authorize('manage_expenses');

        $expenses = PosExpense::with(['category', 'user'])
            ->latest()
            ->paginate(20);

        $categories = PosExpenseCategory::where('is_active', true)->get();

        return Inertia::render('POS/Expenses/Index', [
            'user' => auth()->user()->load('roles'),
            'expenses' => $expenses,
            'categories' => $categories
        ]);
    }

    public function createExpense(Request $request)
    {
        $this->authorize('manage_expenses');

        $validated = $request->validate([
            'category_id' => 'required|exists:pos_expense_categories,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,bank_transfer,mobile,room_charge',
            'expense_date' => 'required|date',
            'receipt_number' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $expense = PosExpense::create(array_merge($validated, [
            'expense_number' => (new PosExpense())->generateExpenseNumber(),
            'user_id' => auth()->id()
        ]));

        // If cash payment and active drawer session, record transaction
        if ($validated['payment_method'] === 'cash') {
            $activeSession = CashDrawerSession::where('user_id', auth()->id())
                ->where('is_active', true)
                ->first();

            if ($activeSession) {
                PosTransaction::create([
                    'cash_drawer_session_id' => $activeSession->id,
                    'user_id' => auth()->id(), // Record user
                    'type' => 'cash_out',
                    'amount' => -$validated['amount'],
                    'payment_method' => 'cash',
                    'description' => 'Expense: ' . $validated['description']
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'expense' => $expense->load('category'),
            'message' => 'Expense recorded successfully'
        ]);
    }

    // Reports
    public function reports()
    {
        $today = now()->toDateString();
        $thisMonth = now()->format('Y-m');

        $todaySales = Sale::whereDate('sale_date', $today)->sum('total_amount');
        $monthSales = Sale::where('sale_date', 'like', $thisMonth . '%')->sum('total_amount');
        $todayExpenses = PosExpense::whereDate('expense_date', $today)->sum('amount');
        $monthExpenses = PosExpense::where('expense_date', 'like', $thisMonth . '%')->sum('amount');

        $lowStockProducts = Product::whereRaw('stock_quantity <= min_stock_level')
            ->where('is_active', true)
            ->with('category')
            ->get();

        return Inertia::render('POS/Reports/Index', [
            'user' => auth()->user()->load('roles'),
            'stats' => [
                'today_sales' => $todaySales,
                'month_sales' => $monthSales,
                'today_expenses' => $todayExpenses,
                'month_expenses' => $monthExpenses,
                'today_profit' => $todaySales - $todayExpenses,
                'month_profit' => $monthSales - $monthExpenses
            ],
            'lowStockProducts' => $lowStockProducts
        ]);
    }

    public function sales(Request $request)
    {
        $query = Sale::with([
            'user:id,first_name,last_name,email',
            'customer:id,first_name,last_name,customer_code',
            'items.product:id,name,code',
            'room:id,room_number'
        ])
            ->orderBy('sale_date', 'desc');

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('sale_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('sale_date', '<=', $request->end_date);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by user (staff)
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $sales = $query->paginate(50);

        // Map sales to include profit calculations
        $sales->getCollection()->transform(function ($sale) {
            $sale->total_cost = $sale->items->sum(function ($item) {
                return ($item->unit_cost ?? 0) * $item->quantity;
            });
            $sale->total_profit = $sale->total_amount - $sale->total_cost;
            $sale->profit_margin = $sale->total_amount > 0
                ? (($sale->total_profit / $sale->total_amount) * 100)
                : 0;
            return $sale;
        });

        $user = auth()->user()->load('roles');
        $role = $user->roles->first()->name ?? 'admin';

        return Inertia::render('POS/Sales/Index', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role),
            'sales' => $sales,
            'filters' => $request->only(['start_date', 'end_date', 'payment_method', 'customer_id', 'user_id']),
            'customers' => Customer::active()->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'customer_code']),
            'users' => \App\Models\User::where(function($query) {
                $query->whereHas('sales')
                    ->orWhereHas('roles', function($q) {
                        $q->whereIn('name', ['admin', 'manager', 'front_desk', 'staff', 'accountant']);
                    });
            })
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email'])
            ->unique('id')
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => trim($user->first_name . ' ' . $user->last_name),
                    'email' => $user->email
                ];
            })
            ->values()
        ]);
    }

    public function showSale($id)
    {
        $sale = Sale::with(['user:id,first_name,last_name,email', 'customer', 'items.product.category'])
            ->findOrFail($id);

        // Calculate profit data
        $sale->total_cost = $sale->items->sum(function ($item) {
            return ($item->unit_cost ?? 0) * $item->quantity;
        });
        $sale->total_profit = $sale->total_amount - $sale->total_cost;
        $sale->profit_margin = $sale->total_amount > 0
            ? (($sale->total_profit / $sale->total_amount) * 100)
            : 0;

        $user = auth()->user()->load('roles');
        $role = $user->roles->first()->name ?? 'admin';

        return Inertia::render('POS/Sales/Show', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role),
            'sale' => $sale
        ]);
    }

    public function salesReport(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->endOfMonth()->toDateString();

        $query = Sale::with(['user:id,first_name,last_name,email', 'customer:id,first_name,last_name,customer_code', 'items.product:id,name,code'])
            ->whereBetween('sale_date', [$startDate, $endDate]);

        // Apply all filters
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('min_amount')) {
            $query->where('total_amount', '>=', $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('total_amount', '<=', $request->max_amount);
        }

        $sales = $query->get();

        // Calculate statistics
        $totalSales = $sales->sum('total_amount');
        $totalSubtotal = $sales->sum('subtotal');
        $totalTax = $sales->sum('tax_amount');
        $totalDiscount = $sales->sum('discount_amount');
        $totalCount = $sales->count();

        // Group by payment method
        $byPaymentMethod = $sales->groupBy('payment_method')->map(function($group) {
            return [
                'count' => $group->count(),
                'total' => $group->sum('total_amount')
            ];
        });

        // Group by day
        $byDay = $sales->groupBy(function($sale) {
            return $sale->sale_date->format('Y-m-d');
        })->map(function($group) {
            return [
                'count' => $group->count(),
                'total' => $group->sum('total_amount')
            ];
        });

        // Top products with profit
        $productSales = [];
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $productId = $item->product_id;
                $productName = $item->product->name ?? 'Unknown';
                if (!isset($productSales[$productId])) {
                    $productSales[$productId] = [
                        'name' => $productName,
                        'quantity' => 0,
                        'revenue' => 0,
                        'cost' => 0
                    ];
                }
                $productSales[$productId]['quantity'] += $item->quantity;
                $productSales[$productId]['revenue'] += $item->total_price;
                $productSales[$productId]['cost'] += ($item->unit_cost ?? 0) * $item->quantity;
            }
        }

        $user = auth()->user()->load('roles');
        $role = $user->roles->first()->name ?? 'admin';

        // Calculate profit data
        $totalCost = 0;
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $totalCost += ($item->unit_cost ?? 0) * $item->quantity;
            }
        }
        $totalProfit = $totalSales - $totalCost;
        $profitMargin = $totalSales > 0 ? (($totalProfit / $totalSales) * 100) : 0;

        // Group by staff
        $byStaff = $sales->groupBy('user_id')->map(function($group) {
            $staffSales = $group->sum('total_amount');
            $staffCost = 0;
            foreach ($group as $sale) {
                foreach ($sale->items as $item) {
                    $staffCost += ($item->unit_cost ?? 0) * $item->quantity;
                }
            }
            $firstSale = $group->first();
            return [
                'name' => $firstSale->user ? ($firstSale->user->first_name . ' ' . $firstSale->user->last_name) : 'Unknown',
                'count' => $group->count(),
                'total' => $staffSales,
                'profit' => $staffSales - $staffCost
            ];
        });

        // Daily sales by staff (for employee daily performance)
        $dailyStaffSales = $sales
            ->groupBy(function($sale) {
                return $sale->sale_date->format('Y-m-d');
            })
            ->map(function($dayGroup, $date) {
                return $dayGroup->groupBy('user_id')->map(function($staffGroup, $staffId) use ($date) {
                    $staffSales = $staffGroup->sum('total_amount');
                    $staffCost = 0;
                    foreach ($staffGroup as $sale) {
                        foreach ($sale->items as $item) {
                            $staffCost += ($item->unit_cost ?? 0) * $item->quantity;
                        }
                    }

                    $firstSale = $staffGroup->first();
                    return [
                        'date' => $date,
                        'user_id' => $staffId,
                        'name' => $firstSale->user ? trim(($firstSale->user->first_name ?? '') . ' ' . ($firstSale->user->last_name ?? '')) : 'Unknown',
                        'count' => $staffGroup->count(),
                        'total' => $staffSales,
                        'average_sale' => $staffGroup->count() > 0 ? ($staffSales / $staffGroup->count()) : 0,
                        'profit' => $staffSales - $staffCost,
                    ];
                })->values();
            })
            ->flatten(1)
            ->sortBy([
                ['date', 'desc'],
                ['total', 'desc'],
            ])
            ->values();

        // Update top products with profit
        $topProducts = collect($productSales)->map(function($product) {
            $product['profit'] = $product['revenue'] - ($product['cost'] ?? 0);
            $product['margin'] = $product['revenue'] > 0 ? (($product['profit'] / $product['revenue']) * 100) : 0;
            return $product;
        })->sortByDesc('revenue')->take(10)->values();

        // Top customers
        $customerSales = [];
        foreach ($sales as $sale) {
            $customerId = $sale->customer_id ?? 'walk-in';
            $customerName = $sale->is_walk_in
                ? 'Walk-In'
                : ($sale->customer ? ($sale->customer->first_name . ' ' . $sale->customer->last_name) : 'Unknown');

            if (!isset($customerSales[$customerId])) {
                $customerSales[$customerId] = [
                    'name' => $customerName,
                    'count' => 0,
                    'total' => 0
                ];
            }
            $customerSales[$customerId]['count']++;
            $customerSales[$customerId]['total'] += $sale->total_amount;
        }
        $topCustomers = collect($customerSales)->sortByDesc('total')->take(10)->values();

        $uniqueCustomers = $sales->whereNotNull('customer_id')->pluck('customer_id')->unique()->count();

        return Inertia::render('POS/Sales/Report', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role),
            'stats' => [
                'total_sales' => (float) $totalSales,
                'total_subtotal' => (float) $totalSubtotal,
                'total_tax' => (float) $totalTax,
                'total_discount' => (float) $totalDiscount,
                'total_count' => $totalCount,
                'average_sale' => $totalCount > 0 ? (float) ($totalSales / $totalCount) : 0,
                'total_cost' => (float) $totalCost,
                'total_profit' => (float) $totalProfit,
                'profit_margin' => (float) $profitMargin,
                'discount_percentage' => $totalSales > 0 ? (($totalDiscount / $totalSales) * 100) : 0,
                'tax_percentage' => $totalSubtotal > 0 ? (($totalTax / $totalSubtotal) * 100) : 0,
                'unique_customers' => $uniqueCustomers
            ],
            'byPaymentMethod' => $byPaymentMethod,
            'byDay' => $byDay,
            'byStaff' => $byStaff,
            'dailyStaffSales' => $dailyStaffSales,
            'topProducts' => $topProducts,
            'topCustomers' => $topCustomers,
            'customers' => Customer::active()->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'customer_code']),
            'users' => \App\Models\User::where(function($query) {
                $query->whereHas('sales')
                    ->orWhereHas('roles', function($q) {
                        $q->whereIn('name', ['admin', 'manager', 'front_desk', 'staff', 'accountant']);
                    });
            })
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email'])
            ->unique('id')
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => trim($user->first_name . ' ' . $user->last_name),
                    'email' => $user->email
                ];
            })
            ->values(),
            'filters' => $request->only(['start_date', 'end_date', 'payment_method', 'customer_id', 'user_id', 'status', 'min_amount', 'max_amount'])
        ]);
    }

    /**
     * Get the dashboard URL for the currently authenticated user based on their role.
     */
    private function getDashboardUrlForCurrentUser(): string
    {
        $user = auth()->user()->load('roles');
        $roleName = $user->roles->first()?->name;

        return match ($roleName) {
            'front_desk'   => '/front-desk/dashboard',
            'bartender'    => '/bartender/dashboard',
            'server'       => '/server/dashboard',
            'housekeeping' => '/housekeeping/dashboard',
            'maintenance'  => '/maintenance/dashboard',
            'accountant'   => '/accountant/dashboard',
            'manager'      => '/manager/dashboard',
            'hr'           => '/hr/dashboard',
            default        => '/admin/dashboard',
        };
    }

    /**
     * Get customers combined with occupied room guests
     */
    private function getCustomersWithGuests()
    {
        // Get regular customers
        $customers = Customer::active()->with('customerGroup')->orderBy('first_name')->orderBy('last_name')->get()->map(fn($customer) => [
            'id' => $customer->id,
            'type' => 'customer',
            'customer_code' => $customer->customer_code,
            'full_name' => $customer->full_name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'customer_group' => $customer->customerGroup ? [
                'id' => $customer->customerGroup->id,
                'name' => $customer->customerGroup->name,
                'discount_percentage' => (float) $customer->customerGroup->discount_percentage
            ] : null
        ]);

        // Get occupied rooms with their guests
        $occupiedRooms = Room::where('status', 'occupied')
            ->with(['reservations' => function($query) {
                $query->where('status', 'checked_in')
                    ->whereDate('check_in_date', '<=', now())
                    ->whereDate('check_out_date', '>=', now())
                    ->with(['guest.guestType', 'roomType']);
            }])
            ->get();

        $guests = collect();
        foreach ($occupiedRooms as $room) {
            $activeReservation = $room->reservations->first();
            if ($activeReservation && $activeReservation->guest) {
                $guest = $activeReservation->guest;
                $guests->push([
                    'id' => 'guest_' . $guest->id . '_' . $activeReservation->id, // Unique identifier
                    'type' => 'guest',
                    'customer_code' => null,
                    'full_name' => $guest->full_name . ' (Room ' . $room->room_number . ')',
                    'email' => $guest->email,
                    'phone' => $guest->phone,
                    'customer_group' => null,
                    // Additional guest/reservation info
                    'guest_id' => $guest->id,
                    'reservation_id' => $activeReservation->id,
                    'room_id' => $room->id,
                    'room_number' => $room->room_number,
                    'room_type' => $activeReservation->roomType->name ?? null,
                    // Guest type and VIP information for discounts
                    'guest_type' => $guest->guestType ? [
                        'id' => $guest->guestType->id,
                        'name' => $guest->guestType->name,
                        'code' => $guest->guestType->code,
                        'discount_percentage' => (float) $guest->guestType->discount_percentage,
                        'is_active' => $guest->guestType->is_active,
                    ] : null,
                    'is_vip' => (bool) $guest->is_vip,
                ]);
            }
        }

        // Combine and sort by name
        return $customers->concat($guests)->sortBy('full_name')->values();
    }

    // Unit CRUD Methods
    public function storeUnit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:units,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $unit = Unit::create($validated);

        return redirect()->back()
            ->with('success', 'Unit created successfully');
    }

    public function updateUnit(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:units,code,' . $unit->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $unit->update($validated);

        return redirect()->back()
            ->with('success', 'Unit updated successfully');
    }

    public function deleteUnit(Unit $unit)
    {
        $unit->delete();

        return redirect()->back()
            ->with('success', 'Unit deleted successfully');
    }

    // Category CRUD Methods
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean'
        ]);

        $category = ProductCategory::create($validated);

        return redirect()->back()
            ->with('success', 'Category created successfully');
    }

    public function updateCategory(Request $request, ProductCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean'
        ]);

        $category->update($validated);

        return redirect()->back()
            ->with('success', 'Category updated successfully');
    }

    public function deleteCategory(ProductCategory $category)
    {
        $category->delete();

        return redirect()->back()
            ->with('success', 'Category deleted successfully');
    }

    // Brand CRUD Methods
    public function storeBrand(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:brands,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $brand = Brand::create($validated);

        return redirect()->back()
            ->with('success', 'Brand created successfully');
    }

    public function updateBrand(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:brands,code,' . $brand->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $brand->update($validated);

        return redirect()->back()
            ->with('success', 'Brand updated successfully');
    }

    public function deleteBrand(Brand $brand)
    {
        $brand->delete();

        return redirect()->back()
            ->with('success', 'Brand deleted successfully');
    }

    // Warehouse CRUD Methods
    public function storeWarehouse(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $warehouse = Warehouse::create($validated);

        return redirect()->back()
            ->with('success', 'Warehouse created successfully');
    }

    public function updateWarehouse(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code,' . $warehouse->id,
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $warehouse->update($validated);

        return redirect()->back()
            ->with('success', 'Warehouse updated successfully');
    }

    public function deleteWarehouse(Warehouse $warehouse)
    {
        $warehouse->delete();

        return redirect()->back()
            ->with('success', 'Warehouse deleted successfully');
    }
}
