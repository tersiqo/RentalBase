<?php

use App\Http\Controllers\Customer\CustomerHomeController;
use App\Http\Controllers\Landing\RentalBaseHomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RentalBaseHomeController::class, 'index'])
    ->name('landing.home');

Route::get('/client/{subdomain}', [CustomerHomeController::class, 'index'])
    ->name('customer.home');

Route::get('/client/{subdomain}/checkout/{product}', [\App\Http\Controllers\Customer\CustomerCheckoutController::class, 'create'])
    ->name('customer.checkout');