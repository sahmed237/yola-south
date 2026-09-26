<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_establishments' => Establishment::areaRestricted()->count(),
            'pending_approvals' => Establishment::areaRestricted()->where('status', 'pending')->count(),
            'total_revenue' => Payment::whereHas('establishment', function($q) {
                $q->areaRestricted();
            })->where('status', 'success')->sum('amount'),
            'total_users' => User::count(),
        ];

        $recentEstablishments = Establishment::areaRestricted()->latest()->take(5)->get();
        $recentPayments = Payment::whereHas('establishment', function($q) {
            $q->areaRestricted();
        })->latest()->take(5)->with('establishment')->get();

        return view('admin.dashboard', compact('stats', 'recentEstablishments', 'recentPayments'));
    }
}
