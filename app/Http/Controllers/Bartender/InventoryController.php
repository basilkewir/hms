<?php

namespace App\Http\Controllers\Bartender;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Setting;

class InventoryController extends Controller
{
    /**
     * Display the bar inventory.
     */
    public function index()
    {
        $user = Auth::user();

        // Get all active products (bar inventory)
        $inventory = Product::where('is_active', true)
            ->with('category')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'category' => $product->category?->name ?? 'Uncategorized',
                    'price' => $product->price,
                    'cost_price' => $product->cost_price,
                    'stock_quantity' => $product->stock_quantity,
                    'min_stock_level' => $product->min_stock_level,
                    'unit' => $product->unit,
                    'emoji' => $product->emoji,
                ];
            });

        // Get currency settings from database
        $currency = Setting::where('key', 'currency')->value('value') ?? 'USD';
        $currencyPosition = Setting::where('key', 'currency_position')->value('value') ?? 'prefix';

        return Inertia::render('Bartender/Inventory/Index', [
            'user' => $user,
            'inventory' => $inventory,
            'currency' => $currency,
            'currencyPosition' => $currencyPosition,
        ]);
    }
}
