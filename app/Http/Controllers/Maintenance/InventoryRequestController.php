<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\InventoryRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = InventoryRequest::where('department', 'maintenance')
            ->where('requested_by', $request->user()->id)
            ->orderByDesc('requested_at')
            ->limit(25)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'itemName' => $item->item_name,
                    'quantity' => $item->quantity,
                    'priority' => $item->priority,
                    'status' => $item->status,
                    'date' => $item->requested_at?->format('Y-m-d H:i:s'),
                ];
            });

        return Inertia::render('Maintenance/Inventory/Request', [
            'user' => $request->user()->load('roles'),
            'myRequests' => $requests,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'priority' => 'required|in:low,medium,high,urgent',
            'work_order' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        InventoryRequest::create([
            'department' => 'maintenance',
            'category' => $validated['category'],
            'item_name' => $validated['item_name'],
            'quantity' => $validated['quantity'],
            'priority' => $validated['priority'],
            'status' => 'pending',
            'work_order' => $validated['work_order'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'requested_by' => $request->user()->id,
            'requested_at' => now(),
        ]);

        return redirect()->route('maintenance.inventory.request')
            ->with('success', 'Inventory request submitted successfully.');
    }
}
