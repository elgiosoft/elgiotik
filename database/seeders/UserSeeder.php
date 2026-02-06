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
        // Create Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@elgiotik.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create Staff User
        User::create([
            'name' => 'Staff User',
            'email' => 'staff@elgiotik.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'is_active' => true,
        ]);

        // Create Cashier User
        User::create([
            'name' => 'Cashier User',
            'email' => 'cashier@elgiotik.com',
            'password' => Hash::make('password'),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->command->info('✅ Created 3 users: admin, staff, and cashier');
        $this->command->info('📧 Email: admin@elgiotik.com / staff@elgiotik.com / cashier@elgiotik.com');
        $this->command->info('🔑 Password: password (for all users)');
    }
}
