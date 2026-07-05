<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

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

    protected static ?bool $hasDuePaymentsStatus = null;

    protected function duePaymentsWithEncashment(): HasMany
    {
        if (static::$hasDuePaymentsStatus === null) {
            static::$hasDuePaymentsStatus = Schema::hasColumn('due_payments', 'status');
        }

        $query = $this->duePayments();

        if (static::$hasDuePaymentsStatus) {
            $query->where(fn($q) => $q->where('payment_type', '!=', 'check')->orWhere('status', 'encashed'));
        } else {
            $query->where('payment_type', '!=', 'check');
        }

        return $query;
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->duePaymentsWithEncashment()->sum('amount');
    }

    public function getTotalDiscountAttribute(): float
    {
        return $this->duePaymentsWithEncashment()->sum('discount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->original_amount - $this->total_paid - $this->total_discount);
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