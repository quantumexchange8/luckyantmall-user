<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Group;
use App\Models\User;
use App\Models\Wallet;
use App\Services\GroupService;
use App\Services\RunningNumberService;
use DateInterval;
use DateInvalidOperationException;
use DateMalformedStringException;
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

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     * @throws DateMalformedStringException|DateInvalidOperationException
     */
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

        $dial_code = $validator['dial_code'];
        $country = Country::find($dial_code['id']);

        $userData = [
            'name' => $validator['name'],
            'username' => $validator['username'],
            'email' => $validator['email'],
            'dial_code' => $dial_code['phone_code'],
            'phone' => $validator['phone'],
            'phone_number' => $validator['phone_number'],
            'dob' => $validator['dob'],
            'country_id' => $country->id,
            'nationality' => $country->nationality,
            'password' => Hash::make($validator['password']),
        ];

        $referrer = null;
        $referral_code = $request->input('referral_code');

        if ($referral_code) {
            $referrer = User::where('referral_code', $referral_code)->first();
        }

        if (!$referrer) {
            $referrer = User::find(2);
        }

        $referrer_id = $referrer->id;
        $hierarchyList = empty($referrer['hierarchyList']) ? "-$referrer_id-" : "{$referrer['hierarchyList']}$referrer_id-";

        $userData['upline_id'] = $referrer_id;
        $userData['hierarchyList'] = $hierarchyList;

        $user = User::create($userData);

        $user->setReferralId();

        $id_no = 'LID' . Str::padLeft($user->id - 1, 6, "0");
        $user->id_number = $id_no;
        $user->save();

        if ($referrer->group) {
            (new GroupService())->addUserToGroup($referrer->group->group_id, $user->id);
            $group_rank_setting = $referrer->group->group->group_rank_settings()->first();
            $user->setting_rank_id = $group_rank_setting->id;
        } else {
            (new GroupService())->addUserToGroup(Group::first()->id, $user->id);
        }

        Wallet::create([
            'user_id' => $user->id,
            'type' => 'e_wallet',
            'address' => RunningNumberService::getID('e_wallet'),
            'currency' => 'CNY',
            'currency_symbol' => '¥'
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'type' => 'bonus_wallet',
            'address' => RunningNumberService::getID('bonus_wallet'),
            'currency' => 'CNY',
            'currency_symbol' => '¥'
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'type' => 'cash_wallet',
            'address' => RunningNumberService::getID('cash_wallet'),
            'currency' => 'CNY',
            'currency_symbol' => '¥'
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'type' => 'point_wallet',
            'address' => RunningNumberService::getID('point_wallet'),
            'currency' => 'point',
            'currency_symbol' => 'point'
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('home_screen', absolute: false));
    }
}
