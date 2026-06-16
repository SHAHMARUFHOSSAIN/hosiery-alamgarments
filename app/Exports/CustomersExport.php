<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomersExport implements FromCollection, WithHeadings
{
    protected $customers;

    public function __construct($customers)
    {
        $this->customers = $customers;
    }

    public function collection()
    {
        return $this->customers->map(function ($customer) {
            $prevDue = $customer->previousDues()->latest()->first();
            return [
                $customer->id,
                $customer->name,
                $customer->mobile ?? 'N/A',
                $customer->location ?? 'N/A',
                number_format($customer->opening_balance, 2),
                $customer->creator->name ?? 'N/A',
                $customer->created_at->format('Y-m-d'),
                $prevDue ? number_format($prevDue->original_amount, 2) : 'N/A',
                $prevDue ? number_format($prevDue->total_paid, 2) : 'N/A',
                $prevDue ? number_format($prevDue->remaining_amount, 2) : 'N/A',
                $prevDue ? ucfirst($prevDue->status) : 'N/A',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID', 'Name', 'Mobile', 'Location', 'Opening Balance',
            'Created By', 'Created Date',
            'Prev. Due Original', 'Prev. Due Paid', 'Prev. Due Remaining', 'Prev. Due Status',
        ];
    }
}
