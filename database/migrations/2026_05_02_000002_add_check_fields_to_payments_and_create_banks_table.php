<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('branch')->nullable();
            $table->string('account_no')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('details');
            $table->string('check_no')->nullable()->after('bank_name');
            $table->date('check_date')->nullable()->after('check_no');
            $table->string('check_photo')->nullable()->after('check_date');
            $table->date('check_reminder_date')->nullable()->after('check_date');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'check_no', 'check_date', 'check_photo', 'check_reminder_date']);
        });
        Schema::dropIfExists('banks');
    }
};
