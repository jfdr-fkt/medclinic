<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

// Public auth routes — no.back keeps the browser from showing a cached login page
// after the user has already authenticated (back button bounces them to dashboard).
Route::get('/', fn() => redirect()->route('login'))->middleware('no.back');
Route::get('/login',    [AuthController::class, 'showLogin'])->name('login')->middleware('no.back');
Route::post('/login',   [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register')->middleware('no.back');
Route::post('/register',[AuthController::class, 'register'])->name('register.post');
Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');

// Force-password-change form — only requires auth so the middleware can serve it
// without redirecting in a loop. The middleware itself is what locks every OTHER
// authenticated route until the flag clears.
Route::middleware(['auth', 'no.back'])->group(function () {
    Route::get('/password/force',  [AuthController::class, 'showForcePassword'])->name('password.force');
    Route::post('/password/force', [AuthController::class, 'updateForcePassword'])->name('password.force.update');
});

Route::middleware(['auth', 'no.back', 'force.pw.change'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile',                [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',                [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password',       [ProfileController::class, 'password'])->name('profile.password');
    Route::post('/profile/status',        [ProfileController::class, 'status'])->name('profile.status');
    Route::post('/profile/theme',         [ProfileController::class, 'theme'])->name('profile.theme');
    Route::put('/profile/appearance',     [ProfileController::class, 'appearance'])->name('profile.appearance');
    Route::delete('/profile/avatar',      [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');

    // Patients
    Route::get('/patients',                   [PatientController::class, 'index'])->name('patients.index');
    Route::post('/patients',                  [PatientController::class, 'store'])->name('patients.store');
    Route::get('/patients/{patient}',         [PatientController::class, 'show'])->name('patients.show');
    Route::put('/patients/{patient}',         [PatientController::class, 'update'])->name('patients.update');
    Route::delete('/patients/{patient}',      [PatientController::class, 'destroy'])->name('patients.destroy');
    Route::post('/patients/{patient}/pin',    [PatientController::class, 'pin'])->name('patients.pin');

    // Medicines / Inventory
    // CRITICAL ordering note: literal sub-paths (locations, create, archive) MUST come BEFORE
    // the wildcard /medicines/{medicine} routes — Laravel matches top-down, and the wildcard
    // would happily consume "locations" as an ID, causing a 404 from model binding.
    Route::get('/medicines',                          [MedicineController::class, 'index'])->name('medicines.index');
    Route::get('/medicines/create',                   [MedicineController::class, 'create'])->name('medicines.create');
    Route::post('/medicines',                         [MedicineController::class, 'store'])->name('medicines.store');
    // Storage location management (admin / clinic_head)
    Route::get('/medicines/locations',                [MedicineController::class, 'locationsIndex'])->name('medicines.locations.index');
    Route::post('/medicines/locations',               [MedicineController::class, 'storeLocation'])->name('medicines.locations.store');
    Route::put('/medicines/locations/{location}',     [MedicineController::class, 'updateLocation'])->name('medicines.locations.update');
    Route::delete('/medicines/locations/{location}',  [MedicineController::class, 'destroyLocation'])->name('medicines.locations.destroy');
    // Wildcards below this line
    Route::get('/medicines/{medicine}',               [MedicineController::class, 'show'])->name('medicines.show');
    Route::put('/medicines/{medicine}',               [MedicineController::class, 'update'])->name('medicines.update');
    Route::delete('/medicines/{medicine}',            [MedicineController::class, 'destroy'])->name('medicines.destroy');
    Route::post('/medicines/{medicine}/dispense',     [MedicineController::class, 'dispense'])->name('medicines.dispense');
    Route::post('/medicines/{medicine}/archive',      [MedicineController::class, 'archive'])->name('medicines.archive');
    Route::post('/medicines/{medicine}/unarchive',    [MedicineController::class, 'unarchive'])->name('medicines.unarchive');

    // Smart Scan
    Route::get('/scan',         [ScanController::class, 'index'])->name('scan.index');
    Route::post('/scan/lookup', [ScanController::class, 'lookup'])->name('scan.lookup');
    Route::post('/scan/save',   [ScanController::class, 'storeScan'])->name('scan.save');

    // Audit log — admin only, surfaces oversight-role access to patient records
    Route::get('/audit',                          [AuditLogController::class, 'index'])->name('audit.index');

    // Staff
    Route::get('/staff',                          [StaffController::class, 'index'])->name('staff.index');
    Route::post('/staff',                         [StaffController::class, 'store'])->name('staff.store');
    Route::get('/staff/{user}',                   [StaffController::class, 'show'])->name('staff.show');
    Route::delete('/staff/{user}',                [StaffController::class, 'destroy'])->name('staff.destroy');
    Route::get('/staff/{user}/shift-on',          [StaffController::class, 'shiftOn'])->name('staff.shifts.lookup');
    Route::post('/staff/shifts/store',            [StaffController::class, 'storeShift'])->name('staff.shifts.store');

    // Chat
    Route::get('/chat',                           [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/send',                     [ChatController::class, 'send'])->name('chat.send');
    Route::get('/chat/messages',                  [ChatController::class, 'messages'])->name('chat.messages');
    Route::get('/chat/sidebar',                   [ChatController::class, 'sidebar'])->name('chat.sidebar');
    Route::post('/chat/groups',                   [ChatController::class, 'storeGroup'])->name('chat.groups.store');
    Route::post('/chat/groups/{group}/add',       [ChatController::class, 'addToGroup'])->name('chat.groups.add');
    Route::delete('/chat/messages/{message}',    [ChatController::class, 'destroyMessage'])->name('chat.messages.destroy');

    // AJAX API endpoints
    Route::prefix('api')->group(function () {
        Route::get('/medicines/lookup/{code}', [MedicineController::class, 'lookupByCode']);
        Route::get('/patients/search',         [PatientController::class, 'search']);
    });
});
