<?php

namespace App\Services;

use App\Models\TradingSubscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MainApiService
{
    private string $base_url = "http://luckyant-trading-user.test/api";
    private string $token = "4e29cb4acbd81a9dd9c83e2a2a89eb2c";

    public function sync_user_account($user)
    {
        $response =  Http::acceptJson()
            ->withHeaders([
                'X-API-KEY' => $this->token,
            ])
            ->post($this->base_url . "/sync_user_account", [
                'user' => $user
            ]);

        if ($response->successful()) {
            $responseData = $response->json();
            if ($responseData['success']) {
                Log::info('Sync successful', ['response' => $responseData]);
            } else {
                Log::error('Sync failed', ['response' => $responseData]);
            }
        } else {
            Log::error('HTTP request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        }
    }

    public function sync_trading_account($user, $meta_account)
    {
        $response =  Http::acceptJson()
            ->withHeaders([
                'X-API-KEY' => $this->token,
            ])
            ->post($this->base_url . "/sync_trading_account", [
                'username' => $user->username,
                'meta_account' => $meta_account
            ]);

        if ($response->successful()) {
            $responseData = $response->json();
            if ($responseData['success']) {
                Log::info('Sync successful', ['response' => $responseData]);
            } else {
                Log::error('Sync failed', ['response' => $responseData]);
            }
        } else {
            Log::error('HTTP request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        }
    }

    public function sync_trading_subscription($user, $subscription)
    {
        $response =  Http::acceptJson()
            ->withHeaders([
                'X-API-KEY' => $this->token,
            ])
            ->post($this->base_url . "/sync_trading_subscription", [
                'username' => $user->username,
                'subscription' => $subscription
            ]);

        if ($response->successful()) {
            $responseData = $response->json();
            if ($responseData['success']) {
                Log::info('Sync successful', ['response' => $responseData]);

                TradingSubscription::find($subscription->id)->update([
                    'group_subscription_number' => $responseData['subscription_number'],
                ]);
            } else {
                Log::error('Sync failed', ['response' => $responseData]);
            }
        } else {
            Log::error('HTTP request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        }
    }

    public function sign_in_authorization($username)
    {
        return Http::acceptJson()
            ->withHeaders([
                'X-API-KEY' => $this->token,
            ])
            ->post($this->base_url . "/sign_in_authorization", [
                'username' => $username,
            ])->json();
    }
}
