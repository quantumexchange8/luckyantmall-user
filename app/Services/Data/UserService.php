<?php

namespace App\Services\Data;

use App\Models\Country;
use App\Models\Group;
use App\Models\GroupHasUser;
use App\Models\User;
use App\Models\Wallet;
use App\Services\GroupService;
use App\Services\MainApiService;
use App\Services\RunningNumberService;
use DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    public function createUser(array $validator, string $referral_code = null)
    {
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

        $id_no = 'LID' . Str::padLeft($user->id, 6, "0");
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

        (new MainApiService)->sync_user_account($user);

        return $user;
    }
}
