<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtpRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'email',
        'type',
        'otp',
    ];
}
