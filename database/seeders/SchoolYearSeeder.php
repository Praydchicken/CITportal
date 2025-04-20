<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('school_years')->insert([
            [
                'school_year' => '2023-2024',
                'school_year_status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_year' => '2024-2025',
                'school_year_status' => 'Upcoming',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_year' => '2022-2023',
                'school_year_status' => 'Inactive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
        ]);
    }
}
