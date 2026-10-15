<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminThemeController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ReturnController;
use App\Http\Controllers\Admin\ShipmentController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\TenantRegistrationController;
use App\Http\Controllers\Customer\CustomerCheckoutController;
use App\Http\Controllers\Customer\CustomerHomeController;
use App\Http\Controllers\Customer\CustomerProductController;
use App\Http\Controllers\Landing\RentalBaseHomeController;
use App\Http\Controllers\Owner\OwnerDashboardController;
use App\Http\Controllers\Owner\OwnerRegistrationController;
use App\Http\Controllers\Owner\OwnerTenantController;
use App\Http\Controllers\PageController;
use App\Livewire\Customer\CartPage;
use Illuminate\Support\Facades\Route;

Route::get('/', [RentalBaseHomeController::class, 'index'])
    ->name('landing.home');

Route::get('/my-orders', [PageController::class, 'myOrders'])
    ->middleware('auth')
    ->name('customer.orders');

Route::get('/register-tenant', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'showRegister'])->name('tenant.register');
Route::post('/register-tenant', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'handleRegistration']);

Route::get('/login', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'showLogin'])->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'logout'])->name('logout');

Route::get('/register', fn () => redirect()->route('tenant.register'));
Route::post('/register', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'handleRegistration']);

Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'email'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');

Route::get('/waiting-verification', fn () => redirect()->route('login'))->name('tenant.waiting');

Route::middleware('auth')->group(function () {
    Route::get('/setup-tenant', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'showSetup'])->name('tenant.setup.get');
    Route::post('/setup-tenant', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'setup'])->name('tenant.setup');
    Route::get('/setup-theme', fn () => redirect()->route('login'))->name('tenant.theme.setup.get');
    Route::post('/setup-theme', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'setupTheme'])->name('tenant.theme.setup');
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
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{order}/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');
    Route::post('/orders/{order}/reject', [OrderController::class, 'reject'])->name('orders.reject');
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

Route::prefix('owner')->middleware(['owner'])->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [OwnerDashboardController::class, 'logout'])->name('logout');

    Route::get('/registrations', [OwnerRegistrationController::class, 'index'])->name('registrations.index');
    Route::post('/registrations/{id}/verify', [OwnerRegistrationController::class, 'verify'])->name('registrations.verify');
    Route::post('/registrations/{id}/reject', [OwnerRegistrationController::class, 'reject'])->name('registrations.reject');

    Route::get('/tenants', [OwnerTenantController::class, 'index'])->name('tenants.index');
    Route::put('/tenants/{client}', [OwnerTenantController::class, 'update'])->name('tenants.update');
    Route::post('/tenants/{client}/status', [OwnerTenantController::class, 'updateStatus'])->name('tenants.status');
    Route::put('/tenants/{client}/subscription', [OwnerTenantController::class, 'updateSubscription'])->name('tenants.subscription');
});

Route::get('/client/{subdomain}/cart', CartPage::class)
    ->name('customer.cart');

Route::get('/auth/google', [GoogleController::class, 'redirect'])
    ->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);
