<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        DB::table('roles')->insert([
            ['role_name' => 'superadmin', 'created_at' => $now, 'updated_at' => $now],
            ['role_name' => 'admin', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
