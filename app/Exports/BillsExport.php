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
            $checkPayment = $bill->payments()->where('payment_type', 'check')->first();
            return [
                $bill->id,
                $bill->bill_no,
                $bill->customer?->name ?? 'N/A',
                $bill->shop_name ?? 'N/A',
                $bill->bill_man ?? 'N/A',
                number_format($bill->bill_amount, 2),
                number_format($bill->discount, 2),
                number_format($bill->bill_amount - $bill->discount, 2),
                $bill->user?->name ?? 'N/A',
                $checkPayment ? $checkPayment->payment_type : 'N/A',
                $checkPayment ? $checkPayment->bank_name : 'N/A',
                $checkPayment ? $checkPayment->check_no : 'N/A',
                $checkPayment ? $checkPayment->check_amount : 'N/A',
                $checkPayment ? $checkPayment->check_date?->format('Y-m-d') : 'N/A',
                $bill->report_date?->format('Y-m-d') ?? 'N/A',
            ];
        });
    }

    public function headings(): array
    {
        return ['ID', 'Bill No', 'Customer', 'Shop', 'Bill Man', 'Amount', 'Discount', 'Net', 'User', 'Payment Type', 'Bank', 'Check No', 'Check Amount', 'Check Date', 'Date'];
    }
}