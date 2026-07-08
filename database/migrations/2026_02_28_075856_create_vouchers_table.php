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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->onDelete('cascade');
            $table->foreignId('session_item_id')->nullable()->constrained('session_items')->onDelete('cascade');
            $table->date('due_date');
            $table->decimal('total_amount', 10, 2)->nullable()->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
