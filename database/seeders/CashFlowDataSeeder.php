<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GuestFolio;
use App\Models\FolioCharge;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\Guest;
use App\Models\Room;
use App\Models\Reservation;
use Carbon\Carbon;

class CashFlowDataSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Seeding cash flow data...');
        
        // Create sample folio charges for room revenue
        $this->createFolioCharges();
        
        // Create sample POS sales
        $this->createPOSSales();
        
        // Create sample expenses for operating, investing, and financing activities
        $this->createExpenses();
        
        $this->command->info('Cash flow data seeded successfully!');
    }
    
    private function createFolioCharges()
    {
        // Get existing folios or create minimal ones
        $folios = GuestFolio::take(10)->get();
        
        if ($folios->isEmpty()) {
            // Create minimal folios if none exist
            for ($i = 1; $i <= 10; $i++) {
                $folio = GuestFolio::create([
                    'guest_id' => 1, // Assuming guest 1 exists
                    'folio_number' => 'FOL-' . date('Y') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'folio_date' => Carbon::now()->subDays(rand(1, 180)),
                    'status' => 'open',
                    'balance_amount' => 0,
                    'total_amount' => 0,
                ]);
                $folios->push($folio);
            }
        }
        
        // Create folio charges for the past 6 months
        $roomChargeCodes = ['ROOM', 'ROOM_RATE', 'ACCOMMODATION', 'ROOM_CHARGE', 'STAY', 'NIGHT'];
        $otherChargeCodes = ['FOOD', 'BEVERAGE', 'LAUNDRY', 'SERVICE', 'MINIBAR', 'PARKING', 'PHONE', 'INTERNET'];
        
        for ($monthsAgo = 6; $monthsAgo >= 0; $monthsAgo--) {
            $monthDate = Carbon::now()->subMonths($monthsAgo)->startOfMonth();
            $daysInMonth = $monthDate->daysInMonth;
            
            // Create room charges (daily rates)
            for ($day = 1; $day <= min($daysInMonth, 28); $day++) {
                $chargeDate = $monthDate->copy()->addDays($day - 1);
                
                // Create 5-10 room charges per day
                $chargesPerDay = rand(5, 10);
                for ($i = 0; $i < $chargesPerDay; $i++) {
                    $folio = $folios->random();
                    $chargeCode = $roomChargeCodes[array_rand($roomChargeCodes)];
                    $baseRate = rand(80, 250);
                    $quantity = rand(1, 3);
                    $unitPrice = $baseRate;
                    $totalAmount = $quantity * $unitPrice;
                    
                    FolioCharge::create([
                        'guest_folio_id' => $folio->id,
                        'charge_code' => $chargeCode,
                        'description' => 'Room charge - ' . ucfirst(strtolower(str_replace('_', ' ', $chargeCode))),
                        'charge_date' => $chargeDate,
                        'charge_time' => $chargeDate->copy()->addHours(rand(8, 20)),
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_amount' => $totalAmount,
                        'tax_rate' => 10,
                        'tax_amount' => $totalAmount * 0.1,
                        'discount_rate' => 0,
                        'discount_amount' => 0,
                        'net_amount' => $totalAmount * 1.1,
                        'department' => 'Front Office',
                        'posted_by' => 1,
                        'posted_at' => $chargeDate->copy()->addHours(rand(8, 20)),
                        'is_voided' => false,
                    ]);
                }
                
                // Create other revenue charges
                $otherChargesPerDay = rand(3, 7);
                for ($i = 0; $i < $otherChargesPerDay; $i++) {
                    $folio = $folios->random();
                    $chargeCode = $otherChargeCodes[array_rand($otherChargeCodes)];
                    $baseAmount = rand(15, 80);
                    $quantity = rand(1, 5);
                    $unitPrice = $baseAmount;
                    $totalAmount = $quantity * $unitPrice;
                    
                    FolioCharge::create([
                        'guest_folio_id' => $folio->id,
                        'charge_code' => $chargeCode,
                        'description' => ucfirst(strtolower(str_replace('_', ' ', $chargeCode))) . ' service',
                        'charge_date' => $chargeDate,
                        'charge_time' => $chargeDate->copy()->addHours(rand(6, 23)),
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_amount' => $totalAmount,
                        'tax_rate' => 10,
                        'tax_amount' => $totalAmount * 0.1,
                        'discount_rate' => rand(0, 10) * 0.01,
                        'discount_amount' => $totalAmount * (rand(0, 10) * 0.01),
                        'net_amount' => ($totalAmount * 1.1) * (1 - (rand(0, 10) * 0.01)),
                        'department' => 'Various',
                        'posted_by' => 1,
                        'posted_at' => $chargeDate->copy()->addHours(rand(6, 23)),
                        'is_voided' => false,
                    ]);
                }
            }
        }
        
        $this->command->info('Created folio charges: ' . FolioCharge::count());
    }
    
    private function createPOSSales()
    {
        // Get or create products
        $products = $this->createSampleProducts();
        
        // Create POS sales for the past 6 months
        for ($monthsAgo = 6; $monthsAgo >= 0; $monthsAgo--) {
            $monthDate = Carbon::now()->subMonths($monthsAgo)->startOfMonth();
            $daysInMonth = $monthDate->daysInMonth;
            
            for ($day = 1; $day <= min($daysInMonth, 28); $day++) {
                $saleDate = $monthDate->copy()->addDays($day - 1);
                
                // Create 3-8 sales per day
                $salesPerDay = rand(3, 8);
                for ($i = 0; $i < $salesPerDay; $i++) {
                    $saleTime = $saleDate->copy()->addHours(rand(7, 22))->addMinutes(rand(0, 59));
                    
                    // Create sale items
                    $totalAmount = 0;
                    $items = [];
                    $itemsCount = rand(1, 5);
                    
                    for ($j = 0; $j < $itemsCount; $j++) {
                        $product = $products[array_rand($products)];
                        $quantity = rand(1, 4);
                        $unitPrice = $product->selling_price;
                        $unitCost = $product->cost_price;
                        $totalItemAmount = $quantity * $unitPrice;
                        
                        $items[] = [
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'unit_cost' => $unitCost,
                            'total_amount' => $totalItemAmount,
                            'total_cost' => $quantity * $unitCost,
                        ];
                        
                        $totalAmount += $totalItemAmount;
                    }
                    
                    // Create sale
                    $sale = Sale::create([
                        'sale_number' => 'POS-' . date('Y') . '-' . str_pad(Sale::count() + 1, 4, '0', STR_PAD_LEFT),
                        'sale_date' => $saleDate,
                        'sale_time' => $saleTime,
                        'total_amount' => $totalAmount,
                        'total_cost' => collect($items)->sum('total_cost'),
                        'tax_amount' => $totalAmount * 0.1,
                        'discount_amount' => $totalAmount * (rand(0, 5) * 0.01),
                        'net_amount' => ($totalAmount * 1.1) * (1 - (rand(0, 5) * 0.01)),
                        'payment_method' => ['cash', 'card', 'mobile'][array_rand(['cash', 'card', 'mobile'])],
                        'payment_status' => ['paid', 'completed'][array_rand(['paid', 'completed'])],
                        'customer_name' => 'Guest ' . rand(1, 100),
                        'served_by' => 1,
                    ]);
                    
                    // Create sale items
                    foreach ($items as $item) {
                        \App\Models\SaleItem::create([
                            'sale_id' => $sale->id,
                            'product_id' => $item['product_id'],
                            'product_name' => $item['product_name'],
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['unit_price'],
                            'unit_cost' => $item['unit_cost'],
                            'total_amount' => $item['total_amount'],
                            'total_cost' => $item['total_cost'],
                        ]);
                    }
                }
            }
        }
        
        $this->command->info('Created POS sales: ' . Sale::count());
    }
    
    private function createExpenses()
    {
        // Get expense categories
        $categories = ExpenseCategory::all()->keyBy('code');
        
        // Create expenses for the past 6 months
        for ($monthsAgo = 6; $monthsAgo >= 0; $monthsAgo--) {
            $monthDate = Carbon::now()->subMonths($monthsAgo)->startOfMonth();
            
            // Operating expenses
            $operatingCategories = ['FOOD_COSTS', 'ROOM_SUPPLIES', 'LAUNDRY', 'UTILITIES', 'ADMIN', 'MAINTENANCE'];
            foreach ($operatingCategories as $categoryCode) {
                if (!isset($categories[$categoryCode])) continue;
                
                $category = $categories[$categoryCode];
                $baseAmount = $this->getBaseAmountForCategory($categoryCode);
                
                // Create 2-4 expenses per category per month
                for ($i = 0; $i < rand(2, 4); $i++) {
                    $expenseDate = $monthDate->copy()->addDays(rand(1, 28));
                    
                    Expense::create([
                        'expense_number' => 'EXP-' . $monthDate->format('Y') . '-' . str_pad(Expense::count() + 1, 4, '0', STR_PAD_LEFT),
                        'expense_category_id' => $category->id,
                        'vendor_name' => $this->getVendorForCategory($categoryCode),
                        'description' => $category->name . ' - ' . $monthDate->format('F Y'),
                        'expense_date' => $expenseDate,
                        'amount' => $baseAmount + rand(-1000, 1000),
                        'currency' => 'USD',
                        'payment_method' => 'bank_transfer',
                        'status' => 'approved',
                        'submitted_by' => 1,
                        'approved_by' => 1,
                        'approved_at' => $expenseDate->copy()->addDays(rand(1, 5)),
                    ]);
                }
            }
            
            // Payroll expenses
            if (isset($categories['PAYROLL'])) {
                $category = $categories['PAYROLL'];
                Expense::create([
                    'expense_number' => 'EXP-' . $monthDate->format('Y') . '-' . str_pad(Expense::count() + 1, 4, '0', STR_PAD_LEFT),
                    'expense_category_id' => $category->id,
                    'vendor_name' => 'Internal Payroll',
                    'description' => 'Monthly payroll for ' . $monthDate->format('F Y'),
                    'expense_date' => $monthDate->copy()->addDays(rand(25, 28)),
                    'amount' => 95000 + rand(-5000, 5000),
                    'currency' => 'USD',
                    'payment_method' => 'bank_transfer',
                    'status' => 'approved',
                    'submitted_by' => 1,
                    'approved_by' => 1,
                    'approved_at' => $monthDate->copy()->addDays(rand(25, 28)),
                ]);
            }
            
            // Investing expenses (equipment, furniture)
            $investingCategories = ['MAINTENANCE']; // Use maintenance for capital expenditures
            foreach ($investingCategories as $categoryCode) {
                if (!isset($categories[$categoryCode])) continue;
                
                $category = $categories[$categoryCode];
                
                // Create 1-2 investing expenses per quarter
                if ($monthsAgo % 3 === 0) {
                    for ($i = 0; $i < rand(1, 2); $i++) {
                        $expenseDate = $monthDate->copy()->addDays(rand(1, 28));
                        
                        Expense::create([
                            'expense_number' => 'EXP-' . $monthDate->format('Y') . '-' . str_pad(Expense::count() + 1, 4, '0', STR_PAD_LEFT),
                            'expense_category_id' => $category->id,
                            'vendor_name' => 'Equipment Supplier',
                            'description' => 'Capital equipment purchase - ' . $monthDate->format('F Y'),
                            'expense_date' => $expenseDate,
                            'amount' => rand(5000, 25000),
                            'currency' => 'USD',
                            'payment_method' => 'bank_transfer',
                            'status' => 'approved',
                            'submitted_by' => 1,
                            'approved_by' => 1,
                            'approved_at' => $expenseDate->copy()->addDays(rand(1, 5)),
                        ]);
                    }
                }
            }
            
            // Financing expenses (interest, loans)
            if (isset($categories['INTEREST_EXPENSE'])) {
                $category = $categories['INTEREST_EXPENSE'];
                
                // Monthly interest payment
                Expense::create([
                    'expense_number' => 'EXP-' . $monthDate->format('Y') . '-' . str_pad(Expense::count() + 1, 4, '0', STR_PAD_LEFT),
                    'expense_category_id' => $category->id,
                    'vendor_name' => 'Bank of Commerce',
                    'description' => 'Monthly loan interest payment - ' . $monthDate->format('F Y'),
                    'expense_date' => $monthDate->copy()->addDays(rand(1, 28)),
                    'amount' => 7000 + rand(-500, 500),
                    'currency' => 'USD',
                    'payment_method' => 'bank_transfer',
                    'status' => 'approved',
                    'submitted_by' => 1,
                    'approved_by' => 1,
                    'approved_at' => $monthDate->copy()->addDays(rand(1, 5)),
                ]);
            }
            
            // Depreciation expenses
            if (isset($categories['DEPRECIATION'])) {
                $category = $categories['DEPRECIATION'];
                
                Expense::create([
                    'expense_number' => 'EXP-' . $monthDate->format('Y') . '-' . str_pad(Expense::count() + 1, 4, '0', STR_PAD_LEFT),
                    'expense_category_id' => $category->id,
                    'vendor_name' => 'Internal Accounting',
                    'description' => 'Monthly depreciation - ' . $monthDate->format('F Y'),
                    'expense_date' => $monthDate->copy()->addDays(rand(1, 28)),
                    'amount' => 12000 + rand(-500, 500),
                    'currency' => 'USD',
                    'payment_method' => 'bank_transfer',
                    'status' => 'approved',
                    'submitted_by' => 1,
                    'approved_by' => 1,
                    'approved_at' => $monthDate->copy()->addDays(rand(1, 5)),
                ]);
            }
        }
        
        $this->command->info('Created expenses: ' . Expense::count());
    }
    
    private function createSampleProducts()
    {
        $products = [];
        
        // Food items
        $foodItems = [
            ['Burger', 12.99, 6.50],
            ['Pizza', 15.99, 8.00],
            ['Salad', 8.99, 4.50],
            ['Sandwich', 10.99, 5.50],
            ['Pasta', 14.99, 7.50],
        ];
        
        // Beverage items
        $beverageItems = [
            ['Coffee', 3.99, 1.50],
            ['Tea', 2.99, 1.00],
            ['Juice', 4.99, 2.50],
            ['Soda', 2.99, 1.20],
            ['Water', 1.99, 0.80],
        ];
        
        // Other items
        $otherItems = [
            ['Ice Cream', 4.99, 2.00],
            ['Cake Slice', 5.99, 2.50],
            ['Chocolate Bar', 2.99, 1.20],
            ['Chips', 3.99, 1.80],
            ['Candy', 1.99, 0.90],
        ];
        
        $allItems = array_merge($foodItems, $beverageItems, $otherItems);
        
        foreach ($allItems as [$name, $sellingPrice, $costPrice]) {
            $product = Product::firstOrCreate([
                'name' => $name
            ], [
                'product_category_id' => 1, // Assuming category 1 exists
                'sku' => 'PROD-' . strtoupper(str_replace(' ', '', $name)),
                'description' => 'Sample product: ' . $name,
                'cost_price' => $costPrice,
                'selling_price' => $sellingPrice,
                'stock_quantity' => 1000,
                'reorder_level' => 100,
                'is_active' => true,
            ]);
            $products[] = $product;
        }
        
        return $products;
    }
    
    private function getBaseAmountForCategory($categoryCode)
    {
        $amounts = [
            'FOOD_COSTS' => 35000,
            'ROOM_SUPPLIES' => 25000,
            'LAUNDRY' => 15000,
            'UTILITIES' => 45000,
            'ADMIN' => 18000,
            'MAINTENANCE' => 15000,
            'MARKETING' => 25000,
            'INSURANCE' => 12000,
            'PROFESSIONAL' => 8000,
        ];
        
        return $amounts[$categoryCode] ?? 10000;
    }
    
    private function getVendorForCategory($categoryCode)
    {
        $vendors = [
            'FOOD_COSTS' => 'Food Suppliers Inc',
            'ROOM_SUPPLIES' => 'Hotel Supplies Co',
            'LAUNDRY' => 'Clean Laundry Services',
            'UTILITIES' => 'City Utilities',
            'ADMIN' => 'Office Supplies Store',
            'MAINTENANCE' => 'Maintenance Services LLC',
            'MARKETING' => 'Digital Marketing Agency',
            'INSURANCE' => 'Insurance Company',
            'PROFESSIONAL' => 'Legal & Accounting Firm',
        ];
        
        return $vendors[$categoryCode] ?? 'General Vendor';
    }
}
