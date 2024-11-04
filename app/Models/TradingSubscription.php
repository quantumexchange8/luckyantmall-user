<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TradingSubscription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'meta_login',
        'master_meta_login',
        'type',
        'subscription_amount',
        'real_fund',
        'demo_fund',
        'subscription_number',
        'subscription_period_type',
        'subscription_period',
        'expired_at',
        'settlement_period_type',
        'settlement_period',
        'settlement_at',
        'approval_at',
        'status',
        'terminated_at',
        'remarks',
        'extra_conditions',
        'handle_by',
    ];

    protected function casts(): array
    {
        return [
            'expired_at' => 'datetime',
            'settlement_at' => 'datetime',
            'approval_at' => 'datetime',
            'terminated_at' => 'datetime',
        ];
    }

    // Relations
    public function trading_master(): BelongsTo
    {
        return $this->belongsTo(TradingMaster::class, 'master_meta_login', 'meta_login');
    }
}
