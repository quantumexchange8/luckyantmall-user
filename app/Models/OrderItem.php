<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'product_id',
        'trading_master_id',
        'price_per_unit',
        'quantity',
        'delivered_at',
        'cancelled_at',
        'status'
    ];

    protected function casts(): array
    {
        return [
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }
}
