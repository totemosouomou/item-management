<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => '太郎',
                'email' => 'taro@techis.jp',
                'password' => Hash::make('password'),
            ],
            [
                'name' => '次郎',
                'email' => 'jiro@techis.jp',
                'password' => Hash::make('password'),
            ],
            [
                'name' => '三郎',
                'email' => 'saburo@techis.jp',
                'password' => Hash::make('password'),
            ],
            [
                'name' => '四郎',
                'email' => 'shiro@techis.jp',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($users as $user) {
            $createdAt = Carbon::now()->subDays(rand(0, 60));
            DB::table('users')->insert([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => $user['password'],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}
