<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepositProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'name',
        'account_number',
        'bank_name',
        'bank_branch',
        'crypto_tether',
        'crypto_network',
        'country_id',
        'currency',
        'edited_by',
    ];

    // Relations
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }
}
