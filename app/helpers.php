<?php

use App\Helpers\LanguageHelper;

if (!function_exists('format_number')) {
    function format_number($number, $decimals = 0): string
    {
        return LanguageHelper::formatNumber((float) $number, (int) $decimals);
    }
}

if (!function_exists('format_currency')) {
    function format_currency($amount, $decimals = 2): string
    {
        return LanguageHelper::formatCurrency((float) $amount, (int) $decimals);
    }
}

if (!function_exists('format_date')) {
    function format_date($date, $format = 'M d, Y'): string
    {
        return LanguageHelper::formatDate($date, $format);
    }
}

if (!function_exists('format_date_full')) {
    function format_date_full($date): string
    {
        return LanguageHelper::formatDateFull($date);
    }
}

if (!function_exists('bn_digits')) {
    function bn_digits(string $value): string
    {
        return LanguageHelper::toBanglaDigits($value);
    }
}
