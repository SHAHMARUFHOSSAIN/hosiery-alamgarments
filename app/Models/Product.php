<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'name',
        'size',
        'rate',
        'unit',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public static function categories(): array
    {
        return config('product.categories', []);
    }

    public static function categoryLabel(?string $category): ?string
    {
        return $category === null || $category === '' ? null : (static::categories()[$category] ?? $category);
    }

    public static function sizeOptions(): array
    {
        return config('product.sizes', []);
    }

    public static function sizeOptionsFor($products = null): array
    {
        if ($products instanceof \Illuminate\Contracts\Pagination\Paginator) {
            $products = $products->getCollection();
        }

        $sizes = collect($products ?? [])->pluck('size')->filter(function ($size) {
            return is_scalar($size) && trim((string) $size) !== '';
        })->map(function ($size) {
            return trim((string) $size);
        });

        return $sizes->merge(static::sizeOptions())->unique()->values()->all();
    }

    public function categoryName(): ?string
    {
        return static::categoryLabel($this->category);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
