<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name'       => 'Admin User',
                'email'      => 'admin@example.com',
                'password'   => Hash::make('adminpass'),
                'role'       => 1, // 管理者
                'created_at' => now(),
                'updated_at' => now(),
            ],[
                'name'       => 'Test User',
                'email'      => 'test@example.com',
                'password'   => Hash::make('password'),
                'role'       => 0, // 一般ユーザー
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        User::factory()->count(9)->create();
    }
}
