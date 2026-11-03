<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the "expired" challan status. A challan is set to expired by the school when the
 * student's next challan is issued; 1LINK then answers "Voucher expired" (code 03).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_challans', function (Blueprint $table) {
            $table->enum('status', ['pending', 'review', 'approved', 'expired'])->default('pending')->change();
        });
    }

    public function down(): void
    {
        DB::table('fee_challans')->where('status', 'expired')->update(['status' => 'pending']);

        Schema::table('fee_challans', function (Blueprint $table) {
            $table->enum('status', ['pending', 'review', 'approved'])->default('pending')->change();
        });
    }
};
