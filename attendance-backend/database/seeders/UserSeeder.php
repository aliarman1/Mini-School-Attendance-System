<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin/teacher users
        $users = [
            [
                'name' => 'Admin Teacher',
                'email' => 'admin@school.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'John Teacher',
                'email' => 'john@school.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Sarah Teacher',
                'email' => 'sarah@school.com',
                'password' => Hash::make('password123'),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        $this->command->info('✅ Created ' . count($users) . ' users');
        $this->command->info('   - Email: admin@school.com / Password: password123');
        $this->command->info('   - Email: john@school.com / Password: password123');
        $this->command->info('   - Email: sarah@school.com / Password: password123');
    }
}
