<?php

namespace App\Http\Controllers;

use App\Models\DepositProfile;
use App\Models\Earning;
use App\Models\PaymentAccount;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\RunningNumberService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
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
        $user_id = Auth::id();
        $wallet = Wallet::firstWhere([
            'user_id' => $user_id,
            'type' => $wallet_type,
        ]);

        $transaction_query = Transaction::where('user_id', $user_id)
            ->where('category', $wallet_type);

        $allowedActions = [
            'e_wallet' => ['deposit', 'transfer'],
            'bonus_wallet' => ['transfer', 'withdraw'],
            'cash_wallet' => ['transfer', 'withdraw'],
        ];

        $actions = $allowedActions[$wallet_type] ?? [];

        if ($wallet_type == 'e_wallet' && Auth::user()->role != 'SA') {
            $actions = array_filter($actions, fn($action) => $action != 'transfer');
        }

        $types = (clone $transaction_query)
            ->distinct('transaction_type')
            ->get()
            ->pluck('transaction_type')
            ->filter()
            ->values()
            ->toArray();

        $earningTypes = Earning::where('wallet_type', $wallet_type)
            ->distinct('earning_type')
            ->get()
            ->pluck('earning_type')
            ->filter()
            ->values()
            ->toArray();

        $types = array_merge($types, $earningTypes);
        $types = array_unique($types);

        $earning_counts = Earning::where('user_id', $user_id)
            ->where('wallet_type', $wallet_type)
            ->count();

        return Inertia::render('Profile/Wallets/WalletDetail', [
            'walletType' => $wallet_type,
            'wallet' => $wallet,
            'transactionCounts' => (clone $transaction_query)->count(),
            'allowedActions' => $actions,
            'types' => $types,
            'earningCounts' => $earning_counts,
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

    public function updateBalanceVisibility(Request $request): void
    {
        $wallet = Wallet::find($request->id);

        $wallet->balance_visibility = !$wallet->balance_visibility;
        $wallet->save();
    }

    public function getWalletHistory(Request $request)
    {
        // Query for Transactions
        $transactionQuery = Transaction::with([
            'from_wallet:id,type,currency_symbol',
            'to_wallet:id,type,currency_symbol',
        ])
            ->where('user_id', Auth::id())
            ->where(function ($query) use ($request) {
                $query->where('from_wallet_id', $request->walletId)
                    ->orWhere('to_wallet_id', $request->walletId);
            });

        // Apply filter for Transaction
        if ($request->start_date) {
            $start_date = Carbon::parse($request->start_date)->addHours(8);
            $end_date = Carbon::parse($request->end_date)->addHours(8)->endOfDay();
            $transactionQuery->whereBetween('created_at', [$start_date, $end_date]);
        }

        if ($request->types) {
            $transactionQuery->whereIn('transaction_type', $request->types);
        }

        if ($request->statuses) {
            $transactionQuery->whereIn('status', $request->statuses);
        }

        // Query for Earnings
        $earningQuery = Earning::with([
            'wallet:id,type,currency_symbol',
        ])
            ->where(function ($query) use ($request) {
                $query->where('wallet_id', $request->walletId);
            });

        // Apply filter for Earning
        if ($request->start_date) {
            $start_date = Carbon::parse($request->start_date)->addHours(8);
            $end_date = Carbon::parse($request->end_date)->addHours(8)->endOfDay();
            $earningQuery->whereBetween('created_at', [$start_date, $end_date]);
        }

        if ($request->types) {
            $earningQuery->whereIn('earning_type', $request->types);
        }

        // Combine the results from both tables
        $transactions = $transactionQuery->select('id', 'transaction_type as type', 'transaction_number', 'created_at', 'amount', 'status', 'remarks', 'from_wallet_id as wallet_id')->get();
        $earnings = $earningQuery->select('id', 'earning_type as type', 'created_at', 'converted_amount as amount', DB::raw('NULL as status'), 'wallet_id')->get();

        $transactionsArray = $transactions->toArray();
        $earningsArray = $earnings->toArray();

        $combinedArray = array_merge($transactionsArray, $earningsArray);

        usort($combinedArray, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return response()->json([
            'walletHistory' => $combinedArray
        ]);
    }

    public function wallet_withdrawal($wallet_type)
    {
        $wallets = Wallet::where('user_id', Auth::id())
            ->whereIn('type', ['cash_wallet', 'bonus_wallet'])
            ->get()
            ->sortBy(function ($wallet) use ($wallet_type) {
                return $wallet->type == $wallet_type ? 0 : 1;
            })
            ->values();

        $payment_accounts = PaymentAccount::where('user_id', Auth::id())
            ->orderByDesc('default_account')
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Profile/Wallets/Withdrawal', [
            'walletType' => $wallet_type,
            'wallets' => $wallets,
            'withdrawalFee' => 5,
            'withdrawalFeePercentage' => 5,
            'paymentAccounts' => $payment_accounts
        ]);
    }

    public function submitWithdrawal(Request $request)
    {
        $withdraw_wallets = $request->withdraw_wallets;
        $total_wallet_balance = 0;
        $total_withdraw_amount = 0;
        $hasAmount = false;

        foreach ($withdraw_wallets as $withdraw_wallet => $amount) {
            if ($amount) {
                $hasAmount = true;

                if ($amount < 5) {
                    throw ValidationException::withMessages([
                        'withdraw_wallets.' . $withdraw_wallet => trans('public.withdrawal_amount_too_small')
                    ]);
                }

                $wallet = Wallet::find($withdraw_wallet);

                if (!$wallet || $wallet->balance < $amount) {
                    throw ValidationException::withMessages([
                        'withdraw_wallets.' . $withdraw_wallet => trans('public.insufficient_wallet_balance', [
                            'wallet' => trans("public.$wallet->type")
                        ])
                    ]);
                }

                $total_wallet_balance += $wallet->balance;
                $total_withdraw_amount += $amount;
            }
        }

        if (!$hasAmount) {
            throw ValidationException::withMessages([
                'withdraw_wallets' => trans('public.withdrawal_amount_required')
            ]);
        }

        Validator::make($request->all(), [
            'transaction_charges' => ['required'],
            'payment_account_id' => ['required'],
            'security_pin' => ['required'],
        ])->setAttributeNames([
            'transaction_charges' => trans('public.withdrawal_fee'),
            'payment_account_id' => trans('public.payment_account'),
            'security_pin' => trans('public.security_pin'),
        ])->validate();

        $user = Auth::user();

        if (!is_null($user->security_pin) && !Hash::check($request->get('security_pin'), $user->security_pin)) {
            throw ValidationException::withMessages([
                'security_pin' => trans('public.security_pin_incorrect')
            ]);
        }

        if ($total_wallet_balance < $total_withdraw_amount) {
            return back()->with('toast', [
                'title' => trans("public.warning"),
                'message' => trans('public.insufficient_balance'),
                'type' => 'warning',
            ]);
        }

        $transaction_charges = $request->transaction_charges;
        $transaction_number = RunningNumberService::getID('transaction');
        $payment_account = PaymentAccount::find($request->payment_account_id);

        $non_zero_wallets = array_filter($withdraw_wallets, fn($amount) => $amount > 0);

        if (count($non_zero_wallets) > 1) {
            foreach ($withdraw_wallets as $withdraw_wallet => $amount) {
                if ($amount > 0) {
                    $wallet = Wallet::find($withdraw_wallet);

                    if ($transaction_charges <= 5) {
                        $charge_per_wallet = $transaction_charges / count($non_zero_wallets);
                    } else {
                        $charge_per_wallet = ($amount * $request->transaction_charges_percentage) / 100;
                    }

                    $partial_amount = $amount - $charge_per_wallet;

                    Transaction::create([
                        'user_id' => $user->id,
                        'category' => $wallet->type,
                        'transaction_type' => 'withdrawal',
                        'from_wallet_id' => $wallet->id,
                        'transaction_number' => $transaction_number,
                        'to_payment_account_name' => $payment_account->payment_account_name,
                        'to_payment_platform' => $payment_account->payment_platform,
                        'to_payment_platform_name' => $payment_account->payment_platform_name,
                        'to_payment_account_no' => $payment_account->account_no,
                        'to_bank_sub_branch' => $payment_account?->bank_sub_branch,
                        'amount' => $amount,
                        'transaction_charges' => $transaction_charges,
                        'from_currency' => $wallet->currency,
                        'to_currency' => $payment_account->currency,
                        'transaction_amount' => $partial_amount,
                        'fund_type' => 'real_fund',
                        'old_wallet_amount' => $wallet->balance,
                        'new_wallet_amount' => $wallet->balance - $partial_amount,
                    ]);

                    $wallet->balance -= $partial_amount;
                    $wallet->save();
                }
            }
        } else {
            foreach ($withdraw_wallets as $withdraw_wallet => $amount) {
                if ($amount > 0) {
                    $wallet = Wallet::find($withdraw_wallet);
                    $final_amount = $amount - $transaction_charges;

                    Transaction::create([
                        'user_id' => $user->id,
                        'category' => $wallet->type,
                        'transaction_type' => 'withdrawal',
                        'from_wallet_id' => $wallet->id,
                        'transaction_number' => $transaction_number,
                        'to_payment_account_name' => $payment_account->payment_account_name,
                        'to_payment_platform' => $payment_account->payment_platform,
                        'to_payment_platform_name' => $payment_account->payment_platform_name,
                        'to_payment_account_no' => $payment_account->account_no,
                        'to_bank_sub_branch' => $payment_account?->bank_sub_branch,
                        'amount' => $amount,
                        'transaction_charges' => $transaction_charges,
                        'from_currency' => $wallet->currency,
                        'to_currency' => $payment_account->currency,
                        'transaction_amount' => $final_amount,
                        'fund_type' => 'real_fund',
                        'old_wallet_amount' => $wallet->balance,
                        'new_wallet_amount' => $wallet->balance - $amount,
                    ]);

                    $wallet->balance -= $amount;
                    $wallet->save();
                }
            }
        }

        return to_route('profile.wallet_detail', Wallet::find(array_key_first($withdraw_wallets))->type)->with('toast', [
            'title' => trans("public.success"),
            'message' => trans('public.successfully_submit_withdrawal_request'),
            'type' => 'success',
        ]);
    }
}
