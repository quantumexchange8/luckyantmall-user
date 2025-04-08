<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TradingMasterToGroup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'trading_master_id',
        'group_id',
    ];

    // Relation
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }
}
