<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SelectOptionController;
use App\Http\Controllers\WalletController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Illuminate\Support\Facades\App;

Route::get('/', function () {
    return Inertia::render('Home/HomeScreen', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home_screen');

Route::get('locale/{locale}', function ($locale) {
    App::setLocale($locale);
    Session::put("locale", $locale);

    return redirect()->back();
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/getCountries', [SelectOptionController::class, 'getCountries'])->name('getCountries');

Route::middleware('auth')->group(function () {
    Route::get('/getDepositProfiles', [WalletController::class, 'getDepositProfiles'])->name('profile.getDepositProfiles');
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

        Route::post('submitDeposit', [WalletController::class, 'submitDeposit'])->name('profile.submitDeposit');
    });
});

require __DIR__.'/auth.php';
