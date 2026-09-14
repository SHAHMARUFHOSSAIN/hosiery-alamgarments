<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'product_name',
        'rate',
        'quantity',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'quantity' => 'decimal:2',
            'price' => 'decimal:2',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }
}