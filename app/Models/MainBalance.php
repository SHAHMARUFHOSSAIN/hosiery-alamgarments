<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MainBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_no',
        'name',
        'amount',
        'balance',
        'type',
        'note',
        'invoice_no',
        'reference',
        'party_name',
        'user_id',
        'branch_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(User::class, 'branch_id');
    }
}