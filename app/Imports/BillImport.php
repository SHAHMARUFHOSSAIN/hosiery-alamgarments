<?php

namespace App\Imports;

class BillImport
{
    public function headings(): array
    {
        return [
            'bill_no', 'customer_name', 'customer_mobile', 'shop_name', 'bill_man',
            'bill_amount', 'discount', 'payment_type', 'payment_amount',
            'due_date', 'card_reference', 'card_location', 'card_amount', 'card_date',
            'tt_bank_name', 'tt_account_no', 'tt_amount', 'tt_date',
            'bank_name', 'check_no', 'check_amount', 'check_date',
        ];
    }

    public function expectedHeadings(): array
    {
        return [
            'Bill No', 'Customer Name', 'Customer Mobile', 'Shop Name', 'Bill Man',
            'Bill Amount', 'Discount', 'Payment Type', 'Payment Amount',
            'Due Date', 'Card Reference', 'Card Location', 'Card Amount', 'Card Date',
            'TT Bank Name', 'TT Account No', 'TT Amount', 'TT Date',
            'Bank Name', 'Check No', 'Check Amount', 'Check Date',
        ];
    }

    public function validateRow(array $row, int $rowIndex): array
    {
        $errors = [];

        if (empty($row['bill_no'])) {
            $errors[] = 'Bill No is required';
        }

        if (empty($row['customer_name']) && empty($row['customer_mobile'])) {
            $errors[] = 'Either Customer Name or Customer Mobile is required';
        }

        if (empty($row['bill_amount']) || !is_numeric($row['bill_amount'])) {
            $errors[] = 'Bill Amount must be a number';
        }

        $paymentType = $row['payment_type'] ?? '';
        $allowedTypes = ['cash', 'due', 'tt', 'card', 'check'];
        if (!in_array($paymentType, $allowedTypes)) {
            $errors[] = "Payment Type must be one of: " . implode(', ', $allowedTypes);
        }

        return $errors;
    }

    public function upsert(array $row, int $userId): array
    {
        return ['action' => 'inserted'];
    }
}
