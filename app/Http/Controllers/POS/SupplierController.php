<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('manage_suppliers');

        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $query = Supplier::with(['purchaseOrders', 'payments'])
            ->withCount(['purchaseOrders', 'payments'])
            ->withSum('payments', 'amount');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $suppliers = $query->latest()->paginate(20);

        return Inertia::render('POS/Suppliers/Index', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role),
            'suppliers' => $suppliers,
            'filters' => $request->only(['search', 'status'])
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage_suppliers');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $supplier = Supplier::create($validated);

        return redirect()->route('pos.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $this->authorize('manage_suppliers');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $supplier->update($validated);

        return redirect()->route('pos.suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $this->authorize('manage_suppliers');

        // Check if supplier has purchase orders
        if ($supplier->purchaseOrders()->count() > 0) {
            return redirect()->route('pos.suppliers.index')
                ->with('error', 'Cannot delete supplier with existing purchase orders.');
        }

        $supplier->delete();

        return redirect()->route('pos.suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    public function show(Supplier $supplier)
    {
        $this->authorize('manage_suppliers');

        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $supplier->load([
            'purchaseOrders' => function($query) {
                $query->with(['supplier', 'user'])->latest()->limit(10);
            },
            'payments' => function($query) {
                $query->with(['purchaseOrder', 'user'])->latest()->limit(10);
            }
        ]);

        $totalPurchases = $supplier->purchaseOrders()->sum('total_amount');
        $totalPaid = $supplier->payments()->sum('amount');
        $totalPending = max(0, $totalPurchases - $totalPaid);

        return Inertia::render('POS/Suppliers/Show', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role),
            'supplier' => $supplier,
            'totalPurchases' => $totalPurchases,
            'totalPaid' => $totalPaid,
            'totalPending' => $totalPending
        ]);
    }

    public function payments(Request $request, Supplier $supplier)
    {
        $this->authorize('manage_suppliers');

        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $payments = $supplier->payments()
            ->with(['purchaseOrder', 'user'])
            ->latest()
            ->paginate(20);

        $purchaseOrders = PurchaseOrder::where('supplier_id', $supplier->id)
            ->where('remaining_amount', '>', 0)
            ->get();

        return Inertia::render('POS/Suppliers/Payments', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role),
            'supplier' => $supplier,
            'payments' => $payments,
            'purchaseOrders' => $purchaseOrders
        ]);
    }

    public function storePayment(Request $request, Supplier $supplier)
    {
        $this->authorize('manage_suppliers');

        $validated = $request->validate([
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'payment_type' => 'required|in:partial,full',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,credit_card',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $payment = SupplierPayment::create([
                'supplier_id' => $supplier->id,
                'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                'payment_number' => SupplierPayment::generatePaymentNumber(),
                'payment_type' => $validated['payment_type'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'user_id' => auth()->id()
            ]);

            // Update purchase order payment status if linked
            if ($validated['purchase_order_id']) {
                $purchaseOrder = PurchaseOrder::find($validated['purchase_order_id']);
                $purchaseOrder->updatePaymentStatus();
            }

            // Update supplier balance
            $supplier->update([
                'current_balance' => max(0, $supplier->current_balance - $validated['amount'])
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }

    public function deletePayment(SupplierPayment $payment)
    {
        $this->authorize('manage_suppliers');

        DB::beginTransaction();

        try {
            $supplier = $payment->supplier;
            $purchaseOrder = $payment->purchaseOrder;

            $payment->delete();

            // Update purchase order payment status if linked
            if ($purchaseOrder) {
                $purchaseOrder->updatePaymentStatus();
            }

            // Update supplier balance
            $supplier->update([
                'current_balance' => $supplier->current_balance + $payment->amount
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Payment deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to delete payment: ' . $e->getMessage());
        }
    }
}
