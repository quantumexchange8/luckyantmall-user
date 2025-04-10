<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TradeHistory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'master_id',
        'master_lot',
        'master_pnl',
        'master_num_trades',
        'master_total_funds',
        'subscription_id',
        'user_id',
        'meta_login',
        'subscription_funds',
        'subscription_pamm_pct',
        'symbol',
        'ticket',
        'time_open',
        'trade_type',
        'volume',
        'price_open',
        'time_close',
        'price_close',
        'trade_status',
        'trade_profit',
        'trade_profit_pct',
        'trade_swap',
        'rebate_status',
        'master_acc_type',
    ];

    protected function casts(): array
    {
        return [
            'time_open' => 'datetime',
            'time_close' => 'datetime',
        ];
    }
}
