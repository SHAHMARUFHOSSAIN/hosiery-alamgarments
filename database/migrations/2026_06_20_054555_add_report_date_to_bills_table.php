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
        Schema::table('bills', function (Blueprint $table) {
            $table->date('report_date')->nullable()->after('discount');
        });

        \DB::statement('UPDATE bills SET report_date = DATE(created_at) WHERE report_date IS NULL');
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn('report_date');
        });
    }
};
