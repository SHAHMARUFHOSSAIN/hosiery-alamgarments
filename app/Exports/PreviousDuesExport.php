<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PreviousDuesExport implements FromCollection, WithHeadings
{
    protected $previousDues;

    public function __construct($previousDues)
    {
        $this->previousDues = $previousDues;
    }

    public function collection()
    {
        return $this->previousDues->map(function ($pd) {
            $status = match(true) {
                $pd->status === 'paid' => 'Paid',
                $pd->hasPartialPayments() => 'Partial',
                default => 'Pending',
            };
            return [
                $pd->id,
                $pd->customer->name ?? 'N/A',
                $pd->customer->mobile ?? 'N/A',
                number_format($pd->original_amount, 2),
                number_format($pd->total_paid, 2),
                number_format($pd->remaining_amount, 2),
                $status,
                $pd->creator->name ?? 'N/A',
                $pd->created_at->format('Y-m-d'),
            ];
        });
    }

    public function headings(): array
    {
        return ['ID', 'Customer', 'Mobile', 'Original', 'Paid', 'Remaining', 'Status', 'Created By', 'Date'];
    }
}
