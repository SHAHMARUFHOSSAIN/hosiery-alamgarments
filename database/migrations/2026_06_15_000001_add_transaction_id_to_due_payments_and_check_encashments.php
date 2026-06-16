<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('due_payments', function (Blueprint $table) {
            $table->string('transaction_id', 100)->nullable()->after('note');
        });

        Schema::table('check_encashments', function (Blueprint $table) {
            $table->string('transaction_id', 100)->nullable()->after('note');
        });

        Schema::table('previous_due_payments', function (Blueprint $table) {
            $table->string('transaction_id', 100)->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('due_payments', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
        });

        Schema::table('check_encashments', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
        });

        Schema::table('previous_due_payments', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
        });
    }
};
