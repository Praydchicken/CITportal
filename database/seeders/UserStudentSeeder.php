<?php

namespace Database\Seeders;

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
            'Quezon City', 'Manila', 'Makati', 'Taguig', 'Pasig', 
            'Marikina', 'Mandaluyong', 'Parañaque', 'Las Piñas', 
            'Caloocan', 'Malabon', 'Navotas', 'Valenzuela', 'San Juan', 
            'Pasay', 'Muntinlupa', 'Pateros'
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

            Student::create([
                'user_id' => $user->id,
                'section_id' => rand(14, 16),
                'student_status_id' => 1,
                'year_level_id' => rand(1, 4),
                'semester_id' => rand(1, 2),
                'school_year_id' => 4,
                'student_number' => $studentNumberBase . str_pad($i, 6, '0', STR_PAD_LEFT),
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'gender' => $faker->randomElement(['Male', 'Female']),
                'phone_number' => '09' . $faker->numerify('#########'),
                'address' => $faker->streetAddress . ', ' . $faker->randomElement($ncrCities) . ', Metro Manila',
                'enrollment_date' => '2023-01-20',
            ]);
        }
    }
}
