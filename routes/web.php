<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SelectOptionController;
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
    /**
     * ==============================
     *            Profile
     * ==============================
     */
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('profile');
    });
});

require __DIR__.'/auth.php';
