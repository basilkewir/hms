<?php

namespace App\Http\Controllers\Server;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Sale;
use App\Models\Setting;

class SalesController extends Controller
{
    /**
     * Display sales reports for server.
     */
    public function index()
    {
        $user = Auth::user()->load('roles');

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
                    'subtotal' => (float)$sale->subtotal,
                    'tax_amount' => (float)$sale->tax_amount,
                    'discount_amount' => (float)$sale->discount_amount,
                    'total_amount' => (float)$sale->total_amount,
                    'payment_method' => $sale->payment_method,
                    'payment_status' => $sale->payment_status,
                    'sale_date' => $sale->sale_date,
                ];
            });

        // Get currency settings from database
        $currency = Setting::where('key', 'currency')->value('value') ?? 'USD';
        $currencyPosition = Setting::where('key', 'currency_position')->value('value') ?? 'prefix';

        return Inertia::render('Server/Sales/Index', [
            'user' => $user,
            'sales' => $sales,
            'currency' => $currency,
            'currencyPosition' => $currencyPosition,
        ]);
    }
}
