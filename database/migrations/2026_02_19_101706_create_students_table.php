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
            $table->string('name');
            $table->date('date_of_birth');
            $table->enum('gender', ['Male', 'Female']);
            $table->string('b_form_no')->nullable();
            $table->string('religion')->nullable();
            $table->string('caste')->nullable();
            $table->string('domicile')->nullable();
            $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->onDelete('set null');
            $table->foreignId('session_item_id')->nullable()->constrained('session_items')->onDelete('set null');
            $table->string('previous_school')->nullable();
            $table->date('last_fee_paid_upto')->nullable();
            $table->boolean('fees_paid_last_institution')->default(false);
            $table->text('games_sports')->nullable();
            $table->text('extra_curricular')->nullable();
            $table->date('admission_date')->nullable();
            $table->boolean('declaration_accepted')->default(false);
            $table->string('photo_path')->nullable();
            $table->decimal('admission_fee', 10, 2)->nullable()->default(0);
            $table->decimal('tuition_fee', 10, 2)->nullable()->default(0);
            $table->decimal('stationary_fee', 10, 2)->nullable()->default(0);
            $table->decimal('library_fee', 10, 2)->nullable()->default(0);
            $table->decimal('sports_fund', 10, 2)->nullable()->default(0);
            $table->decimal('security_deposit', 10, 2)->nullable()->default(0);
            $table->decimal('development_fund', 10, 2)->nullable()->default(0);
            $table->decimal('misc_charges', 10, 2)->nullable()->default(0);
            $table->decimal('total_fee_paid', 10, 2)->nullable()->default(0);
            $table->string('receipt_no')->nullable();
            $table->date('fee_paid_date')->nullable();
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
