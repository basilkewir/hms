<?php

namespace App\Http\Controllers\Bartender;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Sale;
use Carbon\Carbon;
use App\Models\Setting;

class SalesController extends Controller
{
    /**
     * Display sales reports.
     */
    public function index()
    {
        $user = Auth::user();

        // Get all completed sales
        $sales = Sale::where('payment_status', 'completed')
            ->with(['user', 'customer'])
            ->orderBy('sale_date', 'desc')
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'sale_number' => $sale->sale_number,
                    'customer_name' => $sale->customer_name ?? $sale->customer?->name ?? 'Walk-in',
                    'subtotal' => (float) ($sale->subtotal ?? 0),
                    'tax_amount' => (float) ($sale->tax_amount ?? 0),
                    'discount_amount' => (float) ($sale->discount_amount ?? 0),
                    'total_amount' => (float) ($sale->total_amount ?? 0),
                    'payment_method' => $sale->payment_method,
                    'payment_status' => $sale->payment_status,
                    'sale_date' => $sale->sale_date,
                ];
            });

        // Get currency settings from database
        $currency = Setting::where('key', 'currency')->value('value') ?? 'USD';
        $currencyPosition = Setting::where('key', 'currency_position')->value('value') ?? 'prefix';

        return Inertia::render('Bartender/Sales/Index', [
            'user' => $user,
            'sales' => $sales,
            'currency' => $currency,
            'currencyPosition' => $currencyPosition,
        ]);
    }
}
