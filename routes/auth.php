<?php

use Illuminate\Support\Facades\Route;

// Staff sign in through the Filament panel (/admin); the public site has no
// user accounts, so the Breeze register/login/profile routes are disabled.
// This named route is what Laravel's guest redirect uses.
Route::redirect('login', '/admin/login')->name('login');
