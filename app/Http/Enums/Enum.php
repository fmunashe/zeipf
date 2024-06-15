<?php

namespace App\Http\Enums;

use BadMethodCallException;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use MyCLabs\Enum\Enum as BaseEnum;

abstract class Enum extends BaseEnum implements Htmlable
{
    public static function parse($value)
    {
        if(is_a($value, static::class))
        {
            return $value;
        }

        if(is_numeric($value))
        {
            $value = static::search(intval($value));
        }

        try {
            if(is_string($value))
            {
                return static::$value();
            }
        } catch (BadMethodCallException $ex) {
        }
    }

    public static function toValueKeyArray()
    {
        $collection = collect([]);

        foreach(static::toArray() as $key => $value)
        {
            $collection[$value] = static::$key();
        }

        return $collection;
    }

    public function humanize()
    {
        return Str::title(str_replace('_', ' ', Str::snake($this->getKey())));
    }

    public function toHtml()
    {
        return $this->humanize();
    }

    public function jsonSerialize()
    {
        return $this->humanize();
    }
}
