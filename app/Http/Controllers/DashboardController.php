<?php
namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Today's queue snapshot — drives both the headline stat and the live counts panel.
        $todayVisits = Visit::whereDate('checked_in_at', today())->get();
        $queueCounts = [
            'total' => $todayVisits->count(),
            'waiting' => $todayVisits->where('status', 'waiting')->count(),
            'with_nurse' => $todayVisits->where('status', 'with_nurse')->count(),
            'with_doctor' => $todayVisits->where('status', 'with_doctor')->count(),
            'pharmacy' => $todayVisits->where('status', 'pharmacy')->count(),
            'completed' => $todayVisits->where('status', 'completed')->count(),
            'in_progress' => $todayVisits->whereIn('status', ['with_nurse','with_doctor','pharmacy'])->count(),
        ];
        $todayPatients = $queueCounts['total'];

        // Exclude expired medicines (those whose latestInventory.expiration_date is past)
        $notExpired = fn($q) => $q->whereDoesntHave('latestInventory', fn($s) => $s->where('expiration_date', '<', now()));

        $lowStockMedicines = Medicine::with(['latestInventory', 'location'])
            ->where(function ($q) use ($notExpired) {
                $q->whereHas('inventories', fn($s) => $s->whereColumn('quantity', '<=', 'min_stock_level'));
                $notExpired($q);
            })
            ->take(5)->get();

        $expiringSoon = Medicine::with(['latestInventory', 'location'])
            ->whereHas('inventories', fn($q) =>
                $q->where('expiration_date', '<=', now()->addDays(30))
                  ->where('expiration_date', '>=', now())
            )->take(5)->get();

        // Match the same online definition the staff table uses: recent activity AND not
        // explicitly set to offline. Otherwise users who just logged out still show as online
        // for the next five minutes.
        $onlineStaff = User::where('last_seen_at', '>=', now()->subMinutes(5))
            ->where('status', '!=', 'offline')
            ->where('id', '!=', Auth::id())
            ->get();

        $myPinnedPatients = Auth::user()->pinnedPatients()
            ->with(['nurse', 'doctor'])
            ->get();

        return view('dashboard', compact(
            'todayPatients', 'queueCounts', 'lowStockMedicines', 'expiringSoon', 'onlineStaff', 'myPinnedPatients'
        ));
    }
}
