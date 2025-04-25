<?php

namespace App\Http\Controllers;

use App\Models\DeliveryAddress;
use App\Models\OtpRequest;
use App\Models\PaymentAccount;
use App\Notifications\SendOtpNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Random\RandomException;

class SettingController extends Controller
{
    public function delivery_address()
    {
        return Inertia::render('Setting/DeliveryAddress/DeliveryAddress', [
            'addressCounts' => DeliveryAddress::count(),
            'backRoute' => 'profile',
        ]);
    }

    public function getDeliveryAddress()
    {
        $addresses = DeliveryAddress::with('country:id,name,translations')
            ->where('user_id', Auth::id())
            ->orderByDesc('default_address')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'addresses' => $addresses,
        ]);
    }

    public function addDeliveryAddress(Request $request)
    {
        Validator::make($request->all(), [
            'receiver_name' => ['required'],
            'receiver_phone' => ['required'],
            'address' => ['required'],
            'postal_code' => ['required'],
            'country_id' => ['required'],
        ])->setAttributeNames([
            'receiver_name' => trans('public.receiver_name'),
            'receiver_phone' => trans('public.phone_number'),
            'address' => trans('public.full_address'),
            'postal_code' => trans('public.postal_code'),
            'country_id' => trans('public.country'),
        ])->validate();

        $defaultAddress = (bool)$request->default_address;

        if ($defaultAddress) {
            DeliveryAddress::where('user_id', auth()->id())
                ->where('default_address', 1)
                ->update(['default_address' => 0]);
        }

        DeliveryAddress::create([
            'user_id' => auth()->id(),
            'receiver_name' => $request->receiver_name,
            'receiver_phone' => $request->receiver_phone,
            'address' => $request->address,
            'postal_code' => $request->postal_code,
            'country_id' => $request->country_id,
            'default_address' => $defaultAddress,
        ]);

        return back()->with('toast', [
            'title' => trans('public.success'),
            'message' => trans('public.toast_add_delivery_address_success'),
            'type' => 'success',
        ]);
    }

    public function system_setting()
    {
        return Inertia::render('Setting/SystemSetting/SystemSetting', [
            'backRoute' => 'profile',
        ]);
    }

    public function payment_account()
    {
        return Inertia::render('Setting/PaymentAccount/PaymentAccount', [
            'accountsCount' => PaymentAccount::where('user_id', Auth::id())->count(),
            'backRoute' => 'profile',
        ]);
    }

    public function getPaymentAccounts()
    {
        $accounts = PaymentAccount::with('country:id,name,translations')
            ->where('user_id', Auth::id())
            ->orderByDesc('default_account')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'accounts' => $accounts,
        ]);
    }

    public function addPaymentAccount(Request $request)
    {
        $rules = [
            'payment_method' => ['required'],
            'payment_account_name' => ['required'],
            'payment_platform_name' => ['required'],
            'account_no' => ['required'],
        ];

        if ($request->payment_method == 'bank') {
            $rules['bank_region'] = ['required'];
            $rules['bank_sub_branch'] = ['required'];
            $rules['bank_swift_code'] = ['required'];
            $rules['country_id'] = ['required'];
            $rules['currency'] = ['required'];
        }

        $attributeNames = [
            'payment_method' => trans('public.payment_account_type'),
            'payment_account_name' => $request->payment_method == 'crypto'
                ? trans('public.wallet_name')
                : trans('public.account_name'),
            'payment_account_type' => trans('public.account_type'),
            'payment_platform_name' => trans('public.bank'),
            'account_no' => $request->payment_method == 'crypto'
                ? trans('public.token_address')
                : trans('public.account_number'),
            'bank_region' => trans('public.bank_region'),
            'bank_sub_branch' => trans('public.bank_branch'),
            'bank_swift_code' => trans('public.swift_code'),
            'country_id' => trans('public.country'),
            'currency' => trans('public.currency'),
        ];

        Validator::make($request->all(), $rules)
            ->setAttributeNames($attributeNames)
            ->validate();

        $default_account = (bool)$request->default_account;

        if ($default_account) {
            PaymentAccount::where('user_id', Auth::id())
                ->where('default_account', 1)
                ->update(['default_account' => 0]);
        }

        $payment_account = PaymentAccount::create([
            'user_id' => Auth::id(),
            'payment_account_name' => $request->payment_account_name,
            'payment_platform' => $request->payment_method,
            'payment_platform_name' => $request->payment_platform_name,
            'account_no' => $request->account_no,
            'status' => 'active',
            'default_account' => $request->default_account,
        ]);

        if ($payment_account->payment_platform == 'bank') {
            $payment_account->update([
                'bank_region' => $request->bank_region,
                'bank_sub_branch' => $request->bank_sub_branch,
                'bank_swift_code' => $request->bank_swift_code,
                'country_id' => $request->country_id,
                'currency' => $request->currency,
            ]);
        } else {
            $payment_account->update([
                'currency' => 'USDT',
            ]);
        }

        return redirect()->back()->with('toast', [
            'title' => trans('public.toast_payment_account_success'),
            'type' => 'success'
        ]);
    }

    public function updatePaymentAccount(Request $request)
    {
        $rules = [
            'payment_account_name' => ['required'],
            'payment_platform_name' => ['required'],
            'account_no' => ['required'],
        ];

        if ($request->payment_method == 'bank') {
            $rules['bank_region'] = ['required'];
            $rules['bank_sub_branch'] = ['required'];
            $rules['bank_swift_code'] = ['required'];
            $rules['country_id'] = ['required'];
            $rules['currency'] = ['required'];
        }

        $attributeNames = [
            'payment_account_name' => $request->payment_method == 'crypto'
                ? trans('public.wallet_name')
                : trans('public.account_name'),
            'payment_account_type' => trans('public.account_type'),
            'payment_platform_name' => trans('public.bank'),
            'account_no' => $request->payment_method == 'crypto'
                ? trans('public.token_address')
                : trans('public.account_number'),
            'bank_region' => trans('public.bank_region'),
            'bank_sub_branch' => trans('public.bank_branch'),
            'bank_swift_code' => trans('public.swift_code'),
            'country_id' => trans('public.country'),
            'currency' => trans('public.currency'),
        ];

        Validator::make($request->all(), $rules)
            ->setAttributeNames($attributeNames)
            ->validate();

        $payment_account = PaymentAccount::find($request->payment_account_id);

        $payment_account->update([
            'payment_account_name' => $request->payment_account_name,
            'payment_platform_name' => $request->payment_platform_name,
            'account_no' => $request->account_no,
            'default_account' => $request->default_account,
        ]);

        if ($payment_account->payment_platform == 'bank') {
            $payment_account->update([
                'bank_region' => $request->bank_region,
                'bank_sub_branch' => $request->bank_sub_branch,
                'bank_swift_code' => $request->bank_swift_code,
                'country_id' => $request->country_id,
                'currency' => $request->currency,
            ]);
        }

        return redirect()->back()->with('toast', [
            'title' => trans('public.toast_update_payment_account_success'),
            'type' => 'success'
        ]);
    }

    public function security_pin()
    {
        return Inertia::render('Setting/SecurityPin/SecurityPin', [
            'backRoute' => 'profile',
        ]);
    }

    public function updateSecurityPin(Request $request)
    {
        $user = $request->user();
        $current_pin = $request->current_pin;

        if ($user->security_pin) {
            Validator::make($request->all(), [
                'current_pin' => ['required'],
            ])->setAttributeNames([
                'current_pin' => trans('public.current_pin_code'),
            ])->validate();

            if (!is_null($user->security_pin) && !Hash::check($current_pin, $user->security_pin)) {
                throw ValidationException::withMessages(['current_pin' => trans('public.current_pin_invalid')]);
            }
        }

        Validator::make($request->all(), [
            'pin' => ['required', 'confirmed'],
        ])->setAttributeNames([
            'pin' => trans('public.pin_code'),
        ])->validate();

        $user->update([
            'security_pin' => Hash::make($request->pin),
        ]);

        return to_route('profile')->with('toast', [
            'title' => trans('public.toast_update_pin_success'),
            'type' => 'success'
        ]);
    }

    public function requestOtp()
    {
        $email = Auth::user()->email;

        $otp = random_int(100000, 999999);

        $otp_request = OtpRequest::updateOrCreate(
            [
                'email' => $email,
                'type' => 'reset_pin',
            ],
            [
                'type' => 'reset_pin',
                'otp' => $otp,
            ]
        );

        Notification::route('mail', $email)
            ->notify(new SendOtpNotification($otp_request->otp));

        return back();
    }

    public function resetPin(Request $request)
    {
        Validator::make($request->all(), [
            'pin' => ['required', 'confirmed'],
            'otp' => ['required'],
        ])->setAttributeNames([
            'pin' => trans('public.pin_code'),
            'otp' => trans('public.otp_code'),
        ])->validate();

        $user = $request->user();
        if ($request->otp) {
            $existVerifyOtp = OtpRequest::where([
                'email' => $user->email,
                'type' => 'reset_pin',
            ])->first();

            $otpCreatedAt = Carbon::parse($existVerifyOtp->updated_at);
            $currentTime = Carbon::now();
            $differenceInSeconds = $currentTime->diffInSeconds($otpCreatedAt);

            if ($existVerifyOtp->otp != $request->otp || $differenceInSeconds > 300) {
                throw ValidationException::withMessages(['otp' => trans('public.invalid_otp')]);
            }

            $user->update([
                'security_pin' => Hash::make($request->pin),
            ]);
        }

        return to_route('profile')->with('toast', [
            'title' => trans('public.toast_reset_pin_success'),
            'type' => 'success'
        ]);
    }
}
