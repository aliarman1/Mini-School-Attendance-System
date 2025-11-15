<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Section;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            [
                'name' => 'A',
                'description' => 'Section A',
            ],
            [
                'name' => 'B',
                'description' => 'Section B',
            ],
            [
                'name' => 'C',
                'description' => 'Section C',
            ],
        ];

        foreach ($sections as $section) {
            Section::create($section);
        }
    }
}
