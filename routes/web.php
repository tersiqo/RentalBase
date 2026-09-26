<?php

use App\Http\Controllers\Customer\CustomerHomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/client/jaya');
});

Route::get('/client/{subdomain}', [CustomerHomeController::class, 'index'])
    ->name('customer.home');