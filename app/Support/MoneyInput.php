<?php

namespace App\Support;

use Illuminate\Http\Request;
use InvalidArgumentException;

class MoneyInput
{
    public static function fromRequest(Request $request, string $key): string
    {
        return self::normalize($request->input($key));
    }

    public static function normalize(mixed $value): string
    {
        if ($value === null) {
            throw new InvalidArgumentException('Money value cannot be null.');
        }

        $normalized = trim((string) $value);
        $normalized = str_replace([',', ' '], '', $normalized);

        if ($normalized === '' || !preg_match('/^-?\d+(?:\.\d{1,4})?$/', $normalized)) {
            throw new InvalidArgumentException('Money value must be a valid decimal amount.');
        }

        return number_format((float) $normalized, 2, '.', '');
    }

    public static function format(mixed $value): string
    {
        return number_format((float) self::normalize($value), 2);
    }
}