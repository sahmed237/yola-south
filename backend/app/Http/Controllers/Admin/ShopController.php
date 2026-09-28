<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Market;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Shop::with('market');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('shop_code', 'like', "%{$search}%")
                  ->orWhere('shop_number', 'like', "%{$search}%")
                  ->orWhere('block_name', 'like', "%{$search}%")
                  ->orWhere('current_occupant_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('market_id')) {
            $query->where('market_id', $request->market_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $shops = $query->orderBy('shop_code')->paginate(20)->withQueryString();

        // Status counts for chips
        $totalCount = Shop::count();
        $occupiedCount = Shop::where('status', 'occupied')->count();
        $vacantCount = Shop::where('status', 'vacant')->count();
        $arrearsCount = Shop::where('status', 'arrears')->count();

        $markets = Market::orderBy('name')->get();

        return view('admin.shops.index', compact(
            'shops',
            'markets',
            'totalCount',
            'occupiedCount',
            'vacantCount',
            'arrearsCount'
        ));
    }

    public function create(Request $request)
    {
        $markets = Market::orderBy('name')->get();
        $selectedMarketId = $request->get('market_id');
        return view('admin.shops.create', compact('markets', 'selectedMarketId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'market_id' => 'required|exists:markets,id',
            'block_name' => 'required|string|max:50',
            'shop_number' => 'required|string|max:50',
            'size' => 'required|string|max:50',
            'type' => 'required|string|max:50',
            'monthly_rent' => 'required|numeric|min:0',
            'status' => 'required|in:vacant,occupied,arrears,reserved,maintenance',
            'current_occupant_name' => 'nullable|string|max:255',
            'current_occupant_phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        // Auto-generate shop code if not provided
        $market = Market::findOrFail($validated['market_id']);
        $seq = str_pad(Shop::where('market_id', $market->id)->count() + 1, 4, '0', STR_PAD_LEFT);
        $shopCode = 'YSLG-SHP-' . str_pad(Shop::count() + 100, 6, '0', STR_PAD_LEFT);

        $validated['shop_code'] = $shopCode;
        $validated['annual_rent'] = $validated['monthly_rent'] * 12;

        Shop::create($validated);

        return redirect()->route('admin.shops.index')->with('success', "Shop unit {$shopCode} added to inventory.");
    }

    public function show(Shop $shop)
    {
        $shop->load(['market', 'allocations.reviewer', 'allocations.approver']);
        return view('admin.shops.show', compact('shop'));
    }

    public function edit(Shop $shop)
    {
        $markets = Market::orderBy('name')->get();
        return view('admin.shops.edit', compact('shop', 'markets'));
    }

    public function update(Request $request, Shop $shop)
    {
        $validated = $request->validate([
            'market_id' => 'required|exists:markets,id',
            'block_name' => 'required|string|max:50',
            'shop_number' => 'required|string|max:50',
            'size' => 'required|string|max:50',
            'type' => 'required|string|max:50',
            'monthly_rent' => 'required|numeric|min:0',
            'status' => 'required|in:vacant,occupied,arrears,reserved,maintenance',
            'current_occupant_name' => 'nullable|string|max:255',
            'current_occupant_phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $validated['annual_rent'] = $validated['monthly_rent'] * 12;
        $shop->update($validated);

        return redirect()->route('admin.shops.show', $shop)->with('success', 'Shop unit details updated successfully.');
    }

    public function destroy(Shop $shop)
    {
        if ($shop->status === 'occupied') {
            return back()->with('error', 'Cannot delete an active occupied shop. Revoke or clear occupant standing first.');
        }

        $shop->delete();

        return redirect()->route('admin.shops.index')->with('success', 'Shop unit removed from inventory.');
    }
}
