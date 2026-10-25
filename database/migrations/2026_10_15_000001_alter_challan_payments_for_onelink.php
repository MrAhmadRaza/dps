<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challan_payments', function (Blueprint $table) {
            // 1LINK payments have no uploaded slip image.
            $table->string('voucher_image')->nullable()->change();

            $table->enum('source', ['manual', 'onelink'])->default('manual')->after('fee_challan_id');
            $table->string('tran_auth_id', 100)->nullable()->unique()->after('transaction_id');
            $table->decimal('amount', 10, 2)->nullable()->after('tran_auth_id');
            $table->unsignedSmallInteger('channel')->nullable()->after('amount');
            $table->string('bank_mnemonic', 20)->nullable()->after('channel');
            $table->dateTime('paid_at')->nullable()->after('paid_date');
        });
    }

    public function down(): void
    {
        Schema::table('challan_payments', function (Blueprint $table) {
            $table->dropUnique(['tran_auth_id']);
            $table->dropColumn(['source', 'tran_auth_id', 'amount', 'channel', 'bank_mnemonic', 'paid_at']);
        });
    }
};
