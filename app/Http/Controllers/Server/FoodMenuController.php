<?php

namespace App\Http\Controllers\Server;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class FoodMenuController extends Controller
{
    /**
     * Display the food menu.
     */
    public function index()
    {
        $user = Auth::user()->load('roles');

        // Get food products
        $foodItems = Product::where('is_active', true)
            ->whereIn('category_id', [
                DB::table('product_categories')->where('name', 'LIKE', '%food%')->value('id') ?? 0,
                DB::table('product_categories')->where('name', 'LIKE', '%restaurant%')->value('id') ?? 0,
                DB::table('product_categories')->where('name', 'LIKE', '%appetizer%')->value('id') ?? 0,
                DB::table('product_categories')->where('name', 'LIKE', '%dessert%')->value('id') ?? 0,
                DB::table('product_categories')->where('name', 'LIKE', '%main%')->value('id') ?? 0,
            ])
            ->with('category')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'category' => $item->category?->name ?? 'Uncategorized',
                    'price' => $item->price,
                    'cost_price' => $item->cost_price,
                    'stock_quantity' => $item->stock_quantity,
                    'min_stock_level' => $item->min_stock_level,
                    'unit' => $item->unit,
                    'emoji' => $item->emoji,
                    'description' => $item->description,
                    'margin_percentage' => $item->margin_percentage ?? (($item->price - $item->cost_price) / $item->price * 100),
                ];
            });

        return Inertia::render('Server/Food/Index', [
            'user' => $user,
            'foodItems' => $foodItems,
        ]);
    }
}
