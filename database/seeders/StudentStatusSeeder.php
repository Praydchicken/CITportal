<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('student_statuses')->insert([
            ['status_name' => 'Enrolled'],
            ['status_name' => 'Graduated'],
            ['status_name' => 'Dropped'],
        ]);
    }
}
