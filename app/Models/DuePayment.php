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
        'payment_type',
        'payment_date',
        'remaining_amount',
        'note',
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

    public function due(): BelongsTo
    {
        return $this->belongsTo(Due::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
