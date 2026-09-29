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
        'category',
        'product_name',
        'size_name',
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

    public function categoryName(): ?string
    {
        return Product::categoryLabel($this->category);
    }

    /**
     * SQL that resolves a product attribute, falling back to the product
     * catalog when the bill line was saved without it.
     */
    public static function attributeSql(string $column): string
    {
        return "(SELECT p.{$column} FROM products p"
            . " WHERE p.name = bill_products.product_name"
            . " AND p.{$column} IS NOT NULL AND p.{$column} <> ''"
            . " AND (bill_products.size_name IS NULL OR bill_products.size_name = '' OR p.size = bill_products.size_name)"
            . ' ORDER BY p.id LIMIT 1)';
    }

    public static function resolvedSizeSql(): string
    {
        return 'COALESCE(bill_products.size_name, ' . static::attributeSql('size') . ')';
    }

    public static function resolvedCategorySql(): string
    {
        return 'COALESCE(bill_products.category, ' . static::attributeSql('category') . ')';
    }
}
