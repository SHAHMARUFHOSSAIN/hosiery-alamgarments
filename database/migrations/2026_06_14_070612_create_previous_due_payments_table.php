<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('previous_due_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('previous_due_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_type', ['cash', 'check', 'mobile_banking']);
            $table->date('payment_date');
            $table->decimal('remaining_amount', 12, 2)->default(0);
            $table->string('note')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('check_no')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::table('previous_dues', function (Blueprint $table) {
            $table->decimal('original_amount', 12, 2)->nullable()->after('amount');
        });

        DB::statement('UPDATE previous_dues SET original_amount = amount WHERE original_amount IS NULL');

        Schema::table('previous_dues', function (Blueprint $table) {
            $table->decimal('original_amount', 12, 2)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('previous_dues', function (Blueprint $table) {
            $table->dropColumn('original_amount');
        });
        Schema::dropIfExists('previous_due_payments');
    }
};
