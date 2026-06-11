<?php

namespace App\Helpers;

class NumberHelper
{
    public static function toWords($number): string
    {
        $number = (float) $number;
        $decimal = round($number - floor($number), 2);
        $whole = floor($number);

        $words = self::convertNumber($whole);

        if ($decimal > 0) {
            $decimalStr = (string) ($decimal * 100);
            $words .= ' point ' . self::convertNumber((int) $decimalStr);
        }

        return ucwords($words) . ' Taka Only';
    }

    private static function convertNumber($number): string
    {
        if ($number == 0) return 'zero';

        $ones = [
            '', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine',
            'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen',
            'seventeen', 'eighteen', 'nineteen'
        ];
        $tens = [
            '', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'
        ];

        $words = '';

        if ($number < 20) {
            $words = $ones[$number];
        } elseif ($number < 100) {
            $words = $tens[(int)($number / 10)];
            if ($number % 10 > 0) {
                $words .= ' ' . $ones[$number % 10];
            }
        } elseif ($number < 1000) {
            $words = $ones[(int)($number / 100)] . ' hundred';
            if ($number % 100 > 0) {
                $words .= ' ' . self::convertNumber($number % 100);
            }
        } elseif ($number < 100000) {
            $words = self::convertNumber((int)($number / 1000)) . ' thousand';
            if ($number % 1000 > 0) {
                $words .= ' ' . self::convertNumber($number % 1000);
            }
        } elseif ($number < 10000000) {
            $words = self::convertNumber((int)($number / 100000)) . ' lakh';
            if ($number % 100000 > 0) {
                $words .= ' ' . self::convertNumber($number % 100000);
            }
        } else {
            $words = self::convertNumber((int)($number / 10000000)) . ' crore';
            if ($number % 10000000 > 0) {
                $words .= ' ' . self::convertNumber($number % 10000000);
            }
        }

        return $words;
    }
}
