<?php

namespace App\Helpers;

class LanguageHelper
{
    private static array $banglaDigits = [
        '0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪',
        '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯',
    ];

    public static function toBanglaDigits(string $value): string
    {
        return str_replace(array_keys(self::$banglaDigits), array_values(self::$banglaDigits), $value);
    }

    public static function convertDigits(string $value, string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        if ($locale === 'bn') {
            return self::toBanglaDigits($value);
        }
        return $value;
    }

    public static function formatCurrency(float $amount, int $decimals = 2, string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $formatted = number_format($amount, $decimals, '.', ',');
        $formatted = self::convertDigits($formatted, $locale);
        return '৳ ' . $formatted;
    }

    public static function formatNumber(float $number, int $decimals = 0, string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $formatted = number_format($number, $decimals, '.', ',');
        return self::convertDigits($formatted, $locale);
    }

    public static function formatDate($date, string $format = 'M d, Y', string $locale = null): string
    {
        if (!$date) return '';
        $locale = $locale ?? app()->getLocale();
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }
        $formatted = $date->format($format);
        return self::convertDigits($formatted, $locale);
    }

    public static function formatDateFull($date, string $locale = null): string
    {
        return self::formatDate($date, 'M d, Y h:i A', $locale);
    }

    public static function amountInWords(float $amount, string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        if ($locale === 'bn') {
            return \App\Helpers\NumberHelper::toWords((int) $amount);
        }
        return self::numberToEnglishWords((int) $amount);
    }

    private static function numberToEnglishWords(int $number): string
    {
        if ($number === 0) return 'Zero';

        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
            'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $words = '';
        if ($number >= 10000000) {
            $words .= self::numberToEnglishWords(intdiv($number, 10000000)) . ' Crore ';
            $number %= 10000000;
        }
        if ($number >= 100000) {
            $words .= self::numberToEnglishWords(intdiv($number, 100000)) . ' Lakh ';
            $number %= 100000;
        }
        if ($number >= 1000) {
            $words .= self::numberToEnglishWords(intdiv($number, 1000)) . ' Thousand ';
            $number %= 1000;
        }
        if ($number >= 100) {
            $words .= $ones[intdiv($number, 100)] . ' Hundred ';
            $number %= 100;
        }
        if ($number >= 20) {
            $words .= $tens[intdiv($number, 10)] . ' ';
            $number %= 10;
        }
        if ($number > 0) {
            $words .= $ones[$number] . ' ';
        }

        return trim($words) . ' Taka Only';
    }
}
