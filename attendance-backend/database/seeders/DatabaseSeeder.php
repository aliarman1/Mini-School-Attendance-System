<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');
        $this->command->newLine();

        // Run seeders in order (classes and sections must be created before students, users before attendance)
        $this->call([
            UserSeeder::class,
            ClassSeeder::class,
            SectionSeeder::class,
            StudentSeeder::class,
            AttendanceSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('🎉 Database seeding completed successfully!');
        $this->command->newLine();
        $this->command->info('📝 You can now login with:');
        $this->command->info('   Email: admin@school.com');
        $this->command->info('   Password: password123');
    }
}
