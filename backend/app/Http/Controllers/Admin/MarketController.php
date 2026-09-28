<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\Ward;
use App\Models\Shop;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    public function index(Request $request)
    {
        $query = Market::with(['ward', 'shops']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('ward_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('ward_id')) {
            $query->where('ward_id', $request->ward_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $markets = $query->orderBy('name')->paginate(15)->withQueryString();

        // High-level KPIs matching UI/index.html
        $totalMarkets = Market::count();
        $totalShops = Shop::count();
        $occupiedShops = Shop::where('status', 'occupied')->count();
        $vacantShops = Shop::where('status', 'vacant')->count();
        $occupancyRate = $totalShops > 0 ? round(($occupiedShops / $totalShops) * 100, 1) : 0;
        $totalRevenueYtd = Market::sum('revenue_ytd');

        $wards = Ward::orderBy('name')->get();

        return view('admin.markets.index', compact(
            'markets',
            'totalMarkets',
            'totalShops',
            'occupiedShops',
            'vacantShops',
            'occupancyRate',
            'totalRevenueYtd',
            'wards'
        ));
    }

    public function create()
    {
        $wards = Ward::orderBy('name')->get();
        return view('admin.markets.create', compact('wards'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:markets,code',
            'ward_id' => 'nullable|exists:wards,id',
            'ward_name' => 'nullable|string|max:100',
            'blocks_count' => 'required|integer|min:1',
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,under_renovation',
        ]);

        if (!empty($validated['ward_id']) && empty($validated['ward_name'])) {
            $ward = Ward::find($validated['ward_id']);
            $validated['ward_name'] = $ward?->name;
        }

        $market = Market::create($validated);

        return redirect()->route('admin.markets.show', $market)->with('success', 'Market successfully registered.');
    }

    public function show(Market $market)
    {
        $market->load(['ward', 'shops' => function ($q) {
            $q->orderBy('block_name')->orderBy('shop_number');
        }]);

        $shopsCount = $market->shops->count();
        $occupiedCount = $market->shops->where('status', 'occupied')->count();
        $vacantCount = $market->shops->where('status', 'vacant')->count();
        $arrearsCount = $market->shops->where('status', 'arrears')->count();
        $occupancyRate = $shopsCount > 0 ? round(($occupiedCount / $shopsCount) * 100, 1) : 0;

        return view('admin.markets.show', compact('market', 'shopsCount', 'occupiedCount', 'vacantCount', 'arrearsCount', 'occupancyRate'));
    }

    public function edit(Market $market)
    {
        $wards = Ward::orderBy('name')->get();
        return view('admin.markets.edit', compact('market', 'wards'));
    }

    public function update(Request $request, Market $market)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:markets,code,' . $market->id,
            'ward_id' => 'nullable|exists:wards,id',
            'ward_name' => 'nullable|string|max:100',
            'blocks_count' => 'required|integer|min:1',
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,under_renovation',
        ]);

        if (!empty($validated['ward_id']) && empty($validated['ward_name'])) {
            $ward = Ward::find($validated['ward_id']);
            $validated['ward_name'] = $ward?->name;
        }

        $market->update($validated);

        return redirect()->route('admin.markets.show', $market)->with('success', 'Market details updated successfully.');
    }

    public function destroy(Market $market)
    {
        if ($market->shops()->where('status', 'occupied')->exists()) {
            return back()->with('error', 'Cannot delete market with active occupied shops.');
        }

        $market->delete();

        return redirect()->route('admin.markets.index')->with('success', 'Market archived successfully.');
    }
}
