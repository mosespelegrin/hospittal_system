<?php

use App\Http\Controllers\PatientController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PatientController::class, 'welcome'])->name('home');

Route::get('/consultation', [PatientController::class, 'create'])->name('patient.register');
Route::post('/consultation', [PatientController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('patient.store');

// Signed so only someone holding the link just generated at booking time
// (not anyone who can guess/increment a patient id) can view this page.
Route::get('/consultation/success/{patient}', [PatientController::class, 'success'])
    ->middleware('signed')
    ->name('patient.success');

Route::get('/privacy', [PatientController::class, 'privacy'])->name('privacy');
Route::get('/sitemap.xml', [PatientController::class, 'sitemap'])->name('sitemap');

// Lightweight health check (database included) for load balancers/monitors.
Route::get('/health', function () {
    try {
        \Illuminate\Support\Facades\DB::select('select 1');
    } catch (\Throwable $e) {
        report($e);

        return response()->json(['status' => 'error'], 503);
    }

    return response()->json(['status' => 'ok']);
})->name('health');

require __DIR__.'/auth.php';