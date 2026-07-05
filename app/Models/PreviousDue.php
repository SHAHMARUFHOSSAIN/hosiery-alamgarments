<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PreviousDue extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'amount',
        'original_amount',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'original_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PreviousDuePayment::class)->orderBy('created_at', 'asc');
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->payments()
            ->where(fn($q) => $q->where('payment_type', '!=', 'check')->orWhere('status', 'encashed'))
            ->sum('amount');
    }

    public function getTotalDiscountAttribute(): float
    {
        return $this->payments()
            ->where(fn($q) => $q->where('payment_type', '!=', 'check')->orWhere('status', 'encashed'))
            ->sum('discount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->original_amount - $this->total_paid - $this->total_discount);
    }

    public function hasPartialPayments(): bool
    {
        return $this->payments()->count() > 0;
    }

    public function markAsPaid(): void
    {
        $this->update(['status' => 'paid']);
    }
}
