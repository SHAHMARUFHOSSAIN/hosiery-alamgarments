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
        if (!Schema::hasColumn('due_payments', 'encashed_amount')) {
            Schema::table('due_payments', function (Blueprint $table) {
                $table->decimal('encashed_amount', 12, 2)->default(0);
                $table->string('status', 50)->default('pending');
            });
            if (Schema::hasColumn('due_payments', 'status') && Schema::hasColumn('due_payments', 'check_amount')) {
                \DB::table('due_payments')->where('payment_type', 'check')->where('status', 'pending')->update(['status' => 'encashed', 'encashed_amount' => \DB::raw('check_amount')]);
            }
        }

        if (!Schema::hasColumn('previous_due_payments', 'encashed_amount')) {
            Schema::table('previous_due_payments', function (Blueprint $table) {
                $table->decimal('encashed_amount', 12, 2)->default(0);
                $table->string('status', 50)->default('pending');
            });
            if (Schema::hasColumn('previous_due_payments', 'status') && Schema::hasColumn('previous_due_payments', 'check_amount')) {
                \DB::table('previous_due_payments')->where('payment_type', 'check')->where('status', 'pending')->update(['status' => 'encashed', 'encashed_amount' => \DB::raw('check_amount')]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $dueColumns = array_filter(['encashed_amount', 'status'], fn($col) => Schema::hasColumn('due_payments', $col));
        if (!empty($dueColumns)) {
            Schema::table('due_payments', function (Blueprint $table) use ($dueColumns) {
                $table->dropColumn($dueColumns);
            });
        }

        $prevDueColumns = array_filter(['encashed_amount', 'status'], fn($col) => Schema::hasColumn('previous_due_payments', $col));
        if (!empty($prevDueColumns)) {
            Schema::table('previous_due_payments', function (Blueprint $table) use ($prevDueColumns) {
                $table->dropColumn($prevDueColumns);
            });
        }
    }
};
