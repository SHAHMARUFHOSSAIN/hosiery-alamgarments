<?php

namespace App\Console\Commands;

use App\Models\Due;
use App\Models\User;
use App\Notifications\DueReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class DueReminder extends Command
{
    protected $signature = 'due:reminder {--send : Send email notifications}';

    protected $description = 'List and notify about pending dues due today';

    public function handle(): int
    {
        $todayDues = Due::with(['customer', 'creator'])
            ->whereDate('due_date', now()->toDateString())
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        $this->info('=' . str_repeat('=', 79));
        $this->info('DUE REMINDER - ' . now()->format('M d, Y l'));
        $this->info('=' . str_repeat('=', 79));

        if ($todayDues->isEmpty()) {
            $this->info('No pending dues for today. Great job!');
            return Command::SUCCESS;
        }

        $this->table(
            ['ID', 'Customer', 'Mobile', 'Amount', 'Created By'],
            $todayDues->map(fn ($due) => [
                $due->id,
                $due->customer->name ?? 'N/A',
                $due->customer->mobile ?? 'N/A',
                number_format($due->amount, 2),
                $due->creator->name ?? 'N/A',
            ])
        );

        $this->newLine();
        $this->info("Total Pending Dues: {$todayDues->count()}");
        $this->info('Total Amount: ' . number_format($todayDues->sum('amount'), 2));

        if ($this->option('send')) {
            $this->info('Sending email notifications...');
            $admins = User::where('role', 'admin')->get();
            $admins->each(function ($admin) use ($todayDues) {
                $admin->notify(new DueReminderNotification($todayDues));
            });
            $this->info('Emails sent to ' . $admins->count() . ' admin(s)');
        }

        return Command::SUCCESS;
    }
}