<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->get();
        $categories = ProductCategory::where('is_active', true)->get();

        return Inertia::render('Admin/POS/Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'user' => auth()->user()->load('roles')
        ]);
    }

    public function store(Request $request)
    {
        // Only allow admins and managers to create products
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin') && !$user->hasRole('manager')) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:products,code',
            'category_id' => 'nullable|exists:product_categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|in:pieces,kg,liters,meters,boxes,bottles',
            'barcode' => 'nullable|string|max:50|unique:products,barcode',
            'emoji' => 'nullable|string|max:2',
            'is_active' => 'boolean',
            'is_service' => 'boolean',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'nullable|exists:units,id'
        ]);

        // Ensure at least unit or unit_id is set
        if (empty($validated['unit']) && empty($validated['unit_id'])) {
            $validated['unit'] = 'pieces'; // Default fallback
        } elseif (empty($validated['unit']) && !empty($validated['unit_id'])) {
            // If unit_id is set but unit is not, keep unit as null
            $validated['unit'] = null;
        } elseif (!empty($validated['unit']) && empty($validated['unit_id'])) {
            // If unit is set but unit_id is not, keep unit_id as null
            $validated['unit_id'] = null;
        }

        // Auto-generate code if not provided
        if (empty($validated['code'])) {
            $validated['code'] = $this->generateProductCode($validated['category_id'] ?? null);
        }

        // Set defaults
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['is_service'] = $validated['is_service'] ?? false;
        $validated['tax_rate'] = $validated['tax_rate'] ?? 0;

        Product::create($validated);

        return redirect()->back()
            ->with('success', 'Product created successfully.');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:products,code,' . $product->id,
            'category_id' => 'nullable|exists:product_categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|in:pieces,kg,liters,meters,boxes,bottles',
            'barcode' => 'nullable|string|max:50|unique:products,barcode,' . $product->id,
            'emoji' => 'nullable|string|max:2',
            'is_active' => 'boolean',
            'is_service' => 'boolean',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'nullable|exists:units,id'
        ]);

        // Ensure at least unit or unit_id is set
        if (empty($validated['unit']) && empty($validated['unit_id'])) {
            $validated['unit'] = 'pieces'; // Default fallback
        } elseif (empty($validated['unit']) && !empty($validated['unit_id'])) {
            // If unit_id is set but unit is not, keep unit as null
            $validated['unit'] = null;
        } elseif (!empty($validated['unit']) && empty($validated['unit_id'])) {
            // If unit is set but unit_id is not, keep unit_id as null
            $validated['unit_id'] = null;
        }

        // Auto-generate code if not provided
        if (empty($validated['code'])) {
            $validated['code'] = $this->generateProductCode($validated['category_id'] ?? null);
        }

        $product->update($validated);

        return redirect()->back()
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->back()
            ->with('success', 'Product deleted successfully.');
    }

    public function destroyAll()
    {
        Product::query()->delete();

        return redirect()->back()
            ->with('success', 'All products deleted successfully.');
    }

    public function destroyBulk(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:products,id',
        ]);

        $count = Product::whereIn('id', $validated['ids'])->delete();

        return redirect()->back()
            ->with('success', $count . ' product(s) deleted successfully.');
    }

    private function generateProductCode($categoryId)
    {
        if ($categoryId) {
            $category = ProductCategory::find($categoryId);
            $prefix = strtoupper(substr($category->name ?? 'GEN', 0, 3));
            $count = Product::where('category_id', $categoryId)->count() + 1;
        } else {
            $prefix = 'GEN';
            $count = Product::whereNull('category_id')->count() + 1;
        }
        
        return $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}