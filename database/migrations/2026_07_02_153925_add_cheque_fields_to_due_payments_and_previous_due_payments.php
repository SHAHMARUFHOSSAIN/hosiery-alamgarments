<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('previous_due_payments', 'check_date')) {
            Schema::table('previous_due_payments', function (Blueprint $table) {
                $table->date('check_date')->nullable()->after('check_no');
                $table->decimal('check_amount', 12, 2)->nullable()->after('check_date');
                $table->date('check_reminder_date')->nullable()->after('check_amount');
                $table->string('check_photo')->nullable()->after('check_reminder_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('previous_due_payments', function (Blueprint $table) {
            $table->dropColumn(['check_date', 'check_amount', 'check_reminder_date', 'check_photo']);
        });
    }
};
