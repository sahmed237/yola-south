<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\Occupant;
use App\Models\EstablishmentImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SyncController extends Controller
{
    public function fetchStatus(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $query = Establishment::query();
        if (!$user->hasPermissionTo('view all establishment')) {
            $query->where('created_by', $user->id);
        } else {
            $query->areaRestricted();
        }

        $establishments = $query->with(['occupant', 'owner', 'establishmentType', 'establishmentSize', 'images', 'activityLogs'])
            ->get()
            ->map(function ($est) {
                $rejectionLog = $est->activityLogs->where('action_type', 'rejected')->last();
                $est->setAttribute('rejection_remarks', $rejectionLog ? $rejectionLog->remarks : null);
                return $est;
            });

        return response()->json([
            'establishments' => $establishments
        ]);
    }

    public function sync(Request $request)
    {
        $establishmentsData = $request->input('establishments', $request->input('shops', []));
        
        // Basic validation for the payload structure
        if (!is_array($establishmentsData)) {
            return response()->json(['error' => 'Invalid data format'], 400);
        }

        $results = [];

        foreach ($establishmentsData as $data) {
            try {
                // Validate coordinate ranges before processing
                if (isset($data['lat']) && ($data['lat'] < -90 || $data['lat'] > 90)) {
                    throw new \Exception("Latitude value [{$data['lat']}] is out of range (-90 to 90).");
                }
                if (isset($data['lng']) && ($data['lng'] < -180 || $data['lng'] > 180)) {
                    throw new \Exception("Longitude value [{$data['lng']}] is out of range (-180 to 180).");
                }

                $user = auth()->user() ?? $request->user();
                if ($user && !$user->hasRole('super-admin')) {
                    $allowedLgasAndWards = $user->getDesignatedLgasAndWards();
                    $allowedLga = $allowedLgasAndWards->firstWhere('name', $data['lga']);
                    if (!$allowedLga) {
                        throw new \Exception("You are not designated to work in the selected LGA: {$data['lga']}.");
                    }
                    $allowedWard = $allowedLga->wards->firstWhere('name', $data['ward']);
                    if (!$allowedWard) {
                        throw new \Exception("You are not designated to work in the selected Ward: {$data['ward']}.");
                    }
                }

                DB::transaction(function () use ($data, $request, &$results) {
                    $serverId = $data['server_id'] ?? null;
                    $establishment = $serverId ? Establishment::find($serverId) : null;

                    $occupantId = null;
                    if ($establishment) {
                        $occupantId = $establishment->occupant_id;
                    }

                    if (!empty($data['occupant_name'])) {
                        if ($occupantId) {
                            $occupant = Occupant::find($occupantId);
                            if ($occupant) {
                                $occupant->update([
                                    'name' => $data['occupant_name'],
                                    'phone' => $data['occupant_phone'] ?? null,
                                ]);
                            } else {
                                $occupant = Occupant::create([
                                    'name' => $data['occupant_name'],
                                    'phone' => $data['occupant_phone'] ?? null,
                                ]);
                                $occupantId = $occupant->id;
                            }
                        } else {
                            $occupant = Occupant::create([
                                'name' => $data['occupant_name'],
                                'phone' => $data['occupant_phone'] ?? null,
                            ]);
                            $occupantId = $occupant->id;
                        }
                    } else {
                        $occupantId = null;
                    }

                    // Dynamically resolve establishment type and size IDs from text values
                    $typeId = null;
                    if (!empty($data['type'])) {
                        $typeId = DB::table('establishment_types')
                            ->where('value', $data['type'])
                            ->value('id');
                    }
                    if (!$typeId) {
                        $typeId = DB::table('establishment_types')->first()?->id;
                    }

                    $sizeId = null;
                    if (!empty($data['size'])) {
                        $sizeId = DB::table('establishment_sizes')
                            ->where('value', $data['size'])
                            ->value('id');
                    }
                    if (!$sizeId) {
                        $sizeId = DB::table('establishment_sizes')->first()?->id;
                    }

                    // Dynamically create or link owner if provided
                    $ownerId = null;
                    if ($establishment) {
                        $ownerId = $establishment->owner_id;
                    }

                    if (!empty($data['owner_name'])) {
                        $gender = isset($data['owner_gender']) ? strtolower(trim($data['owner_gender'])) : null;
                        if ($ownerId) {
                            $owner = \App\Models\EstablishmentOwner::find($ownerId);
                            if ($owner) {
                                $owner->update([
                                    'name' => $data['owner_name'],
                                    'phone' => $data['owner_phone'] ?? null,
                                    'email' => $data['owner_email'] ?? null,
                                    'gender' => $gender,
                                    'nin' => $data['owner_nin'] ?? null,
                                ]);
                            } else {
                                $owner = \App\Models\EstablishmentOwner::create([
                                    'name' => $data['owner_name'],
                                    'phone' => $data['owner_phone'] ?? null,
                                    'email' => $data['owner_email'] ?? null,
                                    'gender' => $gender,
                                    'nin' => $data['owner_nin'] ?? null,
                                ]);
                                $ownerId = $owner->id;
                            }
                        } else {
                            $owner = \App\Models\EstablishmentOwner::create([
                                'name' => $data['owner_name'],
                                'phone' => $data['owner_phone'] ?? null,
                                'email' => $data['owner_email'] ?? null,
                                'gender' => $gender,
                                'nin' => $data['owner_nin'] ?? null,
                            ]);
                            $ownerId = $owner->id;
                        }
                    } else {
                        $ownerId = null;
                    }

                    if ($establishment) {
                        $establishment->update([
                            'name' => $data['name'],
                            'establishment_type_id' => $typeId,
                            'establishment_size_id' => $sizeId,
                            'lga' => $data['lga'],
                            'ward' => $data['ward'],
                            'lat' => $data['lat'],
                            'lng' => $data['lng'],
                            'inside_metropolis' => filter_var($data['inside_metropolis'] ?? false, FILTER_VALIDATE_BOOLEAN),
                            'street_address' => $data['street_address'] ?? null,
                            'house_number' => $data['house_number'] ?? null,
                            'city' => $data['city'] ?? null,
                            'postal_code' => $data['postal_code'] ?? null,
                            'occupant_id' => $occupantId,
                            'owner_id' => $ownerId,
                            'status' => 'pending',
                            'base_year' => $data['base_year'] ?? config('app.revenue_base_year', date('Y')),
                        ]);
                    } else {
                        $establishment = Establishment::create([
                            'name' => $data['name'],
                            'establishment_type_id' => $typeId,
                            'establishment_size_id' => $sizeId,
                            'lga' => $data['lga'],
                            'ward' => $data['ward'],
                            'lat' => $data['lat'],
                            'lng' => $data['lng'],
                            'inside_metropolis' => filter_var($data['inside_metropolis'] ?? false, FILTER_VALIDATE_BOOLEAN),
                            'street_address' => $data['street_address'] ?? null,
                            'house_number' => $data['house_number'] ?? null,
                            'city' => $data['city'] ?? null,
                            'postal_code' => $data['postal_code'] ?? null,
                            'occupant_id' => $occupantId,
                            'owner_id' => $ownerId,
                            'status' => 'pending',
                            'created_by' => $request->user()->id ?? null,
                            'base_year' => $data['base_year'] ?? config('app.revenue_base_year', date('Y')),
                        ]);
                    }

                    if (!empty($data['images']) && is_array($data['images'])) {
                        // Delete old images first if establishment already exists
                        if ($serverId && $establishment) {
                            foreach ($establishment->images as $oldImage) {
                                Storage::disk('public')->delete($oldImage->image_path);
                                $oldImage->delete();
                            }
                        }

                        foreach ($data['images'] as $index => $base64Image) {
                            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                                $imageContent = substr($base64Image, strpos($base64Image, ',') + 1);
                                $imageContent = base64_decode($imageContent);
                                $extension = strtolower($type[1]); // png, jpg, jpeg
                            } else {
                                $imageContent = base64_decode($base64Image);
                                $extension = 'jpg';
                            }

                            if ($imageContent !== false) {
                                $fileName = Str::random(40) . '.' . $extension;
                                $path = 'establishments/' . $establishment->id . '/' . $fileName;
                                Storage::disk('public')->put($path, $imageContent);

                                EstablishmentImage::create([
                                    'establishment_id' => $establishment->id,
                                    'image_path' => $path,
                                    'is_primary' => $index === 0,
                                    'lat' => $data['lat'] ?? null,
                                    'lng' => $data['lng'] ?? null,
                                    'device_info' => $request->header('User-Agent'),
                                ]);
                            }
                        }
                    }

                    $results[] = [
                        'local_id' => $data['local_id'] ?? null,
                        'server_id' => $establishment->id,
                        'status' => 'synced'
                    ];
                });
            } catch (\Exception $e) {
                $results[] = [
                    'local_id' => $data['local_id'] ?? null,
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json(['results' => $results]);
    }

    public function fetchUnpaidTaxes(Request $request, \App\Services\Revenue\RevenueService $revenueService)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        if (!$user->hasPermissionTo('view unpaid taxes')) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $query = Establishment::query()->where('status', 'approved');
        if (!$user->hasPermissionTo('view all establishment')) {
            $query->where('created_by', $user->id);
        } else {
            $query->areaRestricted();
        }

        $establishments = $query->with(['occupant', 'owner', 'establishmentType', 'establishmentSize', 'images'])
            ->get();

        $unpaidList = [];
        foreach ($establishments as $establishment) {
            $taxStatus = $revenueService->getEstablishmentTaxStatus($establishment);
            $outstanding = (float) ($taxStatus['totals']['outstanding'] ?? 0.0);
            if ($outstanding > 0) {
                $establishment->setAttribute('outstanding_amount', $outstanding);
                $establishment->setAttribute('total_due', (float) ($taxStatus['totals']['due'] ?? 0.0));
                $establishment->setAttribute('total_paid', (float) ($taxStatus['totals']['paid'] ?? 0.0));
                $unpaidList[] = $establishment;
            }
        }

        return response()->json([
            'establishments' => $unpaidList
        ]);
    }
}

