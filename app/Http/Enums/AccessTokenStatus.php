<?php

namespace App\Http\Enums;

class AccessTokenStatus extends Enum
{
    private const Active  = 0b0001;
    private const Suspended   = 0b0011;
    private const Deleted   = 0b0100;
}
