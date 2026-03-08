<?php

namespace App\Http\Controllers\Bartender;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;

class DrinksController extends Controller
{
    /**
     * Display the drinks menu.
     */
    public function index()
    {
        $user = Auth::user()->load('roles');

        // Get drinks (beverages, cocktails, etc.)
        $drinks = Product::where('is_active', true)
            ->whereIn('category_id', [
                DB::table('product_categories')->where('name', 'LIKE', '%drink%')->value('id') ?? 0,
                DB::table('product_categories')->where('name', 'LIKE', '%beverage%')->value('id') ?? 0,
                DB::table('product_categories')->where('name', 'LIKE', '%cocktail%')->value('id') ?? 0,
                DB::table('product_categories')->where('name', 'LIKE', '%alcohol%')->value('id') ?? 0,
            ])
            ->with('category')
            ->get()
            ->map(function ($drink) {
                return [
                    'id' => $drink->id,
                    'name' => $drink->name,
                    'code' => $drink->code,
                    'category' => $drink->category?->name ?? 'Uncategorized',
                    'price' => $drink->price,
                    'cost_price' => $drink->cost_price,
                    'stock_quantity' => $drink->stock_quantity,
                    'min_stock_level' => $drink->min_stock_level,
                    'unit' => $drink->unit,
                    'emoji' => $drink->emoji,
                    'margin_percentage' => $drink->margin_percentage ?? (($drink->price - $drink->cost_price) / $drink->price * 100),
                ];
            });

        // Get currency settings from database
        $currency = Setting::where('key', 'currency')->value('value') ?? 'USD';
        $currencyPosition = Setting::where('key', 'currency_position')->value('value') ?? 'prefix';

        return Inertia::render('Bartender/Drinks/Index', [
            'user' => $user,
            'drinks' => $drinks,
            'currency' => $currency,
            'currencyPosition' => $currencyPosition,
        ]);
    }
}
