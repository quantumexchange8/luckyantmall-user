<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'cartItemsCount' => $request->user() ? $request->user()->cart_items()->count() : null,
                'paymentAccounts' => $request->user() ? $request->user()->payment_accounts : null,
            ],
            'canLogin' => app('router')->has('login'),
            'canRegister' => app('router')->has('register'),
            'locale' => session('locale') ? session('locale') : app()->getLocale(),
            'toast' => session('toast'),
        ];
    }
}
