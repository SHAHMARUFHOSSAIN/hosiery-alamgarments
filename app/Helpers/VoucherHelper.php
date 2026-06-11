<?php

namespace App\Helpers;

use App\Models\MainBalance;
use App\Models\Setting;

class VoucherHelper
{
    public static function generateVoucherNo(): string
    {
        $prefix = Setting::get('voucher_prefix', 'V');
        $year = now()->format('Y');
        $month = now()->format('m');

        $last = MainBalance::where('voucher_no', 'like', "{$prefix}-{$year}{$month}-%")
            ->orderBy('voucher_no', 'desc')
            ->value('voucher_no');

        if ($last) {
            $parts = explode('-', $last);
            $seq = (int) end($parts) + 1;
        } else {
            $seq = 1;
        }

        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $seq);
    }
}
