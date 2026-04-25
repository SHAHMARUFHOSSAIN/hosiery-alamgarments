<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BillsExport implements FromCollection, WithHeadings
{
    protected $bills;

    public function __construct($bills)
    {
        $this->bills = $bills;
    }

    public function collection()
    {
        return $this->bills->map(function ($bill) {
            return [
                $bill->id,
                $bill->bill_no,
                $bill->customer->name ?? 'N/A',
                $bill->shop_name ?? 'N/A',
                number_format($bill->bill_amount, 2),
                number_format($bill->discount, 2),
                number_format($bill->bill_amount - $bill->discount, 2),
                $bill->user->name ?? 'N/A',
                $bill->created_at->format('Y-m-d H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return ['ID', 'Bill No', 'Customer', 'Shop', 'Amount', 'Discount', 'Net', 'User', 'Date'];
    }
}