<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // Import Hash facade

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::create([
            'user_id' => '1',
            'first_name' => 'Ben',
            'middle_name' => 'Corpuz',
            'last_name' => 'Diaz',
            'phone_number' => '555-555-5555',
            'gender' => 'Male',
            'address' => '123 Main St',
        ]);
    }
}
