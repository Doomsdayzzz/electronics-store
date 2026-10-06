<?php

namespace Database\Seeders;


use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name'=>'user1', 'email'=>'user1@ya.ru', 'password'=> Hash::make('1234'),'phone'=>'8-999-123-45-67', 'is_admin'=>0],
            ['name'=>'admin1', 'email'=>'admin1@ya.ru', 'password'=> Hash::make('132456'),'phone'=>'8-777-123-45-67', 'is_admin'=>1],

        ];
        foreach ($users as $user) {
            User::query()->firstOrCreate($user);
        }
    }
}
