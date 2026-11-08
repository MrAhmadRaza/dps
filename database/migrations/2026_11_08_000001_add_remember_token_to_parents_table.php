<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Parent portal "Keep me signed in" needs a remember_token column on parents.
 * Nullable, so existing rows and the admin portal are unaffected.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('parents', 'remember_token')) {
            Schema::table('parents', function (Blueprint $table) {
                $table->rememberToken()->after('password');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('parents', 'remember_token')) {
            Schema::table('parents', function (Blueprint $table) {
                $table->dropColumn('remember_token');
            });
        }
    }
};
