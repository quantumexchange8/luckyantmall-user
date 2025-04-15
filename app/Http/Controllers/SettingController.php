<?php

namespace App\Http\Controllers;

use App\Models\DeliveryAddress;
use App\Models\PaymentAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function delivery_address()
    {
        return Inertia::render('Setting/DeliveryAddress/DeliveryAddress', [
            'addressCounts' => DeliveryAddress::count(),
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
}
