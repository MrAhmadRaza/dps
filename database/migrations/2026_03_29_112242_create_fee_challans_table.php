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
        Schema::create('fee_challans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('month'); 
            $table->string('challan_no', 50)->nullable()->unique();
            $table->boolean('include_admission_fee')->default(false);
            $table->enum('status', ['pending','review','approved'])->default('pending');
            $table->date('issue_date');
            $table->date('due_date');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('amount_after_due_date',10, 2);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['student_id','month']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_challans');
    }
};
