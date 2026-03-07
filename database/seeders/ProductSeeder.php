<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductCategory;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing data
        Product::truncate();
        ProductCategory::truncate();
        
        // Re-enable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create Categories
        $categories = [
            [
                'name' => 'Drinks',
                'color' => '#38bdf8',
                'is_active' => true,
                'products' => [
                    ['name' => 'Cola Large', 'emoji' => '🥤', 'price' => 3.50, 'cost' => 1.20],
                    ['name' => 'Latte', 'emoji' => '☕', 'price' => 4.80, 'cost' => 2.20],
                    ['name' => 'Orange Juice', 'emoji' => '🍊', 'price' => 4.20, 'cost' => 1.80],
                    ['name' => 'Iced Tea', 'emoji' => '🧊', 'price' => 3.20, 'cost' => 1.00],
                    ['name' => 'Smoothie', 'emoji' => '🥤', 'price' => 5.50, 'cost' => 2.50],
                    ['name' => 'Water Bottle', 'emoji' => '💧', 'price' => 2.00, 'cost' => 0.50],
                    ['name' => 'Energy Drink', 'emoji' => '⚡', 'price' => 4.50, 'cost' => 2.00],
                    ['name' => 'Hot Chocolate', 'emoji' => '☕', 'price' => 4.00, 'cost' => 1.80]
                ]
            ],
            [
                'name' => 'Food',
                'color' => '#ef4444',
                'is_active' => true,
                'products' => [
                    ['name' => 'Cheeseburger', 'emoji' => '🍔', 'price' => 8.90, 'cost' => 4.50],
                    ['name' => 'Fries', 'emoji' => '🍟', 'price' => 4.20, 'cost' => 2.00],
                    ['name' => 'Margherita Pizza', 'emoji' => '🍕', 'price' => 12.90, 'cost' => 6.50],
                    ['name' => 'Chicken Wings', 'emoji' => '🍗', 'price' => 9.50, 'cost' => 4.80],
                    ['name' => 'Caesar Salad', 'emoji' => '🥗', 'price' => 7.80, 'cost' => 3.20],
                    ['name' => 'Fish & Chips', 'emoji' => '🐟', 'price' => 11.50, 'cost' => 5.80],
                    ['name' => 'Pasta', 'emoji' => '🍝', 'price' => 10.20, 'cost' => 4.50],
                    ['name' => 'Sandwich', 'emoji' => '🥪', 'price' => 6.50, 'cost' => 3.00],
                    ['name' => 'Tacos', 'emoji' => '🌮', 'price' => 8.20, 'cost' => 3.80],
                    ['name' => 'Soup', 'emoji' => '🍲', 'price' => 5.50, 'cost' => 2.20]
                ]
            ],
            [
                'name' => 'Desserts',
                'color' => '#f59e0b',
                'is_active' => true,
                'products' => [
                    ['name' => 'Cheesecake', 'emoji' => '🍰', 'price' => 6.50, 'cost' => 3.00],
                    ['name' => 'Ice Cream', 'emoji' => '🍦', 'price' => 4.80, 'cost' => 2.20],
                    ['name' => 'Chocolate Cake', 'emoji' => '🎂', 'price' => 7.20, 'cost' => 3.50],
                    ['name' => 'Apple Pie', 'emoji' => '🥧', 'price' => 5.90, 'cost' => 2.80],
                    ['name' => 'Cookies', 'emoji' => '🍪', 'price' => 3.50, 'cost' => 1.50],
                    ['name' => 'Donut', 'emoji' => '🍩', 'price' => 2.80, 'cost' => 1.20],
                    ['name' => 'Muffin', 'emoji' => '🧁', 'price' => 3.20, 'cost' => 1.40],
                    ['name' => 'Brownie', 'emoji' => '🍫', 'price' => 4.50, 'cost' => 2.00]
                ]
            ],
            [
                'name' => 'Promo',
                'color' => '#10b981',
                'is_active' => true,
                'products' => [
                    ['name' => 'Combo Meal', 'emoji' => '🍽️', 'price' => 12.99, 'cost' => 6.50],
                    ['name' => 'Happy Hour Special', 'emoji' => '🎉', 'price' => 8.99, 'cost' => 4.00],
                    ['name' => 'Family Pack', 'emoji' => '👨‍👩‍👧‍👦', 'price' => 24.99, 'cost' => 12.00],
                    ['name' => 'Student Discount', 'emoji' => '🎓', 'price' => 6.99, 'cost' => 3.50],
                    ['name' => 'Weekend Special', 'emoji' => '🌟', 'price' => 15.99, 'cost' => 8.00]
                ]
            ],
            [
                'name' => 'Breakfast',
                'color' => '#8b5cf6',
                'is_active' => true,
                'products' => [
                    ['name' => 'Pancakes', 'emoji' => '🥞', 'price' => 7.50, 'cost' => 3.20],
                    ['name' => 'Eggs Benedict', 'emoji' => '🍳', 'price' => 9.80, 'cost' => 4.50],
                    ['name' => 'French Toast', 'emoji' => '🍞', 'price' => 6.90, 'cost' => 3.00],
                    ['name' => 'Omelette', 'emoji' => '🥚', 'price' => 8.20, 'cost' => 3.80],
                    ['name' => 'Bagel', 'emoji' => '🥯', 'price' => 4.50, 'cost' => 2.00],
                    ['name' => 'Cereal', 'emoji' => '🥣', 'price' => 5.20, 'cost' => 2.50]
                ]
            ]
        ];

        foreach ($categories as $categoryData) {
            $category = ProductCategory::create([
                'name' => $categoryData['name'],
                'color' => $categoryData['color'],
                'is_active' => $categoryData['is_active']
            ]);

            foreach ($categoryData['products'] as $index => $productData) {
                Product::create([
                    'name' => $productData['name'],
                    'code' => strtoupper(substr($categoryData['name'], 0, 3)) . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'category_id' => $category->id,
                    'description' => 'Delicious ' . $productData['name'],
                    'price' => $productData['price'],
                    'cost_price' => $productData['cost'],
                    'stock_quantity' => rand(20, 100),
                    'min_stock_level' => rand(5, 15),
                    'unit' => 'piece',
                    'is_active' => true,
                    'is_service' => false,
                    'tax_rate' => 7.5,
                    'emoji' => $productData['emoji'] ?? '🍽️'
                ]);
            }
        }
    }
}