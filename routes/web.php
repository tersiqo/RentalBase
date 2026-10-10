<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ShipmentController;
use App\Http\Controllers\Admin\ReturnController;
use App\Http\Controllers\Customer\CustomerHomeController;
use App\Http\Controllers\Landing\RentalBaseHomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RentalBaseHomeController::class, 'index'])
    ->name('landing.home');

Route::get('/my-orders', [PageController::class, 'myOrders'])
    ->name('customer.orders');

Route::get('/register-tenant', function () {
    return view('landing.register-tenant');
})->name('landing.register');
Route::get('/login', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'showLogin'])->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'logout'])->name('logout');

Route::get('/register', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'showRegister'])->name('tenant.register');
Route::post('/register', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'register']);

Route::get('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'request'])->name('password.request');
Route::post('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'email'])->name('password.email');
Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\PasswordResetController::class, 'reset'])->name('password.reset');
Route::post('/reset-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'update'])->name('password.update');

Route::middleware('auth')->group(function () {
    Route::get('/setup-tenant', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'showSetup'])->name('tenant.setup');
    Route::post('/setup-tenant', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'setup']);
    Route::get('/setup-theme', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'showThemeSetup'])->name('tenant.theme.setup');
    Route::post('/setup-theme', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'setupTheme']);
    Route::get('/waiting-verification', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'showWaiting'])->name('tenant.waiting');
});

Route::get('/client/{subdomain}', [CustomerHomeController::class, 'index'])
    ->name('customer.home');

Route::get('/client/{subdomain}/products/{product}', [\App\Http\Controllers\Customer\CustomerProductController::class, 'show'])
    ->name('customer.product.show');

Route::middleware(['auth'])->group(function () {
    Route::get('/client/{subdomain}/checkout', [\App\Http\Controllers\Customer\CustomerCheckoutController::class, 'create'])
        ->name('customer.checkout');
    Route::post('/client/{subdomain}/checkout', [\App\Http\Controllers\Customer\CustomerCheckoutController::class, 'store'])
        ->name('customer.checkout.store');
    Route::get('/client/{subdomain}/checkout/{product}', [\App\Http\Controllers\Customer\CustomerCheckoutController::class, 'direct'])
        ->name('customer.checkout.direct');
});

Route::prefix('admin')->middleware(['admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AdminDashboardController::class, 'logout'])->name('logout');
    Route::get('/orders', fn () => back())->name('orders.index');
    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::post('/shipments/{order}/process', [ShipmentController::class, 'process'])->name('shipments.process');
    Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
    Route::post('/returns/{order}/process', [ReturnController::class, 'process'])->name('returns.process');
    Route::get('/condition-checks', fn () => back())->name('condition-checks.index');
    Route::get('/damage-cases', fn () => back())->name('damage-cases.index');
    Route::get('/categories', fn () => back())->name('categories.index');
    Route::get('/products', fn () => back())->name('products.index');
    Route::get('/units', fn () => back())->name('units.index');
    Route::get('/reports', fn () => back())->name('reports.index');
    Route::get('/theme', [\App\Http\Controllers\Admin\AdminThemeController::class, 'index'])->name('theme.index');
    Route::post('/theme', [\App\Http\Controllers\Admin\AdminThemeController::class, 'update'])->name('theme.update');
    Route::get('/settings', fn () => back())->name('settings.index');
});

Route::get('/client/{subdomain}/cart', \App\Livewire\Customer\CartPage::class)
    ->name('customer.cart');

Route::get('/auth/google', [GoogleController::class, 'redirect'])
    ->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);
