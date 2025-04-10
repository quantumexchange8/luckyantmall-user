<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SelectOptionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WalletController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Illuminate\Support\Facades\App;

Route::get('locale/{locale}', function ($locale) {
    App::setLocale($locale);
    Session::put("locale", $locale);

    return redirect()->back();
});

Route::get('/getCountries', [SelectOptionController::class, 'getCountries'])->name('getCountries');
Route::get('/getCategories', [SelectOptionController::class, 'getCategories'])->name('getCategories');

/**
 * ==============================
 *            Home
 * ==============================
 */
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::prefix('home')->group(function () {
    Route::get('/getPopularProducts', [HomeController::class, 'getPopularProducts'])->name('home.getPopularProducts');
});

/**
 * ==============================
 *            Shop
 * ==============================
 */
Route::prefix('shop')->group(function () {
    Route::get('/', [ShopController::class, 'index'])->name('shop');
    Route::get('/getProducts', [ShopController::class, 'getProducts'])->name('shop.getProducts');
    Route::get('/product/{slug}/{id}', [ShopController::class, 'product_detail'])->name('shop.product_detail');

    Route::middleware('auth')->group(function () {
        Route::post('/addToCart', [ShopController::class, 'addToCart'])->name('shop.addToCart');
        Route::post('/buyNow', [ShopController::class, 'buyNow'])->name('shop.buyNow');
    });
});

Route::middleware('auth')->group(function () {
    // Get Options
    Route::get('/getTradeSymbols', [SelectOptionController::class, 'getTradeSymbols'])->name('getTradeSymbols');

    Route::get('/getDepositProfiles', [WalletController::class, 'getDepositProfiles'])->name('profile.getDepositProfiles');

    /**
     * ==============================
     *            Cart
     * ==============================
     */
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('cart');
        Route::get('/getCartItems', [CartController::class, 'getCartItems'])->name('cart.getCartItems');
        Route::get('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

        Route::post('/proceedCheckout', [CartController::class, 'proceedCheckout'])->name('cart.proceedCheckout');
        Route::post('/confirmPayment', [CartController::class, 'confirmPayment'])->name('cart.confirmPayment');

        Route::delete('/deleteItem', [CartController::class, 'deleteItem'])->name('cart.deleteItem');
    });

    /**
     * ==============================
     *            Profile
     * ==============================
     */
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('profile');
        Route::get('/getWalletData', [WalletController::class, 'getWalletData'])->name('profile.getWalletData');
        Route::get('/{wallet_type}', [WalletController::class, 'wallet_detail'])->name('profile.wallet_detail');
        Route::get('/{wallet_type}/deposit', [WalletController::class, 'wallet_deposit'])->name('profile.wallet_deposit');
        Route::get('/{wallet_type}/getWalletHistory', [WalletController::class, 'getWalletHistory'])->name('profile.getWalletHistory');

        Route::post('submitDeposit', [WalletController::class, 'submitDeposit'])->name('profile.submitDeposit');
        Route::patch('updateBalanceVisibility', [WalletController::class, 'updateBalanceVisibility'])->name('profile.updateBalanceVisibility');
    });

    /**
     * ==============================
     *            Setting
     * ==============================
     */
    Route::prefix('setting')->group(function () {
        // Delivery Address
        Route::get('/delivery_address', [SettingController::class, 'delivery_address'])->name('delivery_address');
        Route::get('/getDeliveryAddress', [SettingController::class, 'getDeliveryAddress'])->name('getDeliveryAddress');

        Route::post('addDeliveryAddress', [SettingController::class, 'addDeliveryAddress'])->name('setting.addDeliveryAddress');

        // Payment Account
        Route::get('/payment_account', [SettingController::class, 'payment_account'])->name('setting.payment_account');

        // System
        Route::get('/system_setting', [SettingController::class, 'system_setting'])->name('setting.system_setting');
    });

    // Portal
    Route::prefix('portal')->group(function () {
        /**
         * ==============================
         *            Dashboard
         * ==============================
         */
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        /**
         * ==============================
         *           Referral
         * ==============================
         */
        Route::prefix('referral')->group(function () {
            Route::get('/', [ReferralController::class, 'index'])->name('referral');
            Route::get('/getStructureData', [ReferralController::class, 'getStructureData'])->name('referral.getStructureData');
            Route::get('/getReferralListingData', [ReferralController::class, 'getReferralListingData'])->name('referral.getReferralListingData');
        });

        /**
         * ==============================
         *         Trade History
         * ==============================
         */
        Route::prefix('trade_history')->group(function () {
            Route::get('/', [ReportController::class, 'trade_history'])->name('trade_history');
            Route::get('/getTradeHistoriesData', [ReportController::class, 'getTradeHistoriesData'])->name('trade_history.getTradeHistoriesData');
        });
    });
});

require __DIR__.'/auth.php';
