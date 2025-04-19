<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\SchoolYear;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run()
    {
        $activeSchoolYear = SchoolYear::where('school_year_status', 'Active')->first();

        if (!$activeSchoolYear) {
            $this->command->error('No active school year found.');
            return;
        }

        $schoolYearId = $activeSchoolYear->id;

        $sections = [];

        for ($year = 1; $year <= 4; $year++) {
            for ($semester = 1; $semester <= 2; $semester++) {
                foreach (['A', 'B', 'C', 'D'] as $suffix) {
                    $sectionName = ($year * 100 + $semester) . $suffix;

                    $sections[] = [
                        'year_level_id' => $year,
                        'semester_id' => $semester,
                        'section' => $sectionName,
                        'minimum_number_students' => 30,
                        'maximum_number_students' => 50,
                        'school_year_id' => $schoolYearId, // ✅ Dynamic assignment
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        DB::table('sections')->insert($sections);
    }
}
