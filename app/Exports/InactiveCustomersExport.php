<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Customer;

class InactiveCustomersExport implements FromCollection, WithHeadings
{
    protected $customers;

    public function __construct($customers)
    {
        $this->customers = $customers;
    }

    public function collection()
    {
        return $this->customers->map(function ($customer) {
            $lastBill = $customer->bills()->latest('report_date')->first();
            return [
                $customer->id,
                $customer->name,
                $customer->mobile ?? 'N/A',
                $customer->location ?? 'N/A',
                $lastBill ? $lastBill->report_date->format('Y-m-d') : 'N/A',
                $customer->created_at->format('Y-m-d'),
            ];
        });
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Mobile', 'Location', 'Last Bill Date', 'Created Date'];
    }
}