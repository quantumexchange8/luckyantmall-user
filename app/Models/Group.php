<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'group_leader_id',
        'color',
        'parent_group_id',
        'edited_by',
    ];

    // Relations
    public function group_leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'group_leader_id', 'id');
    }

    public function group_has_user(): HasMany
    {
        return $this->hasMany(GroupHasUser::class, 'group_id', 'id');
    }

    public function group_rank_settings(): hasMany
    {
        return $this->hasMany(GroupRankSetting::class, 'group_id', 'id');
    }
}
