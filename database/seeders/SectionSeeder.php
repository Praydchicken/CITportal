<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch active school year ID
        $activeSchoolYear = DB::table('school_years')->where('school_year_status', 'Active')->first();

        // Check if an active school year was found
        if (!$activeSchoolYear) {
            $this->command->error('No active school year found. Please seed the school_years table first.');
            return;
        }

        // Fetch existing Year Level IDs
        $yearLevels = DB::table('year_levels')->pluck('id', 'year_level');

        $sections = ['A', 'B', 'C', 'D'];
        $data = [];

        foreach ($yearLevels as $year => $yearId) {
            foreach ($sections as $section) {
                $data[] = [
                    'year_level_id' => $yearId,
                    'section' => $section,
                    'school_year_id' => $activeSchoolYear->id, // 🟢 Add school year ID here
                    'minimum_number_students' => 30,
                    'maximum_number_students' => 50,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('sections')->insert($data);
        $this->command->info('Sections seeded successfully with active school year.');
    }
}
