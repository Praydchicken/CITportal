<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\User;
use App\Models\SchoolYear;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('en_PH'); 
        $schoolYears = SchoolYear::pluck('id')->all();

        $userCount = 15; // Specify how many faculty members will be generated.

        for ($i = 0; $i < $userCount; $i++) {

            $firstName = $faker->firstName;
            $middleName = $faker->firstName;
            $lastName = $faker->lastName;
            $email = strtolower($firstName . $i . '@example.com');

            $user = User::create([
                'user_type_id' => 3,
                'email' => $email,
                'password' => bcrypt('password123'), // Default password, hashed in the database
            ]);

            Teacher::create([
                'user_id' => $user->id, // The user ID is auto-incremented
                'school_year_id' => $faker->randomElement($schoolYears), // Randomly select a school year based on existing IDs (plucked)
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'phone_number' => '09' . $faker->randomNumber(9, true), // PH mobile format
                'address' => $faker->address, //Random address
            ]);
        }
    }
}
