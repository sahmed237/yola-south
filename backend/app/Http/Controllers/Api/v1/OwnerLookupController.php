<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EstablishmentOwner;
use Illuminate\Http\Request;

class OwnerLookupController extends Controller
{
    public function search(Request $request)
    {
        $nin = $request->query('nin');
        if (!$nin) {
            return response()->json([]);
        }

        $owners = EstablishmentOwner::where('nin', 'like', '%' . $nin . '%')
            ->limit(5)
            ->get();

        return response()->json($owners);
    }
}
