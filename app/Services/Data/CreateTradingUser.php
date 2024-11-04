<?php

namespace App\Services\Data;

use App\Models\TradingUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateTradingUser
{
    public function execute(User $user, $data): TradingUser
    {
        return $this->storeNewUser($user, $data);
    }

    public function storeNewUser(User $user, $data): TradingUser
    {
        $tradingUser = new TradingUser();
        $tradingUser->user_id = $user->id;
        $tradingUser->name = $data['name'];
        $tradingUser->meta_login = $data['login'];
        $tradingUser->account_type_id = 1;
        $tradingUser->leverage = $data['leverage'];

        DB::transaction(function () use ($tradingUser) {
            $tradingUser->save();
        });

        return $tradingUser;
    }
}
