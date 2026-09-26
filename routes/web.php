<?php

use App\Http\Controllers\CustomerHomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CustomerHomeController::class, 'index']);