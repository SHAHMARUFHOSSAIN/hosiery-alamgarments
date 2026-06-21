<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('today_sales_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date');
            $table->integer('total_bills')->default(0);
            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->decimal('cheque_amt', 12, 2)->default(0);
            $table->decimal('ref_card_amt', 12, 2)->default(0);
            $table->decimal('discount_amt', 12, 2)->default(0);
            $table->decimal('due_amt', 12, 2)->default(0);
            $table->decimal('final_amount', 12, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['report_date', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('today_sales_reports');
    }
};
