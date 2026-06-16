<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreviousDuePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'previous_due_id',
        'amount',
        'payment_type',
        'payment_date',
        'remaining_amount',
        'note',
        'transaction_id',
        'bank_name',
        'check_no',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function previousDue(): BelongsTo
    {
        return $this->belongsTo(PreviousDue::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
