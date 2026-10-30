<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\Admin\AdminThemeController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\TenantProfileController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ReturnController;
use App\Http\Controllers\Admin\ShipmentController;
use App\Http\Controllers\Admin\ConditionCheckController;
use App\Http\Controllers\Admin\DamageCaseController;
use App\Http\Controllers\Admin\EquipmentUnitController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\TenantRegistrationController;
use App\Http\Controllers\Customer\CustomerCheckoutController;
use App\Http\Controllers\Customer\CustomerHomeController;
use App\Http\Controllers\Customer\CustomerProductController;
use App\Http\Controllers\Customer\DamageReportController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Landing\RentalBaseHomeController;
use App\Http\Controllers\Owner\OwnerAuthController;
use App\Http\Controllers\Owner\OwnerDashboardController;
use App\Http\Controllers\Owner\OwnerRegistrationController;
use App\Http\Controllers\Owner\OwnerTenantController;
use App\Http\Controllers\Owner\SubscriptionController;
use App\Http\Controllers\Owner\ActivityLogController as OwnerActivityLogController;
use App\Http\Controllers\PageController;
use App\Livewire\Customer\CartPage;
use Illuminate\Support\Facades\Route;

Route::get('/', [RentalBaseHomeController::class, 'index'])
    ->name('landing.home');

Route::get('/my-orders', [PageController::class, 'myOrders'])
    ->middleware('auth')
    ->name('customer.orders');

Route::middleware('auth')->group(function () {
    Route::get('/my-orders/{order}', [PageController::class, 'orderDetail'])->name('customer.orders.show');
    Route::post('/my-orders/{order}/cancel', [PageController::class, 'cancelOrder'])->name('customer.orders.cancel');
    Route::post('/my-orders/{order}/receive', [PageController::class, 'receiveOrder'])->name('customer.orders.receive');
    Route::get('/my-orders/{order}/return', [PageController::class, 'returnForm'])->name('customer.orders.return.form');
    Route::post('/my-orders/{order}/return', [PageController::class, 'returnOrder'])->name('customer.orders.return');
    Route::post('/my-orders/{order}/payment', [PageController::class, 'uploadPayment'])->name('customer.orders.payment');
    Route::post('/my-orders/{order}/identity', [PageController::class, 'reuploadIdentity'])->name('customer.orders.identity');
    Route::get('/my-orders/{order}/damage', [DamageReportController::class, 'show'])->name('customer.orders.damage');
    Route::post('/my-orders/{order}/damage/respond', [DamageReportController::class, 'respond'])->name('customer.orders.damage.respond');
    Route::post('/my-orders/{order}/damage/pay', [DamageReportController::class, 'pay'])->name('customer.orders.damage.pay');

    // Customer Notification Hub
    Route::get('/notifications', [\App\Http\Controllers\Customer\NotificationController::class, 'index'])->name('customer.notifications.index');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\Customer\NotificationController::class, 'markAsRead'])->name('customer.notifications.read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Customer\NotificationController::class, 'markAllAsRead'])->name('customer.notifications.mark-all-read');

    // Customer Profile Global Alias
    Route::get('/profile', [ProfileController::class, 'showGlobal'])->name('customer.profile.global');
});

Route::get('/register-tenant', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'showRegister'])->name('tenant.register');
Route::post('/register-tenant', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'handleRegistration']);

Route::get('/login', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'showLogin'])->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'logout'])->name('logout');

Route::get('/register', fn (\Illuminate\Http\Request $request) => redirect()->route('login', array_merge(['tab' => 'register'], $request->query())))->name('register');
Route::post('/register', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'registerCustomer'])->name('register.submit');

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
    Route::get('/client/{subdomain}/notifications', [\App\Http\Controllers\Customer\NotificationController::class, 'index'])
        ->name('customer.notifications.tenant');
    Route::get('/client/{subdomain}/profile', [ProfileController::class, 'show'])
        ->name('customer.profile');
    Route::put('/client/{subdomain}/profile', [ProfileController::class, 'updateProfile'])
        ->name('customer.profile.update');
    Route::put('/client/{subdomain}/profile/password', [ProfileController::class, 'updatePassword'])
        ->name('customer.profile.password');
});

