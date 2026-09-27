<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
|
| Endpoint ini menggunakan session authentication Laravel.
| Karena aplikasinya adalah web-based Laravel Blade, session login
| digunakan sebagai mekanisme authentication utama.
|
*/

Route::middleware('web')->group(function () {

    // -------------------------------------------------------------
    // AUTHENTICATION
    // -------------------------------------------------------------

    Route::post('/register', [
        AuthController::class,
        'register',
    ])->name('api.register');

    Route::post('/login', [
        AuthController::class,
        'login',
    ])->name('api.login');

    Route::post('/logout', [
        AuthController::class,
        'logout',
    ])->middleware('auth')
      ->name('api.logout');

    Route::get('/me', [
        AuthController::class,
        'me',
    ])->middleware('auth')
      ->name('api.me');


    // -------------------------------------------------------------
    // ROLE GROUP
    // -------------------------------------------------------------
    //
    // Group ini disiapkan untuk endpoint berikutnya.
    // Belum diisi fitur transaksi/admin/owner.
    //

    Route::middleware([
        'auth',
        RoleMiddleware::class . ':customer',
    ])->prefix('customer')->group(function () {

        // Endpoint Customer akan ditambahkan
        // pada tahap pengembangan modul Customer.
    });


    Route::middleware([
        'auth',
        RoleMiddleware::class . ':admin_rental',
    ])->prefix('admin')->group(function () {

        // Endpoint Admin Rental akan ditambahkan
        // pada tahap pengembangan modul Admin Rental.
    });


    Route::middleware([
        'auth',
        RoleMiddleware::class . ':owner',
    ])->prefix('owner')->group(function () {

        // Endpoint Owner akan ditambahkan
        // pada tahap pengembangan modul Owner.
    });
});