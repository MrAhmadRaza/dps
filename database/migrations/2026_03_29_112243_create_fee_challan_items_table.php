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
        Schema::create('fee_challan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_challan_id')->constrained('fee_challans')->onDelete('cascade');
            $table->unsignedBigInteger('fee_item_id');
            $table->string('fee_item_name');
            $table->decimal('amount',10,2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_challan_items');
    }
};
