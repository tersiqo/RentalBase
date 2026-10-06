<?php

use App\Http\Controllers\Customer\CustomerHomeController;
use App\Http\Controllers\Landing\RentalBaseHomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RentalBaseHomeController::class, 'index'])
    ->name('landing.home');

Route::get('/register-tenant', function () {
    return view('landing.register-tenant');
})->name('landing.register');

Route::get('/client/{subdomain}', [CustomerHomeController::class, 'index'])
    ->name('customer.home');

Route::get('/client/{subdomain}/products/{product}', [\App\Http\Controllers\Customer\CustomerProductController::class, 'show'])
    ->name('customer.product.show');

Route::get('/client/{subdomain}/checkout/{product}', [\App\Http\Controllers\Customer\CustomerCheckoutController::class, 'create'])
    ->name('customer.checkout');