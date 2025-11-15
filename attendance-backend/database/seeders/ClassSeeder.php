<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassModel;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [
            [
                'name' => '9',
                'capacity' => 40,
                'description' => 'Grade 9',
            ],
            [
                'name' => '10',
                'capacity' => 40,
                'description' => 'Grade 10',
            ],
            [
                'name' => '11',
                'capacity' => 35,
                'description' => 'Grade 11',
            ],
            [
                'name' => '12',
                'capacity' => 35,
                'description' => 'Grade 12',
            ],
        ];

        foreach ($classes as $class) {
            ClassModel::create($class);
        }
    }
}
