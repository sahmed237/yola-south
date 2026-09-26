<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RevenueRule;
use App\Models\Agency;
use Illuminate\Http\Request;

class RevenueRuleController extends Controller
{
    public function index()
    {
        $rules = RevenueRule::with('agency')->paginate(20);
        return view('admin.revenue_rules.index', compact('rules'));
    }

    public function create()
    {
        $agencies = Agency::where('status', true)->where('is_service_fee', false)->get();
        return view('admin.revenue_rules.create', compact('agencies'));
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

        RevenueRule::create($validated);

        return redirect()->route('admin.revenue-rules.index')->with('success', 'Revenue rule created successfully.');
    }

    public function edit(RevenueRule $revenueRule)
    {
        $agencies = Agency::where('status', true)->where('is_service_fee', false)->get();
        return view('admin.revenue_rules.edit', compact('revenueRule', 'agencies'));
    }

    public function update(Request $request, RevenueRule $revenueRule)
    {
        $validated = $request->validate([
            'agency_id' => 'required|exists:agencies,id',
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|string',
            'sql_rule' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        $revenueRule->update($validated);

        return redirect()->route('admin.revenue-rules.index')->with('success', 'Revenue rule updated successfully.');
    }

    public function destroy(RevenueRule $revenueRule)
    {
        $revenueRule->delete();
        return redirect()->route('admin.revenue-rules.index')->with('success', 'Revenue rule deleted successfully.');
    }
}
