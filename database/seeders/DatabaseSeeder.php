<?php

namespace Database\Seeders;

use App\Models\Curriculum;
use App\Models\StudentStatus;
use App\Models\User;
use App\Models\YearLevel;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // Import Hash facade

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserTypeSeeder::class,
            UserSeeder::class,
            AdminSeeder::class,
            YearLevelSeeder::class,
            SemesterSeeder::class,
            SchoolYearSeeder::class,
            StudentStatusSeeder::class,
            CurriculumSeeder::class,
            SectionSeeder::class,
            // Add other seeders here
        ]);

    }
}
