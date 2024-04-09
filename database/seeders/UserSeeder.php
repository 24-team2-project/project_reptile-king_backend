<?php

namespace Database\Seeders;

use App\Models\Role;
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
        $user = User::create([
            'name' => 'superAdmin',
            'email' => 'pachungking@gmail.com',
            'password' => Hash::make('superAdmin2024!'),
            'nickname' => 'administrator',
            'phone' => '010-0000-0000',

        ]);

        $role = Role::where('role', 'super_admin')->first();
        $user->roles()->attach($role, ['created_at' => now()]);
        
    }
}
