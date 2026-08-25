<?php

namespace App\Support;

final class EgyptianPhoneNumber
{
    public static function normalize(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        $digits = ltrim($digits, '0');

        if (str_starts_with($digits, '20')) {
            $digits = substr($digits, 2);
        }

        if (! preg_match('/^1[0125][0-9]{8}$/', $digits)) {
            return null;
        }

        return '+20'.$digits;
    }

    public static function isValid(string $phone): bool
    {
        return self::normalize($phone) !== null;
    }
}
