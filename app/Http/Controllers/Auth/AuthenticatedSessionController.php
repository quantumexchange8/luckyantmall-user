<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\Data\UserService;
use App\Services\MainApiService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('home', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function sign_up_with_group(Request $request)
    {
        return Inertia::render('Auth/PlatformSignIn');
    }

    public function proceedGroupSignIn(Request $request)
    {
        Validator::make($request->all(), [
            'username' => ['required', 'string', 'regex:/^[\p{L}\p{N}\p{M}. @]+$/u'],
        ])->setAttributeNames([
            'username' => trans('public.username')
        ])->validate();

        $params = [
            'username' => $request->username,
        ];

        $redirectUrl = "http://luckyant-trading-user.test/authorize_user_account?" . http_build_query($params);

        return Inertia::location($redirectUrl);
    }

    public function authorize_login(Request $request)
    {
        $users = User::all();
        $token = $request->token;

        foreach ($users as $user) {
            $dataToHash = md5($user->name . $user->email . $user->username);

            if ($dataToHash === $token) {
                // Hash matches, log in the user and redirect
                Auth::login($user);
                return redirect()->route('home')->with('toast', [
                    'title' => trans('public.success_authorized'),
                    'message' => trans('public.toast_signed_in_success'),
                    'type' => 'success',
                ]);
            }
        }

        // No matching user found, create and authenticate
        $newUser = [
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'dial_code' => [
                'phone_code' => substr($request->dial_code, 1),
                'id' => $request->country_id,
            ],
            'phone_number' => $request->phone_number,
            'dob' => $request->dob,
            'password' => 'LuckyantMall666$',
        ];

        $newUser['phone'] = str_replace($request->dial_code, '', $newUser['phone_number']);

        $user = (new UserService)->createUser($newUser);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('home', absolute: false));
    }
}
