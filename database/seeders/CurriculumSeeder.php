<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Curriculum;

use Illuminate\Support\Facades\DB;

class CurriculumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $curricula = [
            // FIRST YEAR - 1ST SEM
            ['year_level_id' => 1, 'semester_id' => 1, 'course_code' => 'INTROCOMP', 'subject_name' => 'Introduction to Computing', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0',  'units' => '0'],
            ['year_level_id' => 1, 'semester_id' => 1, 'course_code' => 'COMPRO 1', 'subject_name' => 'Computer Programming 1', 'description' => '', 'lecture_units' => '2', 'lab_units' => '1', 'units' => '0'],
            ['year_level_id' => 1, 'semester_id' => 1, 'course_code' => 'FIL 1', 'subject_name' => 'Sining ng Komunikasyon', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 1, 'semester_id' => 1, 'course_code' => 'MATH 1', 'subject_name' => 'Math in the Modern World', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 1, 'semester_id' => 1, 'course_code' => 'ART 1', 'subject_name' => 'Art Appreciation', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 1, 'semester_id' => 1, 'course_code' => 'PE 1', 'subject_name' => 'Physical Education 1', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 1, 'semester_id' => 1, 'course_code' => 'PHILIT', 'subject_name' => 'Literatures of the Philippines', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 1, 'semester_id' => 1, 'course_code' => 'ENG 1', 'subject_name' => 'Purposive Communication', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 1, 'semester_id' => 1, 'course_code' => 'NSTP 1', 'subject_name' => 'National Service Training Program 1', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],

            // FIRST YEAR - 2ND SEM
            ['year_level_id' => 1, 'semester_id' => 2, 'course_code' => 'HUMCOM', 'subject_name' => 'Introduction to Human Computer Interaction', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 1, 'semester_id' => 2, 'course_code' => 'COMPRO 2', 'subject_name' => 'Computer Programming 2', 'description' => '', 'lecture_units' => '2', 'lab_units' => '1', 'units' => '0'],
            ['year_level_id' => 1, 'semester_id' => 2, 'course_code' => 'FIL 2', 'subject_name' => 'Filipino Panitikan', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 1, 'semester_id' => 2, 'course_code' => 'SCITECH', 'subject_name' => 'Science, Technology and Society', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 1, 'semester_id' => 2, 'course_code' => 'USELF', 'subject_name' => 'Understanding the Self', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 1, 'semester_id' => 2, 'course_code' => 'PE 2', 'subject_name' => 'Physical Education 2', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 1, 'semester_id' => 2, 'course_code' => 'OS', 'subject_name' => 'Operating System', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 1, 'semester_id' => 2, 'course_code' => 'NSTP 2', 'subject_name' => 'National Service Training Program 2', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],

            // 2ND YR - 1ST SEM
            ['year_level_id' => 2, 'semester_id' => 1, 'course_code' => 'DATSTRUC', 'subject_name' => 'Data Structure and Algorithm', 'description' => '', 'lecture_units' => '2', 'lab_units' => '1', 'units' => '0'],
            ['year_level_id' => 2, 'semester_id' => 1, 'course_code' => 'OOP', 'subject_name' => 'Object Oriented Programming', 'description' => '', 'lecture_units' => '2', 'lab_units' => '1', 'units' => '0'],
            ['year_level_id' => 2, 'semester_id' => 1, 'course_code' => 'PLATTECH', 'subject_name' => 'Platform Technologies', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 2, 'semester_id' => 1, 'course_code' => 'DISCMATH', 'subject_name' => 'Discrete Mathematics', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 2, 'semester_id' => 1, 'course_code' => 'ANMOD', 'subject_name' => 'Analytics Modeling', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 2, 'semester_id' => 1, 'course_code' => 'PE 3', 'subject_name' => 'Physical Education 3', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 2, 'semester_id' => 1, 'course_code' => 'QUANMET', 'subject_name' => 'Quantitative Methods', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 2, 'semester_id' => 1, 'course_code' => 'RIZAL', 'subject_name' => 'Rizals Life and Works', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            
            // 2ND YR - 2ND SEM
            ['year_level_id' => 2, 'semester_id' => 2, 'course_code' => 'INFOMAN', 'subject_name' => 'Information Management', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 2, 'semester_id' => 2, 'course_code' => 'NET 1', 'subject_name' => 'Networking 1 (Fundamentals)', 'description' => '', 'lecture_units' => '2', 'lab_units' => '1', 'units' => '0'],
            ['year_level_id' => 2, 'semester_id' => 2, 'course_code' => 'INTECH 1', 'subject_name' => 'Integrative Programming and Technologies 1', 'description' => '', 'lecture_units' => '2', 'lab_units' => '1', 'units' => '0'],
            ['year_level_id' => 2, 'semester_id' => 2, 'course_code' => 'WEBAPPS', 'subject_name' => 'Web Application Development', 'description' => '', 'lecture_units' => '2', 'lab_units' => '1', 'units' => '0'],
            ['year_level_id' => 2, 'semester_id' => 2, 'course_code' => 'TECHNOP', 'subject_name' => 'Technopreneur', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 2, 'semester_id' => 2, 'course_code' => 'PE 4', 'subject_name' => 'Physical Education 4', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 2, 'semester_id' => 2, 'course_code' => 'FUNDBS', 'subject_name' => 'Fundamentals of Database Systems', 'description' => '', 'lecture_units' => '2', 'lab_units' => '1', 'units' => '0'],
            ['year_level_id' => 2, 'semester_id' => 2, 'course_code' => 'ANTECH', 'subject_name' => 'Analytics Techniques and Tools', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],


             // 3RD YR - 1ST SEM
            ['year_level_id' => 3, 'semester_id' => 1, 'course_code' => 'NET 2', 'subject_name' => 'Networking 2 (Routing Protocols)', 'description' => '', 'lecture_units' => '2', 'lab_units' => '1', 'units' => '0'],
            ['year_level_id' => 3, 'semester_id' => 1, 'course_code' => 'SIA 1', 'subject_name' => 'Systems Integration and Architecture 1', 'description' => '', 'lecture_units' => '2', 'lab_units' => '1', 'units' => '0'],
            ['year_level_id' => 3, 'semester_id' => 1, 'course_code' => 'INTECH 2', 'subject_name' => 'Integrative Programming and Technologies 2', 'description' => '', 'lecture_units' => '2', 'lab_units' => '1', 'units' => '0'],
            ['year_level_id' => 3, 'semester_id' => 1, 'course_code' => 'FDWDM', 'subject_name' => 'Fundamentals of Data Warehousing and Data Mining', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 3, 'semester_id' => 1, 'course_code' => 'SAD', 'subject_name' => 'Systems Analysis and Design', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 3, 'semester_id' => 1, 'course_code' => 'FUNBUS', 'subject_name' => 'Fundamentals of Business Analytics', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],

            // 3RD YR - 2ND SEM
            ['year_level_id' => 3, 'semester_id' => 2, 'course_code' => 'IAS 1', 'subject_name' => 'Information Assurance and Security 1', 'description' => '', 'lecture_units' => '2', 'lab_units' => '1', 'units' => '0'],
            ['year_level_id' => 3, 'semester_id' => 2, 'course_code' => 'ETHICS', 'subject_name' => 'Ethics', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 3, 'semester_id' => 2, 'course_code' => 'ADET', 'subject_name' => 'Application Development and Emerging Technologies', 'description' => '', 'lecture_units' => '2', 'lab_units' => '1', 'units' => '0'],
            ['year_level_id' => 3, 'semester_id' => 2, 'course_code' => 'ENDAMA', 'subject_name' => 'Enterprise Data Management', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 3, 'semester_id' => 2, 'course_code' => 'PHILHIS', 'subject_name' => 'Readings in Philippine History', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 3, 'semester_id' => 2, 'course_code' => 'CONWORLD', 'subject_name' => 'The Contemporary World', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
          
            // 4TH YR - 1ST SEM
            ['year_level_id' => 4, 'semester_id' => 1, 'course_code' => 'IAS 2', 'subject_name' => 'Information Assurance and Security 2', 'description' => '', 'lecture_units' => '2', 'lab_units' => '1', 'units' => '0'],
            ['year_level_id' => 4, 'semester_id' => 1, 'course_code' => 'ITPROJ 1', 'subject_name' => 'Capstone Project and Research 1', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 4, 'semester_id' => 1, 'course_code' => 'SAM', 'subject_name' => 'Systems Administration and Maintenance 1 ', 'description' => '', 'lecture_units' => '2', 'lab_units' => '1', 'units' => '0'],
            ['year_level_id' => 4, 'semester_id' => 1, 'course_code' => 'SOCPRO', 'subject_name' => 'Social and Professional Issues', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            
            // 4TH YR - 2ND SEM
            ['year_level_id' => 4, 'semester_id' => 2, 'course_code' => 'ITPROJ 2', 'subject_name' => 'Capstone Project and Research 2', 'description' => '', 'lecture_units' => '3', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 4, 'semester_id' => 2, 'course_code' => 'PRAC 1', 'subject_name' => 'Practicum', 'description' => '', 'lecture_units' => '6', 'lab_units' => '0', 'units' => '0'],
            ['year_level_id' => 4, 'semester_id' => 2, 'course_code' => 'SIA 2', 'subject_name' => 'Systems Integration and Architecture 2', 'description' => '', 'lecture_units' => '2', 'lab_units' => '1', 'units' => '0'],
        ];

        foreach ($curricula as $curriculum) {
            Curriculum::create($curriculum);
        }
    }
}
