<?php

namespace App\Http\Controllers;

use App\Models\DepositProfile;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\RunningNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class WalletController extends Controller
{
    public function getWalletData()
    {
        return response()->json([
            'wallets' => Wallet::where('user_id', Auth::id())->get(),
        ]);
    }

    public function wallet_detail($wallet_type)
    {
        return Inertia::render('Profile/Wallets/WalletDetail', [
            'walletType' => $wallet_type,
            'wallet' => Wallet::where('user_id', Auth::id())->where('type', $wallet_type)->first()
        ]);
    }

    public function wallet_deposit($wallet_type)
    {
        if ($wallet_type == 'e_wallet') {
            return Inertia::render('Profile/Wallets/WalletDeposit', [
                'walletType' => $wallet_type,
                'wallet' => Wallet::where('user_id', Auth::id())->where('type', $wallet_type)->first()
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function submitDeposit(Request $request)
    {
        Validator::make($request->all(), [
            'amount' => ['required'],
            'payment_slip' => ['required']
        ])->setAttributeNames([
            'amount' => trans('public.amount'),
            'payment_slip' => trans('public.payment_slip')
        ])->validate();

        $wallet = Wallet::find($request->wallet_id);
        $deposit_profile = DepositProfile::find($request->deposit_profile_id);
        $amount = $request->amount;

        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'category' => 'e_wallet',
            'transaction_type' => 'deposit',
            'to_wallet_id' => $wallet->id,
            'transaction_number' => RunningNumberService::getID('transaction'),
            'to_payment_account_name' => $deposit_profile->name,
            'to_payment_platform' => $deposit_profile->type,
            'to_payment_platform_name' => $deposit_profile->type == 'bank' ? $deposit_profile->bank_name: $deposit_profile->crypto_tether,
            'to_payment_account_no' => $deposit_profile->account_number,
            'to_bank_branch_address' => $deposit_profile?->bank_branch,
            'amount' => $amount,
            'from_currency' => $wallet->currency,
            'to_currency' => $deposit_profile->currency,
            'transaction_amount' => $amount,
            'fund_type' => 'real_fund',
            'old_wallet_amount' => $wallet->balance,
            'new_wallet_amount' => $wallet->balance,
        ]);

        if ($request->payment_slip) {
            $transaction->addMedia($request->payment_slip)->toMediaCollection('payment_slip');
        }

        return redirect()->route('profile');
    }

    public function getDepositProfiles()
    {
        $depositProfiles = DepositProfile::with('country:id,name,translations')
            ->where('status', 'active')
            ->get()
            ->map(function ($profile) {
                $profile->networks = json_decode($profile->crypto_network);
                return $profile;
            });

        return response()->json($depositProfiles);
    }
}
