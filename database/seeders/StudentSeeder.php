<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Student::create([
            'user_id' => '1',
            'first_name' => 'Joshua',
            'middle_name' => 'Golong',
            'last_name' => 'Bolasa',
            'phone_number' => '0912345678',
            'gender' => 'Male',
            'address' => '123 DiyanLang St',
            'enrollment_date' => '2014-06-12',
            'status' => 'Active',
        ]);
    }
}
