<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminThemeController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ReturnController;
use App\Http\Controllers\Admin\ShipmentController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\TenantRegistrationController;
use App\Http\Controllers\Customer\CustomerCheckoutController;
use App\Http\Controllers\Customer\CustomerHomeController;
use App\Http\Controllers\Customer\CustomerProductController;
use App\Http\Controllers\Landing\RentalBaseHomeController;
use App\Http\Controllers\PageController;
use App\Livewire\Customer\CartPage;
use Illuminate\Support\Facades\Route;

Route::get('/', [RentalBaseHomeController::class, 'index'])
    ->name('landing.home');

Route::get('/my-orders', [PageController::class, 'myOrders'])
    ->middleware('auth')
    ->name('customer.orders');

Route::get('/register-tenant', function () {
    return view('landing.register-tenant');
})->name('landing.register');
Route::get('/login', [TenantRegistrationController::class, 'showLogin'])->name('login');
Route::post('/login', [TenantRegistrationController::class, 'login']);
Route::post('/logout', [TenantRegistrationController::class, 'logout'])->name('logout');

Route::get('/register', [TenantRegistrationController::class, 'showRegister'])->name('tenant.register');
Route::post('/register', [TenantRegistrationController::class, 'register']);

Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'email'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');

Route::middleware('auth')->group(function () {
    Route::get('/setup-tenant', [TenantRegistrationController::class, 'showSetup'])->name('tenant.setup');
    Route::post('/setup-tenant', [TenantRegistrationController::class, 'setup']);
    Route::get('/setup-theme', [TenantRegistrationController::class, 'showThemeSetup'])->name('tenant.theme.setup');
    Route::post('/setup-theme', [TenantRegistrationController::class, 'setupTheme']);
    Route::get('/waiting-verification', [TenantRegistrationController::class, 'showWaiting'])->name('tenant.waiting');
});

Route::get('/client/{subdomain}', [CustomerHomeController::class, 'index'])
    ->name('customer.home');

Route::get('/client/{subdomain}/products/{product}', [CustomerProductController::class, 'show'])
    ->name('customer.product.show');

Route::middleware(['auth'])->group(function () {
    Route::get('/client/{subdomain}/checkout', [CustomerCheckoutController::class, 'create'])
        ->name('customer.checkout');
    Route::post('/client/{subdomain}/checkout', [CustomerCheckoutController::class, 'store'])
        ->name('customer.checkout.store');
    Route::get('/client/{subdomain}/checkout/{product}', [CustomerCheckoutController::class, 'direct'])
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
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('/products', fn () => back())->name('products.index');
    Route::get('/units', fn () => back())->name('units.index');
    Route::get('/reports', fn () => back())->name('reports.index');
    Route::get('/theme', [AdminThemeController::class, 'index'])->name('theme.index');
    Route::post('/theme', [AdminThemeController::class, 'update'])->name('theme.update');
    Route::get('/settings', fn () => back())->name('settings.index');
});

Route::get('/client/{subdomain}/cart', CartPage::class)
    ->name('customer.cart');

Route::get('/auth/google', [GoogleController::class, 'redirect'])
    ->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);
