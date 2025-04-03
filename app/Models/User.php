<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function setReferralId(): void
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomString = 'LKMx';

        $length = 10 - strlen($randomString);

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        $this->referral_code = $randomString;
        $this->save();
    }

    public function getChildrenIds(): array
    {
        return User::query()->where('hierarchyList', 'like', '%-' . $this->id . '-%')
            ->pluck('id')
            ->toArray();
    }

    // Relations
    public function group(): HasOne
    {
        return $this->hasOne(GroupHasUser::class, 'user_id');
    }

    public function trading_accounts(): HasMany
    {
        return $this->hasMany(TradingAccount::class, 'user_id', 'id');
    }

    public function trading_users(): HasMany
    {
        return $this->hasMany(TradingUser::class, 'user_id', 'id');
    }

    public function active_trading_subscriptions(): HasMany
    {
        return $this->hasMany(TradingSubscription::class, 'user_id', 'id')
            ->where('status', 'active');
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class, 'user_id', 'id');
    }

    public function directs(): HasMany
    {
        return $this->hasMany(User::class, 'upline_id', 'id');
    }

    public function upline(): BelongsTo
    {
        return $this->belongsTo(User::class, 'upline_id', 'id');
    }

    public function cart_items(): HasManyThrough
    {
        return $this->hasManyThrough(CartItem::class, Cart::class, 'user_id', 'cart_id');
    }
}
