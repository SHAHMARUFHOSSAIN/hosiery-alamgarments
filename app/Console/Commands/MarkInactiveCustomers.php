<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Bill;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class MarkInactiveCustomers extends Command
{
    protected $signature = 'customers:mark-inactive';
    protected $description = 'Mark customers inactive if no bill in last 30 days';

    public function handle(): int
    {
        $date = now()->subDays(30);
        
        $customerIds = Bill::where('report_date', '>=', $date)
            ->distinct()
            ->pluck('customer_id');

        $inactiveCount = Customer::where('is_active', true)
            ->whereNotIn('id', $customerIds)
            ->update(['is_active' => false]);

        Customer::where('is_active', false)
            ->whereIn('id', $customerIds)
            ->update(['is_active' => true]);

        $this->info("Marked {$inactiveCount} customers as inactive.");

        return Command::SUCCESS;
    }
}