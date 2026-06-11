<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'payment_type',
        'amount',
        'details',
        'bank_name',
        'check_no',
        'check_date',
        'check_reminder_date',
        'check_photo',
        'check_amount',
        'encashed_amount',
        'partially_encashed',
        'tt_bank_name',
        'tt_account_no',
        'tt_amount',
        'tt_date',
        'card_reference',
        'card_location',
        'card_amount',
        'card_date',
        'status',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'check_amount' => 'decimal:2',
            'encashed_amount' => 'decimal:2',
            'tt_amount' => 'decimal:2',
            'card_amount' => 'decimal:2',
            'partially_encashed' => 'boolean',
            'due_date' => 'date',
            'check_date' => 'date',
            'check_reminder_date' => 'date',
            'tt_date' => 'date',
            'card_date' => 'date',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function checkEncashments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CheckEncashment::class);
    }

    public function remainingCheckAmount(): float
    {
        return (float) $this->check_amount - (float) $this->encashed_amount;
    }

    public function hasPartialEncashments(): bool
    {
        return $this->partially_encashed;
    }
}