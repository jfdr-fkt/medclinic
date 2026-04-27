<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
})->name('login');

// Authentication Routes
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Patients
    Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
    Route::post('/patients', [PatientController::class, 'store'])->name('patients.store');
    Route::get('/patients/{patient}', [PatientController::class, 'show'])->name('patients.show');
    Route::put('/patients/{patient}', [PatientController::class, 'update'])->name('patients.update');
    Route::delete('/patients/{patient}', [PatientController::class, 'destroy'])->name('patients.destroy');
    Route::post('/patients/{patient}/pin', [PatientController::class, 'pin'])->name('patients.pin');
    
    // Medicines/Inventory
    Route::get('/medicines', [MedicineController::class, 'index'])->name('medicines.index');
    Route::post('/medicines', [MedicineController::class, 'store'])->name('medicines.store');
    Route::get('/medicines/create', [MedicineController::class, 'create'])->name('medicines.create');
    Route::get('/medicines/{medicine}', [MedicineController::class, 'show'])->name('medicines.show');
    Route::put('/medicines/{medicine}', [MedicineController::class, 'update'])->name('medicines.update');
    
    // Smart Scan
    Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
    Route::post('/scan/lookup', [ScanController::class, 'lookup'])->name('scan.lookup');
    
    // Staff
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/{staff}', [StaffController::class, 'show'])->name('staff.show');

    // Staff Routes
Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
Route::post('/staff/shifts/store', [StaffController::class, 'storeShift'])->name('staff.shifts.store');
Route::post('/staff/{user}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggleStatus');
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Medicine Locations
Route::post('/medicines/locations/store', [MedicineController::class, 'storeLocation'])
     ->name('medicines.locations.store');

// Staff Shifts
Route::post('/staff/shifts/store', [StaffController::class, 'storeShift'])
     ->name('staff.shifts.store');

});

// API Routes for AJAX
Route::middleware(['auth'])->group(function () {
    Route::get('/api/medicines/lookup/{code}', [MedicineController::class, 'lookupByCode']);
    Route::post('/api/medicines', [MedicineController::class, 'store']);
    Route::get('/api/patients/search', [PatientController::class, 'search']);
});