<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $yearLevels = DB::table('year_levels')->pluck('id', 'year_level'); // Fetch existing Year Level IDs

        $sections = ['A', 'B', 'C', 'D'];

        $data = [];

        foreach ($yearLevels as $year => $yearId) {
            foreach ($sections as $section) {
                $data[] = [
                    'year_level_id' => $yearId,  // Assign to respective year level
                    'section' => $section,
                    'minimum_number_students' => 30,
                    'maximum_number_students' => 50,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('sections')->insert($data);

    }
}
