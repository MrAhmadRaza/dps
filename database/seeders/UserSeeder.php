<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        DB::table('users')->insert([
            [
                'name' => 'super admin',
                'email' => 'superadmin@gmail.com',
                'password' => Hash::make('12345678A'),
                'role_id' => 1, // 1 = super admin
                'email_verified_at' => $now,
                'remember_token' => Str::random(10),
                'created_at' => $now,
                'updated_at' => $now,
            ],
             [
                'name' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('12345678A'),
                'role_id' => 2, // 1 = super admin
                'email_verified_at' => $now,
                'remember_token' => Str::random(10),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
