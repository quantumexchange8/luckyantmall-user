<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Item extends Model
{
    use SoftDeletes, HasTranslations;

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    public array $translatable = ['name'];

    protected function casts(): array
    {
        return [
            'name' => 'json',
        ];
    }

    // Relations
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'item_id', 'id');
    }
}
