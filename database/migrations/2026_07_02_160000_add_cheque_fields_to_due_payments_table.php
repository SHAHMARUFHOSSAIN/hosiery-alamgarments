<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = ['bank_name', 'check_no', 'check_date', 'check_amount', 'check_reminder_date', 'check_photo'];
        $existing = array_filter($columns, fn($col) => Schema::hasColumn('due_payments', $col));
        $missing = array_diff($columns, $existing);

        if (!empty($missing)) {
            Schema::table('due_payments', function (Blueprint $table) use ($missing) {
                if (in_array('bank_name', $missing)) {
                    $table->string('bank_name')->nullable();
                }
                if (in_array('check_no', $missing)) {
                    $table->string('check_no')->nullable();
                }
                if (in_array('check_date', $missing)) {
                    $table->date('check_date')->nullable();
                }
                if (in_array('check_amount', $missing)) {
                    $table->decimal('check_amount', 12, 2)->nullable();
                }
                if (in_array('check_reminder_date', $missing)) {
                    $table->date('check_reminder_date')->nullable();
                }
                if (in_array('check_photo', $missing)) {
                    $table->string('check_photo')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        $columns = array_filter(['bank_name', 'check_no', 'check_date', 'check_amount', 'check_reminder_date', 'check_photo'], fn($col) => Schema::hasColumn('due_payments', $col));
        if (!empty($columns)) {
            Schema::table('due_payments', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
