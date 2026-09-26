<?php

use App\Http\Controllers\Customer\CustomerHomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CustomerHomeController::class, 'index']);