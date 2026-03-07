<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductCategory;
use App\Models\Product;

class POSSeeder extends Seeder
{
    public function run()
    {
        // Create categories
        $beverages = ProductCategory::create([
            'name' => 'Beverages',
            'description' => 'Drinks and beverages',
            'color' => '#3B82F6',
            'is_active' => true
        ]);

        $food = ProductCategory::create([
            'name' => 'Food',
            'description' => 'Food items and meals',
            'color' => '#EF4444',
            'is_active' => true
        ]);

        $snacks = ProductCategory::create([
            'name' => 'Snacks',
            'description' => 'Light snacks and appetizers',
            'color' => '#F59E0B',
            'is_active' => true
        ]);

        $alcohol = ProductCategory::create([
            'name' => 'Alcohol',
            'description' => 'Alcoholic beverages',
            'color' => '#8B5CF6',
            'is_active' => true
        ]);

        // Create products
        $products = [
            // Beverages
            ['name' => 'Coca Cola', 'code' => 'BEV001', 'category_id' => $beverages->id, 'price' => 2.50, 'cost_price' => 1.20, 'stock_quantity' => 100, 'tax_rate' => 8.5],
            ['name' => 'Orange Juice', 'code' => 'BEV002', 'category_id' => $beverages->id, 'price' => 3.00, 'cost_price' => 1.50, 'stock_quantity' => 50, 'tax_rate' => 8.5],
            ['name' => 'Coffee', 'code' => 'BEV003', 'category_id' => $beverages->id, 'price' => 2.00, 'cost_price' => 0.80, 'stock_quantity' => 200, 'tax_rate' => 8.5],
            ['name' => 'Tea', 'code' => 'BEV004', 'category_id' => $beverages->id, 'price' => 1.50, 'cost_price' => 0.60, 'stock_quantity' => 150, 'tax_rate' => 8.5],
            
            // Food
            ['name' => 'Burger', 'code' => 'FOOD001', 'category_id' => $food->id, 'price' => 12.00, 'cost_price' => 6.00, 'stock_quantity' => 30, 'tax_rate' => 8.5],
            ['name' => 'Pizza Slice', 'code' => 'FOOD002', 'category_id' => $food->id, 'price' => 8.50, 'cost_price' => 4.00, 'stock_quantity' => 25, 'tax_rate' => 8.5],
            ['name' => 'Sandwich', 'code' => 'FOOD003', 'category_id' => $food->id, 'price' => 7.00, 'cost_price' => 3.50, 'stock_quantity' => 40, 'tax_rate' => 8.5],
            ['name' => 'Salad', 'code' => 'FOOD004', 'category_id' => $food->id, 'price' => 9.00, 'cost_price' => 4.50, 'stock_quantity' => 20, 'tax_rate' => 8.5],
            
            // Snacks
            ['name' => 'Chips', 'code' => 'SNACK001', 'category_id' => $snacks->id, 'price' => 3.50, 'cost_price' => 1.75, 'stock_quantity' => 80, 'tax_rate' => 8.5],
            ['name' => 'Nuts', 'code' => 'SNACK002', 'category_id' => $snacks->id, 'price' => 4.00, 'cost_price' => 2.00, 'stock_quantity' => 60, 'tax_rate' => 8.5],
            ['name' => 'Cookies', 'code' => 'SNACK003', 'category_id' => $snacks->id, 'price' => 2.50, 'cost_price' => 1.25, 'stock_quantity' => 70, 'tax_rate' => 8.5],
            
            // Alcohol
            ['name' => 'Beer', 'code' => 'ALC001', 'category_id' => $alcohol->id, 'price' => 5.00, 'cost_price' => 2.50, 'stock_quantity' => 120, 'tax_rate' => 15.0],
            ['name' => 'Wine Glass', 'code' => 'ALC002', 'category_id' => $alcohol->id, 'price' => 8.00, 'cost_price' => 4.00, 'stock_quantity' => 50, 'tax_rate' => 15.0],
            ['name' => 'Whiskey Shot', 'code' => 'ALC003', 'category_id' => $alcohol->id, 'price' => 6.00, 'cost_price' => 3.00, 'stock_quantity' => 80, 'tax_rate' => 15.0],
        ];

        foreach ($products as $productData) {
            Product::create(array_merge($productData, [
                'description' => 'Sample product for POS system',
                'min_stock_level' => 10,
                'unit' => 'pcs',
                'is_active' => true,
                'is_service' => false
            ]));
        }
    }
}