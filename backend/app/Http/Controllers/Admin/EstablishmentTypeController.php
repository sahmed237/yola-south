<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EstablishmentType;
use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EstablishmentTypeController extends Controller
{
    public function index()
    {
        $types = EstablishmentType::with(['creator', 'updater'])->get();
        return view('admin.setup.establishment_types.index', compact('types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'value' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:establishment_types,key',
            'status' => 'nullable|boolean'
        ]);

        EstablishmentType::create([
            'key' => Str::slug($validated['key']),
            'value' => $validated['value'],
            'status' => $request->has('status'),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Establishment type created successfully.');
    }

    public function update(Request $request, $id)
    {
        $type = EstablishmentType::findOrFail($id);
        
        $validated = $request->validate([
            'value' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:establishment_types,key,' . $id,
            'status' => 'nullable|boolean'
        ]);

        $type->update([
            'key' => Str::slug($validated['key']),
            'value' => $validated['value'],
            'status' => $request->has('status'),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Establishment type updated successfully.');
    }

    public function destroy($id)
    {
        $type = EstablishmentType::findOrFail($id);
        
        // Check if referenced by any establishments
        $referenced = Establishment::where('establishment_type_id', $id)->exists();
        
        if ($referenced) {
            return redirect()->back()->with('error', 'Cannot delete establishment type because it is currently assigned to one or more establishments.');
        }

        $type->delete();
        
        return redirect()->back()->with('success', 'Establishment type deleted successfully.');
    }
}
