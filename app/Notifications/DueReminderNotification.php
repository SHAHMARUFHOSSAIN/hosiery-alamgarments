<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DueReminderNotification extends Notification
{
    use Queueable;

    protected $dues;

    public function __construct($dues)
    {
        $this->dues = $dues;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Due Reminder - ' . now()->format('M d, Y') . ' (' . $this->dues->count() . ' dues)')
            ->line('**Daily Due Reminder Report - ' . now()->format('M d, Y l') . '**')
            ->line('Total Pending: **' . $this->dues->count() . '** dues worth **$' . number_format($this->dues->sum('amount'), 2) . '**')
            ->line('---');

        foreach ($this->dues as $due) {
            $mail->line("
- Customer: {$due->customer->name} ({$due->customer->mobile})
  Amount: $" . number_format($due->amount, 2) . "
  Due Date: {$due->due_date->format('M d, Y')}
  Created By: {$due->creator->name}
            ");
        }

        return $mail->line('---')
            ->action('View in App', url('/dues/daily-report'))
            ->line('This is an automated notification from Daily Report System.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'dues_count' => $this->dues->count(),
            'total_amount' => $this->dues->sum('amount'),
            'date' => now()->toDateString(),
        ];
    }
}