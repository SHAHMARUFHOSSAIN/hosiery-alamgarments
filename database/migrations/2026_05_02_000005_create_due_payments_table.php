<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('due_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('due_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_type', ['cash', 'check', 'mobile_banking']);
            $table->date('payment_date');
            $table->decimal('remaining_amount', 12, 2)->default(0);
            $table->string('note')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::table('dues', function (Blueprint $table) {
            $table->decimal('original_amount', 12, 2)->nullable()->after('amount');
        });

        DB::statement('UPDATE dues SET original_amount = amount WHERE original_amount IS NULL');

        Schema::table('dues', function (Blueprint $table) {
            $table->decimal('original_amount', 12, 2)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('dues', function (Blueprint $table) {
            $table->dropColumn('original_amount');
        });
        Schema::dropIfExists('due_payments');
    }
};
