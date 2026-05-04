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
            $table->string('card_name')->nullable()->after('tt_date');
            $table->string('card_location')->nullable()->after('card_name');
            $table->decimal('card_amount', 12, 2)->nullable()->after('card_location');
            $table->date('card_date')->nullable()->after('card_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['card_name', 'card_location', 'card_amount', 'card_date']);
        });
    }
};
