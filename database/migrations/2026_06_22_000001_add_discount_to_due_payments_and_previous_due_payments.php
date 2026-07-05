<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('due_payments', function (Blueprint $table) {
            $table->decimal('discount', 12, 2)->default(0)->after('amount');
        });

        Schema::table('previous_due_payments', function (Blueprint $table) {
            $table->decimal('discount', 12, 2)->default(0)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('due_payments', function (Blueprint $table) {
            $table->dropColumn('discount');
        });

        Schema::table('previous_due_payments', function (Blueprint $table) {
            $table->dropColumn('discount');
        });
    }
};
