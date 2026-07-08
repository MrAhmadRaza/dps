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
        Schema::create('session_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->onDelete('cascade');
            $table->string('section');
            $table->string('class');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_items');
    }
};
