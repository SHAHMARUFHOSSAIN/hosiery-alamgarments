<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('main_balances', function (Blueprint $table) {
            $table->decimal('balance', 15, 2)->default(0)->after('amount');
            $table->string('invoice_no')->nullable()->after('balance');
            $table->string('reference')->nullable()->after('invoice_no');
            $table->string('party_name')->nullable()->after('reference');
        });
    }

    public function down(): void
    {
        Schema::table('main_balances', function (Blueprint $table) {
            $table->dropColumn(['balance', 'invoice_no', 'reference', 'party_name']);
        });
    }
};
