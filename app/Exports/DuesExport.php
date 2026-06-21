<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DuesExport implements FromCollection, WithHeadings
{
    protected $dues;

    public function __construct($dues)
    {
        $this->dues = $dues;
    }

    public function collection()
    {
        return $this->dues->map(function ($due) {
            return [
                $due->id,
                $due->customer?->name ?? 'N/A',
                $due->customer?->mobile ?? 'N/A',
                $due->bill?->bill_no ?? 'N/A',
                number_format($due->original_amount, 2),
                number_format($due->total_paid, 2),
                number_format($due->remaining_amount, 2),
                $due->due_date?->format('Y-m-d') ?? 'N/A',
                ucfirst($due->status),
                $due->creator?->name ?? 'N/A',
            ];
        });
    }

    public function headings(): array
    {
        return ['ID', 'Customer', 'Mobile', 'Bill No', 'Original', 'Paid', 'Remaining', 'Due Date', 'Status', 'Created By'];
    }
}