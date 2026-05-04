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
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('encashed_amount', 12, 2)->default(0)->after('check_amount');
            $table->boolean('partially_encashed')->default(false)->after('encashed_amount');
        });

        DB::statement("UPDATE payments SET encashed_amount = CASE WHEN status = 'encashed' THEN check_amount ELSE 0 END, partially_encashed = false WHERE payment_type = 'check'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['encashed_amount', 'partially_encashed']);
        });
    }
};
