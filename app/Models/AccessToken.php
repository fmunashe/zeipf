<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_name',
        'protocol_id',
        'token',
        'expiration_date',
        'short_code',
        'status'
    ];
}
