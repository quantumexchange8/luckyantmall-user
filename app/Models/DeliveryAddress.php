<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryAddress extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'receiver_name',
        'receiver_phone',
        'address',
        'unit_number',
        'postal_code',
        'country_id',
        'state_name',
        'city_name',
        'default_address',
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }
}
