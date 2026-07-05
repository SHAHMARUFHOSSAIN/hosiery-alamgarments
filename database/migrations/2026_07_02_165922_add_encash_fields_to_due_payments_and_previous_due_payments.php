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
                $table->decimal('encashed_amount', 12, 2)->default(0)->after('check_photo');
                $table->string('status', 50)->default('pending')->after('encashed_amount');
            });
            \DB::table('due_payments')->where('payment_type', 'check')->where('status', 'pending')->update(['status' => 'encashed', 'encashed_amount' => \DB::raw('check_amount')]);
        }

        if (!Schema::hasColumn('previous_due_payments', 'encashed_amount')) {
            Schema::table('previous_due_payments', function (Blueprint $table) {
                $table->decimal('encashed_amount', 12, 2)->default(0)->after('check_photo');
                $table->string('status', 50)->default('pending')->after('encashed_amount');
            });
            \DB::table('previous_due_payments')->where('payment_type', 'check')->where('status', 'pending')->update(['status' => 'encashed', 'encashed_amount' => \DB::raw('check_amount')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('due_payments', function (Blueprint $table) {
            $table->dropColumn(['encashed_amount', 'status']);
        });

        Schema::table('previous_due_payments', function (Blueprint $table) {
            $table->dropColumn(['encashed_amount', 'status']);
        });
    }
};
