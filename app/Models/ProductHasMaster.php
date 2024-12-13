<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductHasMaster extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'trading_master_id',
    ];
}
