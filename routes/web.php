<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RouterController;
use App\Http\Controllers\BandwidthPlanController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\HotspotUserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\GuestPortalController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Guest Portal Routes (MikroTik Hotspot Portal)
Route::prefix('guest')->name('guest.')->group(function () {
    Route::get('/{router_hash}/portal', [GuestPortalController::class, 'portal'])->name('portal');
    Route::get('/routers/{router_hash}/plans', [GuestPortalController::class, 'plans'])->name('plans');
    Route::post('/vouchers/{voucher_hash}/pay', [GuestPortalController::class, 'pay'])->name('pay');
    Route::get('/payment-status', [GuestPortalController::class, 'paymentStatus'])->name('payment-status');
    Route::post('/payment-callback', [GuestPortalController::class, 'paymentCallback'])->name('payment-callback');
});

// Public Captive Portal Routes (No Authentication Required)
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [PortalController::class, 'index'])->name('index');
    Route::post('/activate-voucher', [PortalController::class, 'activateVoucher'])->name('activate');
    Route::get('/payment', [PortalController::class, 'showPayment'])->name('payment');
    Route::post('/payment', [PortalController::class, 'processPayment'])->name('payment.process');
    Route::get('/payment-status', [PortalController::class, 'paymentStatus'])->name('payment-status');
    Route::post('/payment-callback', [PortalController::class, 'paymentCallback'])->name('payment-callback');
    Route::get('/payment-success', [PortalController::class, 'paymentSuccess'])->name('payment-success');
    Route::get('/success', [PortalController::class, 'success'])->name('success');
});

