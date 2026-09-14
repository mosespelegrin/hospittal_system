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

require __DIR__.'/auth.php';