<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\EstablishmentType;
use App\Models\EstablishmentSize;
use App\Models\EstablishmentOwner;
use App\Models\ActivityLog;
use App\Models\Lga;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class EstablishmentApprovalController extends Controller
{
    public function index(Request $request)
    {
        $query = Establishment::areaRestricted()->where('status', 'pending')->with(['occupant', 'owner', 'creator']);

        // Specific filters
        if ($request->filled('unique_id')) {
            $query->where('unique_id', 'like', '%' . $request->unique_id . '%');
        }
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('establishment_type_id')) {
            $query->where('establishment_type_id', $request->establishment_type_id);
        }
        if ($request->filled('establishment_size_id')) {
            $query->where('establishment_size_id', $request->establishment_size_id);
        }
        if ($request->filled('lga')) {
            $query->where('lga', $request->lga);
        }
        if ($request->filled('ward')) {
            $query->where('ward', $request->ward);
        }
        if ($request->filled('address')) {
            $query->where(function($q) use ($request) {
                $q->where('street_address', 'like', '%' . $request->address . '%')
                  ->orWhere('city', 'like', '%' . $request->address . '%')
                  ->orWhere('house_number', 'like', '%' . $request->address . '%')
                  ->orWhere('postal_code', 'like', '%' . $request->address . '%');
            });
        }

        $perPage = $request->input('per_page', 25);
        $pendingEstablishments = $query->latest()->paginate($perPage)->withQueryString();
        $lgas = auth()->user()->getDesignatedLgasAndWards();
        $establishmentTypes = EstablishmentType::where('status', true)->get();
        $establishmentSizes = EstablishmentSize::where('status', true)->get();

        return view('admin.establishments.approvals', compact('pendingEstablishments', 'lgas', 'establishmentTypes', 'establishmentSizes'));
    }

    public function show($id)
    {
        $establishment = Establishment::areaRestricted()->with(['occupant', 'owner', 'creator', 'images', 'activityLogs'])->findOrFail($id);
        $establishmentTypes = EstablishmentType::where('status', 1)->get();
        $establishmentSizes = EstablishmentSize::where('status', 1)->get();
        $lgas = auth()->user()->getDesignatedLgasAndWards();
        return view('admin.establishments.approval_show', compact('establishment', 'establishmentTypes', 'establishmentSizes', 'lgas'));
    }

    public function process(Request $request, $id)
    {
        $establishment = Establishment::areaRestricted()->with(['occupant', 'owner'])->findOrFail($id);

        $isOwnerProvided = $request->anyFilled(['owner_name', 'owner_phone', 'owner_email', 'owner_gender', 'owner_nin']);

        $request->validate([
            'action' => 'required|in:approve,reject',
            'remarks' => 'required_if:action,reject',
            'name' => 'required|string|max:255',
            'establishment_type_id' => 'required|exists:establishment_types,id',
            'establishment_size_id' => 'required|exists:establishment_sizes,id',
            'lga' => 'required|string|max:255',
            'ward' => 'required|string|max:255',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'inside_metropolis' => 'required|boolean',
            'street_address' => 'required|string|max:255',
            'house_number' => 'required|string|max:50',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'owner_name' => ($isOwnerProvided ? 'required' : 'nullable') . '|string|max:255',
            'owner_gender' => ($isOwnerProvided ? 'required' : 'nullable') . '|in:male,female,corporate',
            'owner_phone' => ($isOwnerProvided && !$request->filled('owner_email') ? 'required' : 'nullable') . '|string|max:20',
            'owner_email' => ($isOwnerProvided && !$request->filled('owner_phone') ? 'required' : 'nullable') . '|email|max:255',
            'owner_nin' => 'nullable|string|max:20',
            'occupant_name' => 'nullable|string|max:255',
            'occupant_phone' => 'nullable|string|max:20',
            'base_year' => 'required|integer|min:2015|max:' . date('Y'),
        ]);

        $user = auth()->user();
        if (!$user->hasRole('super-admin')) {
            $allowedLgasAndWards = $user->getDesignatedLgasAndWards();
            $allowedLga = $allowedLgasAndWards->firstWhere('name', $request->lga);
            if (!$allowedLga) {
                return back()->withErrors(['lga' => 'You are not designated to work in the selected LGA.'])->withInput();
            }
            $allowedWard = $allowedLga->wards->firstWhere('name', $request->ward);
            if (!$allowedWard) {
                return back()->withErrors(['ward' => 'You are not designated to work in the selected Ward.'])->withInput();
            }
        }

        // Check if data changed
        $updatedData = $request->only(['name', 'establishment_type_id', 'establishment_size_id', 'lga', 'ward', 'lat', 'lng', 'inside_metropolis', 'street_address', 'house_number', 'city', 'postal_code', 'base_year']);
        $establishment->fill($updatedData);
        $isUpdated = $establishment->isDirty();

        // Check if owner changed
        $ownerUpdated = false;
        if ($establishment->owner) {
            $establishment->owner->fill([
                'name' => $request->owner_name,
                'phone' => $request->owner_phone,
                'email' => $request->owner_email,
                'gender' => $request->owner_gender,
                'nin' => $request->owner_nin,
            ]);
            $ownerUpdated = $establishment->owner->isDirty();
        } elseif (!empty($request->owner_name)) {
            $owner = EstablishmentOwner::create([
                'name' => $request->owner_name,
                'phone' => $request->owner_phone,
                'email' => $request->owner_email,
                'gender' => $request->owner_gender,
                'nin' => $request->owner_nin,
            ]);
            $establishment->owner_id = $owner->id;
            $ownerUpdated = true;
        }
        
        if ($isUpdated || $ownerUpdated) {
            if ($ownerUpdated && $establishment->owner) {
                $establishment->owner->save();
            }
            $establishment->save();
            ActivityLog::create([
                'establishment_id' => $establishment->id,
                'user_id' => auth()->id(),
                'action_type' => 'updated',
                'remarks' => 'Establishment details were updated during verification.'
            ]);
        }

        // Update occupant details if changed
        if ($establishment->occupant) {
            $occupantData = [
                'name' => $request->occupant_name,
                'phone' => $request->occupant_phone
            ];
            $establishment->occupant->fill($occupantData);
            if ($establishment->occupant->isDirty()) {
                $establishment->occupant->save();
            }
        }

        if ($request->action === 'approve') {
            // Generate Unique ID: LGA-WARD-XXXXXXXXXX
            $uniqueId = strtoupper($establishment->lga . '-' . $establishment->ward . '-' . Str::random(10));
            
            // QR Code Path
            $qrPath = 'qrcodes/' . $uniqueId . '.svg';
            
            // Ensure directory exists
            if (!Storage::disk('public')->exists('qrcodes')) {
                Storage::disk('public')->makeDirectory('qrcodes');
            }

            // Generate QR Code using BaconQrCode directly
            $renderer = new ImageRenderer(
                new RendererStyle(300),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);
            $qrCode = $writer->writeString(route('public.establishment.show', $uniqueId));

            // Save QR Code to public disk
            Storage::disk('public')->put($qrPath, $qrCode);

            $establishment->update([
                'status' => 'approved',
                'unique_id' => $uniqueId,
                'qr_code_path' => $qrPath
            ]);

            ActivityLog::create([
                'establishment_id' => $establishment->id,
                'user_id' => auth()->id(),
                'action_type' => 'approved',
                'remarks' => $request->remarks ?? 'Establishment approved successfully.'
            ]);

            return redirect()->route('admin.approvals.index')->with('success', 'Establishment approved. Unique ID: ' . $uniqueId);
        }

        if ($request->action === 'reject') {
            $establishment->update(['status' => 'rejected']);

            ActivityLog::create([
                'establishment_id' => $establishment->id,
                'user_id' => auth()->id(),
                'action_type' => 'rejected',
                'remarks' => $request->remarks
            ]);

            return redirect()->route('admin.approvals.index')->with('error', 'Establishment registration rejected.');
        }

        return redirect()->route('admin.approvals.index');
    }
}
