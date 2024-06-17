<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccumulatedCredit extends Model
{
    use HasFactory;

    protected $fillable = [
        'ecNumber',
        'valuationDate',
        'zwlInterest',
        'usdInterest',
        'zwlOpening',
        'zwlClosing',
        'usdOpening',
        'usdClosing',
    ];
}
