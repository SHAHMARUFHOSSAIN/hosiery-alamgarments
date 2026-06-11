<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    protected $fillable = [
        'file_name',
        'file_path',
        'import_type',
        'import_date',
        'total_rows',
        'inserted_rows',
        'updated_rows',
        'skipped_rows',
        'errors',
        'imported_data',
        'status',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'import_date' => 'date',
            'errors' => 'array',
            'imported_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
