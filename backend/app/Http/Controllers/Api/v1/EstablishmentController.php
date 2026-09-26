<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\Occupant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstablishmentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'size' => 'required|string',
            'lga' => 'required|string',
            'ward' => 'required|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'inside_metropolis' => 'nullable|boolean',
            'street_address' => 'nullable|string',
            'house_number' => 'nullable|string',
            'city' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'owner_name' => 'nullable|string',
            'owner_phone' => 'nullable|string',
            'owner_email' => 'nullable|string',
            'owner_gender' => 'nullable|string',
            'owner_nin' => 'nullable|string',
            'occupant_name' => 'nullable|string',
            'occupant_phone' => 'nullable|string',
            'base_year' => 'nullable|integer|min:2000|max:2100',
        ]);

        $user = auth()->user() ?? $request->user();
        if ($user && !$user->hasRole('super-admin')) {
            $allowedLgasAndWards = $user->getDesignatedLgasAndWards();
            $allowedLga = $allowedLgasAndWards->firstWhere('name', $validated['lga'] ?? $request->lga);
            if (!$allowedLga) {
                return response()->json(['error' => 'You are not designated to work in the selected LGA.'], 403);
            }
            $allowedWard = $allowedLga->wards->firstWhere('name', $validated['ward'] ?? $request->ward);
            if (!$allowedWard) {
                return response()->json(['error' => 'You are not designated to work in the selected Ward.'], 403);
            }
        }

        return DB::transaction(function () use ($validated, $user) {
            $occupantId = null;
            if (!empty($validated['occupant_name'])) {
                $occupant = Occupant::create([
                    'name' => $validated['occupant_name'],
                    'phone' => $validated['occupant_phone'] ?? null,
                ]);
                $occupantId = $occupant->id;
            }

            // Dynamically resolve type and size IDs
            $typeId = null;
            if (!empty($validated['type'])) {
                $typeId = DB::table('establishment_types')
                    ->where('value', $validated['type'])
                    ->value('id');
            }
            if (!$typeId) {
                $typeId = DB::table('establishment_types')->first()?->id;
            }

            $sizeId = null;
            if (!empty($validated['size'])) {
                $sizeId = DB::table('establishment_sizes')
                    ->where('value', $validated['size'])
                    ->value('id');
            }
            if (!$sizeId) {
                $sizeId = DB::table('establishment_sizes')->first()?->id;
            }

            // Dynamically create or link owner if provided
            $ownerId = null;
            if (!empty($validated['owner_name'])) {
                $gender = isset($validated['owner_gender']) ? strtolower(trim($validated['owner_gender'])) : null;
                $owner = \App\Models\EstablishmentOwner::create([
                    'name' => $validated['owner_name'],
                    'phone' => $validated['owner_phone'] ?? null,
                    'email' => $validated['owner_email'] ?? null,
                    'gender' => $gender,
                    'nin' => $validated['owner_nin'] ?? null,
                ]);
                $ownerId = $owner->id;
            }

            $establishment = Establishment::create([
                'name' => $validated['name'],
                'establishment_type_id' => $typeId,
                'establishment_size_id' => $sizeId,
                'lga' => $validated['lga'],
                'ward' => $validated['ward'],
                'lat' => $validated['lat'],
                'lng' => $validated['lng'],
                'inside_metropolis' => filter_var($validated['inside_metropolis'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'street_address' => $validated['street_address'] ?? null,
                'house_number' => $validated['house_number'] ?? null,
                'city' => $validated['city'] ?? null,
                'postal_code' => $validated['postal_code'] ?? null,
                'occupant_id' => $occupantId,
                'owner_id' => $ownerId,
                'status' => 'pending',
                'created_by' => $user->id ?? null,
                'base_year' => $validated['base_year'] ?? config('app.revenue_base_year', date('Y')),
            ]);

            $establishment->load(['occupant', 'owner', 'establishmentType', 'establishmentSize']);

            return response()->json([
                'message' => 'Establishment registered successfully',
                'establishment' => $establishment
            ], 201);
        });
    }

    public function show(Request $request, $id)
    {
        $query = Establishment::with(['occupant', 'owner', 'establishmentType', 'establishmentSize']);
        $user = auth()->user() ?? $request->user();
        if ($user) {
            $query->areaRestricted();
        }
        $establishment = $query->where(function($q) use ($id) {
            $q->where('unique_id', $id)->orWhere('id', $id);
        })->firstOrFail();
        return response()->json($establishment);
    }
}
