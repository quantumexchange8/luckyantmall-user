<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductBundlePrice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'min_quantity',
        'price_per_unit',
    ];
}