Route::prefix('admin')->middleware(['admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AdminDashboardController::class, 'logout'])->name('logout');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');
    Route::post('/orders/{order}/reject', [OrderController::class, 'reject'])->name('orders.reject');
    Route::post('/orders/{order}/allocate', [OrderController::class, 'allocate'])->name('orders.allocate');
    Route::delete('/orders/{order}/allocate/{orderItemUnit}', [OrderController::class, 'deallocate'])->name('orders.deallocate');
    Route::post('/orders/{order}/refund', [OrderController::class, 'processRefund'])->name('orders.refund');
    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::post('/shipments/{order}/process', [ShipmentController::class, 'process'])->name('shipments.process');
    Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
    Route::post('/returns/{order}/process', [ReturnController::class, 'process'])->name('returns.process');
    
    Route::get('/condition-checks', [ConditionCheckController::class, 'index'])->name('condition-checks.index');
    Route::post('/condition-checks', [ConditionCheckController::class, 'store'])->name('condition-checks.store');
    Route::get('/condition-checks/units', [ConditionCheckController::class, 'getUnits'])->name('condition-checks.units');

    Route::get('/damage-cases', [DamageCaseController::class, 'index'])->name('damage-cases.index');
    Route::get('/damage-cases/create', [DamageCaseController::class, 'create'])->name('damage-cases.create');
    Route::post('/damage-cases', [DamageCaseController::class, 'store'])->name('damage-cases.store');
    Route::get('/damage-cases/units', [DamageCaseController::class, 'getUnits'])->name('damage-cases.units');
    Route::get('/damage-cases/{damageCase}', [DamageCaseController::class, 'show'])->name('damage-cases.show');
    Route::post('/damage-cases/{damageCase}/respond', [DamageCaseController::class, 'respond'])->name('damage-cases.respond');
    Route::post('/damage-cases/{damageCase}/verify-payment', [DamageCaseController::class, 'verifyPayment'])->name('damage-cases.verify-payment');
    Route::post('/damage-cases/{damageCase}/close', [DamageCaseController::class, 'close'])->name('damage-cases.close');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::post('/products/{product}/status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/units', [EquipmentUnitController::class, 'index'])->name('units.index');
    Route::post('/units', [EquipmentUnitController::class, 'store'])->name('units.store');
    Route::put('/units/{unit}', [EquipmentUnitController::class, 'update'])->name('units.update');
    Route::delete('/units/{unit}', [EquipmentUnitController::class, 'destroy'])->name('units.destroy');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/settings/profile', [TenantProfileController::class, 'edit'])->name('settings.profile');
    Route::post('/settings/profile', [TenantProfileController::class, 'update'])->name('settings.profile.update');
    Route::get('/settings/payments', [PaymentMethodController::class, 'index'])->name('settings.payments');
    Route::post('/settings/payments', [PaymentMethodController::class, 'store'])->name('settings.payments.store');
    Route::get('/settings/payments/{paymentMethod}/edit', [PaymentMethodController::class, 'edit'])->name('settings.payments.edit');
    Route::put('/settings/payments/{paymentMethod}', [PaymentMethodController::class, 'update'])->name('settings.payments.update');
    Route::delete('/settings/payments/{paymentMethod}', [PaymentMethodController::class, 'destroy'])->name('settings.payments.destroy');
    Route::post('/settings/payments/{paymentMethod}/toggle', [PaymentMethodController::class, 'toggleStatus'])->name('settings.payments.toggle');
    Route::get('/theme', [TenantProfileController::class, 'edit'])->name('theme.index');
    Route::post('/theme', [TenantProfileController::class, 'update'])->name('theme.update');
    Route::get('/settings', fn () => redirect()->route('admin.settings.profile'))->name('settings.index');

    Route::get('/admins', [AdminStaffController::class, 'index'])->name('admins.index');
    Route::post('/admins', [AdminStaffController::class, 'store'])->name('admins.store');
    Route::post('/admins/{user}/status', [AdminStaffController::class, 'toggleStatus'])->name('admins.toggle-status');

    // Pusat Pemberitahuan Operasional Toko
    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [AdminNotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [AdminNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
});

Route::prefix('owner')->name('owner.')->group(function () {
    Route::get('/login', [OwnerAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [OwnerAuthController::class, 'login'])->name('login.submit');

    Route::middleware(['owner'])->group(function () {
        Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [OwnerAuthController::class, 'logout'])->name('logout');

        Route::get('/registrations', [OwnerRegistrationController::class, 'index'])->name('registrations.index');
        Route::post('/registrations/{id}/verify', [OwnerRegistrationController::class, 'verify'])->name('registrations.verify');
        Route::post('/registrations/{id}/reject', [OwnerRegistrationController::class, 'reject'])->name('registrations.reject');

        Route::get('/tenants', [OwnerTenantController::class, 'index'])->name('tenants.index');
        Route::put('/tenants/{client}', [OwnerTenantController::class, 'update'])->name('tenants.update');
        Route::post('/tenants/{client}/status', [OwnerTenantController::class, 'updateStatus'])->name('tenants.status');
        Route::put('/tenants/{client}/subscription', [OwnerTenantController::class, 'updateSubscription'])->name('tenants.subscription');

        Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::post('/subscriptions/{id}/renew', [SubscriptionController::class, 'renew'])->name('subscriptions.renew');
        Route::get('/subscriptions/{id}/history', [SubscriptionController::class, 'history'])->name('subscriptions.history');

        // Pusat Notifikasi Platform Owner
        Route::get('/notifications', [\App\Http\Controllers\Owner\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{notification}/read', [\App\Http\Controllers\Owner\NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Owner\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

        // Audit Trail Activity Log Platform Owner (Minggu 9.2-A2)
        Route::get('/activity-logs', [OwnerActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/activity-logs/{activityLog}', [OwnerActivityLogController::class, 'show'])->name('activity-logs.show');
    });
});

Route::get('/client/{subdomain}/cart', CartPage::class)
    ->name('customer.cart');

Route::get('/auth/google', [GoogleController::class, 'redirect'])
    ->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);
