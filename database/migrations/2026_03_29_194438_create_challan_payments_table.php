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
        Schema::create('challan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_challan_id')->constrained('fee_challans')->cascadeOnDelete();
            $table->unique('fee_challan_id');
            $table->string('bank_name');
            $table->date('paid_date')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('voucher_image');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challan_payments');
    }
};
