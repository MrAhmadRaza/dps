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
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->string('father_name');
            $table->string('father_nic',15)->unique();
            $table->string('password');
            $table->string('mother_name')->nullable();
            $table->string('occupation');
            $table->decimal('income', 10, 2)->nullable();
            $table->text('address');
            $table->string('contact_no')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parents');
    }
};
