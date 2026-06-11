<?php

namespace App\Imports;

use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerImport
{
    public function headings(): array
    {
        return ['name', 'mobile', 'location'];
    }

    public function expectedHeadings(): array
    {
        return ['Name', 'Mobile', 'Location'];
    }

    public function validateRow(array $row, int $rowIndex): array
    {
        $errors = [];

        if (empty($row['name'])) {
            $errors[] = 'Name is required';
        }

        if (empty($row['mobile'])) {
            $errors[] = 'Mobile is required';
        }

        return $errors;
    }

    public function upsert(array $row, int $userId): array
    {
        $existing = Customer::where('mobile', $row['mobile'])->first();

        if ($existing) {
            $existing->update([
                'name' => $row['name'],
                'location' => $row['location'] ?? $existing->location,
            ]);
            return ['action' => 'updated', 'id' => $existing->id];
        }

        $customer = Customer::create([
            'name' => $row['name'],
            'mobile' => $row['mobile'],
            'location' => $row['location'] ?? null,
            'created_by' => $userId,
        ]);

        return ['action' => 'inserted', 'id' => $customer->id];
    }
}
