<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name',
        'item_id',
        'category_id',
        'descriptions',
        'quantity',
        'base_price',
        'discount_type',
        'discount_percentage',
        'discount_price',
        'final_price_type',
        'final_price',
        'required_delivery',
    ];

    // Relations
    public function price_bundles(): HasMany
    {
        return $this->hasMany(ProductBundlePrice::class, 'product_id', 'id');
    }

    public function masters(): HasManyThrough
    {
        return $this->hasManyThrough(
            TradingMaster::class,
            ProductHasMaster::class,
            'product_id',
            'id',
            'id',
            'trading_master_id'
        );
    }
}
