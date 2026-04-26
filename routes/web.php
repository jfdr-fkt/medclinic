<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ScanController;

Route::get('/login',[AuthController::class,'showLogin'])->name('login');
Route::post('/login',[AuthController::class,'login']);
Route::post('/logout',[AuthController::class,'logout'])->name('logout');

Route::middleware(['auth'])->group(function(){
    Route::get('/',[DashboardController::class,'index'])->name('dashboard');
    Route::get('/patients',[PatientController::class,'index'])->name('patients.index');
    Route::post('/patients',[PatientController::class,'store'])->name('patients.store');
    Route::post('/patients/{patient}/pin',[PatientController::class,'togglePin'])->name('patients.pin');
    Route::get('/medicines',[MedicineController::class,'index'])->name('medicines.index');
    Route::post('/medicines',[MedicineController::class,'store'])->name('medicines.store');
    Route::post('/medicines/locations',[MedicineController::class,'storeLocation'])->name('medicines.locations.store');
    Route::get('/staff',[StaffController::class,'index'])->name('staff.index');
    Route::post('/staff/shifts',[StaffController::class,'storeShift'])->name('staff.shifts.store');
    Route::get('/scan',[ScanController::class,'index'])->name('scan.index');
    Route::post('/scan/lookup',[ScanController::class,'lookup'])->name('scan.lookup');
});