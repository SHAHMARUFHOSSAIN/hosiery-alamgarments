<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckEncashment extends Model
{
    protected $fillable = [
        'payment_id',
        'encash_amount',
        'encash_date',
        'next_due_date',
        'note',
        'transaction_id',
        'user_id',
    ];

    protected $casts = [
        'encash_date' => 'date',
        'next_due_date' => 'date',
        'encash_amount' => 'decimal:2',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
