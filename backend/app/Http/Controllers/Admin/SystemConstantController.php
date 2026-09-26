<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemConstant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SystemConstantController extends Controller
{
    public function index($category)
    {
        $constants = SystemConstant::where('category', $category)
            ->with(['creator', 'updater'])
            ->get();
            
        $title = $this->getCategoryTitle($category);
        
        return view('admin.setup.constants.index', compact('constants', 'category', 'title'));
    }

    public function store(Request $request, $category)
    {
        $validated = $request->validate([
            'value' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:system_constants,key',
        ]);

        SystemConstant::create([
            'category' => $category,
            'key' => Str::slug($validated['key']),
            'value' => $validated['value'],
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Setup item added successfully.');
    }

    public function update(Request $request, $id)
    {
        $constant = SystemConstant::findOrFail($id);
        
        $validated = $request->validate([
            'value' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:system_constants,key,' . $id,
        ]);

        $constant->update([
            'key' => Str::slug($validated['key']),
            'value' => $validated['value'],
            'updated_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Setup item updated successfully.');
    }

    public function destroy($id)
    {
        $constant = SystemConstant::findOrFail($id);
        $constant->delete();
        return redirect()->back()->with('success', 'Setup item deleted successfully.');
    }

    private function getCategoryTitle($category)
    {
        return match($category) {
            'establishment_type' => 'Establishment Types',
            'establishment_size' => 'Establishment Sizes',
            default => 'System Setup',
        };
    }
}
