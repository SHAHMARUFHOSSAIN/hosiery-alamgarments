<?php

namespace App\Imports;

use App\Helpers\VoucherHelper;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\Due;
use App\Models\MainBalance;
use App\Models\Payment;

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
        $mobile = $row['customer_mobile'] ?? null;
        $name = $row['customer_name'] ?? 'Unknown';

        $customer = $mobile
            ? Customer::where('mobile', $mobile)->first()
            : null;

        if (!$customer) {
            $customer = Customer::create([
                'name' => $name,
                'mobile' => $mobile,
                'created_by' => $userId,
            ]);
        }

        $bill = Bill::create([
            'bill_no' => $row['bill_no'],
            'customer_id' => $customer->id,
            'shop_name' => $row['shop_name'] ?? null,
            'bill_man' => $row['bill_man'] ?? null,
            'bill_amount' => $row['bill_amount'],
            'discount' => $row['discount'] ?? 0,
            'user_id' => $userId,
        ]);

        $paymentType = $row['payment_type'] ?? 'cash';
        $paymentAmount = $row['payment_amount'] ?? 0;
        $netAmount = (float) $bill->bill_amount - (float) $bill->discount;
        $totalReceived = 0;
        $mainBalanceAmount = 0;

        if ($paymentType === 'check') {
            $checkAmount = $row['check_amount'] ?? 0;
            if ($checkAmount > 0) {
                Payment::create([
                    'bill_id' => $bill->id,
                    'payment_type' => 'check',
                    'amount' => $checkAmount,
                    'check_no' => $row['check_no'] ?? null,
                    'bank_name' => $row['bank_name'] ?? null,
                    'check_date' => $row['check_date'] ?? null,
                    'check_amount' => $checkAmount,
                    'status' => 'pending',
                ]);
                $totalReceived += $checkAmount;
            }
        } elseif ($paymentType === 'card') {
            $cardAmount = $row['card_amount'] ?? $paymentAmount;
            if ($paymentAmount > 0) {
                Payment::create([
                    'bill_id' => $bill->id,
                    'payment_type' => 'cash',
                    'amount' => $paymentAmount,
                    'status' => 'encashed',
                ]);
                $totalReceived += $paymentAmount;
                $mainBalanceAmount += $paymentAmount;
            }
            if ($cardAmount > 0) {
                Payment::create([
                    'bill_id' => $bill->id,
                    'payment_type' => 'card',
                    'amount' => $cardAmount,
                    'card_reference' => $row['card_reference'] ?? null,
                    'card_location' => $row['card_location'] ?? null,
                    'card_amount' => $cardAmount,
                    'card_date' => $row['card_date'] ?? null,
                    'status' => 'pending',
                ]);
                $totalReceived += $cardAmount;
            }
        } elseif ($paymentType === 'tt') {
            $ttAmount = $row['tt_amount'] ?? $paymentAmount;
            Payment::create([
                'bill_id' => $bill->id,
                'payment_type' => 'tt',
                'amount' => $ttAmount,
                'tt_bank_name' => $row['tt_bank_name'] ?? null,
                'tt_account_no' => $row['tt_account_no'] ?? null,
                'tt_amount' => $ttAmount,
                'tt_date' => $row['tt_date'] ?? null,
                'status' => 'encashed',
            ]);
            $totalReceived += $ttAmount;
            $mainBalanceAmount += $ttAmount;
        } elseif ($paymentType === 'due') {
            if ($paymentAmount > 0) {
                Payment::create([
                    'bill_id' => $bill->id,
                    'payment_type' => 'cash',
                    'amount' => $paymentAmount,
                    'status' => 'encashed',
                ]);
                $totalReceived += $paymentAmount;
                $mainBalanceAmount += $paymentAmount;
            }
            Payment::create([
                'bill_id' => $bill->id,
                'payment_type' => 'due',
                'amount' => max(0, $netAmount - $paymentAmount),
                'status' => 'pending',
                'due_date' => $row['due_date'] ?? null,
            ]);
        } else {
            Payment::create([
                'bill_id' => $bill->id,
                'payment_type' => 'cash',
                'amount' => $paymentAmount,
                'status' => 'encashed',
            ]);
            $totalReceived += $paymentAmount;
            $mainBalanceAmount += $paymentAmount;
        }

        if ($mainBalanceAmount > 0) {
            $lastBal = MainBalance::where('branch_id', $userId)->orderBy('id', 'desc')->value('balance') ?? 0;
            MainBalance::create([
                'voucher_no' => VoucherHelper::generateVoucherNo(),
                'name' => 'Sales - Bill #' . $bill->bill_no,
                'amount' => $mainBalanceAmount,
                'balance' => $lastBal + $mainBalanceAmount,
                'type' => 'credit',
                'invoice_no' => $bill->bill_no,
                'party_name' => $customer->name,
                'note' => 'Bill: ৳' . number_format($netAmount, 2) . ' | Received: ' . $paymentType,
                'user_id' => $userId,
                'branch_id' => $userId,
            ]);
        }

        $dueAmount = $netAmount - $totalReceived;
        if ($dueAmount > 0) {
            Due::create([
                'customer_id' => $customer->id,
                'bill_id' => $bill->id,
                'amount' => $dueAmount,
                'original_amount' => $dueAmount,
                'due_date' => $row['due_date'] ?? now()->addDays(7)->toDateString(),
                'status' => 'pending',
                'created_by' => $userId,
            ]);
        }

        return ['action' => 'inserted', 'id' => $bill->id];
    }
}
