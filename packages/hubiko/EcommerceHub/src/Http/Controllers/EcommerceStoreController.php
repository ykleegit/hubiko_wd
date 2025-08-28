<?php

namespace Hubiko\EcommerceHub\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Hubiko\EcommerceHub\Models\EcommerceStore;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EcommerceStoreController extends Controller
{
    public function index()
    {
        $workspaceId = getActiveWorkSpace();
        $stores = EcommerceStore::where('workspace_id', $workspaceId)
            ->with(['products', 'orders', 'customers'])
            ->paginate(10);
            
        return view('ecommercehub::stores.index', compact('stores'));
    }

    public function create()
    {
        return view('ecommercehub::stores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'currency' => 'required|string|size:3',
            'timezone' => 'required|string'
        ]);

        $store = EcommerceStore::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'website_url' => $request->website_url,
            'currency' => $request->currency,
            'timezone' => $request->timezone,
            'workspace_id' => getActiveWorkSpace(),
            'created_by' => Auth::id(),
            'settings' => $request->settings ?? []
        ]);

        return redirect()->route('ecommerce.stores.index')->with('success', 'Store created successfully');
    }

    public function show(EcommerceStore $store)
    {
        $store->load(['products', 'orders', 'customers']);
        return view('ecommercehub::stores.show', compact('store'));
    }

    public function edit(EcommerceStore $store)
    {
        return view('ecommercehub::stores.edit', compact('store'));
    }

    public function update(Request $request, EcommerceStore $store)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'currency' => 'required|string|size:3',
            'timezone' => 'required|string'
        ]);

        $store->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'website_url' => $request->website_url,
            'currency' => $request->currency,
            'timezone' => $request->timezone,
            'is_active' => $request->has('is_active'),
            'settings' => $request->settings ?? []
        ]);

        return redirect()->route('ecommerce.stores.index')->with('success', 'Store updated successfully');
    }

    public function destroy(EcommerceStore $store)
    {
        $store->delete();
        return redirect()->route('ecommerce.stores.index')->with('success', 'Store deleted successfully');
    }

    public function toggleStatus($id)
    {
        // Toggle status logic here
        return response()->json(['success' => true]);
    }

    public function storefront($slug)
    {
        // storefront logic here
    }
}
