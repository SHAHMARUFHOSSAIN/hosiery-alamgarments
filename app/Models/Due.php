<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Due extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'bill_id',
        'amount',
        'original_amount',
        'due_date',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'original_amount' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function duePayments(): HasMany
    {
        return $this->hasMany(DuePayment::class)->orderBy('created_at', 'asc');
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->duePayments()->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return $this->original_amount - $this->total_paid;
    }

    public function hasPartialPayments(): bool
    {
        return $this->duePayments()->count() > 0;
    }

    public function markAsPaid(): void
    {
        $this->update(['status' => 'paid']);
    }
}