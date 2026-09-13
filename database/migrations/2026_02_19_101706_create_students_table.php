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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('parents')->onDelete('cascade');
            $table->string('register_no', 70)->unique()->nullable();
            $table->string('name');
            $table->date('date_of_birth');
            $table->enum('gender', ['Male', 'Female']);
            $table->string('b_form_no')->nullable();
            $table->string('religion')->nullable();
            $table->string('caste')->nullable();
            $table->string('domicile')->nullable();
            $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->onDelete('set null');
            $table->foreignId('session_item_id')->nullable()->constrained('session_items')->onDelete('set null');
            $table->boolean('discount')->default(false);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->date('discount_due_date')->nullable();
            $table->text('discount_notes')->nullable();
            $table->string('previous_school')->nullable();
            $table->date('last_fee_paid_upto')->nullable();
            $table->boolean('fees_paid_last_institution')->default(false);
            $table->text('games_sports')->nullable();
            $table->text('extra_curricular')->nullable();
            $table->date('admission_date')->nullable();
            $table->boolean('declaration_accepted')->default(false);
            $table->string('photo_path')->nullable();
            $table->string('receipt_no')->nullable()->unique();
            $table->date('fee_paid_date')->nullable();
            $table->enum('status', ['active','inactive'])->default('inactive');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
