<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;
use App\Models\Product;
use Carbon\Carbon;

class SimpleCashFlowSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Seeding additional cash flow data...');
        
        $this->createPOSSales();
        
        $this->command->info('Additional cash flow data seeded successfully!');
    }
    
    private function createPOSSales()
    {
        // Get existing products or create basic ones
        $products = Product::take(15)->get();
        
        if ($products->isEmpty()) {
            $this->command->info('Creating basic products first...');
            $this->createBasicProducts();
            $products = Product::take(15)->get();
        }
        
        // Create POS sales for the past 6 months
        for ($monthsAgo = 6; $monthsAgo >= 0; $monthsAgo--) {
            $monthDate = Carbon::now()->subMonths($monthsAgo)->startOfMonth();
            $daysInMonth = $monthDate->daysInMonth;
            
            for ($day = 1; $day <= min($daysInMonth, 28); $day++) {
                $saleDate = $monthDate->copy()->addDays($day - 1);
                
                // Create 5-12 sales per day
                $salesPerDay = rand(5, 12);
                for ($i = 0; $i < $salesPerDay; $i++) {
                    $saleTime = $saleDate->copy()->addHours(rand(7, 22))->addMinutes(rand(0, 59));
                    
                    // Create sale items
                    $totalAmount = 0;
                    $totalCost = 0;
                    $itemsCount = rand(1, 4);
                    
                    for ($j = 0; $j < $itemsCount; $j++) {
                        $product = $products->random();
                        $quantity = rand(1, 3);
                        $unitPrice = $product->selling_price ?: rand(5, 25);
                        $unitCost = $product->cost_price ?: ($unitPrice * 0.6);
                        $totalItemAmount = $quantity * $unitPrice;
                        
                        $totalAmount += $totalItemAmount;
                        $totalCost += $quantity * $unitCost;
                    }
                    
                    // Create sale
                    $sale = Sale::create([
                        'sale_number' => 'POS-' . date('Y') . '-' . str_pad(Sale::count() + 1, 4, '0', STR_PAD_LEFT),
                        'user_id' => 1,
                        'sale_date' => $saleTime,
                        'subtotal' => $totalAmount,
                        'tax_amount' => $totalAmount * 0.1,
                        'discount_amount' => $totalAmount * (rand(0, 5) * 0.01),
                        'total_amount' => ($totalAmount * 1.1) * (1 - (rand(0, 5) * 0.01)),
                        'payment_method' => ['cash', 'card', 'mobile'][array_rand(['cash', 'card', 'mobile'])],
                        'payment_status' => 'paid',
                        'customer_name' => 'Guest ' . rand(1, 100),
                        'is_walk_in' => true,
                        'is_charged_to_room' => false,
                    ]);
                    
                    // Create sale items
                    for ($j = 0; $j < $itemsCount; $j++) {
                        $product = $products->random();
                        $quantity = rand(1, 3);
                        $unitPrice = $product->selling_price ?: rand(5, 25);
                        $unitCost = $product->cost_price ?: ($unitPrice * 0.6);
                        
                        \App\Models\SaleItem::create([
                            'sale_id' => $sale->id,
                            'product_id' => $product->id,
                            'product_name' => $product->name ?: 'Product ' . $product->id,
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'unit_cost' => $unitCost,
                            'total_amount' => $quantity * $unitPrice,
                            'total_cost' => $quantity * $unitCost,
                        ]);
                    }
                }
            }
        }
        
        $this->command->info('Created POS sales: ' . Sale::count());
    }
    
    private function createBasicProducts()
    {
        $products = [
            ['Coffee', 3.99, 1.50],
            ['Tea', 2.99, 1.00],
            ['Juice', 4.99, 2.50],
            ['Soda', 2.99, 1.20],
            ['Water', 1.99, 0.80],
            ['Burger', 12.99, 6.50],
            ['Pizza', 15.99, 8.00],
            ['Salad', 8.99, 4.50],
            ['Sandwich', 10.99, 5.50],
            ['Pasta', 14.99, 7.50],
            ['Ice Cream', 4.99, 2.00],
            ['Cake Slice', 5.99, 2.50],
            ['Chocolate Bar', 2.99, 1.20],
            ['Chips', 3.99, 1.80],
            ['Candy', 1.99, 0.90],
        ];
        
        foreach ($products as [$name, $sellingPrice, $costPrice]) {
            Product::firstOrCreate([
                'name' => $name
            ], [
                'product_category_id' => 1,
                'sku' => 'PROD-' . strtoupper(str_replace(' ', '', $name)),
                'description' => 'Sample product: ' . $name,
                'cost_price' => $costPrice,
                'selling_price' => $sellingPrice,
                'stock_quantity' => 1000,
                'reorder_level' => 100,
                'is_active' => true,
            ]);
        }
    }
}
