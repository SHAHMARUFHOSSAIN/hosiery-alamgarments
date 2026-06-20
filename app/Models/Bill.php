<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_no',
        'customer_id',
        'shop_name',
        'bill_man',
        'bill_amount',
        'discount',
        'report_date',
        'edited_at',
        'edited_by',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'bill_amount' => 'decimal:2',
            'discount' => 'decimal:2',
            'report_date' => 'date',
            'edited_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function checkPayment()
    {
        return $this->hasOne(Payment::class)->where('payment_type', 'check');
    }

    public function dues(): HasMany
    {
        return $this->hasMany(Due::class);
    }

    public function isEditable(): bool
    {
        if (Auth::user()?->isAdmin()) {
            return true;
        }
        return $this->created_at && $this->created_at->diffInHours(now()) < 24;
    }

    public function isDeletable(): bool
    {
        return $this->isEditable();
    }
}