<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RevenueHead;
use App\Models\Agency;
use Illuminate\Http\Request;

class RevenueHeadController extends Controller
{
    public function index()
    {
        $heads = RevenueHead::with('agency')->paginate(20);
        return view('admin.revenue_heads.index', compact('heads'));
    }

    public function create()
    {
        $agencies = Agency::where('status', true)->where('is_service_fee', false)->get();
        return view('admin.revenue_heads.create', compact('agencies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'agency_id' => 'required|exists:agencies,id',
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|string',
            'sql_rule' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        RevenueHead::create($validated);

        return redirect()->route('admin.revenue-heads.index')->with('success', 'Revenue head created successfully.');
    }

    public function edit(RevenueHead $revenueHead)
    {
        $agencies = Agency::where('status', true)->where('is_service_fee', false)->get();
        return view('admin.revenue_heads.edit', compact('revenueHead', 'agencies'));
    }

    public function update(Request $request, RevenueHead $revenueHead)
    {
        $validated = $request->validate([
            'agency_id' => 'required|exists:agencies,id',
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|string',
            'sql_rule' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        $revenueHead->update($validated);

        return redirect()->route('admin.revenue-heads.index')->with('success', 'Revenue head updated successfully.');
    }

    public function destroy(RevenueHead $revenueHead)
    {
        $revenueHead->delete();
        return redirect()->route('admin.revenue-heads.index')->with('success', 'Revenue head deleted successfully.');
    }
}
