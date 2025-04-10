<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $semesters = [
            ['semester_name' => '1st Semester', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['semester_name' => '2nd Semester', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]

        ];

        DB::table('semesters')->insert($semesters);
    }
}
