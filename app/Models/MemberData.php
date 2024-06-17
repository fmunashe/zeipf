<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberData extends Model
{
    use HasFactory;

    protected $fillable = [
        'national_id',
        'name',
        'surname',
        'dob',
        'doj',
        'doe',
        'memberType',
        'memberStatus',
        'pin',
        'memberCategory',
        'ecNumber',
        'lifeStatus'
    ];
}
