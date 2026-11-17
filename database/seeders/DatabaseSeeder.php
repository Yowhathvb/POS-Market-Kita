<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default Admin User
        User::create([
            'id_users' => 'POS-ADMIN001',
            'nama' => 'Admin POS Market',
            'username' => 'admin',
            'email' => 'admin@posmarket.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'key' => null, // Generated when user logs in
            'value' => [
                'phone' => '081234567890',
                'branch' => 'Headquarters Jakarta',
                'permissions' => ['all'],
            ],
        ]);

        // Default Regular User
        User::create([
            'id_users' => 'POS-BUDI001',
            'nama' => 'Budi Santoso',
            'username' => 'budi',
            'email' => 'budi@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'key' => null, // Generated when user logs in
            'value' => [
                'phone' => '089876543210',
                'address' => 'Jl. Pemuda No. 45, Surabaya',
                'membership' => 'silver',
            ],
        ]);
    }
}
