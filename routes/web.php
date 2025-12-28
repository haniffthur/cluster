<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\DeviceController; // Gates
use App\Http\Controllers\BillController;
use App\Http\Controllers\GateLogController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root ke login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes (Admin Panel)
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- MODULE MASTER DATA ---
    
    // 1. Penghuni (Residents) + Import CSV
    Route::get('/residents/import', [ResidentController::class, 'importForm'])->name('residents.import');
    Route::post('/residents/import', [ResidentController::class, 'importProcess'])->name('residents.import.process');
    Route::resource('residents', ResidentController::class);

    // 2. Kartu Akses (RFID)
    Route::resource('cards', CardController::class);

    // --- MODULE KEAMANAN (GATE) ---
    
    // 3. Gate & Kamera (Devices)
    Route::resource('gates', DeviceController::class)->parameters(['gates' => 'device']);
    Route::post('/gates/{device}/ios', [App\Http\Controllers\DeviceIoController::class, 'store'])->name('gates.ios.store');
    // Hapus IO
    Route::delete('/gates/ios/{id}', [App\Http\Controllers\DeviceIoController::class, 'destroy'])->name('gates.ios.destroy');
    
    // 4. Riwayat Akses (Logs) - Read Only & Delete
    Route::resource('access-logs', GateLogController::class)->only(['index', 'destroy']);
    Route::get('/api/logs-ajax', [App\Http\Controllers\GateLogController::class, 'getLogsAjax'])->name('api.logs.ajax');

    // --- MODULE KEUANGAN ---
    
    // 5. Data Tunggakan (Filter Status: Belum Bayar)
    Route::get('/arrears', [BillController::class, 'arrears'])->name('tunggakan.index');
    
    // 6. Tagihan IPL (Full CRUD)
    Route::put('/bills/{bill}/pay', [BillController::class, 'markAsPaid'])->name('bills.pay');
    Route::post('/bills/generate', [BillController::class, 'generateBills'])->name('bills.generate');
    Route::resource('bills', BillController::class)->names(['index' => 'ipl_bills.index']);

});