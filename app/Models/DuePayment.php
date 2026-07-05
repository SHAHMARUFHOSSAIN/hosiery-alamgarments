<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DuePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'due_id',
        'amount',
        'discount',
        'payment_type',
        'bank_name',
        'check_no',
        'check_date',
        'check_amount',
        'check_reminder_date',
        'check_photo',
        'payment_date',
        'remaining_amount',
        'encashed_amount',
        'status',
        'note',
        'transaction_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'discount' => 'decimal:2',
            'check_amount' => 'decimal:2',
            'encashed_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'payment_date' => 'date',
            'check_date' => 'date',
            'check_reminder_date' => 'date',
        ];
    }

    public function due(): BelongsTo
    {
        return $this->belongsTo(Due::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
