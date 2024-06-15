<?php

namespace App\Http\Enums;

class Protocols extends Enum
{
    private const SOAP         = 0b0001;
    private const REST         = 0b0010;
    private const WEBSOCKETS   = 0b0011;
    private const GRAPHQL      = 0b0100;
}
