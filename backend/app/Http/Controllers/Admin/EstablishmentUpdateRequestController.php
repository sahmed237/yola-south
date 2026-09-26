<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\EstablishmentUpdateRequest;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstablishmentUpdateRequestController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermissionTo('approve establishment update')) {
            abort(403);
        }

        $requests = EstablishmentUpdateRequest::with(['establishment', 'requester'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(25);

        return view('admin.establishment_update_requests.index', compact('requests'));
    }

    public function show($id)
    {
        if (!auth()->user()->hasPermissionTo('approve establishment update')) {
            abort(403);
        }

        $updateRequest = EstablishmentUpdateRequest::with(['establishment.occupant', 'establishment.owner', 'establishment.creator', 'establishment.activityLogs', 'establishment.images', 'requester'])->findOrFail($id);
        $establishment = $updateRequest->establishment;

        $qrCode = null;
        if ($establishment->qr_code_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($establishment->qr_code_path)) {
            $qrCode = \Illuminate\Support\Facades\Storage::disk('public')->get($establishment->qr_code_path);
        } else {
            $publicUrl = route('public.establishment.show', $establishment->unique_id ?? 'pending');
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );
            $writer = new \BaconQrCode\Writer($renderer);
            $qrCode = $writer->writeString($publicUrl);
        }

        return view('admin.establishment_update_requests.show', compact('updateRequest', 'establishment', 'qrCode'));
    }

    public function store(Request $request, $establishmentId)
    {
        if (!auth()->user()->hasPermissionTo('request establishment update')) {
            abort(403);
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $establishment = Establishment::findOrFail($establishmentId);

        if ($establishment->status !== 'approved') {
            return back()->with('error', 'Update requests can only be submitted for approved establishments.');
        }

        // Check if there's already an active request
        $exists = EstablishmentUpdateRequest::where('establishment_id', $establishmentId)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'There is already an active update request for this establishment.');
        }

        DB::transaction(function () use ($establishmentId, $request) {
            EstablishmentUpdateRequest::create([
                'establishment_id' => $establishmentId,
                'user_id' => auth()->id(),
                'reason' => $request->reason,
                'status' => 'pending',
            ]);

            ActivityLog::create([
                'establishment_id' => $establishmentId,
                'user_id' => auth()->id(),
                'action_type' => 'update_requested',
                'remarks' => $request->reason,
            ]);
        });

        return back()->with('success', 'Update request submitted for approval.');
    }

    public function approve(Request $request, $id)
    {
        if (!auth()->user()->hasPermissionTo('approve establishment update')) {
            abort(403);
        }

        $updateRequest = EstablishmentUpdateRequest::findOrFail($id);
        
        DB::transaction(function () use ($updateRequest, $request) {
            $updateRequest->update([
                'status' => 'approved',
                'approver_id' => auth()->id(),
                'approval_remarks' => $request->remarks,
                'approved_at' => now(),
            ]);

            ActivityLog::create([
                'establishment_id' => $updateRequest->establishment_id,
                'user_id' => auth()->id(),
                'action_type' => 'update_authorized',
                'remarks' => $request->remarks ?? 'Update request authorized.',
            ]);
        });

        return redirect()->route('admin.establishment-update-requests.index')->with('success', 'Update request approved.');
    }

    public function reject(Request $request, $id)
    {
        if (!auth()->user()->hasPermissionTo('approve establishment update')) {
            abort(403);
        }

        $updateRequest = EstablishmentUpdateRequest::findOrFail($id);
        
        DB::transaction(function () use ($updateRequest, $request) {
            $updateRequest->update([
                'status' => 'rejected',
                'approver_id' => auth()->id(),
                'approval_remarks' => $request->remarks,
            ]);

            ActivityLog::create([
                'establishment_id' => $updateRequest->establishment_id,
                'user_id' => auth()->id(),
                'action_type' => 'update_denied',
                'remarks' => $request->remarks ?? 'Update request denied.',
            ]);
        });

        return redirect()->route('admin.establishment-update-requests.index')->with('success', 'Update request rejected.');
    }
}
