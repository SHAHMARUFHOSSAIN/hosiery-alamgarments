<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('check_encashments', function (Blueprint $table) {
            $table->string('payment_type', 50)->default('cash')->after('encash_amount');
        });
    }

    public function down(): void
    {
        Schema::table('check_encashments', function (Blueprint $table) {
            $table->dropColumn('payment_type');
        });
    }
};
