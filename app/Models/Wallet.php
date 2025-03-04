<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'address',
        'currency',
        'currency_symbol',
        'balance',
        'balance_visibility',
    ];
}
