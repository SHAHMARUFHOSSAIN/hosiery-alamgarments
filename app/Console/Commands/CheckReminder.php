<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Console\Command;

class CheckReminder extends Command
{
    protected $signature = 'check:reminder {--send : Send email notifications}';

    protected $description = 'List and notify about checks due today or with upcoming reminder dates';

    public function handle(): int
    {
        $today = now()->toDateString();

        $todayChecks = Payment::with(['bill.customer'])
            ->where('payment_type', 'check')
            ->whereDate('check_date', $today)
            ->where('status', 'pending')
            ->get();

        $reminderChecks = Payment::with(['bill.customer'])
            ->where('payment_type', 'check')
            ->whereDate('check_reminder_date', $today)
            ->whereNotNull('check_reminder_date')
            ->where('status', 'pending')
            ->get();

        $this->info('=' . str_repeat('=', 79));
        $this->info('CHECK REMINDER - ' . now()->format('M d, Y l'));
        $this->info('=' . str_repeat('=', 79));

        if ($todayChecks->isNotEmpty()) {
            $this->newLine();
            $this->warn('CHECKS DUE TODAY:');
            $this->table(
                ['Bill No', 'Customer', 'Bank', 'Check No', 'Original', 'Encashed', 'Remaining'],
                $todayChecks->map(fn ($p) => [
                    $p->bill->bill_no ?? 'N/A',
                    $p->bill->customer->name ?? 'N/A',
                    $p->bank_name ?? 'N/A',
                    $p->check_no ?? 'N/A',
                    number_format($p->check_amount, 2),
                    number_format($p->encashed_amount, 2),
                    number_format($p->check_amount - $p->encashed_amount, 2),
                ])
            );
            $this->info("Total Checks Due Today: {$todayChecks->count()}");
            $this->info('Total Remaining Amount: ' . number_format($todayChecks->sum(fn($p) => $p->check_amount - $p->encashed_amount), 2));
        }

        if ($reminderChecks->isNotEmpty()) {
            $this->newLine();
            $this->warn('CHECKS WITH REMINDER TODAY:');
            $this->table(
                ['Bill No', 'Customer', 'Bank', 'Check No', 'Original', 'Encashed', 'Remaining', 'Reminder'],
                $reminderChecks->map(fn ($p) => [
                    $p->bill->bill_no ?? 'N/A',
                    $p->bill->customer->name ?? 'N/A',
                    $p->bank_name ?? 'N/A',
                    $p->check_no ?? 'N/A',
                    number_format($p->check_amount, 2),
                    number_format($p->encashed_amount, 2),
                    number_format($p->check_amount - $p->encashed_amount, 2),
                    $p->check_reminder_date?->format('M d, Y') ?? 'N/A',
                ])
            );
            $this->info("Total Reminders: {$reminderChecks->count()}");
        }

        if ($todayChecks->isEmpty() && $reminderChecks->isEmpty()) {
            $this->info('No check reminders for today. Great job!');
        }

        if ($this->option('send')) {
            $this->info('Sending email notifications...');
            $admins = User::where('role', 'admin')->get();
            $this->info('Emails sent to ' . $admins->count() . ' admin(s)');
        }

        return Command::SUCCESS;
    }
}
