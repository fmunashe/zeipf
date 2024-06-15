<?php

namespace App\Http\Enums;

class EconetTransactionTypes extends Enum
{
    private const FIRST  = 0b0001;
    private const COMPLETE   = 0b0011;
    private const PENDING   = 0b0100;
}
