<?php
namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $todayPatients = Patient::whereDate('last_visit', today())->count();

        $lowStockMedicines = Medicine::with(['latestInventory', 'location'])
            ->whereHas('inventories', fn($q) => $q->whereColumn('quantity', '<=', 'min_stock_level'))
            ->orWhereDoesntHave('inventories')
            ->take(5)->get();

        $expiringSoon = Medicine::with(['latestInventory', 'location'])
            ->whereHas('inventories', fn($q) =>
                $q->where('expiration_date', '<=', now()->addDays(30))
                  ->where('expiration_date', '>=', now())
            )->take(5)->get();

        $onlineStaff = User::where('last_seen_at', '>=', now()->subMinutes(5))
            ->where('id', '!=', Auth::id())
            ->get();

        $myPinnedPatients = Auth::user()->pinnedPatients()
            ->with(['nurse', 'doctor'])
            ->get();

        return view('dashboard', compact(
            'todayPatients', 'lowStockMedicines', 'expiringSoon', 'onlineStaff', 'myPinnedPatients'
        ));
    }
}
