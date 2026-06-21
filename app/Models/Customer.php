<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'mobile',
        'location',
        'opening_balance',
        'created_by',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function dues(): HasMany
    {
        return $this->hasMany(Due::class);
    }

    public function previousDues(): HasMany
    {
        return $this->hasMany(PreviousDue::class);
    }

    public function getLastBillDateAttribute(): ?Carbon
    {
        $lastBill = $this->bills()->latest('created_at')->first();
        return $lastBill?->created_at ?? $this->created_at;
    }

    public function isInactive(): bool
    {
        return $this->last_bill_date 
            && $this->last_bill_date->diffInDays(now()) > 30;
    }
}