<?php

namespace Hubiko\EcommerceHub\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Hubiko\EcommerceHub\Models\EcommerceProduct;
use Hubiko\EcommerceHub\Models\EcommerceStore;
use Hubiko\EcommerceHub\Models\EcommerceCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EcommerceProductController extends Controller
{
    public function index()
    {
        $workspaceId = getActiveWorkSpace();
        $products = EcommerceProduct::whereHas('store', function($query) use ($workspaceId) {
            $query->where('workspace_id', $workspaceId);
        })->with(['store', 'category'])->paginate(10);
        
        return view('ecommercehub::products.index', compact('products'));
    }

    public function create()
    {
        $workspaceId = getActiveWorkSpace();
        $stores = EcommerceStore::where('workspace_id', $workspaceId)->active()->get();
        $categories = EcommerceCategory::whereHas('store', function($query) use ($workspaceId) {
            $query->where('workspace_id', $workspaceId);
        })->active()->get();
        
        return view('ecommercehub::products.create', compact('stores', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:ecommerce_products,sku',
            'price' => 'required|numeric|min:0',
            'store_id' => 'required|exists:ecommerce_stores,id',
            'description' => 'nullable|string',
            'stock_quantity' => 'required|integer|min:0'
        ]);

        EcommerceProduct::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'short_description' => $request->short_description,
            'sku' => $request->sku,
            'price' => $request->price,
            'sale_price' => $request->sale_price,
            'cost_price' => $request->cost_price,
            'stock_quantity' => $request->stock_quantity,
            'manage_stock' => $request->has('manage_stock'),
            'stock_status' => $request->stock_status ?? 'in_stock',
            'weight' => $request->weight,
            'category_id' => $request->category_id,
            'tags' => $request->tags ? explode(',', $request->tags) : [],
            'is_featured' => $request->has('is_featured'),
            'is_digital' => $request->has('is_digital'),
            'status' => $request->status ?? 'active',
            'visibility' => $request->visibility ?? 'public',
            'seo_title' => $request->seo_title,
            'seo_description' => $request->seo_description,
            'seo_keywords' => $request->seo_keywords,
            'store_id' => $request->store_id,
            'created_by' => Auth::id()
        ]);

        return redirect()->route('ecommerce.products.index')->with('success', 'Product created successfully');
    }

    public function show(EcommerceProduct $product)
    {
        $product->load(['store', 'category', 'orderItems']);
        return view('ecommercehub::products.show', compact('product'));
    }

    public function edit(EcommerceProduct $product)
    {
        $workspaceId = getActiveWorkSpace();
        $stores = EcommerceStore::where('workspace_id', $workspaceId)->active()->get();
        $categories = EcommerceCategory::whereHas('store', function($query) use ($workspaceId) {
            $query->where('workspace_id', $workspaceId);
        })->active()->get();
        
        return view('ecommercehub::products.edit', compact('product', 'stores', 'categories'));
    }

    public function update(Request $request, EcommerceProduct $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:ecommerce_products,sku,' . $product->id,
            'price' => 'required|numeric|min:0',
            'store_id' => 'required|exists:ecommerce_stores,id',
            'description' => 'nullable|string',
            'stock_quantity' => 'required|integer|min:0'
        ]);

        $product->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'short_description' => $request->short_description,
            'sku' => $request->sku,
            'price' => $request->price,
            'sale_price' => $request->sale_price,
            'cost_price' => $request->cost_price,
            'stock_quantity' => $request->stock_quantity,
            'manage_stock' => $request->has('manage_stock'),
            'stock_status' => $request->stock_status ?? 'in_stock',
            'weight' => $request->weight,
            'category_id' => $request->category_id,
            'tags' => $request->tags ? explode(',', $request->tags) : [],
            'is_featured' => $request->has('is_featured'),
            'is_digital' => $request->has('is_digital'),
            'status' => $request->status ?? 'active',
            'visibility' => $request->visibility ?? 'public',
            'seo_title' => $request->seo_title,
            'seo_description' => $request->seo_description,
            'seo_keywords' => $request->seo_keywords,
            'store_id' => $request->store_id
        ]);

        return redirect()->route('ecommerce.products.index')->with('success', 'Product updated successfully');
    }

    public function destroy(EcommerceProduct $product)
    {
        $product->delete();
        return redirect()->route('ecommerce.products.index')->with('success', 'Product deleted successfully');
    }

    public function toggleStatus($id)
    {
        return response()->json(['success' => true]);
    }

    public function bulkAction(Request $request)
    {
        return response()->json(['success' => true]);
    }
}
