<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\InventoryRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function parts(Request $request)
    {
        $items = InventoryRequest::where('department', 'maintenance')
            ->where('category', '!=', 'tools')
            ->orderByDesc('requested_at')
            ->limit(50)
            ->get()
            ->map(fn($item) => $this->mapRequest($item));

        return Inertia::render('Maintenance/Inventory/Parts', [
            'user' => $request->user()->load('roles'),
            'items' => $items,
        ]);
    }

    public function tools(Request $request)
    {
        $items = InventoryRequest::where('department', 'maintenance')
            ->where('category', 'tools')
            ->orderByDesc('requested_at')
            ->limit(50)
            ->get()
            ->map(fn($item) => $this->mapRequest($item));

        return Inertia::render('Maintenance/Inventory/Tools', [
            'user' => $request->user()->load('roles'),
            'items' => $items,
        ]);
    }

    public function vendors(Request $request)
    {
        $vendors = Supplier::orderBy('name')->get()->map(function ($supplier) {
            return [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'contact_person' => $supplier->contact_person,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'is_active' => (bool) $supplier->is_active,
            ];
        });

        return Inertia::render('Maintenance/Inventory/Vendors', [
            'user' => $request->user()->load('roles'),
            'vendors' => $vendors,
        ]);
    }

    private function mapRequest(InventoryRequest $item): array
    {
        return [
            'id' => $item->id,
            'item_name' => $item->item_name,
            'category' => $item->category,
            'quantity' => $item->quantity,
            'priority' => $item->priority,
            'status' => $item->status,
            'requested_at' => $item->requested_at?->format('Y-m-d H:i:s'),
        ];
    }
}
