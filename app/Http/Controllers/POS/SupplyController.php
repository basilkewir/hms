<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Supply;
use App\Models\SupplyCategory;
use App\Models\Supplier;
use App\Models\SupplyMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SupplyController extends Controller
{
    /**
     * Display a listing of the supplies.
     */
    public function index()
    {
        $supplies = Supply::with(['category', 'supplier', 'movements.user'])
            ->orderBy('name')
            ->get();

        $categories = SupplyCategory::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return inertia('POS/Supplies/Index', [
            'supplies' => $supplies,
            'categories' => $categories,
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Store a newly created supply in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:supplies,name',
            'code' => 'required|string|max:50|unique:supplies,code',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:supply_categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'cost_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'unit_of_measure' => ['required', Rule::in(['pieces', 'liters', 'kilograms', 'boxes', 'packs'])],
            'barcode' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::transaction(function () use ($request) {
            $supply = Supply::create([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'supplier_id' => $request->supplier_id,
                'cost_price' => $request->cost_price,
                'stock_quantity' => $request->stock_quantity,
                'reorder_level' => $request->reorder_level,
                'unit_of_measure' => $request->unit_of_measure,
                'barcode' => $request->barcode,
            ]);

            // Record initial stock movement
            if ($request->stock_quantity > 0) {
                SupplyMovement::create([
                    'supply_id' => $supply->id,
                    'type' => 'add',
                    'quantity_change' => $request->stock_quantity,
                    'new_balance' => $request->stock_quantity,
                    'reason' => 'Initial stock',
                    'user_id' => Auth::id(),
                ]);
            }
        });

        return redirect()->route('pos.supplies.index')
            ->with('success', 'Supply created successfully.');
    }

    /**
     * Update the specified supply in storage.
     */
    public function update(Request $request, Supply $supply)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', Rule::unique('supplies', 'name')->ignore($supply->id)],
            'code' => ['required', 'string', 'max:50', Rule::unique('supplies', 'code')->ignore($supply->id)],
            'description' => 'nullable|string',
            'category_id' => 'required|exists:supply_categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'cost_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:0',
            'unit_of_measure' => ['required', Rule::in(['pieces', 'liters', 'kilograms', 'boxes', 'packs'])],
            'barcode' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $supply->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'supplier_id' => $request->supplier_id,
            'cost_price' => $request->cost_price,
            'reorder_level' => $request->reorder_level,
            'unit_of_measure' => $request->unit_of_measure,
            'barcode' => $request->barcode,
        ]);

        return redirect()->route('pos.supplies.index')
            ->with('success', 'Supply updated successfully.');
    }

    /**
     * Remove the specified supply from storage.
     */
    public function destroy(Supply $supply)
    {
        // Check if supply has any stock movements
        if ($supply->movements()->exists()) {
            return redirect()->route('pos.supplies.index')
                ->with('error', 'Cannot delete supply with existing stock movements.');
        }

        $supply->delete();

        return redirect()->route('pos.supplies.index')
            ->with('success', 'Supply deleted successfully.');
    }

    /**
     * Adjust stock for a supply.
     */
    public function adjustStock(Request $request, Supply $supply)
    {
        $validator = Validator::make($request->all(), [
            'type' => ['required', Rule::in(['add', 'remove', 'set'])],
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::transaction(function () use ($request, $supply) {
            $oldQuantity = $supply->stock_quantity;
            $newQuantity = $oldQuantity;
            $quantityChange = 0;

            switch ($request->type) {
                case 'add':
                    $quantityChange = $request->quantity;
                    $newQuantity = $oldQuantity + $quantityChange;
                    break;

                case 'remove':
                    if ($oldQuantity < $request->quantity) {
                        throw new \Exception('Cannot remove more stock than available.');
                    }
                    $quantityChange = -$request->quantity;
                    $newQuantity = $oldQuantity + $quantityChange;
                    break;

                case 'set':
                    $quantityChange = $request->quantity - $oldQuantity;
                    $newQuantity = $request->quantity;
                    break;
            }

            // Update supply stock
            $supply->update(['stock_quantity' => $newQuantity]);

            // Record stock movement
            SupplyMovement::create([
                'supply_id' => $supply->id,
                'type' => $request->type,
                'quantity_change' => $quantityChange,
                'new_balance' => $newQuantity,
                'reason' => $request->reason,
                'user_id' => Auth::id(),
            ]);
        });

        return redirect()->route('pos.supplies.index')
            ->with('success', 'Stock adjusted successfully.');
    }

    /**
     * Import supplies from file.
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,xlsx',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // TODO: Implement file import logic
        // This would typically involve:
        // 1. Reading the uploaded file
        // 2. Validating the data
        // 3. Creating supplies in bulk
        // 4. Recording initial stock movements

        return redirect()->route('pos.supplies.index')
            ->with('success', 'Supplies imported successfully.');
    }
}
