<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        DB::table('settings')->insert([
            ['key' => 'company_name', 'value' => 'Alam Hosiery & Store'],
            ['key' => 'company_address', 'value' => '121/1, Fagu Mokbul Mansion, Nawabpur Road, Dhaka'],
            ['key' => 'company_phone', 'value' => '01711-111111'],
            ['key' => 'company_email', 'value' => 'info@alamhosiery.com'],
            ['key' => 'voucher_prefix', 'value' => 'V'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
