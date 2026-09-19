<?php

use App\Http\Controllers\Admin\AuthController as AdminAuth;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\OrderController as AdminOrder;
use App\Http\Controllers\Admin\ProductController as AdminProduct;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('coffee.index'));

// ===== Public storefront =====
Route::get('/coffee', [MenuController::class, 'index'])->name('coffee.index');

// ===== Cart =====
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::post('/cart/add', [CartController::class, 'store'])->name('cart.add');
Route::post('/cart/remove/{id}', [CartController::class, 'destroy'])->name('cart.remove');

// ===== Checkout Dine In / Take Away =====
Route::get('/checkout/type', [CheckoutController::class, 'chooseType'])->name('checkout.type');
Route::post('/checkout/form', [CheckoutController::class, 'showForm'])->name('checkout.form');
Route::post('/checkout/store', [CheckoutController::class, 'store'])->name('checkout.store');

// ===== Pembayaran & status pesanan =====
Route::get('/checkout/payment/{order}', [PaymentController::class, 'show'])->name('checkout.payment');
Route::post('/checkout/payment/{order}/charge', [PaymentController::class, 'charge'])->name('checkout.charge');
Route::get('/checkout/success/{order}', [PaymentController::class, 'status'])->name('checkout.success');

// ===== Notifikasi Midtrans (server-ke-server, dikecualikan dari CSRF) =====
// Satu-satunya jalur yang boleh mengubah status pembayaran pesanan.
Route::post('/webhook/midtrans', [MidtransWebhookController::class, 'handle'])->name('webhook.midtrans');

// ===== Admin =====
Route::prefix('admin')->name('admin.')->group(function () {
    // Auth
    Route::get('/login', [AdminAuth::class, 'showLogin'])->name('login');
    // Dibatasi 5 percobaan per menit agar tidak bisa di-brute force.
    Route::post('/login', [AdminAuth::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.submit');
    Route::post('/logout', [AdminAuth::class, 'logout'])->name('logout');

    // Protected
    Route::middleware('admin')->group(function () {
        Route::get('/', [AdminDashboard::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard.index');

        // Orders
        Route::get('/orders', [AdminOrder::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrder::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [AdminOrder::class, 'updateStatus'])->name('orders.status');
        Route::delete('/orders/{order}', [AdminOrder::class, 'destroy'])->name('orders.destroy');

        // Products
        Route::get('/products', [AdminProduct::class, 'index'])->name('products.index');
        Route::get('/products/create', [AdminProduct::class, 'create'])->name('products.create');
        Route::post('/products', [AdminProduct::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [AdminProduct::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [AdminProduct::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [AdminProduct::class, 'destroy'])->name('products.destroy');
    });
});

