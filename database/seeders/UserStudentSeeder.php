<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Student;
use Faker\Factory as Faker;

class UserStudentSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('en_PH');
        $studentNumberBase = '042';

        $ncrCities = [
            'Quezon City',
            'Manila',
            'Makati',
            'Taguig',
            'Pasig',
            'Marikina',
            'Mandaluyong',
            'Parañaque',
            'Las Piñas',
            'Caloocan',
            'Malabon',
            'Navotas',
            'Valenzuela',
            'San Juan',
            'Pasay',
            'Muntinlupa',
            'Pateros'
        ];

        for ($i = 1; $i <= 50; $i++) {
            $firstName = $faker->firstName;
            $middleName = $faker->firstName;
            $lastName = $faker->lastName;
            $email = strtolower($firstName . $i . '@example.com');

            $user = User::create([
                'user_type_id' => 2,
                'email' => $email,
                'password' => Hash::make('password123'),
            ]);

            $randomSectionId = rand(1, 30); // Adjust the range to match your section IDs

            // Attempt to retrieve the section with the random ID
            $section = Section::find($randomSectionId);

            Student::create([
                'user_id' => $user->id,
                'section_id' => $randomSectionId,
                'student_status_id' => 1,
                'year_level_id' => $section->year_level_id ?? 1, // Default to 1 if section not found
                'semester_id' => $section->semester_id ?? 1, // Default to 1 if section not found
                'school_year_id' => 1,
                'student_number' => $studentNumberBase . str_pad($i, 6, '0', STR_PAD_LEFT),
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'gender' => $faker->randomElement(['Male', 'Female']),
                'phone_number' => '09' . $faker->numerify('#########'),
                'address' => $faker->streetAddress . ', ' . $faker->randomElement($ncrCities) . ', Metro Manila',
                'enrollment_date' => '2025-06-20',
            ]);
        }
    }
}
