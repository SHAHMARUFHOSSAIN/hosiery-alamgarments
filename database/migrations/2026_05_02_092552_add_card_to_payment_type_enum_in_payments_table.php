<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_type ENUM('cash', 'check', 'tt', 'card', 'due') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_type ENUM('cash', 'check', 'tt', 'due') NOT NULL");
    }
};
