<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TodaySalesReport extends Model
{
    protected $fillable = [
        'report_date',
        'total_bills',
        'gross_amount',
        'cheque_amt',
        'ref_card_amt',
        'discount_amt',
        'due_amt',
        'final_amount',
        'status',
        'closed_by',
        'closed_at',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'gross_amount' => 'decimal:2',
            'cheque_amt' => 'decimal:2',
            'ref_card_amt' => 'decimal:2',
            'discount_amt' => 'decimal:2',
            'due_amt' => 'decimal:2',
            'final_amount' => 'decimal:2',
            'closed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
