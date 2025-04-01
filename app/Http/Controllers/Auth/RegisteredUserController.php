<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Group;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Data\UserService;
use App\Services\GroupService;
use App\Services\RunningNumberService;
use DateInterval;
use DateTime;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        $form_step = $request->step;
        $rules = [
            'name' => ['required', 'regex:/^[a-zA-Z0-9\p{Han}. ]+$/u', 'max:255'],
            'username' => ['required', 'regex:/^[a-zA-Z0-9\p{Han}. ]+$/u', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:' . User::class],
            'dob' => ['required'],
            'country' => ['required'],
            'dial_code' => ['required'],
            'phone' => ['required'],
            'phone_number' => ['required', 'max:255', 'unique:' . User::class],
        ];

        $attributeNames = [
            'name' => trans('public.full_name'),
            'username' => trans('public.username'),
            'email' => trans('public.email'),
            'country' => trans('public.country'),
            'dob' => trans('public.dob'),
            'phone' => trans('public.phone_number'),
            'phone_number' => trans('public.phone_number'),
            'password' => trans('public.password'),
            'terms' => trans('public.terms'),
        ];

        switch ($form_step) {
            case 1:
                Validator::make($request->all(), $rules)
                    ->setAttributeNames($attributeNames)
                    ->validate();

                $dobDate = new DateTime($request->dob);
                $today = new DateTime();
                $todayMinus18Years = $today->sub(new DateInterval('P18Y'));

                if ($dobDate > $todayMinus18Years) {
                    throw ValidationException::withMessages(['dob' => trans('public.user_not_reach_eighteen_years')]);
                }

                return back();

            case 2:
                $passwordRules = [
                    'password' => ['required', 'confirmed', Password::min(8)->letters()->symbols()->numbers()->mixedCase()],
                ];

                Validator::make($request->all(), $passwordRules)
                    ->setAttributeNames($attributeNames)
                    ->validate();

                return back();

            case 3:
                $rules['password'] = ['required', 'confirmed', Password::min(8)->letters()->symbols()->numbers()->mixedCase()];
                $rules['referral_code'] = ['nullable'];
                $rules['terms'] = ['required'];

                $validator = Validator::make($request->all(), $rules)
                    ->setAttributeNames($attributeNames)
                    ->validate();

                break;

            default:
                $rules['password'] = ['required', 'confirmed', Password::min(8)->letters()->symbols()->numbers()->mixedCase()];

                $validator = Validator::make($request->all(), $rules)
                    ->setAttributeNames($attributeNames)
                    ->validate();
                break;
        }

        $user = (new UserService)->createUser($validator, $request->referral_code);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('home', absolute: false));
    }
}