// Redirect root to portal
Route::redirect('/', '/portal');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Authenticated Routes
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Router Management
    Route::prefix('routers')->name('routers.')->group(function () {
        Route::get('/', [RouterController::class, 'index'])->name('index');
        Route::get('/create', [RouterController::class, 'create'])->name('create');
        Route::post('/', [RouterController::class, 'store'])->name('store');
        Route::get('/{router}', [RouterController::class, 'show'])->name('show');
        Route::get('/{router}/edit', [RouterController::class, 'edit'])->name('edit');
        Route::put('/{router}', [RouterController::class, 'update'])->name('update');
        Route::delete('/{router}', [RouterController::class, 'destroy'])->name('destroy');

        // Custom Router Actions
        Route::post('/{router}/test-connection', [RouterController::class, 'testConnection'])->name('testConnection');
        Route::post('/{router}/sync-users', [RouterController::class, 'syncUsers'])->name('syncUsers');
        Route::post('/{router}/disconnect', [RouterController::class, 'disconnect'])->name('disconnect');

        // VPN Management
        Route::post('/{router}/enable-vpn', [RouterController::class, 'enableVpn'])->name('enableVpn');
        Route::post('/{router}/disable-vpn', [RouterController::class, 'disableVpn'])->name('disableVpn');
        Route::post('/{router}/regenerate-vpn', [RouterController::class, 'regenerateVpn'])->name('regenerateVpn');
        Route::get('/{router}/download-vpn-script', [RouterController::class, 'downloadVpnScript'])->name('downloadVpnScript');

        // Wallet & Portal
        Route::post('/{router}/withdraw', [RouterController::class, 'withdraw'])->name('withdraw');
        Route::get('/{router}/download-portal', [RouterController::class, 'downloadPortal'])->name('downloadPortal');

        // Voucher Management (under router)
        Route::prefix('/{router}/vouchers')->name('vouchers.')->group(function () {
            Route::get('/', [VoucherController::class, 'index'])->name('index');
            Route::get('/create', [VoucherController::class, 'create'])->name('create');
            Route::post('/', [VoucherController::class, 'store'])->name('store');
            Route::get('/{voucher}', [VoucherController::class, 'show'])->name('show');
            Route::get('/{voucher}/edit', [VoucherController::class, 'edit'])->name('edit');
            Route::put('/{voucher}', [VoucherController::class, 'update'])->name('update');
            Route::delete('/{voucher}', [VoucherController::class, 'destroy'])->name('destroy');

            // Voucher Actions
            Route::post('/{voucher}/activate', [VoucherController::class, 'activate'])->name('activate');
            Route::post('/{voucher}/disable', [VoucherController::class, 'disable'])->name('disable');
            Route::post('/{voucher}/enable', [VoucherController::class, 'enable'])->name('enable');
            Route::post('/{voucher}/sync-profile', [VoucherController::class, 'syncProfile'])->name('syncProfile');

            // Generate Hotspot Users
            Route::get('/{voucher}/generate-users', [VoucherController::class, 'showGenerateUsers'])->name('showGenerateUsers');
            Route::post('/{voucher}/generate-users', [VoucherController::class, 'generateUsers'])->name('generateUsers');
            Route::post('/{voucher}/retry-sync', [VoucherController::class, 'retrySync'])->name('retrySync');
            Route::get('/{voucher}/print', [VoucherController::class, 'print'])->name('print');
        });
    });

    // Bandwidth Plans Management
    Route::prefix('bandwidth-plans')->name('bandwidth-plans.')->group(function () {
        Route::get('/', [BandwidthPlanController::class, 'index'])->name('index');
        Route::get('/create', [BandwidthPlanController::class, 'create'])->name('create');
        Route::post('/', [BandwidthPlanController::class, 'store'])->name('store');
        Route::get('/{bandwidthPlan}', [BandwidthPlanController::class, 'show'])->name('show');
        Route::get('/{bandwidthPlan}/edit', [BandwidthPlanController::class, 'edit'])->name('edit');
        Route::put('/{bandwidthPlan}', [BandwidthPlanController::class, 'update'])->name('update');
        Route::delete('/{bandwidthPlan}', [BandwidthPlanController::class, 'destroy'])->name('destroy');
    });

    // Customer Management
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');

        // Custom Customer Actions
        Route::get('/{customer}/purchase-history', [CustomerController::class, 'purchaseHistory'])->name('purchaseHistory');
        Route::get('/{customer}/active-services', [CustomerController::class, 'activeServices'])->name('activeServices');
    });


    // Hotspot Users Management
    Route::prefix('hotspot-users')->name('hotspot-users.')->group(function () {
        Route::get('/', [HotspotUserController::class, 'index'])->name('index');
        Route::get('/create', [HotspotUserController::class, 'create'])->name('create');
        Route::post('/', [HotspotUserController::class, 'store'])->name('store');
        Route::get('/{hotspotUser}', [HotspotUserController::class, 'show'])->name('show');
        Route::get('/{hotspotUser}/edit', [HotspotUserController::class, 'edit'])->name('edit');
        Route::put('/{hotspotUser}', [HotspotUserController::class, 'update'])->name('update');
        Route::delete('/{hotspotUser}', [HotspotUserController::class, 'destroy'])->name('destroy');

        // Custom Hotspot User Actions
        Route::post('/{hotspotUser}/disconnect', [HotspotUserController::class, 'disconnect'])->name('disconnect');
        Route::post('/{hotspotUser}/enable', [HotspotUserController::class, 'enable'])->name('enable');
        Route::post('/{hotspotUser}/disable', [HotspotUserController::class, 'disable'])->name('disable');
        Route::post('/{hotspotUser}/update-usage', [HotspotUserController::class, 'updateUsage'])->name('updateUsage');
        Route::get('/online-users', [HotspotUserController::class, 'onlineUsers'])->name('onlineUsers');
    });

    // Reports (Admin Only)
    Route::middleware('role:admin')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
        Route::get('/customers', [ReportController::class, 'customers'])->name('customers');
        Route::get('/usage', [ReportController::class, 'usage'])->name('usage');
        Route::get('/vouchers', [ReportController::class, 'vouchers'])->name('vouchers');

        // Report Exports
        Route::get('/exports/sales', [ReportController::class, 'exportSales'])->name('exports.sales');
        Route::get('/exports/revenue', [ReportController::class, 'exportRevenue'])->name('exports.revenue');
        Route::get('/exports/customers', [ReportController::class, 'exportCustomers'])->name('exports.customers');
        Route::get('/exports/usage', [ReportController::class, 'exportUsage'])->name('exports.usage');
        Route::get('/exports/vouchers', [ReportController::class, 'exportVouchers'])->name('exports.vouchers');
    });

    // Settings (Admin Only)
    Route::middleware('role:admin')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::put('/', [SettingController::class, 'update'])->name('update');
        Route::get('/{key}', [SettingController::class, 'get'])->name('get');
        Route::post('/{key}', [SettingController::class, 'set'])->name('set');
    });

    // Profile Routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/edit', function () {
            return view('profile.edit');
        })->name('edit');

        Route::get('/security', function () {
            return view('profile.security');
        })->name('security');
    });

});
