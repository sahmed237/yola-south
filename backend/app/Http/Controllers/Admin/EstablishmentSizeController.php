<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EstablishmentSize;
use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EstablishmentSizeController extends Controller
{
    public function index()
    {
        $sizes = EstablishmentSize::with(['creator', 'updater'])->get();
        return view('admin.setup.establishment_sizes.index', compact('sizes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'value' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:establishment_sizes,key',
            'status' => 'nullable|boolean'
        ]);

        EstablishmentSize::create([
            'key' => Str::slug($validated['key']),
            'value' => $validated['value'],
            'status' => $request->has('status'),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Establishment size created successfully.');
    }

    public function update(Request $request, $id)
    {
        $size = EstablishmentSize::findOrFail($id);
        
        $validated = $request->validate([
            'value' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:establishment_sizes,key,' . $id,
            'status' => 'nullable|boolean'
        ]);

        $size->update([
            'key' => Str::slug($validated['key']),
            'value' => $validated['value'],
            'status' => $request->has('status'),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Establishment size updated successfully.');
    }

    public function destroy($id)
    {
        $size = EstablishmentSize::findOrFail($id);
        
        // Check if referenced by any establishments
        $referenced = Establishment::where('establishment_size_id', $id)->exists();
        
        if ($referenced) {
            return redirect()->back()->with('error', 'Cannot delete establishment size because it is currently assigned to one or more establishments.');
        }

        $size->delete();
        
        return redirect()->back()->with('success', 'Establishment size deleted successfully.');
    }
}
