<?php

namespace App\Services\Data;

use App\Models\TradingAccount;
use Illuminate\Support\Facades\DB;

class UpdateTradingAccount
{
    public function execute($meta_login, $data): TradingAccount
    {
        return $this->updateTradingAccount($meta_login, $data);
    }

    public function updateTradingAccount($meta_login, $data): TradingAccount
    {
        $tradingAccount = TradingAccount::query()
            ->where('meta_login', $meta_login)
            ->where('status', 'active')
            ->first();

        if (\Auth::user()->role == 'user') {
            $tradingAccount->real_fund = $data['balance'];
        } else {
            $tradingAccount->demo_fund = $data['balance'];
        }
        $tradingAccount->currency_digits = $data['currencyDigits'];
        $tradingAccount->balance = $data['balance'];
        $tradingAccount->credit = $data['credit'];
        $tradingAccount->margin_leverage = $data['marginLeverage'];
        $tradingAccount->equity = $data['equity'];
        $tradingAccount->floating = $data['floating'];
        DB::transaction(function () use ($tradingAccount) {
            $tradingAccount->save();
        });

        return $tradingAccount;
    }
}
