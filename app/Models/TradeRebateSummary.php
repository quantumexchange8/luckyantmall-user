<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TradeRebateSummary extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'upline_user_id',
        'upline_rank_id',
        'downline_id',
        'downline_rank_id',
        'subs_id',
        'user_id',
        'meta_login',
        'acc_type',
        'symbol_group',
        'closed_time',
        'volume',
        'rebate',
        'currency_rate',
        'from_currency',
        'to_currency',
        'converted_amount',
        'status',
        'execute_at',
    ];

    protected function casts(): array
    {
        return [
            'closed_time' => 'datetime',
            'execute_at' => 'datetime',
        ];
    }

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'upline_user_id', 'id');
    }

    public function trader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
