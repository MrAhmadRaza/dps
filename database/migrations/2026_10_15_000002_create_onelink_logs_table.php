<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onelink_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);                      // inquiry | payment | rejected
            $table->string('consumer_number', 50)->nullable()->index();
            $table->string('tran_auth_id', 100)->nullable()->index();
            $table->string('amount', 30)->nullable();
            $table->string('response_code', 5)->nullable()->index();
            $table->foreignId('fee_challan_id')->nullable()->constrained('fee_challans')->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->json('request')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onelink_logs');
    }
};
