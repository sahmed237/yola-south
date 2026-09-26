<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\Occupant;
use App\Models\EstablishmentType;
use App\Models\EstablishmentSize;
use App\Models\EstablishmentOwner;
use App\Models\EstablishmentImage;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Lga;
use App\Services\Revenue\RevenueService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class EstablishmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Establishment::areaRestricted()->with(['occupant', 'owner', 'creator']);

        // Check if user has permission to view all, otherwise restrict to their own creations
        $canViewAll = auth()->user()->hasPermissionTo('view all establishment');
        if (!$canViewAll) {
            $query->where('created_by', auth()->id());
        } else {
            // Apply registered_by filter if provided and user has permission
            if ($request->filled('registered_by')) {
                $query->where('created_by', $request->registered_by);
            }
        }

        // Specific filters
        if ($request->filled('unique_id')) {
            $query->where('unique_id', 'like', '%' . $request->unique_id . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
            $addressQuery = trim($request->address);
            $query->where(function($q) use ($addressQuery) {
                $q->where('street_address', 'like', '%' . $addressQuery . '%')
                  ->orWhere('city', 'like', '%' . $addressQuery . '%')
                  ->orWhere('house_number', 'like', '%' . $addressQuery . '%')
                  ->orWhere('postal_code', 'like', '%' . $addressQuery . '%')
                  ->orWhere('lga', 'like', '%' . $addressQuery . '%')
                  ->orWhere('ward', 'like', '%' . $addressQuery . '%');
            });
        }
        if ($request->filled('owner_name')) {
            $query->whereHas('owner', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->owner_name . '%');
            });
        }

        $perPage = $request->input('per_page', 25);
        $establishments = $query->latest()->paginate($perPage)->withQueryString();
        
        // Fetch users for the filter dropdown if user has permission
        $creators = collect();
        if ($canViewAll) {
            $creators = User::whereHas('createdEstablishments')->get();
        }

        $lgas = auth()->user()->getDesignatedLgasAndWards();
        $establishmentTypes = EstablishmentType::where('status', true)->get();
        $establishmentSizes = EstablishmentSize::where('status', true)->get();

        return view('admin.establishments.index', compact('establishments', 'creators', 'canViewAll', 'lgas', 'establishmentTypes', 'establishmentSizes'));
    }

    public function approved(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view all establishment')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Establishment::areaRestricted()->where('status', 'approved')->with(['occupant', 'owner', 'creator']);

        // Apply filters (shared logic with index)
        if ($request->filled('registered_by')) {
            $query->where('created_by', $request->registered_by);
        }
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
            $addressQuery = trim($request->address);
            $query->where(function($q) use ($addressQuery) {
                $q->where('street_address', 'like', '%' . $addressQuery . '%')
                  ->orWhere('city', 'like', '%' . $addressQuery . '%')
                  ->orWhere('house_number', 'like', '%' . $addressQuery . '%')
                  ->orWhere('postal_code', 'like', '%' . $addressQuery . '%')
                  ->orWhere('lga', 'like', '%' . $addressQuery . '%')
                  ->orWhere('ward', 'like', '%' . $addressQuery . '%');
            });
        }
        if ($request->filled('owner_name')) {
            $query->whereHas('owner', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->owner_name . '%');
            });
        }

        $perPage = $request->input('per_page', 25);
        $establishments = $query->latest()->paginate($perPage)->withQueryString();
        
        $canViewAll = true;
        $creators = User::whereHas('createdEstablishments')->get();
        $lgas = auth()->user()->getDesignatedLgasAndWards();
        $establishmentTypes = EstablishmentType::where('status', true)->get();
        $establishmentSizes = EstablishmentSize::where('status', true)->get();

        return view('admin.establishments.approved', compact('establishments', 'creators', 'canViewAll', 'lgas', 'establishmentTypes', 'establishmentSizes'));
    }

    public function invalid(Request $request)
    {
        $query = Establishment::areaRestricted()->where('status', 'rejected')->with(['occupant', 'owner', 'creator', 'activityLogs']);
        
        if (!auth()->user()->hasPermissionTo('view all invalid establishment')) {
            $query->where('created_by', auth()->id());
        }

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
            $addressQuery = trim($request->address);
            $query->where(function($q) use ($addressQuery) {
                $q->where('street_address', 'like', '%' . $addressQuery . '%')
                  ->orWhere('city', 'like', '%' . $addressQuery . '%')
                  ->orWhere('house_number', 'like', '%' . $addressQuery . '%')
                  ->orWhere('postal_code', 'like', '%' . $addressQuery . '%')
                  ->orWhere('lga', 'like', '%' . $addressQuery . '%')
                  ->orWhere('ward', 'like', '%' . $addressQuery . '%');
            });
        }
        if ($request->filled('owner_name')) {
            $query->whereHas('owner', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->owner_name . '%');
            });
        }

        $perPage = $request->input('per_page', 25);
        $establishments = $query->latest()->paginate($perPage)->withQueryString();
        $lgas = auth()->user()->getDesignatedLgasAndWards();
        $establishmentTypes = EstablishmentType::where('status', true)->get();
        $establishmentSizes = EstablishmentSize::where('status', true)->get();

        return view('admin.establishments.invalid', compact('establishments', 'lgas', 'establishmentTypes', 'establishmentSizes'));
    }

    public function unpaid(Request $request, RevenueService $revenueService)
    {
        if (!auth()->user()->hasPermissionTo('view unpaid taxes')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Establishment::areaRestricted()->where('status', 'approved')->with(['occupant', 'owner', 'creator']);

        // Check if user has permission to view all, otherwise restrict to their own creations
        $canViewAll = auth()->user()->hasPermissionTo('view all establishment');
        if (!$canViewAll) {
            $query->where('created_by', auth()->id());
        } else {
            // Apply registered_by filter if provided and user has permission
            if ($request->filled('registered_by')) {
                $query->where('created_by', $request->registered_by);
            }
        }

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
            $addressQuery = trim($request->address);
            $query->where(function($q) use ($addressQuery) {
                $q->where('street_address', 'like', '%' . $addressQuery . '%')
                  ->orWhere('city', 'like', '%' . $addressQuery . '%')
                  ->orWhere('house_number', 'like', '%' . $addressQuery . '%')
                  ->orWhere('postal_code', 'like', '%' . $addressQuery . '%')
                  ->orWhere('lga', 'like', '%' . $addressQuery . '%')
                  ->orWhere('ward', 'like', '%' . $addressQuery . '%');
            });
        }

        // Fetch matching approved records
        $establishments = $query->latest()->get();

        // Evaluate dynamic outstanding tax balance and filter
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

        // Manual length-aware pagination
        $perPage = (int) $request->input('per_page', 25);
        $currentPage = (int) $request->input('page', 1);
        $total = count($unpaidList);
        
        $pagedData = array_slice($unpaidList, ($currentPage - 1) * $perPage, $perPage);
        
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedData,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Fetch users for the filter dropdown if user has permission
        $creators = collect();
        if ($canViewAll) {
            $creators = User::whereHas('createdEstablishments')->get();
        }

        $lgas = auth()->user()->getDesignatedLgasAndWards();
        $establishmentTypes = EstablishmentType::where('status', true)->get();
        $establishmentSizes = EstablishmentSize::where('status', true)->get();

        return view('admin.establishments.unpaid', compact(
            'paginated',
            'creators',
            'canViewAll',
            'lgas',
            'establishmentTypes',
            'establishmentSizes'
        ));
    }

    public function create()
    {
        $establishmentTypes = EstablishmentType::where('status', true)->orderBy('value')->get();
        $establishmentSizes = EstablishmentSize::where('status', true)->orderBy('value')->get();
        $lgas = auth()->user()->getDesignatedLgasAndWards();
        return view('admin.establishments.create', compact('establishmentTypes', 'establishmentSizes', 'lgas'));
    }

    public function store(Request $request)
    {
        $isOwnerProvided = $request->anyFilled(['owner_name', 'owner_phone', 'owner_email', 'owner_gender', 'owner_nin']);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'establishment_type_id' => 'required|exists:establishment_types,id',
            'establishment_size_id' => 'required|exists:establishment_sizes,id',
            'lga' => 'required|string',
            'ward' => 'required|string',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
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
            'establishment_images' => 'required|array|min:1|max:3',
            'establishment_images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            'base_year' => 'required|integer|min:2015|max:' . date('Y'),
        ]);

        $user = auth()->user();
        if (!$user->hasRole('super-admin')) {
            $allowedLgasAndWards = $user->getDesignatedLgasAndWards();
            $allowedLga = $allowedLgasAndWards->firstWhere('name', $validated['lga'] ?? $request->lga);
            if (!$allowedLga) {
                return back()->withErrors(['lga' => 'You are not designated to work in the selected LGA.'])->withInput();
            }
            $allowedWard = $allowedLga->wards->firstWhere('name', $validated['ward'] ?? $request->ward);
            if (!$allowedWard) {
                return back()->withErrors(['ward' => 'You are not designated to work in the selected Ward.'])->withInput();
            }
        }

        return DB::transaction(function () use ($validated, $request) {
            $occupantId = null;
            if (!empty($validated['occupant_name'])) {
                $occupant = Occupant::create([
                    'name' => $validated['occupant_name'],
                    'phone' => $validated['occupant_phone'] ?? null,
                ]);
                $occupantId = $occupant->id;
            }

            $ownerId = null;
            if (!empty($validated['owner_name'])) {
                $owner = EstablishmentOwner::create([
                    'name' => $validated['owner_name'],
                    'phone' => $validated['owner_phone'] ?? null,
                    'email' => $validated['owner_email'] ?? null,
                    'gender' => $validated['owner_gender'] ?? null,
                    'nin' => $validated['owner_nin'] ?? null,
                ]);
                $ownerId = $owner->id;
            }

            $establishment = Establishment::create([
                'name' => $validated['name'],
                'establishment_type_id' => $validated['establishment_type_id'],
                'establishment_size_id' => $validated['establishment_size_id'],
                'lga' => $validated['lga'],
                'ward' => $validated['ward'],
                'lat' => $validated['lat'],
                'lng' => $validated['lng'],
                'inside_metropolis' => $validated['inside_metropolis'],
                'street_address' => $validated['street_address'] ?? null,
                'house_number' => $validated['house_number'] ?? null,
                'city' => $validated['city'] ?? null,
                'postal_code' => $validated['postal_code'] ?? null,
                'occupant_id' => $occupantId,
                'owner_id' => $ownerId,
                'status' => 'pending',
                'created_by' => auth()->id(),
                'base_year' => $validated['base_year'],
            ]);

            if ($request->hasFile('establishment_images')) {
                foreach ($request->file('establishment_images') as $index => $image) {
                    $path = $image->store('establishments/' . $establishment->id, 'public');
                    
                    EstablishmentImage::create([
                        'establishment_id' => $establishment->id,
                        'image_path' => $path,
                        'is_primary' => $index === 0,
                        'lat' => $validated['lat'] ?? null, // Default to location
                        'lng' => $validated['lng'] ?? null,
                        'device_info' => $request->header('User-Agent'),
                    ]);
                }
            }

            ActivityLog::create([
                'establishment_id' => $establishment->id,
                'user_id' => auth()->id(),
                'action_type' => 'registration_submitted',
                'remarks' => 'Initial establishment registration submitted for approval.'
            ]);

            return redirect()->route('admin.establishments.index')->with('success', 'Establishment registered successfully with ' . count($request->file('establishment_images')) . ' images.');
        });
    }

    public function show($id)
    {
        $establishment = Establishment::areaRestricted()->with(['occupant', 'owner', 'creator', 'activityLogs'])->findOrFail($id);
        
        // Restrict to creator or 'view all establishment' permission
        if ($establishment->created_by !== auth()->id() && !auth()->user()->hasPermissionTo('view all establishment')) {
            abort(403, 'Unauthorized action.');
        }
        
        $qrCode = null;
        if ($establishment->qr_code_path && Storage::disk('public')->exists($establishment->qr_code_path)) {
            $qrCode = Storage::disk('public')->get($establishment->qr_code_path);
        } else {
            $publicUrl = route('public.establishment.show', $establishment->unique_id ?? 'pending');
            $renderer = new ImageRenderer(
                new RendererStyle(200),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);
            $qrCode = $writer->writeString($publicUrl);
        }

        // Mock outstanding payments
        $outstandingAmount = 5000.00; 

        return view('admin.establishments.show', compact('establishment', 'qrCode', 'outstandingAmount'));
    }

    public function details($id, RevenueService $revenueService)
    {
        $establishment = Establishment::areaRestricted()->with(['occupant', 'owner', 'creator', 'activityLogs', 'images', 'updateRequests'])->findOrFail($id);
        
        // Restrict to creator or 'view all establishment' permission
        if ($establishment->created_by !== auth()->id() && !auth()->user()->hasPermissionTo('view all establishment')) {
            abort(403, 'Unauthorized action.');
        }
        
        $qrCode = null;
        if ($establishment->qr_code_path && Storage::disk('public')->exists($establishment->qr_code_path)) {
            $qrCode = Storage::disk('public')->get($establishment->qr_code_path);
        } else {
            $publicUrl = route('public.establishment.show', $establishment->unique_id ?? 'pending');
            $renderer = new ImageRenderer(
                new RendererStyle(200),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);
            $qrCode = $writer->writeString($publicUrl);
        }

        $taxStatus = $revenueService->getEstablishmentTaxStatus($establishment);

        // Paginate the flat fees array (15 per page)
        $perPage  = 15;
        $page     = request()->input('fee_page', 1);
        $allFees  = $taxStatus['fees'];
        $pagedFees = new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($allFees, ($page - 1) * $perPage, $perPage),
            count($allFees),
            $perPage,
            $page,
            ['pageName' => 'fee_page', 'path' => request()->url(), 'query' => request()->query()]
        );
        $taxStatus['fees'] = $pagedFees;

        return view('admin.establishments.details', compact('establishment', 'qrCode', 'taxStatus'));
    }

    public function edit($id)
    {
        $establishment = Establishment::areaRestricted()->with(['occupant', 'activityLogs', 'updateRequests'])->findOrFail($id);
        
        // Find the latest approved update request that hasn't been completed yet
        $latestApprovedRequest = $establishment->updateRequests()
            ->where('status', 'approved')
            ->latest()
            ->first();

        $canExecuteUpdate = auth()->user()->hasPermissionTo('execute establishment update');
        
        $isCreator = $establishment->created_by === auth()->id();
        $canViewInvalid = auth()->user()->hasPermissionTo('view all invalid establishment');
        
        $isRejectedEdit = ($establishment->status === 'rejected' && ($isCreator || $canViewInvalid));
        $isApprovedRequestEdit = ($latestApprovedRequest && $canExecuteUpdate);

        if (!$isRejectedEdit && !$isApprovedRequestEdit) {
            abort(403, 'Unauthorized action. This establishment is not in a state that allows editing, or you do not have an approved update request.');
        }

        $establishmentTypes = EstablishmentType::where('status', true)->orderBy('value')->get();
        $establishmentSizes = EstablishmentSize::where('status', true)->orderBy('value')->get();
        $lgas = auth()->user()->getDesignatedLgasAndWards();
        
        return view('admin.establishments.edit', compact('establishment', 'establishmentTypes', 'establishmentSizes', 'lgas'));
    }

    public function update(Request $request, $id)
    {
        $establishment = Establishment::areaRestricted()->with(['occupant', 'images', 'updateRequests'])->findOrFail($id);

        // Find the latest approved update request that hasn't been completed yet
        $latestApprovedRequest = $establishment->updateRequests()
            ->where('status', 'approved')
            ->latest()
            ->first();

        $canExecuteUpdate = auth()->user()->hasPermissionTo('execute establishment update');
        
        $isCreator = $establishment->created_by === auth()->id();
        $canViewInvalid = auth()->user()->hasPermissionTo('view all invalid establishment');
        
        $isRejectedEdit = ($establishment->status === 'rejected' && ($isCreator || $canViewInvalid));
        $isApprovedRequestEdit = ($latestApprovedRequest && $canExecuteUpdate);

        if (!$isRejectedEdit && !$isApprovedRequestEdit) {
            abort(403, 'Unauthorized action. This establishment is not in a state that allows editing, or you do not have an approved update request.');
        }

        $isOwnerProvided = $request->anyFilled(['owner_name', 'owner_phone', 'owner_email', 'owner_gender', 'owner_nin']);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'establishment_type_id' => 'required|exists:establishment_types,id',
            'establishment_size_id' => 'required|exists:establishment_sizes,id',
            'lga' => 'required|string',
            'ward' => 'required|string',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
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
            'establishment_images' => 'nullable|array|max:3',
            'establishment_images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            'base_year' => 'required|integer|min:2015|max:' . date('Y'),
        ]);

        $user = auth()->user();
        if (!$user->hasRole('super-admin')) {
            $allowedLgasAndWards = $user->getDesignatedLgasAndWards();
            $allowedLga = $allowedLgasAndWards->firstWhere('name', $validated['lga'] ?? $request->lga);
            if (!$allowedLga) {
                return back()->withErrors(['lga' => 'You are not designated to work in the selected LGA.'])->withInput();
            }
            $allowedWard = $allowedLga->wards->firstWhere('name', $validated['ward'] ?? $request->ward);
            if (!$allowedWard) {
                return back()->withErrors(['ward' => 'You are not designated to work in the selected Ward.'])->withInput();
            }
        }

        $deletedIds = json_decode($request->input('deleted_image_ids', '[]'), true);
        $newImages = $request->file('establishment_images', []);
        
        $totalImages = $establishment->images->count() - count($deletedIds) + count($newImages);

        if ($totalImages < 1) {
            return back()->withErrors(['establishment_images' => 'At least one establishment image is required.'])->withInput();
        }
        if ($totalImages > 3) {
            return back()->withErrors(['establishment_images' => 'Maximum of 3 images allowed.'])->withInput();
        }

        return DB::transaction(function () use ($validated, $establishment, $request, $deletedIds, $newImages, $isApprovedRequestEdit, $latestApprovedRequest) {
            if ($establishment->occupant) {
                $establishment->occupant->update([
                    'name' => $validated['occupant_name'] ?? $establishment->occupant->name,
                    'phone' => $validated['occupant_phone'] ?? $establishment->occupant->phone,
                ]);
            } elseif (!empty($validated['occupant_name'])) {
                $occupant = Occupant::create([
                    'name' => $validated['occupant_name'],
                    'phone' => $validated['occupant_phone'] ?? null,
                ]);
                $establishment->occupant_id = $occupant->id;
            }

            if ($establishment->owner) {
                $establishment->owner->update([
                    'name' => $validated['owner_name'] ?? $establishment->owner->name,
                    'phone' => $validated['owner_phone'] ?? $establishment->owner->phone,
                    'email' => $validated['owner_email'] ?? $establishment->owner->email,
                    'gender' => $validated['owner_gender'] ?? $establishment->owner->gender,
                    'nin' => $validated['owner_nin'] ?? $establishment->owner->nin,
                ]);
            } elseif (!empty($validated['owner_name'])) {
                $owner = EstablishmentOwner::create([
                    'name' => $validated['owner_name'],
                    'phone' => $validated['owner_phone'] ?? null,
                    'email' => $validated['owner_email'] ?? null,
                    'gender' => $validated['owner_gender'] ?? null,
                    'nin' => $validated['owner_nin'] ?? null,
                ]);
                $establishment->owner_id = $owner->id;
            }

            $establishment->update([
                'name' => $validated['name'],
                'establishment_type_id' => $validated['establishment_type_id'],
                'establishment_size_id' => $validated['establishment_size_id'],
                'lga' => $validated['lga'],
                'ward' => $validated['ward'],
                'lat' => $validated['lat'],
                'lng' => $validated['lng'],
                'inside_metropolis' => $validated['inside_metropolis'],
                'street_address' => $validated['street_address'] ?? null,
                'house_number' => $validated['house_number'] ?? null,
                'city' => $validated['city'] ?? null,
                'postal_code' => $validated['postal_code'] ?? null,
                'status' => ($isApprovedRequestEdit) ? $establishment->status : 'pending',
                'base_year' => $validated['base_year'],
            ]);

            if ($isApprovedRequestEdit && $latestApprovedRequest) {
                $latestApprovedRequest->update([
                    'status' => 'completed',
                    'completed_by' => auth()->id(),
                    'completed_at' => now(),
                ]);
            }

            ActivityLog::create([
                'establishment_id' => $establishment->id,
                'user_id' => auth()->id(),
                'action_type' => 'update_completed',
                'remarks' => ($isApprovedRequestEdit ? 'Establishment details updated via approved update request.' : 'Establishment details were corrected and re-submitted for approval.')
            ]);

            // Handle Deletions
            if (!empty($deletedIds)) {
                $imagesToDelete = EstablishmentImage::whereIn('id', $deletedIds)->where('establishment_id', $establishment->id)->get();
                foreach ($imagesToDelete as $image) {
                    if (Storage::disk('public')->exists($image->image_path)) {
                        Storage::disk('public')->delete($image->image_path);
                    }
                    $image->delete();
                }
            }

            // Handle New Uploads
            if (!empty($newImages)) {
                foreach ($newImages as $image) {
                    $path = $image->store('establishments/' . $establishment->id, 'public');
                    EstablishmentImage::create([
                        'establishment_id' => $establishment->id,
                        'image_path' => $path,
                        'is_primary' => $establishment->images()->where('is_primary', true)->exists() ? false : true,
                        'lat' => $validated['lat'],
                        'lng' => $validated['lng'],
                        'device_info' => $request->header('User-Agent'),
                    ]);
                }
            }

            $redirectRoute = ($isApprovedRequestEdit) ? 'admin.establishments.approved' : 'admin.establishments.invalid';
            $message = ($isApprovedRequestEdit) ? 'Establishment details successfully updated.' : 'Establishment updated and re-submitted for approval.';

            return redirect()->route($redirectRoute)->with('success', $message);
        });
    }

    public function destroy($id)
    {
        $establishment = Establishment::areaRestricted()->findOrFail($id);
        $establishment->delete();
        return redirect()->back()->with('success', 'Establishment record deleted.');
    }
}
