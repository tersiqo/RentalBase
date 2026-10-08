<?php

use App\Http\Controllers\Admin\AdminDashboardController;
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

Route::prefix('admin')->middleware(['admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AdminDashboardController::class, 'logout'])->name('logout');
    Route::get('/orders', fn () => back())->name('orders.index');
    Route::get('/shipments', fn () => back())->name('shipments.index');
    Route::get('/returns', fn () => back())->name('returns.index');
    Route::get('/condition-checks', fn () => back())->name('condition-checks.index');
    Route::get('/damage-cases', fn () => back())->name('damage-cases.index');
    Route::get('/categories', fn () => back())->name('categories.index');
    Route::get('/products', fn () => back())->name('products.index');
    Route::get('/units', fn () => back())->name('units.index');
    Route::get('/reports', fn () => back())->name('reports.index');
    Route::get('/settings', fn () => back())->name('settings.index');
});
Route::get('/client/{subdomain}/cart', \App\Livewire\Customer\CartPage::class)
    ->name('customer.cart');
