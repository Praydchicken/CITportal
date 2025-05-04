<?php

namespace Database\Seeders;

use App\Models\SchoolYear;
use App\Models\Teacher;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('en_PH');
        $schoolYearActive = SchoolYear::where('school_year_status', 'Active')->first();

        $filipinoMiddleNames = [
            'Santos',
            'Cruz',
            'Reyes',
            'Bautista',
            'Gonzales',
        ];

        for ($teacher = 1; $teacher <= 2; $teacher++) {
            $firstName = $faker->firstName;
            $middleName = $faker->randomElement($filipinoMiddleNames);
            $lastName = $faker->lastName;
            $email = strtolower($firstName . $teacher . '@xample.com');

            $user = User::create([
                'user_type_id' => 3,
                'email' => $email,
                'password' => Hash::make('password123'),
            ]);

            Teacher::create([
                'user_id' => $user->id,
                'school_year_id' => $schoolYearActive->id,
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'phone_number' => '09' . $faker->numerify('#########'),
                'address' => $faker->streetAddress,
            ]);
        }
    }
}
