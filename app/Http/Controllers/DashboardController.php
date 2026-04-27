<?php
namespace App\Http\Controllers;
use App\Models\Patient;
use App\Models\Medicine;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
class DashboardController extends Controller {
   public function index() {
   
    $user = \App\Models\User::first();
    if (!$user) {
        $user = new \App\Models\User(['id' => 1, 'name' => 'Dev User', 'email' => 'dev@clinic.com', 'role' => 'admin']);
    }
    \Illuminate\Support\Facades\Auth::login($user);

    $todayPatients = \App\Models\Patient::whereDate('last_visit', today())->count();
    $lowStockMedicines = \App\Models\Medicine::with(['latestInventory', 'location'])
        ->whereHas('inventories', fn($q) => $q->whereColumn('quantity', '<=', 'min_stock_level')->orWhere('quantity', 0))
        ->orWhereDoesntHave('inventories')->take(5)->get();
    $expiringSoon = \App\Models\Medicine::with(['latestInventory', 'location'])
        ->whereHas('inventories', fn($q) => $q->where('expiration_date', '<=', now()->addDays(30))->where('expiration_date', '>=', now()))
        ->take(5)->get();
    $onlineStaff = \App\Models\User::where('last_seen_at', '>=', now()->subMinutes(5))->where('id', '!=', $user->id)->get();
    $myPinnedPatients = collect([]);

    return view('dashboard', compact('todayPatients', 'lowStockMedicines', 'expiringSoon', 'onlineStaff', 'myPinnedPatients'));
}
}