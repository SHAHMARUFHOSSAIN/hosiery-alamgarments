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
            $table->string('tt_bank_name')->nullable()->after('check_photo');
            $table->string('tt_account_no')->nullable()->after('tt_bank_name');
            $table->decimal('tt_amount', 12, 2)->nullable()->after('tt_account_no');
            $table->date('tt_date')->nullable()->after('tt_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['tt_bank_name', 'tt_account_no', 'tt_amount', 'tt_date']);
        });
    }
};
