<?php

namespace App\Helpers;

class PhoneHelper
{
    /**
     * Normalize phone number to 08xx format.
     */
    public static function normalize(string $phone): string
    {
        // Remove spaces, dashes, dots
        $phone = preg_replace('/[\s\-\.]/', '', $phone);

        // Convert +62 or 62 prefix to 0
        $phone = preg_replace('/^(\+?62)/', '0', $phone);

        return $phone;
    }

    /**
     * Mask phone number for privacy: 0812-XXXX-7890
     */
    public static function mask(string $phone): string
    {
        $phone = self::normalize($phone);

        if (strlen($phone) < 8) {
            return $phone;
        }

        $first = substr($phone, 0, 4);
        $last = substr($phone, -4);

        return $first.'-XXXX-'.$last;
    }

    /**
     * Validate Indonesian phone number format.
     */
    public static function validate(string $phone): bool
    {
        $phone = self::normalize($phone);

        return (bool) preg_match('/^08\d{8,12}$/', $phone);
    }
}
